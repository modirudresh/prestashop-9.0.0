<?php
/**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

class NrtCustomFonts extends ObjectModel
{
    public $id_nrt_custom_fonts;
    public $title;
    public $font_style;
    public $font_weight;
    public $font_name;
    public $woff;
    public $woff2;
    public $ttf;
    public $svg;
    public $eot;
    public $active = 1;
	
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'nrt_custom_fonts',
        'primary' => 'id_nrt_custom_fonts',
        'fields' => array(
            'title' 			=>  array('type' => self::TYPE_STRING, 	'validate' => 'isName', 'required' => true),
			'font_style' 		=>  array('type' => self::TYPE_STRING, 	'required' => true),
            'font_weight' 		=>  array('type' => self::TYPE_STRING, 	'required' => true),
			'font_name' 		=>  array('type' => self::TYPE_STRING, 	'required' => true),
            'woff' 				=>  array('type' => self::TYPE_STRING),
            'woff2' 			=>  array('type' => self::TYPE_STRING),
            'ttf' 				=>  array('type' => self::TYPE_STRING),
            'svg' 				=>  array('type' => self::TYPE_STRING),
			'eot' 				=>  array('type' => self::TYPE_STRING),
			'active' 			=> 	array('type' => self::TYPE_BOOL,	'validate' => 'isBool'),
        ),
    );
}