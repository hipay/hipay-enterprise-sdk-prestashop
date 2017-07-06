{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Manage saved credit/debit card' d='hipay_enterprise'}
{/block}

{block name='page_content'}
    <h6>{l s='Saved credit/debit card' d='hipay_enterprise'}</h6>
    {if $savedCC }
        <table class="table table-striped table-bordered hidden-sm-down">
            <thead class="thead-default">
                <tr>
                    <th>{l s="Card number" mod="hipay_enterprise"}</th>
                    <th>{l s="Card owner" mod="hipay_enterprise"}</th>
                    <th>{l s="Expiration date" mod="hipay_enterprise"}</th>
                    <th>{l s="Action" mod="hipay_enterprise"}</th>
                </tr>
            </thead>
            <tbody>

                {foreach $savedCC as $cc}
                    <tr>
                        <td><img  src="{$this_path_ssl}/views/img/{$cc.brand|lower}_small.png" /> {$cc.pan}</td>
                        <td>{$cc.card_holder}</td>
                        <td>{"%02d"|sprintf:$cc.card_expiry_month} / {$cc.card_expiry_year}</td>
                        <td class="text-xs-center">
                            <form enctype="application/x-www-form-urlencoded" action="{$link->getModuleLink('hipay_enterprise', 'userToken', [], true)|escape:'html'}" method="post">
                                <input type="hidden" name="hipayCCTokenId" value="{$cc.hp_id}" />
                                <button type="submit" name="submitDelToken"  class="btn btn-danger" >
                                    <i class="material-icons" > delete </i>
                                    {l s='Delete' mod='hipay_enterprise'}
                                </button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
            {/if}
        </tbody>
    </table>

{/block}

