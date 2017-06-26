<?php

/* You need two sets of credentials in order to
 * make this integration work:
 * - Public credentials: they will be used by 
 *   the HiPay Fullservice SDK for JavaScript
 *   in order to tokenize the card number.
 *   they will appear in clear in the JS source code.
 *   Their scope is limited to tokenization only.
 * 
 * - Private credentials: they will be used by the
 *   HiPay Fullservice SDK for PHP in order to create
 *   a charge. They will not appear on the client side.
 *   Their scope is much wider and they must not be
 *   accessible by third party. In your own case, you
 *   may use any other server-side technology. Not 
 *   necessarily the PHP SDK.
 */

$credentials = [
  
  /* You can create your credentials in your HiPay
   * Fullservice back office > Integration >
   * Security Settings.
   */

  /* PUBLIC CREDENTIALS
   * To create such credentials, you must
   * set credentials accessibility to "Public"
   * and at least check the "Tokenization" grant.
   */
  'public' => [
    'username' => '',
    'password' => '',
  ],

  /* PRIVATE CREDENTIALS
   * To create such credentials, you must
   * set credentials accessibility to "Private"
   * and at least check "Process an order through the API" 
   */
  'private' => [
    'username' => '',
    'password' => '',
  ]

];
