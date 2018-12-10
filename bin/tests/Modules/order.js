/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

var id = 0,
    reference = 0,
    cartId = 0;

function setReference(pending) {

    if (pending)
        reference = casper.fetchText(x('//p[contains(., "Order #")]')).split('#')[1];
    else {
        var text = casper.fetchText(x('//li[contains(., "Référence de la commande : ")]')).split(':')[1];
        reference = text.substring(1, text.length - 1);
    }
    casper.echo("Order Référence : " + reference, "COMMENT");
}

function getReference() {

    return reference;
}

function setId() {
    id = utilsHiPay.getHttpGetData('id_order');
    casper.echo("Order Id : " + id, "COMMENT");
}

function getId() {
    return id;
}

function setCartId() {
    cartId = utilsHiPay.getHttpGetData('id_cart');
    casper.echo("Cart Id : " + cartId, "COMMENT");
}

function getCartId() {
    return cartId;
}

module.exports = {
    setReference: setReference,
    getReference: getReference,
    setId: setId,
    getId: getId,
    setCartId: setCartId,
    getCartId: getCartId
};
