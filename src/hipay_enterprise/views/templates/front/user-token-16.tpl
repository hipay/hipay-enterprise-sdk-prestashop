{**
* 2017 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2017 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}
{capture name=path}
    <a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
        {l s='My account'}
    </a>
    <span class="navigation-pipe">{$navigationPipe}</span>
    <span class="navigation_page">{l s='Order history'}</span>
{/capture}
{include file="$tpl_dir./errors.tpl"}

<h1 class="page-heading bottom-indent">{l s='Saved credit/debit card' d='hipay_enterprise'}</h1>
<p class="info-title">{l s='Here are the saved credit card from previous order.' mod='hipay_enterprise'}</p>
<div class="block-center">
    {if $savedCC }
        <table id="order-list" class="table table-bordered footab default footable-loaded footable">
            <thead>
                <tr>
                    <th>{l s='Card number' mod='hipay_enterprise'}</th>
                    <th>{l s='Card owner' mod='hipay_enterprise'}</th>
                    <th>{l s='Expiration date' mod='hipay_enterprise'}</th>
                    <th>{l s='Action' mod='hipay_enterprise'}</th>
                </tr>
            </thead>
            <tbody>

                {foreach $savedCC as $cc}
                    <tr>
                        <td><img src="{$this_path_ssl}/views/img/{$cc.brand|lower}_small.png"/> {$cc.pan}</td>
                        <td>{$cc.card_holder}</td>
                        <td>{"%02d"|sprintf:$cc.card_expiry_month} / {$cc.card_expiry_year}</td>
                        <td class="text-xs-center">
                            <form class="delTokenForm" enctype="application/x-www-form-urlencoded"
                                  action="{$link->getModuleLink('hipay_enterprise', 'userToken', [], true)|escape:'html'}"
                                  method="post">
                                <input type="hidden" name="hipayCCTokenId" value="{$cc.hp_id}"/>
                                <button type="submit" name="submitDelToken" class="btn btn-danger">
                                    <i class="icon-remove"></i>
                                    {l s='Delete' mod='hipay_enterprise'}
                                </button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
            {else}
            <p class="alert alert-warning">{l s='You have no saved credit/debit card.' mod='hipay_enterprise'}</p>
        {/if}
        </tbody>
    </table>
</div>
<ul class="footer_links clearfix">
    <li>
        <a class="btn btn-default button button-small"
           href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
            <span>
                <i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='hipay_enterprise'}
            </span>
        </a>
    </li>
    <li>
        <a class="btn btn-default button button-small" href="{$base_dir}">
            <span><i class="icon-chevron-left"></i> {l s='Home' mod='hipay_enterprise'}</span>
        </a>
    </li>
</ul>
{if $savedCC }
    <script>
        $(".delTokenForm").submit(function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (confirm("{l s='Are you sure to delete this card ?' mod='hipay_enterprise'}")) {
                this.submit();
            }
        });
    </script>
{/if}