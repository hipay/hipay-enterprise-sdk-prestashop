<?php
/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

/**
 * Form input builder
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
abstract class HipayFormInput
{
    /**
     * @return array
     */
    protected function generateFormNotice()
    {
        $params = array('col' => 6, 'offset' => 0, "class" => "alert alert-info");

        return $this->generateInput('free', 'input_split', null, $params);
    }

    protected function generateFormSplit()
    {
        $params = array('col' => 6, 'offset' => 0, "class" => "bloc");

        return $this->generateInput('free', 'input_split', null, $params);
    }

    protected function generateInput($type, $name, $label = false, $params = array())
    {
        $input = array(
            'type' => $type,
            'label' => $label,
            'name' => $name
        );

        if (is_array($params) === true) {
            foreach ($params as $key => $value) {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    protected function generateInputEmail($name, $title, $description)
    {
        return $this->generateInputText(
            $name,
            $title,
            array(
                'required' => true,
                'hint' => $description,
                'placeholder' => $this->module->l('email@domain.com'),
                'class' => 'fixed-width-xxl',
            )
        );
    }

    protected function generateInputFree($name, $label = false, $params = array())
    {
        return $this->generateInput('free', $name, $label, $params);
    }

    protected function generateInputText($name, $label = false, $params = array())
    {
        return $this->generateInput('text', $name, $label, $params);
    }

    protected function generateInputSelect($name, $label = false, $params = array())
    {
        return $this->generateInput('select', $name, $label, $params);
    }

    protected function generateInputCheckbox($name, $label = false, $params = array())
    {
        return $this->generateInput('checkbox', $name, $label, $params);
    }

    protected function generateInputTextarea($name, $label = false, $params = array())
    {
        return $this->generateInput('textarea', $name, $label, $params);
    }

    public function generateLegend($title, $icon = false)
    {
        return array('title' => $title, 'icon' => $icon,);
    }

    protected function generateSubmitButton($title, $params = array())
    {
        $input = array('title' => $title, 'type' => 'submit', 'class' => 'btn btn-default pull-right');

        if (is_array($params) === true) {
            foreach ($params as $key => $value) {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    protected function generateSwitchButton($name, $title, $params = array())
    {
        $input = array(
            'type' => 'switch',
            'label' => $title,
            'name' => $name,
            'is_bool' => true,
            'values' => array(
                array('id' => 'active_on', 'value' => 1, 'label' => $this->module->l('Enabled')),
                array('id' => 'active_off', 'value' => 0, 'label' => $this->module->l('Disabled')),
            ),
        );

        if (is_array($params) === true) {
            foreach ($params as $key => $value) {
                $input[$key] = $value;
            }
        }

        return $input;
    }
}
