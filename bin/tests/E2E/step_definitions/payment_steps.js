const { I } = inject();
const getCardDatas = require("@hipay/hipay-cypress-utils/utils/get-card-datas");
const getLocalMethodData = require("@hipay/hipay-cypress-utils/utils/get-local-method-data");
const urlModule = require('url');
const fs = require('fs');

When('Je remplis les informations de facturation', () => {
    let customer = JSON.parse(fs.readFileSync('./fixtures/customerFR.json', 'utf8'));

    I.fillField('//*[@id="customer-form"]//input[@name="firstname"]', customer.firstName);
    I.fillField('//*[@id="customer-form"]//input[@name="lastname"]', customer.lastName);
    I.fillField('//*[@id="customer-form"]//input[@name="email"]', customer.email);
    I.click('//*[@id="customer-form"]//input[@name="psgdpr"]');

    I.click('//*[@id="customer-form"]//button[@name="continue"]');
});

When('Je remplis les informations d\'expédition', async () => {
    let customer = JSON.parse(fs.readFileSync('./fixtures/customerFR.json', 'utf8'));

    const adressAlreadyEntered = await I.checkIfVisible('a[data-link-action="edit-address"]');
    if (adressAlreadyEntered) {
        I.click('a[data-link-action="edit-address"]');
    }

    I.fillField('//*[@id="delivery-address"]//input[@name="address1"]', customer.streetAddress);
    I.fillField('//*[@id="delivery-address"]//input[@name="postcode"]', customer.zipCode);
    I.fillField('//*[@id="delivery-address"]//input[@name="city"]', customer.city);
    I.selectOption('//*[@id="delivery-address"]//select[@name="id_country"]', customer.country);
    if (customer.state !== undefined) {
        I.selectOption('#billing_state', customer.state);
    }

    I.click('//button[@name="confirm-addresses"]');
});

When('Je sélectionne un mode d\'expédition', () => {
    I.click('#delivery_option_1');
    I.click('//*[@id="js-delivery"]//button[@name="confirmDeliveryOption"]')
});

When('Je sélectionne le mode de paiement {string}', (paymentMethod) => {
    I.click('Payer par ' + paymentMethod);
});

When('Je rentre mes informations de paiement pour la carte {string} avec le 3DS {string}', (cardCode, mode) => {
    let suffix = "_ok";
    if(mode === "actif"){
        suffix = "_3DS";
    }

    let cardData = getCardDatas(cardCode + suffix);

    I.fillField('#card-number', cardData.cardNumber);
    I.selectOption('#expiry-month', cardData.expiryMonth);
    I.selectOption('#expiry-year', cardData.expiryYear.substr(2));
    I.fillField('#cvc', cardData.cvc);
});

When('Je rentre mes informations de paiement pour la carte {string} avec le 3DS {string} en mode hosted fields', async (cardCode, mode) => {
    let suffix = "_ok";
    if(mode === "actif"){
        suffix = "_3DS";
    }

    let cardData = getCardDatas(cardCode + suffix);

    await I.fillFieldInIFrame('iframe[id^="hipay-hosted-cardNumber"]', 'input[name="cardnumber"]', cardData.cardNumber);
    await I.fillFieldInIFrame('iframe[id^="hipay-hosted-expiryDate"]', 'input[name="cc-exp"]', cardData.expiryMonth + cardData.expiryYear.substr(2));
    await I.fillFieldInIFrame('iframe[id^="hipay-hosted-cvc"]', 'input[name="cvc"]', cardData.cvc);
});

When('Je décide de sauvegarder ma carte de crédit', () => {
    I.click('#saveTokenHipay');
});

When('Je paye', () => {
    I.click('#conditions-to-approve input');
    I.click('#payment-confirmation button');
});

When('Je paye la page Hosted Page', () => {
    I.click('#submit-button');
});

When('Je valide la transaction 3DS {string}', (state) => {
    if(state === "actif") {
        I.click('#Submit');
    }
});

When('Je rentre mes informations pour {string}', async (paymentMethod) => {
    let data = getLocalMethodData(paymentMethod.toLowerCase());

    switch(paymentMethod){
        case "Paypal":
            I.fillField('#email', data.data.login);

            // Paypal form has 2 formats, Login + Password || Login > Next > Password
            const password_field_visible = await I.checkIfVisible('#password');
            if (!password_field_visible) {
                I.click('#btnNext');
            }

            I.fillField('#password', data.data.password);
            I.click('#btnLogin');
            I.waitForNavigation();
            break;
    }
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

Then("La page de paiement {string} s'ouvre", (paymentMethod) => {
    let data = getLocalMethodData(paymentMethod.toLowerCase());

    if(data.hasOwnProperty("url")) {
        I.seeInCurrentUrl(data.url);
    } else {
        I.seeInCurrentUrl(paymentMethod.toLowerCase());
    }
});

Then("Je vois ma carte {string} avec le 3DS {string} sauvegardée sur l'écran", (cardCode, mode) => {
    let suffix = "_ok";
    if(mode === "actif"){
        suffix = "_3DS";
    }

    let cardData = getCardDatas(cardCode + suffix);

    I.see('Payer avec une carte de crédit sauvegardée', '//label[@for="radio-with-token"]');
    I.see(cardData.cardNumber.substring(0, 6) + '******' + cardData.cardNumber.slice(cardData.cardNumber.length - 4), '//label[@for="radio-with-token"]/span');
    I.see(cardData.expiryMonth + '/' + cardData.expiryYear, '//label[@for="radio-with-token"]/span');
});