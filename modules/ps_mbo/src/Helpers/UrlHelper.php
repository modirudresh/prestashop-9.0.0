<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
declare(strict_types=1);

namespace PrestaShop\Module\Mbo\Helpers;

class UrlHelper
{
    public static function transformToAbsoluteUrl(string $url): string
    {
        if (\Validate::isAbsoluteUrl($url)) {
            return $url;
        }

        return rtrim(Config::getShopUrl(false), '/') . DIRECTORY_SEPARATOR . ltrim($url, '/');
    }

    public static function getQueryParameterValue(string $url, string $key): string
    {
        $parameters = [];
        $query = parse_url($url, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str($query, $parameters);
        }

        return $parameters[$key] ?? '';
    }
}
