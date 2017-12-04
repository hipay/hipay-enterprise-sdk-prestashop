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
<div class="panel" id="panel-faq">
    <div class="panel-heading"></div>
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-question">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><i
                        class="icon-question-circle"></i> {l s='How do I get my HiPay API credentials ?' mod='hipay_enterprise'}
                <i class="icon-arrow-circle-o-down"></i></a>
        </div>
        <div id="collapseOne" class="panel-collapse collapse">
            <div class="panel-body">
                <p>
                    {l s='You need to generate' mod='hipay_enterprise'}
                    <strong>{l s='API credentials' mod='hipay_enterprise'}</strong> {l s='to send requests to the HiPay Enterprise platform. To do so, go to the "Integration" section of your HiPay Enterprise back office, then to "Security Settings".' mod='hipay_enterprise'}
                </p>
                <p>
                    {l s='To be sure that your credentials have the proper accessibility' mod='hipay_enterprise'}:
                </p>
                <p>
                <ul>
                    <li>{l s='Scroll down to "Api credentials".' mod='hipay_enterprise'}</li>
                    <li>{l s='Click on the edit icon next to the credentials you want to use.' mod='hipay_enterprise'}</li>
                </ul>
                </p>
                <p>
                    <strong>{l s='Private credentials' mod='hipay_enterprise'}</strong>
                </p>
                {l s='Your credentials must be granted to' mod='hipay_enterprise'}:

                <p>
                    <strong>{l s='Order' mod='hipay_enterprise'}</strong>
                </p>
                <ul>
                    <li>{l s='Create a payment page' mod='hipay_enterprise'}</li>
                    <li>{l s='Process an order through the API' mod='hipay_enterprise'}</li>
                    <li>{l s='Get transaction informations' mod='hipay_enterprise'}</li>
                </ul>
                <p>
                    <strong>{l s='Maintenance' mod='hipay_enterprise'}</strong>
                </p>
                <ul>
                    <li>{l s='Capture' mod='hipay_enterprise'}</li>
                    <li>{l s='Refund' mod='hipay_enterprise'}</li>
                    <li>{l s='Accept/Deny' mod='hipay_enterprise'}</li>
                    <li>{l s='Cancel' mod='hipay_enterprise'}</li>
                    <li>{l s='Finalize' mod='hipay_enterprise'}</li>
                </ul>

                <p>
                    <strong>{l s='Public credentials' mod='hipay_enterprise'}</strong>
                </p>
                {l s='Your credentials must be granted to' mod='hipay_enterprise'}:

                <p>
                    <strong>{l s='Tokenization' mod='hipay_enterprise'}</strong>
                </p>
                <ul>
                    <li>{l s='Tokenize a card' mod='hipay_enterprise'}</li>
                </ul>

                <p>
                    <strong>{l s='Order' mod='hipay_enterprise'}</strong>
                </p>

                <ul>
                    <li>{l s='Get transaction details with public credentials' mod='hipay_enterprise'}</li>
                    <li>{l s='Process an order through the API with public credentials' mod='hipay_enterprise'}</li>
                    <li>{l s='Create a payment page with public credentials' mod='hipay_enterprise'}</li>
                </ul>

            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-question">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"><i
                        class="icon-question-circle"></i> {l s='How do I fill in the API IDs in the module ?' mod='hipay_enterprise'}
                <i class="icon-arrow-circle-o-down"></i></a>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse">
            <div class="panel-body">
                <p>
                    {l s='In the module configuration, go to “Modules > Modules & Services”.' mod='hipay_enterprise'}
                    {l s='In the HiPay Enterprise configuration, click on the “Module Settings” tab.' mod='hipay_enterprise'}
                </p>
                <p>
                    {l s='If your module is in Test mode, you can specify the IDs in the Test area. If it is in Production mode, do the same in the Production area.' mod='hipay_enterprise'}
                </p>
                <p>
                    {l s='Enter the corresponding username, password and secret passphrase.' mod='hipay_enterprise'}
                </p>
                {l s='Public credentials are mandatory if you do not use the payment form hosted by HiPay.' mod='hipay_enterprise'}

                {l s='After specifying these identifiers, make a test payment to check that they are valid and that they have the proper rights.' mod='hipay_enterprise'}
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-question">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseThree"><i
                        class="icon-question-circle"></i> {l s=' Why do orders never reach the “Accepted Payment” status?' mod='hipay_enterprise'}
                <i class="icon-arrow-circle-o-down"></i></a>
        </div>
        <div id="collapseThree" class="panel-collapse collapse">
            <div class="panel-body">
                <p>
                    {l s='First, check if the notification URL is correctly entered in your HiPay Enterprise back office.' mod='hipay_enterprise'}
                    {l s='In the configuration of the PrestaShop module, retrieve the callback URL in the "Module Settings" tab.' mod='hipay_enterprise'}
                </p>
                <p>
                    {l s='Then, in your HiPay Enterprise back office, in the "Integration" section , click on "Notifications".' mod='hipay_enterprise'}
                <ul>
                    <li>{l s='Notification URL: http: // www.[Your-domain.com] /index.php?fc=module&module=hipay_enterprise&controller=notify' mod='hipay_enterprise'}</li>
                    <li>{l s='Request method: HTTP POST' mod='hipay_enterprise'}</li>
                    <li>{l s='I want to be notified for the following transaction statuses: ALL' mod='hipay_enterprise'}</li>
                </ul>
                </p>
                <p>
                    {l s='Then make a test payment.' mod='hipay_enterprise'}
                    {l s='From the "Notifications" section of your HiPay Enterprise back office, in the transaction details, you can also check the status of the call.' mod='hipay_enterprise'}
                </p>
                <p>
                    {l s='If notifications are sent, there may be an internal module error.' mod='hipay_enterprise'}
                    {l s='To check if an error occurred during the notification, check the hipay-error and hipay-callback logs.' mod='hipay_enterprise'}
                </p>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-question">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseFour"><i
                        class="icon-question-circle"></i> {l s='What to do when payment errors occur ?' mod='hipay_enterprise'}
                <i class="icon-arrow-circle-o-down"></i></a>
        </div>
        <div id="collapseFour" class="panel-collapse collapse">
            <div class="panel-body">
                <ul>
                    <li>{l s='Make sure that your credentials are correctly set and that the module is in the mode you want (Test or Production).' mod='hipay_enterprise'}</li>
                    <li>{l s='Check that the related payment methods are activated in your contract(s).' mod='hipay_enterprise'}</li>
                    <li>{l s='Check the version of the installed module, and upgrade the module if the version is old.' mod='hipay_enterprise'}</li>
                    <li>{l s='Check HiPay logs to see if any errors appear. Then send these logs to the HiPay Support team.' mod='hipay_enterprise'}</li>
                    <li>{l s='Check that your servers are not behind a proxy. If so, provide the proxy information in the module configuration.' mod='hipay_enterprise'}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-question">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseFive"><i
                        class="icon-question-circle"></i> {l s='How come my payment method(s) do(es) not appear in the order funnel ?' mod='hipay_enterprise'}
                <i class="icon-arrow-circle-o-down"></i></a>
        </div>
        <div id="collapseFive" class="panel-collapse collapse">
            <div class="panel-body">
                <ul>
                    <li>{l s='Check that the HiPay Enterprise module is properly set up with your currencies and your carriers in the “Improve > Payment > Preference” menu. When adding a carrier or a currency, you should always activate them in this setup screen.' mod='hipay_enterprise'}</li>
                    <li>{l s='Check in the HiPay module configuration that the payment method(s) is/are enabled for test countries and currencies.' mod='hipay_enterprise'}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-question">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseSix"><i
                        class="icon-question-circle"></i> {l s='How come Oney Facily Pay does not work?' mod='hipay_enterprise'}
                <i class="icon-arrow-circle-o-down"></i></a>
        </div>
        <div id="collapseSix" class="panel-collapse collapse">
            <div class="panel-body">
                {l s='If Oney payments do not work, check that the mappings for your categories and carriers are done correctly. This information is mandatory in the Oney payment workflow because the customer’s basket information is sent to the platform.' mod='hipay_enterprise'}
                {l s='To help you with your mappings, please refer to the corresponding documentation on our Developer Portal: https://developer.hipay.com/doc/hipay-enterprise-sdk-prestashop_1-6-1-7/' mod='hipay_enterprise'}
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-question">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseSeven"><i
                        class="icon-question-circle"></i> {l s='How can I do a manual capture and refund?' mod='hipay_enterprise'}
                <i class="icon-arrow-circle-o-down"></i></a>
        </div>
        <div id="collapseSeven" class="panel-collapse collapse">
            <div class="panel-body">
                {l s='If your module is configured as "Capture: Manual", you must make captures manually.' mod='hipay_enterprise'}

                <p>
                    {l s='Two possibilities are offered to you: either from your HiPay Enterprise back office, or directly from the order form on your PrestaShop site.' mod='hipay_enterprise'}
                </p>
                {l s='To get the detailed procedure, please refer to the module documentation on our Developer Portal: https://developer.hipay.com/doc/hipay-enterprise-sdk-prestashop_1-6-1-7/' mod='hipay_enterprise'}
            </div>
        </div>
    </div>
</div>







