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
<div class="panel">
    <dl id="faq_q_a">
        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq1" aria-expanded="true" aria-controls="faq1"><i
                class="icon icon-question-circle"></i> {l s='In which countries can I use HiPay Professional?' mod='hipay_enterprise'}
        </dt>
        <dd id="faq1" class="panel-collapse collapse">
            <p>
                {l s='HiPay Professional is available to all your customers, wherever they are.' mod='hipay_enterprise'}
                <br/>
                {l s='Depending on a customer\'s country, some local and leading payment solutions will be available as well.' mod='hipay_enterprise'}
                <br/>
            </p>
        </dd>

        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq2" aria-expanded="true" aria-controls="faq2"><i
                class="icon icon-question-circle"></i> {l s='Which payment solutions are available with HiPay Professional?' mod='hipay_enterprise'}
        </dt>
        <dd id="faq2" class="panel-collapse collapse">
            <p>
                {l s='The payment solutions available on your shop with HiPay Professional depend on your customer’s delivery address: each country has a different set of payment solutions' mod='hipay_enterprise'}
                <br/>
                {l s='If you wish to remove some payment solutions from your offer, please contact [1]prestashop@hipay.com[/1]' tags=['<a href="mailto:prestashop@hipay.com">'] mod='hipay_enterprise'}
            </p>
        </dd>

        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq3" aria-expanded="true" aria-controls="faq3"><i
                class="icon icon-question-circle"></i> {l s='Which currencies can I accept with HiPay Professional?' mod='hipay_enterprise'}
        </dt>
        <dd id="faq3" class="panel-collapse collapse">
            <p>
                {l s='HiPay Professional works with the following currencies:' mod='hipay_enterprise'}<br/>
                {l s='Australian Dollar, Canadian Dollar, Swiss Franc, Euro, British Pound, Swedish Krona, US Dollar, Polish Sloty.' mod='hipay_enterprise'}
            </p>
        </dd>

        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq4" aria-expanded="true" aria-controls="faq4"><i
                class="icon icon-question-circle"></i> {l s='I want to have HiPay Professional currencies available in my shop, how can I do?' mod='hipay_enterprise'}
        </dt>
        <dd id="faq4" class="panel-collapse collapse">
            {l s='If you want to offer new currencies on your shop, please follow these steps:' mod='hipay_enterprise'}
            <br/>
            <ol>
                <li>{l s='In Localization > Currencies, add the new currency you want to activate' mod='hipay_enterprise'}</li>
                <li>{l s='In Modules > Payment, activate the new currency for HiPay Professional. If you don’t want to accept this currency through other payment solutions, please make sure it is activated only for HiPay Professional.' mod='hipay_enterprise'}</li>
            </ol>
        </dd>

        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq5" aria-expanded="true" aria-controls="faq5"><i
                class="icon icon-question-circle"></i> {l s='How much does it cost?' mod='hipay_enterprise'}</dt>
        <dd id="faq5" class="panel-collapse collapse">
            <p>
                {l s='Some fees might apply on some operations (withdrawal, transfer, etc), please [1]click here for more info[/1].' tags=['<a href="#" target="_blank">'] mod='hipay_enterprise'}
            </p>
        </dd>

        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq6" aria-expanded="true" aria-controls="faq6"><i
                class="icon icon-question-circle"></i> {l s='Why do I have some sub-accounts?' mod='hipay_enterprise'}
        </dt>
        <dd id="faq6" class="panel-collapse collapse">
            <p>
                {l s='Your account is created based on your shop’s main currency. To handle other available currencies, some sub-accounts have been created. Each sub-account handles a different currency.' mod='hipay_enterprise'}
                <br/>
                {l s='To withdraw money from your sub-account, you should transfer their respective balances to your main account first' mod='hipay_enterprise'}
            </p>
        </dd>

        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq7" aria-expanded="true" aria-controls="faq7"><i
                class="icon icon-question-circle"></i> {l s='How can I transfer my account balance to my own bank account?' mod='hipay_enterprise'}
        </dt>
        <dd id="faq7" class="panel-collapse collapse">
            <p>
                {l s='You can transfer your account balance from your main account only. If you wish to withdraw your sub-accounts balances too (if any), you should transfer these balances to your main account first. You can only withdraw money from your main account.' mod='hipay_enterprise'}
                <br/>
                {l s='Before requesting a transfer, please make sure you have sent your bank details to HiPay. HiPay won’t be able to transfer the money unless your bank details have been validated.' mod='hipay_enterprise'}
                <br/>
                {l s='To do so, please go to your [1]HiPay account[/1], “Withdrawal > Enter your bank details” section.' tags=['<a href="https://www.hipaydirect.com/prestashop-payments/" target="_blank">'] mod='hipay_enterprise'}
            </p>
        </dd>

        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq8" aria-expanded="true" aria-controls="faq8"><i
                class="icon icon-question-circle"></i> {l s='What is “identification” and why my account should be “identified”?' mod='hipay_enterprise'}
        </dt>
        <dd id="faq8" class="panel-collapse collapse">
            <p>
                {l s='An unidentified account is an account which we have not checked yet, nor confirmed its identification details upon signing up. The balance of an unidentified account cannot exceed €2500.' mod='hipay_enterprise'}
                <br/>
                <br/>
                {l s='Any transaction which, if completed, would exceed this maximum balance, will be refused by our system.' mod='hipay_enterprise'}
                <br/>
                {l s='For an account to the registered as «Identified» by HiPay’s Verification Department, you must send HiPay the legal and administrative documents we need to positively establish your identity (i.e. the information you entered when you first signed up).' mod='hipay_enterprise'}
                <br/>
                {l s='In keeping with our General Conditions of Use, you must inform HiPay of any changes to your personal data, and submit suitable items of written proof.' mod='hipay_enterprise'}
            </p>
        </dd>

        <dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq9" aria-expanded="true" aria-controls="faq9"><i
                class="icon icon-question-circle"></i> {l s='I want more info, where should I look?' mod='hipay_enterprise'}
        </dt>
        <dd id="faq9" class="panel-collapse collapse">
            <p>
                {l s='Don’t hesitate to [1]log in to your HiPay account[/1] to have more detailed information on your account and your transactions.' tags=['<a href="https://www.hipaydirect.com/prestashop-payments/" target="_blank">'] mod='hipay_enterprise'}
            </p>
        </dd>
    </dl>

    <hr/>
</div>
