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
 * Handle module logs
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayLogs
{
    const LOG_HIPAY_ERROR = 0;
    const LOG_HIPAY_INFOS = 1;
    const LOG_HIPAY_REQUEST = 2;
    const LOG_HIPAY_CALLBACK = 3;
    const DEBUG_KEYS_MASK = '****';

    public $enable = true;
    private $basePath;
    private $privateDataKeys = array('token', 'cardtoken', 'card_number', 'cvc', 'api_password_sandbox',
        'api_tokenjs_username_sandbox', 'api_tokenjs_password_publickey_sandbox', 'api_secret_passphrase_sandbox',
        'api_password_production', 'api_tokenjs_username_production',
        'api_tokenjs_password_publickey_production', 'api_secret_passphrase_production', 'api_moto_username_production',
        'api_moto_password_production', 'api_moto_secret_passphrase_production');

    /**
     * HipayLogs constructor.
     *
     * @param $module_instance
     * @param bool $enableConf
     */
    public function __construct($module_instance, $enableConf = true)
    {
        $this->context = Context::getContext();
        $this->module = $module_instance;

        // Init base path for logs
        $this->basePath = _PS_ROOT_DIR_ . '/app/logs/';

        if (!file_exists($this->basePath)) {
            $this->basePath = _PS_ROOT_DIR_ . '/var/logs/';
        }

        if (!file_exists($this->basePath)) {
            $this->basePath = _PS_ROOT_DIR_ . '/log/';
        }

        $this->enable = (isset($enableConf) ? $enableConf : true);
    }

    /**
     *  Log exception
     *
     * @param GatewayException
     */
    public function logException(Exception $exception)
    {
        $this->logErrors($exception->getMessage());
        $this->logErrors($exception->getTraceAsString());
    }

    /**
     *  Log error
     *
     * @param $msg
     */
    public function logErrors($msg)
    {
        $this->writeLogs(self::LOG_HIPAY_ERROR, $this->getExecutionContext() . ':' . $msg);
    }

    /**
     *  Log infos ( HiPay Technical Logs )
     *
     * @param $msg
     */
    public function logInfos($msg)
    {
        if ($this->module->hipayConfigTool->getPaymentGlobal()["log_infos"]) {
            if (is_array($msg)) {
                $this->writeLogs(self::LOG_HIPAY_INFOS, print_r($this->filterDebugData($msg), true));
            } else {
                $this->writeLogs(self::LOG_HIPAY_INFOS, $msg);
            }
        }
    }

    /**
     *  Logs Callback ( HiPay notification )
     *
     * @param $transaction
     */
    public function logCallback($transaction)
    {
        $this->writeLogs(
            self::LOG_HIPAY_CALLBACK,
            print_r($this->filterDebugData($this->toArray($transaction)), true)
        );
    }

    /**
     * Logs Request ( HiPay Request )
     *
     * @param $request
     */
    public function logRequest($request)
    {
        $this->writeLogs(self::LOG_HIPAY_REQUEST, print_r($this->filterDebugData($this->toArray($request)), true));
    }

    /**
     * List log files
     *
     * @return string
     */
    public function getLogFiles()
    {
        // Scan log dir
        $directory = $this->getBasePath();
        $files = scandir($directory, 1);

        // Init array files
        $error_files = array();
        $info_files = array();
        $callback_files = array();
        $request_files = array();
        $refund_files = array();

        // List files
        foreach ($files as $file) {
            if (preg_match("/error/i", $file) && count($error_files) < 10) {
                $error_files[] = $file;
            }
            if (preg_match("/callback/i", $file) && count($callback_files) < 10) {
                $callback_files[] = $file;
            }
            if (preg_match("/infos/i", $file) && count($info_files) < 10) {
                $info_files[] = $file;
            }
            if (preg_match("/request/i", $file) && count($request_files) < 10
            ) {
                $request_files[] = $file;
            }
        }

        return array(
            'error' => $error_files,
            'infos' => $info_files,
            'callback' => $callback_files,
            'request' => $request_files,
            'refund' => $refund_files
        );
    }

    /**
     * Display log file
     * @param type $logFile
     */
    public function displayLogFile($logFile)
    {
        $path = $this->getBasePath() . $logFile;

        if (!file_exists($path)) {
            http_response_code(404);
            $this->logErrors("Log File not found $path");
            die('<h1>File not found</h1>');
        } else {
            header('Content-Type: text/plain');
            $content = Tools::file_get_contents($path);
            echo $content;
            die();
        }
    }

    /**
     *  Return path for logs
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Recursive filter data for privacy data
     *
     * @param array $debugData
     * @return array
     */
    protected function filterDebugData(array $debugData)
    {
        $debugReplacePrivateDataKeys = array_map('strtolower', $this->privateDataKeys);

        foreach (array_keys($debugData) as $key) {
            if (in_array(Tools::strtolower($key), $debugReplacePrivateDataKeys)) {
                $debugData[$key] = self::DEBUG_KEYS_MASK;
            } elseif (is_array($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($debugData[$key]);
            } elseif (is_object($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($this->toArray($debugData[$key]));
            }
        }
        return $debugData;
    }

    /**
     * Get execution context
     *
     * @return Execution
     */
    protected function getExecutionContext()
    {
        $debug = debug_backtrace();
        if (isset($debug[2])) {
            return $debug[2]['class'] . ':' . $debug[2]['function'];
        }
        return null;
    }

    /**
     *  Convert Object to Array
     *
     * @param $object
     * @return array
     */
    private function toArray($object)
    {
        return (array)$object;
    }

    /**
     * Format log message and write log
     *
     * @param $type string
     * @param $message string
     * @return bool|int
     */
    private function writeLogs($type, $message)
    {
        $formatted_message = date('Y/m/d - H:i:s') . ': ' . $message . "\r\n";
        return file_put_contents($this->getFilename($type), $formatted_message, FILE_APPEND);
    }

    /**
     * Get log filename according de type of error
     * @param $type strinf
     * @return string
     */
    private function getFilename($type)
    {
        switch ($type) {
            case self::LOG_HIPAY_ERROR:
                $filename = 'error';
                break;
            case self::LOG_HIPAY_INFOS:
                $filename = 'infos';
                break;
            case self::LOG_HIPAY_CALLBACK:
                $filename = 'callback';
                break;
            case self::LOG_HIPAY_REQUEST:
                $filename = 'request';
                break;
            default:
                $filename = 'infos';
                break;
        }
        return $this->basePath . date('Y-m-d') . '-' . 'hipay' . '-' . $filename . '.log';
    }
}
