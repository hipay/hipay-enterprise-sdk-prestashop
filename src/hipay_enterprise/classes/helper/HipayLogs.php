<?php
/**
 * HiPay Enterprise SDK Prestashop.
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
 * Handle module logs.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayLogs
{
    public const LOG_HIPAY_ERROR = 0;
    public const LOG_HIPAY_INFOS = 1;
    public const LOG_HIPAY_REQUEST = 2;
    public const LOG_HIPAY_CALLBACK = 3;
    public const LOG_HIPAY_NOTIFICATION_CRON = 4;
    public const DEBUG_KEYS_MASK = '****';

    public $enable = true;
    private $basePath;
    private $privateDataKeys = ['token', 'cardtoken', 'card_number', 'cvc', 'api_password_sandbox',
        'api_tokenjs_username_sandbox', 'api_tokenjs_password_publickey_sandbox', 'api_secret_passphrase_sandbox',
        'api_password_production', 'api_tokenjs_username_production',
        'api_tokenjs_password_publickey_production', 'api_secret_passphrase_production', 'api_moto_username_production',
        'api_moto_password_production', 'api_moto_secret_passphrase_production',
        'api_apple_pay_username_production', 'api_apple_pay_password_production', 'api_apple_pay_passphrase_production',
        'api_tokenjs_apple_pay_username_production', 'api_tokenjs_apple_pay_password_production',
        'api_apple_pay_username_sandbox', 'api_apple_pay_password_sandbox', 'api_apple_pay_passphrase_sandbox',
        'api_tokenjs_apple_pay_username_sandbox', 'api_tokenjs_apple_pay_password_sandbox',
    ];

    private $installProcess = true;

    /** @var Hipay_entreprise */
    private $module;

    /**
     * HipayLogs constructor.
     *
     * @param Hipay_entreprise $module_instance
     * @param bool             $enableConf
     */
    public function __construct($module_instance, $enableConf = true)
    {
        $this->module = $module_instance;

        // Init base path for logs
        $this->basePath = _PS_ROOT_DIR_.'/app/logs/';

        if (!file_exists($this->basePath)) {
            $this->basePath = _PS_ROOT_DIR_.'/var/logs/';
        }

        if (!file_exists($this->basePath)) {
            $this->basePath = _PS_ROOT_DIR_.'/log/';
        }

        $this->enable = (isset($enableConf) ? $enableConf : true);
    }

    /**
     * @return void
     */
    public function setInstallProcess(bool $installProcess)
    {
        $this->installProcess = $installProcess;
    }

    /**
     * Log exception.
     *
     * @param GatewayException
     *
     * @return void
     */
    public function logException(Exception $exception)
    {
        $this->logErrors($exception->getMessage());
        $this->logErrors($exception->getTraceAsString());
    }

    /**
     * Log error.
     *
     * @param string $msg
     *
     * @return void
     */
    public function logErrors($msg)
    {
        $this->writeLogs(self::LOG_HIPAY_ERROR, $this->getExecutionContext().':'.$msg);
    }

    /**
     * Log infos ( HiPay Technical Logs ).
     *
     * @param string $msg
     *
     * @return void
     */
    public function logInfos($msg)
    {
        if ($this->installProcess || $this->module->hipayConfigTool->getPaymentGlobal()['log_infos']) {
            if (is_array($msg)) {
                $this->writeLogs(self::LOG_HIPAY_INFOS, print_r($this->filterDebugData($msg), true));
            } else {
                $this->writeLogs(self::LOG_HIPAY_INFOS, $msg);
            }
        }
    }

    /**
     * Logs Callback ( HiPay notification ).
     *
     * @param array<string,mixed> $transaction
     *
     * @return void
     */
    public function logCallback($transaction)
    {
        $this->writeLogs(
            self::LOG_HIPAY_CALLBACK,
            print_r($this->filterDebugData($this->toArray($transaction)), true)
        );
    }

    /**
     * Logs Request ( HiPay Request ).
     *
     * @param array<string,mixed> $transaction
     *
     * @return void
     */
    public function logRequest($request)
    {
        $this->writeLogs(self::LOG_HIPAY_REQUEST, print_r($this->filterDebugData($this->toArray($request)), true));
    }

    /**
     * Logs Notification cron.
     *
     * @param string $msg
     *
     * @return void
     */
    public function logNotificationCron($msg)
    {
        $this->writeLogs(self::LOG_HIPAY_NOTIFICATION_CRON, $msg);
    }

    /**
     * List log files.
     *
     * @return array<string,string[]>
     */
    public function getLogFiles()
    {
        // Scan log dir
        $directory = $this->getBasePath();
        $files = scandir($directory, 1);

        // Init array files
        $error_files = [];
        $info_files = [];
        $callback_files = [];
        $request_files = [];
        $refund_files = [];

        // List files
        foreach ($files as $file) {
            if (preg_match('/error/i', $file) && count($error_files) < 10) {
                $error_files[] = $file;
            }
            if (preg_match('/callback/i', $file) && count($callback_files) < 10) {
                $callback_files[] = $file;
            }
            if (preg_match('/infos/i', $file) && count($info_files) < 10) {
                $info_files[] = $file;
            }
            if (preg_match('/request/i', $file) && count($request_files) < 10
            ) {
                $request_files[] = $file;
            }
        }

        return [
            'error' => $error_files,
            'infos' => $info_files,
            'callback' => $callback_files,
            'request' => $request_files,
            'refund' => $refund_files,
        ];
    }

    /**
     * Display log file.
     *
     * @param string $logFile
     *
     * @return void
     */
    public function displayLogFile($logFile)
    {
        $path = $this->getBasePath().$logFile;

        if (!file_exists($path)) {
            http_response_code(404);
            $this->logErrors("Log File not found $path");
            exit('<h1>File not found</h1>');
        } else {
            header('Content-Type: text/plain');
            $content = Tools::file_get_contents($path);
            echo $content;
            exit;
        }
    }

    /**
     * Return path for logs.
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Recursive filter data for privacy data.
     *
     * @return array
     */
    protected function filterDebugData(array $debugData)
    {
        $debugReplacePrivateDataKeys = array_map('strtolower', $this->privateDataKeys);

        foreach (array_keys($debugData) as $key) {
            if (false !== strpos($key, "\0")) {
                $newKey = str_replace("\0", '', $key);
                $debugData[$newKey] = $debugData[$key];
                unset($debugData[$key]);
                $key = $newKey;
            }

            if (in_array(preg_replace('/^[^a-z]+/', '', Tools::strtolower($key)), $debugReplacePrivateDataKeys)) {
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
     * Get execution context.
     *
     * @return Execution
     */
    protected function getExecutionContext()
    {
        $debug = debug_backtrace();
        if (isset($debug[2])) {
            return $debug[2]['class'].':'.$debug[2]['function'];
        }

        return null;
    }

    /**
     * Convert Object to Array.
     *
     * @param mixed
     *
     * @return array
     */
    private function toArray($object)
    {
        return (array) $object;
    }

    /**
     * Format log message and write log.
     *
     * @param string $type
     * @param string $message
     *
     * @return bool|int
     */
    private function writeLogs($type, $message)
    {
        $formatted_message = date('Y/m/d - H:i:s').': '.$message."\r\n";

        return file_put_contents($this->getFilename($type), $formatted_message, FILE_APPEND);
    }

    /**
     * Get log filename according de type of error.
     *
     * @param string $type
     *
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
            case self::LOG_HIPAY_NOTIFICATION_CRON:
                $filename = 'notification-cron';
                break;
            default:
                $filename = 'infos';
                break;
        }

        return $this->basePath.date('Y-m-d').'-hipay-'.$filename.'.log';
    }
}
