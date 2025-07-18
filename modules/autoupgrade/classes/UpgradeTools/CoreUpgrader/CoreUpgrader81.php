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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class CoreUpgrader81 extends CoreUpgrader80
{
    /**
     * @throws UpgradeException
     */
    public function writeNewSettings(): void
    {
        $parametersPath = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/app/config/parameters.php';
        $parameters = require $parametersPath;
        if (!isset($parameters['parameters']['api_public_key']) || isset($parameters['parameters']['api_private_key'])) {
            $this->logger->debug($this->container->getTranslator()->trans('API keys not present in parameters, generating'));
            $privateKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);

            $this->logger->debug($this->container->getTranslator()->trans('Keys generated using openssl_pkey_new, exporting private and public keys'));
            openssl_pkey_export($privateKey, $apiPrivateKey);
            $apiPublicKey = openssl_pkey_get_details($privateKey)['key'];
            $parameters['parameters']['api_public_key'] = $apiPublicKey;
            $parameters['parameters']['api_private_key'] = $apiPrivateKey;

            $parametersContent = sprintf('<?php return %s;', var_export($parameters, true));
            $this->logger->debug($this->container->getTranslator()->trans('Updating parameters file'));
            if (!file_put_contents($parametersPath, $parametersContent)) {
                throw new UpgradeException($this->container->getTranslator()->trans('Unable to migrate parameters'));
            }

            if (function_exists('opcache_invalidate')) {
                $this->logger->debug($this->container->getTranslator()->trans('Invalidating opcache for parameters file'));
                opcache_invalidate($parametersPath);
            }
            $this->logger->debug($this->container->getTranslator()->trans('Parameters file updated'));
        }
    }
}
