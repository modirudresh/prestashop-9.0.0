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

class AxonCreatorTemplate extends ObjectModel
{
    public $id_axon_creator_template;
    public $id_employee;
    public $title;
    public $type;
    public $content;
	public $page_settings;
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'axon_creator_template',
        'primary' => 'id_axon_creator_template',
        'fields' => array(
            'id_employee' 		=> 	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedId'),
            'title' 			=>  array('type' => self::TYPE_STRING, 	'required' => true),
			'type' 				=>  array('type' => self::TYPE_STRING),
            'content' 			=>  array('type' => self::TYPE_HTML, 	'validate' => 'isJson'),
            'page_settings' 	=>  array('type' => self::TYPE_HTML, 	'validate' => 'isJson'),
            'date_add' 			=> 	array('type' => self::TYPE_DATE,	'validate' => 'isDate'),
        ),
    );
}
