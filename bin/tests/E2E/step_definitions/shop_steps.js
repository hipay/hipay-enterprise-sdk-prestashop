const { I } = inject();
const fs = require('fs');

When("J'ajoute {string} T-shirt à mon panier", (qty) => {
    I.amOnPage('/index.php?id_product=1&rewrite=hummingbird-printed-t-shirt&controller=product#/1-taille-s/8-couleur-blanc');
    I.fillField('#quantity_wanted', qty);
    I.click('button.add-to-cart');
    I.waitForText('Produit ajouté au panier avec succès', 5);
    I.amOnPage('/index.php');
});

When('Je valide mon panier', (qty) => {
    I.amOnPage('/index.php?controller=order');
});


Given("Je m'enregistre sur la boutique", () => {
    let customer = JSON.parse(fs.readFileSync('./fixtures/customerFR.json', 'utf8'));

    I.amOnPage('/index.php?controller=authentication&create_account=1');
    I.fillField('//input[@name="firstname"]', customer.firstName);
    I.fillField('//input[@name="lastname"]', customer.lastName);
    I.fillField('//input[@name="email"]', customer.registeredEmail);
    I.fillField('//input[@name="password"]', customer.password);
    I.click('//input[@name="psgdpr"]');
    I.click('//button[@data-link-action="save-customer"]');
});

