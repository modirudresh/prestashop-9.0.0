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

namespace PrestaShop\Module\Mbo\Traits\Hooks;

use PrestaShop\Module\Mbo\Addons\Provider\LinksProvider;
use PrestaShop\Module\Mbo\Exception\ExpectedServiceNotFoundException;
use PrestaShop\Module\Mbo\Helpers\ErrorHelper;
use Twig\Environment;

trait UseDisplayEmptyModuleCategoryExtraMessage
{
    /**
     * Hook displayEmptyModuleCategoryExtraMessage.
     * Add extra message to display for an empty modules' category.
     */
    public function hookDisplayEmptyModuleCategoryExtraMessage(array $params): string
    {
        $categoryName = $params['category_name'];

        try {
            /** @var Environment|null $twig */
            $twig = $this->get(Environment::class);
            /** @var LinksProvider|null $linksProvider */
            $linksProvider = $this->get(LinksProvider::class);

            if (null === $linksProvider || null === $twig) {
                throw new ExpectedServiceNotFoundException('Some services not found in UseDisplayEmptyModuleCategoryExtraMessage');
            }

            return $twig->render(
                '@Modules/ps_mbo/views/templates/hook/twig/module_manager_empty_category.html.twig', [
                    'categoryName' => $categoryName,
                    'categoryLink' => $linksProvider->getCategoryLink($categoryName),
                ]
            );
        } catch (\Exception $e) {
            ErrorHelper::reportError($e);

            return '';
        }
    }
}
