/**
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.wallet@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
 */

var hiPayInputControl = {};

hiPayInputControl.checkControl = checkControl;
hiPayInputControl.addInput = addInput;
hiPayInputControl.forms = [];


function checkControl(form) {

    success = true;
    if (hiPayInputControl.forms[form]) {
        hiPayInputControl.forms[form].fields.forEach(function (input) {
            console.log(input);
            console.log(typeControlCheck(input));
            success = success && typeControlCheck(input);
        })
    }

    return success;
}

function addInput(form, field, type, required) {
    if (!hiPayInputControl.forms[form]) {
        hiPayInputControl.forms[form] = new Form();
    }
    hiPayInputControl.forms[form].fields.push(new Input(field, type, required));
}

function Form() {
    this.fields = [];
}

function Input(field, type, required) {
    this.field = field;
    this.type = type;
    this.required = required;
}

function typeControlCheck(input) {
    removeElementsByClass('error-text-hp');
    element = document.getElementById(input.field);
    removeClass(element, 'error-input-hp');

    switch (input.type) {
        case 'iban':
            return checkIban(element);
        default :
            return checkNotEmptyField(element);
    }
}

function checkNotEmptyField(element) {
    if (element.value == null || element.value == "") {
        errorMessage(element, 'Field is mandatory');
        return false;
    }

    return true;
}

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

function errorMessage(element, text) {
    addClass(element, 'error-input-hp');
    insertAfter(generateElement("Error : " + text), element);
}

function generateElement(text) {
    pInsert = document.createElement('p'); // create new textarea
    pInsert.textContent = text;
    addClass(pInsert, 'error-text-hp');

    return pInsert;
}


// create function, it expects 2 values.
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

function removeElementsByClass(className) {
    var elements = document.getElementsByClassName(className);
    while (elements.length > 0) {
        elements[0].parentNode.removeChild(elements[0]);
    }
}

function hasClass(el, className) {
    if (el.classList)
        return el.classList.contains(className)
    else
        return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'))
}

function addClass(el, className) {
    if (el.classList)
        el.classList.add(className)
    else if (!hasClass(el, className))
        el.className += " " + className
}

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