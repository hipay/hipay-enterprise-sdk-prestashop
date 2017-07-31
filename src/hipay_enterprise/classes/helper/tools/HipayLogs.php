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
    const LOG_HIPAY_ERROR = 0;
    const LOG_HIPAY_INFOS = 1;
    const LOG_HIPAY_REQUEST = 2;
    const LOG_HIPAY_CALLBACK = 3;

    const DEBUG_KEYS_MASK = '****';

    public $enable = true;
    private $basePath;

    private $privateDataKeys = array('token', 'cardtoken', 'card_number', 'cvc');


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
        //TODO Rebrancher
        ///if ( $this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["log_infos"]) {
            $this->writeLogs(self::LOG_HIPAY_INFOS, $msg);
       // }
    }

    /**
     *  Logs Callback ( HiPay notification )
     *
     * @param $transaction
     */
    public function logCallback($transaction)
    {
        $this->writeLogs(self::LOG_HIPAY_CALLBACK, print_r(
            $this->filterDebugData($this->to_array($transaction)),
            true
        ));
    }

    /**
     * Logs Request ( HiPay Request )
     *
     * @param $request
     */
    public function logRequest($request)
    {
        $this->writeLogs(self::LOG_HIPAY_REQUEST, print_r(
            $this->filterDebugData($this->to_array($request)),
            true
        ));
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
     * Format log message and write log
     *
     * @param $type string
     * @param $message string
     * @return bool|int
     */
    private function writeLogs($type, $message)
    {
        $formatted_message = date('Y/m/d - H:i:s').': '.$message."\r\n";
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
            if (in_array(strtolower($key), $debugReplacePrivateDataKeys)) {
                $debugData[$key] = self::DEBUG_KEYS_MASK;
            } elseif (is_array($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($debugData[$key]);
            } elseif (is_object($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($this->to_array($debugData[$key]));
            }

        }
        return $debugData;
    }

    /**
     *  Convert Object to Array
     *
     * @param $object
     * @return array
     */
    function to_array($object) {
        return (array) $object;
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
}
