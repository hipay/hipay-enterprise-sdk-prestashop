const { I } = inject();
const signatureHeader = "X-ALLOPASS-SIGNATURE";
const notifURL = '/index.php?fc=module&module=hipay_enterprise&controller=notify';

When('Je me connecte au BO HiPay', () => {
    I.amOnPage('https://stage-merchant.hipay-tpp.com/default/auth/login');
    I.fillField('#email-address', 'ogone.dev.test@gmail.com');
    I.fillField('#password', 'testcasperjs');
    I.click('#submit-login');
});

When('J\'ouvre la transaction pour la commande {string}', async(varName) => {
    let orderIdObject = await I.getValue(varName);
    let orderId = orderIdObject.lastOrderCartId;
    I.click('.nav-transactions');
    I.click('#submitbutton');
    I.click('//span[contains(@data-original-title, "' + orderId + '(")]/../..//a[@data-original-title="View transaction details"]');
});

When('J\'envoie la notification {string}', async (notifCode) => {
    I.click('//a[@href="#payment-notification"]');
    I.click('//div[@id="payment-notification-container"]//span[contains(text(), "' + notifCode + '")]/../..//a[@class="details-notification"]');

    I.waitForText('transaction_reference', 5, '.copy-transaction-message-textarea');

    let hashCode = await I.grabTextFrom('//table[contains(@class, "list-fraud-review")]//pre[contains(text(), "Hash")]');
    hashCode = hashCode.match(/Hash: (.*)/m)[1];
    await I.setValue('notifHash', hashCode);

    let notifBody = await I.grabHTMLFrom('//textarea[@id="copy-transaction-message"]');
    notifBody = notifBody.replace(/&amp;/gi, '&');
    await I.setValue('notifBody', notifBody);

    I.click('#fsmodal .close-modal');

    I.sendPostRequest(notifURL, notifBody, {
        [signatureHeader]: hashCode
    });
});