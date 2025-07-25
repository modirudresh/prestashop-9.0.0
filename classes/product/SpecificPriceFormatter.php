<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

/**
 * This class is an extract of the ProductController->formatQuantityDiscounts() method
 * It is still legacy code and need some heavy refactoring.
 * This code has been extracted here for unit testing purpose
 *
 * @todo Refactor this class
 */
class SpecificPriceFormatterCore
{
    /**
     * Calculation method to be used (tax included or not?)
     *
     * @var bool
     */
    private $isTaxIncluded;

    /**
     * Specific price data array
     *
     * @var array
     */
    private $specificPrice;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var bool
     */
    private $displayDiscountPrice;

    /**
     * SpecificPriceFormatter constructor.
     *
     * @param array $specificPrice
     * @param bool $isTaxIncluded
     * @param Currency $currency
     * @param bool $displayDiscountPrice
     */
    public function __construct(array $specificPrice, bool $isTaxIncluded, Currency $currency, bool $displayDiscountPrice)
    {
        $this->specificPrice = $specificPrice;
        $this->isTaxIncluded = $isTaxIncluded;
        $this->currency = $currency;
        $this->displayDiscountPrice = $displayDiscountPrice;
    }

    /**
     * This is legacy code extracted from ProductController and it should be refactored
     *
     * @param float $initialPrice
     * @param float $tax_rate
     * @param float $ecotax_amount
     *
     * @return array
     */
    public function formatSpecificPrice($initialPrice, $tax_rate, $ecotax_amount)
    {
        $priceFormatter = new PriceFormatter();

        $this->specificPrice['quantity'] = &$this->specificPrice['from_quantity'];
        if ($this->specificPrice['price'] >= 0) {
            // The price may be directly set

            /* @var float $currentPriceDefaultCurrency current price with taxes in default currency */
            if ($this->isTaxIncluded) {
                $currentPriceDefaultCurrency = ($this->specificPrice['price'] * (1 + $tax_rate / 100)) + (float) $ecotax_amount;
            } else {
                $currentPriceDefaultCurrency = $this->specificPrice['price'] + (float) $ecotax_amount;
            }

            // Since this price is set in default currency,
            // we need to convert it into current currency
            $currentPriceCurrentCurrency = Tools::convertPrice($currentPriceDefaultCurrency, $this->currency, true);

            if ($this->specificPrice['reduction_type'] == 'amount') {
                if (!$this->specificPrice['reduction_tax'] && $this->isTaxIncluded) {
                    $this->specificPrice['reduction'] = $this->specificPrice['reduction'] * (1 + $tax_rate / 100);
                }
                if ($this->isTaxIncluded) {
                    $currentPriceCurrentCurrency -= $this->specificPrice['reduction'];
                    $this->specificPrice['reduction_with_tax'] = $this->specificPrice['reduction'];
                } else {
                    $currentPriceCurrentCurrency -= ($this->specificPrice['reduction_tax'] ? $this->specificPrice['reduction'] / (1 + $tax_rate / 100) : $this->specificPrice['reduction']);
                    $this->specificPrice['reduction_with_tax'] = $this->specificPrice['reduction_tax'] ? $this->specificPrice['reduction'] / (1 + $tax_rate / 100) : $this->specificPrice['reduction'];
                }
            } else {
                $currentPriceCurrentCurrency *= 1 - $this->specificPrice['reduction'];
            }
            $this->specificPrice['real_value'] = $initialPrice > 0 ? $initialPrice - $currentPriceCurrentCurrency : $currentPriceCurrentCurrency;
            $discountPrice = $initialPrice - $this->specificPrice['real_value'];

            if ($this->displayDiscountPrice) {
                if ($this->specificPrice['reduction_tax'] == 0 && !$this->specificPrice['price']) {
                    $this->specificPrice['discount'] = $priceFormatter->format($initialPrice - ($initialPrice * $this->specificPrice['reduction_with_tax']));
                } else {
                    $this->specificPrice['discount'] = $priceFormatter->format($initialPrice - $this->specificPrice['real_value']);
                }
            } else {
                $this->specificPrice['discount'] = $priceFormatter->format($this->specificPrice['real_value']);
            }
        } else {
            if ($this->specificPrice['reduction_type'] == 'amount') {
                if ($this->isTaxIncluded) {
                    $this->specificPrice['real_value'] = $this->specificPrice['reduction_tax'] == 1 ? $this->specificPrice['reduction'] : $this->specificPrice['reduction'] * (1 + $tax_rate / 100);
                } else {
                    $this->specificPrice['real_value'] = $this->specificPrice['reduction_tax'] == 0 ? $this->specificPrice['reduction'] : $this->specificPrice['reduction'] / (1 + $tax_rate / 100);
                }
                $this->specificPrice['reduction_with_tax'] = $this->specificPrice['reduction_tax'] ? $this->specificPrice['reduction'] : $this->specificPrice['reduction'] + ($this->specificPrice['reduction'] * $tax_rate) / 100;
                $discountPrice = $initialPrice - $this->specificPrice['real_value'];
                if ($this->displayDiscountPrice) {
                    if ($this->specificPrice['reduction_tax'] == 0 && !$this->specificPrice['price']) {
                        $this->specificPrice['discount'] = $priceFormatter->format($initialPrice - ($initialPrice * $this->specificPrice['reduction_with_tax']));
                    } else {
                        $this->specificPrice['discount'] = $priceFormatter->format($initialPrice - $this->specificPrice['real_value']);
                    }
                } else {
                    $this->specificPrice['discount'] = $priceFormatter->format($this->specificPrice['real_value']);
                }
            } else {
                $this->specificPrice['real_value'] = $this->specificPrice['reduction'] * 100;
                $discountPrice = $initialPrice - $initialPrice * $this->specificPrice['reduction'];
                if ($this->displayDiscountPrice) {
                    if ($this->specificPrice['reduction_tax'] == 0) {
                        $this->specificPrice['reduction_with_tax'] = $this->specificPrice['reduction'];
                        $this->specificPrice['discount'] = $priceFormatter->format($initialPrice - ($initialPrice * $this->specificPrice['reduction_with_tax']));
                    } else {
                        $this->specificPrice['reduction_with_tax'] = $this->specificPrice['reduction'];
                        $this->specificPrice['discount'] = $priceFormatter->format($initialPrice - ($initialPrice * $this->specificPrice['reduction']));
                    }
                } else {
                    $this->specificPrice['discount'] = $this->specificPrice['real_value'] . '%';
                }
            }
        }

        $this->specificPrice['save'] = $priceFormatter->format(($initialPrice * $this->specificPrice['quantity']) - ($discountPrice * $this->specificPrice['quantity']));
        $this->specificPrice['discounted_unit_price'] = $priceFormatter->format($discountPrice);
        $this->specificPrice['discounted_unit_price_raw'] = $discountPrice;
        $this->specificPrice['initial_price'] = $priceFormatter->format($initialPrice);
        $this->specificPrice['initial_price_raw'] = $initialPrice;

        return $this->specificPrice;
    }
}
