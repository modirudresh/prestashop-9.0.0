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

namespace PrestaShop\PrestaShop\Core\Addon\Theme;

use Employee;
use ErrorException;
use Exception;
use Language;
use PrestaShop\PrestaShop\Core\Addon\AddonManagerInterface;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Addon\Theme\Exception\ThemeAlreadyExistsException;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use PrestaShop\PrestaShop\Core\Context\ApiClientContext;
use PrestaShop\PrestaShop\Core\Domain\Theme\Exception\FailedToEnableThemeModuleException;
use PrestaShop\PrestaShop\Core\Domain\Theme\Exception\ThemeConstraintException;
use PrestaShop\PrestaShop\Core\Exception\FileNotFoundException;
use PrestaShop\PrestaShop\Core\Foundation\Filesystem\FileSystem as PsFileSystem;
use PrestaShop\PrestaShop\Core\Image\ImageTypeRepository;
use PrestaShop\PrestaShop\Core\Module\HookConfigurator;
use PrestaShop\PrestaShop\Core\Translation\Storage\Finder\TranslationFinder;
use PrestaShopBundle\Service\TranslationService;
use PrestaShopLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shop;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Yaml\Parser;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tools;

class ThemeManager implements AddonManagerInterface
{
    public ?string $sandbox;

    private readonly TranslationFinder $translationFinder;

    private readonly LoggerInterface $logger;

    private readonly ApiClientContext $apiClientContext;

    public function __construct(
        private Shop $shop,
        private readonly ConfigurationInterface $configuration,
        private readonly ThemeValidator $themeValidator,
        private readonly TranslatorInterface $translator,
        private readonly Employee $employee,
        private readonly Filesystem $filesystem,
        private Finder $finder,
        private readonly HookConfigurator $hookConfigurator,
        private readonly ThemeRepository $themeRepository,
        private readonly ImageTypeRepository $imageTypeRepository,
        ?LoggerInterface $logger = null,
        ?ApiClientContext $apiClientContext = null,
    ) {
        $this->translationFinder = new TranslationFinder();
        $this->logger = $logger ?? new NullLogger();
        $this->apiClientContext = $apiClientContext ?? new ApiClientContext(null);
    }

    /**
     * Add new theme from zipball. This will unzip the file and move the content
     * to the right locations.
     * A theme can bundle modules, resources, documentation, email templates and so on.
     *
     * @param string $source The source can be a module name (installed from either local disk or addons.prestashop.com).
     *                       or a location (url or path to the zip file)
     *
     * @return bool true for success
     */
    public function install($source)
    {
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            $source = Tools::createFileFromUrl($source);
        }
        if (preg_match('/\.zip$/', $source)) {
            $this->installFromZip($source);
        }

