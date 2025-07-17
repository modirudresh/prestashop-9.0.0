<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }

class Ets_baw_defines
{
    public static $instance;
    public $name = 'ets_banneranywhere';
    public $context;
    public $smarty;
    public function __construct()
    {
        $this->context = Context::getContext();
        if (is_object($this->context->smarty)) {
            $this->smarty = $this->context->smarty;
        }
    }
    public static function getInstance()
    {
        if (!(isset(self::$instance)) || !self::$instance) {
            self::$instance = new Ets_baw_defines();
        }
        return self::$instance;
    }
    public function l($string)
    {
        return Translate::getModuleTranslation('ets_banneranywhere', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    public function installDb(){
        if(!is_dir(_PS_ETS_BAW_IMG_DIR_))
        {
            @mkdir(_PS_ETS_BAW_IMG_DIR_,0755,true);
            @copy(dirname(__FILE__).'/index.php', _PS_ETS_BAW_IMG_DIR_. 'index.php');
        }
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_baw_banner` ( 
        `id_ets_baw_banner` INT(11) NOT NULL AUTO_INCREMENT , 
        `active` TINYINT(1) NULL , 
        PRIMARY KEY (`id_ets_baw_banner`), 
        INDEX (`active`)) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_baw_banner_lang` ( 
        `id_ets_baw_banner` INT(11) NOT NULL , 
        `id_lang` INT(11) NOT NULL , 
        `title` VARCHAR(200) NULL , 
        `image` VARCHAR(200) NULL , 
        `image_alt` VARCHAR(100) NULL , 
        `image_url` VARCHAR(100) NULL , 
        `content_before_image` TEXT NULL , 
        `content_after_image` TEXT NULL , 
        PRIMARY KEY (`id_ets_baw_banner`, `id_lang`)) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_baw_banner_position` ( 
        `id_ets_baw_banner` INT(11) NOT NULL, 
        `position` VARCHAR(50) NOT NULL , 
        `sort` INT(11) NULL , 
        PRIMARY KEY (`id_ets_baw_banner`,`position`), 
        INDEX (`sort`)) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
        return true;
    }
    public function unInstallDb()
    {
        $tables = array(
            'ets_baw_banner',
            'ets_baw_banner_lang',
            'ets_baw_banner_position'
        );
        if($tables)
        {
            foreach($tables as $table)
               Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . pSQL($table).'`'); 
        }
        return true;
    }
}