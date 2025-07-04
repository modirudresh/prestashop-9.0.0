<?php

class AdminNrtCustomFontsController extends ModuleAdminController {
	
    public $name;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'NrtCustomFonts';
        $this->table = 'nrt_custom_fonts';

        $this->addRowAction('edit');
        $this->addRowAction('delete');
		
        parent::__construct();
		
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
		
        $this->_orderBy = 'title';
        $this->identifier = 'id_nrt_custom_fonts';
		
        $this->fields_list = array(
            'id_nrt_custom_fonts' => array('title' => $this->module->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'),
			'title' => array('title' => $this->module->l('Title'), 'width' => 'auto'),
            'font_style' => array('title' => $this->module->l('Font Style'), 'width' => 'auto'),
			'font_weight' => array('title' => $this->module->l('Font Weight'), 'width' => 'auto'),
			'ttf' => array('title' => $this->module->l('TTF'), 'width' => 'auto'),
			'woff' => array('title' => $this->module->l('WOFF'), 'width' => 'auto'),
			'woff2' => array('title' => $this->module->l('WOFF2'), 'width' => 'auto'),
			'svg' => array('title' => $this->module->l('SVG'), 'width' => 'auto'),
			'eot' => array('title' => $this->module->l('EOT'), 'width' => 'auto'),
            'active' => array('title' => $this->module->l('Active'), 'align' => 'center', 'search' => false, 'active' => 'status', 'type' => 'bool')
        );
				
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('Delete selected items?'),
            ),
        );

        $this->name = 'AdminNrtCustomFonts';
    }
	
	public function renderForm()
    {		
        $obj = new $this->className((int) Tools::getValue('id_nrt_custom_fonts'));
						
        $this->fields_form = array(
            'legend' => array(
                'title' => isset($obj->id) ? $this->module->l('Edit layout.') : $this->module->l('New layout'),
                'icon' => isset($obj->id) ? 'icon-edit' : 'icon-plus-square',
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_nrt_custom_fonts',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Title'),
                    'name' => 'title',
                    'required' => true,
					'desc' => $this->module->l('Ex: Pacifico')
                ),
				array(
					'type' => 'select',
					'name' => 'font_style',
					'label' => $this->module->l('Font Style'),
					'required' => true,
					'options' => array(
						'query' => [
							['value' => 'normal', 'name' => 'Normal'],
							['value' => 'italic', 'name' => 'Italic'],
							['value' => 'oblique', 'name' => 'Oblique'],
						],
						'id' => 'value',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'name' => 'font_weight',
					'label' => $this->module->l('Font Weight'),
					'required' => true,
					'options' => array(
						'query' => [
							['value' => 'normal', 'name' => 'Normal'],
							['value' => 'bold', 'name' => 'Bold'],
							['value' => '100', 'name' => '100'],
							['value' => '200', 'name' => '200'],
							['value' => '300', 'name' => '300'],
							['value' => '400', 'name' => '400'],
							['value' => '500', 'name' => '500'],
							['value' => '600', 'name' => '600'],
							['value' => '700', 'name' => '700'],
							['value' => '800', 'name' => '800'],
							['value' => '900', 'name' => '900'],
						],
						'id' => 'value',
						'name' => 'name'
					)
				),
				array(
                    'type' => 'file',
                    'label' => $this->module->l('TTF File'),
                    'name' => 'ttf_file',
                ),
				array(
                    'type' => 'text',
                    'label' => $this->module->l('TTF'),
                    'name' => 'ttf',
					'readonly' => true,
					'desc' => $this->module->l('TrueType Fonts, Used for better supporting Safari, Android, iOS')
                ),
				array(
                    'type' => 'file',
                    'label' => $this->module->l('WOFF File'),
                    'name' => 'woff_file',
                ),
				array(
                    'type' => 'text',
                    'label' => $this->module->l('WOFF'),
                    'name' => 'woff',
					'readonly' => true,
					'desc' => $this->module->l('The Web Open Font Format, Used by Modern Browsers')
                ),
				array(
                    'type' => 'file',
                    'label' => $this->module->l('WOFF2 File'),
                    'name' => 'woff2_file',
                ),
				array(
                    'type' => 'text',
                    'label' => $this->module->l('WOFF2'),
                    'name' => 'woff2',
					'readonly' => true,
					'desc' => $this->module->l('The Web Open Font Format 2, Used by Super Modern Browsers')
                ),
				array(
                    'type' => 'file',
                    'label' => $this->module->l('SVG File'),
                    'name' => 'svg_file',
                ),
				array(
                    'type' => 'text',
                    'label' => $this->module->l('SVG'),
                    'name' => 'svg',
					'readonly' => true,
					'desc' => $this->module->l('SVG fonts allow SVG to be used as glyphs when displaying text, Used by Legacy iOS')
                ),
				array(
                    'type' => 'file',
                    'label' => $this->module->l('EOT File'),
                    'name' => 'eot_file',
                ),
				array(
                    'type' => 'text',
                    'label' => $this->module->l('EOT'),
                    'name' => 'eot',
					'readonly' => true,
					'desc' => $this->module->l('Embedded OpenType, Used by IE6-IE9 Browsers')
                ),
				array(
					'type'     => 'switch',
					'label'    => $this->module->l('Status'),
					'name'     => 'active',
					'is_bool'  => true,
					'values'   => array(
						array(
							'id'    => 'active',
							'value' => 1,
							'label' => $this->module->l('Enabled'),
						),
						array(
							'id'    => 'active',
							'value' => 0,
							'label' => $this->module->l('Disabled'),
						),
					),
				),
            ),
            'submit' => array(
                'name' => 'submit' . $this->className,
                'title' => $this->module->l('Save'),
            ),
        );

        return parent::renderForm();
    }

    protected function buildHelper()
    {
        $helper = new HelperForm();

        $helper->module = $this->module;
        $helper->identifier = $this->className;
        $helper->token = Tools::getAdminTokenLite($this->name);
        $helper->languages = $this->_languages;
        $helper->currentIndex = $this->context->link->getAdminLink($this->name);
        $helper->default_form_language = $this->default_form_language;
        $helper->allow_employee_form_lang = $this->allow_employee_form_lang;
        $helper->toolbar_scroll = true;
        $helper->toolbar_btn = $this->initToolbar();

        return $helper;
    }
	
	public function postProcess()
    {
        if (Tools::isSubmit('submit' . $this->className)) {
			$font_name = preg_replace('/\s+/', '',  $_POST['title']);
			
            $folder_name = _PS_MODULE_DIR_ . $this->module->name . '/views/fonts/' . $font_name . '/';
			
            if (!is_dir($folder_name)){
                mkdir($folder_name);
                chmod($folder_name, 0777);
            } else {
                if(!Tools::isSubmit('id_nrt_custom_fonts')){
                    return parent::postProcess();
                }
            }  
			
            if (move_uploaded_file($_FILES['ttf_file']['tmp_name'], $folder_name. $_FILES['ttf_file']['name'])) {
                $_POST['ttf'] = $_FILES['ttf_file']['name'];
            }
            if (move_uploaded_file($_FILES['woff_file']['tmp_name'], $folder_name. $_FILES['woff_file']['name'])) {
                $_POST['woff'] = $_FILES['woff_file']['name'];
            }
            if (move_uploaded_file($_FILES['woff2_file']['tmp_name'], $folder_name. $_FILES['woff2_file']['name'])) {
                $_POST['woff2'] = $_FILES['woff2_file']['name'];
            }
            if (move_uploaded_file($_FILES['svg_file']['tmp_name'], $folder_name. $_FILES['svg_file']['name'])) {
                $_POST['svg'] = $_FILES['svg_file']['name'];
            }
            if (move_uploaded_file($_FILES['eot_file']['tmp_name'], $folder_name. $_FILES['eot_file']['name'])) {
                $_POST['eot'] = $_FILES['eot_file']['name'];
            }
				
            $src = '';  
			
            if(isset($_POST['ttf']) && $_POST['ttf'] != ''){
                $src .= "url('".$_POST['ttf']."') format('ttf'),";
            }
            if(isset($_POST['woff']) && $_POST['woff'] != ''){
                $src .= "url('".$_POST['woff']."') format('woff'),";
            }
            if(isset($_POST['ttf']) && $_POST['woff2'] != ''){
                $src .= "url('".$_POST['woff2']."') format('woff2'),";
            }
            if(isset($_POST['ttf']) && $_POST['svg']!=''){
                $src .= "url('".$_POST['svg']."') format('svg'),";
            }
            if(isset($_POST['ttf']) && $_POST['eot']!=''){
                $src .= "url('".$_POST['eot']."') format('eot'),";
            }
			
			$src = rtrim($src, ',');
			
			if( $src ){
				$src = ' src: ' . $src . ';';
			}
			
            $_POST['font_name'] = $font_name;
			
            $css_file = $folder_name . $font_name . '-' . $_POST['font_weight'] . '.css';
			
            $css = "@font-face {font-family: '" . $font_name . "';" . $src . " font-display: auto; font-weight: " . $_POST['font_weight'] . "; font-style: " . $_POST['font_style'] . ";}";
						
            $fp = fopen($css_file, 'w');
			
            fwrite($fp, $css);
			
            fclose($fp);
			
            chmod($css_file, 0777);
        }
		
        return parent::postProcess();
    }
}