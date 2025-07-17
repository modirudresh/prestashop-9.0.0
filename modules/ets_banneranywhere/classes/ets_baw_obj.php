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
class Ets_baw_obj extends ObjectModel 
{
	public $fields;
	public $module;
	public $fields_form;

    public function renderForm()
    {
    	$this->module = new Ets_banneranywhere();
        $this->fields = $this->getListFields();
        $helper = new HelperForm();
        $helper->module = Module::getInstanceByName('ets_banneranywhere');
        $configs = $this->fields['configs'];
        $fields_form = array();
        $fields_form['form'] = $this->fields['form'];               
        if($configs)
        {
            foreach($configs as $key => $config)
            {                
                if(isset($config['type']) && in_array($config['type'],array('sort_order')))
                    continue;
                $confFields = array(
                    'name' => $key,
                    'type' => $config['type'],
                    'class'=>isset($config['class'])?$config['class']:'',
                    'label' => $config['label'],
                    'desc' => isset($config['desc']) ? $config['desc'] : false,
                    'required' => isset($config['required']) && $config['required'] ? true : false,
                    'readonly' => isset($config['readonly']) ? $config['readonly'] : false,
                    'autoload_rte' => isset($config['autoload_rte']) && $config['autoload_rte'] ? true : false,
                    'options' => isset($config['options']) && $config['options'] ? $config['options'] : array(),
                    'suffix' => isset($config['suffix']) && $config['suffix'] ? $config['suffix']  : false,
                    'values' => isset($config['values']) ? $config['values'] : false,
                    'lang' => isset($config['lang']) ? $config['lang'] : false,
                    'showRequired' => isset($config['showRequired']) && $config['showRequired'],
                    'hide_delete' => isset($config['hide_delete']) ? $config['hide_delete'] : false,
                    'placeholder' => isset($config['placeholder']) ? $config['placeholder'] : false,
                    'display_img' => $this->id && isset($config['type']) && $config['type']=='file' && $this->$key!='' && @file_exists(_PS_ETS_BAW_IMG_DIR_.$this->$key) ? _PS_ETS_BAW_IMG_.$this->$key : false,
                    'img_del_link' => $this->id && isset($config['type']) && $config['type']=='file' && $this->$key!='' && @file_exists(_PS_ETS_BAW_IMG_DIR_.$this->$key) ? Context::getContext()->link->getAdminBaseLink('AdminModules').'&configure='.$this->module->name.'&deleteimage='.$key.'&itemId='.(isset($this->id)?$this->id:'0').'&obj='.Tools::ucfirst($fields_form['form']['name']) : false,
                    'min' => isset($config['min']) ? $config['min'] : false,
                    'max' => isset($config['max']) ? $config['max'] : false, 
                    'data_suffix' => isset($config['data_suffix']) ? $config['data_suffix'] :'',
                    'data_suffixs' => isset($config['data_suffixs']) ? $config['data_suffixs'] :'',
                    'multiple' => isset($config['multiple']) ? $config['multiple']: false,
                    'tab' => isset($config['tab']) ? $config['tab']:false,
                    'html_content' => isset($config['html_content']) ? $config['html_content']:'',
                    'form_group_class' => isset($config['form_group_class']) ? $config['form_group_class']:'',
                );
                if(isset($config['col']) && $config['col'])
                    $confFields['col'] = $config['col'];
                if(isset($config['tree']) && $config['tree'])
                {
                    $confFields['tree'] = $config['tree'];
                    if(isset($config['tree']['use_checkbox']) && $config['tree']['use_checkbox'])
                        $confFields['tree']['selected_categories'] = explode(',',$this->$key);
                    else
                        $confFields['tree']['selected_categories'] = array($this->$key);
                }                    
                if(!$confFields['suffix'])
                    unset($confFields['suffix']);                
                $fields_form['form']['input'][] = $confFields;
            }
        }        
        $fields_form['form']['input'][] = array(
            'type' => 'hidden',
            'name' => $fields_form['form']['key'],
        );
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();		
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'save_'.$this->fields['form']['name'];
		$helper->currentIndex = '';
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $fields = array();        
        $languages = Language::getLanguages(false);
        $helper->override_folder = '/';        
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                if($config['type']=='checkbox' || (isset($config['multiple']) && $config['multiple']))
                {
                    if(Tools::isSubmit($key))
                        $fields[$key] = Tools::getValue($key);
                    else
                        $fields[$key] = $this->id ? explode(',',$this->$key) : (isset($config['default']) && $config['default'] ? $config['default'] : array());
                }
                elseif(isset($config['lang']) && $config['lang'])
                {                    
                    foreach($languages as $l)
                    {
                        $temp = $this->$key;
                        if(Tools::isSubmit($key.'_'.$l['id_lang']))
                            $fields[$key][$l['id_lang']] = Tools::getValue($key.'_'.$l['id_lang']);
                        else
                            $fields[$key][$l['id_lang']] = $this->id ? $temp[$l['id_lang']] : (isset($config['default']) && $config['default'] ? $config['default'] : null);
                    }
                }
                elseif(isset($config['type']) && $config['type']=='file_lang')
                {
                    foreach($languages as $l)
                    {
                        $temp = $this->$key;
                        $fields[$key][$l['id_lang']] = $this->id ? $temp[$l['id_lang']] : (isset($config['default']) && $config['default'] ? $config['default'] : null);
                    }
                }
                elseif(!isset($config['tree']))
                {
                    if(Tools::isSubmit($key))
                        $fields[$key] = Tools::getValue($key);
                    else
                    {
                        $fields[$key] = $this->id ? $this->$key : (isset($config['default']) && $config['default'] ? $config['default'] : null);
                        if(isset($config['validate']) && ($config['validate']=='isUnsignedFloat' ||  $config['validate']=='isUnsignedInt') && $fields[$key]==0)
                            $fields[$key] =''; 
                        if(isset($config['validate']) && $config['validate']=='isDate' && $fields[$key]=='0000-00-00 00:00:00')
                            $fields[$key] =''; 
                    }
                     
                }    
                                        
            }
        }
        $fields[$fields_form['form']['key']] = $this->id;
        $helper->tpl_vars = array(
			'base_url' => Context::getContext()->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
			'fields_value' => $fields,
			'languages' => Context::getContext()->controller->getLanguages(),
			'id_language' => Context::getContext()->language->id, 
            'key_name' => 'id_'.$fields_form['form']['name'],
            'item_id' => $this->id,  
            'list_item' => true,
            'image_baseurl' => _PS_ETS_BAW_IMG_, 
            'configTabs'=>  isset($this->fields['tabs']) ?  $this->fields['tabs']:false, 
            'name_controller' =>  isset($this->fields['name_controller']) ?  $this->fields['name_controller']:'', 
            'link'=> Context::getContext()->link,           
        );        
        return $helper->generateForm(array($fields_form));	
    }
    public function saveData()
    {
        $this->fields = $this->getListFields();
        $errors = array();
        $success = array();
        $languages = Language::getLanguages(false);
        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        $parent= isset($this->fields['form']['parent'])? $this->fields['form']['parent']:'1';
        $configs = $this->fields['configs'];  
        $files = array();  
        $old_files = array(); 
        if(method_exists($this,'validateCustomField'))
            $this->validateCustomField($errors);  
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                $value_key = Tools::getValue($key);
                if($config['type']=='sort_order' || $config['type']=='html')
                    continue;
                if(isset($config['lang']) && $config['lang'])
                {
                    $key_value_lang_default = trim(Tools::getValue($key.'_'.$id_lang_default));
                    if(isset($config['required']) && $config['required'] && $config['type']!='switch' && $key_value_lang_default == '')
                    {
                        $errors[] = sprintf($this->l('%s is required','ets_baw_obj'),$config['label']);
                    }
                    elseif($key_value_lang_default!='' && !is_array($key_value_lang_default) && isset($config['validate']) && method_exists('Validate',$config['validate']))
                    {
                        $validate = $config['validate'];
                        if(!Validate::$validate(trim($key_value_lang_default)))
                            $errors[] = sprintf($this->l('%s is not valid','ets_baw_obj'),$config['label']);
                        unset($validate);
                    }
                    elseif(!Validate::isCleanHtml($key_value_lang_default))
                        $errors[] = sprintf($this->l('%s is not valid','ets_baw_obj'),$config['label']);
                    else
                    {
                        foreach($languages as $language)
                        {
                            if($language['id_lang']!=$id_lang_default)
                            {
                                $value_lang = trim(Tools::getValue($key.'_'.$language['id_lang']));
                                if($value_lang!='' && !is_array($value_lang) && isset($config['validate']) && method_exists('Validate',$config['validate']))
                                {
                                    $validate = $config['validate'];
                                    if(!Validate::$validate(trim($value_lang)))
                                        $errors[] = sprintf($this->l('%s is not valid in %s','ets_baw_obj'),$config['label'],$language['iso_code']);
                                    unset($validate);
                                }
                                elseif(!Validate::isCleanHtml($value_lang))
                                    $errors[] = sprintf($this->l('%s is not valid in %s','ets_baw_obj'),$config['label'],$language['iso_code']);
                            }
                        }
                    }                    
                }
                elseif($config['type']=='file_lang')
                {
                    $files[$key] = array();
                    foreach($languages as $l)
                    {
                        $name = $key.'_'.$l['id_lang'];
                        if(isset($_FILES[$name]['tmp_name']) && isset($_FILES[$name]['name']) && $_FILES[$name]['name'])
                        {
                            $_FILES[$name]['name'] = str_replace(array(' ','(',')','!','@','#','+'),'_',$_FILES[$name]['name']);
                            $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$name]['name'], '.'), 1));
                            $imageName = @file_exists(_PS_ETS_BAW_IMG_DIR_.Tools::strtolower($_FILES[$name]['name'])) ? Tools::passwdGen().'-'.Tools::strtolower($_FILES[$name]['name']) : Tools::strtolower($_FILES[$name]['name']);
                            $fileName = _PS_ETS_BAW_IMG_DIR_.$imageName;  
                            $max_file_size = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')*1024*1024;
                            if(!Validate::isFileName($_FILES[$name]['name']))
                                $errors[] = sprintf($this->l('%s is not valid','ets_baw_obj'),$config['label']);
                            elseif($_FILES[$name]['size'] > $max_file_size)
                                $errors[] = sprintf($this->l('%s file is too large','ets_baw_obj'),$config['label']);
                            elseif(file_exists($fileName))
                            {
                                $errors[] =sprintf($this->l('%s file already existed','ets_baw_obj'),$config['label']);
                            }
                            else
                            {                                    
                    			$imagesize = @getimagesize($_FILES[$name]['tmp_name']);                                    
                                if (!$errors && isset($_FILES[$name]) &&				
                    				!empty($_FILES[$name]['tmp_name']) &&
                    				!empty($imagesize) &&
                    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
                    			)
                    			{
                    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
                    				if ($error = ImageManager::validateUpload($_FILES[$name]))
                    					$errors[] = $error;
                    				elseif (!$temp_name || !move_uploaded_file($_FILES[$name]['tmp_name'], $temp_name))
                    					$errors[] = sprintf($this->l('%s cannot upload in %s','ets_baw_obj'),$config['label'],$l['iso_code']);
                    				elseif (!ImageManager::resize($temp_name, $fileName, null, null, $type))
                    					$errors[] = printf($this->l('%s An error occurred during the image upload process in %s','ets_baw_obj'),$config['label'],$l['iso_code']);
                    				$temp_name;
                                    if(!$errors)
                                    {
                                        $files[$key][$l['id_lang']] = $imageName;  
                                    }
                                }
                                else
                                    $errors[] = sprintf($this->l('%s file in %s is not in the correct format, accepted formats: jpg, gif, jpeg, png.','ets_baw_obj'),$config['label'],$l['iso_code']);
                            }
                        }
                    }
                }
                else
                {
                    if(isset($config['required']) && $config['required'] && isset($config['type']) && $config['type']=='file')
                    {
                        if($this->$key=='' && !isset($_FILES[$key]['size']))
                            $errors[] = sprintf($this->l('%s is required','ets_baw_obj'),$config['label']);
                        elseif(isset($_FILES[$key]['size']))
                        {
                            $fileSize = round((int)$_FILES[$key]['size'] / (1024 * 1024));
                			if($fileSize > 100)
                                $errors[] = sprintf($this->l('%s file is too large','ets_baw_obj'),$config['label']);
                        }   
                    }
                    else
                    {
                        if(isset($config['required']) && $config['required'] && $config['type']!='switch' && !is_array($value_key) && trim($value_key) == '')
                        {
                            $errors[] = sprintf($this->l('%s is required','ets_baw_obj'),$config['label']);
                        }
                        elseif($value_key!='' && !is_array($value_key) && isset($config['validate']) && method_exists('Validate',$config['validate']))
                        {
                            $validate = $config['validate'];
                            if(!Validate::$validate(trim($value_key)))
                                $errors[] = sprintf($this->l('%s is not valid','ets_baw_obj'),$config['label']);
                            unset($validate);
                        }
                        elseif($value_key!='' && !is_array($value_key)  && !Validate::isCleanHtml(trim($value_key)))
                        {
                            $errors[] = sprintf($this->l('%s is required','ets_baw_obj'),$config['label']);
                        } 
                    }                          
                }                    
            }
        }            
        if(!$errors)
        {            
            if($configs)
            {
                foreach($configs as $key => $config)
                {
                    if( $config['type']=='html')
                        continue;
                    $value_key = Tools::getValue($key);
                    if(isset($config['lang']) && $config['lang'])
                    {
                        $valules = array();
                        $key_value_lang_default = trim(Tools::getValue($key.'_'.$id_lang_default));
                        foreach($languages as $lang)
                        {
                            $key_value_lang = trim(Tools::getValue($key.'_'.$lang['id_lang']));
                            if($config['type']=='switch')                                                           
                                $valules[$lang['id_lang']] = (int)$key_value_lang ? 1 : 0;                                
                            elseif(Validate::isCleanHtml($key_value_lang))
                                $valules[$lang['id_lang']] = $key_value_lang ? : (Validate::isCleanHtml($key_value_lang_default) ? $key_value_lang_default:'');
                        }
                        $this->$key = $valules;
                    }
                    elseif($config['type']=='file_lang')
                    {
                        if(isset($files[$key]))
                        {
                            $valules = array();
                            $old_values = is_array($this->$key) ? $this->$key : array();
                            $old_files[$key] = array();
                            foreach($languages as $lang)
                            {
                                if(isset($files[$key][$lang['id_lang']]) && $files[$key][$lang['id_lang']])
                                {
                                    $valules[$lang['id_lang']] = $files[$key][$lang['id_lang']];
                                    if(isset($old_values[$lang['id_lang']]) && $old_values[$lang['id_lang']])
                                        $old_files[$key][$lang['id_lang']] = $old_values[$lang['id_lang']];
                                }
                                elseif((!isset($old_values[$lang['id_lang']]) || !$old_values[$lang['id_lang']]) && isset($files[$key][$id_lang_default]) && $files[$key][$id_lang_default])
                                    $valules[$lang['id_lang']] = $files[$key][$id_lang_default];
                                else
                                    $valules[$lang['id_lang']] = isset($old_values[$lang['id_lang']]) ? $old_values[$lang['id_lang']] : '';
                            }
                            $this->$key = $valules;
                        }
                    }
                    elseif($config['type']=='switch')
                    {                           
                        $this->$key = (int)$value_key ? 1 : 0;                                                      
                    }
                    elseif($config['type']=='categories' && is_array($value_key) && isset($config['tree']['use_checkbox']) && $config['tree']['use_checkbox'] || $config['type']=='checkbox')
                    {
                        if($value_key)
                        {
                            if(in_array('all',$value_key))
                                $this->$key = 'all';
                            else
                                $this->$key = implode(',',$value_key); 
                        }
                        else
                            $this->$key='';
                    }                                                  
                    elseif(Validate::isCleanHtml($value_key))
                        $this->$key = trim($value_key);   
                    }
                }
        }     
        if (!count($errors))
        { 
            $this->id_shop = Context::getContext()->shop->id;
            if($this->id && $this->update() || !$this->id && $this->add(true,true))
            {
                $success[] = $this->l('Saved successfully','ets_baw_obj');
                if($old_files)
                {
                    foreach($old_files as $key_file => $file)
                    {
                        if($file)
                        {
                            if(is_array($file))
                            {
                                foreach($file as $f)
                                {
                                    if(!in_array($f,$this->$key_file))
                                        @unlink(_PS_ETS_BAW_IMG_DIR_.$f);
                                }
                            }
                            else
                                @unlink(_PS_ETS_BAW_IMG_DIR_.$file);
                        }
                    }
                }
            }                
            else
            {
                if($files)
                {
                    foreach($files as $key_file => $file)
                    {
                        if($file)
                        {
                            if(is_array($file))
                            {
                                foreach($file as $f)
                                {
                                    @unlink(_PS_ETS_BAW_IMG_DIR_.$f); 
                                }
                            }
                            else
                                @unlink(_PS_ETS_BAW_IMG_DIR_.$file);
                        }
                    }
                }
                $errors[] = $this->l('Saving failed','ets_baw_obj');
            }
        }
        return array('errors' => $errors, 'success' => $success);  
    }
    public function maxVal() // $key,$group = false, $groupval=0
    {
        return true;
    }
	public function l($string,$file_name='')
	{
		return Translate::getModuleTranslation('ets_banneranywhere', $string, $file_name ? : pathinfo(__FILE__, PATHINFO_FILENAME));
	}

	public function getListFields()
	{
		$configs = array(
			'title' => array(
				'type'=>'text',
				'lang'=>true,
				'label'=> $this->l('Title'),
				'validate'=>'isCleanHtml',
			),
			'image' => array(
				'type' => 'file_lang',
				'label' => $this->l('Image'),
				'validate'=>'isCleanHtml',
				'desc' => sprintf($this->l('Accepted format: jpg, gif, jpeg, png. Limit %dMB'),Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'))
			),
			'image_alt' => array(
				'type'=> 'text',
				'label' => $this->l('Image alt text'),
				'lang'=>true,
				'autoload_rte'=>true,
				'validate'=>'isCleanHtml',
			),
			'image_url' => array(
				'type'=> 'text',
				'label' => $this->l('Image link direct'),
				'lang'=>true,
				'autoload_rte'=>true,
				'validate'=>'isCleanHtml',
				'desc' => sprintf($this->l('Image links must start with http:// or https://')),
			),
			'content_before_image' => array(
				'type'=>'textarea',
				'label' => $this->l('Content appears before the image'),
				'lang'=>true,
				'autoload_rte'=>true,
				'validate'=>'isCleanHtml',
			),
			'content_after_image' => array(
				'type'=>'textarea',
				'label' => $this->l('Content appears after the image'),
				'lang'=>true,
				'autoload_rte'=>true,
				'validate'=>'isCleanHtml',
			),
			'position' => array(
				'type' => 'checkbox',
				'label' => $this->l('Display positions'),
				'values' => array(
					'query' => $this->getPositions(),
					'id' => 'id_option',
					'name' => 'name'
				),
				'validate'=>'isCleanHtml',
			),
			'active' => array(
				'type'=>'switch',
				'label'=>$this->l('Active'),
				'default' =>1,
				'values' => array(
					array(
						'label' => $this->l('Yes'),
						'id' => 'active_on',
						'value' => 1,
					),
					array(
						'label' => $this->l('No'),
						'id' => 'active_off',
						'value' => 0,
					)
				),
			),
		);
		return array(
			'form' => array(
				'legend' => array(
					'title' => $this->id ? $this->l('Edit banner') : $this->l('Add banner') ,
				),
				'input' => array(),
				'submit' => array(
					'title' => $this->l('Save'),
				),
				'buttons'=> array(
					array(
						'title' => $this->l('Cancel'),
						//'type' => 'submit',
						'class' => 'pull-left',
						//'name' => 'btncancel',
						'icon' => 'process-icon-cancel',
						'href' => Context::getContext()->link->getAdminLink('AdminModules').'&configure=ets_banneranywhere'
					)
				),
				'name' => 'baw_banner',
				'key' => 'id_ets_baw_banner',
			),
			'configs' =>$configs,
		);
	}
	public function getPositions()
	{
		$positions =  array(
			'displayNav1' => array(
				'id_option' => 'displayNav1',
				'name'=> $this->l('[highlight]Header:[end_highlight] On the top navigation bar'),
			),
			'displayProductListHeaderBefore' => array(
				'id_option' => 'displayProductListHeaderBefore',
				'name'=> $this->l('[highlight]Category page:[end_highlight] On top of the header of product listing page'),
				'class'=> 'display_hook',
			),
			'displayFooterAfter' => array(
				'id_option' => 'displayFooterAfter',
				'name' => $this->l('[highlight]Footer:[end_highlight] On the bottom of Footer section'),
			),
			'displayFooterCategory' => array(
				'id_option' => 'displayFooterCategory',
				'name'=> $this->l('[highlight]Category page:[end_highlight] On the bottom of product category page'),
				'class'=> 'display_hook',
			),
			'displayFooterBefore' => array(
				'id_option' => 'displayFooterBefore',
				'name' => $this->l('[highlight]Footer:[end_highlight] On top of Footer section'),
			),
			'displayProductListHeaderAfter' => array(
				'id_option' => 'displayProductListHeaderAfter',
				'name'=> $this->l('[highlight]Category page:[end_highlight] Under the header of product listing page'),
				'class'=> 'display_hook',
			),
			'displayRightColumnBefore' => array(
				'id_option' => 'displayBeforeRightColumn',
				'name' => $this->l('[highlight]Right column:[end_highlight] On the top of the right column')
			),
			'displayAfterProductThumbs' => array(
				'id_option' => 'displayAfterProductThumbs',
				'name' => $this->l('[highlight]Product page:[end_highlight] Under the product thumbnail images on product detail page'),
				'class'=> 'display_hook',
			),
			'displayRightColumn' => array(
				'id_option' => 'displayRightColumn',
				'name' => $this->l('[highlight]Right column:[end_highlight] On the bottom of the right column')
			),
			'displayProductCommentsListHeaderBefore' => array(
				'id_option' => 'displayProductCommentsListHeaderBefore',
				'name' => sprintf($this->l('[highlight]Product page:[end_highlight] On top of %sProduct Comments%s block on product detail page'),'"','"'),
				'class'=> 'display_hook',
			),
			'displayLeftColumnBefore' => array(
				'id_option' => 'displayBeforeLeftColumn',
				'name' => $this->l('[highlight]Left column:[end_highlight] On the top of the left column')
			),
			'displayProductVariantsAfter' => array(
				'id_option' => 'displayProductVariantsAfter',
				'name' => $this->l('[highlight]Product page:[end_highlight] On the bottom of the product combination block'),
				'class'=> 'display_hook',
			),
			'displayLeftColumn' => array(
				'id_option' => 'displayLeftColumn',
				'name' => $this->l('[highlight]Left column:[end_highlight] On the bottom of the left column')
			),
			'displayProductAdditionalInfo' => array(
				'id_option' => 'displayProductAdditionalInfo',
				'name' => sprintf($this->l('[highlight]Product page:[end_highlight] On bottom of %sSocial sharing%s block on product detail page'),'"','"'),
				'class'=> 'display_hook',
			),
			'displayCartGridBodyBefore1' => array(
				'id_option' => 'displayCartGridBodyBefore1',
				'name' => sprintf($this->l('[highlight]Cart page:[end_highlight] On the top of shopping cart detail on %sShopping cart%s page'),'"','"'),
			),
			'displayFooterProduct' => array(
				'id_option' => 'displayFooterProduct',
				'name' => $this->l('[highlight]Product page:[end_highlight] Under the product description section'),
				'class'=> 'display_hook',
			),
			'displayShoppingCartFooter' => array(
				'id_option' => 'displayShoppingCartFooter',
				'name' => $this->l('[highlight]Cart page:[end_highlight] On the bottom of shopping cart detail'),
			),
			'displayProductVariantsBefore' => array(
				'id_option' => 'displayProductVariantsBefore',
				'name' => $this->l('[highlight]Product page:[end_highlight] On top of the product combination block'),
				'class'=> 'display_hook',
			),
			'displayCartGridBodyBefore2' => array(
				'id_option' => 'displayCartGridBodyBefore2',
				'name' => $this->l('[highlight]Checkout page:[end_highlight] On top of the checkout page')
			),
			'displayReassurance' => array(
				'id_option' => 'displayReassurance',
				'name' => sprintf($this->l('[highlight]Product page:[end_highlight] Under the %sCustomer reassurance%s block'),'"','"'),
				'class'=> 'display_hook',
			),
			'displayCartGridBodyAfter' => array(
				'id_option' => 'displayCartGridBodyAfter',
				'name' => $this->l('[highlight]Checkout page:[end_highlight] On the bottom of the checkout page')
			),
			'displayBanner' => array(
				'id_option' => 'displayBanner',
				'name' => $this->l('[highlight]On top of the homepage banner[end_highlight]')
			),
			'displayHome' => array(
				'id_option' => 'displayHome',
				'name' => $this->l('[highlight]Home page[end_highlight]')
			),


		);
		$version = (string)_PS_VERSION_;
		$version = (string)Tools::substr($version, 0, 7);
		$version = str_replace('.', '', $version);
		$version = (int)$version;
		if($version <= 0)
		{
			unset($positions['displayProductListHeaderBefore']);
			unset($positions['displayProductListHeaderAfter']);
		}
		if($version<1770)
		{
			unset($positions['displayFooterCategory']);
		}
		if($version < 1700)
		{
			unset($positions['displayNav1']);
			unset($positions['displayFooterBefore']);
			unset($positions['displayFooterAfter']);
			unset($positions['displayCartGridBodyBefore1']);
			unset($positions['displayReassurance']);
		}
		if($version < 1710)
		{
			unset($positions['displayAfterProductThumbs']);
		}
		if($version < 1760)
		{
//            unset($positions['displayProductActions']);
			unset($positions['displayProductCommentsListHeaderBefore']);
		}
		return $positions;
	}
}