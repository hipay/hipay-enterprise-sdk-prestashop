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

/**
 * HiPay Order status manager
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HiPayOrderStatus
{

    /**
     * Add HiPay order status
     * @return boolean
     */
    public static function updateHiPayOrderStates($module)
    {
        $hipayStates = self::getOrderStatusList($module);

        foreach ($hipayStates as $name => $state) {
            $waiting_state_config = $name;
            $waiting_state_color = $state["waiting_state_color"];
            $waiting_state_names = array();

            $setup = $state["setup"];

            foreach (Language::getLanguages(false) as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $waiting_state_names[(int)$language['id_lang']] = $state["name_FR"];
                } else {
                    $waiting_state_names[(int)$language['id_lang']] = $state["name_EN"];
                }
            }

            self::saveOrderState($waiting_state_config, $waiting_state_color, $waiting_state_names, $setup, $module);
        }

        return true;
    }

    /**
     * save new order status
     * @param type $config
     * @param type $color
     * @param type $names
     * @param type $setup
     * @return boolean
     */
    private static function saveOrderState($config, $color, $names, $setup, $module)
    {
        $state_id = Configuration::get($config);

        if ((bool)$state_id == true) {
            $order_state = new OrderState($state_id);
        } else {
            $order_state = new OrderState();
        }

        $order_state->name = $names;
        $order_state->color = $color;

        foreach ($setup as $param => $value) {
            $order_state->{$param} = $value;
        }

        if ((bool)$state_id == true) {
            return $order_state->save();
        } elseif ($order_state->add() == true) {
            Configuration::updateValue($config, $order_state->id);
            @copy(
                $module->getLocalPath() . 'views/img/logo-16.png',
                _PS_ORDER_STATE_IMG_DIR_ . (int)$order_state->id . '.gif'
            );

            return true;
        }
        return false;
    }

    /**
     * Return list of all HiPay order status
     * @param type $module
     * @return array
     */
    private static function getOrderStatusList($module)
    {
        $hipayStates = array(
            "HIPAY_OS_PENDING" => array(
                "waiting_state_color" => "#4169E1",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                ),
                "name_FR" => "En attente d'autorisation (HiPay)",
                "name_EN" => "Waiting for authorization (HiPay)",
            ),
            "HIPAY_OS_MOTO_PENDING" => array(
                "waiting_state_color" => "#4169E1",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                ),
                "name_FR" => "En attente de paiement MO/TO (HiPay)",
                "name_EN" => "Waiting for MO/TO payment (HiPay)",
            ),
            "HIPAY_OS_EXPIRED" => array(
                "waiting_state_color" => "#8f0621",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                ),
                "name_FR" => "Expiré (HiPay)",
                "name_EN" => "Expired (HiPay)",
            ),
            "HIPAY_OS_CHALLENGED" => array(
                "waiting_state_color" => "#4169E1",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                ),
                "name_FR" => "Contesté (HiPay) ",
                "name_EN" => "Challenged (HiPay)",
            ),
            "HIPAY_OS_AUTHORIZED" => array(
                "waiting_state_color" => "LimeGreen",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                ),
                "name_FR" => "Paiement autorisé (HiPay)",
                "name_EN" => "Payment authorized (HiPay)",
            ),
            "HIPAY_OS_CAPTURE_REQUESTED" => array(
                "waiting_state_color" => "LimeGreen",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                ),
                "name_FR" => "Capture demandée (HiPay)",
                "name_EN" => "Capture requested (HiPay)",
            ),
            "HIPAY_OS_CAPTURED" => array(
                "waiting_state_color" => "LimeGreen",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                ),
                "name_FR" => "Capturé (HiPay)",
                "name_EN" => "Captured (HiPay)",
            ),
            "HIPAY_OS_PARTIALLY_CAPTURED" => array(
                "waiting_state_color" => "LimeGreen",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Capture partielle (HiPay)",
                "name_EN" => "partially captured (HiPay)",
            ),
            "HIPAY_OS_REFUND_REQUESTED" => array(
                "waiting_state_color" => "#ec2e15",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Remboursement demandé (HiPay)",
                "name_EN" => "Refund requested (HiPay)",
            ),
            "HIPAY_OS_REFUNDED_PARTIALLY" => array(
                "waiting_state_color" => "HotPink",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Remboursé Partiellement (HiPay)",
                "name_EN" => "Refunded Partially (HiPay)",
            ),
            "HIPAY_OS_REFUNDED" => array(
                "waiting_state_color" => "HotPink",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => true,
                    'paid' => false,
                    'template' => 'refund'
                ),
                "name_FR" => "Remboursé (HiPay)",
                "name_EN" => "Refunded (HiPay)",
            ),
            "HIPAY_OS_DENIED" => array(
                "waiting_state_color" => "#8f0621",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Refusé (HiPay)",
                "name_EN" => "Denied (HiPay)",
            ),
            "HIPAY_OS_CHARGEDBACK" => array(
                "waiting_state_color" => "#f89406",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $module->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Charged back (HiPay)",
                "name_EN" => "Charged back (HiPay)",
            )
        );

        return $hipayStates;
    }
}
