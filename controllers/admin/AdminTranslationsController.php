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
use PrestaShop\PrestaShop\Core\Addon\Theme\Theme;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder;
use PrestaShop\PrestaShop\Core\Foundation\Filesystem\FileSystem;
use PrestaShop\PrestaShop\Core\Security\Permission;

class AdminTranslationsControllerCore extends AdminController
{
    /** Name of theme by default */
    public const DEFAULT_THEME_NAME = _PS_DEFAULT_THEME_NAME_;
    public const TEXTAREA_SIZED = 70;

    /** @var string : Link which list all pack of language */
    protected $link_lang_pack = 'http://i18n.prestashop-project.org/translations/%ps_version%/available_languages.json';

    /** @var int : number of sentence which can be translated */
    protected $total_expression = 0;

    /** @var int : number of sentence which aren't translated */
    protected $missing_translations = 0;

    /** @var array : List of ISO code for all languages */
    protected $all_iso_lang = [];

    /** @var array */
    protected $modules_translations = [];

    /** @var array : List of folder which must be ignored */
    protected static $ignore_folder = ['.', '..', '.svn', '.git', '.htaccess', 'index.php'];

    /** @var array : List of content type accepted for translation mail file */
    protected static $content_type_accepted = ['txt', 'tpl', 'html'];

    /** @var array : List of theme by translation type : FRONT, BACK, ERRORS... */
    protected $translations_informations = [];

    /** @var array : List of all languages */
    protected $languages;

    /** @var array : List of all themes */
    protected $themes;

    /** @var string : Directory of selected theme */
    protected $theme_selected;

    /** @var string : Name of translations type */
    protected $type_selected;

    /** @var Language object : Language for the selected language */
    protected $lang_selected;

    /** @var bool : Is true if number of var exceed the suhosin request or post limit */
    protected $post_limit_exceed = false;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->multishop_context = Shop::CONTEXT_ALL;
        $this->table = 'translations';

        parent::__construct();

        $this->link_lang_pack = str_replace('%ps_version%', _PS_VERSION_, $this->link_lang_pack);

