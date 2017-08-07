<?php
/**
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.wallet@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
 */
require_once(dirname(__FILE__).'/../apiHandler/ApiHandler.php');

class HipayConfig
{
    const THREE_D_S_DISABLED           = 0;
    const THREE_D_S_TRY_ENABLE_ALL     = 1;
    const THREE_D_S_TRY_ENABLE_RULES   = 2;
    const THREE_D_S_FORCE_ENABLE_RULES = 3;
    const THREE_D_S_FORCE_ENABLE_ALL   = 4;

    private $jsonFilesPath;
    private $context;
    private $configHipay = array();

    public function __construct($module_instance)
    {
        $this->context       = Context::getContext();
        $this->module        = $module_instance;
        $this->jsonFilesPath = dirname(__FILE__)."/../paymentConfigFiles/";
    }

    /**
     * return config array
     * @return type
     */
    public function getConfigHipay()
    {
        if (empty($this->configHipay)) {
            $this->initConfigHiPay();
        }

        return $this->configHipay;
    }

    /**
     *  save a specific key of the module config
     * @param: string $key
     * @param: mixed $value
     * @return : bool
     * */
    public function setConfigHiPay($key, $value)
    {
        // Use this function only if you have just one variable to update
        // init multistore
        $id_shop       = (int) $this->context->shop->id;
        $id_shop_group = (int) Shop::getContextShopGroupID();

        // the config is stacked in JSON
        $confHipay = Tools::jsonDecode(
                Configuration::get(
                    'HIPAY_CONFIG',
                    null,
                    $id_shop_group,
                    $id_shop
                ),
                true
        );

        $confHipay[$key] = $value;
        //$confHipay = array_replace_recursive($confHipay,$value);
        if (Configuration::updateValue(
                'HIPAY_CONFIG',
                Tools::jsonEncode($confHipay),
                false,
                $id_shop_group,
                $id_shop
            )
        ) {
            $this->configHipay = Tools::jsonDecode(
                    Configuration::get(
                        'HIPAY_CONFIG',
                        null,
                        $id_shop_group,
                        $id_shop
                    ),
                    true
            );
            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * Functions to init the configuration HiPay
     */
    private function initConfigHiPay()
    {
        // init multistore
        $id_shop           = (int) $this->context->shop->id;
        $id_shop_group     = (int) Shop::getContextShopGroupID();
        $this->configHipay = Tools::jsonDecode(
                Configuration::get(
                    'HIPAY_CONFIG',
                    null,
                    $id_shop_group,
                    $id_shop
                ),
                true
        );

        // if config exist but empty, init new object for configHipay
        if (!$this->configHipay || empty($this->configHipay)) {
            $this->insertConfigHiPay();
        }
    }

    /**
     * init module configuration
     * @return : bool
     */
    private function insertConfigHiPay()
    {
        $configFields                             = array(
            "account" => array(
                "global" => array(
                    "sandbox_mode" => 1,
                    "host_proxy" => "",
                    "host_proxy" => "",
                    "port_proxy" => "",
                    "user_proxy" => "",
                    "password_proxy" => ""
                ),
                "sandbox" => array(
                    "api_username_sandbox" => "",
                    "api_password_sandbox" => "",
                    "api_tokenjs_username_sandbox" => "",
                    "api_tokenjs_password_publickey_sandbox" => "",
                    "api_secret_passphrase_sandbox" => "",
                    "api_moto_username_sandbox" => "",
                    "api_moto_password_sandbox" => "",
                    "api_moto_secret_passphrase_sandbox" => ""
                ),
                "production" => array(
                    "api_username_production" => "",
                    "api_password_production" => "",
                    "api_tokenjs_username_production" => "",
                    "api_tokenjs_password_publickey_production" => "",
                    "api_secret_passphrase_production" => "",
                    "api_moto_username_production" => "",
                    "api_moto_password_production" => "",
                    "api_moto_secret_passphrase_production" => ""
                )
            ),
            "payment" => array(
                "global" => array(
                    "operating_mode" => Apihandler::DIRECTPOST,
                    "iframe_hosted_page_template" => "basic-js",
                    "display_card_selector" => 0,
                    "display_hosted_page" => "redirect",
                    "css_url" => "",
                    "activate_3d_secure" => HipayConfig::THREE_D_S_DISABLED,
                    "3d_secure_rules" => array(
                        array(
                            "field" => "total_price",
                            "operator" => ">",
                            "value" => 100,
                        )
                    ),
                    "capture_mode" => "automatic",
                    "card_token" => 0,
                    "electronic_signature" => 1,
                    "activate_basket" => 1,
                    "log_infos" => 1,
                    "regenerate_cart_on_decline" => 1,
                    "ccDisplayName" => "Credit card"
                ),
                "credit_card" => array(),
                "local_payment" => array()
            ),
            "fraud" => array(
                "payment_fraud_email_sender" => (string) Configuration::get('PS_SHOP_EMAIL'),
                "send_payment_fraud_email_copy_to" => "",
                "send_payment_fraud_email_copy_method" => "bcc"
            )
        );
        $configFields["payment"]["credit_card"]   = $this->insertPaymentsConfig("creditCard/");
        $configFields["payment"]["local_payment"] = $this->insertPaymentsConfig("local/");


        return $this->setAllConfigHiPay($configFields);
    }

    /**
     * Save initial module config
     * @param : array $arrayHipay
     *
     * @return : bool
     * */
    private function setAllConfigHiPay($arrayHipay = null)
    {
        // use this function if you have a few variables to update
        if ($arrayHipay != null) {
            $for_json_hipay = $arrayHipay;
        } else {
            $for_json_hipay = $this->configHipay;
        }

        // init multistore
        $id_shop       = (int) $this->context->shop->id;
        $id_shop_group = (int) Shop::getContextShopGroupID();
        if (Configuration::updateValue(
                'HIPAY_CONFIG',
                Tools::jsonEncode($for_json_hipay),
                false,
                $id_shop_group,
                $id_shop
            )
        ) {
            $this->configHipay = Tools::jsonDecode(
                    Configuration::get(
                        'HIPAY_CONFIG',
                        null,
                        $id_shop_group,
                        $id_shop
                    ),
                    true
            );


            $this->module->getLogs()->logInfos(
                print_r(
                    $this->configHipay,
                    true
                )
            );

            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * init local config
     * @return array
     */
    private function insertPaymentsConfig($folderName)
    {
        $creditCard = array();

        $files = scandir($this->jsonFilesPath.$folderName);

        foreach ($files as $file) {
            $creditCard = array_merge(
                $creditCard,
                $this->addPaymentConfig(
                    $file,
                    $folderName
                )
            );
        }

        return $creditCard;
    }

    /**
     * add specific payment config from JSON file
     * @param type $file
     * @return type
     */
    private function addPaymentConfig(
    $file, $folderName
    )
    {
        $creditCard = array();

        if (preg_match(
                '/(.*)\.json/',
                $file
            ) == 1
        ) {
            $json                      = Tools::jsonDecode(
                    Tools::file_get_contents($this->jsonFilesPath.$folderName.$file),
                    true
            );
            $creditCard[$json["name"]] = $json["config"];
        }

        return $creditCard;
    }
}