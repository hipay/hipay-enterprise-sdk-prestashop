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

        return $cart->toJson();
    }

    /**
     * map prestashop cart informations to request fields.
     *
     * @param Cart &$cart
     *
     * @return void
     */
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
        if (!empty($this->cart->getCartRules())) {
            $item = $this->getDiscountItem();
            $cart->addItem($item);
        }

        // Fees items
        if ($item = $this->getFeesItem($cartSummary)) {
            $cart->addItem($item);
        }
    }

    /**
     * @return Item
     *
     * @throws Exception
     */
    private function getWrappingGoodItem()
    {
        $item = new Item();

        $product_reference = 'wrapping';
        $type = 'good';

        $name = 'wrapping';
        $quantity = 1;

        $unit_price = $total_amount = $this->cart->getOrderTotal(true, GlobalCart::ONLY_WRAPPING);

        $item->__constructItem(
            null,
            $product_reference,
            $type,
            $name,
            $quantity,
            $unit_price,
            0,
            0,
            $total_amount,
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

    /**
     * create a good item from product line informations.
     *
     * @param array<string,mixed> $product
     *
     * @return Item
     */
    private function getGoodItem($product)
    {
        $item = new Item();

        $european_article_numbering = null;
        if (isset($product['ean13']) && '0' != $product['ean13']) {
            $european_article_numbering = $product['ean13'];
        }

        $product_reference = HipayHelper::getProductRef($product);
        $type = 'good';
        $name = $product['name'];
        $quantity = (int) $product['cart_quantity'];

        $discount = -1 *
            Tools::ps_round(
                ($product['price_without_reduction'] * $product['cart_quantity']) -
                ($product['price_with_reduction'] * $product['cart_quantity']),
                2
            );
        $total_amount = Tools::ps_round($product['total_wt'], 2);
        $unit_price = Tools::ps_round(($total_amount - $discount) / $quantity, 3);

        $tax_rate = $product['rate'];
        $discount_description = '';
        $product_description = $product['description_short'];
        $delivery_method = null;
        $delivery_company = null;
        $delivery_delay = null;
        $delivery_number = null;
        $product_category = $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']);
        $shop_id = null;

        $item->__constructItem(
            $european_article_numbering,
            $product_reference,
            $type,
            $name,
            $quantity,
            $unit_price,
            $tax_rate,
            $discount,
            $total_amount,
            $discount_description,
            $product_description,
            $delivery_method,
            $delivery_company,
            $delivery_delay,
            $delivery_number,
            $product_category,
            $shop_id
        );

        return $item;
    }

    /**
     * create a discount item from discount line informations.
     *
     * @return Item
     */
    private function getDiscountItem()
    {
        $product_reference = [];
        $name = [];
        $unit_price = 0;
        $discount_description = [];
        $total_amount = 0;

        foreach ($this->cart->getCartRules() as $disc) {
            $product_reference[] = HipayHelper::getDiscountRef($disc);
            $name[] = $disc['name'];
            $unit_price += -1 * Tools::ps_round($disc['value_real'], 2);
            $tax_rate = 0.00;
            $discount = 0.00;
            $discount_description[] = $disc['description'];
            $total_amount += -1 * Tools::ps_round($disc['value_real'], 2);
        }
        $product_reference = join('/', $product_reference);
        $name = join('/', $name);
        $discount_description = join('/', $discount_description);

        $item = Item::buildItemTypeDiscount(
            $product_reference,
            $name,
            $unit_price,
            $tax_rate,
            $discount,
            $discount_description,
            $total_amount
        );
        // forced category
        $item->setProductCategory(1);

        return $item;
    }

    /**
     * create a Fees item from cart informations.
     *
     * @param array<string,mixed> $cartSummary
     *
     * @return Item|null
     */
    private function getFeesItem($cartSummary)
    {
        if ($totalAmount = $this->cart->getTotalShippingCost()) {
            if ($cartSummary['carrier']->id) {
                // One carier
                $carrier = new Carrier($cartSummary['carrier']->id);
                $productReference = HipayHelper::getCarrierRef($carrier);
                $name = $cartSummary['carrier']->name;
            } else {
                // multiples carriers
                $productReference = 0;
                $name = 'Multiple carriers';
            }

            $taxRate = round(($totalAmount / $cartSummary['total_shipping_tax_exc'] - 1), 2);
            $discount = 0.00;

            $item = Item::buildItemTypeFees(
                $productReference,
                $name,
                $totalAmount,
                $taxRate,
                $discount,
                $totalAmount
            );
            // forced category
            $item->setProductCategory(11);

            return $item;
        }

        return null;
    }
}
