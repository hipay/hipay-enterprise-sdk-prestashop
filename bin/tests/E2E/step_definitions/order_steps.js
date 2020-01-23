const { I } = inject();

When("J'ouvre la commande {string}", async (varName) => {
    let orderId = await I.getValue(varName);
    orderId = orderId.lastOrderId;
    I.amOnPage('/admin-hipay/index.php?controller=AdminOrders&id_order=' + orderId + '&vieworder');
    I.click('a.btn-continue');
});

When('Je capture complètement la commande', () => {
    let captureStyle = "complete";
    I.selectOption('#hipay_capture_type', captureStyle);

    I.click('//button[@name="hipay_capture_basket_submit"]');
    I.acceptPopup();
});

When('Je capture partiellement {string} objets la commande', (nObj) => {
    let captureStyle = "partial";
    I.selectOption('#hipay_capture_type', captureStyle);
    I.fillField('#good-selector-1', nObj);

    I.click('//button[@name="hipay_capture_basket_submit"]');
    I.acceptPopup();
});

Then('La notification {string} est reçue', (notifCode) => {
    I.see(notifCode, '.message-item-text');
});

Then("La commande est à l'état {string}", (statusText) => {
    I.see(statusText, '#id_order_state_chosen a.chosen-single span');
});


Then("Aucune action HiPay n'est disponible", () => {
    I.see('Aucune action disponible');
});

Then("L'action HiPay {string} est disponible", (action) => {
    I.seeElement('//button[contains(text(), ' + action + ')]');
});