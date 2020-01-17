const { I } = inject();

When('J\'ajoute {string} T-shirt à mon panier', (qty) => {
    I.amOnPage('/index.php?id_product=1&rewrite=hummingbird-printed-t-shirt&controller=product#/1-taille-s/8-couleur-blanc');
    I.fillField('#quantity_wanted', qty);
    I.click('button.add-to-cart');
    I.amOnPage('/index.php');
});

When('Je valide mon panier', (qty) => {
    I.amOnPage('/index.php?controller=order');
});
