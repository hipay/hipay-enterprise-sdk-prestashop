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
 * Class AdminHiPaySynchronizeHashingController
 *
 * Manage synchronization for Hashing Algorithm with Hipay Backend
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AdminHiPaySynchronizeHashingController extends ModuleAdminController
{

    const CODE_MESSAGE = "message";

    const CODE_STATUS = "status";

    /**
     * AdminHiPaySynchronizeHashingController constructor.
     */
    public function __construct()
    {
        $this->module = 'hipay_enterprise';
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();

        $this->apiHandler = new ApiHandler($this->module, $this->context);
    }

    /**
     * Synchronize Hashing from each platform
     *
     * @throws GatewayException
     */
    public function displayAjaxSynchronizeHashing()
    {
        $configHash = $this->module->hipayConfigTool->getHashAlgorithm();
        foreach (HipayHelper::$platforms as $platform) {
            $labelPlatform = HipayHelper::getLabelForPlatform($platform);

            if (HipayHelper::existCredentialForPlateform($this->module, $platform)) {
                $messages = array();

                try {
                    $hashing = ApiCaller::getSecuritySettings($this->module, $platform);

                    if ($configHash[$platform] == $hashing->getHashingAlgorithm()) {
                        $messages[$platform][self::CODE_STATUS] = "success";
                        $messages[$platform][self::CODE_MESSAGE] = sprintf(
                            $this->module->l('Hash Algorithm for %s was already set with %s'),
                            $labelPlatform,
                            $hashing->getHashingAlgorithm()
                        );
                    } else {
                        $configHash[$platform] = $hashing->getHashingAlgorithm();
                        $this->module->hipayConfigTool->setHashAlgorithm($configHash);
                        $messages[$platform][self::CODE_STATUS] = "success";
                        $messages[$platform][self::CODE_MESSAGE] = sprintf(
                            $this->module->l('Hash Algorithm for %s has been syncrhonize with %s'),
                            $labelPlatform,
                            $hashing->getHashingAlgorithm()
                        );
                        $messages[$platform]["value"] = $hashing->getHashingAlgorithm();
                    }
                } catch (Exception $e) {
                    $messages[$platform][self::CODE_STATUS] = "error";
                    $messages[$platform][self::CODE_MESSAGE] = sprintf(
                        $this->module->l('An error occurred for %s : %s'),
                        $labelPlatform,
                        $e->getMessage()
                    );
                }
            } else {
                $messages[$platform][self::CODE_STATUS] = "error";
                $messages[$platform][self::CODE_MESSAGE] = sprintf(
                    $this->module->l('Hash Algorithm for %s has not been updated : You must filled credentials.'),
                    $labelPlatform
                );
            }
        }

        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        die(Tools::jsonEncode($messages));
    }
}
