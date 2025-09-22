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
 * HiPay SRI Helper
 *
 * Handles Subresource Integrity (SRI) functionality for the HiPay SDK
 */
class HipaySRIHelper
{
    /**
     * @var HipayConfig
     */
    private $config;

    /**
     * @var HipayLogs
     */
    private $logs;

    /**
     * HipaySRIHelper constructor.
     *
     * @param HipayConfig $config
     * @param HipayLogs $logs
     */
    public function __construct($config, $logs)
    {
        $this->config = $config;
        $this->logs = $logs;
    }

    /**
     * Get the integrity hash for the SDK JS file
     *
     * @return string|null
     */
    public function getIntegrityHash()
    {
        $sdkUrl = $this->config->getPaymentGlobal()['sdk_js_url'] ?? null;

        if (empty($sdkUrl)) {
            return null;
        }

        // Generate integrity URL by replacing .js with .integrity
        $integrityUrl = str_replace('.js', '.integrity', $sdkUrl);

        try {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $integrityUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'HiPay-PrestaShop-SRI/1.0',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/plain'
                ]
            ]);

            $integrityHash = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);

            if ($error || $httpCode !== 200) {
                $this->logs->logInfos('Failed to fetch integrity hash from: ' . $integrityUrl . 
                    ' (HTTP: ' . $httpCode . ', Error: ' . $error . ')');
                return null;
            }

            // Clean the hash (remove whitespace, newlines, etc.)
            $integrityHash = trim($integrityHash);
            
            // Validate that it looks like a hash (starts with sha256-, sha384-, or sha512-)
            if (!preg_match('/^sha(256|384|512)-[a-zA-Z0-9+\/=]+$/', $integrityHash)) {
                $this->logs->logInfos('Invalid integrity hash format: ' . $integrityHash);
                return null;
            }

            return $integrityHash;

        } catch (Exception $e) {
            $this->logs->logInfos('Exception while fetching integrity hash: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate the script tag with SRI attributes
     *
     * @param string $sdkUrl
     * @return string
     */
    public function generateScriptTag($sdkUrl)
    {
        $integrityHash = $this->getIntegrityHash();

        if ($integrityHash) {
            return sprintf(
                '<script src="%s" type="text/javascript" charset="utf-8" integrity="%s" crossorigin="anonymous"></script>',
                htmlspecialchars($sdkUrl, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($integrityHash, ENT_QUOTES, 'UTF-8')
            );
        }

        // Fallback without SRI
        return sprintf(
            '<script src="%s" type="text/javascript" charset="utf-8"></script>',
            htmlspecialchars($sdkUrl, ENT_QUOTES, 'UTF-8')
        );
    }
} 