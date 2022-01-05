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

var typeCC;

exports.setTypeCC = function (cc) {
    typeCC = cc;
};

exports.getTypeCC = function () {
    return typeCC;
};

/**
 *
 * @param test
 * @param file
 * @param new_typeCC
 */
exports.testOtherTypeCC = function testOtherTypeCC(test, file, new_typeCC) {
    casper.then(function () {
        this.echo("Configure Test other Type cc with " + new_typeCC + file, "INFO");
        if (new_typeCC && new_typeCC !== typeCC) {
            typeCC = new_typeCC;
            test.info("New type CC is configured and new test is injected");
            phantom.injectJs(pathHeader + file);
        } else if (this.cli.get('type-cc') === undefined) {
            if (typeCC === "visa") {
                typeCC = "mastercard";
                phantom.injectJs(pathHeader + file);
            }
            else {
                typeCC = "visa";
            }
        } else {
            typeCC = "visa";
        }
    });
};

exports.getHttpGetData = function getHttpGetData(param) {
    var vars = {};
    casper.getCurrentUrl().replace(
        /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
        function (m, key, value) { // callback
            vars[key] = value !== undefined ? value : '';
        }
    );

    if (param) {
        return vars[param] ? vars[param] : null;
    }
    return vars;
};
