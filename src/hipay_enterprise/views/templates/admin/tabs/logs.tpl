{**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *}
<div class="panel">
    <div class="row">
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#logs" class="list-group-item list-group-item-danger">Error Logs</a>
                {foreach from=$HiPay_logs['error'] item=select}
                    <a href="{$HiPay_module_url}&logfile={$select|escape:'htmlall':'UTF-8'}" target="_blank"
                        class="list-group-item ">{$select|escape:'html':'UTF-8'}</a>
                {/foreach}
            </div>
        </div>
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#logs" class="list-group-item list-group-item-info">Info Logs</a>
                {foreach from=$HiPay_logs['infos'] item=select}
                    <a href="{$HiPay_module_url}&logfile={$select|escape:'htmlall':'UTF-8'}" target="_blank"
                        class="list-group-item ">{$select|escape:'html':'UTF-8'}</a>
                {/foreach}
            </div>
        </div>
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#logs" class="list-group-item list-group-item-info">Callback Logs</a>
                {foreach from=$HiPay_logs['callback'] item=select}
                    <a href="{$HiPay_module_url}&logfile={$select|escape:'htmlall':'UTF-8'}" target="_blank"
                        class="list-group-item ">{$select|escape:'html':'UTF-8'}</a>
                {/foreach}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#logs" class="list-group-item list-group-item-warning">Request Logs</a>
                {foreach from=$HiPay_logs['request'] item=select}
                    <a href="{$HiPay_module_url}&logfile={$select|escape:'htmlall':'UTF-8'}" target="_blank"
                        class="list-group-item ">{$select|escape:'html':'UTF-8'}</a>
                {/foreach}
            </div>
        </div>
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#logs" class="list-group-item list-group-item-info">Cron Logs</a>
                {foreach from=$HiPay_logs['notification-cron'] item=select}
                    <a href="{$HiPay_module_url}&logfile={$select|escape:'htmlall':'UTF-8'}" target="_blank"
                        class="list-group-item ">{$select|escape:'html':'UTF-8'}</a>
                {/foreach}
            </div>
        </div>
    </div>
</div>