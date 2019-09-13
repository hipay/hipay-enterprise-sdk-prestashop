/**
 * Log to Prestashop Admin
 */
Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/admin-hipay');
    cy.get('#email').type("demo@hipay.com");
    cy.get('#passwd').type('hipay123');
    cy.get('#submit_login').click();

    cy.get('#header_logo');
});

Cypress.Commands.add("adminLogOut", () => {
    cy.visit('/admin-hipay');
    cy.get('#header_logout').click({force: true});
});

Cypress.Commands.add("goToHipayModuleAdmin", () => {
    cy.visit('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');
    cy.get('.btn-continue').click();
});

Cypress.Commands.add("goToHipayModulePaymentMethodAdmin", () => {
    cy.get('a[aria-controls="payment_form"]').click();
});

Cypress.Commands.add("resetProductionConfigForm", () => {
    cy.get('input[name="api_username_production"]').clear({force: true});
    cy.get('input[name="api_password_production"]').clear({force: true});
    cy.get('input[name="api_secret_passphrase_production"]').clear({force: true});
    cy.get('input[name="api_tokenjs_username_production"]').clear({force: true});
    cy.get('input[name="api_tokenjs_password_publickey_production"]').clear({force: true});
    cy.get('input[name="api_moto_username_production"]').clear({force: true});
    cy.get('input[name="api_moto_password_production"]').clear({force: true});
    cy.get('input[name="api_moto_secret_passphrase_production"]').clear({force: true});
});

/**
 *  Activate Payment Methods
 */
Cypress.Commands.add("activateLocalPaymentMethods", (method) => {
    cy.get('#' + method + '_activated_on').then(($input) => {
        if ($input.attr('checked') === undefined) {
            $input.click();
        }
    });
    cy.get('#local_payment_form > .panel-footer > .col-md-12 > .pull-right').click();
});

Cypress.Commands.add("activateOneClick", (method) => {
    cy.get('#card_token_switchmode_on').then(($input) => {
        if ($input.attr('checked') === undefined) {
            $input.click();
        }
    });
    cy.get('#panel-global-settings > form:nth-child(1) > div:nth-child(2) > button:nth-child(2)').click();
});


/**
 *  Activate Basket
 */
Cypress.Commands.add("activateBasket", () => {
    cy.get('#activate_basket_switchmode_on').then(($input) => {
        if ($input.attr('checked') === undefined) {
            $input.click();
        }
    });
});

Cypress.Commands.add("importCountry", (country) => {
    cy.visit('/admin-hipay/index.php/improve/international/localization/');
    cy.get('.btn-outline-danger').click();

    cy.get("#import_localization_pack_iso_localization_pack").select(country, {force: true});
    cy.get("form[name='import_localization_pack'] .card-footer .btn").click();
});

/**
 *  Connect and go to detail of an order
 */
Cypress.Commands.add("logAndGoToDetailOrder", (lastOrderId) => {
    cy.logToAdmin();
    cy.goToDetailOrder(lastOrderId);
});

/**
 * Go to Order detail
 */
Cypress.Commands.add("goToDetailOrder", (id) => {
    cy.visit('/admin-hipay/index.php?controller=AdminOrders&id_order=' + id + '&vieworder');
    cy.get('.btn-continue').click();
});

Cypress.Commands.add("activateWrappingGift", () => {
    cy.visit('/admin-hipay/index.php/configure/shop/order-preferences/');
    cy.get('.btn-outline-danger').click();

    cy.get('#form_gift_options_enable_gift_wrapping_1').then(($input) => {
        if ($input.attr('checked') === undefined) {
            $input.click();
        }
    });

    cy.setWrappingGiftPrice(10);

    cy.get(':nth-child(1) > .card > .card-footer > .d-flex > .btn').click();
});

Cypress.Commands.add("setWrappingGiftPrice", (price) => {
    cy.get("#form_gift_options_gift_wrapping_price").clear();
    cy.get("#form_gift_options_gift_wrapping_price").type(price);
});

Cypress.Commands.add("getLastOrderRequest", () => {
    cy.getAllRequests().then((requests) => {
        requests.reverse();
        for (let request of requests) {
            if (request.orderid) {
                return request;
            }
        }

        return null;
    });
});

Cypress.Commands.add("getOrderRequest", (orderId) => {
    let goodOrder = null;
    let regex = new RegExp(orderId + ".*");
    cy.getAllRequests().then((requests) => {
        for (let request of requests) {
            if (request.orderid && request.orderid.match(regex)) {
                goodOrder = request;
            }
        }

        return goodOrder;
    });
});


Cypress.Commands.add("getAllRequests", () => {
    cy.goToHipayModuleAdmin();

    cy.get('*[href="#logs"]').click();
    cy.get('#logs > div:nth-child(1) > div:nth-child(2) > div:nth-child(1) > div:nth-child(1) > a:nth-child(2)')
        .invoke('removeAttr', 'target').click();
    cy.get('pre')
        .invoke('text')
        .then((text) => {
            let rawLogArray = text.split(/^20[0-9]{2}\/[0-9]{2}\/[0-9]{2} - [0-9]{2}:[0-9]{2}:[0-9]{2}: /gm);
            let logArray = [];
            for (let rawLog of rawLogArray) {
                if (rawLog !== "") {
                    let logJson = rawLog.replace(/\\/gm, '\\\\"')
                        .replace(/"/gm, '\\"')
                        .replace(/ => /gm, '": "')
                        .replace(/^\s*\[(.*)\]": /gm, '"$1": ')
                        .replace(/(.)$/gm, '$1",')
                        .replace(/\s*"?Array",\s*\(",/gm, '{')
                        .replace(/^\s*\)",\s*$/gm, '},')
                        .replace(/,\s*}/gm, '}')
                    logJson = logJson.substr(0, logJson.length - 1);

                    let log = JSON.parse(logJson);
                    logArray.push(log);
                }
            }

            cy.log(logArray).then(() => {
                return logArray;
            });
        });
});

Cypress.Commands.add("deleteClients", () => {
    cy.visit("/admin-hipay/index.php?controller=AdminCustomers");

    cy.get('body').then(($body) => {
        // synchronously query from body
        // to find which element was created
        if ($body.find('#customer_grid_bulk_action_select_all').length) {
            cy.get(".js-bulk-action-checkbox").click({force: true, multiple: true});
            cy.wait(2000);
            cy.get("#customer_grid_bulk_action_delete_selection").click({force: true});

            cy.get('.js-submit-delete-customers').click();
        } else {
            cy.log("No user found");
            return;
        }
    });
});

Cypress.Commands.add("changeProductStock", (productId, stock, preorderDate) => {
    cy.visit("/admin-hipay/index.php/sell/catalog/products/" + productId);
    cy.get('.btn-outline-danger').click();
    cy.get('#tab_step3').click();

    cy.get('body').then(($body) => {
        // synchronously query from body
        // to find which element was created
        if ($body.find('#form_step3_qty_0').is(':visible')) {
            cy.get('#form_step3_qty_0').clear().type(stock);

            if(preorderDate !== undefined) {
                cy.get('#form_step3_out_of_stock_1').click();
                cy.get('#form_step3_available_date').clear().type(preorderDate);
            } else {
                cy.get('#form_step3_available_date').clear();
            }
        } else {
            cy.get('.attribute-quantity input').each(($input, idx, $inputs) => {
                cy.wrap($input).clear().type(stock);
            });
        }

        cy.get('.product-footer button.js-btn-save').click({force: true});
        cy.get('#growls-default div').should('be.visible');
    });
});