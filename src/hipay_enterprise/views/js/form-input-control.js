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

var hiPayInputControl = {};

hiPayInputControl.checkControl = checkControl;
hiPayInputControl.addInput = addInput;
hiPayInputControl.normalizePrice = normalizePrice;
hiPayInputControl.forms = [];

/**
 * 
 * @param {type} form
 * @returns {success|Boolean}
 */
function checkControl(form) {

    success = true;
    if (hiPayInputControl.forms[form]) {
        removeElementsByClass('error-text-hp');
        hiPayInputControl.forms[form].fields.forEach(function (input) {
            success = typeControlCheck(input) && success;
        })
    }

    return success;
}

/**
 * 
 * @param {type} form
 * @param {type} field
 * @param {type} type
 * @param {type} required
 * @returns {undefined}
 */
function addInput(form, field, type, required) {
    if (!hiPayInputControl.forms[form]) {
        hiPayInputControl.forms[form] = new Form();
    }
    hiPayInputControl.forms[form].fields.push(new Input(field, type, required));
}

/**
 * 
 * @returns {Form}
 */
function Form() {
    this.fields = [];
}

/**
 * 
 * @param {type} field
 * @param {type} type
 * @param {type} required
 * @returns {Input}
 */
function Input(field, type, required) {
    this.field = field;
    this.type = type;
    this.required = required;
}

/**
 * 
 * @param {type} input
 * @returns {Boolean}
 */
function typeControlCheck(input) {
    element = document.getElementById(input.field);
    removeClass(element, 'error-input-hp');

    switch (input.type) {
        case 'iban':
            return checkIban(element);
        case 'bic':
            return checkBic(element);
        case 'creditcardnumber':
            return checkCCNumber(element);
        case 'cpf':
            return checkCPF(element);
        case 'curp-cpn':
            return checkCPNCURP(element);
        default :
            return checkNotEmptyField(element);
    }
}

/**
 * 
 * @param {type} element
 * @returns {Boolean}
 */
function checkNotEmptyField(element) {

    if (element.value == null || element.value == "") {
        errorMessage(element, 'Field is mandatory');
        return false;
    }

    return true;
}

/**
 * 
 * @param {type} element
 * @returns {Boolean}
 */
function checkIban(element) {

    if (!checkNotEmptyField(element)) {
        return false;
    }

    if (!validIBAN(element.value)) {
        errorMessage(element, "This is not a correct IBAN");
        return false;
    }
    return true;
}

/**
 * 
 * @param {type} element
 * @returns {Boolean}
 */
function checkBic(element) {

    if (!checkNotEmptyField(element)) {
        return false;
    }

    if (!validBic(element.value)) {
        errorMessage(element, "This is not a correct BIC");
        return false;
    }
    return true;
}

/**
 * 
 * @param {type} element
 * @returns {Boolean}
 */
function checkCCNumber(element) {

    if (!checkNotEmptyField(element)) {
        return false;
    }

    if (!isCardNumberValid(element.value)) {
        errorMessage(element, "This is not a correct credit card number");
        return false;
    }
    return true;
}

/**
 * 
 * @param {type} element
 * @returns {Boolean}
 */
function checkCPF(element) {

    if (!checkNotEmptyField(element)) {
        return false;
    }

    if (!isCPFValid(element.value)) {
        errorMessage(element, "This is not a correct CPF");
        return false;
    }
    return true;
}

/**
 * 
 * @param {type} element
 * @returns {Boolean}
 */
function checkCPNCURP(element) {

    if (!checkNotEmptyField(element)) {
        return false;
    }

    if (!isCPNCURPValid(element.value)) {
        errorMessage(element, "This is not a correct CPN/CURP");
        return false;
    }
    return true;
}

/**
 * 
 * @param {type} element
 * @param {type} text
 * @returns {undefined}
 */
function errorMessage(element, text) {
    addClass(element, 'error-input-hp');
    insertAfter(generateElement(text), element);
}

/**
 * 
 * @param {type} text
 * @returns {pInsert|Element}
 */
function generateElement(text) {
    pInsert = document.createElement('span');
    pInsert.textContent = text;
    addClass(pInsert, 'error-text-hp');

    return pInsert;
}

/**
 * 
 * @param {type} newElement
 * @param {type} targetElement
 * @returns {undefined}
 */
function insertAfter(newElement, targetElement) {
    // target is what you want it to go after. Look for this elements parent.
    var parent = targetElement.parentNode;

    // if the parents lastchild is the targetElement...
    if (parent.lastChild == targetElement) {
        // add the newElement after the target element.
        parent.appendChild(newElement);
    } else {
        // else the target has siblings, insert the new element between the target and it's next sibling.
        parent.insertBefore(newElement, targetElement.nextSibling);
    }
}

/**
 * 
 * @param {type} className
 * @returns {undefined}
 */
function removeElementsByClass(className) {
    var elements = document.getElementsByClassName(className);
    while (elements.length > 0) {
        elements[0].parentNode.removeChild(elements[0]);
    }
}

