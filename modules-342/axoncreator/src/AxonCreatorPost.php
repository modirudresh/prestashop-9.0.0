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

class AxonCreatorPost extends ObjectModel
{
    public $id_axon_creator_post;
    public $id_employee;
    public $title;
    public $post_type;
    public $active = 1;
    public $date_add;
    public $date_upd;
	// Lang fields
    public $content;
    public $content_autosave;
	
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'axon_creator_post',
        'primary' => 'id_axon_creator_post',
        'multilang' => true,
        'fields' => array(
            'id_employee' 		=> 	array('type' => self::TYPE_INT, 	'validate' => 'isUnsignedId'),
            'title' 			=>  array('type' => self::TYPE_STRING, 	'required' => true),
			'post_type' 		=>  array('type' => self::TYPE_STRING),
            'active' 			=> 	array('type' => self::TYPE_INT, 	'validate' => 'isBool'),
            'content' 			=>  array('type' => self::TYPE_HTML, 	'lang' => true, 'validate' => 'isJson'),
            'content_autosave' 	=>  array('type' => self::TYPE_HTML, 	'lang' => true, 'validate' => 'isJson'),
            'date_add' 			=> 	array('type' => self::TYPE_DATE,	'validate' => 'isDate'),
            'date_upd' 			=> 	array('type' => self::TYPE_DATE,	'validate' => 'isDate'),
        ),
    );
	
    public function __construct($id = null, $id_lang = null)
    {		
        parent::__construct($id, $id_lang);
    }	
	
	public function delete()
	{
		self::delete_revisions();

		AxonCreator\Plugin::instance()->files_manager->on_delete_post( $this->id );
		
		Module::getInstanceByName('axoncreator')->clearElementorCache( $this->id );
		
		return parent::delete();
	}	

    public function update($nullValues = false)
    {
        Module::getInstanceByName('axoncreator')->clearElementorCache( $this->id );
        
        return parent::update($nullValues);
    }
	
    public function delete_revisions() {	
		$res = true;
		
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'axon_creator_revisions` WHERE `id_post` = ' . $this->id;
		
		$revisions = Db::getInstance()->executeS( $sql );
		
		foreach ( $revisions as $revision ) {
			$revi = new AxonCreatorRevisions( $revision['id_axon_creator_revisions'] );
			$res &= $revi->delete();
		}
		
		return $res;
	}
}
