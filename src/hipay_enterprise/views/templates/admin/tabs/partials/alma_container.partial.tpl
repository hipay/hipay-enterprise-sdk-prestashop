<div class="input-group col-lg-6">
        <div class="alma-container">
            <h4 id="{$key}_{$amount}"><span></span></h4>
        </div>
        </br></br>
        {if $amount == 'maxAmount'}
                <p class="alert alert-info">
                        {l s='For any questions regarding minimum and maximum amounts, please contact support or your account manager.' mod='hipay_enterprise'}
                </p>
        {/if}
</div>