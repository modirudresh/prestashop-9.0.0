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

use PrestaShop\Autoload\PrestashopAutoload;
use PrestaShop\PrestaShop\Adapter\ContainerFinder;
use PrestaShop\PrestaShop\Adapter\LegacyLogger;
use PrestaShop\PrestaShop\Adapter\Module\ModuleDataProvider;
use PrestaShop\PrestaShop\Adapter\Module\Repository\ModuleRepository;
use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException;
use PrestaShop\PrestaShop\Core\Foundation\Filesystem\FileSystem;
use PrestaShop\PrestaShop\Core\Module\Legacy\ModuleInterface;
use PrestaShop\PrestaShop\Core\Module\ModuleOverrideChecker;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Security\Permission;
use PrestaShop\TranslationToolsBundle\Translation\Helper\DomainHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SfFileSystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment;

abstract class ModuleCore implements ModuleInterface
{
    /** @var int|null Module ID */
    public $id = null;

    /** @var string Version */
    public $version;
    public $database_version;

    /** @var array filled with known compliant PS versions */
    public $ps_versions_compliancy = [];

    /** @var array filled with modules needed for install */
    public $dependencies = [];

    /** @var string|int|null Unique name */
    public $name;

    /** @var string Human name */
    public $displayName;

    /** @var string A little description of the module */
    public $description;

    /**
     * @var string Text to display when ask for confirmation on uninstall action
     */
    public $confirmUninstall = '';

    /** @var string author of the module */
    public $author;

    /** @var string URI author of the module */
    public $author_uri = '';

    /** @var string Module key provided by addons.prestashop.com */
    public $module_key = '';

    /**
     * @var bool Set to true to enable bootstrap theme on configuration page
     */
    public $bootstrap = false;

    public $description_full;

    public $additional_description;

    public $compatibility;

    public $nb_rates;

    public $avg_rate;

    public $badges;

    /** @var string */
    public $message = '';

    /** @var string */
    public $logo = '';

    /** @var array */
    public $options;

    /** @var int need_instance */
    public $need_instance = 1;

    /** @var int */
    public $is_configurable = 0;

    /** @var string Admin tab corresponding to the module */
    public $tab = null;

    /** @var bool Status */
    public $active = false;

    /** @var string Fill it if the module is installed but not yet set up */
    public $warning;

    /**
     * @deprecated since 9.0.0 - This functionality was disabled. Attribute will be completely removed
     * in the next major. There is no replacement, all clients should have the same experience.
     *
     * @var int enable_device */
    public $enable_device = 7;

    /** @var array to store the limited country */
    public $limited_countries = [];

    /** @var array names of the controllers */
    public $controllers = [];

    /** @var bool */
    public $installed;

    /** @var bool */
    public $onclick_option = false;

    /** @var string|null */
    public $url = null;

    /** @var string|null */
    public $image = null;

    /** @var string|null */
    public $price = null;

    /** @var string|null Can be addonsPartner|addonsNative */
    public $type = null;

    /** @var array used by AdminTab to determine which lang file to use (admin.php or module lang file) */
    public static $classInModule = [];

    /** @var array current language translations */
    protected $_lang = [];

    /** @var string Module web path (eg. '/shop/modules/modulename/') */
    protected $_path = null;
    /**
     * @since 1.5.0.1
     *
     * @var string Module local path (eg. '/home/prestashop/modules/modulename/')
     */
    protected $local_path = null;

    /** @var array Array filled with module errors */
    protected $_errors = [];

    /** @var array Array  array filled with module success */
    protected $_confirmations = [];

    /** @var string Main table used for modules installed */
    protected $table = 'module';

    /** @var string Identifier of the main table */
    protected $identifier = 'id_module';

    /** @var array|null Array cache filled with modules informations */
    protected static $modules_cache;

    /** @var array Array cache filled with modules instances */
    protected static $_INSTANCE = [];

    /** @var bool Config xml generation mode */
    protected static $_generate_config_xml_mode = false;

    /** @var array Array filled with cache translations */
    protected static $l_cache = [];

    /** @var array Array filled with cache permissions (modules / employee profiles) */
    protected static $cache_permissions = [];

    /** @var array Array filled with cache permissions (modules / employee profiles) */
    protected static $cache_lgc_access = [];

    /** @var Context */
    protected $context;

    /** @var Smarty_Data|Smarty_Internal_TemplateBase */
    protected $smarty;

    /** @var Smarty_Internal_Template|null */
    protected $current_subtemplate = null;

    protected static $update_translations_after_install = true;

    protected static $_batch_mode = false;
    protected static $_defered_clearCache = [];
    protected static $_defered_func_call = [];

    /**
     * @var array array of arrays representing tabs added by this module
     *
     * @see PrestaShop\PrestaShop\Adapter\Module\Tab\RegisterTabs($module)
     */
    protected $tabs = [];

    /** @var ContainerInterface */
    private $container;

    /** @var array|null used to cache module ids */
    protected static $cachedModuleNames = null;

    /** @var int Defines the multistore compatibility level of the module */
    public $multistoreCompatibility = self::MULTISTORE_COMPATIBILITY_UNKNOWN;

    public const MULTISTORE_COMPATIBILITY_NO = -20;
    public const MULTISTORE_COMPATIBILITY_NOT_CONCERNED = -10;
    public const MULTISTORE_COMPATIBILITY_UNKNOWN = 0;
    public const MULTISTORE_COMPATIBILITY_PARTIAL = 10;
    public const MULTISTORE_COMPATIBILITY_YES = 20;

    public static function setContextInstanceForTesting(Context $context)
    {
        /** @var Module $module */
        foreach (static::$_INSTANCE as $module) {
            $module->context = $context;
        }
    }

    /**
     * Set the flag to indicate we are doing an import.
     *
     * @param bool $value
     */
    public static function setBatchMode($value)
    {
        static::$_batch_mode = (bool) $value;
    }

    /**
     * @return bool
     */
    public static function getBatchMode()
    {
        return static::$_batch_mode;
    }

    public static function processDeferedFuncCall()
    {
        static::setBatchMode(false);
        foreach (static::$_defered_func_call as $func_call) {
            call_user_func_array($func_call[0], $func_call[1]);
        }

        static::$_defered_func_call = [];
    }

    /**
     * Clear the caches stored in $_defered_clearCache.
     */
    public static function processDeferedClearCache()
    {
        static::setBatchMode(false);

        foreach (static::$_defered_clearCache as $clearCache_array) {
            static::_deferedClearCache($clearCache_array[0], $clearCache_array[1], $clearCache_array[2]);
        }

        static::$_defered_clearCache = [];
    }

    /**
     * Constructor.
     *
     * @param string|null $name (Deprecated parameter)
     * @param Context|null $context
     */
    public function __construct($name = null, ?Context $context = null)
    {
        if ($name !== null) {
            Tools::displayParameterAsDeprecated('name');
        }

        if (!isset($this->ps_versions_compliancy['min'])) {
            $this->ps_versions_compliancy['min'] = '1.4.0.0';
        }

        if (!isset($this->ps_versions_compliancy['max'])) {
            $this->ps_versions_compliancy['max'] = _PS_VERSION_;
        }

        $minParts = explode('.', $this->ps_versions_compliancy['min']);
        $maxParts = explode('.', $this->ps_versions_compliancy['max']);

        // Since v8, we don't pad versions
        if ((int) current($minParts) < 8) {
            $this->ps_versions_compliancy['min'] = str_pad($this->ps_versions_compliancy['min'], 7, '.0');
        }
        if ((int) current($maxParts) < 8) {
            $padLength = strlen($this->ps_versions_compliancy['max']) + (4 - count($maxParts)) * 4;
            $this->ps_versions_compliancy['max'] = str_pad($this->ps_versions_compliancy['max'], $padLength, '.999');
        }

        // Load context and smarty
        $this->context = $context ? $context : Context::getContext();
        if (is_object($this->context->smarty)) {
            $this->smarty = $this->context->smarty->createData($this->context->smarty);
        }

        // If the module has no name we gave him its id as name
        if ($this->name === null) {
            $this->name = $this->id;
        }

        // If the module has the name we load the corresponding data from the cache
        if ($this->name != null) {
            // If cache is not generated, we generate it
            if (static::$modules_cache == null && !is_array(static::$modules_cache)) {
                $id_shop = (Validate::isLoadedObject($this->context->shop) ? $this->context->shop->id : Configuration::get('PS_SHOP_DEFAULT'));

                static::$modules_cache = [];
                // Join clause is done to check if the module is activated in current shop context
                $result = Db::getInstance()->executeS('
                SELECT m.`id_module`, m.`name`, ms.`id_module`as `mshop`
                FROM `' . _DB_PREFIX_ . 'module` m
                LEFT JOIN `' . _DB_PREFIX_ . 'module_shop` ms
                ON m.`id_module` = ms.`id_module`
                AND ms.`id_shop` = ' . (int) $id_shop);
                foreach ($result as $row) {
                    static::$modules_cache[$row['name']] = $row;
                    static::$modules_cache[$row['name']]['active'] = ($row['mshop'] > 0) ? 1 : 0;
                }
            }

            // We load configuration from the cache
            if (isset(static::$modules_cache[$this->name])) {
                if (isset(static::$modules_cache[$this->name]['id_module'])) {
                    $this->id = static::$modules_cache[$this->name]['id_module'];
                }
                foreach (static::$modules_cache[$this->name] as $key => $value) {
                    if (array_key_exists($key, get_object_vars($this))) {
                        $this->{$key} = $value;
                    }
                }
                $this->_path = __PS_BASE_URI__ . 'modules/' . $this->name . '/';
            }
            if (!$this->context->controller instanceof Controller) {
                static::$modules_cache = null;
            }
            $this->local_path = _PS_MODULE_DIR_ . $this->name . '/';
        }
    }

    /**
     * Insert module into datable.
     */
    public function install()
    {
        Hook::exec('actionModuleInstallBefore', ['object' => $this]);

        PrestaShopLogger::addLog(
            Context::getContext()->getTranslator()->trans(
                'Starting module install: %s v%s',
                [$this->name, $this->version],
                'Admin.Modules.Notification'
            ),
            PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
            null,
            'Module',
            null,
            true
        );
        if ($this->_errors) {
            return false;
        }

        // Check module name validation
        if (!Validate::isModuleName($this->name)) {
            $this->_errors[] = Context::getContext()->getTranslator()->trans('Unable to install the module (Module name is not valid).', [], 'Admin.Modules.Notification');

            return false;
        }

        // Check PS version compliancy
        if (!$this->checkCompliancy()) {
            $this->_errors[] = Context::getContext()->getTranslator()->trans('The version of your module is not compliant with your PrestaShop version.', [], 'Admin.Modules.Notification');

            return false;
        }

        // Check module dependencies
        if (count($this->dependencies) > 0) {
            foreach ($this->dependencies as $dependency) {
                if (!Db::getInstance()->getRow('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module` WHERE LOWER(`name`) = \'' . pSQL(Tools::strtolower($dependency)) . '\'')) {
                    $error = Context::getContext()->getTranslator()->trans('Before installing this module, you have to install this/these module(s) first:', [], 'Admin.Modules.Notification') . '<br />';
                    foreach ($this->dependencies as $d) {
                        $error .= '- ' . $d . '<br />';
                    }
                    $this->_errors[] = $error;

                    return false;
                }
            }
        }

        // Check if module is installed
        $result = (new ModuleDataProvider(new LegacyLogger(), $this->getTranslator()))->isInstalled($this->name);
        if ($result) {
            $this->_errors[] = Context::getContext()->getTranslator()->trans('This module has already been installed.', [], 'Admin.Modules.Notification');

            return false;
        }

        // Check for override conflicts
        $moduleOverrideChecker = $this->get(ModuleOverrideChecker::class);
        if (!$moduleOverrideChecker) {
            $moduleOverrideChecker = new ModuleOverrideChecker($this->getTranslator(), _PS_OVERRIDE_DIR_);
        }
        if ($moduleOverrideChecker->hasOverrideConflict($this->getLocalPath() . 'override')) {
            $this->_errors = array_merge($moduleOverrideChecker->getErrors(), $this->_errors);

            return false;
        }

        if (!$this->installControllers()) {
            $this->_errors[] = Context::getContext()->getTranslator()->trans('Could not install module controllers.', [], 'Admin.Modules.Notification');
            $this->uninstallOverrides();

            return false;
        }

        // Install module and retrieve the installation id
        $result = Db::getInstance()->insert($this->table, [
            'name' => $this->name,
            'active' => 1,
            'version' => $this->version,
        ]);
        if (!$result) {
            $this->_errors[] = Context::getContext()->getTranslator()->trans('Technical error: PrestaShop could not install this module.', [], 'Admin.Modules.Notification');
            if (method_exists($this, 'uninstallTabs')) {
                $this->uninstallTabs();
            }
            $this->uninstallOverrides();

            return false;
        }
        $this->id = Db::getInstance()->Insert_ID();

        Cache::clean('Module::isInstalled' . $this->name);
        Cache::clean('Module::getModuleIdByName_' . pSQL($this->name));

        // Enable the module for current shops in context
        if (!$this->enable()) {
            return false;
        }

        // Permissions management
        foreach (['CREATE', 'READ', 'UPDATE', 'DELETE'] as $action) {
            $slug = Permission::PREFIX_MODULE . strtoupper($this->name) . '_' . $action;

            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'authorization_role` (`slug`) VALUES ("' . $slug . '")'
            );

            Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'module_access` (`id_profile`, `id_authorization_role`) (
                    SELECT id_profile, "' . Db::getInstance()->Insert_ID() . '"
                    FROM ' . _DB_PREFIX_ . 'access a
                    LEFT JOIN `' . _DB_PREFIX_ . 'authorization_role` r
                    ON r.id_authorization_role = a.id_authorization_role
                    WHERE r.slug = "ROLE_MOD_TAB_ADMINMODULESSF_' . $action . '"
            )');
        }

        // Adding Restrictions for client groups
        Group::addRestrictionsForModule($this->id, Shop::getShops(true, null, true));
        PrestaShopLogger::addLog(
            Context::getContext()->getTranslator()->trans(
                'Module installed successfully: %s v%s',
                [$this->name, $this->version],
                'Admin.Modules.Notification'
            ),
            PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
            null,
            'Module',
            null,
            true
        );
        Hook::exec('actionModuleInstallAfter', ['object' => $this]);

        if (Module::$update_translations_after_install) {
            $this->updateModuleTranslations();
        }

