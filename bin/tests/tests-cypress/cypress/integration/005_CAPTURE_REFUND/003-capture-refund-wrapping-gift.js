/**
 * Functionality tested
 *  - Set conf for manual capture ( Just authorization)
 *  - Do transaction with basket
 *  - Do several manual capture
 *  - Process transaction and check status order, order note
 *
 */
var utils = require('../../support/utils');
describe('Process transaction and do manual capture and refund with basket and wrapping gift', function () {

    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.fixture('basket').as("basket");
        cy.fixture('notification').as("notification");
    });

    /**
     * Process an payment with mapping ( Transaction should be OK )
     */
    it('Succeed Transaction', function () {

        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.get('#operating_mode').select("hosted_page");
        cy.get('#display_hosted_page').select("redirect");
        cy.get('#capture_mode').select("manual");
        cy.activateBasket();
        cy.get('#credit_card_form > .panel-footer > .pull-right').click();

        cy.activateWrappingGift();

        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.goToCart();

        cy.fillBillingForm();
        cy.fillShippingForm();
        cy.selectShippingForm(true);
        cy.get('input[data-module-name="credit_card"]').click({force: true});
        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});

        cy.payCcHostedWithHF("visa_ok");
        cy.checkOrderSuccess();
        cy.saveLastOrderId();
    });

    /**
     * Send transaction for authorization
     */
    it('Check Basket in transaction and send authorization', function () {
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderCartId + "(");
        cy.openNotificationOnHipayBO(116).then(() => {
            let basketTransaction = utils.fetchInput("basket", decodeURI(this.data));
            assert.equal(basketTransaction, JSON.stringify(this.basket.wrappingGift));
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    /**
     * Do capture and process transaction
     */
    it('Process capture with basket', function () {
        let stub = cy.stub();
        cy.logAndGoToDetailOrder(this.order.lastOrderId);
        cy.checkAuthorizationStatusMessage();
        cy.get("#hipay_capture_type").select('partial');
        cy.get("#block-capture-amount > .table > tbody > :nth-child(1) > :nth-child(5) input").clear();
        cy.get("#block-capture-amount > .table > tbody > :nth-child(1) > :nth-child(5) input").type("5");
        cy.get('#block-capture-amount > .table > tbody > :nth-child(2) > :nth-child(5) input').clear();
        cy.get('#block-capture-amount > .table > tbody > :nth-child(2) > :nth-child(5) input').type("3");
        cy.get("#capture-wrapping").click();

        cy.get('#total-capture-input').should('have.value', '167.56');

        cy.on('window:confirm', stub);

        cy.get('#hipay_capture_form > :nth-child(7) > .btn').click();

        cy.get('#block-capture-amount > .table > tbody > :nth-child(1) > :nth-child(5) span').contains("0");
        cy.get('#block-capture-amount > .table > tbody > :nth-child(2) > :nth-child(5) span').contains("0");
    });

    /**
     * Send transaction for capture
     */
    it('Send capture notification', function () {
        cy.log("sendNotification");
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderCartId + "(");
        cy.openNotificationOnHipayBO(118).then(() => {
            let basketTransaction = utils.fetchInput("basket", decodeURI(this.data));
            assert.equal(basketTransaction, JSON.stringify(this.basket.wrappingGiftCapture));
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    it('Process refund with basket', function () {
        cy.logAndGoToDetailOrder(this.order.lastOrderId);
        cy.checkCaptureStatusMessage();

        let stub = cy.stub();
        cy.get("#hipay_refund_type").select('partial');
        cy.get("#block-refund-amount > .table > tbody > :nth-child(1) > :nth-child(5) input").clear();
        cy.get("#block-refund-amount > .table > tbody > :nth-child(1) > :nth-child(5) input").type("5");
        cy.get('#block-refund-amount > .table > tbody > :nth-child(2) > :nth-child(5) input').clear();
        cy.get('#block-refund-amount > .table > tbody > :nth-child(2) > :nth-child(5) input').type("3");
        cy.get("#refund-wrapping").click();

        cy.get('#total-refund-input').should('have.value', '167.56');

        cy.on('window:confirm', stub);

        cy.get('#hipay_refund_form > :nth-child(7) > .btn').click();

        cy.get('tfoot > :nth-child(2) > :nth-child(4) > .badge').contains("Rembourser");
    });

    /**
     * Send notification for refund
     */
    it('Send refund notification', function () {
        cy.log("sendNotification");
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderCartId + "(");
        cy.openNotificationOnHipayBO(124).then(() => {
            let basketTransaction = utils.fetchInput("basket", decodeURI(this.data));
            assert.equal(basketTransaction, JSON.stringify(this.basket.wrappingGiftCapture));
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    it('check refund status', function () {
        cy.logAndGoToDetailOrder(this.order.lastOrderId);
        cy.checkRefundStatusMessage();
    });
});