/**
 * 
 * @param {type} el
 * @param {type} className
 * @returns {Boolean}
 */
function hasClass(el, className) {
    if (el.classList)
        return el.classList.contains(className)
    else
        return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'))
}

/**
 * 
 * @param {type} el
 * @param {type} className
 * @returns {undefined}
 */
function addClass(el, className) {
    if (el.classList)
        el.classList.add(className)
    else if (!hasClass(el, className))
        el.className += " " + className
}

/**
 * 
 * @param {type} el
 * @param {type} className
 * @returns {undefined}
 */
function removeClass(el, className) {
    if (el.classList)
        el.classList.remove(className)
    else if (hasClass(el, className)) {
        var reg = new RegExp('(\\s|^)' + className + '(\\s|$)')
        el.className = el.className.replace(reg, ' ')
    }
}

/**
 * validation algorithms
 */

var validIBAN = (function () { // use an IIFE
    // A "constant" lookup table of IBAN lengths per country
    // (the funky formatting is just to make it fit better in the answer here on CR)
    var CODE_LENGTHS = {
        AD: 24, AE: 23, AT: 20, AZ: 28, BA: 20, BE: 16, BG: 22, BH: 22, BR: 29,
        CH: 21, CR: 21, CY: 28, CZ: 24, DE: 22, DK: 18, DO: 28, EE: 20, ES: 24,
        FI: 18, FO: 18, FR: 27, GB: 22, GI: 23, GL: 18, GR: 27, GT: 28, HR: 21,
        HU: 28, IE: 22, IL: 23, IS: 26, IT: 27, JO: 30, KW: 30, KZ: 20, LB: 28,
        LI: 21, LT: 20, LU: 20, LV: 21, MC: 27, MD: 24, ME: 22, MK: 19, MR: 27,
        MT: 31, MU: 30, NL: 18, NO: 15, PK: 24, PL: 28, PS: 29, PT: 25, QA: 29,
        RO: 24, RS: 22, SA: 24, SE: 24, SI: 19, SK: 24, SM: 27, TN: 24, TR: 26
    };

    // piece-wise mod97 using 9 digit "chunks", as per Wikipedia's example:
    // http://en.wikipedia.org/wiki/International_Bank_Account_Number#Modulo_operation_on_IBAN
    function mod97(string) {
        var checksum = string.slice(0, 2),
                fragment;

        for (var offset = 2; offset < string.length; offset += 7) {
            fragment = String(checksum) + string.substring(offset, offset + 7);
            checksum = parseInt(fragment, 10) % 97;
        }

        return checksum;
    }

    // return a function that does the actual work
    return function (input) {
        var iban = String(input).toUpperCase().replace(/[^A-Z0-9]/g, ''), // keep only alphanumeric characters
                code = iban.match(/^([A-Z]{2})(\d{2})([A-Z\d]+)$/), // match and capture (1) the country code, (2) the check digits, and (3) the rest
                digits;

        // check syntax and length
        if (!code || iban.length !== CODE_LENGTHS[code[1]]) {
            return false;
        }

        // rearrange country code and check digits, and convert chars to ints
        digits = (code[3] + code[1] + code[2]).replace(/[A-Z]/g, function (letter) {
            return letter.charCodeAt(0) - 55;
        });

        // final check
        return mod97(digits) === 1;
    };
}
());

/**
 * 
 * @param {type} value
 * @returns {Boolean}
 */
function isCardNumberValid(value) {
    // accept only digits, dashes or spaces
    if (/[^0-9-\s]+/.test(value))
        return false;

    // The Luhn Algorithm. It's so pretty.
    var nCheck = 0, nDigit = 0, bEven = false;
    value = value.replace(/\D/g, "");

    for (var n = value.length - 1; n >= 0; n--) {
        var cDigit = value.charAt(n),
                nDigit = parseInt(cDigit, 10);

        if (bEven) {
            if ((nDigit *= 2) > 9)
                nDigit -= 9;
        }

        nCheck += nDigit;
        bEven = !bEven;
    }

    return (nCheck % 10) == 0;
}

/**
 * 
 * @param {type} value
 * @returns {unresolved}
 */
function isCPFValid(value) {
    return value.match(/(\d{2}[.]?\d{3}[.]?\d{3}[\/]?\d{4}[-]?\d{2})|(\d{3}[.]?\d{3}[.]?\d{3}[-]?\d{2})$/);
}

/**
 * 
 * @param {type} value
 * @returns {unresolved}
 */
function isCPNCURPValid(value) {
    return value.match(/^[a-zA-Z]{4}\d{6}[a-zA-Z]{6}\d{2}$/);
}

function validBic(value){
    return value.match(/^[a-z]{6}[2-9a-z][0-9a-np-z]([a-z0-9]{3}|x{3})?$/i);
}

/**
 *
 * @param price
 * @returns {Number|*}
 */
function normalizePrice (price) {
    price = parseFloat(price.replace(/,/g, '.'));

    if (isNaN(price) || price === '') {
        price = 0;
    }

    return price;
}