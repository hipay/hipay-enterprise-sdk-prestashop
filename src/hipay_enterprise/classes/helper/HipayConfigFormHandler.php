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

require_once(dirname(__FILE__) . '/enums/OperatingMode.php');

/**
 * Handle config form data savings
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayConfigFormHandler
{

    /**
     * HipayConfigFormHandler constructor.
     *
     * @param $module_instance
     */
    public function __construct($module_instance)
    {
        $this->context = Context::getContext();
        $this->module = $module_instance;
    }

    /**
     * Save Account informations send by config page form
     *
     * @return bool
     * */
    public function saveAccountInformations()
    {
        $this->module->logs->logInfos('# SaveAccountInformations');

        try {
            // saving all array "account" in $configHipay
            $accountConfig = array("global" => array(), "sandbox" => array(), "production" => array());

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

            foreach ($this->module->hipayConfigTool->getAccountGlobal() as $key => $value) {
                $fieldValue = Tools::getValue($key);
                $accountConfig["global"][$key] = $fieldValue;
            }

            foreach ($this->module->hipayConfigTool->getAccountSandbox() as $key => $value) {
                if (($key == "api_username_sandbox" &&
                        Tools::getValue("api_username_sandbox") &&
                        !Tools::getValue("api_password_sandbox")) ||
                    ($key == "api_password_sandbox" &&
                        Tools::getValue("api_password_sandbox") &&
                        !Tools::getValue("api_username_sandbox"))
                ) {
                    $this->module->_errors[] = $this->module->l(
                        "If sandbox api username is filled sandbox api password is mandatory"
                    );
                    return false;
                } elseif (($key == "api_tokenjs_username_sandbox" &&
                        Tools::getValue("api_tokenjs_username_sandbox") &&
                        !Tools::getValue("api_tokenjs_password_publickey_sandbox")) ||
                    ($key == "api_tokenjs_password_publickey_sandbox" &&
                        Tools::getValue("api_tokenjs_password_publickey_sandbox") &&
                        !Tools::getValue("api_tokenjs_username_sandbox"))
                ) {
                    $this->module->_errors[] = $this->module->l(
                        "If sandbox api TokenJS username is filled sandbox api TokenJS password is mandatory"
                    );
                    return false;
                } elseif (($key == "api_moto_username_sandbox" &&
                        Tools::getValue("api_moto_username_sandbox") &&
                        !Tools::getValue("api_moto_password_sandbox"))
                    ||
                    ($key == "api_moto_password_sandbox" &&
                        Tools::getValue("api_moto_password_sandbox") &&
                        !Tools::getValue(
                            "api_moto_username_sandbox"
                        ))
                ) {
                    $this->module->_errors[] = $this->module->l(
                        "If sandbox api MO/TO username is filled sandbox api MO/TO password is mandatory"
                    );
                    return false;
                } else {
                    if ($key == "api_secret_passphrase_sandbox" || $key == "api_moto_secret_passphrase_sandbox") {
                        $fieldValue = HipayHelper::getValue($key);
                        $accountConfig["sandbox"][$key] = $fieldValue;
                    } else {
                        $fieldValue = Tools::getValue($key);
                        $accountConfig["sandbox"][$key] = $fieldValue;
                    }
                }
            }

            foreach ($this->module->hipayConfigTool->getAccountProduction() as $key => $value) {
                if (($key == "api_username_production" &&
                        Tools::getValue("api_username_production") &&
                        !Tools::getValue("api_password_production"))
                    ||
                    ($key == "api_password_production" &&
                        Tools::getValue("api_password_production") &&
                        !Tools::getValue("api_username_production"))
                ) {
                    $this->module->_errors[] = $this->module->l(
                        "If production api username is filled production api password is mandatory"
                    );
                    return false;
                } elseif (($key == "api_tokenjs_username_production" &&
                        Tools::getValue("api_tokenjs_username_production") &&
                        !Tools::getValue("api_tokenjs_password_publickey_production")) ||
                    ($key == "api_tokenjs_password_publickey_production" &&
                        Tools::getValue("api_tokenjs_password_publickey_production") &&
                        !Tools::getValue("api_tokenjs_username_production"))
                ) {
                    $this->module->_errors[] = $this->module->l(
                        "If production api TokenJS username is filled production api TokenJS password is mandatory"
                    );
                    return false;
                } elseif (($key == "api_moto_username_production" &&
                        Tools::getValue("api_moto_username_production") &&
                        !Tools::getValue("api_moto_password_production"))
                    ||
                    ($key == "api_moto_password_production" &&
                        Tools::getValue("api_moto_password_production") &&
                        !Tools::getValue("api_moto_username_production"))
                ) {
                    $this->module->_errors[] = $this->module->l(
                        "If production api MO/TO username is filled production api MO/TO password is mandatory"
                    );
                    return false;
                } else {
                    if ($key == "api_secret_passphrase_production" || $key == "api_moto_secret_passphrase_production") {
                        $fieldValue = HipayHelper::getValue($key);
                        $accountConfig["production"][$key] = $fieldValue;
                    } else {
                        $fieldValue = Tools::getValue($key);
                        $accountConfig["production"][$key] = $fieldValue;
                    }
                }
            }

            $accountConfig["hash_algorithm"] = $this->module->hipayConfigTool->getHashAlgorithm();

            //save configuration
            $this->module->hipayConfigTool->setConfigHiPay("account", $accountConfig);

            $this->module->_successes[] = $this->module->l('Module settings saved successfully.');
            $this->module->logs->logInfos($this->module->hipayConfigTool->getConfigHipay());

            return true;
        } catch (Exception $e) {
            // LOGS
            $this->module->logs->logException($e);
            $this->module->_errors[] = $this->module->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save Global payment informations send by config page form
     *
     * @return bool
     * */
    public function saveGlobalPaymentInformations()
    {
        $this->module->logs->logInfos("# saveGlobalPaymentInformations");

        try {
            // saving all array "payemnt" "global" in $configHipay
            $accountConfig = array(
                "global" => array(),
                // Not cool but works
                "credit_card" => $this->module->hipayConfigTool->getPaymentCreditCard(),
                "local_payment" => $this->module->hipayConfigTool->getLocalPayment()
            );

            //requirement : input name in tpl must be the same that name of indexes in $this->module->configHipay

            foreach ($this->module->hipayConfigTool->getPaymentGlobal() as $key => $value) {
                if (is_bool(Tools::getValue($key)) && !Tools::getValue($key)) {
                    $fieldValue = $value;
                } elseif ($key == "css_url" &&
                    Tools::getValue("css_url") &&
                    !HipayFormControl::checkHttpsUrl(Tools::getValue("css_url"))
                ) {
                    $this->module->_errors[] = $this->module->l("CSS url needs to be a valid https url.");
                    return false;
                } elseif ($key == "operating_mode" &&
                    Tools::getValue("operating_mode")
                ) {
                    $fieldValue = OperatingMode::getOperatingMode(Tools::getValue("operating_mode"));
                } else {
                    $fieldValue = Tools::getValue($key);
                }

                $this->module->logs->logInfos($key . " => " . print_r($fieldValue, true));
                $accountConfig["global"][$key] = $fieldValue;
            }
            $conf3d = $this->save3DSecureInformations();
            $accountConfig["global"]["activate_3d_secure"] = $conf3d["global"]["activate_3d_secure"];
            $accountConfig["global"]["3d_secure_rules"] = $conf3d["global"]["3d_secure_rules"];

            //save configuration
            $this->module->hipayConfigTool->setConfigHiPay("payment", $accountConfig);

            $this->module->_successes[] = $this->module->l('Global payment method settings saved successfully.');
            $this->module->logs->logInfos($this->module->hipayConfigTool->getConfigHipay());
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->module->logs->logException($e);
            $this->module->_errors[] = $this->module->l($e->getMessage());
        }

        return false;
    }

    /**
     * save credit cards settings form
     *
     * @return boolean
     */
    public function saveCreditCardInformations($context)
    {
        $this->module->logs->logInfos("# SaveCreditCardInformations");

        try {
            // saving all array "payemnt" "credit_card" in $configHipay
            $accountConfig = array(
                "global" => $this->module->hipayConfigTool->getPaymentGlobal(),
                "credit_card" => array(),
                "local_payment" => $this->module->hipayConfigTool->getLocalPayment()
            );

            $keySaved = array(
                "activated",
                "currencies",
                "countries",
                "minAmount",
                "maxAmount"
            );

            if (Tools::getValue("ccDisplayName")) {
                $accountConfig["global"]["ccDisplayName"] = Tools::getValue("ccDisplayName");
            }
            if (Tools::getValue("ccFrontPosition")) {
                $accountConfig["global"]["ccFrontPosition"] = Tools::getValue("ccFrontPosition");
            }

            //requirement : input name in tpl must be the same that name of indexes in $this->module->configHipay

            foreach ($this->module->hipayConfigTool->getPaymentCreditCard() as $card => $conf) {
                foreach ($conf as $key => $value) {
                    if (in_array($key, $keySaved)) {
                        $fieldValue = Tools::getValue($card . "_" . $key);
                        if ($key == "currencies") {
                            foreach (Tools::getValue($card . "_" . $key) as $currency) {
                                if (!in_array($currency, $this->module->moduleCurrencies)) {
                                    $this->module->db->setCurrencies($this->module->id, $context->shop->id, $currency);
                                }
                            }
                        }
                    } else {
                        $fieldValue = $this->module->hipayConfigTool->getPaymentCreditCard()[$card][$key];
                    }

                    $accountConfig["credit_card"][$card][$key] = $fieldValue;
                }
            }
            //save configuration
            $this->module->hipayConfigTool->setConfigHiPay("payment", $accountConfig);

            $this->module->_successes[] = $this->module->l('Credit card settings saved successfully.');
            $this->module->logs->logInfos($this->module->hipayConfigTool->getConfigHipay());
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->module->logs->logException($e);
            $this->module->_errors[] = $this->module->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save local payment form
     *
     * @return boolean
     */
    public function saveLocalPaymentInformations($context)
    {
        $this->module->logs->logInfos("# SaveLocalPaymentInformations");
        try {
            // saving all array "payemnt" "local_payment" in $configHipay
            $accountConfig = array(
                "global" => $this->module->hipayConfigTool->getPaymentGlobal(),
                "credit_card" => $this->module->hipayConfigTool->getPaymentCreditCard(),
                "local_payment" => array()
            );

            $keySavedBase = array(
                "activated",
                "currencies",
                "countries",
                "minAmount",
                "maxAmount",
                "displayName",
                "frontPosition"
            );

            foreach ($this->module->hipayConfigTool->getLocalPayment() as $card => $conf) {
                $keySaved = array_merge($conf["displayConfigurationFields"], $keySavedBase);
                foreach ($conf as $key => $value) {
                    //prevent specific fields from being updated
                    if (in_array($key, $keySaved)) {
                        $fieldValue = Tools::getValue($card . "_" . $key);
                        if ($key == "currencies") {
                            foreach (Tools::getValue($card . "_" . $key) as $currency) {
                                if (!in_array($currency, $this->module->moduleCurrencies)) {
                                    $this->module->db->setCurrencies($this->module->id, $context->shop->id, $currency);
                                }
                            }
                        }
                    } else {
                        $fieldValue = $this->module->hipayConfigTool->getLocalPayment()[$card][$key];
                    }
                    $accountConfig["local_payment"][$card][$key] = $fieldValue;
                }
            }
            //save configuration
            $this->module->hipayConfigTool->setConfigHiPay("payment", $accountConfig);

            $this->module->_successes[] = $this->module->l('Local payment settings saved successfully.');
            $this->module->logs->logInfos($this->module->hipayConfigTool->getConfigHipay());
            return true;
        } catch (Exception $e) {
            $this->module->logs->logException($e);
            $this->module->_errors[] = $this->module->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save fraud settings
     *
     * @return boolean
     */
    public function saveFraudInformations()
    {
        $this->module->logs->logInfos("# SaveFraudInformations");

        try {
            if (!Validate::isEmail(Tools::getValue('send_payment_fraud_email_copy_to'))) {
                $this->module->_errors[] = $this->module->l('The Copy To Email is not valid.');
                return false;
            } else {
                // saving all array "fraud" in $configHipay
                $accountConfig = array();

                //requirement : input name in tpl must be the same that name of indexes in $this->configHipay
                foreach ($this->module->hipayConfigTool->getFraud() as $key => $value) {
                    $fieldValue = Tools::getValue($key);
                    $accountConfig[$key] = $fieldValue;
                }

                //save configuration
                $this->module->hipayConfigTool->setConfigHiPay("fraud", $accountConfig);

                $this->module->_successes[] = $this->module->l('Fraud settings saved successfully.');
                $this->module->logs->logInfos($this->module->hipayConfigTool->getConfigHipay());
                return true;
            }
        } catch (Exception $e) {
            $this->module->logs->logException($e);
            $this->module->_errors[] = $this->module->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save Category Mapping informations send by config page form
     *
     * @return bool
     */
    public function saveCategoryMappingInformations()
    {
        $this->module->logs->logInfos('# saveCategoryMappingInformations');
        try {
            $psCategories = $this->module->mapper->getPrestashopCategories();
            $mapping = array();
            foreach ($psCategories as $cat) {
                $psMapCat = Tools::getValue('ps_map_' . $cat["id_category"]);
                $hipayMapCat = Tools::getValue('hipay_map_' . $cat["id_category"]);

                if ($this->module->mapper->hipayCategoryExist($hipayMapCat)) {
                    $mapping[] = array("pscat" => $psMapCat, "hipaycat" => $hipayMapCat);
                }
            }
            if (!empty($this->module->_errors)) {
                $this->module->_errors = array(end($this->module->_errors));
                return false;
            }

            $this->module->mapper->setMapping(HipayMapper::HIPAY_CAT_MAPPING, $mapping);

            $this->module->_successes[] = $this->module->l('Category mapping configuration saved successfully.');
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->module->logs->logException($e);
            $this->module->_errors[] = $this->module->l($e->getMessage());
        }
        return false;
    }

    /**
     * Save Carrier Mapping informations send by config page form
     *
     * @return bool
     */
    public function saveCarrierMappingInformations()
    {
        $this->module->logs->logInfos('# SaveCarrierMappingInformations');

        try {
            $psCarriers = $this->module->mapper->getPrestashopCarriers();

            $mapping = array();
            $this->module->_errors = array();
            foreach ($psCarriers as $car) {
                $psMapCar = Tools::getValue('ps_map_' . $car["id_carrier"]);
                $hipayMapCarMode = Tools::getValue('hipay_map_mode_' . $car["id_carrier"]);
                $hipayMapCarShipping = Tools::getValue('hipay_map_shipping_' . $car["id_carrier"]);
                $hipayMapCarOETA = Tools::getValue('ps_map_prep_eta_' . $car["id_carrier"]);
                $hipayMapCarDETA = Tools::getValue('ps_map__delivery_eta_' . $car["id_carrier"]);

                $mapping[] = array(
                    "pscar" => $psMapCar,
                    "hipaycarmode" => $hipayMapCarMode,
                    "hipaycarshipping" => $hipayMapCarShipping,
                    "prepeta" => $hipayMapCarOETA,
                    "deliveryeta" => $hipayMapCarDETA
                );
            }

            if (!empty($this->module->_errors)) {
                $this->module->_errors = array(end($this->module->_errors));
                return false;
            }

            $this->module->mapper->setMapping(HipayMapper::HIPAY_CARRIER_MAPPING, $mapping);
            $this->module->_successes[] = $this->module->l('Carrier mapping configuration saved successfully.');

            return true;
        } catch (Exception $e) {
            // LOGS
            $this->module->logs->logException($e);
            $this->module->_errors[] = $this->module->l($e->getMessage());
        }

        return false;
    }

    private function save3DSecureInformations()
    {
        $this->module->logs->logInfos('# save3DSecureInformations');

        try {
            $accountConfig = array("global" => array());
            $accountConfig["global"]["activate_3d_secure"] = Tools::getValue("activate_3d_secure");
            $accountConfig["global"]["3d_secure_rules"] = array();

            foreach (Tools::getValue("3d_secure_rules") as $rule) {
                $newRules = array(
                    "field" => $rule["field"],
                    "operator" => htmlentities($rule["operator"]),
                    "value" => $rule["value"]
                );

                $accountConfig["global"]["3d_secure_rules"][] = $newRules;
            }

            return $accountConfig;
        } catch (Exception $e) {
            // LOGS
            $this->module->logs->logErrors($e->getMessage());
            $this->module->_errors[] = $this->module->l($e->getMessage());
        }
    }
}
