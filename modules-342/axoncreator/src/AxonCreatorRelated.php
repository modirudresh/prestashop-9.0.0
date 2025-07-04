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


class AxonCreatorRelated extends ObjectModel
{
    public $id_axon_creator_related;
    public $id_post;
    public $post_type;
    public $key_related;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'axon_creator_related',
        'primary' => 'id_axon_creator_related',
        'fields' => array(
            'id_post' 		=>	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedId'),
			'post_type' 	=>  array('type' => self::TYPE_STRING, 	'required' => true),
            'key_related' 	=>  array('type' => self::TYPE_STRING, 	'required' => true),
        ),
    );
	
    public function __construct( $id = null, $id_lang = null, $id_shop = null )
    {		
        parent::__construct( $id, $id_lang, $id_shop );
		
		Shop::addTableAssociation( 'axon_creator_related', array('type' => 'shop') );
    }		
}
