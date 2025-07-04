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

class AxonCreatorRevisions extends ObjectModel
{
    public $id_axon_creator_revisions;
    public $id_post;
    public $id_lang;
    public $id_employee;
    public $content;
	public $page_settings;
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'axon_creator_revisions',
        'primary' => 'id_axon_creator_revisions',
        'fields' => array(
            'id_post' 			=> 	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedId'),
            'id_lang' 			=> 	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedId'),
            'id_employee' 		=> 	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedId'),
            'content' 			=>  array('type' => self::TYPE_HTML, 	'validate' => 'isJson'),
            'page_settings' 	=>  array('type' => self::TYPE_HTML, 	'validate' => 'isJson'),
            'date_add' 			=> 	array('type' => self::TYPE_DATE,	'validate' => 'isDate'),
        ),
    );
}
