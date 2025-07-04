<?php

use AxonCreator\Wp_Helper;

class AdminNrtUpgradeController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('magic_quotes_runtime', '0');
        @ini_set('magic_quotes_sybase', '0');

        $this->fields_options = array(
            'general' => array(
                'title' =>  $this->module->l('Settings'),
                'fields' => array(
                    'NrtAutoupPerformance' => array(
                        'title' => $this->module->l('Server performance'),
                        'desc' => $this->module->l('Unless you are using a dedicated server, select "Low".') . '<br />' . $this->module->l('A high value can cause the upgrade to fail if your server is not powerful enough to process the upgrade tasks in a short amount of time.'),
                        'cast' => 'intval',
                        'type' => 'select',
                        'list' => [['id' => 400, 'name' => $this->module->l('Low (recommended)')], ['id' => 800, 'name' => $this->module->l('Medium')], ['id' => 1600, 'name' => $this->module->l('High')]],
                        'identifier' => 'id'
                    )
                ),
                'submit' => array('title' => $this->module->l('Save'))
            )
        );
    }

    public function setMedia($isNewTheme = false) {
        parent::setMedia();
         
        Media::addJsDef(array(
            'opNrtUpgrade' => [
                'ajaxUrl' => Context::getContext()->link->getAdminLink( 'AdminNrtUpgrade' ) . '&ajax=1&action=upgradeTheme',
                '_PS_MODE_DEV_' => (defined('_PS_MODE_DEV_') && true == _PS_MODE_DEV_),
                'jsonParseErrorForAction' => $this->module->l('Javascript error (parseJSON) detected for action '),
                'endOfProcess' => $this->module->l('End of process'),
                'errorDetectedDuring' => $this->module->l('Error detected during'),
                'cannotDownloadFile' => $this->module->l('Your server cannot download the file. Please upload it first by ftp in your modules/nrtupgrade/autoupgrade directory'),
                'downloadTimeout' => $this->module->l('The request exceeded the max_time_limit. Please change your server configuration.'),
                'updateInProgress' => $this->module->l('An update is currently in progress... Click "OK" to abort.'),
                'upgradingTheme' => $this->module->l('Upgrading Theme'),
                'upgradeComplete' => $this->module->l('Upgrade complete'),
                'upgradeCompleteWithWarnings' => $this->module->l('Upgrade complete, but warning notifications has been found.'),
                'todoList' => [
                    $this->module->l('Cookies have changed, you will need to log in again once you refreshed the page'),
                    $this->module->l('Javascript and CSS files have changed, please clear your browser cache with CTRL-F5'),
                    $this->module->l('Please check that your front-office theme is functional (try to create an account, place an order...)'),
                    $this->module->l('Product images do not appear in the front-office? Try regenerating the thumbnails in Preferences > Images'),
                    $this->module->l('Do not forget to reactivate your shop once you have checked everything!')
                ],
                'todoListTitle' => $this->module->l('ToDo list:'),
            ]
        ));
        
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin.js');
        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/admin.css');
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('Axon - 1 Click Upgrade Akira');
    }
	
    public function renderList()
    {		
        $showUpgradeButton = false;

        $checkLicense = $this->module->checkLicense();

        $upgrade_now = false;

        if($checkLicense){
            if(version_compare( Configuration::get('nrt_akira_version_2'), $checkLicense['version'], '<' )){
                $upgrade_now = true;
            }
        }

        $config = [
            'activeLink' => Context::getContext()->link->getAdminLink('AdminAxonCreatorLicense'),
            'maintenanceLink' => Context::getContext()->link->getAdminLink('AdminMaintenance'),
            'rootDirectoryIsWritable' => $this->module->isRootDirectoryWritable(),
            'rootDirectoryWritableReport' => $this->module->getRootWritableReport(),
            'rootDirectory' => _PS_ROOT_DIR_,
            'safeModeIsDisabled' => $this->module->isSafeModeDisabled(),
            'allowFOpenIsEnabledOrAllowCUrlIsInstalled' => $this->module->isFOpenEnabledOrIsCUrlInstalled(),
            'zipIsEnabled' => $this->module->isZipEnabled(),
            'storeIsInMaintenance' => $this->module->isShopDeactivated(),
            'cachingIsDisabled' => $this->module->isCacheDisabled(),
            'maxExecutionTime' => $this->module->getMaxExecutionTime(),
            'showUpgradeButton' => $this->module->isOkForUpgrade(),
            'requiredAxonCreator' => Module::isEnabled('axoncreator'),
            'checkLicense' => $checkLicense ? $checkLicense['active'] : false,
            'currentPsVersion' => Configuration::get('nrt_akira_version_2') ? Configuration::get('nrt_akira_version_2') : 'N/A',
            'latestChannelVersion' => $checkLicense ? $checkLicense['version'] : 'N/A',
            'upgrade_now' => $upgrade_now
        ];

        $this->context->smarty->assign(['config' => $config]);

        $output = $this->context->smarty->fetch('module:nrtupgrade/views/templates/admin/configure.tpl');
        
        return $output . parent::renderList();
    }	

    public function ajaxProcessUpgradeTheme()
    {
        $func = Tools::getValue('func');

        switch ($func) {
            case 'upgradeNrtNow':
                $this->upgradeNrtNow();
                break;
            case 'nrtDownload':
                $this->nrtDownload();
                break;
            case 'nrtUnzip':
                $this->nrtUnzip();
                break;   
            case 'upgradeNrtFiles':
                $this->upgradeNrtFiles();
                break;  
            case 'upgradeNrtModules':
                $this->upgradeNrtModules();
                break;  
            case 'upgradeNrtComplete':
                $this->upgradeNrtComplete();
                break;  
        }

        $this->module->nrtGetResponseJson();
    }

    public function upgradeNrtNow()
    {
        $channel = 'default';

        $this->module->nrtInfo($this->module->l('Starting upgrade...'));

        switch ($channel) {
            case 'archive':
                NrtUpgrade::$nrtNext = 'nrtUnzip';
                $this->module->nrtDebug($this->module->l('Downloading step has been skipped, upgrade process will now unzip the local archive.'));
                $this->module->nrtInfo($this->module->l('Shop deactivated. Extracting files...'));
                break;
            default:
                NrtUpgrade::$nrtNext = 'nrtDownload';
                $this->module->nrtInfo($this->module->l('Shop deactivated. Now downloading... (this can take a while)'));
                $this->module->nrtDebug($this->module->l('Downloaded archive will come from AxonVIZ(theme.zip)'));
        }
    }

    public function nrtDownload()
    {
        $this->module->nrtDebug($this->module->l('Downloading from AxonVIZ(theme.zip)'));
        $this->module->nrtDebug($this->module->l('File will be saved in modules/nrtupgrade/autoupgrade/download'));

        $download_path = str_replace('/', DIRECTORY_SEPARATOR, _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/download');

        if (file_exists($download_path)) {
            NrtUpgrade::deleteDirectory($download_path, false);
            $this->module->nrtDebug($this->module->l('Download directory has been emptied'));
        }

        $report = '';
        $relative_download_path = str_replace(_PS_ROOT_DIR_, '', $download_path);

        $getLink = $this->module->getNrtLink();

        if(isset($getLink['error'])){
            $this->module->nrtError($getLink['error']);
            NrtUpgrade::$nrtNext = 'error';

            return;
        }

        NrtUpgrade::$nrtLink = $getLink['link'];

        if (ConfigurationTest::test_dir($relative_download_path, false, $report)) {
            $res = $this->module->downloadLast($download_path);
            
            if ($res) {
                NrtUpgrade::$nrtNext = 'nrtUnzip';
                $this->module->nrtDebug($this->module->l('Download complete.'));
                $this->module->nrtInfo($this->module->l('Download complete. Now extracting...'));
            } else {
                $this->module->nrtError($this->module->l('Error during download'));
                NrtUpgrade::$nrtNext = 'error';
            }
        } else {
            $this->module->nrtError($this->module->l('Download directory modules/nrtupgrade/autoupgrade/download/ is not writable.'));
            NrtUpgrade::$nrtNext = 'error';
        }
    }

    public function nrtUnzip()
    {
        $filepath = str_replace('/', DIRECTORY_SEPARATOR, _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/download/theme.zip');
        $destExtract = str_replace('/', DIRECTORY_SEPARATOR, _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/latest');

        if (file_exists($destExtract)) {
            NrtUpgrade::deleteDirectory($destExtract, false);
            $this->module->nrtDebug($this->module->l('"/latest" directory has been emptied'));
        }

        $report = '';
        $relative_extract_path = str_replace(_PS_ROOT_DIR_, '', $destExtract);

        if (!ConfigurationTest::test_dir($relative_extract_path, false, $report)) {
            $this->module->nrtError($this->module->l('Extraction directory modules/nrtupgrade/autoupgrade/latest is not writable.'));
            NrtUpgrade::$nrtNext = 'error';

            return;
        }

        $res = $this->module->nrtExtract($filepath, $destExtract);

        if (!$res) {
            NrtUpgrade::$nrtNext = 'error';
            $this->module->nrtInfo(
                sprintf(
                    $this->module->l( 'Unable to extract %s file into %s folder...' ),
                    $filepath,
                    $destExtract
                )
            );

            return;
        }

        NrtUpgrade::$nrtNext = 'upgradeNrtFiles';
        $this->module->nrtInfo($this->module->l('File extraction complete. Removing sample files...'));

        unlink($filepath);
    }

    public function upgradeNrtFiles()
    {
        $FILES_TO_UPGRADE_LIST = 'filesToUpgrade.list';

        if (!$this->module->nrtExists($FILES_TO_UPGRADE_LIST)) {
            return $this->warmUp();
        }

        NrtUpgrade::$nrtNext = 'upgradeNrtFiles';

        $filesToUpgrade = $this->module->nrtLoad($FILES_TO_UPGRADE_LIST);

        if (!is_array($filesToUpgrade)) {
            NrtUpgrade::$nrtNext = 'error';
            $this->module->nrtError($this->module->l('filesToUpgrade is not an array'));

            return false;
        }

        $performance = Configuration::get('NrtAutoupPerformance') ? Configuration::get('NrtAutoupPerformance') : 400;

        for ($i = 0; $i < $performance; ++$i) {
            if (count($filesToUpgrade) <= 0) {
                NrtUpgrade::$nrtNext = 'upgradeNrtModules';
                if (file_exists($FILES_TO_UPGRADE_LIST)) {
                    unlink($FILES_TO_UPGRADE_LIST);
                }
                $this->module->nrtInfo($this->module->l('All files upgraded.'));
                NrtUpgrade::$nrtStepDone = true;
                break;
            }

            $file = array_pop($filesToUpgrade);
            if (!$this->upgradeThisFile($file)) {
                NrtUpgrade::$nrtNext = 'error';
                $this->module->nrtError(
                    sprintf(
                        $this->module->l( 'Error when trying to upgrade file %s.' ),
                        $file
                    )
                );

                break;
            }
        }

        $this->module->nrtSave($filesToUpgrade, $FILES_TO_UPGRADE_LIST);

        if (count($filesToUpgrade) > 0) {
            $this->module->nrtInfo(
                sprintf(
                    $this->module->l( '%s files left to upgrade.' ),
                    count($filesToUpgrade)
                )
            );

            NrtUpgrade::$nrtStepDone = false;
        }
    }

    protected function warmUp()
    {
        $FILES_TO_UPGRADE_LIST = 'filesToUpgrade.list';

        $newReleasePath = str_replace('/', DIRECTORY_SEPARATOR, _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/latest');

        $list_files_to_upgrade = $this->listFilesToUpgrade($newReleasePath);

        if (false === $list_files_to_upgrade) {
            return false;
        }

        $list_files_diff = [];
        
        $list_files_to_upgrade = array_reverse(array_merge($list_files_diff, $list_files_to_upgrade));

        $this->module->nrtSave($list_files_to_upgrade, $FILES_TO_UPGRADE_LIST);
        $total_files_to_upgrade = count($list_files_to_upgrade);

        if ($total_files_to_upgrade == 0) {
            $this->module->nrtError($this->module->l('[ERROR] Unable to find files to upgrade.'));
            NrtUpgrade::$nrtNext = 'error';

            return false;
        }

        $this->module->nrtInfo(
            sprintf(
                $this->module->l( '%s files will be upgraded.' ),
                $total_files_to_upgrade
            )
        );

        NrtUpgrade::$nrtNext = 'upgradeNrtFiles';
        NrtUpgrade::$nrtStepDone = false;

        return true;
    }

    protected function listFilesToUpgrade($dir)
    {
        $newReleasePath = str_replace('/', DIRECTORY_SEPARATOR, _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/latest');

        $list = array();
        if (!is_dir($dir)) {
            $this->module->nrtError(
                sprintf(
                    $this->module->l( '[ERROR] %s does not exist or is not a directory.' ),
                    $dir
                )
            );
            $this->module->nrtInfo($this->module->l('Nothing has been extracted. It seems the unzipping step has been skipped.'));
            NrtUpgrade::$nrtNext = 'error';

            return false;
        }

        $allFiles = scandir($dir);

        foreach ($allFiles as $file) {
            if (!in_array($file, array('.', '..'))) {
                $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

                $list[] = str_replace($newReleasePath, '', $fullPath);
                
                if (is_dir($fullPath)) {
                    $list = array_merge($list, $this->listFilesToUpgrade($fullPath));
                }
            }
        }

        return $list;
    }

    public function upgradeThisFile($file)
    {
        // translations_custom and mails_custom list are currently not used
        // later, we could handle customization with some kind of diff functions
        // for now, just copy $file in str_replace($this->latestRootDir,_PS_ROOT_DIR_)

        $newReleasePath = str_replace('/', DIRECTORY_SEPARATOR, _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/latest');
        $destUpgradePath = str_replace('/', DIRECTORY_SEPARATOR, _PS_ROOT_DIR_);

        $orig = $newReleasePath . $file;
        $dest = $destUpgradePath . $file;

        if (is_dir($orig)) {
            // if $dest is not a directory (that can happen), just remove that file
            if (!is_dir($dest) && file_exists($dest)) {
                unlink($dest);
                $this->module->nrtDebug(
                    sprintf(
                        $this->module->l( '[WARNING] File %1$s has been deleted.' ),
                        $file
                    )
                );
            }
            if (!file_exists($dest)) {
                if (mkdir($dest)) {
                    $this->module->nrtDebug(
                        sprintf(
                            $this->module->l( 'Directory %1$s created.' ),
                            $file
                        )
                    );

                    return true;
                } else {
                    NrtUpgrade::$nrtNext = 'error';

                    $this->module->nrtError(
                        sprintf(
                            $this->module->l( 'Error while creating directory %s.' ),
                            $dest
                        )
                    );

                    return false;
                }
            } else { // directory already exists
                $this->module->nrtDebug(
                    sprintf(
                        $this->module->l( 'Directory %s already exists.' ),
                        $file
                    )
                );

                return true;
            }
        } elseif (is_file($orig)) {
            if (copy($orig, $dest)) {
                $this->module->nrtDebug(
                    sprintf(
                        $this->module->l( 'Copied %1$s.' ),
                        $file
                    )
                );

                return true;
            } else {
                NrtUpgrade::$nrtNext = 'error';
                $this->module->nrtError(
                    sprintf(
                        $this->module->l( 'Error while copying file %s' ),
                        $dest
                    )
                );

                return false;
            }
        } elseif (is_file($dest)) {
            if (file_exists($dest)) {
                unlink($dest);
            }
            $this->module->nrtDebug(
                sprintf(
                    $this->module->l( 'Removed file %1$s.' ),
                    $file
                )
            );

            return true;
        } elseif (is_dir($dest)) {
            if (strpos($dest, DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR) === false) {
                NrtUpgrade::deleteDirectory($dest, true);
            }

            $this->module->nrtDebug(
                sprintf(
                    $this->module->l( 'Removed dir %1$s.' ),
                    $file
                )
            );

            return true;
        } else {
            return true;
        }
    }

    public function upgradeNrtModules()
    {
        $MODULES_TO_UPGRADE_LIST = 'modulesToUpgrade.list';

        if (!$this->module->nrtExists($MODULES_TO_UPGRADE_LIST)) {
            return $this->warmUpModules();
        }

        NrtUpgrade::$nrtNext = 'upgradeNrtModules';

        $modulesToUpgrade = $this->module->nrtLoad($MODULES_TO_UPGRADE_LIST);

        if (!is_array($modulesToUpgrade)) {
            NrtUpgrade::$nrtNext = 'error';
            $this->module->nrtError($this->module->l('modulesToUpgrade is not an array'));

            return false;
        }

        for ($i = 0; $i < 9999; ++$i) {
            if (count($modulesToUpgrade) <= 0) {
                NrtUpgrade::$nrtNext = 'upgradeNrtComplete';
                if (file_exists($MODULES_TO_UPGRADE_LIST)) {
                    unlink($MODULES_TO_UPGRADE_LIST);
                }
                $this->module->nrtInfo($this->module->l('All modules upgraded.'));
                NrtUpgrade::$nrtStepDone = true;
                break;
            }

            $module_name = array_pop($modulesToUpgrade);

            if (!$this->upgradeThisModule($module_name)) {
                NrtUpgrade::$nrtNext = 'error';
                $this->module->nrtError(
                    sprintf(
                        $this->module->l( 'Error when trying to upgrade module %s.' ),
                        $module_name
                    )
                );

                break;
            }
        }

        $this->module->nrtSave($modulesToUpgrade, $MODULES_TO_UPGRADE_LIST);

        if (count($modulesToUpgrade) > 0) {
            $this->module->nrtInfo(
                sprintf(
                    $this->module->l( '%s modules left to upgrade.' ),
                    count($modulesToUpgrade)
                )
            );

            NrtUpgrade::$nrtStepDone = false;
        }
    }

    protected function warmUpModules()
    {
        $MODULES_TO_UPGRADE_LIST = 'modulesToUpgrade.list';

        $list_modules = [
            'nrtthemecustomizer',
            'nrtmegamenu',
            'axoncreator',
            'nrtpopupnewsletter',
            'nrtcompare',
            'nrtwishlist',
            'nrtproductslinknav',
            'nrtaddthisbutton',
            'nrtzoom',
            'nrtvariant',
            'nrtproducttags',
            'nrtsearchbar',
            'nrtsociallogin',
            'nrtsocialbutton',
            'nrtshoppingcart',
            'nrtcustomtab',
            'nrtreviews',
            'nrtcountdown',
            'nrtsizechart',
            'nrtcookielaw',
            'nrtproductvideo',
            'nrtcaptcha',
            'nrtshippingfreeprice',
            'smartblog',
            'smartblogsearch',
            'smartblogcategories',
            'smartblogrecentposts',
            'smartblogarchive',
            'smartbloglatestcomments',
            'smartblogpopularposts',
            'smartblogtag'
        ];

        $list_modules_diff = [];
        $list_modules_to_upgrade = [];

        $modules_list = Module::getModulesOnDisk();

        foreach ($modules_list as $module_list) {
            if (!in_array($module_list->name, $list_modules)) {
                continue;
            }
            if(Module::isInstalled($module_list->name)){
                if (Module::initUpgradeModule($module_list)) {
                    $list_modules_to_upgrade[] = $module_list->name;
                }
            }
        }
        
        $list_modules_to_upgrade = array_reverse(array_merge($list_modules_diff, $list_modules_to_upgrade));

        $this->module->nrtSave($list_modules_to_upgrade, $MODULES_TO_UPGRADE_LIST);
        $total_modules_to_upgrade = count($list_modules_to_upgrade);

        $this->module->nrtInfo(
            sprintf(
                $this->module->l( '%s modules will be upgraded.' ),
                $total_modules_to_upgrade
            )
        );

        NrtUpgrade::$nrtNext = 'upgradeNrtModules';
        NrtUpgrade::$nrtStepDone = false;

        return true;
    }

    public function upgradeThisModule($module_name)
    {
        $module_list = Module::getModulesOnDisk();

        foreach ($module_list as $module) {
            if ($module->name != $module_name || !Module::isInstalled($module_name)) {
                continue;
            }

            if (Module::initUpgradeModule($module)) {
                $legacy_instance = Module::getInstanceByName($module_name);
                $legacy_instance->runUpgradeModule();

                Module::upgradeModuleVersion($module_name, $module->version);

                return !count($legacy_instance->getErrors());
            } elseif (Module::getUpgradeStatus($module_name)) {
                return true;
            }

            return true;
        }

        return true;
    }

    public function upgradeNrtComplete()
    {
        $this->module->nrtInfo( $this->module->l('Upgrade process done. Congratulations! You can now reactivate your shop.') );

        NrtUpgrade::$nrtNext = '';

        $destExtract = str_replace('/', DIRECTORY_SEPARATOR, _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/latest');

        if (file_exists($destExtract) && NrtUpgrade::deleteDirectory($destExtract, false)) {
            $this->module->nrtDebug(
                sprintf(
                    $this->module->l( '%s removed' ),
                    $destExtract
                )
            );
        } elseif (is_dir($destExtract)) {
            $this->module->nrtDebug(
                '<strong>' . sprintf(
                    $this->module->l( 'Please remove %s by FTP' ),
                    $destExtract
                ) . '</strong>'
            );
        }

        $checkLicense = $this->module->checkLicense();

        if($checkLicense){
            Configuration::updateValue('nrt_akira_version_2', $checkLicense['version']);
        }

        $this->module->nrtCleanAll();
    }
}
