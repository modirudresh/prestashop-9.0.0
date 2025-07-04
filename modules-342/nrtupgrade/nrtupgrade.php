<?php
/*
* 2017 AxonVIZ
*
* NOTICE OF LICENSE
*
*  @author AxonVIZ <axonviz.com@gmail.com>
*  @copyright  2017 axonviz.com
*   
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

use AxonCreator\Wp_Helper;

class NrtUpgrade extends Module implements WidgetInterface
{
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'nrtupgrade';
		$this->version = '1.1.1';
		$this->tab = 'front_office_features';
        $this->author = 'AxonVIZ';
		$this->bootstrap = true;
		$this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Axon - 1 Click Upgrade Akira Theme');
        $this->description = $this->l('Provides an automated method to upgrade your shop to the latest version of Akira Theme.');

        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
    }
	
    public function install()
    {			
        return (!Configuration::get('nrt_akira_version_2') ? Configuration::updateValue('nrt_akira_version_2', '2.5.0') : true) && parent::install() && $this->_createTab();		
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->_deleteTab();
    }
	
	/* ------------------------------------------------------------- */
    /*  CREATE THE TAB MENU
    /* ------------------------------------------------------------- */
    public function _createTab()
    {
            $response = true;

            // First check for parent tab
            $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

            if ($parentTabID) {
                $parentTab = new Tab($parentTabID);
            }
            else {
                $parentTab = new Tab();
                $parentTab->active = 1;
                $parentTab->name = array();
                $parentTab->class_name = "AdminMenuFirst";
                foreach (Language::getLanguages() as $lang) {
                    $parentTab->name[$lang['id_lang']] = "AXON - MODULES";
                }
                $parentTab->id_parent = 0;
                $parentTab->module ='';
                $response &= $parentTab->add();
            }
			// Check for parent tab2
			$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');
			if ($parentTab_2ID) {
				$parentTab_2 = new Tab($parentTab_2ID);
			}
			else {
				$parentTab_2 = new Tab();
				$parentTab_2->active = 1;
				$parentTab_2->name = array();
				$parentTab_2->class_name = "AdminMenuSecond";
				foreach (Language::getLanguages() as $lang) {
					$parentTab_2->name[$lang['id_lang']] = "Modules";
				}
				$parentTab_2->id_parent = $parentTab->id;
				$parentTab_2->module = '';
				$parentTab_2->icon = 'build';
				$response &= $parentTab_2->add();
			}
			// Created tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = "AdminNrtUpgrade";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang){
            $tab->name[$lang['id_lang']] = "- 1 Click Upgrade Akira";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }
	 /* ------------------------------------------------------------- */
    /*  DELETE THE TAB MENU
    /* ------------------------------------------------------------- */
    private function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminNrtUpgrade');
        $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

        $tab = new Tab($id_tab);
        $tab->delete();
		// Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
		$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');
		$tabCount_2 = Tab::getNbTabs($parentTab_2ID);
        if ($tabCount_2 == 0) {
            $parentTab_2 = new Tab($parentTab_2ID);
            $parentTab_2->delete();
        }
        // Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
        $tabCount = Tab::getNbTabs($parentTabID);
        if ($tabCount == 0){
            $parentTab = new Tab($parentTabID);
            $parentTab->delete();
        }

        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin( $this->context->link->getAdminLink('AdminNrtUpgrade') );
    }

    public function renderWidget($hookName = null, array $configuration = []) 
    {

    }

    public function getWidgetVariables($hookName = null, array $configuration = []) 
    {

    }

    ////////////////////////////////////////////////

    const RECOMMENDED_PHP_VERSION = 70103;
    private $phpUpgradeNoticelink;

    public function isPhpUpgradeRequired()
    {
        if (null !== $this->phpUpgradeNoticelink) {
            return $this->phpUpgradeNoticelink;
        }

        return $this->phpUpgradeNoticelink = $this->checkPhpVersionNeedsUpgrade();
    }

    private function checkPhpVersionNeedsUpgrade()
    {
        return PHP_VERSION_ID < self::RECOMMENDED_PHP_VERSION;
    }

    ////////////////////////////////////////////////

    private $rootWritableReport;
    private $rootDirectoryWritable;

    public function getRootWritableReport()
    {
        if (null !== $this->rootWritableReport) {
            return $this->rootWritableReport;
        }

        $this->rootWritableReport = '';
        $this->isRootDirectoryWritable();

        return $this->rootWritableReport;
    }

    public function isRootDirectoryWritable()
    {
        if (null !== $this->rootDirectoryWritable) {
            return $this->rootDirectoryWritable;
        }

        return $this->rootDirectoryWritable = $this->checkRootWritable();
    }

    private function checkRootWritable()
    {
        return  ConfigurationTest::test_dir('/', false, $this->rootWritableReport);
    }

    ////////////////////////////////////////////////////

    private $safeModeDisabled;

    public function isSafeModeDisabled()
    {
        if (null !== $this->safeModeDisabled) {
            return $this->safeModeDisabled;
        }

        return $this->safeModeDisabled = $this->checkSafeModeIsDisabled();
    }

    private function checkSafeModeIsDisabled()
    {
        $safeMode = @ini_get('safe_mode');
        if (empty($safeMode)) {
            $safeMode = '';
        }

        return !in_array(strtolower($safeMode), array(1, 'on'));
    }

    //////////////////////////////////////////////////////

    public function isFOpenEnabledOrIsCUrlInstalled()
    {
        return ConfigurationTest::test_fopen() || ConfigurationTest::test_curl();
    }

    /////////////////////////////////////////////////////

    private $zipEnabled;

    public function isZipEnabled()
    {
        if (null !== $this->zipEnabled) {
            return $this->zipEnabled;
        }

        return $this->zipEnabled = extension_loaded('zip');
    }

    /////////////////////////////////////////////////////

    private $shopDeactivated;

    public function isShopDeactivated()
    {
        if (null !== $this->shopDeactivated) {
            return $this->shopDeactivated;
        }

        return $this->shopDeactivated = $this->checkShopIsDeactivated();
    }

    private function checkShopIsDeactivated()
    {
        return
            !Configuration::get('PS_SHOP_ENABLE')
            || (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], array('127.0.0.1', 'localhost', '[::1]')));
    }

    ///////////////////////////////////////////////////

    private $cacheDisabled;

    public function isCacheDisabled()
    {
        if (null !== $this->cacheDisabled) {
            return $this->cacheDisabled;
        }

        return $this->cacheDisabled = !(defined('_PS_CACHE_ENABLED_') && false != _PS_CACHE_ENABLED_);
    }

    /////////////////////////////////////////////////////

    private $maxExecutionTime;

    public function getMaxExecutionTime()
    {
        if (null !== $this->maxExecutionTime) {
            return $this->maxExecutionTime;
        }

        return $this->maxExecutionTime = $this->checkMaxExecutionTime();
    }

    private function checkMaxExecutionTime()
    {
        return (int) @ini_get('max_execution_time');
    }

    public function checkLicense()
    {	
        if(Module::isEnabled('axoncreator')){
            require_once _PS_MODULE_DIR_   . 'axoncreator/src/Wp_Helper.php';

            $url = 'https://api.axonviz.com/api_upgrade';

            $body_args = [
                'license' => Wp_Helper::api_get_license_key(),
                'item_name' => Wp_Helper::PRODUCT_NAME,
                'url' => _PS_BASE_URL_SSL_,
                'ps_ver' => _PS_VERSION_
            ];

            $response = Wp_Helper::wp_remote_post( $url, [
                'timeout' => 40,
                'body' => $body_args,
            ] );

            if ( Wp_Helper::is_wp_error( $response ) ) {
                return false;
            }

            $response_code = (int) Wp_Helper::wp_remote_retrieve_response_code( $response );

            if ( 200 !== $response_code ) {
                return false;
            }

            $license = json_decode( Wp_Helper::wp_remote_retrieve_body( $response ), true );

            if ( empty( $license['active'] ) ) {
                return false;
            }

            return $license;
        } else{
            return false;
        }
    }

    ///////////////////////////////////////////////////////

    public function isOkForUpgrade()
    {
        return
            $this->isFOpenEnabledOrIsCUrlInstalled()
            && $this->isZipEnabled()
            && $this->isRootDirectoryWritable()
            && $this->isShopDeactivated()
            && $this->isCacheDisabled()
            && Module::isEnabled('axoncreator');
    }

    ////////////////////////////////////////////////////////

    public static $nrtStepDone = true;
    public static $nrtStatus;
    public static $nrtNextParams = [];
    public static $nrtNext;
    public static $nrtLastInfo;
    public static $nrtLink;
    public static $nrtIModule = 0;
    
    public static $nrtNormalMessages = [];
    public static $nrtSevereMessages = [];

    public function getNrtLink()
    {
        if(Module::isEnabled('axoncreator')){
            require_once _PS_MODULE_DIR_   . 'axoncreator/src/Wp_Helper.php';

            $url = 'https://api.axonviz.com/api_upgrade';

            $body_args = [
                'license' => Wp_Helper::api_get_license_key(),
                'item_name' => Wp_Helper::PRODUCT_NAME,
                'url' => _PS_BASE_URL_SSL_,
                'down_load' => true,
                'ps_ver' => _PS_VERSION_
            ];

            $response = Wp_Helper::wp_remote_post( $url, [
                'timeout' => 40,
                'body' => $body_args,
            ] );

            if ( Wp_Helper::is_wp_error( $response ) ) {
                return [
                    'error' => $this->l('Error! An error occurred. Please try again later')
                ];
            }

            $response_code = (int) Wp_Helper::wp_remote_retrieve_response_code( $response );

            if ( 200 !== $response_code ) {
                return [
                    'error' => $this->l('Error 200')
                ];
            }

            $res = json_decode( Wp_Helper::wp_remote_retrieve_body( $response ), true );

            if ( !isset( $res['link'] ) || !$res['link'] ) {
                return [
                    'error' => $this->l('Link not exit')
                ];
            }

            return $res;
        } else{
            return [
                'error' => $this->l('The AxonCreator module has not been installed or enabled')
            ];
        }
    }

    public function downloadLast($dest, $filename = 'theme.zip')
    {
        $destPath = realpath($dest) . DIRECTORY_SEPARATOR . $filename;

        try {
            $filesystem = new Filesystem();
            $filesystem->copy(self::$nrtLink, $destPath);
        } catch (IOException $e) {
            // If the Symfony filesystem failed, we can try with
            // the legacy method which uses curl.
            self::copy(self::$nrtLink, $destPath);
        }

        return is_file($destPath);
    }

    public static function copy($source, $destination, $stream_context = null)
    {
        if (null === $stream_context && !preg_match('/^https?:\/\//', $source)) {
            return @copy($source, $destination);
        }

        $destFile = fopen($destination, 'wb');
        if (!is_resource($destFile)) {
            return false;
        }

        $result = false;

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_FILE, $destFile);
            $result = curl_exec($ch);
            curl_close($ch);
        }

        fclose($destFile);

        return $result;
    }

    public function nrtGetResponseJson()
    {
    	header('Content-Type: application/json');

		die(json_encode(
		    array(
		        'stepDone' => self::$nrtStepDone,
                'next' => self::$nrtNext,
                'nrtIModule' => self::$nrtIModule,

                'status' => $this->nrtGetStatus(),
		        'nextParams' => $this->nrtGetNextParams(),
                'next_desc' => $this->nrtGetLastInfo(),
                'nextQuickInfo' => $this->nrtGetInfos(),
                'nextErrors' => $this->nrtGetErrors(),
		    )
		));
    }

    public function nrtGetLastInfo()
    {
        return self::$nrtLastInfo;
    }

    public function nrtGetStatus()
    {
        return $this->nrtGetNext() == 'error' ? 'error' : 'ok';
    }

    public function nrtGetNext()
    {
        return self::$nrtNext;
    }

    public function nrtGetNextParams()
    {
        return self::$nrtNextParams;
    }

    public function nrtGetInfos()
    {
        return self::$nrtNormalMessages;
    }

    public function nrtGetErrors()
    {
        return self::$nrtSevereMessages;
    }

    public function nrtInfo($message)
    {
        if (empty($message)) {
            return;
        }

        if (!empty(self::$nrtLastInfo)) {
            self::$nrtNormalMessages[] = self::$nrtLastInfo;
        }

        self::$nrtLastInfo = $message;
    } 

    public function nrtDebug($message)
    {
        self::$nrtNormalMessages[] = $message;
    }

    public function nrtError($message)
    {
        self::$nrtSevereMessages[] = $message;
    }

    private function nrtOpen($zipFile, $flags = null)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, $flags) !== true || empty($zip->filename)) {
            $this->nrtError(sprintf($this->l('Unable to open zipFile %s'), $zipFile));

            return false;
        }

        return $zip;
    }

    public function nrtExtract($from_file, $to_dir)
    {
        if (!is_file($from_file)) {
            $this->nrtError(sprintf($this->l('%s is not a file'), $from_file));

            return false;
        }

        if (!file_exists($to_dir)) {
            // ToDo: Use Filesystem from Symfony
            if (!mkdir($to_dir)) {
                $this->nrtError(sprintf($this->l('Unable to create directory %s.'), $to_dir));

                return false;
            }
            chmod($to_dir, 0775);
        }

        $zip = $this->nrtOpen($from_file);
        if ($zip === false) {
            return false;
        }

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            if (!$zip->extractTo($to_dir, array($zip->getNameIndex($i)))) {
                $this->nrtError(
                    sprintf( 
                        $this->l('Could not extract %s from backup, the destination might not be writable.'), 
                        $zip->statIndex($i)['name'] 
                    )
                );
                $zip->close();

                return false;
            }
        }

        $zip->close();
        
        $this->nrtDebug(
            sprintf( 
                $this->l('Content of archive %s is extracted'), 
                $from_file
            )
        );

        return true;
    }

    public static function deleteDirectory($dirname, $delete_self = true)
    {
        $dirname = rtrim($dirname, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (file_exists($dirname)) {
            if ($files = scandir($dirname)) {
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..' && $file != '.svn') {
                        if (is_file($dirname . $file)) {
                            unlink($dirname . $file);
                        } elseif (is_dir($dirname . $file . DIRECTORY_SEPARATOR)) {
                            self::deleteDirectory($dirname . $file . DIRECTORY_SEPARATOR, true);
                        }
                    }
                }
                if ($delete_self && file_exists($dirname)) {
                    if (!rmdir($dirname)) {
                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }

    public function nrtExists($fileName)
    {
        $filesystem = new Filesystem();

        $configPath = _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/';

        return $filesystem->exists($configPath . $fileName);
    }

    public function nrtLoad($fileName = '')
    {
        $configPath = _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/';

        $configFilePath = $configPath . $fileName;
        $config = array();

        if (file_exists($configFilePath)) {
            $config = @unserialize(base64_decode(self::nrt_file_get_contents($configFilePath)));
        }

        return $config;
    }

    public function nrtSave($config, $fileName)
    {
        $configPath = _PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/';

        $filesystem = new Filesystem();

        $configFilePath = $configPath . $fileName;
        try {
            $filesystem->dumpFile($configFilePath, base64_encode(serialize($config)));

            return true;
        } catch (IOException $e) {
            // TODO: $e needs to be logged
            return false;
        }
    }

    public static function nrtGetValue($key, $defaultValue = false)
    {
        if (!isset($key) || empty($key) || !is_string($key)) {
            return false;
        }
        $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

        if (is_string($ret) === true) {
            $ret = urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret)));
        }

        return !is_string($ret) ? $ret : stripslashes($ret);
    }

    public static function nrtStrtolower($str)
    {
        if (is_array($str)) {
            return false;
        }
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'utf-8');
        }

        return strtolower($str);
    }

    public static function nrt_file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
    {
        if (!extension_loaded('openssl') && strpos('https://', $url) === true) {
            $url = str_replace('https', 'http', $url);
        }

        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = @stream_context_create(array('http' => array('timeout' => $curl_timeout, 'header' => "User-Agent:MyAgent/1.0\r\n")));
        }

        $var = @file_get_contents($url, $use_include_path, $stream_context);

        /* PSCSX-3205 buffer output ? */
        if (self::nrtGetValue('ajaxMode') && ob_get_level() && ob_get_length() > 0) {
            ob_clean();
        }

        if ($var) {
            return $var;
        }

        return false;
    }

    public function nrtCleanAll()
    {
        $filesystem = new Filesystem();
        $filesystem->remove(_PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/filesToUpgrade.list');
        $filesystem->remove(_PS_MODULE_DIR_ . 'nrtupgrade/autoupgrade/modulesToUpgrade.list');
    }

    public function nrtRemove($files)
    {
        if ($files instanceof Traversable) {
            $files = iterator_to_array($files, false);
        } elseif (!is_array($files)) {
            $files = array($files);
        }
        $files = array_reverse($files);
        foreach ($files as $file) {
            if (is_link($file)) {
                // See https://bugs.php.net/52176
                if (!@(unlink($file) || '\\' !== DIRECTORY_SEPARATOR || rmdir($file)) && file_exists($file)) {
                    $error = error_get_last();
                    throw new IOException(sprintf('Failed to remove symlink "%s": %s.', $file, $error['message']));
                }
            } elseif (is_dir($file)) {
                $this->nrtRemove(new FilesystemIterator($file, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS));

                if (!@rmdir($file) && file_exists($file)) {
                    $error = error_get_last();
                    throw new IOException(sprintf('Failed to remove directory "%s": %s.', $file, $error['message']));
                }
            } elseif (!@unlink($file) && file_exists($file)) {
                $error = error_get_last();
                throw new IOException(sprintf('Failed to remove file "%s": %s.', $file, $error['message']));
            }
        }
    }
}
