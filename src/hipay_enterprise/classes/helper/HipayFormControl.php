<?php
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

require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 * Form input control
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayFormControl
{

    /**
     *
     * @param type $fields
     * @param type $data
     * @param type $module
     * @return type
     */
    public static function checkPaymentForm($fields, $data, $module)
    {
        $errors = array();

        foreach ($data as $name => $value) {
            if (isset($fields[$name]['controlType'])) {
                HipayFormControl::typedFormControl($errors, $fields[$name]['controlType'], $value, $name, $module);
            }

            if (isset($fields[$name]['required']) && $fields[$name]['required']) {
                if (empty($value)) {
                    $errors[$name] = $module->l('Field is mandatory');
                }
            }
        }

        return $errors;
    }

    /**
     *
     * @param type $url
     * @return type
     */
    public static function checkHttpsUrl($url)
    {
        return preg_match('/(https)(:\/\/)(\S*?\.\S*?)([\s)\[\]{},;"\':<]|\.\s|$)/', $url);
    }

    /**
     *
     * @param type $errors
     * @param type $type
     * @param type $value
     * @param type $name
     * @param type $module
     */
    private static function typedFormControl(&$errors, $type, $value, $name, $module)
    {
        switch ($type) {
            case 'iban':
                if (!HipayFormControl::isValidIBAN($value)) {
                    $errors[$name] = $module->l('This is not a correct IBAN');
                }
                break;
            case 'bic':
                if (!HipayFormControl::isValidBIC($value)) {
                    $errors[$name] = $module->l('This is not a correct BIC');
                }
                break;
            case 'cpf':
                if (!HipayFormControl::isValidCPF($value)) {
                    $errors[$name] = $module->l('Error : This is not a correct CPF');
                }
                break;
            case 'curp-cpn':
                if (!HipayFormControl::isValidCPNCURP($value)) {
                    $errors[$name] = $module->l('Error : This is not a correct CURP/CPN');
                }
                break;
        }
    }

    /**
     *
     * @param type $value
     * @return type
     */
    private static function isValidCPF($value)
    {
        return preg_match("/(\d{2}[.]?\d{3}[.]?\d{3}[\/]?\d{4}[-]?\d{2})|(\d{3}[.]?\d{3}[.]?\d{3}[-]?\d{2})$/", $value);
    }

    /**
     *
     * @param type $value
     * @return type
     */
    private static function isValidCPNCURP($value)
    {
        return preg_match("/^[a-zA-Z]{4}\d{6}[a-zA-Z]{6}\d{2}$/", $value);
    }

    /**
     *
     * @param type $value
     * @return type
     */
    private static function isValidBIC($value)
    {
        return preg_match("/^[a-z]{6}[2-9a-z][0-9a-np-z]([a-z0-9]{3}|x{3})?$/i", $value);
    }

    /**
     *
     * @param type $iban
     * @return boolean
     */
    private static function isValidIBAN($iban)
    {

        $iban = Tools::strtolower($iban);
        $Countries = array(
            'al' => 28, 'ad' => 24, 'at' => 20, 'az' => 28, 'bh' => 22, 'be' => 16,
            'ba' => 20, 'br' => 29, 'bg' => 22, 'cr' => 21, 'hr' => 21, 'cy' => 28,
            'cz' => 24,
            'dk' => 18, 'do' => 28, 'ee' => 20, 'fo' => 18, 'fi' => 18, 'fr' => 27,
            'ge' => 22, 'de' => 22, 'gi' => 23, 'gr' => 27, 'gl' => 18, 'gt' => 28,
            'hu' => 28,
            'is' => 26, 'ie' => 22, 'il' => 23, 'it' => 27, 'jo' => 30, 'kz' => 20,
            'kw' => 30, 'lv' => 21, 'lb' => 28, 'li' => 21, 'lt' => 20, 'lu' => 20,
            'mk' => 19,
            'mt' => 31, 'mr' => 27, 'mu' => 30, 'mc' => 27, 'md' => 24, 'me' => 22,
            'nl' => 18, 'no' => 15, 'pk' => 24, 'ps' => 29, 'pl' => 28, 'pt' => 25,
            'qa' => 29,
            'ro' => 24, 'sm' => 27, 'sa' => 24, 'rs' => 22, 'sk' => 24, 'si' => 19,
            'es' => 24, 'se' => 24, 'ch' => 21, 'tn' => 24, 'tr' => 26, 'ae' => 23,
            'gb' => 22, 'vg' => 24
        );
        $Chars = array(
            'a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14, 'f' => 15, 'g' => 16,
            'h' => 17, 'i' => 18, 'j' => 19, 'k' => 20, 'l' => 21, 'm' => 22,
            'n' => 23, 'o' => 24, 'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29,
            'u' => 30, 'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35
        );

        if (empty($iban)) {
            return false;
        }

        if (!isset($Countries[Tools::substr($iban, 0, 2)])) {
            return false;
        }

        if (Tools::strlen($iban) != $Countries[Tools::substr($iban, 0, 2)]) {
            return false;
        }

        $MovedChar = Tools::substr($iban, 4) . Tools::substr($iban, 0, 4);
        $MovedCharArray = str_split($MovedChar);
        $NewString = "";

        foreach ($MovedCharArray as $k => $v) {
            if (!is_numeric($MovedCharArray[$k])) {
                $MovedCharArray[$k] = $Chars[$MovedCharArray[$k]];
            }
            $NewString .= $MovedCharArray[$k];
        }
        if (function_exists("bcmod")) {
            return bcmod($NewString, '97') == 1;
        }

        $x = $NewString;
        $y = "97";
        $take = 5;
        $mod = "";

        do {
            $a = (int)$mod . Tools::substr($x, 0, $take);
            $x = Tools::substr($x, $take);
            $mod = $a % $y;
        } while (Tools::strlen($x));

        return (int)$mod == 1;
    }
}
