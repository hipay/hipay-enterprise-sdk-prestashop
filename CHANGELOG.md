# Changelog

## Unreleased

- **Fix**: Fixed redirect controller when Hosted Page is selected
- **Fix**: Fixed PHP warning messages during installation

## 2.21.4

- **Fix**: Fixed upgrade script from previous version `2.21.3`

## 2.21.3

- **Fix**: Fixed notification process when using CRON
- **Fix**: Fixed issue about interruption during notification by CRON
- **Fix**: Fixed Prestashop credit note with discount in order
- **Fix**: Fixed division by 0 error on `getFeesItem` method, thanks to [Crouvizier](https://github.com/Crouvizier) on [PR #86](https://github.com/hipay/hipay-enterprise-sdk-prestashop/pull/86)
- **Fix**: Fixed SQL lock issue which was potentially unreleased
- **Fix**: Fixed errors during some upgrade, thanks to [clotairer](https://github.com/clotairer) on [PR #84](https://github.com/hipay/hipay-enterprise-sdk-prestashop/pull/84)
- **Fix**: Fixed issue about duplicate transaction with same Cart adding automatic cancellation

## 2.21.2

- **Fix**: Fixed polyfill lib maximum version for ps17

## 2.21.1

- **Fix**: Fixed ApplePay credentials detection in production
- **Fix**: Fixed name of global JS methods adding HiPay namespace
- **Fix**: Fixed name of smarty variables adding HiPay namespace

## 2.21.0

- **Add**: Added reference to pay on customer order details for `Mooney` / `Multibanco` payment means
- **Fix**: Fixed partial capture for `Alma` methods
- **Add**: Display cancel button
- **Add**: Added debug log option

## 2.20.0

- **Add**: Added support for new payment means:
  - Alma 3x
  - Alma 4x
- **Add**: Removed support for some unused payment means:
  - Astropay payment methods
  - Belfius Direct Net
  - ING Home'Pay
  - Yandex Money
- **Add**: Updated GiroPay Logo
- **Fix**: Fixed ApplePay credentials on production environment, thanks to [Aerue](https://github.com/Aerue) on [PR #83](https://github.com/hipay/hipay-enterprise-sdk-prestashop/pull/83)
- **Fix**: Fixed issue causing all card orders to be sent to ApplePay account

## 2.19.0

- **Add**: Added support for Prestashop 8
- **Add**: Added Hosted Fields usage instead of custom fields for local payment means
- **Fix**: Fixed order description trimming, thanks to [PetruOPower](https://github.com/PetruOPower) on [PR #82](https://github.com/hipay/hipay-enterprise-sdk-prestashop/pull/82)

## Version 2.18.0

- **Add**: Implement cron notifications
- **Fix**: shipping cost dispay in backoffice, thanks to [Lionel-dev](https://github.com/Lionel-dev) on [PR #80](https://github.com/hipay/hipay-enterprise-sdk-prestashop/pull/80)
- **Fix**: Add index on Notification table
- **Fix**: Handle multiple order from one cart
- **Fix**: Security improvement, thanks to [okom3pom](https://github.com/okom3pom) on [PR #78](https://github.com/hipay/hipay-enterprise-sdk-prestashop/pull/78)
- **Fix**: Error message on order cancellation, thanks to [axometeam](https://github.com/axometeam)
- **Fix**: Fixed CONTRIBUTING.md,  thanks to [PrestaEdit](https://github.com/PrestaEdit), [okom3pom](https://github.com/okom3pom), [Lionel-dev](https://github.com/Lionel-dev), [touchweb-vincent](https://github.com/touchweb-vincent)

## Version 2.17.2

- **Fix**: Fixed issue with MOTO payments

## Version 2.17.1

- **Fix**: Fixed issue with configuration getting too long when many languages were enabled

## Version 2.17.0

- **Add** : Removed Direct Post option on payment configuration. Use instead Hosted Fields or Hosted Page option on payment configuration
- **Add**: Added **Bancontact Credit Card / QR code** payment method

## Version 2.16.3

- **Fix** : Fixed response page when using Mooney payment method

## Version 2.16.2

- **Fix** : Fixed PHP compatibility error

## Version 2.16.1

- **Fix** : Fixed ApplePay payment method

## Version 2.16.0

- **Add** : Added new ApplePay payment method
- **Fix** : Fixed Italian translations

## Version 2.15.0

- **Add** : New MultiBanco and MBWay payment methods

## Version 2.14.0

- **Add** : Handle PrestaShop native refund form
- **Add** : Add option to disable order messages
- **Fix** : Fixed credit note generation
- **Fix** : Fixed maintenances handled by other gateway
- **Fix** : Fixed HostedPage display if api_v2 parameter is enabled for MB Way and Multibanco

## Version 2.13.9

- **Fix** : Update PHP SDK

## Version 2.13.8

- **Fix** : Update PHP SDK to fix some errors on PSD2 fields

## Version 2.13.7

- **Fix** : Replaced Sisal displayName and Logo by Mooney
- **Fix** : Enabled HostedPage v2 by default
- **Fix** : Fixed missing translation error
- **Fix** : Fixed total paid value error
- **Fix** : Added new payment method duplicating Oney credit-long to allow for multiple credit-long configurations

##  Version 2.13.6

- **Fix** : Fixed merchant_promotion for Oney
- **Fix** : Fixed default category for item fee with Oney
- **Fix** : Fixed Mbway phone

##  Version 2.13.5

- **Fix** : Fixed saving of HiPay module settings
- **Fix** : Fixed order creation on status 112

## Version 2.13.4

- **Fix** : Fixed order creation on notification for transactions in pending

## Version 2.13.3

- **Fix** : Fixed behavior on multi use token parameter
- **Fix** : Fixed delivery address for Oney payment methods when pick up in store
- **Fix** : Fixed description max length for Oney payment methods
- **Fix** : Increased Hipay API timeout

## Version 2.13.2

- **Fix** : Fixed Refund process for prestashop v1.7.7+
- **Fix** : Fixed Notification handling error when Hipay module was installed on the second shop
- **Fix** : Upgraded Hipay API timeout to 35 seconds

## Version 2.13.1

- **Fix** : recipient info parameter in order

## Version 2.13.0

- **Add** : Hosted Page v2 option for credit card on HostedPage
- **Add** : MBWay payment method
- **Fix** : jQuery conflicts with other modules

## Version 2.12.3

- **Fix**: Changed notification handling

## Version 2.12.2

- **Fix** 2.12.0 upgrade script

## Version 2.12.1

- **Fix** catch notation for PHP versions prior to 7

## Version 2.12.0

- **New** payment methods :
  - Credit Long - Oney
  - Illicado
- **Add**: Oney restrictions

## Version 2.11.0

- **Fix**: Changed order creation moment and notification handling

## Version 2.10.0

- **Add**: Added merchant promotion field for Oney With or Without Fees payment methods

## Version 2.9.3

- **Fix**: Notification reception when Prestashop receives HiPay's notifications in the wrong order
- **Fix**: Documentation regarding custom data

## Version 2.9.2

- **Fix**: Password and account changing date on DSP2

## Version 2.9.1

- **Fix**: Basket saving on payment failure

## Version 2.9.0

- **New**: Add Multibanco support

## Version 2.8.2

- Fix: Missing BDD upgrade script (refund and capture not working)
- Fix: MO/TO credentials retrieval
- Fix: Save oneclick cards on 116 and 118 notification

## Version 2.8.1

- Fix: Error message when opening an order on prestashop's admin panel
- Fix: Maintenance requests errors when no wrapping selected
- Fix: Challenge requests errors

## Version 2.8.0

- Added 3DSv2 handling

## Version 2.7.1

- Get payment method configuration from PHP SDK
- Add update notifications in admin dashboard

## Version 2.7.0

- Add MyBank Payment method
- Add Italian translations
- Fix: Handle wrapping gift in order with basket
- Fix: upgrade management
- Fix: capture form not displaying
- Fix: french and english translations

## Version 2.6.1

- Fix: shipping fees calculation

## Version 2.6.0

- Add error message on field (hosted fields)
- Fix: device fingerprint on hosted fields
- Update payment method configuration
- Fix: credit card number format on paste

## Version 2.5.2

- Fix: Bnppf with Prestashop 1.6

## Version 2.5.1

- Fix : Category and carrier mapping not saving

## Version 2.5.0

- Add one-click support for Hosted Page
- Refactoring one-click workflow
- Fix amex one-click
- Add minimum prestashop support

## Version 2.4.1

- Fix missing month in credit card form

## Version 2.4.0

- Get payment product from SDK JS
- Configurable SDK JS url
- Fix: amount in basket on maintenance request
- Fix: Oney payment method
- Fix: cardholder For Amex

## Version 2.3.2

- Fix : Refund and capture with an update of product price or discount

## Version 2.3.1

- Fix : refund for local payment method

## Version 2.3.0

- Add support for hosted fields
- Fix : switch klarna to klarnainvoice

## Version 2.2.7

- Fix : unnecessary mandatory CVV for Maestro card

## Version 2.2.6

- [#64](https://github.com/hipay/hipay-enterprise-sdk-prestashop/issues/64) Fix issue [#64]
- Remove electronic signature from SDD
- Fix : Js error on backend notification pop-up

## Version 2.2.5

- Improve CI
- Refactor functional tests
- Fix : Proxy settings

## Version 2.2.4

- Fix : Bug on BCMC card form

## Version 2.2.3

- Fix : Bug on notification

## Version 2.2.2

- Fix : Credit card form autofill not filling properly
- Fix : Bug with discount in basket (PHP7.1 silent conversion error)

## Version 2.2.1

- Fix : update Mastercard bin range
- Fix : Add message to specify that Oneclick payment can only be used with Api mode

## Version 2.2.0

- Add Oney gift card payment support
- Add support for several hashing algorithm for notification
- Add support for notify_url

## Version 2.1.5

- Fix: Fix Js error

## Version 2.1.4

- Fix: credit card block are displaying even if no credit card payment are activated

## Version 2.1.3

- Fix local payment with hosted order

## Version 2.1.2

- Fix translations

## Version 2.1.1

- Fix error 500 on refused notification

## Version 2.1.0

- Fix link in PrestaShop BO (Module configuration page)
- Add upgrade script
- Fix Oneclick payment bug on 1.6 and 1.7

## Version 2.0.5

- Add payment method **Bnp personal Finance**
- Add log for concurrent transactions
- Override "OrderExists" method for concurrent notifications (Bug with prestashop cache)
- Add translation support for payment method name in front-office.
- Add FAQ translation
- Change form handling for passphrase

## Version 2.0.4

- Fix Product key for Prestashop addons

## Version 2.0.3

- Fix template FAQ Tab

## Version 2.0.2

- Fix translations FR and EN

## Version 2.0.1-beta

- Fix refund with multi currencies
- Fix module installation

## Version 2.0.0-beta

- Project initialization
