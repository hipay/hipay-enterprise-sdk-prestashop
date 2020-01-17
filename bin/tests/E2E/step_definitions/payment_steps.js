const { I } = inject();
const getCardDatas = require("@hipay/hipay-cypress-utils/utils/get-card-datas");
const urlModule = require('url');
const querystring = require('querystring');
const fs = require('fs');

When('Je remplis les informations de facturation', () => {
    let customer = JSON.parse(fs.readFileSync('./fixtures/customerFR.json', 'utf8'));

    I.fillField('//*[@id="customer-form"]//input[@name="firstname"]', customer.firstName);
    I.fillField('//*[@id="customer-form"]//input[@name="lastname"]', customer.lastName);
    I.fillField('//*[@id="customer-form"]//input[@name="email"]', customer.email);
    I.click('//*[@id="customer-form"]//input[@name="psgdpr"]');

    I.click('//*[@id="customer-form"]//button[@name="continue"]');
});

When('Je remplis les informations d\'expédition', () => {
    let customer = JSON.parse(fs.readFileSync('./fixtures/customerFR.json', 'utf8'));

    I.fillField('//*[@id="delivery-address"]//input[@name="address1"]', customer.streetAddress);
    I.fillField('//*[@id="delivery-address"]//input[@name="postcode"]', customer.zipCode);
    I.fillField('//*[@id="delivery-address"]//input[@name="city"]', customer.city);
    I.selectOption('//*[@id="delivery-address"]//select[@name="id_country"]', customer.country);
    if (customer.state !== undefined) {
        I.selectOption('#billing_state', customer.state);
    }

    I.click('//*[@id="delivery-address"]//button[@name="confirm-addresses"]');
});

When('Je sélectionne un mode d\'expédition', () => {
    I.click('#delivery_option_1');
    I.click('//*[@id="js-delivery"]//button[@name="confirmDeliveryOption"]')
});

When('Je sélectionne le mode de paiement {string}', (paymentMethod) => {
    I.click('Payer par ' + paymentMethod);
});

When('Je rentre mes informations de paiement pour la carte {string}', (cardCode) => {
    let cardData = getCardDatas(cardCode + "_ok");

    I.fillField('#card-number', cardData.cardNumber);
    I.selectOption('#expiry-month', cardData.expiryMonth);
    I.selectOption('#expiry-year', cardData.expiryYear.substr(2));
    I.fillField('#cvc', cardData.cvc);
});

When('Je paye', () => {
    I.click('#conditions-to-approve input');
    I.click('#payment-confirmation button');
});

Then('La commande est en succès', () => {
    I.seeInCurrentUrl('controller=order-confirmation');
});

When('Je sauvegarde le numéro de commande dans {string}', async (valName) => {
    let url = await I.grabCurrentUrl();
    await I.setValue(valName, {
        lastOrderId: urlModule.parse(url, true).query.id_order,
        lastOrderCartId: urlModule.parse(url, true).query.id_cart
    });
});