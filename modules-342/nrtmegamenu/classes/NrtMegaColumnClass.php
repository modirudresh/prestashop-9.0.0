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

class NrtMegaColumnClass extends ObjectModel
{
	/** @var integer banner id*/
	public $id;
    public $width;
	public $id_nrt_mega_menu;
    public $position;
    public $active;
    public $hide_on_mobile;
    public $title;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table'     => 'nrt_mega_column',
		'primary'   => 'id_nrt_mega_column',
		'fields' => array(
            'id_nrt_mega_menu'         => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'width'       => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'position'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'active'          => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'hide_on_mobile'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'title'           => array('type' => self::TYPE_STRING, 'size' => 255, 'validate' => 'isGenericName'),
		)
	);

    public static function getAll($id_nrt_mega_menu, $active=0)
    {
        $result = Db::getInstance()->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'nrt_mega_column`
            WHERE `id_nrt_mega_menu`='.(int)$id_nrt_mega_menu.($active ? ' AND `active`=1 ' : '').'
            ORDER BY `position`
            ');
        return $result;
    }
    public function copyFromPost()
    {
        /* Classical fields */
        foreach ($_POST AS $key => $value)
            if (property_exists($this, $key) && $key != 'id_'.$this->table && !isset($_FILES[$key]))
                $this->{$key} = $value;
    }
    public function delete()
    {
        if (!$this->id)
            return false;
        if (parent::delete())
        {
            NrtMegaMenuClass::deleteByColumn($this->id);
            return true;
        }
        return false;
    }
    public static function deleteByMenu($id_mega_menu = 0)
    {
        if (!$id_mega_menu)
            return false;
        $res = Db::getInstance()->executeS('
            SELECT `id_nrt_mega_column`
            FROM `'._DB_PREFIX_.'nrt_mega_column`
            WHERE `id_nrt_mega_menu` = '.(int)$id_mega_menu.'
        ');
        $ret = true;
        foreach($res AS $value)
        {
            $column = new NrtMegaColumnClass($value['id_nrt_mega_column']);
            $ret &= $column->delete();
        }
        return $ret;
    }
}