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

use PrestaShop\Module\Mbo\Addons\ApiClient;
use PrestaShop\Module\Mbo\Exception\ExpectedServiceNotFoundException;
use PrestaShop\Module\Mbo\Helpers\ErrorHelper;
use PrestaShop\Module\Mbo\Module\ActionsManager;
use PrestaShop\Module\Mbo\Service\HookExceptionHolder;
use PrestaShop\PrestaShop\Adapter\Module\ModuleDataProvider;
use PrestaShop\PrestaShop\Core\File\Exception\FileNotFoundException;
use PrestaShop\PrestaShop\Core\Module\SourceHandler\SourceHandlerNotFoundException;

trait UseActionBeforeInstallModule
{
    /**
     * Hook actionBeforeInstallModule.
     *
     * @throws SourceHandlerNotFoundException
     * @throws FileNotFoundException
     */
    public function hookActionBeforeInstallModule(array $params): void
    {
        try {
            /** @var ModuleDataProvider|null $moduleDataProvider */
            $moduleDataProvider = $this->get('prestashop.adapter.data_provider.module');
            if (null === $moduleDataProvider) {
                throw new ExpectedServiceNotFoundException('Unable to get ModuleDataProvider');
            }
        } catch (\Exception $e) {
            ErrorHelper::reportError($e);

            return;
        }

        if (empty($params['moduleName']) || $moduleDataProvider->isOnDisk($params['moduleName'])) {
            return;
        }

        $moduleName = (string) $params['moduleName'];

        try {
            /** @var ApiClient|null $addonsClient */
            $addonsClient = $this->get(ApiClient::class);
            if (null === $addonsClient) {
                throw new ExpectedServiceNotFoundException('Unable to get Addons ApiClient');
            }
        } catch (\Exception $e) {
            ErrorHelper::reportError($e);

            return;
        }

        $moduleId = (int) \Tools::getValue('module_id');

        if (!$moduleId) {
            $addon = $addonsClient->getModuleByName($moduleName);

            if (null === $addon || !isset($addon->product->id_product)) {
                return;
            }

            $moduleId = (int) $addon->product->id_product;
        }

        try {
            /** @var ActionsManager|null $actionsManager */
            $actionsManager = $this->get(ActionsManager::class);
            if (null === $actionsManager) {
                throw new ExpectedServiceNotFoundException('Unable to get ActionsManager');
            }
        } catch (\Exception $e) {
            ErrorHelper::reportError($e);

            return;
        }

        try {
            $actionsManager->install($moduleId);
        } catch (\Exception $e) {
            /** @var HookExceptionHolder $hookExceptionHolder */
            $hookExceptionHolder = $this->get(HookExceptionHolder::class);
            if (null !== $hookExceptionHolder) {
                $hookExceptionHolder->holdException('actionBeforeInstallModule', $e);
            }

            throw $e;
        }
    }
}