        return true;
    }

    /**
     * Important: Do not type this method for compatibility reason.
     * If your module aims to be compatible for older PHP versions, it will
     * not be possible if we add strict typing as PHP 5.6 (for example) cannot strict type with bool.
     *
     * @return bool
     */
    public function postInstall()
    {
        return true;
    }

    public function checkCompliancy()
    {
        if (version_compare(_PS_VERSION_, $this->ps_versions_compliancy['min'], '<') || version_compare(_PS_VERSION_, $this->ps_versions_compliancy['max'], '>')) {
            return false;
        } else {
            return true;
        }
    }

    public static function updateTranslationsAfterInstall($update = true)
    {
        Module::$update_translations_after_install = (bool) $update;
    }

    public function updateModuleTranslations()
    {
        return Language::updateModulesTranslations([$this->name]);
    }

    /**
     * Set errors, warning or success message of a module upgrade.
     *
     * @param array $upgrade_detail
     */
    protected function setUpgradeMessage($upgrade_detail)
    {
        // Store information if a module has been upgraded (memory optimization)
        if ($upgrade_detail['available_upgrade']) {
            $translator = Context::getContext()->getTranslator();
            if ($upgrade_detail['success']) {
                $this->_confirmations[] = $translator->trans('Current version: %s', [self::getModuleVersion($this)], 'Admin.Modules.Notification');
                $this->_confirmations[] = $translator->trans('%d file update applied', [$upgrade_detail['number_upgraded']], 'Admin.Modules.Notification');

                return;
            }

            if (!$upgrade_detail['number_upgraded']) {
                $this->_errors[] = $translator->trans('No update has been applied', [], 'Admin.Modules.Notification');
            } else {
                $this->_errors[] = $translator->trans('Updated from: %s to %s', [$upgrade_detail['upgraded_from'], $upgrade_detail['upgraded_to']], 'Admin.Modules.Notification');
                $this->_errors[] = $translator->trans('%d update left', [$upgrade_detail['number_upgrade_left']], 'Admin.Modules.Notification');
            }

            if (isset($upgrade_detail['duplicate']) && $upgrade_detail['duplicate']) {
                $this->_errors[] = $translator->trans('Module %s cannot be updated this time: please refresh this page to update it.', [$this->name], 'Admin.Modules.Notification');
            } else {
                $this->_errors[] = $translator->trans('To prevent any problem, this module has been turned off', [], 'Admin.Modules.Notification');
            }
        }
    }

    /**
     * Init the upgrade module. This method verifies if module upgrade is available and if yes,
     * it preloads the upgrade data. They will be later used in runUpgradeModule method.
     *
     * @param Module|stdClass $module
     *
     * @return bool
     */
    public static function initUpgradeModule($module)
    {
        if ($module->installed && empty($module->database_version)) {
            $moduleVersion = self::getModuleVersion($module);
            Module::upgradeModuleVersion($module->name, $moduleVersion);
            $module->database_version = $moduleVersion;
        }

        if ($module->database_version == $module->version) {
            PrestaShopLogger::addLog(
                Context::getContext()->getTranslator()->trans(
                    'Starting module install: %s v%s',
                    [$module->name, $module->version],
                    'Admin.Modules.Notification'
                ),
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
                null,
                'Module',
                null,
                true
            );
        } else {
            PrestaShopLogger::addLog(
                Context::getContext()->getTranslator()->trans(
                    'Starting module upgrade: %s v%s to v%s',
                    [$module->name, $module->database_version, $module->version],
                    'Admin.Modules.Notification'
                ),
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
                null,
                'Module',
                null,
                true
            );
        }

        /*
         * Init default upgrade data.
         *
         * Beware, that this data is later wiped by ServiceLocator::get($module_name); called inside
         * Module::needUpgrade -> Module::getInstanceByName($module->name), not sure what causes this.
         */
        static::$modules_cache[$module->name]['upgrade'] = [
            'success' => false, // bool to know if upgrade succeed or not
            'available_upgrade' => 0, // Number of available module before any upgrade
            'number_upgraded' => 0, // Number of upgrade done
            'number_upgrade_left' => 0,
            'upgrade_file_left' => [], // List of the upgrade file left
            'version_fail' => 0, // Version of the upgrade failure
            'upgraded_from' => 0, // Version number before upgrading anything
            'upgraded_to' => 0, // Last upgrade applied
        ];

        // Need Upgrade will check and load upgrade file to the moduleCache upgrade case detail
        $ret = $module->installed && Module::needUpgrade($module);

        return $ret;
    }

    /**
     * Run the upgrade for a given module name and version.
     *
     * @return array
     */
    public function runUpgradeModule()
    {
        $upgrade = &static::$modules_cache[$this->name]['upgrade'];
        foreach ($upgrade['upgrade_file_left'] as $num => $file_detail) {
            foreach ($file_detail['upgrade_function'] as $item) {
                if (function_exists($item)) {
                    $upgrade['success'] = false;
                    $upgrade['duplicate'] = true;

                    break 2;
                }
            }

            include $file_detail['file'];

            // Call the upgrade function if defined
            $upgrade['success'] = false;
            foreach ($file_detail['upgrade_function'] as $item) {
                if (function_exists($item)) {
                    $upgrade['success'] = $item($this);
                }
            }

            // Set detail when an upgrade succeed or failed
            if ($upgrade['success']) {
                ++$upgrade['number_upgraded'];
                $upgrade['upgraded_to'] = $file_detail['version'];

                unset($upgrade['upgrade_file_left'][$num]);
            } else {
                $upgrade['version_fail'] = $file_detail['version'];

                // If any errors, the module is disabled
                $this->disable();

                break;
            }
        }

        $upgrade['number_upgrade_left'] = count($upgrade['upgrade_file_left']);

        // Update module version in DB with the last succeed upgrade
        if ($upgrade['upgraded_to']) {
            Module::upgradeModuleVersion($this->name, $upgrade['upgraded_to']);
            PrestaShopLogger::addLog(
                Context::getContext()->getTranslator()->trans(
                    'Module upgraded successfully: %s to v%s',
                    [$this->name, $upgrade['upgraded_to']],
                    'Admin.Modules.Notification'
                ),
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
                null,
                'Module',
                null,
                true
            );
        }
        $this->setUpgradeMessage($upgrade);

        return $upgrade;
    }

    /**
     * Upgrade the registered version to a new one.
     *
     * @param string $name
     * @param string $version
     *
     * @return bool
     */
    public static function upgradeModuleVersion($name, $version)
    {
        return Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'module` m
            SET m.version = \'' . pSQL($version) . '\'
            WHERE m.name = \'' . pSQL($name) . '\'');
    }

    /**
     * Check if a module need to be upgraded.
     * This method modify the module_cache adding an upgrade list file.
     *
     * @param Module $module
     *
     * @return bool|null Boolean if Module::$version != Module::$database_version, null instead
     */
    public static function needUpgrade($module)
    {
        static::$modules_cache[$module->name]['upgrade']['upgraded_from'] = $module->database_version;
        // Check the version of the module with the registered one and look if any upgrade file exist
        $moduleVersion = self::getModuleVersion($module);
        if (Tools::version_compare($moduleVersion, $module->database_version, '>')) {
            /*
             * $old_version variable is saved on purpose here, because calling Module::getInstanceByName will wipe
             * the $module->database_version information from the object.
             */
            $old_version = $module->database_version;
            $module = Module::getInstanceByName($module->name);
            if ($module instanceof Module) {
                return $module->loadUpgradeVersionList($module->name, $moduleVersion, $old_version);
            }
        }

        return null;
    }

    public static function getModuleVersion(ModuleCore|stdClass $module): string
    {
        $moduleConfig = self::loadModuleXMLConfig($module->name);

        return $moduleConfig['version'] ?? $module->version;
    }

    /**
     * Load the available list of upgrade of a specified module
     * with an associated version.
     *
     * @param string $module_name
     * @param string $module_version
     * @param string $registered_version
     *
     * @return bool to know directly if any files have been found
     */
    protected static function loadUpgradeVersionList($module_name, $module_version, $registered_version)
    {
        /*
         * Init cache upgrade details, again.
         *
         * We already initialized this in Module::initUpgradeModule, but for some reason, the static variable
         * is wiped when calling Module::getInstanceByName in Module::needUpgrade.
         */
        static::$modules_cache[$module_name]['upgrade'] = [
            'success' => false, // bool to know if upgrade succeed or not
            'available_upgrade' => 0, // Number of available module before any upgrade
            'number_upgraded' => 0, // Number of upgrade done
            'number_upgrade_left' => 0,
            'upgrade_file_left' => [], // List of the upgrade file left
            'version_fail' => 0, // Version of the upgrade failure
            'upgraded_from' => $registered_version, // Version number before upgrading anything
            'upgraded_to' => 0, // Last upgrade applied
        ];

        // Prepare list of upgrade files
        $list = [];

        $upgrade_path = _PS_MODULE_DIR_ . $module_name . '/upgrade/';

        // Check if folder exist and it could be read
        if (file_exists($upgrade_path) && ($files = scandir($upgrade_path, SCANDIR_SORT_NONE))) {
            // Read each file name
            foreach ($files as $file) {
                if (!in_array($file, ['.', '..', '.svn', 'index.php']) && preg_match('/\.php$/', $file)) {
                    $tab = explode('-', $file);

                    if (!isset($tab[1])) {
                        continue;
                    }

                    /*
                     * @TODO
                     *
                     * Some more verifications should be done here, because some faulty modules could use
                     * filenames with dashes instead of dots, or non-standard versioning, breaking the process.
                     *
                     * Example - install-1-1.php
                     */
                    $file_version = basename($tab[1], '.php');
                    // Compare version, if minor than actual, we need to upgrade the module
                    if (count($tab) == 2
                         && (Tools::version_compare($file_version, $module_version, '<=')
                            && Tools::version_compare($file_version, $registered_version, '>'))) {
                        $list[] = [
                            'file' => $upgrade_path . $file,
                            'version' => $file_version,
                            'upgrade_function' => [
                                'upgrade_module_' . str_replace('.', '_', $file_version),
                                'upgradeModule' . str_replace('.', '', $file_version), ],
                        ];
                    }
                }
            }
        }

        // No files upgrade, then upgrade succeed
        if (count($list) == 0) {
            static::$modules_cache[$module_name]['upgrade']['success'] = true;
            Module::upgradeModuleVersion($module_name, $module_version);
        }

        usort($list, 'ps_module_version_sort');

        static::$modules_cache[$module_name]['upgrade']['upgrade_file_left'] = $list;
        static::$modules_cache[$module_name]['upgrade']['available_upgrade'] = count($list);

        return (bool) count($list);
    }

    /**
     * Return the status of the upgraded module.
     *
     * @param string $module_name
     *
     * @return bool
     */
    public static function getUpgradeStatus($module_name)
    {
        return isset(static::$modules_cache[$module_name])
            && static::$modules_cache[$module_name]['upgrade']['success'];
    }

    /**
     * Uninstalls the module from database.
     *
     * @return bool result
     */
    public function uninstall()
    {
        Hook::exec('actionModuleUninstallBefore', ['object' => $this]);

        PrestaShopLogger::addLog(
            Context::getContext()->getTranslator()->trans(
                'Starting module uninstall: %s v%s',
                [$this->name, $this->version],
                'Admin.Modules.Notification'
            ),
            PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
            null,
            'Module',
            null,
            true
        );
        // Check if module instance is valid
        if (!Validate::isUnsignedId($this->id)) {
            $this->_errors[] = Context::getContext()->getTranslator()->trans('The module is not installed.', [], 'Admin.Modules.Notification');

            return false;
        }

        // Uninstall all overrides this module may have used
        if (!$this->uninstallOverrides()) {
            return false;
        }

        // Retrieve hooks used by the module
        $sql = 'SELECT DISTINCT(`id_hook`) FROM `' . _DB_PREFIX_ . 'hook_module` WHERE `id_module` = ' . (int) $this->id;
        $result = Db::getInstance()->executeS($sql);
        foreach ($result as $row) {
            // Unhook this module from each of the hooks
            $this->unregisterHook((int) $row['id_hook']);
            // Remove all hook conditions that may have been configured - don't confuse it with error exception. :-)
            $this->unregisterExceptions((int) $row['id_hook']);
        }

        // Remove all configured meta data (titles, URLs etc.) for this module's front controllers
        foreach ($this->controllers as $controller) {
            $page_name = 'module-' . $this->name . '-' . $controller;
            $meta = Db::getInstance()->getValue('SELECT id_meta FROM `' . _DB_PREFIX_ . 'meta` WHERE page="' . pSQL($page_name) . '"');
            if ((int) $meta > 0) {
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'meta_lang` WHERE id_meta=' . (int) $meta);
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'meta` WHERE id_meta=' . (int) $meta);
            }
        }

        // Disable the module for all shops
        $this->disable(true);

        // Delete permissions module access
        $roles = Db::getInstance()->executeS('SELECT `id_authorization_role` FROM `' . _DB_PREFIX_ . 'authorization_role` WHERE `slug` LIKE "' . Permission::PREFIX_MODULE . strtoupper($this->name) . '_%"');

        if (!empty($roles)) {
            foreach ($roles as $role) {
                Db::getInstance()->execute(
                    'DELETE FROM `' . _DB_PREFIX_ . 'module_access` WHERE `id_authorization_role` = ' . $role['id_authorization_role']
                );
                Db::getInstance()->execute(
                    'DELETE FROM `' . _DB_PREFIX_ . 'authorization_role` WHERE `id_authorization_role` = ' . $role['id_authorization_role']
                );
            }
        }

        // Remove restrictions for client groups
        Group::truncateRestrictionsByModule($this->id);

        // Uninstall the module
        if (Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'module` WHERE `id_module` = ' . (int) $this->id)) {
            Cache::clean('Module::isInstalled' . $this->name);
            Cache::clean('Module::getModuleIdByName_' . pSQL($this->name));
            PrestaShopLogger::addLog(
                Context::getContext()->getTranslator()->trans(
                    'Module uninstalled successfully: %s v%s',
                    [$this->name, $this->version],
                    'Admin.Modules.Notification'
                ),
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE,
                null,
                'Module',
                null,
                true
            );
            Hook::exec('actionModuleUninstallAfter', ['object' => $this]);

            return true;
        }

        return false;
    }

    /**
     * This function enable module $name. If an $name is an array,
     * this will enable all of them.
     *
     * @param array|string $name
     *
     * @return bool
     *
     * @since 1.4.1
     * @deprecated since 1.7
     * @see  PrestaShop\PrestaShop\Core\Module\ModuleManager->enable($name)
     */
    public static function enableByName($name)
    {
        // If $name is not an array, we set it as an array
        if (!is_array($name)) {
            $name = [$name];
        }
        $res = true;
        // Enable each module
        foreach ($name as $n) {
            if (Validate::isModuleName($n)) {
                $res &= Module::getInstanceByName($n)->enable();
            }
        }

        return $res;
    }

    /**
     * Activate current module.
     *
     * @param bool $force_all If true, enable module for all shop
     *
     * @return bool
     */
    public function enable($force_all = false)
    {
        // Retrieve all shops where the module is enabled
        $list = Shop::getContextListShopID();
        if (!$this->id || !is_array($list)) {
            return false;
        }
        $sql = 'SELECT `id_shop` FROM `' . _DB_PREFIX_ . 'module_shop`
                WHERE `id_module` = ' . (int) $this->id .
                ((!$force_all) ? ' AND `id_shop` IN(' . implode(', ', $list) . ')' : '');

        // Store the results in an array
        $items = [];
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $row) {
                $items[] = $row['id_shop'];
            }
        }

        $moduleOverrideChecker = $this->get(ModuleOverrideChecker::class);
        if (!$moduleOverrideChecker) {
            $moduleOverrideChecker = new ModuleOverrideChecker($this->getTranslator(), _PS_OVERRIDE_DIR_);
        }

        if ($this->getOverrides() != null) {
            if (!$moduleOverrideChecker->hasOverrideConflict($this->getLocalPath() . 'override')) {
                // Install overrides
                try {
                    $this->installOverrides();
                } catch (Exception $e) {
                    $this->_errors[] = Context::getContext()->getTranslator()->trans('Unable to install override: %s', [$e->getMessage()], 'Admin.Modules.Notification');
                    $this->uninstallOverrides();

                    return false;
                }
            } else {
                $this->_errors = array_merge($moduleOverrideChecker->getErrors(), $this->_errors);

                return false;
            }
        }

        // Enable module in the shop where it is not enabled yet
        $moduleActivated = false;
        foreach ($list as $id) {
            if (!in_array($id, $items)) {
                Db::getInstance()->insert('module_shop', [
                    'id_module' => $this->id,
                    'id_shop' => $id,
                ]);
                $moduleActivated = true;
            }
        }

        // set active to 1 in the module table
        Db::getInstance()->update('module', ['active' => 1], 'id_module = ' . (int) $this->id);

        if ($moduleActivated) {
            $this->loadBuiltInTranslations();
        }

        return true;
    }

    /**
     * @deprecated since 9.0.0 - This functionality was disabled. Function will be completely removed
     * in the next major. There is no replacement, all clients should have the same experience.
     */
    public function enableDevice($device)
    {
        @trigger_error(
            sprintf(
                '%s is deprecated since version 9.0.0. There is no replacement.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return true;
    }

    /**
     * @deprecated since 9.0.0 - This functionality was disabled. Function will be completely removed
     * in the next major. There is no replacement, all clients should have the same experience.
     */
    public function disableDevice($device)
    {
        @trigger_error(
            sprintf(
                '%s is deprecated since version 9.0.0. There is no replacement.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return true;
    }

    /**
     * This function disable all module $name. If an $name is an array,
     * this will disable all of them.
     *
     * @param array|string $name
     *
     * @return bool
     *
     * @since 1.7
     */
    public static function disableAllByName($name)
    {
        // If $name is not an array, we set it as an array
        if (!is_array($name)) {
            $name = [$name];
        }
        $res = true;
        // Disable each module
        foreach ($name as $n) {
            $sql = 'DELETE `' . _DB_PREFIX_ . 'module_shop` FROM `' . _DB_PREFIX_ . 'module_shop` JOIN `' . _DB_PREFIX_ . 'module` USING (id_module) WHERE `name` = "' . pSQL($n) . '"';
            $res &= Db::getInstance()->execute($sql);
        }

        return $res;
    }

    /**
     * This function disable module $name. If an $name is an array,
     * this will disable all of them.
     *
     * @param array|string $name
     *
     * @return bool
     *
     * @since 1.4.1
     * @deprecated since 1.7
     * @see  PrestaShop\PrestaShop\Core\Module\ModuleManager->disable($name)
     */
    public static function disableByName($name)
    {
        // If $name is not an array, we set it as an array
        if (!is_array($name)) {
            $name = [$name];
        }
        $res = true;
        // Disable each module
        foreach ($name as $n) {
            if (Validate::isModuleName($n)) {
                $res &= Module::getInstanceByName($n)->disable();
            }
        }

        return $res;
    }

    /**
     * Desactivate current module.
     *
     * @param bool $force_all If true, disable module for all shop
     *
     * @return bool
     */
    public function disable($force_all = false)
    {
        $result = true;
        if ($this->getOverrides() != null) {
            $result &= $this->uninstallOverrides();
        }

        // Disable module for all shops or contextual shops
        $whereIdShop = $force_all ? '' : ' AND `id_shop` IN(' . implode(', ', Shop::getContextListShopID()) . ')';
        $result &= Db::getInstance()->delete('module_shop', '`id_module` = ' . (int) $this->id . $whereIdShop);

        // if module has no more shop associations, set module.active = 0
        if (!$this->hasShopAssociations()) {
            $result &= Db::getInstance()->update('module', ['active' => 0], 'id_module = ' . (int) $this->id);
        }

        return (bool) $result;
    }

    public function hasShopAssociations(): bool
    {
        $sql = "SELECT m.id_module FROM %smodule m INNER JOIN %smodule_shop ms ON ms.id_module = m.id_module WHERE m.id_module = '%s'";
        $result = Db::getInstance()->getRow(sprintf($sql, _DB_PREFIX_, _DB_PREFIX_, (int) $this->id));

        return isset($result['id_module']);
    }

    /**
     * Connect module to a hook.
     *
     * @param string|array $hook_name Hook name
     * @param array|null $shop_list List of shop linked to the hook (if null, link hook to all shops)
     *
     * @return bool result
     */
    public function registerHook($hook_name, $shop_list = null)
    {
        return Hook::registerHook($this, $hook_name, $shop_list);
    }

    /**
     * Unregister module from hook.
     *
     * @param int|string $hook_id Hook id (can be a hook name since 1.5.0)
     * @param array|null $shop_list List of shop
     *
     * @return bool result
     */
    public function unregisterHook($hook_id, $shop_list = null)
    {
        return Hook::unregisterHook($this, $hook_id, $shop_list);
    }

    /**
     * Unregister exceptions linked to module.
     *
     * @param int $hook_id Hook id
     * @param array|null $shop_list List of shop
     *
     * @return bool result
     */
    public function unregisterExceptions($hook_id, $shop_list = null)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'hook_module_exceptions`
            WHERE `id_module` = ' . (int) $this->id . ' AND `id_hook` = ' . (int) $hook_id
            . (($shop_list) ? ' AND `id_shop` IN(' . implode(', ', array_map('intval', $shop_list)) . ')' : '');

        return Db::getInstance()->execute($sql);
    }

    /**
     * Add exceptions for module->Hook.
     *
     * @param int $id_hook Hook id
     * @param array $excepts List of file name
     * @param array $shop_list List of shop
     *
     * @return bool result
     */
    public function registerExceptions($id_hook, $excepts, $shop_list = null)
    {
        // If shop lists is null, we fill it with all shops
        if (null === $shop_list) {
            $shop_list = Shop::getContextListShopID();
        }

        // Save modules exception for each shop
        foreach ($shop_list as $shop_id) {
            foreach ($excepts as $except) {
                if (!$except) {
                    continue;
                }
                $insert_exception = [
                    'id_module' => (int) $this->id,
                    'id_hook' => (int) $id_hook,
                    'id_shop' => (int) $shop_id,
                    'file_name' => pSQL($except),
                ];
                $result = Db::getInstance()->insert('hook_module_exceptions', $insert_exception);
                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Edit exceptions for module->Hook.
     *
     * @param int $id_hook Hook id
     * @param array $excepts List of shopID and file name
     *
     * @return bool result
     */
    public function editExceptions($id_hook, $excepts)
    {
        $result = true;
        foreach ($excepts as $shop_id => $except) {
            $shop_list = ($shop_id == 0) ? Shop::getContextListShopID() : [$shop_id];
            $this->unregisterExceptions($id_hook, $shop_list);
            $result &= $this->registerExceptions($id_hook, $except, $shop_list);
        }

        return $result;
    }

    /**
     * This function is used to determine the module name
     * of an AdminTab which belongs to a module, in order to keep translation
     * related to a module in its directory.
     *
     * Note: this won't work if the module's path contains symbolic links
     *
     * @param string $current_class Name of Module class
     *
     * @return bool|string if the class belongs to a module, will return the module name. Otherwise, return false.
     */
    public static function getModuleNameFromClass($current_class)
    {
        // Module can now define AdminTab keeping the module translations method,
        // i.e. in modules/[module name]/[iso_code].php
        if (!isset(static::$classInModule[$current_class]) && class_exists($current_class)) {
            $reflection_class = new ReflectionClass($current_class);
            $file_path = realpath($reflection_class->getFileName());
            $realpath_module_dir = realpath(_PS_MODULE_DIR_);
            if (substr(realpath($file_path), 0, strlen($realpath_module_dir)) == $realpath_module_dir) {
                // For controllers in module/controllers path
                if (basename(dirname(dirname($file_path))) == 'controllers') {
                    static::$classInModule[$current_class] = basename(dirname(dirname(dirname($file_path))));
                } else {
                    // For old AdminTab controllers
                    static::$classInModule[$current_class] = substr(dirname($file_path), strlen($realpath_module_dir) + 1);
                }
            } else {
                static::$classInModule[$current_class] = false;
            }
        }

        // return name of the module, or false
        return static::$classInModule[$current_class];
    }

    /**
     * Return an instance of the specified module.
     *
     * @param string $module_name Module name
     *
     * @return Module|false
     */
    public static function getInstanceByName($module_name)
    {
        if (!Validate::isModuleName($module_name)) {
            if (!_PS_MODE_DEV_) {
                return false;
            }

            die(Tools::displayError(Context::getContext()->getTranslator()->trans(
                '%1$s is not a valid module name.',
                [Tools::safeOutput($module_name)],
                'Admin.Modules.Notification'
            )));
        }

        if (!isset(static::$_INSTANCE[$module_name])) {
            if (!Tools::file_exists_no_cache(_PS_MODULE_DIR_ . $module_name . '/' . $module_name . '.php')) {
                return false;
            }

            return Module::coreLoadModule($module_name);
        }

        return static::$_INSTANCE[$module_name];
    }

    protected static function coreLoadModule($module_name)
    {
        include_once _PS_MODULE_DIR_ . $module_name . '/' . $module_name . '.php';

        $r = false;
        if (Tools::file_exists_no_cache(_PS_OVERRIDE_DIR_ . 'modules/' . $module_name . '/' . $module_name . '.php')) {
            include_once _PS_OVERRIDE_DIR_ . 'modules/' . $module_name . '/' . $module_name . '.php';
            $override = $module_name . 'Override';

            if (class_exists($override, false)) {
                $r = static::$_INSTANCE[$module_name] = ServiceLocator::get($override);
            }
        }

        if (!$r && class_exists($module_name, false)) {
            $r = static::$_INSTANCE[$module_name] = ServiceLocator::get($module_name);
        }

        return $r;
    }

    /**
     * Return an instance of the specified module.
     *
     * @param int $id_module Module ID
     *
     * @return Module|false
     */
    public static function getInstanceById($id_module)
    {
        if (null === static::$cachedModuleNames) {
            static::$cachedModuleNames = [];
            $sql = 'SELECT `id_module`, `name` FROM `' . _DB_PREFIX_ . 'module`';
            if ($results = Db::getInstance()->executeS($sql)) {
                foreach ($results as $row) {
                    static::$cachedModuleNames[$row['id_module']] = $row['name'];
                }
            }
        }

        if (isset(static::$cachedModuleNames[$id_module])) {
            return Module::getInstanceByName(static::$cachedModuleNames[$id_module]);
        }

        return false;
    }

    /**
     * Clear static cache.
     */
    public static function clearStaticCache()
    {
        static::$cachedModuleNames = null;
    }

    public static function configXmlStringFormat($string)
    {
        return Tools::htmlentitiesDecodeUTF8($string);
    }

    public static function loadModuleXMLConfig(string $technicalName, ?string $languageIsoCode = null): ?array
    {
        if (empty($languageIsoCode)) {
            $languageIsoCode = substr(Context::getContext()->language->iso_code, 0, 2);
        }

        // Config file
        $configFilePath = _PS_MODULE_DIR_ . $technicalName . '/config_' . $languageIsoCode . '.xml';
        // For "en" iso code, we keep the default config.xml name
        if ($languageIsoCode == 'en' || !file_exists($configFilePath)) {
            $configFilePath = _PS_MODULE_DIR_ . $technicalName . '/config.xml';
            if (!file_exists($configFilePath)) {
                return null;
            }
        }

        // Load config.xml
        libxml_use_internal_errors(true);
        $moduleConfigXML = @simplexml_load_file($configFilePath);
        if (!$moduleConfigXML) {
            return null;
        }

        $moduleConfig = [];
        foreach ($moduleConfigXML as $key => $value) {
            $moduleConfig[(string) $key] = (string) $value;
        }

        return $moduleConfig;
    }

    public static function getModuleName($module)
    {
        $iso = substr(Context::getContext()->language->iso_code, 0, 2);

        // Config file
        $config_file = _PS_MODULE_DIR_ . $module . '/config_' . $iso . '.xml';
        // For "en" iso code, we keep the default config.xml name
        if ($iso == 'en' || !file_exists($config_file)) {
            $config_file = _PS_MODULE_DIR_ . $module . '/config.xml';
            if (!file_exists($config_file)) {
                return 'Module ' . ucfirst($module);
            }
        }

        // Load config.xml
        libxml_use_internal_errors(true);
        $xml_module = @simplexml_load_file($config_file);
        if (!$xml_module) {
            return 'Module ' . ucfirst($module);
        }
        foreach (libxml_get_errors() as $error) {
            libxml_clear_errors();

            return 'Module ' . ucfirst($module);
        }
        libxml_clear_errors();

        // Find translations
        global $_MODULES;
        $file = _PS_MODULE_DIR_ . $module . '/' . Context::getContext()->language->iso_code . '.php';
        if (Tools::file_exists_cache($file) && include_once ($file)) {
            /* @phpstan-ignore-next-line Defined variable in translation file */
            if (isset($_MODULE) && is_array($_MODULE)) {
                /** @phpstan-ignore-next-line Defined variable in translation file */
                $_MODULES = !empty($_MODULES) ? array_merge($_MODULES, $_MODULE) : $_MODULE;
            }
        }

        // Return Name
        return Translate::getModuleTranslation((string) $xml_module->name, Module::configXmlStringFormat($xml_module->displayName), (string) $xml_module->name);
    }

    protected static function useTooMuchMemory()
    {
        $memory_limit = Tools::getMemoryLimit();
        if (function_exists('memory_get_usage') && $memory_limit != '-1') {
            $current_memory = memory_get_usage(true);
            $memory_threshold = (int) max($memory_limit * 0.15, Tools::isX86_64arch() ? 4194304 : 2097152);
            $memory_left = $memory_limit - $current_memory;

            if ($memory_left <= $memory_threshold) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return available modules.
     *
     * @param bool $use_config in order to use config.xml file in module dir
     *
     * @return array<StdClass> Modules
     */
    public static function getModulesOnDisk($use_config = false, $id_employee = false)
    {
        global $_MODULES;

        // Init var
        $module_list = [];
        $module_name_list = [];
        $modules_name_to_cursor = [];
        $errors = [];

        // Get modules directory list and memory limit
        $modules_dir = Module::getModulesDirOnDisk();

        $modules_installed = [];
        $result = Db::getInstance()->executeS('
        SELECT m.name, m.version, mp.interest
        FROM `' . _DB_PREFIX_ . 'module` m
        ' . Shop::addSqlAssociation('module', 'm', false) . '
        LEFT JOIN `' . _DB_PREFIX_ . 'module_preference` mp ON (mp.`module` = m.`name` AND mp.`id_employee` = ' . (int) $id_employee . ')');
        foreach ($result as $row) {
            $modules_installed[$row['name']] = $row;
        }

        foreach ($modules_dir as $module) {
            $module_errors = [];
            if (Module::useTooMuchMemory()) {
                $module_errors[] = Context::getContext()->getTranslator()->trans('All modules cannot be loaded due to memory limit restrictions, please increase your memory_limit value on your server configuration', [], 'Admin.Modules.Notification');

                break;
            }

            $iso = substr(Context::getContext()->language->iso_code, 0, 2);

            // Check if config.xml module file exists and if it's not outdated

            if ($iso == 'en') {
                $config_file = _PS_MODULE_DIR_ . $module . '/config.xml';
            } else {
                $config_file = _PS_MODULE_DIR_ . $module . '/config_' . $iso . '.xml';
            }

            $xml_exist = file_exists($config_file);
            $need_new_config_file = $xml_exist ? (@filemtime($config_file) < @filemtime(_PS_MODULE_DIR_ . $module . '/' . $module . '.php')) : true;

            // If config.xml exists and that the use config flag is at true
            if ($use_config && $xml_exist && !$need_new_config_file) {
                // Load config.xml
                libxml_use_internal_errors(true);
                $xml_module = @simplexml_load_file($config_file);
                if (!$xml_module) {
                    $module_errors[] = Context::getContext()->getTranslator()->trans(
                        '%s could not be loaded.',
                        [$config_file],
                        'Admin.Modules.Notification'
                    );

                    break;
                }
                foreach (libxml_get_errors() as $error) {
                    $module_errors[] = '[' . $module . '] ' . Context::getContext()->getTranslator()->trans('Error found in config file:', [], 'Admin.Modules.Notification') . ' ' . htmlentities($error->message);
                }
                libxml_clear_errors();

                // If no errors in Xml, no need instand and no need new config.xml file, we load only translations
                if (!count($module_errors) && (int) $xml_module->need_instance == 0) {
                    $file = _PS_MODULE_DIR_ . $module . '/' . Context::getContext()->language->iso_code . '.php';
                    if (Tools::file_exists_cache($file) && include_once ($file)) {
                        /* @phpstan-ignore-next-line Defined variable in translation file */
                        if (isset($_MODULE) && is_array($_MODULE)) {
                            /** @phpstan-ignore-next-line Defined variable in translation file */
                            $_MODULES = !empty($_MODULES) ? array_merge($_MODULES, $_MODULE) : $_MODULE;
                        }
                    }

                    $item = new stdClass();
                    $item->id = 0;
                    $item->warning = '';

                    foreach ($xml_module as $k => $v) {
                        $item->$k = (string) $v;
                    }

                    $item->version = self::getModuleVersion($item);
                    $item->displayName = stripslashes(Translate::getModuleTranslation((string) $xml_module->name, Module::configXmlStringFormat($xml_module->displayName), (string) $xml_module->name));
                    $item->description = stripslashes(Translate::getModuleTranslation((string) $xml_module->name, Module::configXmlStringFormat($xml_module->description), (string) $xml_module->name));
                    $item->author = stripslashes(Translate::getModuleTranslation((string) $xml_module->name, Module::configXmlStringFormat($xml_module->author), (string) $xml_module->name));
                    $item->author_uri = (isset($xml_module->author_uri) && $xml_module->author_uri) ? stripslashes($xml_module->author_uri) : false;

                    if (isset($xml_module->confirmUninstall)) {
                        $item->confirmUninstall = Translate::getModuleTranslation((string) $xml_module->name, html_entity_decode(Module::configXmlStringFormat($xml_module->confirmUninstall)), (string) $xml_module->name);
                    }

                    $item->active = 0;
                    $item->onclick_option = false;

                    $module_list[$item->name . '_disk'] = $item;

                    $module_name_list[] = '\'' . pSQL($item->name) . '\'';
                    $modules_name_to_cursor[Tools::strtolower((string) $item->name)] = $item;
                }
            }

            // If use config flag is at false or config.xml does not exist OR need instance OR need a new config.xml file
            if (!$use_config || !$xml_exist || (isset($xml_module->need_instance) && (int) $xml_module->need_instance == 1) || $need_new_config_file) {
                // If class does not exists, we include the file
                if (!class_exists($module, false)) {
                    // Get content from php file
                    $file_path = _PS_MODULE_DIR_ . $module . '/' . $module . '.php';
                    $file = trim(file_get_contents(_PS_MODULE_DIR_ . $module . '/' . $module . '.php'));

                    try {
                        $parser = (new PhpParser\ParserFactory())->create(PhpParser\ParserFactory::ONLY_PHP7);
                        $parser->parse($file);
                        require_once $file_path;
                    } catch (PhpParser\Error $e) {
                        $errors[] = Context::getContext()->getTranslator()->trans('%1$s (parse error in %2$s)', [$module, substr($file_path, strlen(_PS_ROOT_DIR_))], 'Admin.Modules.Notification');
                    }

                    preg_match('/\n[\s\t]*?namespace\s.*?;/', $file, $ns);
                    if (!empty($ns)) {
                        $ns = preg_replace('/\n[\s\t]*?namespace\s/', '', $ns[0]);
                        $ns = rtrim($ns, ';');
                        $module = $ns . '\\' . $module;
                    }
                }

                // If class exists, we just instanciate it
                if (class_exists($module, false)) {
                    try {
                        $tmp_module = ServiceLocator::get($module);

                        $item = new stdClass();

                        $item->id = (int) $tmp_module->id;
                        $item->warning = $tmp_module->warning;
                        $item->name = $tmp_module->name;
                        $item->version = self::getModuleVersion($tmp_module);
                        $item->tab = $tmp_module->tab;
                        $item->displayName = $tmp_module->displayName;
                        $item->description = isset($tmp_module->description) ? stripslashes($tmp_module->description) : null;
                        $item->author = $tmp_module->author;
                        $item->author_uri = (isset($tmp_module->author_uri) && $tmp_module->author_uri) ? $tmp_module->author_uri : false;
                        $item->limited_countries = $tmp_module->limited_countries;
                        $item->parent_class = get_parent_class($module);
                        $item->is_configurable = $tmp_module->is_configurable = method_exists($tmp_module, 'getContent') ? 1 : 0;
                        $item->need_instance = isset($tmp_module->need_instance) ? $tmp_module->need_instance : 0;
                        $item->active = $tmp_module->active;
                        $item->currencies = isset($tmp_module->currencies) ? $tmp_module->currencies : null;
                        $item->currencies_mode = isset($tmp_module->currencies_mode) ? $tmp_module->currencies_mode : null;
                        $item->confirmUninstall = isset($tmp_module->confirmUninstall) ? html_entity_decode($tmp_module->confirmUninstall) : null;
                        $item->description_full = isset($tmp_module->description_full) ? stripslashes($tmp_module->description_full) : null;
                        $item->additional_description = isset($tmp_module->additional_description) ? stripslashes($tmp_module->additional_description) : null;
                        $item->compatibility = isset($tmp_module->compatibility) ? (array) $tmp_module->compatibility : null;
                        $item->nb_rates = isset($tmp_module->nb_rates) ? (array) $tmp_module->nb_rates : null;
                        $item->avg_rate = isset($tmp_module->avg_rate) ? (array) $tmp_module->avg_rate : null;
                        $item->badges = isset($tmp_module->badges) ? (array) $tmp_module->badges : null;
                        $item->url = isset($tmp_module->url) ? $tmp_module->url : null;
                        $item->onclick_option = method_exists($module, 'onclickOption') ? true : false;

                        if ($item->onclick_option) {
                            $href = Context::getContext()->link->getAdminLink('Module', true, [], ['module_name' => $tmp_module->name, 'tab_module' => $tmp_module->tab]);
                            $item->onclick_option_content = [];
                            $option_tab = ['desactive', 'reset', 'configure', 'delete'];

                            foreach ($option_tab as $opt) {
                                $item->onclick_option_content[$opt] = $tmp_module->onclickOption($opt, $href);
                            }
                        }

                        $module_list[$item->name . '_disk'] = $item;

                        if (!$xml_exist || $need_new_config_file) {
                            static::$_generate_config_xml_mode = true;
                            $tmp_module->_generateConfigXml();
                            static::$_generate_config_xml_mode = false;
                        }

                        unset($tmp_module);
                    } catch (Exception $e) {
                    }
                } else {
                    $module_errors[] = Context::getContext()->getTranslator()->trans(
                        '%1$s (class missing in %2$s)',
                        [
                            $module,
                            substr($file_path ?? '', strlen(_PS_ROOT_DIR_)),
                        ],
                        'Admin.Modules.Notification'
                    );
                }
            }
            $errors = array_merge($errors, $module_errors);
        }

        // Get modules information from database
        if (!empty($module_name_list)) {
            $list = Shop::getContextListShopID();
            $sql = 'SELECT m.id_module, m.name, (
                        SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'module_shop ms WHERE m.id_module = ms.id_module AND ms.id_shop IN (' . implode(',', $list) . ')
                    ) as total
                    FROM ' . _DB_PREFIX_ . 'module m
                    WHERE LOWER(m.name) IN (' . Tools::strtolower(implode(',', $module_name_list)) . ')';
            $results = Db::getInstance()->executeS($sql);

            foreach ($results as $result) {
                if (isset($modules_name_to_cursor[Tools::strtolower($result['name'])])) {
                    $module_cursor = $modules_name_to_cursor[Tools::strtolower($result['name'])];
                    $module_cursor->id = (int) $result['id_module'];
                    $module_cursor->active = ($result['total'] == count($list)) ? 1 : 0;
                }
            }
        }

        foreach ($module_list as $key => &$module) {
            if (!isset($module->tab)) {
                $module->tab = 'others';
            }
            if (isset($modules_installed[$module->name])) {
                $module->installed = true;
                $module->database_version = $modules_installed[$module->name]['version'];
                $module->interest = $modules_installed[$module->name]['interest'];
            } else {
                $module->installed = false;
                $module->database_version = 0;
                $module->interest = 0;
            }
        }

        usort($module_list, function ($a, $b) {
            return strnatcasecmp($a->displayName, $b->displayName);
        });
        if ($errors) {
            if (!isset(Context::getContext()->controller) && !Context::getContext()->controller->controller_name) {
                echo '<div class="alert error"><h3>' . Context::getContext()->getTranslator()->trans('The following module(s) could not be loaded', [], 'Admin.Modules.Notification') . ':</h3><ol>';
                foreach ($errors as $error) {
                    echo '<li>' . $error . '</li>';
                }
                echo '</ol></div>';
            } else {
                foreach ($errors as $error) {
                    Context::getContext()->controller->errors[] = $error;
                }
            }
        }

        return $module_list;
    }

    /**
     * Return modules directory list.
     *
     * @return array Modules Directory List
     */
    public static function getModulesDirOnDisk()
    {
        $module_list = [];
        $modules = scandir(_PS_MODULE_DIR_, SCANDIR_SORT_NONE);
        foreach ($modules as $name) {
            if (is_file(_PS_MODULE_DIR_ . $name)) {
                continue;
            } elseif (is_dir(_PS_MODULE_DIR_ . $name . DIRECTORY_SEPARATOR) && Tools::file_exists_cache(_PS_MODULE_DIR_ . $name . '/' . $name . '.php')) {
                if (!Validate::isModuleName($name)) {
                    throw new PrestaShopException(sprintf('Module %s is not a valid module name', $name));
                }
                $module_list[] = $name;
            }
        }

        return $module_list;
    }

    /**
     * Return non native module.
     *
     * @return array|false Modules list or false if cannot read xml
     */
    public static function getNonNativeModuleList()
    {
        return self::getModuleRepository()->getNonNativeModules();
    }

    /**
     * @return array<string>
     */
    public static function getNativeModuleList()
    {
        return self::getModuleRepository()->getNativeModules();
    }

    /**
     * @return ModuleRepository
     *
     * @throws ContainerNotFoundException
     */
    private static function getModuleRepository(): ModuleRepository
    {
        $finder = new ContainerFinder(Context::getContext());
        $sfContainer = $finder->getContainer();

        return $sfContainer->get('prestashop.adapter.module.repository.module_repository');
    }

    /**
     * Return installed modules.
     *
     * @param int $position Take only positionnables modules
     *
     * @return array Modules
     */
    public static function getModulesInstalled($position = 0)
    {
        $sql = 'SELECT m.* FROM `' . _DB_PREFIX_ . 'module` m ';
        if ($position) {
            $sql .= 'LEFT JOIN `' . _DB_PREFIX_ . 'hook_module` hm ON m.`id_module` = hm.`id_module`
                 LEFT JOIN `' . _DB_PREFIX_ . 'hook` k ON hm.`id_hook` = k.`id_hook`
                 WHERE k.`position` = 1
                 GROUP BY m.id_module';
        }

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Returns the list of the payment module associated to the current customer.
     *
     * @see PaymentModule::getInstalledPaymentModules() if you don't care about the context
     *
     * @return array module informations
     */
    public static function getPaymentModules()
    {
        $context = Context::getContext();
        if (isset($context->cart)) {
            $billing = new Address((int) $context->cart->id_address_invoice);
        }

        $use_groups = Group::isFeatureActive();

        $frontend = true;
        $groups = [];
        if (isset($context->employee)) {
            $frontend = false;
        } elseif (isset($context->customer) && $use_groups) {
            $groups = $context->customer->getGroups();
            if (!count($groups)) {
                $groups = [Configuration::get('PS_UNIDENTIFIED_GROUP')];
            }
        }

        $hook_payment = 'Payment';
        if (Db::getInstance()->getValue('SELECT `id_hook` FROM `' . _DB_PREFIX_ . 'hook` WHERE `name` = \'paymentOptions\'')) {
            $hook_payment = 'paymentOptions';
        }

        $list = Shop::getContextListShopID();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT DISTINCT m.`id_module`, h.`id_hook`, m.`name`, hm.`position`
        FROM `' . _DB_PREFIX_ . 'module` m
        ' . ($frontend ? 'LEFT JOIN `' . _DB_PREFIX_ . 'module_country` mc ON (m.`id_module` = mc.`id_module` AND mc.id_shop = ' . (int) $context->shop->id . ')' : '') . '
        ' . ($frontend && $use_groups ? 'INNER JOIN `' . _DB_PREFIX_ . 'module_group` mg ON (m.`id_module` = mg.`id_module` AND mg.id_shop = ' . (int) $context->shop->id . ')' : '') . '
        ' . ($frontend && isset($context->customer) && $use_groups ? 'INNER JOIN `' . _DB_PREFIX_ . 'customer_group` cg on (cg.`id_group` = mg.`id_group`AND cg.`id_customer` = ' . (int) $context->customer->id . ')' : '') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'hook_module` hm ON hm.`id_module` = m.`id_module`
        LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON hm.`id_hook` = h.`id_hook`
        WHERE h.`name` = \'' . pSQL($hook_payment) . '\'
        ' . (isset($billing) && $frontend ? 'AND mc.id_country = ' . (int) $billing->id_country : '') . '
        AND (SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'module_shop ms WHERE ms.id_module = m.id_module AND ms.id_shop IN(' . implode(', ', $list) . ')) = ' . count($list) . '
        AND hm.id_shop IN(' . implode(', ', $list) . ')
        ' . ((count($groups) && $frontend && $use_groups) ? 'AND (mg.`id_group` IN (' . implode(', ', $groups) . '))' : '') . '
        GROUP BY hm.id_hook, hm.id_module
        ORDER BY hm.`position`, m.`name` DESC');
    }

    /**
     * Get translation for a given module text.
     *
     * Note: $specific parameter is mandatory for library files.
     * Otherwise, translation key will not match for Module library
     * when module is loaded with eval() Module::getModulesOnDisk()
     *
     * @param string $string String to translate
     * @param bool|string $specific filename to use in translation key
     * @param string|null $locale Locale to translate to
     *
     * @return string Translation
     */
    public function l($string, $specific = false, $locale = null)
    {
        if (static::$_generate_config_xml_mode) {
            return $string;
        }

        return Translate::getModuleTranslation(
            $this,
            $string,
            ($specific) ? $specific : $this->name,
            null,
            false,
            $locale
        );
    }

    /**
     * Reposition module
     *
     * @param int $id_hook Hook ID
     * @param bool $way Up (0) or Down (1)
     * @param int|null $position
     *
     * @return bool
     */
    public function updatePosition($id_hook, $way, $position = null)
    {
        foreach (Shop::getContextListShopID() as $shop_id) {
            $getAvailableHookPositionsSql = 'SELECT hm.`id_module`, hm.`position`, hm.`id_hook`
                    FROM `' . _DB_PREFIX_ . 'hook_module` hm
                    WHERE hm.`id_hook` = ' . (int) $id_hook . ' AND hm.`id_shop` = ' . $shop_id . '
                    ORDER BY hm.`position` ' . ($way ? 'ASC' : 'DESC');

            if (!$sqlResult = Db::getInstance()->executeS($getAvailableHookPositionsSql)) {
                // no hook positions available
                continue;
            }
            if (count($sqlResult) === 1) {
                // if there is only 1 position available, it cannot be updated
                return false;
            }

            foreach ($sqlResult as $positionNumber => $positionSettings) {
                $thisIsTheSettingsForThisModule = ((int) $positionSettings[$this->identifier] == (int) $this->id);

                if ($thisIsTheSettingsForThisModule) {
                    $thisModulePositionNumber = $positionNumber;

                    break;
                }
            }

            if (!isset($thisModulePositionNumber)) {
                // could not find hook positions for this module
                return false;
            }
            if (!isset($sqlResult[$thisModulePositionNumber])) {
                // ok this one is really weird
                return false;
            }
            if (!isset($sqlResult[$thisModulePositionNumber + 1])) {
                // no alternative position available following $way, so position cannot be updated
                return false;
            }

            $from = $sqlResult[$thisModulePositionNumber];
            $to = $sqlResult[$thisModulePositionNumber + 1];

            if (!empty($position)) {
                $to['position'] = (int) $position;
            }

            $minPosition = min((int) $from['position'], (int) $to['position']);
            $maxPosition = max((int) $from['position'], (int) $to['position']);

            $shiftHookPositionsSql = 'UPDATE `' . _DB_PREFIX_ . 'hook_module`
                SET position = position ' . ($way ? '- 1' : '+ 1') . '
                WHERE position BETWEEN ' . $minPosition . ' AND ' . $maxPosition . '
                AND `id_hook` = ' . (int) $from['id_hook'] . ' AND `id_shop` = ' . $shop_id;

            if (!Db::getInstance()->execute($shiftHookPositionsSql)) {
                return false;
            }

            $createMissingPositionSql = 'UPDATE `' . _DB_PREFIX_ . 'hook_module`
                SET `position`=' . (int) $to['position'] . '
                WHERE `' . pSQL($this->identifier) . '` = ' . (int) $from[$this->identifier] . '
                AND `id_hook` = ' . (int) $to['id_hook'] . ' AND `id_shop` = ' . $shop_id;
            if (!Db::getInstance()->execute($createMissingPositionSql)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reorder modules position
     *
     * @param int $id_hook Hook ID
     * @param array|null $shop_list List of shop
     *
     * @return bool
     */
    public function cleanPositions($id_hook, $shop_list = null)
    {
        $sql = 'SELECT `id_module`, `id_shop`
            FROM `' . _DB_PREFIX_ . 'hook_module`
            WHERE `id_hook` = ' . (int) $id_hook . '
            ' . ((null !== $shop_list && $shop_list) ? ' AND `id_shop` IN(' . implode(', ', array_map('intval', $shop_list)) . ')' : '') . '
            ORDER BY `position`';
        $results = Db::getInstance()->executeS($sql);
        $position = [];
        foreach ($results as $row) {
            if (!isset($position[$row['id_shop']])) {
                $position[$row['id_shop']] = 1;
            }

            $sql = 'UPDATE `' . _DB_PREFIX_ . 'hook_module`
                SET `position` = ' . $position[$row['id_shop']] . '
                WHERE `id_hook` = ' . (int) $id_hook . '
                AND `id_module` = ' . $row['id_module'] . ' AND `id_shop` = ' . $row['id_shop'];
            Db::getInstance()->execute($sql);
            ++$position[$row['id_shop']];
        }

        return true;
    }

    /**
     * Helper displaying error message(s).
     *
     * @param string|array $error
     *
     * @return string
     */
    public function displayError($error)
    {
        $output = '
        <div class="bootstrap">
        <div class="module_error alert alert-danger" >
            <button type="button" class="close" data-dismiss="alert">&times;</button>';

        if (is_array($error)) {
            $output .= '<ul>';
            foreach ($error as $msg) {
                $output .= '<li>' . $msg . '</li>';
            }
            $output .= '</ul>';
        } else {
            $output .= $error;
        }

        // Close div openned previously
        $output .= '</div></div>';

        return $output;
    }

    /**
     * Helper displaying warning message(s).
     *
     * @param string|array $warning
     *
     * @return string
     */
    public function displayWarning($warning)
    {
        $output = '
        <div class="bootstrap">
        <div class="module_warning alert alert-warning" >
            <button type="button" class="close" data-dismiss="alert">&times;</button>';

        if (is_array($warning)) {
            $output .= '<ul>';
            foreach ($warning as $msg) {
                $output .= '<li>' . $msg . '</li>';
            }
            $output .= '</ul>';
        } else {
            $output .= $warning;
        }

        // Close div openned previously
        $output .= '</div></div>';

        return $output;
    }

    /**
     * Helper displaying confirmation message.
     *
     * @param string $string
     *
     * @return string
     */
   public function displayConfirmation($string)
{
    $output = '
    <div class="bootstrap">
        <div class="module_confirmation conf confirm alert alert-success" id="custom-confirmation-message">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            ' . $string . '
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
                var el = document.getElementById("custom-confirmation-message");
                if (el) {
                    el.style.transition = "opacity 0.5s ease";
                    el.style.opacity = 0;
                    setTimeout(function () {
                        el.remove();
                    }, 500); // wait for fade-out to complete
                }
            }, 8000); // Hide after 8 seconds
        });
    </script>';

    return $output;
}


    /**
     * Helper displaying information message(s).
     *
     * @param string|array $information
     *
     * @return string
     */
    public function displayInformation($information)
    {
        $output = '
        <div class="bootstrap">
        <div class="module_info info alert alert-info">
            <button type="button" class="close" data-dismiss="alert">&times;</button>';

        if (is_array($information)) {
            $output .= '<ul>';
            foreach ($information as $msg) {
                $output .= '<li>' . $msg . '</li>';
            }
            $output .= '</ul>';
        } else {
            $output .= $information;
        }

        // Close div openned previously
        $output .= '</div></div>';

        return $output;
    }

    /**
     * Return exceptions for module in hook
     *
     * @param int $id_module Module ID
     * @param int $id_hook Hook ID
     * @param bool $dispatch
     *
     * @return array Exceptions
     */
    public static function getExceptionsStatic($id_module, $id_hook, $dispatch = false)
    {
        $cache_id = 'exceptionsCache';
        if (!Cache::isStored($cache_id)) {
            $exceptions_cache = [];
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'hook_module_exceptions`
                WHERE `id_shop` IN (' . implode(', ', Shop::getContextListShopID()) . ')';
            $db = Db::getInstance();
            $result = $db->executeS($sql, false);
            while ($row = $db->nextRow($result)) {
                if (!$row['file_name']) {
                    continue;
                }
                $key = $row['id_hook'] . '-' . $row['id_module'];
                if (!isset($exceptions_cache[$key])) {
                    $exceptions_cache[$key] = [];
                }
                if (!isset($exceptions_cache[$key][$row['id_shop']])) {
                    $exceptions_cache[$key][$row['id_shop']] = [];
                }
                $exceptions_cache[$key][$row['id_shop']][] = $row['file_name'];
            }
            Cache::store($cache_id, $exceptions_cache);
        } else {
            $exceptions_cache = Cache::retrieve($cache_id);
        }

        $key = $id_hook . '-' . $id_module;
        $array_return = [];
        if ($dispatch) {
            foreach (Shop::getContextListShopID() as $shop_id) {
                if (isset($exceptions_cache[$key], $exceptions_cache[$key][$shop_id])) {
                    $array_return[$shop_id] = $exceptions_cache[$key][$shop_id];
                }
            }
        } else {
            foreach (Shop::getContextListShopID() as $shop_id) {
                if (isset($exceptions_cache[$key], $exceptions_cache[$key][$shop_id])) {
                    foreach ($exceptions_cache[$key][$shop_id] as $file) {
                        if (!in_array($file, $array_return)) {
                            $array_return[] = $file;
                        }
                    }
                }
            }
        }

        return $array_return;
    }

    /**
     * Return exceptions for module in hook
     *
     * @param int $id_hook Hook ID
     * @param bool $dispatch
     *
     * @return array Exceptions
     */
    public function getExceptions($id_hook, $dispatch = false)
    {
        return Module::getExceptionsStatic($this->id, $id_hook, $dispatch);
    }

    /**
     * @param string $module_name
     *
     * @return bool
     *
     * @deprecated since 1.7
     * @see  PrestaShop\PrestaShop\Core\Module\ModuleManager->isInstalled($name)
     */
    public static function isInstalled($module_name)
    {
        if (!Cache::isStored('Module::isInstalled' . $module_name)) {
            $id_module = Module::getModuleIdByName($module_name);
            Cache::store('Module::isInstalled' . $module_name, (bool) $id_module);

            return (bool) $id_module;
        }

        return Cache::retrieve('Module::isInstalled' . $module_name);
    }

    public function isEnabledForShopContext()
    {
        return (bool) Db::getInstance()->getValue(
            'SELECT id_module
            FROM `' . _DB_PREFIX_ . 'module_shop`
            WHERE id_module=' . (int) $this->id . ' AND id_shop IN (' . implode(',', array_map('intval', Shop::getContextListShopID())) . ')
            GROUP BY id_module
            HAVING COUNT(*)=' . (int) count(Shop::getContextListShopID())
        );
    }

    /**
     * @param string $module_name
     *
     * @return bool
     *
     * @deprecated since 1.7
     * @see  PrestaShop\PrestaShop\Core\Module\ModuleManager->isEnabled($name)
     */
    public static function isEnabled($module_name)
    {
        if (!Cache::isStored('Module::isEnabled' . $module_name)) {
            $active = false;
            $id_module = Module::getModuleIdByName($module_name);
            if (Db::getInstance()->getValue('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` = ' . (int) $id_module . ' AND `id_shop` = ' . (int) Context::getContext()->shop->id)) {
                $active = true;
            }
            Cache::store('Module::isEnabled' . $module_name, (bool) $active);

            return (bool) $active;
        }

        return Cache::retrieve('Module::isEnabled' . $module_name);
    }

    /**
     * Check if module is registered on hook
     *
     * @param string $hook Hook name
     *
     * @return bool
     */
    public function isRegisteredInHook($hook)
    {
        if (!$this->id) {
            return false;
        }

        return Hook::isModuleRegisteredOnHook(
            $this,
            $hook,
            (int) Context::getContext()->shop->id
        );
    }

    /**
     * Template management (display, overload, cache).
     */
    protected static function _isTemplateOverloadedStatic($module_name, $template)
    {
        if (Tools::file_exists_cache(_PS_THEME_DIR_ . 'modules/' . $module_name . '/' . $template)) {
            return _PS_THEME_DIR_ . 'modules/' . $module_name . '/' . $template;
        } elseif (Tools::file_exists_cache(_PS_THEME_DIR_ . 'modules/' . $module_name . '/views/templates/hook/' . $template)) {
            return _PS_THEME_DIR_ . 'modules/' . $module_name . '/views/templates/hook/' . $template;
        } elseif (Tools::file_exists_cache(_PS_THEME_DIR_ . 'modules/' . $module_name . '/views/templates/front/' . $template)) {
            return _PS_THEME_DIR_ . 'modules/' . $module_name . '/views/templates/front/' . $template;
        } elseif (Tools::file_exists_cache(_PS_PARENT_THEME_DIR_ . 'modules/' . $module_name . '/' . $template)) {
            return _PS_PARENT_THEME_DIR_ . 'modules/' . $module_name . '/' . $template;
        } elseif (Tools::file_exists_cache(_PS_PARENT_THEME_DIR_ . 'modules/' . $module_name . '/views/templates/hook/' . $template)) {
            return _PS_PARENT_THEME_DIR_ . 'modules/' . $module_name . '/views/templates/hook/' . $template;
        } elseif (Tools::file_exists_cache(_PS_PARENT_THEME_DIR_ . 'modules/' . $module_name . '/views/templates/front/' . $template)) {
            return _PS_PARENT_THEME_DIR_ . 'modules/' . $module_name . '/views/templates/front/' . $template;
        } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_ . $module_name . '/views/templates/hook/' . $template)) {
            return false;
        } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_ . $module_name . '/views/templates/front/' . $template)) {
            return false;
        } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_ . $module_name . '/' . $template)) {
            return false;
        }

        return null;
    }

    protected function _isTemplateOverloaded($template)
    {
        return Module::_isTemplateOverloadedStatic($this->name, $template);
    }

    protected function getCacheId($name = null)
    {
        $cache_array = [];
        $cache_array[] = $name !== null ? $name : $this->name;
        if (Configuration::get('PS_SSL_ENABLED')) {
            $cache_array[] = (int) Tools::usingSecureMode();
        }
        if (isset($this->context->shop) && Shop::isFeatureActive()) {
            $cache_array[] = (int) $this->context->shop->id;
        }
        if (Group::isFeatureActive() && isset($this->context->customer)) {
            $cache_array[] = (int) Group::getCurrent()->id;
            $cache_array[] = implode('_', Customer::getGroupsStatic($this->context->customer->id));
        }
        if (isset($this->context->language) && Language::isMultiLanguageActivated()) {
            $cache_array[] = (int) $this->context->language->id;
        }
        if (isset($this->context->currency) && Currency::isMultiCurrencyActivated()) {
            $cache_array[] = (int) $this->context->currency->id;
        }
        if (isset($this->context->country)) {
            $cache_array[] = (int) $this->context->country->id;
        }

        return implode('|', $cache_array);
    }

    public function display($file, $template, $cache_id = null, $compile_id = null)
    {
        if (($overloaded = Module::_isTemplateOverloadedStatic(basename($file, '.php'), $template)) === null) {
            return Context::getContext()->getTranslator()->trans('No template found for module', [], 'Admin.Modules.Notification') . ' ' . basename($file, '.php') . (_PS_MODE_DEV_ ? ' (' . $template . ')' : '');
        } else {
            $this->smarty->assign([
                'module_dir' => __PS_BASE_URI__ . 'modules/' . basename($file, '.php') . '/',
                'module_template_dir' => ($overloaded ? _THEME_DIR_ : __PS_BASE_URI__) . 'modules/' . basename($file, '.php') . '/',
                'allow_push' => false, // required by dashboard modules
            ]);

            if ($cache_id !== null) {
                Tools::enableCache();
            }
            if ($compile_id === null) {
                $compile_id = $this->getDefaultCompileId();
            }

            $result = $this->getCurrentSubTemplate($template, $cache_id, $compile_id)->fetch();

            if ($cache_id !== null) {
                Tools::restoreCacheSettings();
            }

            $this->resetCurrentSubTemplate($template, $cache_id, $compile_id);

            return $result;
        }
    }

    /**
     * Use this method to return the result of a smarty template when assign data only locally with $this->smarty->assign().
     *
     * @param string $templatePath relative path the template file, from the module root dir
     * @param string|null $cache_id
     * @param string|null $compile_id
     *
     * @return string
     */
    public function fetch($templatePath, $cache_id = null, $compile_id = null)
    {
        if ($cache_id !== null) {
            Tools::enableCache();
        }
        if ($compile_id === null) {
            $compile_id = $this->getDefaultCompileId();
        }

        $template = $this->context->smarty->createTemplate(
            $templatePath,
            $cache_id,
            $compile_id,
            $this->smarty
        );

        if ($cache_id !== null) {
            Tools::restoreCacheSettings();
        }

        return $template->fetch();
    }

    /**
     * @param string $template
     * @param string|null $cache_id
     * @param string|null $compile_id
     *
     * @return Smarty_Internal_Template
     */
    protected function getCurrentSubTemplate($template, $cache_id = null, $compile_id = null)
    {
        if ($compile_id === null) {
            $compile_id = $this->getDefaultCompileId();
        }

        if (!isset($this->current_subtemplate[$template . '_' . $cache_id . '_' . $compile_id])) {
            if (false === strpos($template, 'module:')
                && !file_exists(_PS_ROOT_DIR_ . '/' . $template)
                && !file_exists($template)
            ) {
                $template = $this->getTemplatePath($template);
            }

            $this->current_subtemplate[$template . '_' . $cache_id . '_' . $compile_id] = $this->context->smarty->createTemplate(
                $template,
                $cache_id,
                $compile_id,
                $this->smarty
            );
        }

        return $this->current_subtemplate[$template . '_' . $cache_id . '_' . $compile_id];
    }

    protected function resetCurrentSubTemplate($template, $cache_id, $compile_id)
    {
        $this->current_subtemplate[$template . '_' . $cache_id . '_' . $compile_id] = null;
    }

    /**
     * Get realpath of a template of current module (check if template is overridden too).
     *
     * @since 1.5.0
     *
     * @param string $template
     *
     * @return string|null
     */
    public function getTemplatePath($template)
    {
        $overloaded = $this->_isTemplateOverloaded($template);
        if ($overloaded === null) {
            return null;
        }

        if ($overloaded) {
            return $overloaded;
        } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_ . $this->name . '/views/templates/hook/' . $template)) {
            return _PS_MODULE_DIR_ . $this->name . '/views/templates/hook/' . $template;
        } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_ . $this->name . '/views/templates/front/' . $template)) {
            return _PS_MODULE_DIR_ . $this->name . '/views/templates/front/' . $template;
        } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_ . $this->name . '/' . $template)) {
            return _PS_MODULE_DIR_ . $this->name . '/' . $template;
        } else {
            return null;
        }
    }

    public function isCached($template, $cache_id = null, $compile_id = null)
    {
        Tools::enableCache();
        if (false === strpos($template, 'module:') && !file_exists(_PS_ROOT_DIR_ . '/' . $template)) {
            $template = $this->getTemplatePath($template);
        }
        if ($compile_id === null) {
            $compile_id = $this->getDefaultCompileId();
        }

        $is_cached = $this->getCurrentSubTemplate($template, $cache_id, $compile_id)->isCached($template, $cache_id, $compile_id);
        Tools::restoreCacheSettings();

        return $is_cached;
    }

    /**
     * Clear template cache.
     *
     * @param string $template Template name
     * @param string|null $cache_id
     * @param string|null $compile_id
     *
     * @return int Number of template cleared
     */
    protected function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        static $ps_smarty_clear_cache = null;
        if ($ps_smarty_clear_cache === null) {
            $ps_smarty_clear_cache = Configuration::get('PS_SMARTY_CLEAR_CACHE');
        }
        if ($compile_id === null) {
            $compile_id = $this->getDefaultCompileId();
        }

        if (static::$_batch_mode) {
            if ($ps_smarty_clear_cache == 'never') {
                return 0;
            }

            if ($cache_id === null) {
                $cache_id = $this->name;
            }

            $key = $template . '-' . $cache_id . '-' . $compile_id;
            if (!isset(static::$_defered_clearCache[$key])) {
                static::$_defered_clearCache[$key] = [$this->getTemplatePath($template), $cache_id, $compile_id];
            }

            return 0;
        } else {
            if ($ps_smarty_clear_cache == 'never') {
                return 0;
            }

            if ($cache_id === null) {
                $cache_id = $this->name;
            }

            Tools::enableCache();
            $number_of_template_cleared = Tools::clearCache(Context::getContext()->smarty, $this->getTemplatePath($template), $cache_id, $compile_id);
            Tools::restoreCacheSettings();

            return $number_of_template_cleared;
        }
    }

    /**
     * Clear defered template cache.
     *
     * @param string $template_path Template path
     * @param string|null $cache_id
     * @param string|null $compile_id
     *
     * @return int Number of template cleared
     */
    public static function _deferedClearCache($template_path, $cache_id, $compile_id)
    {
        Tools::enableCache();
        $number_of_template_cleared = Tools::clearCache(Context::getContext()->smarty, $template_path, $cache_id, $compile_id);
        Tools::restoreCacheSettings();

        return $number_of_template_cleared;
    }

    protected function _generateConfigXml()
    {
        $author_uri = '';
        if ($this->author_uri) {
            $author_uri = '<author_uri><![CDATA[' . Tools::htmlentitiesUTF8($this->author_uri) . ']]></author_uri>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
<module>
    <name>' . $this->name . '</name>
    <displayName><![CDATA[' . str_replace('&amp;', '&', Tools::htmlentitiesUTF8($this->displayName)) . ']]></displayName>
    <version><![CDATA[' . $this->version . ']]></version>
    <description><![CDATA[' . str_replace('&amp;', '&', Tools::htmlentitiesUTF8($this->description)) . ']]></description>
    <author><![CDATA[' . str_replace('&amp;', '&', Tools::htmlentitiesUTF8($this->author)) . ']]></author>'
        . $author_uri . '
    <tab><![CDATA[' . Tools::htmlentitiesUTF8($this->tab) . ']]></tab>' . (!empty($this->confirmUninstall) ? "\n\t" . '<confirmUninstall><![CDATA[' . $this->confirmUninstall . ']]></confirmUninstall>' : '') . '
    <is_configurable>' . (int) $this->is_configurable . '</is_configurable>
    <need_instance>' . (int) $this->need_instance . '</need_instance>' . (!empty($this->limited_countries) ? "\n\t" . '<limited_countries>' . (count($this->limited_countries) == 1 ? $this->limited_countries[0] : '') . '</limited_countries>' : '') . '
</module>';
        if (is_writable(_PS_MODULE_DIR_ . $this->name . '/')) {
            $iso = substr(Context::getContext()->language->iso_code, 0, 2);
            $file = _PS_MODULE_DIR_ . $this->name . '/' . ($iso == 'en' ? 'config.xml' : 'config_' . $iso . '.xml');
            if (!@file_put_contents($file, $xml)) {
                if (!is_writable($file)) {
                    @unlink($file);
                    @file_put_contents($file, $xml);
                }
            }
            @chmod($file, 0664);
        }
    }

    /**
     * Check if the module is transplantable on the hook in parameter.
     *
     * @param string $hook_name
     *
     * @return bool if module can be transplanted on hook
     */
    public function isHookableOn($hook_name)
    {
        if ($this instanceof WidgetInterface) {
            if (Hook::isDisplayHookName($hook_name)) {
                return true;
            }
        }

        return is_callable([$this, 'hook' . ucfirst($hook_name)]);
    }

    /**
     * @param int $idProfile
     *
     * @return array
     */
    public static function getModulesAccessesByIdProfile($idProfile)
    {
        if (empty(static::$cache_lgc_access)) {
            self::warmupRolesCache();
        }

        $roles = static::$cache_lgc_access;

        if ($idProfile == _PS_ADMIN_PROFILE_) {
            foreach ($roles as $moduleName => $permissions) {
                $roles[$moduleName] = array_merge($permissions, [
                    'add' => '1',
                    'view' => '1',
                    'configure' => '1',
                    'uninstall' => '1',
                ]);
            }
        } else {
            $profileRoles = Db::getInstance()->executeS('
            SELECT `slug`,
                `slug` LIKE "%CREATE" as "add",
                `slug` LIKE "%READ" as "view",
                `slug` LIKE "%UPDATE" as "configure",
                `slug` LIKE "%DELETE" as "uninstall"
            FROM `' . _DB_PREFIX_ . 'authorization_role` a
            LEFT JOIN `' . _DB_PREFIX_ . 'module_access` j ON j.id_authorization_role = a.id_authorization_role
            WHERE `slug` LIKE "' . Permission::PREFIX_MODULE . '%"
            AND j.id_profile = "' . (int) $idProfile . '"
            ORDER BY a.slug
        ');

            foreach ($profileRoles as $role) {
                preg_match(
                    '/' . Permission::PREFIX_MODULE . '(?P<moduleName>[A-Z0-9_]+)_(?P<auth>[A-Z]+)/',
                    $role['slug'],
                    $matches
                );

                if ($key = array_search('1', $role)) {
                    $roles[$matches['moduleName']][$key] = '1';
                }
            }
        }

        return $roles;
    }

    private static function warmupRolesCache()
    {
        $result = Db::getInstance()->executeS('
            SELECT `slug`,
                `slug` LIKE "%CREATE" as "add",
                `slug` LIKE "%READ" as "view",
                `slug` LIKE "%UPDATE" as "configure",
                `slug` LIKE "%DELETE" as "uninstall"
            FROM `' . _DB_PREFIX_ . 'authorization_role` a
            WHERE `slug` LIKE "' . Permission::PREFIX_MODULE . '%"
            ORDER BY a.slug
        ');

        foreach ($result as $row) {
            preg_match(
                '/' . Permission::PREFIX_MODULE . '(?P<moduleName>[A-Z0-9_]+)_(?P<auth>[A-Z]+)/',
                $row['slug'],
                $matches
            );

            $m = Module::getInstanceByName(strtolower($matches['moduleName']));

            // the following condition handles invalid modules
            if ($m && !isset(static::$cache_lgc_access[$matches['moduleName']])) {
                static::$cache_lgc_access[$matches['moduleName']] = [];
                static::$cache_lgc_access[$matches['moduleName']]['id_module'] = $m->id;
                static::$cache_lgc_access[$matches['moduleName']]['name'] = $m->displayName;
                static::$cache_lgc_access[$matches['moduleName']]['add'] = '0';
                static::$cache_lgc_access[$matches['moduleName']]['view'] = '0';
                static::$cache_lgc_access[$matches['moduleName']]['configure'] = '0';
                static::$cache_lgc_access[$matches['moduleName']]['uninstall'] = '0';
            }
        }
    }

    /**
     * Check employee permission for module.
     *
     * @param string $variable (action)
     * @param Employee $employee
     *
     * @return bool if module can be transplanted on hook
     */
    public function getPermission($variable, $employee = null)
    {
        return Module::getPermissionStatic($this->id, $variable, $employee);
    }

    /**
     * Check employee permission for module (static method).
     *
     * @param int $id_module
     * @param string $variable (action)
     * @param Employee $employee
     *
     * @return bool if module can be transplanted on hook
     */
    public static function getPermissionStatic($id_module, $variable, $employee = null)
    {
        if (!in_array($variable, ['view', 'configure', 'uninstall'])) {
            return false;
        }

        if (!$employee) {
            $employee = Context::getContext()->employee;
        }

        if ($employee->id_profile == _PS_ADMIN_PROFILE_) {
            return true;
        }

        $slug = Access::findSlugByIdModule($id_module) . Access::getAuthorizationFromLegacy($variable);

        return Access::isGranted($slug, $employee->id_profile);
    }

    /**
     * Get authorized modules for a client group.
     *
     * @param int $group_id
     * @param array $shops
     *
     * @return array|null
     */
    public static function getAuthorizedModules($group_id, $shops = [1])
    {
        return Db::getInstance()->executeS(
            'SELECT m.`id_module`, m.`name` FROM `' . _DB_PREFIX_ . 'module_group` mg
            LEFT JOIN `' . _DB_PREFIX_ . 'module` m ON (m.`id_module` = mg.`id_module`)
            WHERE mg.`id_group` = ' . (int) $group_id . '
            AND `id_shop` IN ('
                . implode(',', array_map('intval', $shops))
            . ')'
        );
    }

    /**
     * Get ID module by name.
     *
     * @param string $name
     *
     * @return int Module ID
     */
    public static function getModuleIdByName($name)
    {
        $cache_id = 'Module::getModuleIdByName_' . pSQL($name);
        if (!Cache::isStored($cache_id)) {
            $result = (int) Db::getInstance()->getValue('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = "' . pSQL($name) . '"');
            Cache::store($cache_id, $result);

            return $result;
        }

        return Cache::retrieve($cache_id);
    }

    /**
     * Get module errors.
     *
     * @since 1.5.0
     *
     * @return array errors
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Get module messages confirmation.
     *
     * @since 1.5.0
     *
     * @return array conf
     */
    public function getConfirmations()
    {
        return $this->_confirmations;
    }

    /**
     * Get local path for module.
     *
     * @since 1.5.0
     *
     * @return string
     */
    public function getLocalPath()
    {
        return $this->local_path;
    }

    /**
     * Get uri path for module.
     *
     * @since 1.5.0
     *
     * @return string
     */
    public function getPathUri()
    {
        return $this->_path;
    }

    /**
     * Return module position for a given hook
     *
     * @param int $id_hook Hook ID
     *
     * @return int position or 0 if hook not found
     */
    public function getPosition($id_hook)
    {
        return (int) Db::getInstance()->getValue('
            SELECT `position`
            FROM `' . _DB_PREFIX_ . 'hook_module`
            WHERE `id_hook` = ' . (int) $id_hook . '
            AND `id_module` = ' . (int) $this->id . '
            AND `id_shop` = ' . (int) Context::getContext()->shop->id);
    }

    /**
     * Getter for $tabs attribute.
     *
     * @return array
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * add a warning message to display at the top of the admin page.
     *
     * @param string $msg
     *
     * @return false|void
     */
    public function adminDisplayWarning($msg)
    {
        if (!($this->context->controller instanceof AdminController)) {
            return false;
        }
        $this->context->controller->warnings[] = $msg;
    }

    /**
     * add a info message to display at the top of the admin page.
     *
     * @param string $msg
     *
     * @return false|void
     */
    protected function adminDisplayInformation($msg)
    {
        if (!($this->context->controller instanceof AdminController)) {
            return false;
        }
        $this->context->controller->informations[] = $msg;
    }

    /**
     * Install module's controllers using public property $controllers.
     *
     * @return bool
     */
    protected function installControllers()
    {
        foreach ($this->controllers as $controller) {
            $page = 'module-' . $this->name . '-' . $controller;
            $result = Db::getInstance()->getValue('SELECT * FROM ' . _DB_PREFIX_ . 'meta WHERE page="' . pSQL($page) . '"');
            if ((int) $result > 0) {
                continue;
            }

            $meta = new Meta();
            $meta->page = $page;
            $meta->configurable = 1;
            $meta->save();
        }

        return true;
    }

    /**
     * Install overrides files for the module.
     *
     * @return bool
     */
    public function installOverrides()
    {
        if (!is_dir($this->getLocalPath() . 'override')) {
            return true;
        }

        $result = true;
        foreach (Tools::scandir($this->getLocalPath() . 'override', 'php', '', true) as $file) {
            $class = basename($file, '.php');
            if (PrestashopAutoload::getInstance()->getClassPath($class . 'Core') || Module::getModuleIdByName($class)) {
                $result &= $this->addOverride($class);
            }
        }

        return $result;
    }

    /**
     * Uninstall overrides files for the module.
     *
     * @return bool
     */
    public function uninstallOverrides()
    {
        if (!is_dir($this->getLocalPath() . 'override')) {
            return true;
        }

        $result = true;
        foreach (Tools::scandir($this->getLocalPath() . 'override', 'php', '', true) as $file) {
            $class = basename($file, '.php');
            if (PrestashopAutoload::getInstance()->getClassPath($class . 'Core') || Module::getModuleIdByName($class)) {
                $result &= $this->removeOverride($class);
            }
        }

        return $result;
    }

    /**
     * Add all methods in a module override to the override class.
     *
     * @param string $classname
     *
     * @return bool
     */
    public function addOverride($classname)
    {
        $orig_path = $path = PrestashopAutoload::getInstance()->getClassPath($classname . 'Core');
        if (!$path) {
            $path = 'modules' . DIRECTORY_SEPARATOR . $classname . DIRECTORY_SEPARATOR . $classname . '.php';
        }
        $path_override = $this->getLocalPath() . 'override' . DIRECTORY_SEPARATOR . $path;

        if (!file_exists($path_override)) {
            return false;
        } else {
            file_put_contents($path_override, preg_replace('#(\r\n|\r)#ism', "\n", file_get_contents($path_override)));
        }

        $psOverrideDir = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'override';

        $pattern_escape_com = '#(^\s*?\/\/.*?\n|\/\*(?!\n\s+\* module:.*?\* date:.*?\* version:.*?\*\/).*?\*\/)#ism';
        // Check if there is already an override file, if not, we just need to copy the file
        $file = PrestashopAutoload::getInstance()->getClassPath($classname);
        $override_path = _PS_ROOT_DIR_ . '/' . $file;

        if ($file && file_exists($override_path)) {
            // Create directory if not exists
            $this->createOverrideDirectory($psOverrideDir, dirname($override_path));

            // Check if override file is writable
            if (!is_writable(dirname($override_path)) || !is_writable($override_path)) {
                throw new Exception(Context::getContext()->getTranslator()->trans('file (%s) not writable', [$override_path], 'Admin.Notifications.Error'));
            }

            // Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
            do {
                $uniq = uniqid();
            } while (class_exists($classname . 'OverrideOriginal_remove', false));

            // Make a reflection of the override class and the module override class
            $override_file = file($override_path);
            $override_file = array_diff($override_file, ["\n"]);
            eval(
                preg_replace(
                    [
                        '#^\s*<\?(?:php)?#',
                        '#class\s+' . $classname . '\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i',
                    ],
                    [
                        ' ',
                        'class ' . $classname . 'OverrideOriginal' . $uniq . ' extends \stdClass',
                    ],
                    implode('', $override_file)
                )
            );
            $override_class = new ReflectionClass($classname . 'OverrideOriginal' . $uniq);

            $module_file = file($path_override);
            $module_file = array_diff($module_file, ["\n"]);
            eval(
                preg_replace(
                    [
                        '#^\s*<\?(?:php)?#',
                        '#class\s+' . $classname . '(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i',
                    ],
                    [
                        ' ',
                        'class ' . $classname . 'Override' . $uniq . ' extends \stdClass',
                    ],
                    implode('', $module_file)
                )
            );
            $module_class = new ReflectionClass($classname . 'Override' . $uniq);

            // Check if none of the methods already exists in the override class
            foreach ($module_class->getMethods() as $method) {
                if ($override_class->hasMethod($method->getName())) {
                    $method_override = $override_class->getMethod($method->getName());
                    if (preg_match('/module: (.*)/ism', $override_file[$method_override->getStartLine() - 5], $name) && preg_match('/date: (.*)/ism', $override_file[$method_override->getStartLine() - 4], $date) && preg_match('/version: ([0-9.]+)/ism', $override_file[$method_override->getStartLine() - 3], $version)) {
                        throw new Exception(Context::getContext()->getTranslator()->trans('The method %1$s in the class %2$s is already overridden by the module %3$s version %4$s at %5$s.', [$method->getName(), $classname, $name[1], $version[1], $date[1]], 'Admin.Modules.Notification'));
                    }

                    throw new Exception(Context::getContext()->getTranslator()->trans('The method %1$s in the class %2$s is already overridden.', [$method->getName(), $classname], 'Admin.Modules.Notification'));
                }

                $module_file = preg_replace('/((:?public|private|protected)\s+(static\s+)?function\s+(?:\b' . $method->getName() . '\b))/ism', "/*\n    * module: " . $this->name . "\n    * date: " . date('Y-m-d H:i:s') . "\n    * version: " . $this->version . "\n    */\n    $1", $module_file);
                if ($module_file === null) {
                    throw new Exception(Context::getContext()->getTranslator()->trans('Failed to override method %1$s in class %2$s.', [$method->getName(), $classname], 'Admin.Modules.Notification'));
                }
            }

            // Check if none of the properties already exists in the override class
            foreach ($module_class->getProperties() as $property) {
                if ($override_class->hasProperty($property->getName())) {
                    throw new Exception(Context::getContext()->getTranslator()->trans('The property %1$s in the class %2$s is already defined.', [$property->getName(), $classname], 'Admin.Modules.Notification'));
                }

                $module_file = preg_replace('/((?:public|private|protected)\s)\s*(static\s)?\s*(\w+\s)?\s*(\$\b' . $property->getName() . '\b)/ism', "/*\n    * module: " . $this->name . "\n    * date: " . date('Y-m-d H:i:s') . "\n    * version: " . $this->version . "\n    */\n    $1$2$3$4", $module_file);
                if ($module_file === null) {
                    throw new Exception(Context::getContext()->getTranslator()->trans('Failed to override property %1$s in class %2$s.', [$property->getName(), $classname], 'Admin.Modules.Notification'));
                }
            }

            // Check if none of the constants already exists in the override class
            foreach ($module_class->getConstants() as $constant => $value) {
                if ($override_class->hasConstant($constant)) {
                    throw new Exception(Context::getContext()->getTranslator()->trans('The constant %1$s in the class %2$s is already defined.', [$constant, $classname], 'Admin.Modules.Notification'));
                }

                $module_file = preg_replace('/(const\s)\s*(\b' . $constant . '\b)/ism', "/*\n    * module: " . $this->name . "\n    * date: " . date('Y-m-d H:i:s') . "\n    * version: " . $this->version . "\n    */\n    $1$2", $module_file);
                if ($module_file === null) {
                    throw new Exception(Context::getContext()->getTranslator()->trans('Failed to override constant %1$s in class %2$s.', [$constant, $classname], 'Admin.Modules.Notification'));
                }
            }

            // Insert the methods from module override in override
            $copy_from = array_slice($module_file, $module_class->getStartLine() + 1, $module_class->getEndLine() - $module_class->getStartLine() - 2);
            array_splice($override_file, $override_class->getEndLine() - 1, 0, $copy_from);
            $code = implode('', $override_file);

            file_put_contents($override_path, preg_replace($pattern_escape_com, '', $code));
        } else {
            $override_src = $path_override;

            $override_dest = $psOverrideDir . DIRECTORY_SEPARATOR . $path;
            $dir_name = dirname($override_dest);

            // Create directory if not exists
            $this->createOverrideDirectory($psOverrideDir, $dir_name);

            if (!is_writable($dir_name)) {
                throw new Exception(Context::getContext()->getTranslator()->trans('directory (%s) not writable', [$dir_name], 'Admin.Notifications.Error'));
            }
            $module_file = file($override_src);
            $module_file = array_diff($module_file, ["\n"]);

            if ($orig_path) {
                do {
                    $uniq = uniqid();
                } while (class_exists($classname . 'OverrideOriginal_remove', false));
                eval(
                    preg_replace(
                        [
                            '#^\s*<\?(?:php)?#',
                            '#class\s+' . $classname . '(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i',
                        ],
                        [
                            ' ',
                            'class ' . $classname . 'Override' . $uniq . ' extends \stdClass',
                        ],
                        implode('', $module_file)
                    )
                );
                $module_class = new ReflectionClass($classname . 'Override' . $uniq);

                // For each method found in the override, prepend a comment with the module name and version
                foreach ($module_class->getMethods() as $method) {
                    $module_file = preg_replace('/((:?public|private|protected)\s+(static\s+)?function\s+(?:\b' . $method->getName() . '\b))/ism', "/*\n    * module: " . $this->name . "\n    * date: " . date('Y-m-d H:i:s') . "\n    * version: " . $this->version . "\n    */\n    $1", $module_file);
                    if ($module_file === null) {
                        throw new Exception(Context::getContext()->getTranslator()->trans('Failed to override method %1$s in class %2$s.', [$method->getName(), $classname], 'Admin.Modules.Notification'));
                    }
                }

                // Same loop for properties
                foreach ($module_class->getProperties() as $property) {
                    $module_file = preg_replace('/((?:public|private|protected)\s)\s*(static\s)?\s*(\w+\s)?\s*(\$\b' . $property->getName() . '\b)/ism', "/*\n    * module: " . $this->name . "\n    * date: " . date('Y-m-d H:i:s') . "\n    * version: " . $this->version . "\n    */\n    $1$2$3$4", $module_file);
                    if ($module_file === null) {
                        throw new Exception(Context::getContext()->getTranslator()->trans('Failed to override property %1$s in class %2$s.', [$property->getName(), $classname], 'Admin.Modules.Notification'));
                    }
                }

                // Same loop for constants
                foreach ($module_class->getConstants() as $constant => $value) {
                    $module_file = preg_replace('/(const\s)\s*(\b' . $constant . '\b)/ism', "/*\n    * module: " . $this->name . "\n    * date: " . date('Y-m-d H:i:s') . "\n    * version: " . $this->version . "\n    */\n    $1$2", $module_file);
                    if ($module_file === null) {
                        throw new Exception(Context::getContext()->getTranslator()->trans('Failed to override constant %1$s in class %2$s.', [$constant, $classname], 'Admin.Modules.Notification'));
                    }
                }
            }

            file_put_contents($override_dest, preg_replace($pattern_escape_com, '', $module_file));

            // Re-generate the class index
            PrestashopAutoload::getInstance()->generateIndex();
        }

        return true;
    }

    /**
     * Create override directory and add index.php in all tree
     *
     * @param string $directoryOverride Absolute path of the override directory
     * @param string $directoryPath Absolute path of the overriden file directory
     *
     * @return void
     */
    private function createOverrideDirectory(string $directoryOverride, string $directoryPath): void
    {
        if (is_dir($directoryPath)) {
            return;
        }
        $fs = new SfFileSystem();

        // Create directory (in recursive mode)
        $fs->mkdir($directoryPath, FileSystem::DEFAULT_MODE_FOLDER);

        // Copy index.php to each directory
        $splDir = new SplFileInfo($directoryPath . DIRECTORY_SEPARATOR . 'index.php');
        do {
            // Copy file
            $fs->copy(
                $directoryOverride . DIRECTORY_SEPARATOR . 'index.php',
                $splDir->getPath() . DIRECTORY_SEPARATOR . 'index.php'
            );

            // Get Parent directory
            $splDir = $splDir->getPathInfo();
        } while ($splDir->getPath() !== $directoryOverride);
    }

    /**
     * Remove all methods in a module override from the override class.
     *
     * @param string $classname
     *
     * @return bool
     */
    public function removeOverride($classname)
    {
        $orig_path = $path = PrestashopAutoload::getInstance()->getClassPath($classname . 'Core');
        $file = PrestashopAutoload::getInstance()->getClassPath($classname);

        if ($orig_path && !$file) {
            return true;
        } elseif (!$orig_path && Module::getModuleIdByName($classname)) {
            $path = 'modules' . DIRECTORY_SEPARATOR . $classname . DIRECTORY_SEPARATOR . $classname . '.php';
        }

        // Check if override file is writable
        if ($orig_path) {
            $override_path = _PS_ROOT_DIR_ . '/' . $file;
        } else {
            $override_path = _PS_OVERRIDE_DIR_ . $path;
        }

        if (!is_file($override_path)) {
            return true;
        }

        if (!is_writable($override_path)) {
            return false;
        }

        file_put_contents($override_path, preg_replace('#(\r\n|\r)#ism', "\n", file_get_contents($override_path)));

        $code = '';
        if ($orig_path) {
            // Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
            do {
                $uniq = uniqid();
            } while (class_exists($classname . 'OverrideOriginal_remove', false));

            // Make a reflection of the override class and the module override class
            $override_file = file($override_path);

            eval(
                preg_replace(
                    [
                        '#^\s*<\?(?:php)?#',
                        '#class\s+' . $classname . '\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i',
                    ],
                    [
                        ' ',
                        'class ' . $classname . 'OverrideOriginal_remove' . $uniq . ' extends \stdClass',
                    ],
                    implode('', $override_file)
                )
            );
            $override_class = new ReflectionClass($classname . 'OverrideOriginal_remove' . $uniq);

            $module_file = file($this->getLocalPath() . 'override/' . $path);
            eval(
                preg_replace(
                    [
                        '#^\s*<\?(?:php)?#',
                        '#class\s+' . $classname . '(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i',
                    ],
                    [
                        ' ',
                        'class ' . $classname . 'Override_remove' . $uniq . ' extends \stdClass',
                    ],
                    implode('', $module_file)
                )
            );
            $module_class = new ReflectionClass($classname . 'Override_remove' . $uniq);

            // Remove methods from override file
            foreach ($module_class->getMethods() as $method) {
                if (!$override_class->hasMethod($method->getName())) {
                    continue;
                }

                $method = $override_class->getMethod($method->getName());
                $length = $method->getEndLine() - $method->getStartLine() + 1;

                $module_method = $module_class->getMethod($method->getName());

                $override_file_orig = $override_file;

                $orig_content = preg_replace('/\s/', '', implode('', array_splice($override_file, $method->getStartLine() - 1, $length, array_pad([], $length, '#--remove--#'))));
                $module_content = preg_replace('/\s/', '', implode('', array_splice($module_file, $module_method->getStartLine() - 1, $length, array_pad([], $length, '#--remove--#'))));

                $replace = true;
                if (preg_match('/\* module: (' . $this->name . ')/ism', $override_file[$method->getStartLine() - 5])) {
                    $override_file[$method->getStartLine() - 6] = $override_file[$method->getStartLine() - 5] = $override_file[$method->getStartLine() - 4] = $override_file[$method->getStartLine() - 3] = $override_file[$method->getStartLine() - 2] = '#--remove--#';
                    $replace = false;
                }

                if (md5($module_content) != md5($orig_content) && $replace) {
                    $override_file = $override_file_orig;
                }
            }

            // Remove properties from override file
            foreach ($module_class->getProperties() as $property) {
                if (!$override_class->hasProperty($property->getName())) {
                    continue;
                }

                // Replace the declaration line by #--remove--#
                foreach ($override_file as $line_number => &$line_content) {
                    if (preg_match('/(public|private|protected)\s+(static\s+)?\s*(\w+\s+)?(\$)?' . $property->getName() . '/i', $line_content)) {
                        if (preg_match('/\* module: (' . $this->name . ')/ism', $override_file[$line_number - 4])) {
                            $override_file[$line_number - 5] = $override_file[$line_number - 4] = $override_file[$line_number - 3] = $override_file[$line_number - 2] = $override_file[$line_number - 1] = '#--remove--#';
                        }
                        $line_content = '#--remove--#';

                        break;
                    }
                }
            }

            // Remove properties from override file
            foreach ($module_class->getConstants() as $constant => $value) {
                if (!$override_class->hasConstant($constant)) {
                    continue;
                }

                // Replace the declaration line by #--remove--#
                foreach ($override_file as $line_number => &$line_content) {
                    if (preg_match('/(const)\s+(static\s+)?(\$)?' . $constant . '/i', $line_content)) {
                        if (preg_match('/\* module: (' . $this->name . ')/ism', $override_file[$line_number - 4])) {
                            $override_file[$line_number - 5] = $override_file[$line_number - 4] = $override_file[$line_number - 3] = $override_file[$line_number - 2] = $override_file[$line_number - 1] = '#--remove--#';
                        }
                        $line_content = '#--remove--#';

                        break;
                    }
                }
            }

            $count = count($override_file);
            for ($i = 0; $i < $count; ++$i) {
                if (preg_match('/(^\s*\/\/.*)/i', $override_file[$i])) {
                    $override_file[$i] = '#--remove--#';
                } elseif (preg_match('/(^\s*\/\*)/i', $override_file[$i])) {
                    if (!preg_match('/(^\s*\* module:)/i', $override_file[$i + 1])
                        && !preg_match('/(^\s*\* date:)/i', $override_file[$i + 2])
                        && !preg_match('/(^\s*\* version:)/i', $override_file[$i + 3])
                        && !preg_match('/(^\s*\*\/)/i', $override_file[$i + 4])) {
                        for (; $override_file[$i] && !preg_match('/(.*?\*\/)/i', $override_file[$i]); ++$i) {
                            $override_file[$i] = '#--remove--#';
                        }
                        $override_file[$i] = '#--remove--#';
                    }
                }
            }

            // Rewrite nice code
            foreach ($override_file as $line) {
                if ($line == '#--remove--#') {
                    continue;
                }

                $code .= $line;
            }

            $to_delete = preg_match('/<\?(?:php)?\s+(?:abstract|interface)?\s*?class\s+' . $classname . '\s+extends\s+' . $classname . 'Core\s*?[{]\s*?[}]/ism', $code);

            if (!$to_delete) {
                // To detect if the class has remaining code, we dynamically create a class which contains the remaining code.
                eval(
                    preg_replace(
                        [
                            '#^\s*<\?(?:php)?#',
                            '#class\s+' . $classname . '\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i',
                        ],
                        [
                            ' ',
                            'class ' . $classname . 'OverrideOriginal_check' . $uniq . ' extends \stdClass',
                        ],
                        $code
                    )
                );

                // Then we use ReflectionClass to analyze what this code actually contains
                $override_class = new ReflectionClass($classname . 'OverrideOriginal_check' . $uniq);

                // If no valuable code remains then we can delete it
                $to_delete = $override_class->getConstants() === []
                    && $override_class->getProperties() === []
                    && $override_class->getMethods() === [];
            }
        }

        if (!isset($to_delete) || $to_delete) {
            // Remove file
            unlink($override_path);

            // Remove directory
            $this->removeOverrideDirectory(
                _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'override',
                dirname($override_path)
            );
        } else {
            file_put_contents($override_path, $code);
        }

        // Re-generate the class index
        PrestashopAutoload::getInstance()->generateIndex();

        return true;
    }

    /**
     * Remove override directory tree if the tree is empty
     *
     * @param string $directoryOverride
     * @param string $directoryPath
     *
     * @return void
     */
    private function removeOverrideDirectory(string $directoryOverride, string $directoryPath): void
    {
        if (!is_dir($directoryPath)) {
            return;
        }

        $fs = new SfFileSystem();

        $splDir = new SplFileInfo($directoryPath);
        do {
            // Check if there is only index.php in directory
            $finder = new Finder();
            $finder
                ->files()
                ->in($splDir->getPathname())
                ->notName('index.php');
            if ($finder->hasResults()) {
                break;
            }

            // Remove index.php
            $fs->remove($splDir->getPathname() . DIRECTORY_SEPARATOR . 'index.php');

            // Remove directory
            $fs->remove($splDir->getPathname());

            // Get Parent directory
            $splDir = $splDir->getPathInfo();
        } while ($splDir->getRealPath() !== $directoryOverride);
    }

    private function getWidgetHooks($existing_hook_ids = [])
    {
        $hooks = array_values(Hook::getHooks(false, true));
        $registeredHookList = Hook::getHookModuleList();

        return array_filter(array_map(function ($hook) use ($registeredHookList, $existing_hook_ids) {
            if (!in_array($hook['id_hook'], $existing_hook_ids)) {
                $hook['registered'] = !empty($registeredHookList[$hook['id_hook']][$this->id]);

                return $hook;
            }

            return null;
        }, $hooks));
    }

    /**
     * Return the hooks list where this module can be hooked.
     *
     * @return array hooks list
     */
    public function getPossibleHooksList()
    {
        $hooks_list = Hook::getHooks();
        $possible_hooks_list = [];
        $registeredHookList = Hook::getHookModuleList();
        foreach ($hooks_list as $current_hook) {
            $hook_name = $current_hook['name'];

            if (!Hook::isAlias($hook_name) && $this instanceof Module && Hook::isHookCallableOn($this, $hook_name)) {
                $possible_hooks_list[] = [
                    'id_hook' => $current_hook['id_hook'],
                    'name' => $hook_name,
                    'description' => $current_hook['description'],
                    'title' => $current_hook['title'],
                    'registered' => !empty($registeredHookList[$current_hook['id_hook']][$this->id]),
                ];
            }
        }

        if ($this instanceof WidgetInterface) {
            $possible_hooks_list = array_merge($this->getWidgetHooks(array_column($possible_hooks_list, 'id_hook')), $possible_hooks_list);
            $name_column = array_column($possible_hooks_list, 'name');
            array_multisort($name_column, SORT_ASC, $possible_hooks_list);
        }

        return $possible_hooks_list;
    }

    /**
     * Retrieve an array of the override in the module.
     *
     * @return array|null
     */
    public function getOverrides()
    {
        if (!is_dir($this->getLocalPath() . 'override')) {
            return null;
        }

        $result = [];
        foreach (Tools::scandir($this->getLocalPath() . 'override', 'php', '', true) as $file) {
            $class = basename($file, '.php');
            if (PrestashopAutoload::getInstance()->getClassPath($class . 'Core') || Module::getModuleIdByName($class)) {
                $result[] = $class;
            }
        }

        return $result;
    }

    public function getTranslator()
    {
        return Context::getContext()->getTranslator();
    }

    protected function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Check if the module uses the new translation system.
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        $moduleName = $this->name;
        $domains = array_keys($this->context->getTranslator()->getCatalogue()->all());
        $moduleBaseDomain = DomainHelper::buildModuleBaseDomain($moduleName);
        $length = strlen($moduleBaseDomain);

        foreach ($domains as $domain) {
            if (substr($domain, 0, $length) === $moduleBaseDomain) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the module is executed in Admin Legacy context.
     *
     * To be removed - because useless - when the migration will be done.
     *
     * @return bool
     */
    public function isAdminLegacyContext()
    {
        return defined('ADMIN_LEGACY_CONTEXT');
    }

    /**
     * Check if the module is executed in Symfony context.
     *
     * To be removed - because useless - when the migration will be done.
     *
     * @return bool
     */
    public function isSymfonyContext()
    {
        return !$this->isAdminLegacyContext() && defined('_PS_ADMIN_DIR_');
    }

    /**
     * Access a service from the container (found from the controller or the context
     * depending on cases). It uses ContainerFinder to find the appropriate container.
     *
     * @param string $serviceName
     *
     * @return object|null If a container is not available it returns false
     *
     * @throws ServiceCircularReferenceException When a circular reference is detected
     * @throws ServiceNotFoundException When the service is not defined
     * @throws Exception
     */
    public function get(string $serviceName): ?object
    {
        if ($serviceName === 'twig') {
            trigger_deprecation('prestashop/prestashop', '9.0', 'Load Twig using $this->getTwig().');

            return $this->getTwig();
        }

        try {
            $container = $this->getContainer();
        } catch (ContainerNotFoundException) {
            return null;
        }

        return $container->get($serviceName);
    }

    /**
     * Check if the container has the requested service, it prevents throwing an ecception when
     * trying to get a service not defined.
     *
     * @param string $serviceName
     *
     * @return bool
     */
    public function has(string $serviceName): bool
    {
        if ($serviceName === 'twig') {
            trigger_deprecation('prestashop/prestashop', '9.0', 'Load Twig using $this->getTwig().');

            return $this->getTwig() !== null;
        }

        try {
            $container = $this->getContainer();
        } catch (ContainerNotFoundException) {
            return false;
        }

        return $container->has($serviceName);
    }

    /**
     * Returns the container depending on the environment:
     *  - Legacy: light container with few services specifically defined for legacy front/admin controllers
     *  - Symfony: symfony container with all the migrated services (CQRS, ...)
     *
     * If you need to detect which kind of container you are using you can check if it is an instance of LegacyContainerInterface,
     * which means it's a legacy/light container.
     *
     * @return ContainerInterface
     *
     * @throws ContainerNotFoundException
     */
    public function getContainer(): ContainerInterface
    {
        if (null === $this->container) {
            $finder = new ContainerFinder($this->context);
            $this->container = $finder->getContainer();
        }

        return $this->container;
    }

    public function getTwig(): ?Environment
    {
        if (method_exists($this->context->controller, 'getTwig')) {
            return $this->context->controller->getTwig();
        }

        return null;
    }

    /**
     * Save dashboard configuration
     *
     * @param array $config
     *
     * @return array Array of errors
     */
    public function validateDashConfig(array $config)
    {
        return [];
    }

    /**
     * Save dashboard configuration
     *
     * @param array $config
     *
     * @return bool Determines if the save returns an error
     */
    public function saveDashConfig(array $config)
    {
        return false;
    }

    /**
     * Returns shop theme name from context as a default compile Id.
     *
     * @return string
     */
    public function getDefaultCompileId()
    {
        return Context::getContext()->shop->theme->getName();
    }

    /**
     * Returns the declared multistore compatibility level
     *
     * @return int
     */
    public function getMultistoreCompatibility(): int
    {
        return $this->multistoreCompatibility;
    }

    /**
     * In order to load or update the module's translations, we just need to clear SfCache.
     * The translator service will be loaded again with the catalogue within the module
     *
     * @throws ContainerNotFoundException
     */
    private function loadBuiltInTranslations(): void
    {
        $modulePath = $this->getLocalPath();
        $translationDir = sprintf('%s/translations/', $modulePath);
        if (!is_dir($translationDir)) {
            return;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.xlf')
            ->in($translationDir);
        if (0 === $finder->count()) {
            return;
        }

        $this->getContainer()->get('prestashop.adapter.cache.clearer.symfony_cache_clearer')->clear();
    }

    public static function resetStaticCache()
    {
        static::$_INSTANCE = [];
        static::$modules_cache = null;
        static::$cachedModuleNames = null;
        Cache::clean('Module::isEnabled*');
    }
}

function ps_module_version_sort($a, $b)
{
    return version_compare($a['version'], $b['version']);
}
