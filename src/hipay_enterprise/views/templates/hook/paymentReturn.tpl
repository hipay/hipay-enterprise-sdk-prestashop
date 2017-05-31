{if isset($status) && $status == 'ok'}
    <h3>{l s='Your order is complete.' mod='hipay_professional'}</h3>
    <p>
        <br/>- {l s='Amount' mod='hipay_professional'} : <span
                class="price"><strong>{$total_to_pay|escape:'htmlall':'UTF-8'}</strong></span>
        <br/>- {l s='Reference' mod='hipay_professional'} : <span
                class="reference"><strong>{$reference|escape:'htmlall':'UTF-8'}</strong></span>
        <br/><br/>{l s='An email has been sent with this information.' mod='hipay_professional'}
        <br/><br/>{l s='If you have questions, comments or concerns, please contact our' mod='hipay_professional'} <a
                href="{$link->getPageLink('contact', true)}">{l s='expert customer support team.' mod='hipay_professional'}</a>
    </p>
{else}
    <h3>{l s='Your order has not been accepted.' mod='hipay_professional'}</h3>
    <p>
        <br/>- {l s='Reference' mod='hipay_professional'} <span
                class="reference"> <strong>{$reference|escape:'htmlall':'UTF-8'}</strong></span>
        <br/><br/>{l s='Please, try to order again.' mod='hipay_professional'}
        <br/><br/>{l s='If you have questions, comments or concerns, please contact our' mod='hipay_professional'} <a
                href="{$link->getPageLink('contact', true)}">{l s='expert customer support team.' mod='hipay_professional'}</a>
    </p>
{/if}
<hr/>