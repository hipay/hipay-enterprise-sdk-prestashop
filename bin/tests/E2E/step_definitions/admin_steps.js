const urlModule = require('url');
const querystring = require('querystring');
const fs = require('fs');

const { I } = inject();

// Add in your custom step files

Given('Je me connecte au panneau d\'administration', () => {
    I.amOnPage('/admin-hipay');
    I.fillField('#email', 'demo@hipay.com');
    I.fillField('#passwd', 'hipay123');
    I.click('#submit_login');
    I.waitForVisible('#header_logo', 5);
});

Given('Je veux payer en mode {string}', async (paymentMode) => {
    let opModes = JSON.parse(fs.readFileSync('./fixtures/enum/UXMode.json', 'utf8'));

    I.amOnPage('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');

    I.clickIfVisible('a.btn-continue');

    I.click('//a[@href="#payment_form"]');
    I.selectOption('#operating_mode', opModes[paymentMode]);
    I.click('//button[@name="submitGlobalPaymentMethods"]');
});

Given('Je veux payer en carte {string}', async (paymentMethod) => {
    I.amOnPage('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');

    I.clickIfVisible('a.btn-continue');

    I.click('//a[@href="#payment_form"]');
    I.click('//a[@href="#' + paymentMethod.toLowerCase() + '"]');
    I.click('//label[@for="' + paymentMethod.toLowerCase() + '_activated_on"]');
    I.click('//button[@name="creditCardSubmit"]');
});

When('J\'ouvre la commande {string}', async (varName) => {
    let orderId = await I.getValue(varName);
    orderId = orderId.lastOrderId;
    I.amOnPage('/admin-hipay/index.php?controller=AdminOrders&id_order=' + orderId + '&vieworder');
    I.clickIfVisible('a.btn-continue');
});

Then('La notification {string} est reçue', (notifCode) => {
    I.see(notifCode, '.message-item-text');
});

Then('La commande est à l\'état {string}', (statusText) => {
    I.see(statusText, '#id_order_state_chosen a.chosen-single span');
});