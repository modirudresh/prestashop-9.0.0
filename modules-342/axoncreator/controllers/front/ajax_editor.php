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

 if (!defined('_PS_VERSION_')) {
    exit;
}

use AxonCreator\Wp_Helper;
use AxonCreator\Plugin;

class AxonCreatorAjax_EditorModuleFrontController extends ModuleFrontController {
	
    public function init() {
        parent::init();
		
		Wp_Helper::add_action( 'wp_ajax_axps_get_products_title_by_id', [ $this, 'axps_get_products_title_by_id' ] );
		Wp_Helper::add_action( 'wp_ajax_axps_get_products_by_query', [ $this, 'axps_get_products_by_query' ] );
    }

    public function postProcess() {
		parent::initContent();
				
		define( 'DOING_AJAX', true );
			
		if( Wp_Helper::set_global_var() ){
			Plugin::instance()->on_rest_api_init();

			if ( isset( $_POST['action'] ) ) {
				$action = $_POST['action'];

				Wp_Helper::do_action( 'wp_ajax_' . $action );
			} elseif ( isset( $_GET['action'] ) ) {
				$action = $_GET['action'];

				Wp_Helper::do_action( 'wp_ajax_' . $action );
			}
		}
				
		die( 'exit' );
    }
	
	public function axps_get_products_title_by_id() {
		header('Content-Type: application/json');
		
        $product_ids = Tools::getValue( 'ids' );

        if ( !$product_ids ) {
			die();
        }

        $product_ids_s = []; 

        $product_ids_arr = explode(',', $product_ids);

		foreach ( $product_ids_arr as $product_id_arr ) {
            $arr = explode('_', $product_id_arr);

            if(isset($arr[1])){
                $id_p = $arr[1];
            }else{
                $id_p = $product_id_arr;
            }

            $product_ids_s[] = $id_p;
		}

        $id_lang = (int) Wp_Helper::$id_lang;
        $id_shop = (int) Wp_Helper::$id_shop;

        $sql = 'SELECT p.`id_product`, product_shop.`id_product`,
				    pl.`name`, pl.`link_rewrite`,
					image_shop.`id_image` id_image
				FROM  `' . _DB_PREFIX_ . 'product` p 
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . '
				)
				LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
					ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $id_shop . ')
	  
				WHERE p.id_product IN (' . implode( ',', $product_ids_s ) . ')' . '
				ORDER BY FIELD(product_shop.id_product, ' . implode( ',', $product_ids_s ) . ')';

        if ( !$items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS( $sql ) ) {
            return;
        }
		
		$results   = [];
		
		foreach ( $items as $item ) {
			$results[ 'p_' . (int)$item['id_product'] ] = '(Id: ' . (int)( $item['id_product'] ) . ') ' . $item['name'];
		}

        die( json_encode( $results ) );
    }

    public function axps_get_products_by_query() {
		header('Content-Type: application/json');
		
        $query = Tools::getValue( 'q', false );
		
        if ( !$query or $query == '' or Tools::strlen( $query ) < 1 ) {
            die();
        }
		
        if ( $pos = strpos( $query, ' (ref:' ) ) {
            $query = Tools::substr( $query, 0, $pos );
        }
		
        $excludeIds = Tools::getValue( 'excludeIds', false );
		
        if ( $excludeIds && $excludeIds != 'NaN' ) {
            $excludeIds = implode( ',', array_map( 'intval', explode( ',', $excludeIds ) ) );
        } else {
            $excludeIds = '';
        }
		
        $excludeVirtuals = false;
		
        $exclude_packs = false;
		
        $id_lang = (int) Wp_Helper::$id_lang;
        $id_shop = (int) Wp_Helper::$id_shop;
		
        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, image.`id_image` id_image, il.`legend`, p.`cache_default_attribute`
        FROM `' . _DB_PREFIX_ . 'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'image` image
        ON (image.`id_product` = p.`id_product` AND image.cover=1)
        LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
        WHERE (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\') AND p.`active` = 1' .
            ( !empty( $excludeIds ) ? ' AND p.id_product NOT IN (' . $excludeIds . ') ' : ' ' ) .
            ( $excludeVirtuals ? 'AND NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'product_download` pd WHERE (pd.id_product = p.id_product))' : '') .
            ( $exclude_packs ? 'AND (p.cache_is_pack IS NULL OR p.cache_is_pack = 0)' : '' ) .
            ' GROUP BY p.id_product';

        $items = Db::getInstance()->executeS($sql);

        if ( $items ) {
            $results = [];
			
            foreach ( $items as $item ) {
                $product = [
                    'id' => 'p_' . (int)($item['id_product']),
                    'text' => '(Id: ' . (int)( $item['id_product'] ) . ') ' . $item['name'],
                ];
                array_push( $results, $product );
            }
			
            $results = array_values( $results );
			
            die( json_encode( $results ) );
        }
    }
		
}
