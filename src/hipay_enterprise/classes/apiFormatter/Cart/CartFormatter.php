<?php
/**
 * HiPay Enterprise SDK Prestashop.
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

use Cart as GlobalCart;
use HiPay\Fullservice\Gateway\Model\Cart\Cart;
use HiPay\Fullservice\Gateway\Model\Cart\Item;

require_once dirname(__FILE__).'/../../../lib/vendor/autoload.php';
require_once dirname(__FILE__).'/../ApiFormatterAbstract.php';
require_once dirname(__FILE__).'/../../helper/HipayMapper.php';
require_once dirname(__FILE__).'/../../helper/HipayHelper.php';

/**
 * Cart for payment request formatter.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class CartFormatter extends ApiFormatterAbstract
{
    /**
     * return mapped cart informations.
     *
     * @return string
     */
    public function generate()
    {
        $cart = new Cart();
        $this->mapRequest($cart);

        // Log the final cart payload before sending to HiPay
        $json = $cart->toJson();
        $this->module->getLogs()->logInfos("HipayPayload: " . $json);

        return $json;
    }

    protected function mapRequest(&$cart)
    {
        $cartSummary = $this->cart->getSummaryDetails();
        // Good items
        foreach ($this->cart->getProducts() as $product) {
            $item = $this->getGoodItem($product);
            $cart->addItem($item);
        }

        if ($this->cart->gift) {
            $item = $this->getWrappingGoodItem();
            $cart->addItem($item);
        }

        // Discount items
        $cartRules = $this->cart->getCartRules();
        $totalDiscount = 0.00;
        foreach ($cartRules as $rule) {
            $totalDiscount += Tools::ps_round($rule['value_real'], 3);
        }

        if ($totalDiscount > 0) {
            $item = $this->getDiscountItem();
            $cart->addItem($item);
        }

        // Fees items
        if ($item = $this->getFeesItem($cartSummary)) {
            $cart->addItem($item);
        }
    }

    private function getGoodItem($product)
    {
        $item = new Item();

        $ean = isset($product['ean13']) && $product['ean13'] !== '0' ? $product['ean13'] : null;
        $product_reference = HipayHelper::getProductRef($product);
        $type = 'good';
        $name = $product['name'];
        $quantity = (int) $product['cart_quantity'];

        $raw_unit_price = $product['price_wt'];
        $unit_price = number_format(Tools::ps_round($raw_unit_price, 3), 3, '.', '');
        $total_amount = number_format(Tools::ps_round($unit_price * $quantity, 3), 3, '.', '');
        $discount = 0.00;

        $this->module->getLogs()->logInfos("Product: {$name} | unit: {$unit_price} | qty: {$quantity} | total: {$total_amount}");

        $tax_rate = $product['rate'];
        $desc = $product['description_short'];
        $cat = $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']);

        $item->__constructItem(
            $ean,
            $product_reference,
            $type,
            $name,
            $quantity,
            $unit_price,
            $tax_rate,
            $discount,
            $total_amount,
            '',
            $desc,
            null,
            null,
            null,
            null,
            $cat,
            null
        );

        return $item;
    }

    private function getDiscountItem()
    {
        $product_reference = [];
        $name = [];
        $discount_description = [];
        $unit_price = 0.00;
        $total_amount = 0.00;

        foreach ($this->cart->getCartRules() as $disc) {
            $product_reference[] = HipayHelper::getDiscountRef($disc);
            $name[] = $disc['name'];
            $desc = $disc['description'];
            $discount_description[] = $desc;

            $discAmount = number_format(Tools::ps_round($disc['value_real'], 3), 3, '.', '');
            $unit_price += -1 * $discAmount;
            $total_amount += -1 * $discAmount;
        }

        $ref = join('/', $product_reference);
        $label = join('/', $name);
        $desc = join('/', $discount_description);
        $tax_rate = 0.00;
        $discount = 0.00;

        $item = Item::buildItemTypeDiscount(
            $ref,
            $label,
            number_format($unit_price, 2, '.', ''),
            $tax_rate,
            $discount,
            $desc,
            number_format($total_amount, 2, '.', '')
        );
        $item->setProductCategory(1);

        return $item;
    }

    private function getWrappingGoodItem()
    {
        $item = new Item();

        $product_reference = 'wrapping';
        $type = 'good';
        $name = 'wrapping';
        $quantity = 1;

        $amount = number_format($this->cart->getOrderTotal(true, GlobalCart::ONLY_WRAPPING), 2, '.', '');

        $item->__constructItem(
            null,
            $product_reference,
            $type,
            $name,
            $quantity,
            $amount,
            0,
            0,
            $amount,
            '',
            'gift wrapping',
            null,
            null,
            null,
            null,
            1,
            null
        );

        return $item;
    }

    private function getFeesItem($cartSummary)
    {
        $totalAmount = $this->cart->getTotalShippingCost();

        if ($totalAmount > 0) {
            $name = 'Multiple carriers';
            $productReference = 0;

            if ($cartSummary['carrier']->id) {
                $carrier = new Carrier($cartSummary['carrier']->id);
                $productReference = HipayHelper::getCarrierRef($carrier);
                $name = $cartSummary['carrier']->name;
            }

            $taxRate = $cartSummary['total_shipping_tax_exc'] === 0 ? 0.00 :
                round(($totalAmount / $cartSummary['total_shipping_tax_exc'] - 1), 2);
            $discount = 0.00;
            $amountFormatted = number_format($totalAmount, 2, '.', '');

            $item = Item::buildItemTypeFees(
                $productReference,
                $name,
                $amountFormatted,
                $taxRate,
                $discount,
                $amountFormatted
            );
            $item->setProductCategory(11);

            return $item;
        }

        return null;
    }
}
