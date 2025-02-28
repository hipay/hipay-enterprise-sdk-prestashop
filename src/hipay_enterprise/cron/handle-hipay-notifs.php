<?php

require_once dirname(__DIR__, 3) . '/config/config.inc.php';

$moduleName = 'hipay_enterprise';
$module = Module::getInstanceByName($moduleName);
if (!$module || !$module->active) {
    die('HiPay module disabled !');
}

$config = $module->hipayConfigTool->getConfigHipay();

if (!$config['account']['global']['notification_cron']) {
    die('HiPay CRON mode disabled !');
}

$token = $config['account']['global']['notification_cron_token'];

$url = \Configuration::get('PS_SHOP_DOMAIN') . '/index.php?fc=module&module=' . $moduleName . '&controller=dispatch&token=' . $token;

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    error_log("Erreur cURL $token : $error");
} else {
    error_log("Requête envoyée avec succès ! Réponse ($httpCode) : $response");
}

echo date('[Y-m-d H:i:s]') . " - CRON job completed\n";
