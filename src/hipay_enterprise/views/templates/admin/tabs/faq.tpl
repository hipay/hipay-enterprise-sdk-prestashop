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
                    You need to generate <strong>API credentials</strong> to send requests to the HiPay Enterprise platform. To do so, go to the "Integration" section of your HiPay Enterprise back office, then to "Security Settings".
                </p>
                <p>
                To be sure that your credentials have the proper accessibility:
                </p>
                <p>
                    - Scroll down to "Api credentials".
                    - Click on the edit icon next to the credentials you want to use.
                </p>
                <p>
                    <strong>Private credentials</strong>
                </p>
                Your credentials must be granted to:

                <p>
                    <strong>Order</strong>
                </p>
                <ul>
                    <li>Create a payment page</li>
                    <li>Process an order through the API</li>
                    <li>Get transaction informations</li>
                </ul>
                <p>
                    <strong>Maintenance</strong>
                </p>
                <ul>
                    <li>Capture</li>
                    <li>Refund</li>
                    <li>Accept/Deny</li>
                    <li>Cancel</li>
                    <li>Finalize</li>
                </ul>

                <p>
                    <strong>Public credentials</strong>
                </p>
                Your credentials must be granted to:

                <p>
                    <strong>Tokenization</strong>
                </p>
                <ul>
                    <li>Tokenize a card</li>
                </ul>

                <p>
                    <strong>Order</strong>
                </p>

                <ul>
                    <li>Get transaction details with public credentials</li>
                    <li>Process an order through the API with public credentials</li>
                    <li>Create a payment page with public credentials</li>
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
                    In the module configuration, go to “Modules > Modules & Services”.
                    In the HiPay Enterprise configuration, click on the “Module Settings” tab.
                </p>
                <p>
                    If your module is in Test mode, you can specify the IDs in the Test area. If it is in Production mode, do the same in the Production area.
                </p>
                <p>
                    Enter the corresponding username, password and secret passphrase.
                </p>
                Public credentials are mandatory if you do not use the payment form hosted by HiPay.

                After specifying these identifiers, make a test payment to check that they are valid and that they have the proper rights.
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
                First, check if the notification URL is correctly entered in your HiPay Enterprise back office.
                In the configuration of the PrestaShop module, retrieve the callback URL in the "Module Settings" tab.
                </p>
                <p>
                Then, in your HiPay Enterprise back office, in the "Integration" section , click on "Notifications".
                    <ul>
                        <li>Notification URL: http: // www.[Your-domain.com] /index.php?fc=module&module=hipay_enterprise&controller=notify</li>
                        <li>Request method: HTTP POST</li>
                        <li>I want to be notified for the following transaction statuses: ALL</li>
                    </ul>
                </p>
                <p>
                    Then make a test payment.
                    From the "Notifications" section of your HiPay Enterprise back office, in the transaction details, you can also check the status of the call.
                </p>
                <p>
                    If notifications are sent, there may be an internal module error.
                    To check if an error occurred during the notification, check the hipay-error and hipay-callback logs.
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
                    <li>Make sure that your credentials are correctly set and that the module is in the mode you want (Test or Production).</li>
                    <li>Check that the related payment methods are activated in your contract(s).</li>
                    <li>Check the version of the installed module, and upgrade the module if the version is old.</li>
                    <li>Check HiPay logs to see if any errors appear. Then send these logs to the HiPay Support team.</li>
                    <li>Check that your servers are not behind a proxy. If so, provide the proxy information in the module configuration.</li>
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
                    <li>Check that the HiPay Enterprise module is properly set up with your currencies and your carriers in the “Improve > Payment > Preference” menu. When adding a carrier or a currency, you should always activate them in this setup screen.</li>
                    <li>Check in the HiPay module configuration that the payment method(s) is/are enabled for test countries and currencies.</li>
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
                If Oney payments do not work, check that the mappings for your categories and carriers are done correctly. This information is mandatory in the Oney payment workflow because the customer’s basket information is sent to the platform.
                To help you with your mappings, please refer to the corresponding documentation on our Developer Portal: https://developer.hipay.com/doc/hipay-enterprise-sdk-prestashop_1-6-1-7/
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
                If your module is configured as "Capture: Manual", you must make captures manually.

                <p>
                    Two possibilities are offered to you: either from your HiPay Enterprise back office, or directly from the order form on your PrestaShop site.
                </p>
                To get the detailed procedure, please refer to the module documentation on our Developer Portal: https://developer.hipay.com/doc/hipay-enterprise-sdk-prestashop_1-6-1-7/
         </div>
        </div>
    </div>
</div>







