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
hiPayInputControl.forms = [];

/**
 *
 * @param {type} newElement
 * @param {type} targetElement
 * @returns {undefined}
 */
function HiPay_insertAfter(newElement, targetElement) {
  // target is what you want it to go after. Look for this elements parent.
  var parent = targetElement.parentNode;

  // if the parents lastchild is the targetElement...
  if (parent.lastChild === targetElement) {
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
function HiPay_removeElementsByClass(className) {
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
function HiPay_hasClass(el, className) {
  if (el.classList) {
    return el.classList.contains(className);
  } else {
    return !!el.className.match(new RegExp("(\\s|^)" + className + "(\\s|$)"));
  }
}

/**
 *
 * @param {type} el
 * @param {type} className
 * @returns {undefined}
 */
function HiPay_addClass(el, className) {
  if (el.classList) {
    el.classList.add(className);
  } else if (!HiPay_hasClass(el, className)) {
    el.className += " " + className;
  }
}

/**
 *
 * @param {type} el
 * @param {type} className
 * @returns {undefined}
 */
function HiPay_removeClass(el, className) {
  if (el.classList) {
    el.classList.remove(className);
  } else if (HiPay_hasClass(el, className)) {
    var reg = new RegExp("(\\s|^)" + className + "(\\s|$)");
    el.className = el.className.replace(reg, " ");
  }
}

/**
 *
 * @param {type} text
 * @returns {pInsert|Element}
 */
function HiPay_generateElement(text) {
  var pInsert = document.createElement("span");
  pInsert.textContent = text;
  HiPay_addClass(pInsert, "error-text-hp");

  return pInsert;
}

/**
 *
 * @param {type} element
 * @param {type} text
 * @returns {undefined}
 */
function HiPay_errorMessage(element, text) {
  HiPay_addClass(element, "error-input-hp");
  HiPay_insertAfter(HiPay_generateElement(text), element);
}

/**
 * validation algorithms
 */

var HiPay_validIBAN = (function () {
  // use an IIFE
  // A "constant" lookup table of IBAN lengths per country
  // (the funky formatting is just to make it fit better in the answer here on CR)
  var CODE_LENGTHS = {
    AD: 24,
    AE: 23,
    AT: 20,
    AZ: 28,
    BA: 20,
    BE: 16,
    BG: 22,
    BH: 22,
    BR: 29,
    CH: 21,
    CR: 21,
    CY: 28,
    CZ: 24,
    DE: 22,
    DK: 18,
    DO: 28,
    EE: 20,
    ES: 24,
    FI: 18,
    FO: 18,
    FR: 27,
    GB: 22,
    GI: 23,
    GL: 18,
    GR: 27,
    GT: 28,
    HR: 21,
    HU: 28,
    IE: 22,
    IL: 23,
    IS: 26,
    IT: 27,
    JO: 30,
    KW: 30,
    KZ: 20,
    LB: 28,
    LI: 21,
    LT: 20,
    LU: 20,
    LV: 21,
    MC: 27,
    MD: 24,
    ME: 22,
    MK: 19,
    MR: 27,
    MT: 31,
    MU: 30,
    NL: 18,
    NO: 15,
    PK: 24,
    PL: 28,
    PS: 29,
    PT: 25,
    QA: 29,
    RO: 24,
    RS: 22,
    SA: 24,
    SE: 24,
    SI: 19,
    SK: 24,
    SM: 27,
    TN: 24,
    TR: 26
  };

  // piece-wise HiPay_mod97 using 9 digit "chunks", as per Wikipedia's example:
  // http://en.wikipedia.org/wiki/International_Bank_Account_Number#Modulo_operation_on_IBAN
  function HiPay_mod97(string) {
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
    var iban = String(input)
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, ""), // keep only alphanumeric characters
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
    return HiPay_mod97(digits) === 1;
  };
})();

/**
 *
 * @param {type} value
 * @returns {Boolean}
 */
function HiPay_isCardNumberValid(value) {
  // accept only digits, dashes or spaces
  if (/[^0-9-\s]+/.test(value)) {
    return false;
  }

  // The Luhn Algorithm. It's so pretty.
  var nCheck = 0,
    nDigit = 0,
    bEven = false;
  value = value.replace(/\D/g, "");

  for (var n = value.length - 1; n >= 0; n--) {
    var cDigit = value.charAt(n);
    nDigit = parseInt(cDigit, 10);

    if (bEven) {
      if ((nDigit *= 2) > 9) {
        nDigit -= 9;
      }
    }

    nCheck += nDigit;
    bEven = !bEven;
  }

  return nCheck % 10 === 0;
}

/**
 *
 * @param {type} value
 * @returns {unresolved}
 */
function HiPay_isCPFValid(value) {
  return value.match(
    /(\d{2}[.]?\d{3}[.]?\d{3}[\/]?\d{4}[-]?\d{2})|(\d{3}[.]?\d{3}[.]?\d{3}[-]?\d{2})$/
  );
}

/**
 *
 * @param {type} value
 * @returns {unresolved}
 */
function HiPay_isCPNCURPValid(value) {
  return value.match(/^[a-zA-Z]{4}\d{6}[a-zA-Z]{6}\d{2}$/);
}

/**
 * Checks if the phone number is valid
 * At the moment it is only able and used to validate portuguese phone numbers
 * @param value
 * @return boolean
 */
function HiPay_isPhoneValid(value) {
  return value.match(/^1(1[2578]|2([09]80|3(45|)|5(3?0|5)|7[67])|414|6(200|(80|91)\d|99[015679])|8(28|91))|(2([1-8]\d|9[136])|30\d|7(0[789]|60)|80[08]|9([136]\d|[124-7]))(\d{6})$/);
}

function HiPay_validBic(value) {
  return value.match(/^[a-z]{6}[2-9a-z][0-9a-np-z]([a-z0-9]{3}|x{3})?$/i);
}

/**
 *
 * @param price
 * @returns {Number|*}
 */
function HiPay_normalizePrice(price) {
  price = parseFloat(price.replace(/,/g, "."));

  if (isNaN(price) || price === "") {
    price = 0;
  }

  return price;
}

/**
 *
 * @param {type} element
 * @returns {Boolean}
 */
function HiPay_checkNotEmptyField(element) {
  if (element.value === null || element.value === "") {
    HiPay_errorMessage(element, i18nFieldIsMandatory);
    return false;
  }

  return true;
}

/**
 *
 * @param {type} element
 * @returns {Boolean}
 */
function HiPay_checkIban(element) {
  if (!HiPay_checkNotEmptyField(element)) {
    return false;
  }

  if (!HiPay_validIBAN(element.value)) {
    HiPay_errorMessage(element, i18nBadIban);
    return false;
  }
  return true;
}

/**
 *
 * @param {type} element
 * @returns {Boolean}
 */
function HiPay_checkBic(element) {
  if (!HiPay_checkNotEmptyField(element)) {
    return false;
  }

  if (!HiPay_validBic(element.value)) {
    HiPay_errorMessage(element, i18nBadBic);
    return false;
  }
  return true;
}

/**
 *
 * @param {type} element
 * @returns {Boolean}
 */
function HiPay_checkCCNumber(element) {
  if (!HiPay_checkNotEmptyField(element)) {
    return false;
  }

  if (!HiPay_isCardNumberValid(element.value)) {
    HiPay_errorMessage(element, i18nBadCC);
    return false;
  }
  return true;
}

/**
 *
 * @param {type} element
 * @returns {Boolean}
 */
function HiPay_checkCVC(element) {
  var myCard = Jquery(".card-js");

  return !(myCard.CardJs("cardType") !== "Bcmc" &&
    myCard.CardJs("cardType") !== "Maestro" &&
    !HiPay_checkNotEmptyField(element));
}

/**
 *
 * @param {type} element
 * @returns {Boolean}
 */
function HiPay_checkCPF(element) {
  if (!HiPay_checkNotEmptyField(element)) {
    return false;
  }

  if (!HiPay_isCPFValid(element.value)) {
    HiPay_errorMessage(element, i18nBadCPF);
    return false;
  }
  return true;
}

/**
 *
 * @param {type} element
 * @returns {Boolean}
 */
function HiPay_checkCPNCURP(element) {
  if (!HiPay_checkNotEmptyField(element)) {
    return false;
  }

  if (!HiPay_isCPNCURPValid(element.value)) {
    HiPay_errorMessage(element, i18nBadCPNCURP);
    return false;
  }
  return true;
}

/**
 * Checks if the phone number is valid
 * @param element
 */
function HiPay_checkPhone(element) {
  if (!HiPay_checkNotEmptyField(element)) {
    return false;
  }

  if (!HiPay_isPhoneValid(element.value)) {
    HiPay_errorMessage(element, i18nBadPhone);
    return false;
  }
  return true;
}

/**
 *
 * @param {type} input
 * @returns {Boolean}
 */
function HiPay_typeControlCheck(input) {
  var element = document.getElementById(input.field);
  HiPay_removeClass(element, "error-input-hp");

  switch (input.type) {
    case "iban":
      return HiPay_checkIban(element);
    case "bic":
      return HiPay_checkBic(element);
    case "creditcardnumber":
      return HiPay_checkCCNumber(element);
    case "cvc":
      return HiPay_checkCVC(element);
    case "cpf":
      return HiPay_checkCPF(element);
    case "curp-cpn":
      return HiPay_checkCPNCURP(element);
    case "phone":
      return HiPay_checkPhone(element);
    default:
      return HiPay_checkNotEmptyField(element);
  }
}

/**
 *
 * @param {type} form
 * @returns {success|Boolean}
 */
function HiPay_checkControl(form) {
  var success = true;
  if (hiPayInputControl.forms[form]) {
    HiPay_removeElementsByClass("error-text-hp");
    hiPayInputControl.forms[form].fields.forEach(function (input) {
      success = HiPay_typeControlCheck(input) && success;
    });
  }

  return success;
}

/**
 *
 * @returns {HiPay_Form}
 */
function HiPay_Form() {
  this.fields = [];
}

/**
 *
 * @param {type} field
 * @param {type} type
 * @param {type} required
 * @returns {HiPay_Input}
 */
function HiPay_Input(field, type, required) {
  this.field = field;
  this.type = type;
  this.required = required;
}

/**
 *
 * @param {type} form
 * @param {type} field
 * @param {type} type
 * @param {type} required
 * @returns {undefined}
 */
function HiPay_addInput(form, field, type, required) {
  if (!hiPayInputControl.forms[form]) {
    hiPayInputControl.forms[form] = new HiPay_Form();
  }
  hiPayInputControl.forms[form].fields.push(new HiPay_Input(field, type, required));
}

hiPayInputControl.HiPay_checkControl = HiPay_checkControl;
hiPayInputControl.HiPay_addInput = HiPay_addInput;
hiPayInputControl.HiPay_normalizePrice = HiPay_normalizePrice;
