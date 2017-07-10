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

class HipayLogs
{
    public $enable = true;
    private $basePath;

    public function __construct(
        $module_instance,
        $enableConf = true
    ) {
        $this->context = Context::getContext();
        $this->module = $module_instance;
        // init config hipay
        $this->enable = (isset($enableConf) ? $enableConf : true);
        $this->basePath = '/hipay_enterprise/logs/';
    }

    /**
     *
     * LOG Errors
     *
     */
    public function errorLogsHipay($msg)
    {
        $this->writeLogs(
            0,
            $msg
        );
    }

    /**
     *
     * LOG APP
     *
     */
    public function logsHipay($msg)
    {
        $this->writeLogs(
            1,
            $msg
        );
    }

    public function callbackLogs($msg)
    {
        $this->writeLogs(
            2,
            $msg
        );
    }

    public function requestLogs($msg)
    {
        $this->writeLogs(
            3,
            $msg
        );
    }

    public function refundLogs($msg)
    {
        $this->writeLogs(
            4,
            $msg
        );
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    private function writeLogs(
        $code,
        $msg
    ) {
        if ($this->enable) {
            switch ($code) {
                case 0:
                    $fp = fopen(
                        _PS_MODULE_DIR_ . $this->basePath . date('Y-m-d') . '-error-logs.txt',
                        'a+'
                    );
                    break;
                case 1:
                    $fp = fopen(
                        _PS_MODULE_DIR_ . $this->basePath . date('Y-m-d') . '-infos-logs.txt',
                        'a+'
                    );
                    break;
                case 2:
                    $fp = fopen(
                        _PS_MODULE_DIR_ . $this->basePath . date('Y-m-d') . '-callback.txt',
                        'a+'
                    );
                    break;
                case 3:
                    $fp = fopen(
                        _PS_MODULE_DIR_ . $this->basePath . date('Y-m-d') . '-request-new-order.txt',
                        'a+'
                    );
                    break;
                case 4:
                    $fp = fopen(
                        _PS_MODULE_DIR_ . $this->basePath . date('Y-m-d') . '-refund-order.txt',
                        'a+'
                    );
                    break;
                default:
                    $fp = fopen(
                        _PS_MODULE_DIR_ . $this->basePath . date('Y-m-d') . '-infos-logs.txt',
                        'a+'
                    );
                    break;
            }
            fseek(
                $fp,
                SEEK_END
            );
            fputs(
                $fp,
                '## ' . date('Y-m-d H:i:s') . ' ##' . PHP_EOL
            );
            fputs(
                $fp,
                $msg . PHP_EOL
            );
            fclose($fp);
        }
    }
}
