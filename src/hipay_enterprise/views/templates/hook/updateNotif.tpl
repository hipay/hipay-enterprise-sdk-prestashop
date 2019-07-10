{**
 * HiPay Enterprise SDK Prestashop
 *
 * 2019 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2019 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *}

{* Showing update block if needed *}
{if $updateNotif->getVersion() != $updateNotif->getNewVersion()}
    <section id="hipayupdate" class="panel widget loading">
        <div class="panel-heading">
            <i class="icon-warning"></i>
            {l s='HiPay information' mod='hipay_enterprise'}
        </div>

        <div class="row vertical-align">
            <div class="col-xs-6 col-sm-6 col-md-3 text-center">
                <img id="hipayNotifLogo" src="/modules/hipay_enterprise/views/img/logo.png" alt="HiPay Logo"/>
            </div>
            <div class="col-xs-18 col-sm-18 col-md-9 text-center">
                <p>{l s='There is a new version of HiPay Enterprise module available.' mod='hipay_enterprise'}
                    <a href="{$updateNotif->getReadMeUrl()}">
                        {l s='View version %s details' mod='hipay_enterprise' sprintf=$updateNotif->getNewVersion()}
                    </a>
                    {l s='or' mod='hipay_enterprise'}
                    <a href="{$updateNotif->getDownloadUrl()}">{l s='update now' mod='hipay_enterprise'}</a>.
                </p>
            </div>
        </div>
    </section>
    {* Script to move the block on the top of the column. Else it appears below everything *}
    <script>
        var notifUpdateBox = $('#hipayupdate');

        notifUpdateBox.remove();
        $('#hookDashboardZoneOne').prepend(notifUpdateBox);
    </script>
{/if}