        $this->themes = (new ThemeManagerBuilder($this->context, Db::getInstance()))
            ->buildRepository()
            ->getList();
    }

    /*
     * Set the type which is selected
     */
    public function setTypeSelected($type_selected)
    {
        $this->type_selected = $type_selected;
    }

    /**
     * AdminController::initContent() override.
     *
     * @see AdminController::initContent()
     */
    public function initContent()
    {
        if (null !== $this->type_selected) {
            $method_name = 'initForm' . $this->type_selected;
            if (method_exists($this, $method_name)) {
                $this->content = $this->initForm($method_name);
            } else {
                $this->errors[] = $this->trans('"%type%" does not exist.', ['%type%' => $this->type_selected], 'Admin.Notifications.Error');
                $this->content = $this->initMain();
            }
        } else {
            $this->content = $this->initMain();
        }

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }

    /**
     * This function create vars by default and call the good method for generate form.
     *
     * @param string $method_name
     *
     * @return mixed Call the method $this->method_name()
     */
    public function initForm($method_name)
    {
        // Create a title for each translation page
        $title = $this->trans(
            '%1$s (Language: %2$s, Theme: %3$s)',
            [
                '%1$s' => (empty($this->translations_informations[$this->type_selected]['name']) ? false : $this->translations_informations[$this->type_selected]['name']),
                '%2$s' => $this->lang_selected->name,
                '%3$s' => $this->theme_selected ? $this->theme_selected : $this->trans('None', [], 'Admin.Global'),
            ],
            'Admin.International.Feature'
        );

        // Set vars for all forms
        $this->tpl_view_vars = [
            'lang' => $this->lang_selected->iso_code,
            'title' => $title,
            'type' => $this->type_selected,
            'theme' => $this->theme_selected,
            'post_limit_exceeded' => $this->post_limit_exceed,
            'url_submit' => self::$currentIndex . '&submitTranslations' . ucfirst($this->type_selected) . '=1&token=' . $this->token,
            'url_submit_installed_module' => self::$currentIndex . '&submitSelect' . ucfirst($this->type_selected) . '=1&token=' . $this->token,
            'toggle_button' => $this->displayToggleButton(),
            'textarea_sized' => self::TEXTAREA_SIZED,
        ];

        // Call method initForm for a type
        return $this->{$method_name}();
    }

    /**
     * AdminController::initToolbar() override.
     *
     * @see AdminController::initToolbar()
     */
    public function initToolbar()
    {
        $this->toolbar_btn['save-and-stay'] = [
            'short' => 'SaveAndStay',
            'href' => '#',
            'desc' => $this->trans('Save and stay', [], 'Admin.Actions'),
        ];
        $this->toolbar_btn['save'] = [
            'href' => '#',
            'desc' => $this->trans('Update translations', [], 'Admin.International.Feature'),
        ];
        $this->toolbar_btn['cancel'] = [
            'href' => self::$currentIndex . '&token=' . $this->token,
            'desc' => $this->trans('Cancel', [], 'Admin.Actions'),
        ];
    }

    /**
     * Generate the Main page.
     */
    public function initMain()
    {
        if (
            !in_array(
                $this->authorizationLevel(),
                [
                    Permission::LEVEL_READ,
                    Permission::LEVEL_UPDATE,
                    Permission::LEVEL_CREATE,
                    Permission::LEVEL_DELETE,
                ]
            )
        ) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminDashboard'));
        }

        // Block add/update a language
        $packsToInstall = [];
        $packsToUpdate = [];
        $token = Tools::getAdminToken('AdminLanguages' . (int) Tab::getIdFromClassName('AdminLanguages') . (int) $this->context->employee->id);
        $arrayStreamContext = @stream_context_create(['http' => ['method' => 'GET', 'timeout' => 8]]);

        if ($langPacks = Tools::file_get_contents($this->link_lang_pack, false, $arrayStreamContext)) {
            if ($langPacks = json_decode($langPacks, true)) {
                foreach ($langPacks as $locale => $langName) {
                    $langDetails = Language::getJsonLanguageDetails($locale);
                    if (!Language::isInstalledByLocale($locale)) {
                        $packsToInstall[$locale] = $langDetails['name'];
                    } else {
                        $packsToUpdate[$locale] = $langDetails['name'];
                    }
                }
            }
        }

        $modules = [];
        foreach ($this->getListModules(true) as $module) {
            $modules[$module->name] = [
                'name' => $module->name,
                'displayName' => $module->displayName,
                'urlToTranslate' => !$module->isUsingNewTranslationSystem() ? $this->context->link->getAdminLink(
                    'AdminTranslations',
                    true,
                    [],
                    [
                        'type' => 'modules',
                        'module' => $module->name,
                    ]
                ) : '',
            ];
        }

        $this->tpl_view_vars = [
            'theme_default' => self::DEFAULT_THEME_NAME,
            'theme_lang_dir' => _THEME_LANG_DIR_,
            'token' => $this->token,
            'languages' => $this->languages,
            'translations_type' => $this->translations_informations,
            'packs_to_install' => $packsToInstall,
            'packs_to_update' => $packsToUpdate,
            'url_submit' => self::$currentIndex . '&token=' . $this->token,
            'themes' => $this->themes,
            'modules' => $modules,
            'current_theme_name' => $this->context->shop->theme_name,
            'url_create_language' => $this->context->link->getAdminLink('AdminLanguages', true, [], ['addlang' => '']),
            'level' => $this->authorizationLevel(),
        ];

        $this->toolbar_scroll = false;

        $this->content .= $this->renderKpis();
        $this->content .= parent::renderView();

        return $this->content;
    }

    /**
     * This method merge each arrays of modules translation in the array of modules translations.
     */
    protected function getModuleTranslations()
    {
        global $_MODULE;
        $name_var = (empty($this->translations_informations[$this->type_selected]['var']) ? false : $this->translations_informations[$this->type_selected]['var']);

        if (!isset($_MODULE) && !isset($GLOBALS[$name_var])) {
            $GLOBALS[$name_var] = [];
        } elseif (isset($_MODULE)) {
            $GLOBALS[$name_var] = $_MODULE;
        }
    }

    /**
     * This method is only used by AdminTranslations::submitCopyLang().
     *
     * It try to create folder in new theme.
     *
     * When a translation file is copied for a module, its translation key is wrong.
     * We have to change the translation key and rewrite the file.
     *
     * @param string $dest file name
     *
     * @return bool
     */
    protected function checkDirAndCreate(string $dest)
    {
        $bool = true;

        // To get only folder path
        $path = dirname($dest);

        // If folder wasn't already added
        // Do not use Tools::file_exists_cache because it changes over time!
        if (!file_exists($path)) {
            if (!mkdir($path, FileSystem::DEFAULT_MODE_FOLDER, true)) {
                $bool &= false;
                $this->errors[] = $this->trans('Cannot create the folder "%folder%". Please check your directory writing permissions.', ['%folder%' => $path], 'Admin.International.Notification');
            }
        }

        return $bool;
    }

    /**
     * Read the Post var and write the translation file.
     * This method overwrites the old translation file.
     *
     * @param bool $override_file Set true if this file is a override
     *
     * @throws PrestaShopException
     */
    protected function writeTranslationFile(bool $override_file = false)
    {
        $type = Tools::toCamelCase($this->type_selected, true);

        if (isset($this->translations_informations[$this->type_selected])) {
            $translation_informations = $this->translations_informations[$this->type_selected];
        } else {
            return;
        }

        if ($override_file) {
            $file_path = $translation_informations['override']['dir'] . $translation_informations['override']['file'];
        } else {
            $file_path = $translation_informations['dir'] . $translation_informations['file'];
        }

        if ($file_path && !file_exists($file_path)) {
            if (!file_exists(dirname($file_path)) && !mkdir(dirname($file_path), FileSystem::DEFAULT_MODE_FOLDER, true)) {
                throw new PrestaShopException($this->trans('Directory "%folder%" cannot be created', ['%folder%' => dirname($file_path)], 'Admin.Notifications.Error'));
            } elseif (!touch($file_path)) {
                throw new PrestaShopException($this->trans('File "%file%" cannot be created', ['%file%' => $file_path], 'Admin.Notifications.Error'));
            }
        }

        $thm_name = str_replace('.', '', Tools::getValue('theme'));
        $kpi_key = substr(strtoupper($thm_name . '_' . Tools::getValue('lang')), 0, 16);

        if ($fd = fopen($file_path, 'wb')) {
            // Get value of button save and stay
            $save_and_stay = Tools::isSubmit('submitTranslations' . $type . 'AndStay');

            // Unset all POST which are not translations
            unset(
                $_POST['submitTranslations' . $type],
                $_POST['submitTranslations' . $type . 'AndStay'],
                $_POST['lang'],
                $_POST['token'],
                $_POST['theme'],
                $_POST['type']
            );

            // Get all POST which aren't empty
            $to_insert = [];
            foreach ($_POST as $key => $value) {
                if (!empty($value)) {
                    $to_insert[$key] = $value;
                }
            }

            ConfigurationKPI::updateValue('FRONTOFFICE_TRANSLATIONS_EXPIRE', time());
            ConfigurationKPI::updateValue('TRANSLATE_TOTAL_' . $kpi_key, count($_POST));
            ConfigurationKPI::updateValue('TRANSLATE_DONE_' . $kpi_key, count($to_insert));

            // translations array is ordered by key (easy merge)
            ksort($to_insert);
            $tab = $translation_informations['var'];
            fwrite($fd, "<?php\n\nglobal \$" . $tab . ";\n\$" . $tab . " = [];\n");
            foreach ($to_insert as $key => $value) {
                fwrite($fd, '$' . $tab . '[\'' . pSQL($key, true) . '\'] = \'' . pSQL($value, true) . '\';' . "\n");
            }
            fwrite($fd, "\n?>");
            fclose($fd);

            // Redirect
            if ($save_and_stay) {
                $this->redirect(true);
            } else {
                $this->redirect();
            }
        } else {
            throw new PrestaShopException($this->trans('Cannot write this file: "%folder%"', ['%folder%' => $file_path], 'Admin.Notifications.Error'));
        }
    }

    public function submitCopyLang()
    {
        $from_lang = Tools::getValue('fromLang');
        $to_lang = Tools::getValue('toLang');
        $from_theme = Tools::getValue('fromTheme');
        $to_theme = Tools::getValue('toTheme');

        if (!$from_lang || !$to_lang) {
            $this->errors[] = $this->trans('You must select two languages in order to copy data from one to another.', [], 'Admin.International.Notification');

            return;
        }
        if (!$from_theme || !$to_theme) {
            $this->errors[] = $this->trans('You must select two themes in order to copy data from one to another.', [], 'Admin.International.Notification');

            return;
        }
        if ($from_lang == $to_lang && $from_theme == $to_theme) {
            $this->errors[] = $this->trans('There is nothing to copy (same language and theme).', [], 'Admin.International.Notification');

            return;
        }

        Language::copyLanguageData(Language::getIdByIso($from_lang), Language::getIdByIso($to_lang));

        $theme_exists = ['from_theme' => false, 'to_theme' => false];
        foreach ($this->themes as $theme) {
            if ($theme->getName() == $from_theme) {
                $theme_exists['from_theme'] = true;
            }
            if ($theme->getName() == $to_theme) {
                $theme_exists['to_theme'] = true;
            }
        }
        if ($theme_exists['from_theme'] == false || $theme_exists['to_theme'] == false) {
            $this->errors[] = $this->trans('Theme(s) not found', [], 'Admin.International.Notification');
        }

        if (count($this->errors)) {
            return;
        }

        $bool = true;
        $items = Language::getFilesList($from_lang, $from_theme, $to_lang, $to_theme, false, false, true);
        foreach ($items as $source => $dest) {
            if (!$this->checkDirAndCreate($dest)) {
                $this->errors[] = $this->trans('Impossible to create the directory "%folder%".', ['%folder%' => $dest], 'Admin.International.Notification');
            } elseif (!copy($source, $dest)) {
                $this->errors[] = $this->trans('Impossible to copy "%source%" to "%dest%".', ['%source%' => $source, '%dest%' => $dest], 'Admin.International.Notification');
            } elseif (strpos($dest, 'modules') && basename($source) === $from_lang . '.php' && $bool !== false) {
                if (!$this->changeModulesKeyTranslation($dest, $from_theme, $to_theme)) {
                    $this->errors[] = $this->trans('Impossible to translate "%dest%".', ['%dest%' => $dest], 'Admin.International.Notification');
                }
            }
        }
        if (!count($this->errors)) {
            $this->redirect(false, 14);
        }
        $this->errors[] = $this->trans('A part of the data has been copied but some of the language files could not be found.', [], 'Admin.International.Notification');
    }

    /**
     * Change the key translation to according it to theme name.
     *
     * @param string $path
     * @param string $theme_from
     * @param string $theme_to
     *
     * @return bool
     */
    public function changeModulesKeyTranslation($path, $theme_from, $theme_to)
    {
        $content = file_get_contents($path);
        $arr_replace = [];
        $bool_flag = true;
        if (preg_match_all('#\$_MODULE\[\'([^\']+)\'\]#Ui', $content, $matches)) {
            foreach ($matches[1] as $value) {
                $arr_replace[$value] = str_replace($theme_from, $theme_to, $value);
            }
            $content = str_replace(array_keys($arr_replace), array_values($arr_replace), $content);
            $bool_flag = (file_put_contents($path, $content) === false) ? false : true;
        }

        return $bool_flag;
    }

    public function exportTabs()
    {
        // Get name tabs by iso code
        $tabs = Tab::getTabs($this->lang_selected->id);

        // Get name of the default tabs
        $tabs_default_lang = Tab::getTabs(1);

        $tabs_default = [];
        foreach ($tabs_default_lang as $tab) {
            $tabs_default[$tab['class_name']] = pSQL($tab['name']);
        }

        // Create content
        $content = "<?php\n\n\$_TABS = array();";
        if (!empty($tabs)) {
            foreach ($tabs as $tab) {
                /*
                 * We don't export tab translations that are identical to the default
                 * tab translations to avoid a problem that would occur in the followin scenario:
                 *
                 * 1) install PrestaShop in, say, Spanish => tabs are by default in Spanish
                 * 2) create a new language, say, Klingon => tabs are populated using the default, Spanish, tabs
                 * 3) export the Klingon language pack
                 *
                 * => Since you have not yet translated the tabs into Klingon,
                 * without the condition below, you would get tabs exported, but in Spanish.
                 * This would lead to a Klingon pack actually containing Spanish.
                 *
                 * This has caused many issues in the past, so, as a precaution, tabs from
                 * the default language are not exported.
                 *
                 */
                if ($tabs_default[$tab['class_name']] != pSQL($tab['name'])) {
                    $content .= "\n\$_TABS['" . $tab['class_name'] . "'] = '" . pSQL($tab['name']) . "';";
                }
            }
        }
        $content .= "\n\nreturn \$_TABS;";

        $dir = _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . DIRECTORY_SEPARATOR;
        $path = $dir . 'tabs.php';

        // Check if tabs.php exists for the selected Iso Code
        if (!Tools::file_exists_cache($dir)) {
            if (!mkdir($dir, FileSystem::DEFAULT_MODE_FOLDER, true)) {
                throw new PrestaShopException('The file ' . $dir . ' cannot be created.');
            }
        }
        if (!file_put_contents($path, $content)) {
            throw new PrestaShopException('File "' . $path . '" does not exist and cannot be created in ' . $dir);
        }
        if (!is_writable($path)) {
            $this->displayWarning($this->trans('This file must be writable: %file%', ['%file%' => $path], 'Admin.Notifications.Error'));
        }
    }

    public static function checkAndAddMailsFiles($iso_code, $files_list)
    {
        if (Language::getIdByIso('en')) {
            $default_language = 'en';
        } else {
            $default_language = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));
        }

        if (!$default_language || !Validate::isLanguageIsoCode($default_language)) {
            return false;
        }

        // 1 - Scan mails files
        $mails = [];
        if (Tools::file_exists_cache(_PS_MAIL_DIR_ . $default_language . '/')) {
            $mails = scandir(_PS_MAIL_DIR_ . $default_language . '/', SCANDIR_SORT_NONE);
        }

        $mails_new_lang = [];

        // Get all email files
        foreach ($files_list as $file) {
            if (preg_match('#^mails\/([a-z0-9]+)\/#Ui', $file['filename'], $matches)) {
                $slash_pos = strrpos($file['filename'], '/');
                $mails_new_lang[] = substr($file['filename'], -(strlen($file['filename']) - $slash_pos - 1));
            }
        }

        // Get the difference
        $arr_mails_needed = array_diff($mails, $mails_new_lang);

        // Add mails files
        foreach ($arr_mails_needed as $mail_to_add) {
            if (!in_array($mail_to_add, self::$ignore_folder)) {
                @copy(_PS_MAIL_DIR_ . $default_language . '/' . $mail_to_add, _PS_MAIL_DIR_ . $iso_code . '/' . $mail_to_add);
            }
        }

        // 2 - Scan modules files
        $modules = scandir(_PS_MODULE_DIR_, SCANDIR_SORT_NONE);

        $module_mail_en = [];
        $module_mail_iso_code = [];

        foreach ($modules as $module) {
            if (!in_array($module, self::$ignore_folder) && Tools::file_exists_cache(_PS_MODULE_DIR_ . $module . '/mails/' . $default_language . '/')) {
                $arr_files = scandir(_PS_MODULE_DIR_ . $module . '/mails/' . $default_language . '/', SCANDIR_SORT_NONE);

                foreach ($arr_files as $file) {
                    if (!in_array($file, self::$ignore_folder)) {
                        if (Tools::file_exists_cache(_PS_MODULE_DIR_ . $module . '/mails/' . $default_language . '/' . $file)) {
                            $module_mail_en[] = _PS_MODULE_DIR_ . $module . '/mails/ISO_CODE/' . $file;
                        }

                        if (Tools::file_exists_cache(_PS_MODULE_DIR_ . $module . '/mails/' . $iso_code . '/' . $file)) {
                            $module_mail_iso_code[] = _PS_MODULE_DIR_ . $module . '/mails/ISO_CODE/' . $file;
                        }
                    }
                }
            }
        }

        // Get the difference in this modules
        $arr_modules_mails_needed = array_diff($module_mail_en, $module_mail_iso_code);

        // Add mails files for this modules
        foreach ($arr_modules_mails_needed as $file) {
            $file_en = str_replace('ISO_CODE', $default_language, $file);
            $file_iso_code = str_replace('ISO_CODE', $iso_code, $file);
            $dir_iso_code = substr($file_iso_code, 0, -(strlen($file_iso_code) - strrpos($file_iso_code, '/') - 1));

            if (!file_exists($dir_iso_code)) {
                mkdir($dir_iso_code);
                file_put_contents($dir_iso_code . '/index.php', Tools::getDefaultIndexContent());
            }

            if (Tools::file_exists_cache($file_en)) {
                copy($file_en, $file_iso_code);
            }
        }
    }

    /**
     * Move theme translations in selected themes.
     *
     * @param array $files
     * @param array $themes_selected
     */
    public function checkAndAddThemesFiles($files, $themes_selected)
    {
        foreach ($files as $file) {
            // Check if file is a file theme
            if (preg_match('#^themes\/([a-z0-9]+)\/lang\/#Ui', $file['filename'], $matches)) {
                $slash_pos = strrpos($file['filename'], '/');
                $name_file = substr($file['filename'], -(strlen($file['filename']) - $slash_pos - 1));
                $name_default_theme = $matches[1];
                $deleted_old_theme = false;

                // Get the old file theme
                if (file_exists(_PS_THEME_DIR_ . 'lang/' . $name_file)) {
                    $theme_file_old = _PS_THEME_DIR_ . 'lang/' . $name_file;
                } else {
                    $deleted_old_theme = true;
                    $theme_file_old = str_replace(self::DEFAULT_THEME_NAME, $name_default_theme, _PS_THEME_DIR_ . 'lang/' . $name_file);
                }

                // Move the old file theme in the new folder
                foreach ($themes_selected as $theme_name) {
                    if (file_exists($theme_file_old)) {
                        copy($theme_file_old, str_replace($name_default_theme, $theme_name, $theme_file_old));
                    }
                }

                if ($deleted_old_theme) {
                    @unlink($theme_file_old);
                }
            }
        }
    }

    /**
     * Add new translations tabs by code ISO.
     *
     * @param string $iso_code
     * @param array $files
     *
     * @return array
     */
    public static function addNewTabs($iso_code, $files)
    {
        $errors = [];

        foreach ($files as $file) {
            // Check if file is a file theme
            if (preg_match('#^translations\/' . $iso_code . '\/tabs.php#Ui', $file['filename'], $matches) && Validate::isLanguageIsoCode($iso_code)) {
                // Include array width new translations tabs
                $_TABS = [];
                clearstatcache();
                if (file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $file['filename'])) {
                    include_once _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $file['filename'];
                }

                /** @var mixed $_TABS */
                if (is_array($_TABS) && count($_TABS)) {
                    foreach ($_TABS as $class_name => $translations) {
                        // Get instance of this tab by class name
                        $tab = Tab::getInstanceFromClassName($class_name);
                        // Check if class name exists
                        if (!empty($tab->class_name)) {
                            $id_lang = Language::getIdByIso($iso_code, true);
                            $tab->name[(int) $id_lang] = $translations;

                            // Do not crash at intall
                            if (!isset($tab->name[Configuration::get('PS_LANG_DEFAULT')])) {
                                $tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $translations;
                            }

                            if (!Validate::isGenericName($tab->name[(int) $id_lang])) {
                                $errors[] = Context::getContext()->getTranslator()->trans('Tab "%s" is not valid', [$tab->name[(int) $id_lang]], 'Admin.International.Notification');
                            } else {
                                $tab->update();
                            }
                        }
                    }
                }
            }
        }

        return $errors;
    }

    public static function checkTranslationFile($content)
    {
        $lines = array_map('trim', explode("\n", $content));
        $global = false;
        foreach ($lines as $line) {
            // PHP tags
            if (in_array($line, ['<?php', '?>', ''])) {
                continue;
            }

            // Global variable declaration
            if (!$global && preg_match('/^global\s+\$([a-z0-9-_]+)\s*;$/i', $line, $matches)) {
                $global = $matches[1];

                continue;
            }
            // Global variable initialization
            if ($global != false && preg_match('/^\$' . preg_quote($global, '/') . '\s*=\s*array\(\s*\)\s*;$/i', $line)) {
                continue;
            }

            // Global variable initialization without declaration
            if (!$global && preg_match('/^\$([a-z0-9-_]+)\s*=\s*array\(\s*\)\s*;$/i', $line, $matches)) {
                $global = $matches[1];

                continue;
            }

            // Assignation
            if (preg_match('/^\$' . preg_quote($global, '/') . '\[\'' . _PS_TRANS_PATTERN_ . '\'\]\s*=\s*\'' . _PS_TRANS_PATTERN_ . '\'\s*;$/i', $line)) {
                continue;
            }

            // Sometimes the global variable is returned...
            if (preg_match('/^return\s+\$' . preg_quote($global, '/') . '\s*;$/i', $line, $matches)) {
                continue;
            }

            return false;
        }

        return true;
    }

    public function submitAddLang()
    {
        $languageDetails = Language::getJsonLanguageDetails(Tools::getValue('params_import_language'));
        $isoCode = $languageDetails['iso_code'];

        if (Validate::isLangIsoCode($isoCode)) {
            $success = Language::downloadAndInstallLanguagePack($isoCode, _PS_VERSION_, null, true);
            if ($success === true) {
                Language::loadLanguages();
                Tools::clearAllCache();

                /* @see AdminController::$_conf */
                $this->redirect(false, 15);
            } else {
                foreach ($success as $error) {
                    $this->errors[] = $error;
                }
            }
        }
    }

    /**
     * This method check each file (tpl or php file), get its sentences to translate,
     * compare with posted values and write in iso code translation file.
     *
     * @param string $file_name
     * @param array $files
     * @param string $theme_name
     * @param string $module_name
     * @param string|bool $dir
     *
     * @throws PrestaShopException
     */
    protected function findAndWriteTranslationsIntoFile(string $file_name, array $files, ?string $theme_name, string $module_name, string|bool $dir = false)
    {
        // These static vars allow to use file to write just one time.
        static $cache_file = [];
        static $str_write = '';
        static $array_check_duplicate = [];

        // Set file_name in static var, this allow to open and wright the file just one time
        if (!isset($cache_file[$theme_name . '-' . $file_name])) {
            $str_write = '';
            $cache_file[$theme_name . '-' . $file_name] = true;
            if (!Tools::file_exists_cache(dirname($file_name))) {
                mkdir(dirname($file_name), FileSystem::DEFAULT_MODE_FOLDER, true);
            }
            if (!Tools::file_exists_cache($file_name)) {
                file_put_contents($file_name, '');
            }
            if (!is_writable($file_name)) {
                throw new PrestaShopException($this->trans('Cannot write to the theme\'s language file (%s). Please check writing permissions.', [$file_name], 'Admin.International.Notification'));
            }

            // this string is initialized one time for a file
            $str_write .= "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n";
            $array_check_duplicate = [];
        }

        $module_name_lowercase = strtolower($module_name);
        $theme_name_lowercase = strtolower((string) $theme_name);

        foreach ($files as $file) {
            if (preg_match('/^(.*)\.(tpl|php)$/', $file) && Tools::file_exists_cache($dir . $file) && !in_array($file, self::$ignore_folder)) {
                // Get content for this file
                $content = file_get_contents($dir . $file);

                // Get file type
                $type_file = substr($file, -4) == '.tpl' ? 'tpl' : 'php';

                // Parse this content
                $matches = $this->userParseFile($content, $this->type_selected, $type_file, $module_name);

                unset($content);

                // Write each translation on its module file
                $template_name = strtolower(substr(basename($file), 0, -4));

                foreach ($matches as $key) {
                    $key_md5 = md5($key);
                    if ($theme_name) {
                        $post_key = md5($module_name_lowercase . '_' . $theme_name_lowercase . '_' . $template_name . '_' . $key_md5);
                        $pattern = '\'<{' . $module_name_lowercase . '}' . $theme_name_lowercase . '>' . $template_name . '_' . $key_md5 . '\'';
                    } else {
                        $post_key = md5($module_name_lowercase . '_' . $template_name . '_' . $key_md5);
                        $pattern = '\'<{' . $module_name_lowercase . '}prestashop>' . $template_name . '_' . $key_md5 . '\'';
                    }

                    if (array_key_exists($post_key, $_POST) && !in_array($pattern, $array_check_duplicate)) {
                        if ($_POST[$post_key] == '') {
                            continue;
                        }
                        $array_check_duplicate[] = $pattern;
                        $str_write .= '$_MODULE[' . $pattern . '] = \'' . pSQL(str_replace(["\r\n", "\r", "\n"], ' ', $_POST[$post_key])) . '\';' . "\n";
                        ++$this->total_expression;
                    }
                }
            }
        }

        if (isset($cache_file[$theme_name . '-' . $file_name]) && $str_write != "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n") {
            file_put_contents($file_name, $str_write);
        }
    }

    /**
     * Clear the list of module file by type (file or directory).
     *
     * @param array $files List of files
     * @param string $type_clear (file|directory)
     * @param string $path
     *
     * @return array : list of a good files
     */
    public function clearModuleFiles($files, $type_clear = 'file', $path = '')
    {
        // List of directory which not must be parsed
        $arr_exclude = ['img', 'js', 'mails', 'override'];

        // List of good extention files
        $arr_good_ext = ['.tpl', '.php'];

        foreach ($files as $key => $file) {
            if ($file[0] === '.' || in_array(substr($file, 0, strrpos($file, '.')), $this->all_iso_lang)) {
                unset($files[$key]);
            } elseif ($type_clear === 'file' && !in_array(substr($file, strrpos($file, '.')), $arr_good_ext)) {
                unset($files[$key]);
            } elseif ($type_clear === 'directory' && (!is_dir($path . $file) || in_array($file, $arr_exclude))) {
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * This method get translation for each files of a module,
     * compare with global $_MODULES array and fill AdminTranslations::modules_translations array
     * With key as English sentences and values as their iso code translations.
     *
     * @param array $files
     * @param string $theme_name
     * @param string $module_name
     * @param string|bool $dir
     */
    protected function findAndFillTranslations(array $files, ?string $theme_name, string $module_name, string|bool $dir = false)
    {
        $name_var = (empty($this->translations_informations[$this->type_selected]['var']) ? false : $this->translations_informations[$this->type_selected]['var']);

        // added for compatibility
        $GLOBALS[$name_var] = array_change_key_case($GLOBALS[$name_var]);

        // Thank to this var similar keys are not duplicate
        // in AndminTranslation::modules_translations array
        // see below
        $array_check_duplicate = [];
        foreach ($files as $file) {
            if ((preg_match('/^(.*).tpl$/', $file) || preg_match('/^(.*).php$/', $file)) && Tools::file_exists_cache($file_path = $dir . $file)) {
                // Get content for this file
                $content = file_get_contents($file_path);

                // Module files can now be ignored by adding this string in a file
                if (strpos($content, 'IGNORE_THIS_FILE_FOR_TRANSLATION') !== false) {
                    continue;
                }

                // Get file type
                $type_file = substr($file, -4) == '.tpl' ? 'tpl' : 'php';

                // Parse this content
                $matches = $this->userParseFile($content, $this->type_selected, $type_file, $module_name);

                unset($content);

                // Write each translation on its module file
                $template_name = substr(basename($file), 0, -4);

                foreach ($matches as $key) {
                    $md5_key = md5($key);
                    $module_key = '<{' . Tools::strtolower($module_name) . '}' . strtolower((string) $theme_name) . '>' . Tools::strtolower($template_name) . '_' . $md5_key;
                    $default_key = '<{' . Tools::strtolower($module_name) . '}prestashop>' . Tools::strtolower($template_name) . '_' . $md5_key;
                    // to avoid duplicate entry
                    if (!in_array($module_key, $array_check_duplicate)) {
                        $array_check_duplicate[] = $module_key;
                        if (!isset($this->modules_translations[$theme_name][$module_name][$template_name][$key]['trad'])) {
                            ++$this->total_expression;
                        }
                        if ($theme_name && array_key_exists($module_key, $GLOBALS[$name_var])) {
                            $this->modules_translations[$theme_name][$module_name][$template_name][$key]['trad'] = html_entity_decode($GLOBALS[$name_var][$module_key], ENT_COMPAT, 'UTF-8');
                        } elseif (array_key_exists($default_key, $GLOBALS[$name_var])) {
                            $this->modules_translations[$theme_name][$module_name][$template_name][$key]['trad'] = html_entity_decode($GLOBALS[$name_var][$default_key], ENT_COMPAT, 'UTF-8');
                        } else {
                            $this->modules_translations[$theme_name][$module_name][$template_name][$key]['trad'] = '';
                            ++$this->missing_translations;
                        }
                        $this->modules_translations[$theme_name][$module_name][$template_name][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                    }
                }
            }
        }
    }

    /**
     * Get list of files which must be parsed by directory and by type of translations.
     *
     * @return array : list of files by directory
     */
    public function getFileToParseByTypeTranslation()
    {
        $directories = [];

        switch ($this->type_selected) {
            case 'front':
                $directories['php'] = [
                    _PS_FRONT_CONTROLLER_DIR_ => scandir(_PS_FRONT_CONTROLLER_DIR_, SCANDIR_SORT_NONE),
                    _PS_OVERRIDE_DIR_ . 'controllers/front/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/front/', SCANDIR_SORT_NONE),
                    _PS_CLASS_DIR_ . 'controller/' => ['FrontController.php'],
                ];

                $directories['tpl'] = [_PS_ALL_THEMES_DIR_ => scandir(_PS_ALL_THEMES_DIR_, SCANDIR_SORT_NONE)];
                self::$ignore_folder[] = 'modules';
                $directories['tpl'] = array_merge($directories['tpl'], $this->listFiles(_PS_THEME_SELECTED_DIR_));
                if (isset($directories['tpl'][_PS_THEME_SELECTED_DIR_ . 'pdf/'])) {
                    unset($directories['tpl'][_PS_THEME_SELECTED_DIR_ . 'pdf/']);
                }

                break;

            case 'back':
                $directories = [
                    'php' => [
                        _PS_ADMIN_CONTROLLER_DIR_ => scandir(_PS_ADMIN_CONTROLLER_DIR_, SCANDIR_SORT_NONE),
                        _PS_OVERRIDE_DIR_ . 'controllers/admin/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/admin/', SCANDIR_SORT_NONE),
                        _PS_CLASS_DIR_ . 'helper/' => scandir(_PS_CLASS_DIR_ . 'helper/', SCANDIR_SORT_NONE),
                        _PS_CLASS_DIR_ . 'controller/' => ['AdminController.php'],
                        _PS_CLASS_DIR_ => ['PaymentModule.php'],
                    ],
                    'php-sf2' => [
                        _PS_ROOT_DIR_ . '/src/' => Tools::scandir(_PS_ROOT_DIR_ . '/src/', 'php', '', true),
                    ],
                    'tpl-sf2' => Tools::scandir(_PS_ROOT_DIR_ . '/src/PrestaShopBundle/Resources/views/', 'twig', '', true),
                    'tpl' => $this->listFiles(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'themes/'),
                    'specific' => [
                        _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR => [],
                    ],
                ];

                // For translate the template which are overridden
                if (file_exists(_PS_OVERRIDE_DIR_ . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates')) {
                    $directories['tpl'] = array_merge($directories['tpl'], $this->listFiles(_PS_OVERRIDE_DIR_ . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates'));
                }

                break;

            case 'errors':
                $directories['php'] = [
                    _PS_ROOT_DIR_ => scandir(_PS_ROOT_DIR_, SCANDIR_SORT_NONE),
                    _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR => scandir(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR, SCANDIR_SORT_NONE),
                    _PS_FRONT_CONTROLLER_DIR_ => scandir(_PS_FRONT_CONTROLLER_DIR_, SCANDIR_SORT_NONE),
                    _PS_ADMIN_CONTROLLER_DIR_ => scandir(_PS_ADMIN_CONTROLLER_DIR_, SCANDIR_SORT_NONE),
                    _PS_OVERRIDE_DIR_ . 'controllers/front/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/front/', SCANDIR_SORT_NONE),
                    _PS_OVERRIDE_DIR_ . 'controllers/admin/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/admin/', SCANDIR_SORT_NONE),
                ];

                // Get all files for folders classes/ and override/classes/ recursively
                $directories['php'] = array_merge($directories['php'], $this->listFiles(_PS_CLASS_DIR_, [], 'php'));
                $directories['php'] = array_merge($directories['php'], $this->listFiles(_PS_OVERRIDE_DIR_ . 'classes/', [], 'php'));

                break;

            case 'pdf':
                $tpl_theme = Tools::file_exists_cache(_PS_THEME_SELECTED_DIR_ . 'pdf/') ? scandir(_PS_THEME_SELECTED_DIR_ . 'pdf/', SCANDIR_SORT_NONE) : [];
                $directories = [
                    'php' => [
                        _PS_CLASS_DIR_ . 'pdf/' => scandir(_PS_CLASS_DIR_ . 'pdf/', SCANDIR_SORT_NONE),
                        _PS_OVERRIDE_DIR_ . 'classes/pdf/' => scandir(_PS_OVERRIDE_DIR_ . 'classes/pdf/', SCANDIR_SORT_NONE),
                    ],
                    'tpl' => [
                        _PS_PDF_DIR_ => scandir(_PS_PDF_DIR_, SCANDIR_SORT_NONE),
                        _PS_THEME_SELECTED_DIR_ . 'pdf/' => $tpl_theme,
                    ],
                ];
                $directories['tpl'] = array_merge($directories['tpl'], $this->getModulesHasPDF());
                $directories['php'] = array_merge($directories['php'], $this->getModulesHasPDF(true));

                break;

            case 'mails':
                $directories['php'] = [
                    _PS_FRONT_CONTROLLER_DIR_ => scandir(_PS_FRONT_CONTROLLER_DIR_, SCANDIR_SORT_NONE),
                    _PS_ADMIN_CONTROLLER_DIR_ => scandir(_PS_ADMIN_CONTROLLER_DIR_, SCANDIR_SORT_NONE),
                    _PS_OVERRIDE_DIR_ . 'controllers/front/' => is_dir(_PS_OVERRIDE_DIR_ . 'controllers/front/') ? scandir(_PS_OVERRIDE_DIR_ . 'controllers/front/', SCANDIR_SORT_NONE) : [],
                    _PS_OVERRIDE_DIR_ . 'controllers/admin/' => is_dir(_PS_OVERRIDE_DIR_ . 'controllers/admin/') ? scandir(_PS_OVERRIDE_DIR_ . 'controllers/admin/', SCANDIR_SORT_NONE) : [],
                    _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR => scandir(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR, SCANDIR_SORT_NONE),
                ];

                // Get all files for folders classes/ and override/classes/ recursively
                $directories['php'] = array_merge($directories['php'], $this->listFiles(_PS_CLASS_DIR_, [], 'php'));
                $directories['php'] = array_merge($directories['php'], $this->listFiles(_PS_OVERRIDE_DIR_ . 'classes/', [], 'php'));
                $directories['php'] = array_merge($directories['php'], $this->getModulesHasMails());

                break;
        }

        return $directories;
    }

    /**
     * This method parse a file by type of translation and type file.
     *
     * @param string $content
     * @param string $type_translation front, back, errors, modules...
     * @param string|bool $type_file (tpl|php)
     * @param string $module_name name of the module
     *
     * @return array
     */
    protected function userParseFile(string $content, string $type_translation, string|bool $type_file = false, string $module_name = '')
    {
        switch ($type_translation) {
            case 'front':
                // Parsing file in Front office
                if ($type_file == 'php') {
                    $regex = '/this->l\((\')' . _PS_TRANS_PATTERN_ . '\'[\)|\,]/U';
                } else {
                    $regex = '/\{l\s*s=([\'\"])' . _PS_TRANS_PATTERN_ . '\1(\s*sprintf=.*)?(\s*js=1)?\s*\}/U';
                }

                break;

            case 'back':
                // Parsing file in Back office
                if ($type_file == 'php') {
                    $regex = '/this->l\((\')' . _PS_TRANS_PATTERN_ . '\'[\)|\,]/U';
                } elseif ($type_file == 'specific') {
                    $regex = '/Context::getContext\(\)->getTranslator\(\)->trans\((\')' . _PS_TRANS_PATTERN_ . '\'(?:,.*)*\)/U';
                } else {
                    $regex = '/\{l\s*s\s*=([\'\"])' . _PS_TRANS_PATTERN_ . '\1(\s*sprintf=.*)?(\s*js=1)?(\s*slashes=1)?.*\}/U';
                }

                break;

            case 'errors':
                // Parsing file for all errors syntax
                $regex = '/Tools::displayError\((\')' . _PS_TRANS_PATTERN_ . '\'(,\s*(.+))?\)/U';

                break;

            case 'modules':
                // Parsing modules file
                if ($type_file == 'php') {
                    $regex = '/->l\(\s*(\')' . _PS_TRANS_PATTERN_ . '\'(\s*,\s*?\'(.+)\')?(\s*,\s*?(.+))?\s*\)/Ums';
                } else {
                    // In tpl file look for something that should contain mod='module_name' according to the documentation
                    $regex = '/\{l\s*s=([\'\"])' . _PS_TRANS_PATTERN_ . '\1.*\s+mod=\'' . $module_name . '\'.*\}/U';
                }

                break;

            case 'pdf':
                // Parsing PDF file
                if ($type_file == 'php') {
                    $regex = [
                        '/HTMLTemplate.*::l\((\')' . _PS_TRANS_PATTERN_ . '\'[\)|\,]/U',
                        '/->l\((\')' . _PS_TRANS_PATTERN_ . '\'(, ?\'(.+)\')?(, ?(.+))?\)/U',
                    ];
                } else {
                    $regex = '/\{l\s*s=([\'\"])' . _PS_TRANS_PATTERN_ . '\1(\s*sprintf=.*)?(\s*js=1)?(\s*pdf=\'true\')?\s*\}/U';
                }

                break;
            default:
                $regex = [];
        }

        if (!is_array($regex)) {
            $regex = [$regex];
        }

        $strings = [];
        foreach ($regex as $regex_row) {
            $matches = [];
            $n = preg_match_all($regex_row, $content, $matches);
            for ($i = 0; $i < $n; ++$i) {
                $quote = $matches[1][$i];
                $string = $matches[2][$i];

                if ($quote === '"') {
                    // Escape single quotes because the core will do it when looking for the translation of this string
                    $string = str_replace('\'', '\\\'', $string);
                    // Unescape double quotes
                    $string = preg_replace('/\\\\+"/', '"', $string);
                }

                $strings[] = $string;
            }
        }

        return array_unique($strings);
    }

    /**
     * Get all translations informations for all type of translations.
     *
     * array(
     *  'type' => array(
     *      'name' => string : title for the translation type,
     *      'var' => string : name of var for the translation file,
     *      'dir' => string : dir of translation file
     *      'file' => string : file name of translation file
     *  )
     * )
     */
    public function getTranslationsInformations()
    {
        $this->translations_informations = [
            'back' => [
                'name' => $this->trans('Back office translations', [], 'Admin.International.Feature'),
                'var' => '_LANGADM',
                'dir' => _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . '/',
                'file' => 'admin.php',
                'sf_controller' => true,
                'choice_theme' => false,
            ],
            'themes' => [
                'name' => $this->trans('Themes translations', [], 'Admin.International.Feature'),
                'var' => '_THEMES',
                'dir' => '',
                'file' => '',
                'sf_controller' => true,
                'choice_theme' => true,
            ],
            'modules' => [
                'name' => $this->trans('Installed modules translations', [], 'Admin.International.Feature'),
                'var' => '_MODULES',
                'dir' => _PS_ROOT_DIR_ . '/modules/',
                'file' => '',
                'sf_controller' => true,
                'choice_theme' => false,
            ],
            'mails' => [
                'name' => $this->trans('Email translations', [], 'Admin.International.Feature'),
                'var' => '_LANGMAIL',
                'dir' => _PS_MAIL_DIR_ . $this->lang_selected->iso_code . '/',
                'file' => 'lang.php',
                'sf_controller' => false,
                'choice_theme' => false,
            ],
            'others' => [
                'name' => $this->trans('Other translations', [], 'Admin.International.Feature'),
                'var' => '_OTHERS',
                'dir' => '',
                'file' => '',
                'sf_controller' => true,
                'choice_theme' => false,
            ],
        ];

        if (defined('_PS_THEME_SELECTED_DIR_')) {
            $this->translations_informations['modules']['override'] = ['dir' => _PS_THEME_SELECTED_DIR_ . 'modules/', 'file' => ''];
            $this->translations_informations['mails']['override'] = ['dir' => _PS_THEME_SELECTED_DIR_ . 'mails/' . $this->lang_selected->iso_code . '/', 'file' => 'lang.php'];
        }
    }

    /**
     * Get all informations on : languages, theme and the translation type.
     */
    public function getInformations()
    {
        // Get all Languages
        $this->languages = Language::getLanguages(false);

        // Get all iso_code of languages
        foreach ($this->languages as $language) {
            $this->all_iso_lang[] = $language['iso_code'];
        }

        // Get folder name of theme
        if (($theme = Tools::getValue('selected-theme')) && !is_array($theme)) {
            $theme_exists = $this->theme_exists($theme);
            if (!$theme_exists) {
                throw new PrestaShopException($this->trans('Invalid theme "%theme%"', ['%theme%' => Tools::safeOutput($theme)], 'Admin.International.Notification'));
            }
            $this->theme_selected = Tools::safeOutput($theme);
        }

        // Set the path of selected theme
        if ($this->theme_selected) {
            define('_PS_THEME_SELECTED_DIR_', _PS_ROOT_DIR_ . '/themes/' . $this->theme_selected . '/');
        } else {
            define('_PS_THEME_SELECTED_DIR_', '');
        }

        // Get type of translation
        if (($type = Tools::getValue('type')) && !is_array($type)) {
            $this->type_selected = strtolower(Tools::safeOutput($type));
        }

        // Get selected language
        if (Tools::getValue('lang') || Tools::getValue('iso_code')) {
            $iso_code = Tools::getValue('lang') ? Tools::getValue('lang') : Tools::getValue('iso_code');

            if (!Validate::isLangIsoCode($iso_code) || !in_array($iso_code, $this->all_iso_lang)) {
                throw new PrestaShopException($this->trans('Invalid iso code "%iso_code%"', ['%iso_code%' => Tools::safeOutput($iso_code)], 'Admin.International.Notification'));
            }

            $this->lang_selected = new Language((int) Language::getIdByIso($iso_code));
        } else {
            $this->lang_selected = new Language((int) Language::getIdByIso('en'));
        }

        // Get all information for translations
        $this->getTranslationsInformations();
    }

    public function renderKpis()
    {
        $time = time();
        $kpis = [];

        /* The data generation is located in AdminStatsControllerCore */

        $helper = new HelperKpi();
        $helper->id = 'box-languages';
        $helper->icon = 'icon-microphone';
        $helper->color = 'color1';
        $helper->href = $this->context->link->getAdminLink('AdminLanguages');
        $helper->title = $this->trans('Enabled Languages', [], 'Admin.International.Feature');
        if (ConfigurationKPI::get('ENABLED_LANGUAGES') !== false) {
            $helper->value = ConfigurationKPI::get('ENABLED_LANGUAGES');
        }
        $helper->source = $this->context->link->getAdminLink('AdminStats') . '&ajax=1&action=getKpi&kpi=enabled_languages';
        $helper->refresh = (bool) (ConfigurationKPI::get('ENABLED_LANGUAGES_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-country';
        $helper->icon = 'icon-home';
        $helper->color = 'color2';
        $helper->title = $this->trans('Main Country', [], 'Admin.International.Feature');
        $helper->subtitle = $this->trans('30 Days', [], 'Admin.Global');
        if (ConfigurationKPI::get('MAIN_COUNTRY', $this->context->language->id) !== false) {
            $helper->value = ConfigurationKPI::get('MAIN_COUNTRY', $this->context->language->id);
        }
        $helper->source = $this->context->link->getAdminLink('AdminStats') . '&ajax=1&action=getKpi&kpi=main_country';
        $helper->refresh = (bool) (ConfigurationKPI::get('MAIN_COUNTRY_EXPIRE', $this->context->language->id) < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-translations';
        $helper->icon = 'icon-list';
        $helper->color = 'color3';
        $helper->title = $this->trans('Front office Translations', [], 'Admin.International.Feature');
        if (ConfigurationKPI::get('FRONTOFFICE_TRANSLATIONS') !== false) {
            $helper->value = ConfigurationKPI::get('FRONTOFFICE_TRANSLATIONS');
        }
        $helper->source = $this->context->link->getAdminLink('AdminStats') . '&ajax=1&action=getKpi&kpi=frontoffice_translations';
        $helper->refresh = (bool) (ConfigurationKPI::get('FRONTOFFICE_TRANSLATIONS_EXPIRE') < $time);
        $kpis[] = $helper->generate();

        $helper = new HelperKpiRow();
        $helper->kpis = $kpis;

        return $helper->generate();
    }

    /**
     * AdminController::postProcess() override.
     *
     * @see AdminController::postProcess()
     */
    public function postProcess()
    {
        $this->getInformations();

        /* PrestaShop demo mode */
        if (_PS_MODE_DEMO_) {
            $this->errors[] = $this->trans('This functionality has been disabled.', [], 'Admin.Notifications.Error');

            return;
        }
        /* PrestaShop demo mode */

        try {
            if (Tools::isSubmit('submitCopyLang')) {
                if ($this->access('add')) {
                    $this->submitCopyLang();
                } else {
                    $this->errors[] = $this->trans('You do not have permission to add this.', [], 'Admin.Notifications.Error');
                }
            } elseif (Tools::isSubmit('submitAddLanguage')) {
                if ($this->access('add')) {
                    $this->submitAddLang();
                } else {
                    $this->errors[] = $this->trans('You do not have permission to add this.', [], 'Admin.Notifications.Error');
                }
            } elseif (Tools::isSubmit('submitTranslationsPdf')) {
                if ($this->access('edit')) {
                    // Only the PrestaShop team should write the translations into the _PS_TRANSLATIONS_DIR_
                    if (!$this->theme_selected) {
                        $this->writeTranslationFile();
                    } else {
                        $this->writeTranslationFile(true);
                    }
                } else {
                    $this->errors[] = $this->trans('You do not have permission to edit this.', [], 'Admin.Notifications.Error');
                }
            } elseif (Tools::isSubmit('submitTranslationsBack') || Tools::isSubmit('submitTranslationsErrors') || Tools::isSubmit('submitTranslationsFields') || Tools::isSubmit('submitTranslationsFront')) {
                if ($this->access('edit')) {
                    $this->writeTranslationFile();
                } else {
                    $this->errors[] = $this->trans('You do not have permission to edit this.', [], 'Admin.Notifications.Error');
                }
            } elseif (Tools::isSubmit('submitTranslationsMails') || Tools::isSubmit('submitTranslationsMailsAndStay')) {
                if ($this->access('edit')) {
                    $this->submitTranslationsMails();
                } else {
                    $this->errors[] = $this->trans('You do not have permission to edit this.', [], 'Admin.Notifications.Error');
                }
            } elseif (Tools::isSubmit('submitTranslationsModules')) {
                if ($this->access('edit')) {
                    // Get list of modules
                    if ($modules = $this->getListModules()) {
                        // Get files of all modules
                        $arr_files = $this->getAllModuleFiles($modules, null, $this->lang_selected->iso_code, true);

                        // Find and write all translation modules files
                        foreach ($arr_files as $value) {
                            $this->findAndWriteTranslationsIntoFile($value['file_name'], $value['files'], $value['theme'], $value['module'], $value['dir']);
                        }

                        // Clear modules cache
                        Tools::clearAllCache();

                        // Redirect
                        if (Tools::getIsset('submitTranslationsModulesAndStay')) {
                            $this->redirect(true);
                        } else {
                            $this->redirect();
                        }
                    }
                } else {
                    $this->errors[] = $this->trans('You do not have permission to edit this.', [], 'Admin.Notifications.Error');
                }
            } elseif (Tools::isSubmit('submitSelectModules')) {
                $this->redirect(false, false, true);
            }
        } catch (PrestaShopException $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * This method redirect in the translation main page or in the translation page.
     *
     * @param bool $save_and_stay : true if the user has clicked on the button "save and stay"
     * @param bool|int $conf : id of confirmation message
     * @param bool $modify_translation : true if the user has clicked on the button "Modify translation"
     */
    protected function redirect(bool $save_and_stay = false, bool|int $conf = false, bool $modify_translation = false)
    {
        $conf = !$conf ? 4 : $conf;
        $url_base = self::$currentIndex . '&token=' . $this->token . '&conf=' . $conf;
        if ($modify_translation) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&lang=' . Tools::getValue('langue') . '&type=' . $this->type_selected . '&module=' . Tools::getValue('module') . '&selected-theme=' . $this->theme_selected);
        } elseif ($save_and_stay) {
            Tools::redirectAdmin($url_base . '&lang=' . $this->lang_selected->iso_code . '&type=' . $this->type_selected . '&module=' . Tools::getValue('module') . '&selected-theme=' . $this->theme_selected);
        } else {
            Tools::redirectAdmin($url_base . '&action=settings');
        }
    }

    protected function getMailPattern()
    {
        Tools::displayAsDeprecated('Email pattern is no longer used, emails are always saved like they are.');

        // Let the indentation like it.
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
    #content
</body>
</html>';
    }

    /**
     * This method is used to write translation for mails.
     * This writes subject translation files
     * (in root/mails/lang_choosen/lang.php or root/_PS_THEMES_DIR_/mails/lang_choosen/lang.php)
     * and mails files.
     */
    protected function submitTranslationsMails()
    {
        $arr_mail_content = [];
        $arr_mail_path = [];

        if (Tools::getValue('core_mail')) {
            $arr_mail_content['core_mail'] = Tools::getValue('core_mail');

            // Get path of directory for find a good path of translation file
            if (!$this->theme_selected) {
                $arr_mail_path['core_mail'] = $this->translations_informations[$this->type_selected]['dir'];
            } else {
                $arr_mail_path['core_mail'] = $this->translations_informations[$this->type_selected]['override']['dir'];
            }
        }

        if (Tools::getValue('module_mail')) {
            $arr_mail_content['module_mail'] = Tools::getValue('module_mail');

            // Get path of directory for find a good path of translation file
            if (!$this->theme_selected) {
                $arr_mail_path['module_mail'] = $this->translations_informations['modules']['dir'] . '{module}/mails/' . $this->lang_selected->iso_code . '/';
            } else {
                $arr_mail_path['module_mail'] = $this->translations_informations['modules']['override']['dir'] . '{module}/mails/' . $this->lang_selected->iso_code . '/';
            }
        }

        // Save each mail content
        foreach ($arr_mail_content as $group_name => $all_content) {
            foreach ($all_content as $type_content => $mails) {
                if (!in_array($type_content, self::$content_type_accepted)) {
                    throw new PrestaShopException($this->trans('This %type_content% file extension is not accepted.', ['%type_content%' => $type_content], 'Admin.International.Notification'));
                }

                foreach ($mails as $mail_name => $content) {
                    $module_name = false;
                    $module_name_pipe_pos = stripos($mail_name, '|');
                    if ($module_name_pipe_pos) {
                        $module_name = substr($mail_name, 0, $module_name_pipe_pos);
                        if (!Validate::isModuleName($module_name)) {
                            throw new PrestaShopException($this->trans('Invalid module name "%module%"', ['%module%' => Tools::safeOutput($module_name)], 'Admin.International.Notification'));
                        }
                        $mail_name = substr($mail_name, $module_name_pipe_pos + 1);
                        if (!Validate::isTplName($mail_name)) {
                            throw new PrestaShopException($this->trans('Invalid mail name "%mail%"', ['%mail%' => Tools::safeOutput($mail_name)], 'Admin.International.Notification'));
                        }
                    }

                    if ($type_content == 'html') {
                        $content = Tools::htmlentitiesUTF8($content);
                        $content = htmlspecialchars_decode($content);
                        // replace correct end of line
                        $content = str_replace("\r\n", PHP_EOL, $content);
                    }

                    if (Validate::isCleanHTML($content)) {
                        $path = $arr_mail_path[$group_name] ?? '';
                        if ($module_name) {
                            $path = str_replace('{module}', $module_name, $path);
                        }
                        if (!file_exists($path) && !mkdir($path, FileSystem::DEFAULT_MODE_FOLDER, true)) {
                            throw new PrestaShopException($this->trans('Directory "%folder%" cannot be created', ['%folder%' => dirname($path)], 'Admin.International.Notification'));
                        }

                        if ($type_content == 'tpl') {
                            preg_match('/{\s*[^$]+/s', $content, $matches);
                            if (!empty($matches)) {
                                throw new PrestaShopException($this->trans('Your email translations contain some invalid HTML and cannot be saved. Please check your content.', [], 'Admin.International.Notification'));
                            }
                        }

                        file_put_contents($path . $mail_name . '.' . $type_content, $content);
                    } else {
                        throw new PrestaShopException($this->trans('Your HTML email templates cannot contain JavaScript code.', [], 'Admin.International.Notification'));
                    }
                }
            }
        }

        // Update subjects
        $array_subjects = [];
        if (($subjects = Tools::getValue('subject')) && is_array($subjects)) {
            $array_subjects['core_and_modules'] = ['translations' => [], 'path' => ($arr_mail_path['core_mail'] ?? '') . 'lang.php'];
            foreach ($subjects as $subject_translation) {
                $array_subjects['core_and_modules']['translations'] = array_merge($array_subjects['core_and_modules']['translations'], $subject_translation);
            }
        }
        if (!empty($array_subjects)) {
            foreach ($array_subjects as $infos) {
                $this->writeSubjectTranslationFile($infos['translations'], $infos['path']);
            }
        }

        if (Tools::isSubmit('submitTranslationsMailsAndStay')) {
            $this->redirect(true);
        } else {
            $this->redirect();
        }
    }

    /**
     * Include file $dir/$file and return the var $var declared in it.
     * This create the file if not exists.
     *
     * return array : translations
     */
    public function fileExists()
    {
        $var = $this->translations_informations[$this->type_selected]['var'];
        $dir = $this->translations_informations[$this->type_selected]['dir'];
        $file = $this->translations_informations[$this->type_selected]['file'];

        $$var = [];
        if (!Tools::file_exists_cache($dir)) {
            if (!mkdir($dir, 0700)) {
                throw new PrestaShopException('Directory ' . $dir . ' cannot be created.');
            }
        }
        if (!Tools::file_exists_cache($dir . DIRECTORY_SEPARATOR . $file)) {
            if (!file_put_contents($dir . '/' . $file, "<?php\n\nglobal \$" . $var . ";\n\$" . $var . " = array();\n\n?>")) {
                throw new PrestaShopException('File "' . $file . '" doesn\'t exists and cannot be created in ' . $dir);
            }
        }
        if (!is_writable($dir . DIRECTORY_SEPARATOR . $file)) {
            $this->displayWarning($this->trans('This file must be writable:', [], 'Admin.Notifications.Error') . ' ' . $dir . '/' . $file);
        }
        include $dir . DIRECTORY_SEPARATOR . $file;

        return $$var;
    }

    public function displayToggleButton($closed = false)
    {
        $str_output = '
        <script type="text/javascript">';
        if (Tools::getValue('type') == 'mails') {
            $str_output .= '$(function(){
                toggleDiv(\'' . $this->type_selected . '_div\'); toggleButtonValue(this.id, openAll, closeAll);
                });';
        }
        $str_output .= '
            var openAll = \'' . html_entity_decode($this->trans('Expand all fieldsets', [], 'Admin.International.Feature'), ENT_NOQUOTES, 'UTF-8') . '\';
            var closeAll = \'' . html_entity_decode($this->trans('Close all fieldsets', [], 'Admin.International.Feature'), ENT_NOQUOTES, 'UTF-8') . '\';
        </script>
        <button type="button" class="btn btn-default" id="buttonall" data-status="open" onclick="toggleDiv(\'' . $this->type_selected . '_div\', $(this).data(\'status\')); toggleButtonValue(this.id, openAll, closeAll);"><i class="process-icon-compress"></i> <span>' . $this->trans('Close all fieldsets', [], 'Admin.International.Feature') . '</span></button>';

        return $str_output;
    }

    public function displayLimitPostWarning($count)
    {
        $return = [];
        if ((ini_get('suhosin.post.max_vars') && ini_get('suhosin.post.max_vars') < $count) || (ini_get('suhosin.request.max_vars') && ini_get('suhosin.request.max_vars') < $count)) {
            $return['error_type'] = 'suhosin';
            $return['post.max_vars'] = ini_get('suhosin.post.max_vars');
            $return['request.max_vars'] = ini_get('suhosin.request.max_vars');
            $return['needed_limit'] = $count + 100;
        } elseif (ini_get('max_input_vars') && ini_get('max_input_vars') < $count) {
            $return['error_type'] = 'conf';
            $return['max_input_vars'] = ini_get('max_input_vars');
            $return['needed_limit'] = $count + 100;
        }

        return $return;
    }

    /**
     * Find sentence which use %d, %s, %%, %1$d, %1$s...
     *
     * @param string $key English sentence
     *
     * @return string|bool List of matches
     */
    public function checkIfKeyUseSprintf($key)
    {
        if (preg_match_all('#(?:%%|%(?:[0-9]+\$)?[+-]?(?:[ 0]|\'.)?-?[0-9]*(?:\.[0-9]+)?[bcdeufFosxX])#', $key, $matches)) {
            return implode(', ', $matches[0]);
        }

        return false;
    }

    /**
     * This method generate the form for front translations.
     */
    public function initFormFront()
    {
        if (!$this->theme_exists(Tools::getValue('theme'))) {
            $this->errors[] = $this->trans('Invalid theme "%theme%"', ['%theme%' => Tools::getValue('theme')], 'Admin.International.Notification');

            return;
        }

        $missing_translations_front = [];
        $name_var = $this->translations_informations[$this->type_selected]['var'];
        $GLOBALS[$name_var] = $this->fileExists();

        /* List templates to parse */
        $files_by_directory = $this->getFileToParseByTypeTranslation();
        $count = 0;
        $tabs_array = [];
        foreach ($files_by_directory as $file_type => $root_directory) {
            foreach ($root_directory as $dir => $files) {
                $prefix = '';

                foreach ($files as $file) {
                    if (preg_match('/^(.*).(tpl|php)$/', $file) && Tools::file_exists_cache($file_path = $dir . $file)) {
                        $prefix_key = $prefix . substr(basename($file), 0, -4);
                        $new_lang = [];

                        // Get content for this file
                        $content = file_get_contents($file_path);

                        // Parse this content
                        $matches = $this->userParseFile($content, $this->type_selected, $file_type);

                        /* Get string translation */
                        foreach ($matches as $key) {
                            if (empty($key)) {
                                $this->errors[] = $this->trans('Empty string found, please edit: "%file%"', ['%file%' => $file_path], 'Admin.International.Notification');
                                $new_lang[$key] = '';
                            } else {
                                // Caution ! front has underscore between prefix key and md5, back has not
                                if (isset($GLOBALS[$name_var][$prefix_key . '_' . md5($key)])) {
                                    $new_lang[$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$name_var][$prefix_key . '_' . md5($key)], ENT_COMPAT, 'UTF-8'));
                                } else {
                                    if (!isset($new_lang[$key]['trad'])) {
                                        $new_lang[$key]['trad'] = '';
                                        if (!isset($missing_translations_front[$prefix_key])) {
                                            $missing_translations_front[$prefix_key] = 1;
                                        } else {
                                            ++$missing_translations_front[$prefix_key];
                                        }
                                    }
                                }
                                $new_lang[$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                            }
                        }

                        if (isset($tabs_array[$prefix_key])) {
                            $tabs_array[$prefix_key] = array_merge($tabs_array[$prefix_key], $new_lang);
                        } else {
                            $tabs_array[$prefix_key] = $new_lang;
                        }

                        $count += count($new_lang);
                    }
                }
            }
        }

        $this->tpl_view_vars = array_merge($this->tpl_view_vars, [
            'missing_translations' => $missing_translations_front,
            'count' => $count,
            'cancel_url' => $this->context->link->getAdminLink('AdminTranslations'),
            'limit_warning' => $this->displayLimitPostWarning($count),
            'mod_security_warning' => Tools::apacheModExists('mod_security'),
            'tabsArray' => $tabs_array,
        ]);

        $this->initToolbar();
        $this->base_tpl_view = 'translation_form.tpl';

        return parent::renderView();
    }

    /**
     * This method generate the form for back translations.
     */
    public function initFormBack()
    {
        $name_var = $this->translations_informations[$this->type_selected]['var'];
        $GLOBALS[$name_var] = $this->fileExists();
        $missing_translations_back = [];

        // Get all types of file (PHP, TPL...) and a list of files to parse by folder
        $files_per_directory = $this->getFileToParseByTypeTranslation();

        // Parse SF2 php files
        $regexSf2Php = [
            '/->trans\(([\'\"])' . _PS_TRANS_PATTERN_ . '([\'\"])(,\s*?[\[|array\(](.*)[\]|\)])(,\s*?([\'\"])(.*)([\'\"]))?\)/Us',
            '/->transchoice\(([\'\"])' . _PS_TRANS_PATTERN_ . '([\'\"])(,\s*?(.*))(,\s*?[\[|array\(](.*)[\]|\)])(,\s*?([\'\"])(.*)([\'\"]))?\)/Us',
        ];

        $tabs_array = [];
        foreach ($files_per_directory['php-sf2'] as $dir => $files) {
            foreach ($files as $file) {
                // Get content for this file
                $content = file_get_contents($dir . $file);
                if (!$content) {
                    continue;
                }

                // Parse this content
                foreach ($regexSf2Php as $reg) {
                    preg_match_all($reg, $content, $matches);
                    foreach ($matches[0] as $key => $match) {
                        $domainKey = strpos($match, 'trans(') !== false ? 8 : 10;
                        $stringToTranslate = $matches[2][$key];
                        $prefix_key = $matches[$domainKey][$key];

                        if ($prefix_key) {
                            if (isset($GLOBALS[$name_var][$prefix_key . md5($stringToTranslate)])) {
                                $tabs_array[$prefix_key][$stringToTranslate]['trad'] = stripslashes(html_entity_decode($GLOBALS[$name_var][$prefix_key . md5($stringToTranslate)], ENT_COMPAT, 'UTF-8'));
                            } else {
                                if (!isset($tabs_array[$prefix_key][$stringToTranslate]['trad'])) {
                                    $tabs_array[$prefix_key][$stringToTranslate]['trad'] = '';
                                    if (!isset($missing_translations_back[$prefix_key])) {
                                        $missing_translations_back[$prefix_key] = 1;
                                    } else {
                                        ++$missing_translations_back[$prefix_key];
                                    }
                                }
                            }
                            $tabs_array[$prefix_key][$stringToTranslate]['use_sprintf'] = $this->checkIfKeyUseSprintf($stringToTranslate);
                        }
                    }
                }
            }
        }

        // Parse SF2/Twig files
        $regexSf2Tpl = [
            '/trans\(([\'\"])' . _PS_TRANS_PATTERN_ . '([\'\"])(,\s*?[\{\[](.*)[\}\]])(,\s*?([\'\"])(.*)([\'\"]))?\)/Us',
            '/transchoice\(([\'\"])' . _PS_TRANS_PATTERN_ . '([\'\"])(,\s*?(.*))(,\s*?[\{\[](.*)[\}\]])(,\s*?([\'\"])(.*)([\'\"]))?\)/Us',
        ];

        foreach ($files_per_directory['tpl-sf2'] as $file) {
            // Get content for this file
            $content = file_get_contents(_PS_ROOT_DIR_ . '/src/PrestaShopBundle/Resources/views/' . $file);
            if (!$content) {
                continue;
            }

            // Parse this content
            foreach ($regexSf2Tpl as $reg) {
                preg_match_all($reg, $content, $matches);
                foreach ($matches[0] as $key => $match) {
                    $domainKey = strpos($match, 'trans(') !== false ? 8 : 10;
                    $stringToTranslate = $matches[2][$key];
                    $prefix_key = $matches[$domainKey][$key];

                    if ($prefix_key) {
                        if (isset($GLOBALS[$name_var][$prefix_key . md5($stringToTranslate)])) {
                            $tabs_array[$prefix_key][$stringToTranslate]['trad'] = stripslashes(html_entity_decode($GLOBALS[$name_var][$prefix_key . md5($stringToTranslate)], ENT_COMPAT, 'UTF-8'));
                        } else {
                            if (!isset($tabs_array[$prefix_key][$stringToTranslate]['trad'])) {
                                $tabs_array[$prefix_key][$stringToTranslate]['trad'] = '';
                                if (!isset($missing_translations_back[$prefix_key])) {
                                    $missing_translations_back[$prefix_key] = 1;
                                } else {
                                    ++$missing_translations_back[$prefix_key];
                                }
                            }
                        }
                        $tabs_array[$prefix_key][$stringToTranslate]['use_sprintf'] = $this->checkIfKeyUseSprintf($stringToTranslate);
                    }
                }
            }
        }

        // Parse ps PHP files
        foreach ($files_per_directory['php'] as $dir => $files) {
            foreach ($files as $file) {
                // Check if is a PHP file and if the override file exists
                if (preg_match('/^(.*)\.php$/', $file) && Tools::file_exists_cache($file_path = $dir . $file) && !in_array($file, self::$ignore_folder)) {
                    $prefix_key = basename($file);
                    // -4 becomes -14 to remove the ending "Controller.php" from the filename
                    if (strpos($file, 'Controller.php') !== false) {
                        $prefix_key = basename(substr($file, 0, -14));
                    } elseif (strpos($file, 'Helper') !== false) {
                        $prefix_key = 'Helper';
                    }

                    if ($prefix_key == 'Admin') {
                        $prefix_key = 'AdminController';
                    }

                    if ($prefix_key == 'PaymentModule.php') {
                        $prefix_key = 'PaymentModule';
                    }

                    // Get content for this file
                    $content = file_get_contents($file_path);

                    // Parse this content
                    $matches = $this->userParseFile($content, $this->type_selected, 'php');

                    foreach ($matches as $key) {
                        // Caution ! front has underscore between prefix key and md5, back has not
                        if (isset($GLOBALS[$name_var][$prefix_key . md5($key)])) {
                            $tabs_array[$prefix_key][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$name_var][$prefix_key . md5($key)], ENT_COMPAT, 'UTF-8'));
                        } else {
                            if (!isset($tabs_array[$prefix_key][$key]['trad'])) {
                                $tabs_array[$prefix_key][$key]['trad'] = '';
                                if (!isset($missing_translations_back[$prefix_key])) {
                                    $missing_translations_back[$prefix_key] = 1;
                                } else {
                                    ++$missing_translations_back[$prefix_key];
                                }
                            }
                        }
                        $tabs_array[$prefix_key][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                    }
                }
            }
        }

        foreach ($files_per_directory['specific'] as $dir => $files) {
            foreach ($files as $file) {
                if (Tools::file_exists_cache($file_path = $dir . $file) && !in_array($file, self::$ignore_folder)) {
                    $prefix_key = 'index';

                    // Get content for this file
                    $content = file_get_contents($file_path);

                    // Parse this content
                    $matches = $this->userParseFile($content, $this->type_selected, 'specific');

                    foreach ($matches as $key) {
                        // Caution ! front has underscore between prefix key and md5, back has not
                        if (isset($GLOBALS[$name_var][$prefix_key . md5($key)])) {
                            $tabs_array[$prefix_key][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$name_var][$prefix_key . md5($key)], ENT_COMPAT, 'UTF-8'));
                        } else {
                            if (!isset($tabs_array[$prefix_key][$key]['trad'])) {
                                $tabs_array[$prefix_key][$key]['trad'] = '';
                                if (!isset($missing_translations_back[$prefix_key])) {
                                    $missing_translations_back[$prefix_key] = 1;
                                } else {
                                    ++$missing_translations_back[$prefix_key];
                                }
                            }
                        }
                        $tabs_array[$prefix_key][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                    }
                }
            }
        }

        foreach ($files_per_directory['tpl'] as $dir => $files) {
            foreach ($files as $file) {
                if (preg_match('/^(.*).tpl$/', $file) && Tools::file_exists_cache($file_path = $dir . $file)) {
                    // get controller name instead of file name
                    $prefix_key = Tools::toCamelCase(str_replace(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'themes', '', $file_path), true);
                    $pos = strrpos($prefix_key, DIRECTORY_SEPARATOR);
                    $tmp = substr($prefix_key, 0, $pos);

                    if (preg_match('#controllers#', $tmp)) {
                        $parent_class = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $tmp));
                        $override = array_search('override', $parent_class);
                        if ($override !== false) {
                            // case override/controllers/admin/templates/controller_name
                            $prefix_key = 'Admin' . ucfirst($parent_class[$override + 4]);
                        } else {
                            // case admin_name/themes/theme_name/template/controllers/controller_name
                            $key = array_search('controllers', $parent_class);
                            $prefix_key = 'Admin' . ucfirst($parent_class[$key + 1]);
                        }
                    } else {
                        $prefix_key = 'Admin' . ucfirst(substr($tmp, strrpos($tmp, DIRECTORY_SEPARATOR) + 1, $pos));
                    }

                    // Adding list, form, option in Helper Translations
                    $list_prefix_key = ['AdminHelpers', 'AdminList', 'AdminView', 'AdminOptions', 'AdminForm',
                        'AdminCalendar', 'AdminTree', 'AdminUploader', 'AdminKpi', 'AdminModule_list', 'AdminModulesList',
                    ];
                    if (in_array($prefix_key, $list_prefix_key)) {
                        $prefix_key = 'Helper';
                    }

                    // Adding the folder backup/download/ in AdminBackup Translations
                    if ($prefix_key == 'AdminDownload') {
                        $prefix_key = 'AdminBackup';
                    }

                    // use the prefix "AdminController" (like old php files 'header', 'footer.inc', 'index', 'login', 'password', 'functions'
                    if ($prefix_key == 'Admin' || $prefix_key == 'AdminTemplate') {
                        $prefix_key = 'AdminController';
                    }

                    $new_lang = [];

                    // Get content for this file
                    $content = file_get_contents($file_path);

                    // Parse this content
                    $matches = $this->userParseFile($content, $this->type_selected, 'tpl');

                    /* Get string translation for each tpl file */
                    foreach ($matches as $english_string) {
                        if (empty($english_string)) {
                            $this->errors[] = $this->trans('There is an error in template, an empty string has been found. Please edit: "%file%"', ['%file%' => $file_path], 'Admin.International.Notification');
                            $new_lang[$english_string] = '';
                        } else {
                            $trans_key = $prefix_key . md5($english_string);

                            if (isset($GLOBALS[$name_var][$trans_key])) {
                                $new_lang[$english_string]['trad'] = html_entity_decode($GLOBALS[$name_var][$trans_key], ENT_COMPAT, 'UTF-8');
                            } else {
                                if (!isset($new_lang[$english_string]['trad'])) {
                                    $new_lang[$english_string]['trad'] = '';
                                    if (!isset($missing_translations_back[$prefix_key])) {
                                        $missing_translations_back[$prefix_key] = 1;
                                    } else {
                                        ++$missing_translations_back[$prefix_key];
                                    }
                                }
                            }
                            $new_lang[$english_string]['use_sprintf'] = $this->checkIfKeyUseSprintf($key ?? '');
                        }
                    }
                    if (isset($tabs_array[$prefix_key])) {
                        $tabs_array[$prefix_key] = array_merge($tabs_array[$prefix_key], $new_lang);
                    } else {
                        $tabs_array[$prefix_key] = $new_lang;
                    }
                }
            }
        }

        // count will contain the number of expressions of the page
        $count = 0;
        foreach ($tabs_array as $array) {
            $count += count($array);
        }

        $this->tpl_view_vars = array_merge($this->tpl_view_vars, [
            'count' => $count,
            'cancel_url' => $this->context->link->getAdminLink('AdminTranslations'),
            'limit_warning' => $this->displayLimitPostWarning($count),
            'mod_security_warning' => Tools::apacheModExists('mod_security'),
            'tabsArray' => $tabs_array,
            'missing_translations' => $missing_translations_back,
        ]);

        $this->initToolbar();
        $this->base_tpl_view = 'translation_form.tpl';

        return parent::renderView();
    }

    /**
     * Check if directory and file exist and return an list of modules.
     *
     * @return array List of modules
     *
     * @throws PrestaShopException
     */
    public function getListModules($withInstance = false)
    {
        if (!Tools::file_exists_cache($this->translations_informations['modules']['dir'])) {
            throw new PrestaShopException($this->trans('Fatal error: The module directory does not exist.', [], 'Admin.Notifications.Error') . '(' . $this->translations_informations['modules']['dir'] . ')');
        }
        if (!is_writable($this->translations_informations['modules']['dir'])) {
            throw new PrestaShopException($this->trans('The module directory must be writable.', [], 'Admin.International.Notification'));
        }

        $module_instances = [];
        // Get all module which are installed for to have a minimum of POST
        $modules = Module::getModulesInstalled();
        if ($withInstance) {
            foreach ($modules as $module) {
                if ($tmp_instance = Module::getInstanceById((int) $module['id_module'])) {
                    // We want to be able to sort modules by display name
                    $module_instances[$tmp_instance->displayName] = $tmp_instance;
                }
            }
            ksort($module_instances);

            return $module_instances;
        }

        foreach ($modules as &$module) {
            $module = $module['name'];
        }

        return $modules;
    }

    /**
     * This method generate the form for errors translations.
     */
    public function initFormErrors()
    {
        $name_var = $this->translations_informations[$this->type_selected]['var'];
        $GLOBALS[$name_var] = $this->fileExists();
        $count_empty = [];

        /* List files to parse */
        $string_to_translate = [];
        $file_by_directory = $this->getFileToParseByTypeTranslation();

        if ($modules = $this->getListModules()) {
            foreach ($modules as $module) {
                if (is_dir(_PS_MODULE_DIR_ . $module) && !in_array($module, self::$ignore_folder)) {
                    $file_by_directory['php'] = array_merge($file_by_directory['php'], $this->listFiles(_PS_MODULE_DIR_ . $module . '/', [], 'php'));
                }
            }
        }

        foreach ($file_by_directory['php'] as $dir => $files) {
            foreach ($files as $file) {
                if (preg_match('/\.php$/', $file) && Tools::file_exists_cache($file_path = $dir . $file) && !in_array($file, self::$ignore_folder)) {
                    if (!filesize($file_path)) {
                        continue;
                    }

                    // Get content for this file
                    $content = file_get_contents($file_path);

                    // Parse this content
                    $matches = $this->userParseFile($content, $this->type_selected);

                    foreach ($matches as $key) {
                        if (array_key_exists(md5($key), $GLOBALS[$name_var])) {
                            $string_to_translate[$key]['trad'] = html_entity_decode($GLOBALS[$name_var][md5($key)], ENT_COMPAT, 'UTF-8');
                        } else {
                            $string_to_translate[$key]['trad'] = '';
                            if (!isset($count_empty[$key])) {
                                $count_empty[$key] = 1;
                            }
                        }
                        $string_to_translate[$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                    }
                }
            }
        }

        // adding sf2 form translations
        $sf2_loader = new Symfony\Component\Translation\Loader\XliffFileLoader();

        try {
            $sf2_trans = $sf2_loader->load(_PS_VENDOR_DIR_ . '/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.' . $this->lang_selected->iso_code . '.xlf', $this->lang_selected->iso_code);
        } catch (Exception $e) {
            $sf2_trans = $sf2_loader->load(_PS_VENDOR_DIR_ . '/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.en.xlf', $this->lang_selected->iso_code);
        }

        foreach ($sf2_trans->all()['messages'] as $k => $v) {
            if (array_key_exists(md5($k), $GLOBALS[$name_var])) {
                $string_to_translate[$k]['trad'] = html_entity_decode($GLOBALS[$name_var][md5($k)], ENT_COMPAT, 'UTF-8');
            } else {
                $string_to_translate[$k]['trad'] = '';
                if (!isset($count_empty[$k])) {
                    $count_empty[$k] = 1;
                }
            }
            $string_to_translate[$k]['use_sprintf'] = false;
        }

        $this->tpl_view_vars = array_merge($this->tpl_view_vars, [
            'count' => count($string_to_translate),
            'cancel_url' => $this->context->link->getAdminLink('AdminTranslations'),
            'limit_warning' => $this->displayLimitPostWarning(count($string_to_translate)),
            'mod_security_warning' => Tools::apacheModExists('mod_security'),
            'errorsArray' => $string_to_translate,
            'missing_translations' => $count_empty,
        ]);

        $this->initToolbar();
        $this->base_tpl_view = 'translation_errors.tpl';

        return parent::renderView();
    }

    /**
     * Get each informations for each mails found in the folder $dir.
     *
     * @since 1.4.0.14
     *
     * @param string $dir
     * @param string $group_name
     *
     * @return array|false
     */
    public function getMailFiles($dir, $group_name = 'mail')
    {
        $arr_return = [];
        if (Language::getIdByIso('en')) {
            $default_language = 'en';
        } else {
            $default_language = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));
        }
        if (!$default_language || !Validate::isLanguageIsoCode($default_language)) {
            return false;
        }

        // Very usefull to name input and textarea fields
        $arr_return['group_name'] = $group_name;
        $arr_return['empty_values'] = 0;
        $arr_return['total_filled'] = 0;
        $arr_return['directory'] = $dir;

        // Get path for english mail directory
        $dir_en = str_replace('/' . $this->lang_selected->iso_code . '/', '/' . $default_language . '/', $dir);

        if (Tools::file_exists_cache($dir_en)) {
            // Get all english files to compare with the language to translate
            foreach (scandir($dir_en, SCANDIR_SORT_NONE) as $email_file) {
                if (strripos($email_file, '.html') > 0 || strripos($email_file, '.txt') > 0) {
                    $email_name = substr($email_file, 0, strripos($email_file, '.'));
                    $type = substr($email_file, strripos($email_file, '.') + 1);
                    if (!isset($arr_return['files'][$email_name])) {
                        $arr_return['files'][$email_name] = [];
                    }
                    // $email_file is from scandir ($dir), so we already know that file exists
                    $arr_return['files'][$email_name][$type]['en'] = $this->getMailContent($dir_en, $email_file);

                    // check if the file exists in the language to translate
                    if (Tools::file_exists_cache($dir . '/' . $email_file)) {
                        $arr_return['files'][$email_name][$type][$this->lang_selected->iso_code] = $this->getMailContent($dir, $email_file);
                        ++$this->total_expression;
                    } else {
                        $arr_return['files'][$email_name][$type][$this->lang_selected->iso_code] = '';
                    }

                    if ($arr_return['files'][$email_name][$type][$this->lang_selected->iso_code] == '') {
                        ++$arr_return['empty_values'];
                    } else {
                        ++$arr_return['total_filled'];
                    }
                }
            }
        } else {
            $this->warnings[] = $this->trans(
                'A mail directory exists for the "%iso_code%" language, but not for the default language (%language%) in %folder%',
                ['%iso_code%' => $this->lang_selected->iso_code, '%folder%' => str_replace(_PS_ROOT_DIR_, '', dirname($dir)), '%language%' => $default_language],
                'Admin.International.Notification'
            );
        }

        return $arr_return;
    }

    /**
     * Get content of the mail file.
     *
     * @since 1.4.0.14
     *
     * @param string $dir
     * @param string $file
     *
     * @return string|false
     */
    protected function getMailContent(string $dir, string $file)
    {
        $content = file_get_contents($dir . '/' . $file);

        if (Tools::strlen($content) === 0) {
            $content = '';
        }

        return $content;
    }

    /**
     * Display mails in html format.
     * This was create for factorize the html displaying.
     *
     * @since 1.4.0.14
     *
     * @param array $mails
     * @param array $all_subject_mail
     * @param Language $obj_lang
     * @param string $id_html Use for set html id attribute for the block
     * @param string $title Set the title for the block
     * @param string|bool $name_for_module Is not false define add a name for distinguish mails module
     *
     * @return string
     */
    protected function displayMailContent(
        array $mails,
        array $all_subject_mail,
        Language $obj_lang,
        string $id_html,
        string $title,
        string|bool $name_for_module = false
    ) {
        $str_return = '';
        $group_name = 'mail';
        if (array_key_exists('group_name', $mails)) {
            $group_name = $mails['group_name'];
        }

        if ($mails['empty_values'] == 0) {
            $translation_missing_badge_type = 'badge-success';
        } else {
            $translation_missing_badge_type = 'badge-danger';
        }
        $str_return .= '<div class="mails_field">
            <h4>
            <span class="badge">' . ((int) $mails['empty_values'] + (int) $mails['total_filled']) . ' <i class="icon-envelope-o"></i></span>
            <a href="javascript:void(0);" onclick="$(\'#' . $id_html . '\').slideToggle();">' . $title . '</a>
            <span class="pull-right badge ' . $translation_missing_badge_type . '">' . $mails['empty_values'] . ' ' . $this->trans('missing translation(s)', [], 'Admin.International.Notification') . '</span>
            </h4>
            <div name="mails_div" id="' . $id_html . '" class="panel-group">';

        if (!empty($mails['files'])) {
            $topic_already_displayed = [];
            foreach ($mails['files'] as $mail_name => $mail_files) {
                $str_return .= '<div class="panel translations-email-panel">';
                $str_return .= '<a href="#' . $id_html . '-' . $mail_name . '" class="panel-title" data-toggle="collapse" data-parent="#' . $id_html . '" >' . $mail_name . ' <i class="icon-caret-down"></i> </a>';
                $str_return .= '<div id="' . $id_html . '-' . $mail_name . '" class="email-collapse panel-collapse collapse">';
                if (array_key_exists('html', $mail_files) || array_key_exists('txt', $mail_files)) {
                    if (array_key_exists($mail_name, $all_subject_mail)) {
                        foreach ($all_subject_mail[$mail_name] as $subject_mail) {
                            $subject_key = 'subject[' . Tools::htmlentitiesUTF8($group_name) . '][' . Tools::htmlentitiesUTF8($subject_mail) . ']';
                            if (in_array($subject_key, $topic_already_displayed)) {
                                continue;
                            }
                            $topic_already_displayed[] = $subject_key;
                            $value_subject_mail = isset($mails['subject'][$subject_mail]) ? $mails['subject'][$subject_mail] : '';
                            $str_return .= '
                            <div class="label-subject row">
                                <label class="control-label col-lg-3">' . $this->trans('Email subject', [], 'Admin.International.Feature');
                            if (isset($value_subject_mail['use_sprintf']) && $value_subject_mail['use_sprintf']) {
                                $str_return .= '<span class="useSpecialSyntax" title="' . $this->trans('This expression uses a special syntax:', [], 'Admin.International.Notification') . ' ' . $value_subject_mail['use_sprintf'] . '">
                                    <i class="icon-exclamation-triangle"></i>
                                </span>';
                            }
                            $str_return .= '</label><div class="col-lg-9">';
                            if (isset($value_subject_mail['trad']) && $value_subject_mail['trad']) {
                                $str_return .= '<input class="form-control" type="text" name="subject[' . Tools::htmlentitiesUTF8($group_name) . '][' . Tools::htmlentitiesUTF8($subject_mail) . ']" value="' . $value_subject_mail['trad'] . '" />';
                            } else {
                                $str_return .= '<input class="form-control" type="text" name="subject[' . Tools::htmlentitiesUTF8($group_name) . '][' . Tools::htmlentitiesUTF8($subject_mail) . ']" value="" />';
                            }
                            $str_return .= '<p class="help-block">' . stripcslashes($subject_mail) . '</p>';
                            $str_return .= '</div></div>';
                        }
                    } else {
                        $str_return .= '
                            <hr><div class="alert alert-info">'
                            . $this->trans('No Subject was found for %mail_name% in the database.', ['%mail_name%' => $mail_name], 'Admin.International.Notification')
                            . '</div>';
                    }
                    // tab menu
                    $str_return .= '<hr><ul class="nav nav-pills">
                        <li class="active"><a href="#' . $mail_name . '-html" data-toggle="tab">' . $this->trans('View HTML version', [], 'Admin.International.Feature') . '</a></li>
                        <li><a href="#' . $mail_name . '-editor" data-toggle="tab">' . $this->trans('Edit HTML version', [], 'Admin.International.Feature') . '</a></li>
                        <li><a href="#' . $mail_name . '-text" data-toggle="tab">' . $this->trans('View/Edit TXT version', [], 'Admin.International.Feature') . '</a></li>
                        </ul>';
                    // tab-content
                    $str_return .= '<div class="tab-content">';

                    $base_uri = str_replace(_PS_ROOT_DIR_, __PS_BASE_URI__, $mails['directory']);
                    $base_uri = str_replace('//', '/', $base_uri);
                    $url_mail = $base_uri . $mail_name . '.html';

                    $mail_files_html = empty($mail_files['html']) ? false : $mail_files['html'];
                    $str_return .= '<div class="tab-pane active" id="' . $mail_name . '-html">';
                    $str_return .= $this->displayMailBlockHtml($mail_files_html, $obj_lang->iso_code, $url_mail, $mail_name, $group_name, $name_for_module);
                    $str_return .= '</div>';

                    $mail_files_txt = empty($mail_files['txt']) ? false : $mail_files['txt'];
                    $str_return .= '<div class="tab-pane" id="' . $mail_name . '-text">';
                    $str_return .= $this->displayMailBlockTxt($mail_files_txt, $obj_lang->iso_code, $mail_name, $group_name, $name_for_module);
                    $str_return .= '</div>';

                    $str_return .= '<div class="tab-pane" id="' . $mail_name . '-editor">';
                    $str_return .= $this->displayMailEditor($mail_files_html, $obj_lang->iso_code, $mail_name, $group_name, $name_for_module);
                    $str_return .= '</div>';

                    $str_return .= '</div>';
                    $str_return .= '</div><!--end .panel-collapse -->';
                    $str_return .= '</div><!--end .panel -->';
                }
            }
        } else {
            $str_return .= '<p class="error">
                ' . $this->trans('There was a problem getting the mail files.', [], 'Admin.International.Notification') . '<br>
                ' . $this->trans('English language files must exist in %folder% folder', [
                '%folder%' => '<em>' . preg_replace('@/[a-z]{2}(/?)$@', '/en$1', $mails['directory']) . '</em>',
            ], 'Admin.International.Notification') . '
            </p>';
        }

        $str_return .= '</div><!-- #' . $id_html . ' --></div><!-- end .mails_field -->';

        return $str_return;
    }

    /**
     * Just build the html structure for display txt mails.
     *
     * @since 1.4.0.14
     *
     * @param array $content With english and language needed contents
     * @param string $lang ISO code of the needed language
     * @param string $mail_name Name of the file to translate (same for txt and html files)
     * @param string $group_name group name allow to distinguish each block of mail
     * @param string|bool $name_for_module Is not false define add a name for distinguish mails module
     *
     * @return string
     */
    protected function displayMailBlockTxt(
        array $content,
        string $lang,
        string $mail_name,
        string $group_name,
        string|bool $name_for_module = false
    ) {
        if (!empty($content)) {
            $text_content = Tools::htmlentitiesUTF8(stripslashes(strip_tags($content[$lang])));
        } else {
            $text_content = '';
        }

        return '<div class="block-mail" >
                    <div class="mail-form">
                        <div><textarea class="rte noEditor" name="' . $group_name . '[txt][' . ($name_for_module ? $name_for_module . '|' : '') . $mail_name . ']">' . $text_content . '</textarea></div>
                    </div>
                </div>';
    }

    /**
     * Just build the html structure for display html mails.
     *
     * @since 1.4.0.14
     *
     * @param array $content With english and language needed contents
     * @param string $lang ISO code of the needed language
     * @param string $url The html page and displaying an outline
     * @param string $mail_name Name of the file to translate (same for txt and html files)
     * @param string $group_name group name allow to distinguish each block of mail
     * @param string|bool $name_for_module Is not false define add a name for distinguish mails module
     *
     * @return string
     */
    protected function displayMailBlockHtml(
        array $content,
        string $lang,
        string $url,
        string $mail_name,
        string $group_name,
        string|bool $name_for_module = false
    ) {
        $title = [];

        if (!empty($content)) {
            $this->cleanMailContent($content, $lang, $title);
        }

        $name_for_module = $name_for_module ? $name_for_module . '|' : '';

        return '<div class="block-mail" >
                    <div class="mail-form">
                        <div class="thumbnail email-html-frame" data-email-src="' . $url . '"></div>
                    </div>
                </div>';
    }

    protected function displayMailEditor(
        array $content,
        string $lang,
        string $mail_name,
        string $group_name,
        string|bool $name_for_module = false
    ) {
        $title = [];

        if (!empty($content)) {
            $this->cleanMailContent($content, $lang, $title);
            $html_content = $content[$lang];
        } else {
            $html_content = '';
        }

        $name_for_module = $name_for_module ? $name_for_module . '|' : '';

        return '<textarea class="rte-mail rte-mail-' . $mail_name . ' form-control" data-rte="' . $mail_name . '" name="' . $group_name . '[html][' . $name_for_module . $mail_name . ']">' . $html_content . '</textarea>';
    }

    protected function cleanMailContent(array &$content, string $lang, array &$title)
    {
        if (stripos($content[$lang], '<body')) {
            $array_lang = $lang != 'en' ? ['en', $lang] : [$lang];
            foreach ($array_lang as $language) {
                $title[$language] = substr($content[$language], 0, stripos($content[$language], '<body'));
                preg_match('#<title>([^<]+)</title>#Ui', $title[$language], $matches);
                $title[$language] = empty($matches[1]) ? '' : $matches[1];
            }
        }
        $content[$lang] = (isset($content[$lang]) ? Tools::htmlentitiesUTF8(stripslashes($content[$lang])) : '');
    }

    /**
     * Check in each module if contains mails folder.
     *
     * @param bool $with_module_name
     *
     * @return array Array of modules which have mails
     */
    public function getModulesHasMails($with_module_name = false)
    {
        $arr_modules = [];
        if (array_key_exists('dir', $this->translations_informations['modules'])) {
            if ($modules_dir = scandir($this->translations_informations['modules']['dir'], SCANDIR_SORT_NONE)) {
                foreach ($modules_dir as $module_dir) {
                    if (!in_array($module_dir, self::$ignore_folder)) {
                        $dir = false;
                        if ($this->theme_selected && Tools::file_exists_cache($this->translations_informations['modules']['override']['dir'] . $module_dir . '/mails/')) {
                            $dir = $this->translations_informations['modules']['override']['dir'] . $module_dir . '/';
                        } elseif (Tools::file_exists_cache($this->translations_informations['modules']['dir'] . $module_dir . '/mails/')) {
                            $dir = $this->translations_informations['modules']['dir'] . $module_dir . '/';
                        }
                        if ($dir !== false) {
                            if ($with_module_name) {
                                $arr_modules[$module_dir] = $dir;
                            } else {
                                if ($this->theme_selected) {
                                    $dir = $this->translations_informations['modules']['dir'] . $module_dir . '/';
                                }
                                $arr_modules[$dir] = scandir($dir, SCANDIR_SORT_NONE);
                            }
                        }
                    }
                }
            }
        }

        return $arr_modules;
    }

    /**
     * Check in each module if contains pdf folder.
     *
     * @param bool $classes
     *
     * @return array Array of modules which have pdf
     */
    public function getModulesHasPDF($classes = false)
    {
        $arr_modules = [];
        foreach (scandir($this->translations_informations['modules']['dir'], SCANDIR_SORT_NONE) as $module_dir) {
            if (!in_array($module_dir, self::$ignore_folder)) {
                $dir = false;
                if ($classes) {
                    if ($this->theme_selected && Tools::file_exists_cache($this->translations_informations['modules']['override']['dir'] . $module_dir . '/classes/')) {
                        $dir = $this->translations_informations['modules']['override']['dir'] . $module_dir . '/classes/';
                    } elseif (Tools::file_exists_cache($this->translations_informations['modules']['dir'] . $module_dir . '/classes/')) {
                        $dir = $this->translations_informations['modules']['dir'] . $module_dir . '/classes/';
                    }
                    if ($dir !== false) {
                        $arr_modules[$dir] = scandir($dir, SCANDIR_SORT_NONE);
                    }
                } else {
                    if ($this->theme_selected && Tools::file_exists_cache($this->translations_informations['modules']['override']['dir'] . $module_dir . '/pdf/')) {
                        $dir = $this->translations_informations['modules']['override']['dir'] . $module_dir . '/pdf/';
                    } elseif (Tools::file_exists_cache($this->translations_informations['modules']['dir'] . $module_dir . '/pdf/')) {
                        $dir = $this->translations_informations['modules']['dir'] . $module_dir . '/pdf/';
                    }
                    if ($dir !== false) {
                        $arr_modules[$dir] = scandir($dir, SCANDIR_SORT_NONE);
                    }
                }
            }
        }

        return $arr_modules;
    }

    protected function getTinyMCEForMails(string $iso_lang)
    {
        // TinyMCE
        $iso_tiny_mce = (Tools::file_exists_cache(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $iso_lang . '.js') ? $iso_lang : 'en');
        $ad = __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_);

        // return false;
        return '
            <script type="text/javascript">
                var iso = \'' . $iso_tiny_mce . '\' ;
                var pathCSS = \'' . _THEME_CSS_DIR_ . '\' ;
                var ad = \'' . $ad . '\' ;
            </script>
            <script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js"></script>
            <script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/admin/tinymce.inc.js"></script>';
    }

    /**
     * This method generate the form for mails translations.
     *
     * @param bool $no_display
     *
     * @return array|string
     */
    public function initFormMails($no_display = false)
    {
        $module_mails = [];

        // get all mail subjects, this method parse each files in Prestashop !!
        $subject_mail = [];

        $modules_has_mails = $this->getModulesHasMails(true);

        $files_by_directories = $this->getFileToParseByTypeTranslation();

        if (!$this->theme_selected || !@filemtime($this->translations_informations[$this->type_selected]['override']['dir'])) {
            $this->copyMailFilesForAllLanguages();
        }

        foreach ($files_by_directories['php'] as $dir => $files) {
            if (!empty($files)) {
                foreach ($files as $file) {
                    // If file exist and is not in ignore_folder, in the next step we check if a folder or mail
                    if (Tools::file_exists_cache($dir . $file) && !in_array($file, self::$ignore_folder)) {
                        $subject_mail = $this->getSubjectMail($dir, $file, $subject_mail);
                    }
                }
            }
        }

        // Get path of directory for find a good path of translation file
        if ($this->theme_selected && @filemtime($this->translations_informations[$this->type_selected]['override']['dir'])) {
            $i18n_dir = $this->translations_informations[$this->type_selected]['override']['dir'];
        } else {
            $i18n_dir = $this->translations_informations[$this->type_selected]['dir'];
        }

        $core_mails = $this->getMailFiles($i18n_dir, 'core_mail');
        $core_mails['subject'] = $this->getSubjectMailContent($i18n_dir);

        foreach ($modules_has_mails as $module_name => $module_path) {
            $module_path = rtrim($module_path, '/');
            $module_mails[$module_name] = $this->getMailFiles($module_path . '/mails/' . $this->lang_selected->iso_code . '/', 'module_mail');
            $module_mails[$module_name]['subject'] = $core_mails['subject'];
            $module_mails[$module_name]['display'] = $this->displayMailContent($module_mails[$module_name], $subject_mail, $this->lang_selected, Tools::strtolower($module_name), $module_name, $module_name);
        }

        if ($no_display) {
            $empty = 0;
            $total = 0;
            $total += (int) $core_mails['total_filled'];
            $empty += (int) $core_mails['empty_values'];
            foreach ($module_mails as $mod_infos) {
                $total += (int) $mod_infos['total_filled'];
                $empty += (int) $mod_infos['empty_values'];
            }

            return ['total' => $total, 'empty' => $empty];
        }

        $this->tpl_view_vars = array_merge($this->tpl_view_vars, [
            'limit_warning' => $this->displayLimitPostWarning($this->total_expression),
            'mod_security_warning' => Tools::apacheModExists('mod_security'),
            'tinyMCE' => $this->getTinyMCEForMails($this->lang_selected->iso_code),
            'mail_content' => $this->displayMailContent($core_mails, $subject_mail, $this->lang_selected, 'core', $this->trans('Core emails', [], 'Admin.International.Feature')),
            'cancel_url' => $this->context->link->getAdminLink('AdminTranslations'),
            'module_mails' => $module_mails,
            'theme_name' => $this->theme_selected,
        ]);

        $this->initToolbar();
        $this->base_tpl_view = 'translation_mails.tpl';

        return parent::renderView();
    }

    public function copyMailFilesForAllLanguages()
    {
        $current_theme = Tools::safeOutput($this->context->shop->theme->getName());
        $languages = Language::getLanguages();

        foreach ($languages as $key => $lang) {
            $dir_to_copy_iso = [];
            $files_to_copy_iso = [];
            $current_iso_code = $lang['iso_code'];

            $dir_to_copy_iso[] = _PS_MAIL_DIR_ . $current_iso_code . '/';

            $modules_has_mails = $this->getModulesHasMails(true);
            foreach ($modules_has_mails as $module_name => $module_path) {
                if ($pos = strpos($module_path, '/modules')) {
                    $dir_to_copy_iso[] = _PS_ROOT_DIR_ . substr($module_path, $pos) . 'mails/' . $current_iso_code . '/';
                }
            }

            foreach ($dir_to_copy_iso as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }
                $scanDir = scandir($dir, SCANDIR_SORT_NONE);
                if (!$scanDir) {
                    continue;
                }
                foreach ($scanDir as $file) {
                    if (!in_array($file, self::$ignore_folder)) {
                        $files_to_copy_iso[] = [
                            'from' => $dir . $file,
                            'to' => str_replace((strpos($dir, _PS_CORE_DIR_) !== false) ? _PS_CORE_DIR_ : _PS_ROOT_DIR_, _PS_ROOT_DIR_ . '/themes/' . $current_theme, $dir) . $file,
                        ];
                    }
                }
            }

            foreach ($files_to_copy_iso as $file) {
                if (!file_exists($file['to'])) {
                    $content = file_get_contents($file['from']);

                    $stack = [];
                    $folder = dirname($file['to']);
                    while (!is_dir($folder)) {
                        $stack[] = $folder;
                        $folder = dirname($folder);
                    }
                    while ($folder = array_pop($stack)) {
                        mkdir($folder);
                    }

                    $success = file_put_contents($file['to'], $content);
                    if ($success === false) {
                        Tools::dieOrLog(sprintf('%s cannot be copied to %s', $file['from'], $file['to']), false);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get list of subjects of mails.
     *
     * @param string $dir
     * @param string $file
     * @param array $subject_mail
     *
     * @return array : list of subjects of mails
     */
    protected function getSubjectMail(string $dir, string $file, array $subject_mail)
    {
        $dir = rtrim($dir, '/');
        // If is file and is not in ignore_folder
        if (is_file($dir . '/' . $file) && !in_array($file, self::$ignore_folder) && preg_match('/\.php$/', $file)) {
            $content = file_get_contents($dir . '/' . $file);
            $content = str_replace("\n", ' ', $content);

            // Subject must match with a template, therefore we first grep the Mail::Send() function then the Mail::l() inside.
            if (preg_match_all('/Mail::Send([^;]*);/si', $content, $tab)) {
                for ($i = 0; isset($tab[1][$i]); ++$i) {
                    $tab2 = explode(',', $tab[1][$i]);
                    if (is_array($tab2) && isset($tab2[1])) {
                        $template = trim(str_replace('\'', '', $tab2[1]));
                        foreach ($tab2 as $tab3) {
                            if (preg_match('/Mail::l\(\'' . _PS_TRANS_PATTERN_ . '\'\)/Us', $tab3 . ')', $matches)) {
                                if (!isset($subject_mail[$template])) {
                                    $subject_mail[$template] = [];
                                }
                                if (!in_array($matches[1], $subject_mail[$template])) {
                                    $subject_mail[$template][] = $matches[1];
                                }
                            }
                        }
                    }
                }
            }
        } elseif (!in_array($file, self::$ignore_folder) && is_dir($dir . '/' . $file)) {
            // Or if is folder, we scan folder for check if found in folder and subfolder
            foreach (scandir($dir . '/' . $file, SCANDIR_SORT_NONE) as $temp) {
                if ($temp[0] != '.') {
                    $subject_mail = $this->getSubjectMail($dir . '/' . $file, $temp, $subject_mail);
                }
            }
        }

        return $subject_mail;
    }

    protected function writeSubjectTranslationFile(array $sub, string $path)
    {
        if (!Tools::file_exists_cache(dirname($path))) {
            if (!mkdir(dirname($path), 0700)) {
                throw new PrestaShopException('Directory ' . dirname($path) . ' cannot be created.');
            }
        }
        if ($fd = @fopen($path, 'wb')) {
            $tab = 'LANGMAIL';
            fwrite($fd, "<?php\n\nglobal \$_" . $tab . ";\n\$_" . $tab . " = array();\n");

            foreach ($sub as $key => $value) {
                fwrite($fd, '$_' . $tab . '[\'' . pSQL($key) . '\'] = \'' . pSQL($value) . '\';' . "\n");
            }

            fwrite($fd, "\n?>");
            fclose($fd);
        } else {
            throw new PrestaShopException($this->trans('Cannot write language file for email subjects. Path is: %folder%', ['%folder%' => $path], 'Admin.International.Notification'));
        }
    }

    /**
     * This get files to translate in module directory.
     * Recursive method allow to get each files for a module no matter his depth.
     *
     * @param string $path directory path to scan
     * @param array $array_files by reference - array which saved files to parse
     * @param string $module_name module name
     * @param string $lang_file full path of translation file
     * @param bool $is_default
     */
    protected function recursiveGetModuleFiles(
        string $path,
        array &$array_files,
        string $module_name,
        string $lang_file,
        bool $is_default = false
    ) {
        $files_module = [];
        if (Tools::file_exists_cache($path)) {
            $files_module = scandir($path, SCANDIR_SORT_NONE);
        }
        $files_for_module = $this->clearModuleFiles($files_module, 'file');
        if (!empty($files_for_module)) {
            $array_files[] = [
                'file_name' => $lang_file,
                'dir' => $path,
                'files' => $files_for_module,
                'module' => $module_name,
                'is_default' => $is_default,
                'theme' => $this->theme_selected,
            ];
        }

        $dir_module = $this->clearModuleFiles($files_module, 'directory', $path);

        if (!empty($dir_module)) {
            foreach ($dir_module as $folder) {
                $this->recursiveGetModuleFiles($path . $folder . '/', $array_files, $module_name, $lang_file, $is_default);
            }
        }
    }

    /**
     * This method get translation in each translations file.
     * The file depend on $lang param.
     *
     * @param array $modules List of modules
     * @param string|null $root_dir path where it get each modules
     * @param string $lang ISO code of chosen language to translate
     * @param bool $is_default Set it if modules are located in root/prestashop/modules folder
     *                         This allow to distinguish overridden prestashop theme and original module
     *
     * @return array
     */
    protected function getAllModuleFiles(array $modules, ?string $root_dir, string $lang, bool $is_default = false)
    {
        $array_files = [];
        $initial_root_dir = $root_dir;
        foreach ($modules as $module) {
            $root_dir = $initial_root_dir;
            if (isset($module[0]) && $module[0] == '.') {
                continue;
            }

            // First we load the default translation file
            if ($root_dir == null) {
                $i18n_dir = $this->translations_informations[$this->type_selected]['dir'];
                if (is_dir($i18n_dir . $module)) {
                    $root_dir = $i18n_dir;
                }

                $lang_file = $root_dir . $module . '/translations/' . $lang . '.php';
                if (!Tools::file_exists_cache($root_dir . $module . '/translations/' . $lang . '.php') && Tools::file_exists_cache($root_dir . $module . '/' . $lang . '.php')) {
                    $lang_file = $root_dir . $module . '/' . $lang . '.php';
                }
                @include $lang_file;
                $this->getModuleTranslations();
                // If a theme is selected, then the destination translation file must be in the theme
                if ($this->theme_selected) {
                    $lang_file = $this->translations_informations[$this->type_selected]['override']['dir'] . $module . '/translations/' . $lang . '.php';
                }
                $this->recursiveGetModuleFiles($root_dir . $module . '/', $array_files, $module, $lang_file, $is_default);
            }

            $root_dir = $initial_root_dir;
            // Then we load the overriden translation file
            if ($this->theme_selected && isset($this->translations_informations[$this->type_selected]['override'])) {
                $i18n_dir = $this->translations_informations[$this->type_selected]['override']['dir'];
                if (is_dir($i18n_dir . $module)) {
                    $root_dir = $i18n_dir;
                }
                if (Tools::file_exists_cache($root_dir . $module . '/translations/' . $lang . '.php')) {
                    $lang_file = $root_dir . $module . '/translations/' . $lang . '.php';
                } elseif (Tools::file_exists_cache($root_dir . $module . '/' . $lang . '.php')) {
                    $lang_file = $root_dir . $module . '/' . $lang . '.php';
                }
                if (!empty($lang_file)) {
                    @include $lang_file;
                    $this->getModuleTranslations();
                    $this->recursiveGetModuleFiles($root_dir . $module . '/', $array_files, $module, $lang_file, $is_default);
                }
            }
        }

        return $array_files;
    }

    /**
     * This method generate the form for modules translations.
     */
    public function initFormModules()
    {
        // Get list of installed modules
        $installed_modules = $this->getListModules();

        // get selected module
        $modules = [Tools::getValue('module')];

        // Get all modules files and include all translation files
        $arr_files = $this->getAllModuleFiles($modules, null, $this->lang_selected->iso_code, true);
        foreach ($arr_files as $value) {
            $this->findAndFillTranslations($value['files'], $value['theme'], $value['module'], $value['dir']);
        }

        $this->tpl_view_vars = array_merge($this->tpl_view_vars, [
            'default_theme_name' => self::DEFAULT_THEME_NAME,
            'count' => $this->total_expression,
            'limit_warning' => $this->displayLimitPostWarning($this->total_expression),
            'mod_security_warning' => Tools::apacheModExists('mod_security'),
            'textarea_sized' => self::TEXTAREA_SIZED,
            'cancel_url' => $this->context->link->getAdminLink('AdminTranslations'),
            'modules_translations' => $this->modules_translations,
            'missing_translations' => $this->missing_translations,
            'module_name' => $modules[0],
            'installed_modules' => $installed_modules,
        ]);

        $this->initToolbar();
        $this->base_tpl_view = 'translation_modules.tpl';

        return parent::renderView();
    }

    /**
     * Parse PDF class.
     *
     * @since 1.4.5.0
     *
     * @param string $file_path File to parse
     * @param string $file_type Type of file
     * @param array $lang_array Contains expression in the chosen language
     * @param string $tab name      To use with the md5 key
     * @param array $tabs_array
     * @param array $count_missing
     *
     * @return array Array          Containing all datas needed for building the translation form
     */
    protected function parsePdfClass(string $file_path, string $file_type, array $lang_array, string $tab, array $tabs_array, array &$count_missing)
    {
        // Get content for this file
        $content = file_get_contents($file_path);

        // Parse this content
        $matches = $this->userParseFile($content, $this->type_selected, $file_type);

        foreach ($matches as $key) {
            if (array_key_exists($tab . md5(addslashes($key)), $lang_array)) {
                $tabs_array[$tab][$key]['trad'] = html_entity_decode($lang_array[$tab . md5(addslashes($key))], ENT_COMPAT, 'UTF-8');
            } else {
                $tabs_array[$tab][$key]['trad'] = '';
                if (!isset($count_missing[$tab])) {
                    $count_missing[$tab] = 1;
                } else {
                    ++$count_missing[$tab];
                }
            }
            $tabs_array[$tab][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
        }

        return $tabs_array;
    }

    /**
     * This method generate the form for PDF translations.
     */
    public function initFormPDF()
    {
        $name_var = $this->translations_informations[$this->type_selected]['var'];
        $GLOBALS[$name_var] = [];
        $missing_translations_pdf = [];

        $i18n_dir = $this->translations_informations[$this->type_selected]['dir'];
        $default_i18n_file = $i18n_dir . $this->translations_informations[$this->type_selected]['file'];

        if (!$this->theme_selected) {
            $i18n_file = $default_i18n_file;
        } else {
            $i18n_dir = $this->translations_informations[$this->type_selected]['override']['dir'];
            $i18n_file = $i18n_dir . $this->translations_informations[$this->type_selected]['override']['file'];
        }

        $this->checkDirAndCreate($i18n_file);
        if ((!file_exists($i18n_file) && !is_writable($i18n_dir)) && !is_writable($i18n_file)) {
            $this->errors[] = $this->trans('Cannot write into the "%file%"', ['%file%' => $i18n_file], 'Admin.International.Notification');
        }

        @include $i18n_file;

        // if the override's translation file is empty load the default file
        /* @phpstan-ignore-next-line */
        if (!isset($GLOBALS[$name_var]) || count($GLOBALS[$name_var]) == 0) {
            @include $default_i18n_file;
        }

        $prefix_key = 'PDF';
        $tabs_array = [$prefix_key => []];

        $files_by_directory = $this->getFileToParseByTypeTranslation();

        foreach ($files_by_directory as $type => $directories) {
            foreach ($directories as $dir => $files) {
                foreach ($files as $file) {
                    if (!in_array($file, self::$ignore_folder) && Tools::file_exists_cache($file_path = $dir . $file)) {
                        if ($type == 'tpl') {
                            if (Tools::file_exists_cache($file_path) && is_file($file_path)) {
                                // Get content for this file
                                $content = file_get_contents($file_path);

                                // Parse this content
                                $matches = $this->userParseFile($content, $this->type_selected, 'tpl');

                                foreach ($matches as $key) {
                                    /* @phpstan-ignore-next-line */
                                    if (isset($GLOBALS[$name_var][$prefix_key . md5($key)])) {
                                        $tabs_array[$prefix_key][$key]['trad'] = html_entity_decode($GLOBALS[$name_var][$prefix_key . md5($key)], ENT_COMPAT, 'UTF-8');
                                    } else {
                                        if (!isset($tabs_array[$prefix_key][$key]['trad'])) {
                                            $tabs_array[$prefix_key][$key]['trad'] = '';
                                            if (!isset($missing_translations_pdf[$prefix_key])) {
                                                $missing_translations_pdf[$prefix_key] = 1;
                                            } else {
                                                ++$missing_translations_pdf[$prefix_key];
                                            }
                                        }
                                    }
                                    $tabs_array[$prefix_key][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
                                }
                            }
                        } elseif (Tools::file_exists_cache($file_path)) {
                            $tabs_array = $this->parsePdfClass($file_path, 'php', $GLOBALS[$name_var], $prefix_key, $tabs_array, $missing_translations_pdf);
                        }
                    }
                }
            }
        }

        $this->tpl_view_vars = array_merge($this->tpl_view_vars, [
            'count' => count($tabs_array['PDF']),
            'limit_warning' => $this->displayLimitPostWarning(count($tabs_array['PDF'])),
            'mod_security_warning' => Tools::apacheModExists('mod_security'),
            'tabsArray' => $tabs_array,
            'cancel_url' => $this->context->link->getAdminLink('AdminTranslations'),
            'missing_translations' => $missing_translations_pdf,
        ]);

        $this->initToolbar();
        $this->base_tpl_view = 'translation_form.tpl';

        return parent::renderView();
    }

    /**
     * Recursively list files in directory $dir.
     *
     * @param string $dir
     * @param array $list
     * @param string $file_ext
     *
     * @return array
     */
    public function listFiles($dir, $list = [], $file_ext = 'tpl')
    {
        $dir = rtrim($dir, '/') . DIRECTORY_SEPARATOR;

        $to_parse = is_dir($dir) ? scandir($dir, SCANDIR_SORT_NONE) : [];
        // copied (and kind of) adapted from AdminImages.php
        foreach ($to_parse as $file) {
            if (!in_array($file, self::$ignore_folder)) {
                if (preg_match('#' . preg_quote($file_ext, '#') . '$#i', $file)) {
                    $list[$dir][] = $file;
                } elseif (is_dir($dir . $file)) {
                    $list = $this->listFiles($dir . $file, $list, $file_ext);
                }
            }
        }

        return $list;
    }

    /**
     * Checks if theme exists.
     *
     * @param string $theme
     *
     * @return bool
     */
    protected function theme_exists(string $theme)
    {
        foreach ($this->themes as $existing_theme) {
            /** @var Theme $existing_theme */
            if ($existing_theme->getName() == $theme) {
                return true;
            }
        }

        return false;
    }

    public static function getEmailHTML($email)
    {
        if (__PS_BASE_URI__ != '/') {
            $email_file = str_replace(__PS_BASE_URI__, _PS_ROOT_DIR_ . '/', $email);
        } else {
            $email_file = _PS_ROOT_DIR_ . $email;
        }

        if (strpos(realpath($email_file), _PS_MAIL_DIR_) === 0 && file_exists($email_file)) {
            $email_html = file_get_contents($email_file);
        } else {
            $email_html = '';
        }

        return $email_html;
    }

    /**
     * Display the HTML content of an email.
     */
    public function displayAjaxEmailHTML()
    {
        $email = Tools::getValue('email');
        $this->ajaxRender(
            AdminTranslationsController::getEmailHTML($email)
        );
    }

    /**
     * @param string $directory Name of directory
     *
     * @return array
     */
    protected function getSubjectMailContent(string $directory)
    {
        $subject_mail_content = [];
        if (Tools::file_exists_cache($directory . '/lang.php')) {
            // we need to include this even if already included (no include once)
            include $directory . '/lang.php';
            foreach ($GLOBALS[$this->translations_informations[$this->type_selected]['var']] as $key => $subject) {
                ++$this->total_expression;
                $subject = str_replace('\n', ' ', $subject);
                $subject = str_replace("\\'", "\'", $subject);
                $subject_mail_content[$key]['trad'] = htmlentities($subject, ENT_QUOTES, 'UTF-8');
                $subject_mail_content[$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
            }
        }

        return $subject_mail_content;
    }
}
