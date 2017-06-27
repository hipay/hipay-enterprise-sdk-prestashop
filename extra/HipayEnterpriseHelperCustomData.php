<?php

/**
 *       This custom class must be placed in the folder /hipay_enterprise/classes/helper
 *       You have to personalize  the method getCustomData and return an json of your choice.
 *
 */
class HipayEnterpriseHelperCustomData
{

    /**
     *  Return yours customs datas in a json for gateway transaction request
     *
     * @param array $payment
     * @param float $amount
     *
     */
    public function getCustomData($cart, $params)
    {
        $customData = array();

        // An example of adding custom data
        if ($cart) {
            $customData['my_field_custom_1'] = $cart->recyclable;
        }

        return $customData;
    }
}