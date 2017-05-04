{**
* 2016 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2016 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}
<div class="panel">
    <div class="row">
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-danger">Error Logs</a>
                {foreach from=$logs['error'] item=select}
                    <a href="{$module_dir}logs/{$select}" target="_blank" class="list-group-item ">{$select}</a>
                {/foreach}
            </div>
        </div>
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-info">Request new order Logs</a>
                {foreach from=$logs['request'] item=select}
                    <a href="{$module_dir}logs/{$select}" target="_blank" class="list-group-item ">{$select}</a>
                {/foreach}
            </div>
        </div>
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-info">Callback Logs</a>
                {foreach from=$logs['callback'] item=select}
                    <a href="{$module_dir}logs/{$select}" target="_blank" class="list-group-item ">{$select}</a>
                {/foreach}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-warning">Refund Logs</a>
                {foreach from=$logs['refund'] item=select}
                    <a href="{$module_dir}logs/{$select}" target="_blank" class="list-group-item ">{$select}</a>
                {/foreach}
            </div>
        </div>
        <div class="col-md-4 col-xs-4">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-info">General Logs</a>
                {foreach from=$logs['infos'] item=select}
                    <a href="{$module_dir}logs/{$select}" target="_blank" class="list-group-item ">{$select}</a>
                {/foreach}
            </div>
        </div>
    </div>
</div>
