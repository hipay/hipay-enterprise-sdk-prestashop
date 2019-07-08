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

require_once(dirname(__FILE__) . '/HipayHelper.php');

/**
 * Handle new versions notifications
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayUpdateNotif
{
    const HIPAY_GITHUB_PRESTASHOP_LATEST = "https://api.github.com/repos/hipay/hipay-enterprise-sdk-prestashop/releases/latest";

    /**
     * @var mixed $module Current module
     */
    private $module;

    /**
     * @var mixed $context Current context
     */
    private $context;

    /**
     * @var String $version Current module version
     */
    private $version;

    /**
     * @var String $newVersion Latest version available
     */
    private $newVersion;

    /**
     * @var String $readMeUrl URL targeting the latest version's ReadMe on GitHub
     */
    private $readMeUrl;

    /**
     * @var String $downloadUrl URL targeting the latest version's direct download on GitHub
     */
    private $downloadUrl;

    /**
     * HipayUpdateNotif constructor.
     * @param string $version
     * @throws Exception
     */
    public function __construct($module, $version)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->version = $version;

        // We read info from the saved configuration first, to have values even if GitHub doesn't answer properly
        $this->readFromConf();

        /*
         * GitHub limits calls over 60 per hour per IP
         * https://developer.github.com/v3/#rate-limiting
         *
         * Solution : max 1 call per hour
         */
        $lastCall = DateTime::createFromFormat('d/m/Y H:i:s', Configuration::get('HIPAY_UPDATE_NOTIF_LAST_CALL'));
        $curdate = new DateTime();

        /*
         * PT1H => Interval of 1 hour
         * https://www.php.net/manual/en/dateinterval.construct.php
         */
        if(!$lastCall || $lastCall->add(new DateInterval("PT1H")) < $curdate ) {
            // Headers to avoid 403 error from GitHub
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: PHP'
                    ]
                ]
            ];
            $context = stream_context_create($opts);
            $gitHubInfo = json_decode(file_get_contents(self::HIPAY_GITHUB_PRESTASHOP_LATEST, false, $context));
            // If call is successful, reading from call
            if ($gitHubInfo) {
                $this->newVersion = $gitHubInfo->tag_name;
                $this->readMeUrl = $gitHubInfo->html_url;
                $this->downloadUrl = $gitHubInfo->assets[0]->browser_download_url;

                $infoFormatted = new stdClass();
                $infoFormatted->newVersion = $this->newVersion;
                $infoFormatted->readMeUrl = $this->readMeUrl;
                $infoFormatted->downloadUrl = $this->downloadUrl;

                Configuration::updateValue('HIPAY_UPDATE_NOTIF_LAST_CALL', $curdate->format('d/m/Y H:i:s'));
                Configuration::updateValue('HIPAY_UPDATE_NOTIF_LAST_RESULT', json_encode($infoFormatted));
            }
        }
    }

    /**
     * Reads the update info from saved configuration data
     */
    public function readFromConf(){
        $lastResult = json_decode(Configuration::get('HIPAY_UPDATE_NOTIF_LAST_RESULT'));

        // If conf exists, reading from it
        if($lastResult) {
            $this->newVersion = $lastResult->newVersion;
            $this->readMeUrl = $lastResult->readMeUrl;
            $this->downloadUrl = $lastResult->downloadUrl;
        // If not, setting default data with values not showing the block
        } else {
            $this->newVersion = $this->version;
            $this->readMeUrl = "#";
            $this->downloadUrl = "#";
        }
    }

    /**
     * Displays the notification block in main admin dashboard
     * @return mixed
     */
    public function displayBlock()
    {
        $this->context->smarty->assign(
            array(
                'updateNotif' => $this
            )
        );

        return $this->module->display(
            $this->module->name,
            'views/templates/hook/updateNotif.tpl'
        );
    }

    /**
     * @return String
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return String
     */
    public function getNewVersion()
    {
        return $this->newVersion;
    }

    /**
     * @return String
     */
    public function getReadMeUrl()
    {
        return $this->readMeUrl;
    }

    /**
     * @return String
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }
}