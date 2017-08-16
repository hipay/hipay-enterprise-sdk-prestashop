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

<div class="row" id="hipay-header">
    <div class="col-xs-12 col-sm-12 col-md-6 text-center">
        <img src="{$module_dir|escape:'html':'UTF-8'}/views/img/logo.png" id="payment-logo"/>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6 text-center">
        <h4>{l s='HiPay is a global online payment platform designed to handle all your payment needs' mod='hipay_enterprise'}</h4>
        <p>{l s='End-to-end customizable fraud protection, industry-leading data and analytics, seamless omnichannel support and automated financial reconciliation' mod='hipay_enterprise'}</p>
        <p>{l s='Visit our website to' mod='hipay_enterprise'} <a href='https://hipay.com/en/payment-solution-enterprise' target='_blank'>{l s='find out more' mod='hipay_enterprise'}</a></p>
    </div>
</div>

<div id="hipay-content">
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <p class="text-center">
                <a class="btn btn-primary" data-toggle="collapse" href="#hipay-marketing-content"
                   aria-expanded="false" aria-controls="hipay-marketing-content">
                    {l s='More info' mod='hipay_enterprise'}
                </a>
            </p>
            <div class="collapse" id="hipay-marketing-content">
                <div class="row">
                    <hr/>
                    <div class="col-md-5">
                        <h4>{l s='Expand your business internationally' mod='hipay_enterprise'}</h4>
                        <p>{l s='Optimize your conversion rates with one single integration to access a wide range of local and international payment methods so your customers can make purchases in their preferred currency and payment methods.' mod='hipay_enterprise'}</p>
                        <p><a href='https://hipay.com/en/payment-methods' target='_blank'>{l s='Over 100 currencies and 220 payment methods available' mod='hipay_enterprise'}</a></p>
                    </div>
                    <div class="col-md-5 col-md-offset-1">
                        <h4>{l s='Chose the best way to integrate your payment pages' mod='hipay_enterprise'}</h4>
                        <ul class="ul-spaced">
                            <li>{l s='Hosted Integration' mod='hipay_enterprise'} : {l s='HiPay hosts the payment page on its secure site. With this option, you will benefit from getting a single point of contact and personalized payment pages that are PCI-DSS compliant.' mod='hipay_enterprise'}</li>
                            <li>{l s='Iframe' mod='hipay_enterprise'} : {l s='A hybrid solution where the buyer remains on the merchant site to make a payment, but the information is entered on an iframe hosted by HiPay.' mod='hipay_enterprise'}</li>
                            <li>{l s='API integration' mod='hipay_enterprise'} : {l s='The payment page is hosted entirely on the merchant site. You will need PCI-DSS certification to allow credit card numbers to transit through your servers.' mod='hipay_enterprise'}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
