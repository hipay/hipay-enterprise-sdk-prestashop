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

    I.click('a.btn-continue');

    I.click('//a[@href="#payment_form"]');
    I.selectOption('#operating_mode', opModes[paymentMode]);
    I.click('//button[@name="submitGlobalPaymentMethods"]');
});

Given('Je veux activer le One-click', () => {
    I.amOnPage('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');

    I.click('a.btn-continue');

    I.click('//a[@href="#payment_form"]');
    I.click('//label[@for="card_token_switchmode_on"]');
    I.click('//button[@name="submitGlobalPaymentMethods"]');
});

Given('Je veux payer en carte {string}', async (paymentMethod) => {
    I.amOnPage('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');

    I.click('a.btn-continue');

    I.click('//a[@href="#payment_form"]');
    I.click('//a[@href="#' + paymentMethod.toLowerCase() + '"]');
    I.click('//label[@for="' + paymentMethod.toLowerCase() + '_activated_on"]');
    I.click('//button[@name="creditCardSubmit"]');
});

Given('Je veux payer avec le mode de paiement {string}', (paymentMethod) => {
    I.amOnPage('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');

    I.click('a.btn-continue');

    I.click('//a[@href="#payment_form"]');
    I.click('//a[@href="#' + paymentMethod.toLowerCase() + '"]');
    I.click('//label[@for="' + paymentMethod.toLowerCase() + '_activated_on"]');
    I.fillField('//input[@name="' + paymentMethod.toLowerCase() + '_minAmount[EUR]"]', '0');
    I.clearField('//input[@name="' + paymentMethod.toLowerCase() + '_maxAmount[EUR]"]');

    I.click('//button[@name="localPaymentSubmit"]');
});

Given("J'active le 3DS {string}", (mode) => {
    I.amOnPage('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');

    I.click('a.btn-continue');
    I.click('//a[@href="#payment_form"]');

    I.selectOption('#activate_3d_secure', mode);
    I.click('//button[@name="submitGlobalPaymentMethods"]');
});


Given('Je supprime tous les comptes utilisateurs', async () => {
    I.amOnPage('/admin-hipay/index.php/sell/customers/');
    I.click('a.btn-outline-danger');

    const customerExists = await I.checkIfVisible('#customer_id_customer');
    if (customerExists) {
        I.click('//input[@id="customer_grid_bulk_action_select_all"]/../../label');
        I.click('//button[contains(text(), "Actions groupées")]');
        I.click('#customer_grid_bulk_action_delete_selection');
        I.click('//div[@id="customer_grid_delete_customers_modal"]//button[contains(text(), "Supprimer")]');
    }
});

Given('Je veux payer en capture {string}', (captureMode) => {
    let captureModeList = JSON.parse(fs.readFileSync('./fixtures/enum/CaptureMode.json', 'utf8'));

    I.amOnPage('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');

    I.click('a.btn-continue');

    I.click('//a[@href="#payment_form"]');
    I.selectOption('#capture_mode', captureModeList[captureMode]);
    I.click('//button[@name="submitGlobalPaymentMethods"]');
});