        return true;
    }

    /**
     * Remove all theme files, resources, documentation and specific modules.
     *
     * @param string $name The source can be a module name (installed from either local disk or addons.prestashop.com).
     *                     or a location (url or path to the zip file)
     *
     * @return bool true for success
     */
    public function uninstall($name)
    {
        $apiClientHasPermission = $this->apiClientContext->getApiClient() !== null && $this->apiClientContext->getApiClient()->hasScope('theme_write');
        if (!$this->employee->can('delete', 'AdminThemes') && !$apiClientHasPermission) {
            return false;
        }

        /** @var Theme $theme */
        $theme = $this->themeRepository->getInstanceByName($name);
        $theme->onUninstall();

        $this->filesystem->remove($theme->getDirectory());

        return true;
    }

    /**
     * Download new files from source, backup old files, replace files with new ones
     * and execute all necessary migration scripts form current version to the new one.
     *
     * @param string $name
     * @param string $version the version you want to up upgrade to
     * @param string $source if the upgrade is not coming from addons, you need to specify the path to the zipball
     *
     * @return bool true for success
     */
    public function upgrade($name, $version, $source = null)
    {
        return true;
    }

    /**
     * Actions to perform when switching from another theme to this one.
     * Example:
     *    - update configuration
     *    - enable/disable modules.
     *
     * @param string $name The theme name to enable
     * @param bool $force bypass user privilege checks
     *
     * @return bool True for success
     */
    public function enable($name, $force = false)
    {
        $apiClientHasPermission = $this->apiClientContext->getApiClient() !== null && $this->apiClientContext->getApiClient()->hasScope('theme_write');
        if (!$force && !$this->employee->can('edit', 'AdminThemes') && !$apiClientHasPermission) {
            return false;
        }

        /* if file exits, remove it and use YAML configuration file instead */
        @unlink($this->configuration->get('_PS_CONFIG_DIR_') . 'themes/' . $name . '/shop' . $this->shop->id . '.json');

        /** @var Theme $theme */
        $theme = $this->themeRepository->getInstanceByName($name);
        if (!$this->themeValidator->isValid($theme)) {
            return false;
        }

        $this->disable($this->shop->theme_name);

        try {
            $this->doCreateCustomHooks($theme->get('global_settings.hooks.custom_hooks', []))
                ->doApplyConfiguration($theme->get('global_settings.configuration', []))
                ->doDisableModules($theme->get('global_settings.modules.to_disable', []))
                ->doEnableModules($theme->getModulesToEnable())
                ->doResetModules($theme->get('global_settings.modules.to_reset', []))
                ->doApplyImageTypes($theme->get('global_settings.image_types', []), $name)
                ->doHookModules($theme->get('global_settings.hooks.modules_to_hook', []));

            $theme->onEnable();

            $this->shop->theme_name = $theme->getName();
            $this->shop->update();

            $this->saveTheme($theme);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        return true;
    }

    /**
     * Actions to perform when switching from this theme to another one.
     *
     * @param string $name The theme name to enable
     *
     * @return bool True for success
     */
    public function disable($name)
    {
        /** @var Theme $theme */
        $theme = $this->themeRepository->getInstanceByName($name);
        $theme->getModulesToDisable();

        $this->doDisableModules($theme->getModulesToDisable());

        @unlink($this->configuration->get('_PS_CONFIG_DIR_') . 'themes/' . $name . '/shop' . $this->shop->id . '.json');

        return true;
    }

    /**
     * Actions to perform to restore default settings.
     *
     * @param string $themeName The theme name to reset
     *
     * @return bool True for success
     */
    public function reset($themeName)
    {
        return $this->disable($themeName) && $this->enable($themeName);
    }

    /**
     * Returns the last error, if found.
     *
     * @param string $themeName The technical theme name
     *
     * @return void
     */
    public function getError($themeName)
    {
    }

    /**
     * Get all errors of theme install.
     *
     * @param string $themeName The technical theme name
     *
     * @return array|string|bool
     */
    public function getErrors($themeName)
    {
        return $this->themeValidator->getErrors($themeName);
    }

    /**
     * @param array $hooks
     *
     * @return self
     */
    private function doCreateCustomHooks(array $hooks): self
    {
        foreach ($hooks as $hook) {
            $this->hookConfigurator->addHook(
                $hook['name'],
                $hook['title'],
                $hook['description']
            );
        }

        return $this;
    }

    /**
     * @param array $configuration
     *
     * @return self
     */
    private function doApplyConfiguration(array $configuration): self
    {
        foreach ($configuration as $key => $value) {
            $this->configuration->set($key, $value);
        }

        return $this;
    }

    /**
     * @param array $modules
     *
     * @return self
     *
     * @throws Exception
     */
    private function doDisableModules(array $modules): self
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        foreach ($modules as $moduleName) {
            if ($moduleManager->isInstalled($moduleName) && $moduleManager->isEnabled($moduleName)) {
                $moduleManager->disable($moduleName);
            }
        }

        return $this;
    }

    /**
     * @param array $modules
     *
     * @return self
     *
     * @throws FailedToEnableThemeModuleException
     */
    private function doEnableModules(array $modules): self
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        foreach ($modules as $moduleName) {
            if (!$moduleManager->isInstalled($moduleName)
                && !$moduleManager->install($moduleName)
            ) {
                throw new FailedToEnableThemeModuleException($moduleName, $moduleManager->getError($moduleName));
            }
            if (!$moduleManager->isEnabled($moduleName)) {
                $moduleManager->enable($moduleName);
            }
        }

        return $this;
    }

    /**
     * Reset the modules received in parameters if they are installed and enabled.
     *
     * @param string[] $modules
     *
     * @return self
     */
    private function doResetModules(array $modules): self
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        foreach ($modules as $moduleName) {
            if ($moduleManager->isInstalled($moduleName)) {
                $moduleManager->reset($moduleName);
            }
        }

        return $this;
    }

    /**
     * @param array $hooks
     *
     * @return self
     */
    private function doHookModules(array $hooks): self
    {
        $this->hookConfigurator->setHooksConfiguration($hooks);

        return $this;
    }

    /**
     * @param array $types
     *
     * @return self
     */
    private function doApplyImageTypes(array $types, ?string $theme_name): self
    {
        $this->imageTypeRepository->setTypes($types, $theme_name);

        return $this;
    }

    /**
     * @param string $source
     *
     * @throws ThemeAlreadyExistsException
     * @throws ThemeConstraintException
     */
    private function installFromZip($source)
    {
        /** @var Finder $finderClass */
        $finderClass = $this->finder::class;
        $this->finder = $finderClass::create();

        $sandboxPath = $this->getSandboxPath();
        Tools::ZipExtract($source, $sandboxPath);

        $themeConfigurationFile = $sandboxPath . '/config/theme.yml';

        if (!file_exists($themeConfigurationFile)) {
            $this->logger->error(sprintf('Configuration file not found %s', $themeConfigurationFile));
            throw new ThemeConstraintException('Missing theme configuration file which should be in located in /config/theme.yml', ThemeConstraintException::MISSING_CONFIGURATION_FILE);
        }

        $theme_data = (new Parser())->parse(file_get_contents($themeConfigurationFile));

        $theme_data['directory'] = $sandboxPath;

        try {
            $theme = new Theme($theme_data);
        } catch (ErrorException $exception) {
            $errorMessage = sprintf('Theme data %s is not valid', var_export($theme_data, true));
            $this->logger->error($errorMessage);
            throw new ThemeConstraintException($errorMessage, ThemeConstraintException::INVALID_DATA, $exception);
        }

        if (!$this->themeValidator->isValid($theme)) {
            $this->filesystem->remove($sandboxPath);

            $this->themeValidator->getErrors($theme->getName());

            $errorMessage = sprintf('Theme configuration file is not valid - %s', var_export($this->themeValidator->getErrors($theme->getName()), true));
            $this->logger->error($errorMessage);
            throw new ThemeConstraintException($errorMessage, ThemeConstraintException::INVALID_CONFIGURATION);
        }

        $module_root_dir = $this->configuration->get('_PS_MODULE_DIR_');
        $modules_parent_dir = $sandboxPath . '/dependencies/modules';
        if ($this->filesystem->exists($modules_parent_dir)) {
            $module_dirs = $this->finder->directories()
                ->in($modules_parent_dir)
                ->depth('== 0');
            /** @var SplFileInfo $dir */
            foreach (iterator_to_array($module_dirs) as $dir) {
                $destination = $module_root_dir . basename($dir->getFileName());
                if (!$this->filesystem->exists($destination)) {
                    $this->filesystem->mkdir($destination);
                }
                $this->filesystem->mirror($dir->getPathName(), $destination);
            }
            $this->filesystem->remove($modules_parent_dir);
        }

        $themePath = $this->configuration->get('_PS_ALL_THEMES_DIR_') . $theme->getName();
        if ($this->filesystem->exists($themePath)) {
            $errorMessage = $this->translator->trans('There is already a theme named ' . $theme->getName() . ' in your themes/ folder. Remove it if you want to continue.', [], 'Admin.Design.Notification');
            $this->logger->error($errorMessage);
            throw new ThemeAlreadyExistsException($theme->getName(), $errorMessage);
        }

        $this->filesystem->mkdir($themePath);
        $this->filesystem->mirror($sandboxPath, $themePath);

        $this->importTranslationToDatabase($theme);

        $this->filesystem->remove($sandboxPath);
    }

    private function getSandboxPath()
    {
        if (!isset($this->sandbox)) {
            $this->sandbox = $this->configuration->get('_PS_CACHE_DIR_') . 'sandbox/' . uniqid() . '/';
            $this->filesystem->mkdir($this->sandbox, PsFileSystem::DEFAULT_MODE_FOLDER);
        }

        return $this->sandbox;
    }

    /**
     * @param Theme $theme
     */
    public function saveTheme($theme)
    {
        $jsonConfigFolder = $this->configuration->get('_PS_CONFIG_DIR_') . 'themes/' . $theme->getName();
        if (!$this->filesystem->exists($jsonConfigFolder) && !is_dir($jsonConfigFolder)) {
            mkdir($jsonConfigFolder, PsFileSystem::DEFAULT_MODE_FOLDER, true);
        }

        file_put_contents(
            $jsonConfigFolder . '/shop' . $this->shop->id . '.json',
            json_encode($theme->get(null))
        );
    }

    /**
     * Import translation from Theme to Database.
     *
     * @param Theme $theme
     */
    private function importTranslationToDatabase(Theme $theme)
    {
        global $kernel; // sf kernel

        if (!(null !== $kernel && $kernel instanceof \Symfony\Component\HttpKernel\KernelInterface)) {
            return;
        }

        $translationService = $kernel->getContainer()->get('prestashop.service.translation');
        $themeProvider = $kernel->getContainer()->get('prestashop.translation.theme_provider');

        $themeName = $theme->getName();
        $themePath = $this->configuration->get('_PS_ALL_THEMES_DIR_') . $themeName;
        $translationFolder = $themePath . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR;

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $locale = $language['locale'];

            // retrieve Lang doctrine entity
            try {
                $lang = $translationService->findLanguageByLocale($locale);
            } catch (Exception) {
                PrestaShopLogger::addLog('ThemeManager->importTranslationToDatabase() - Locale ' . $locale . ' does not exists');

                continue;
            }

            // check if translation dir for this lang exists
            if (!is_dir($translationFolder . $locale)) {
                continue;
            }

            try {
                // construct a new catalog for this lang and import in database if key and message are different
                $messageCatalog = $this->translationFinder->getCatalogueFromPaths(
                    [$translationFolder . $locale],
                    $locale
                );

                // get all default domain from catalog
                $allDomains = $this->getDefaultDomains($locale, $themeProvider);

                // do the import
                $this->handleImport($translationService, $messageCatalog, $allDomains, $lang, $locale, $themeName);
            } catch (FileNotFoundException) {
                // if the directory is there but there are no files, do nothing
            }
        }
    }

    /**
     * Get all default domain from catalog.
     *
     * @param string $locale
     * @param \PrestaShopBundle\Translation\Provider\ThemeProvider $themeProvider
     *
     * @return array
     */
    private function getDefaultDomains($locale, $themeProvider)
    {
        $allDomains = [];

        $defaultCatalogue = $themeProvider
            ->setLocale($locale)
            ->getDefaultCatalogue();

        if (empty($defaultCatalogue)) {
            return $allDomains;
        }

        $defaultCatalogue = $defaultCatalogue->all();

        if (empty($defaultCatalogue)) {
            return $allDomains;
        }

        foreach (array_keys($defaultCatalogue) as $domain) {
            // AdminCatalogFeature.fr-FR to AdminCatalogFeature
            $domain = str_replace('.' . $locale, '', $domain);

            $allDomains[] = $domain;
        }

        return $allDomains;
    }

    /**
     * @param TranslationService $translationService
     * @param MessageCatalogue $messageCatalog
     * @param array $allDomains
     * @param \PrestaShopBundle\Entity\Lang $lang
     * @param string $locale
     * @param string $themeName
     */
    private function handleImport(TranslationService $translationService, MessageCatalogue $messageCatalog, $allDomains, $lang, $locale, $themeName)
    {
        foreach ($messageCatalog->all() as $domain => $messages) {
            $domain = str_replace('.' . $locale, '', $domain);

            if (in_array($domain, $allDomains)) {
                continue;
            }

            foreach ($messages as $key => $message) {
                if ($key !== $message) {
                    $translationService->saveTranslationMessage($lang, $domain, $key, $message, $themeName);
                }
            }
        }
    }
}
