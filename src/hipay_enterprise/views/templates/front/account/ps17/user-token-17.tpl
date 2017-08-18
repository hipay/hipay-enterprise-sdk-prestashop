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
{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Manage saved credit/debit card' mod='hipay_enterprise'}
{/block}

{block name='page_content'}
    {if $savedCC }
        <h6>{l s='Saved credit/debit card' mod='hipay_enterprise'}</h6>
        <table class="table table-striped table-bordered hidden-sm-down">
            <thead class="thead-default">
                <tr>
                    <th class="text-xs-center" >{l s='Card number' mod='hipay_enterprise'}</th>
                    <th class="text-xs-center" >{l s='Card owner' mod='hipay_enterprise'}</th>
                    <th class="text-xs-center" >{l s='Expiration date' mod='hipay_enterprise'}</th>
                    <th class="text-xs-center" >{l s='Actions' mod='hipay_enterprise'}</th>
                </tr>
            </thead>
            <tbody>

                {foreach $savedCC as $cc}
                    <tr>
                        <td><img src="{$this_path_ssl}/views/img/{$cc.brand|lower}_small.png"/> {$cc.pan}</td>
                        <td>{$cc.card_holder}</td>
                        <td>{"%02d"|sprintf:$cc.card_expiry_month} / {$cc.card_expiry_year}</td>
                        <td >
                            <form class="delTokenForm" enctype="application/x-www-form-urlencoded"
                                  action="{$link->getModuleLink('hipay_enterprise', 'userToken', [], true)|escape:'html'}"
                                  method="post">
                                <input type="hidden" name="hipayCCTokenId" value="{$cc.hp_id}"/>
                                <button type="submit" name="submitDelToken" class="btn btn-danger">
                                    <i class="material-icons"> delete </i>
                                    {l s='Delete' mod='hipay_enterprise'}
                                </button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>

        <script>

            document.addEventListener('DOMContentLoaded', formListenerToken, false);

            function formListenerToken() {
                $(".delTokenForm").submit(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm("{l s='Are you sure to delete this card ?' mod='hipay_enterprise'}")) {
                        this.submit();
                    }
                });

            }
        </script>
    {/if}
{/block}

