<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * Order Reports for WooCommerce
 */

if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}
if(!class_exists('WC_Order_Helper')):
	class WC_Order_Helper {
		protected $currency_symbol;
		public function get_val_from_obj($obj, $key, $prefix = null){
			if(isset($obj[$key]) && $obj[$key]){
				return esc_attr($prefix.$obj[$key]);
			}else{
				return esc_attr($prefix."0");
			}
		}
		public function get_price_plan_link(){
      return "https://growcommerce.io/pricings/?product=order-report-for-woocommerce";
    }
    public function get_support_page_link(){
      return "https://growcommerce.io/support?utm_source=Plugin+WordPress+Screen&utm_medium=Support+Page&m_campaign=Upsell+at+WCOrderReportsr+Plugin";
    }
    public function display_proplan_with_link($btn_text = "PRO" ,$utm = "Pro+Button+Link"){
      if(!wcor_is_pro_version){
        if($btn_text ==""){$btn_text = "PRO";}
        if($utm ==""){$utm = "Pro+Button+Link";}
        echo "<a target='_blank' class='pmw_pro_paln_link' href='".esc_url_raw($this->get_price_plan_link()."&utm_source=Plugin+WordPress+Screen&utm_medium=".$utm."&m_campaign=Upsell+at+Order+Report+Plugin")."'>(".$btn_text.")</a>";
      }
    }
		public function get_wcor_website_link(){
      return "https://growcommerce.io/";
    }
    public function wcor_is_pro_version($api_store = array()){
    	if( ! defined( 'wcor_is_pro_version' ) ){
        define('wcor_is_pro_version', false);
      }
    	return false;
    }
    public function save_wcor_api_store($data){
      update_option("wcor_api_store", serialize( $data ));
    }
    public function get_wcor_api_store(){
      return unserialize( get_option("wcor_api_store"));
    }
    public function get_plan_name($api_store = array()){
      if(isset($api_store->plan_name) && $api_store->plan_name){
        return isset($api_store->plan_name)?$api_store->plan_name:"FREE";
      }else{
        $api_store = (object)$this->get_wcor_api_store();
        return isset($api_store->plan_name)?$api_store->plan_name:"FREE";
      }
    }
    /**
     * Pixels options
     **/
    public function save_wcor_option($wcor_option){
      return update_option("wcor_option", serialize( $wcor_option ));
    }
    public function get_wcor_option(){
      return unserialize( get_option("wcor_option"));
    }
		/**
     * Chart Attributes
     *
     * @since    1.3.0
     */
		public function get_ChartAttributes() {
	    $chart_attr = [	    
				"total_sale"=>[
					"id"=>"total_sale",
					"type"=>"currency",
					"is_chart"=>true,
					"chart_info"=>[
						"chart_type"=>"line",
						"chart_title"=>__("Total sales - Net Sales","wc-order-reports"),
						"chart_id"=>"total_sale_chart",
						"tension"=> "0.4",
						"chart_metrics"=>[
							"0"=>[
								"label"=>__("Total sales","wc-order-reports"),
								"dimensions"=>"order_date",
								"metrics"=>"order_total",
								"borderColor"=> "#878743"
							],
							"1"=>[
								"label"=>__("Net Sales","wc-order-reports"),
								"dimensions"=>"order_date",
								"metrics"=>"line_total",
								"borderColor"=> "#8BBFEC"
							]
						]
					]
				],"net_sale"=>[
					"id"=>"net_sale",
					"type"=>"currency",
				],"total_orders"=>[
					"id"=>"total_orders",
					"type"=>"number",
					"is_chart"=>true,
					"chart_info"=>[
						"chart_type"=>"bar",
						"chart_title"=>__("Total Order","wc-order-reports"),
						"chart_id"=>"total_orders_chart",
						"chart_metrics"=>[
							"0"=>[
								"label"=>__("Total Order","wc-order-reports"),
								"dimensions"=>"order_date",
								"metrics"=>"total_orders"
							]
						]
					]
				],"average_order_value"=>[
					"id"=>"average_order_value",
					"type"=>"currency",
					"is_chart"=>true,
					"chart_info"=>[
						"chart_type"=>"line",
						"chart_title"=>__("Average order value","wc-order-reports"),
						"chart_id"=>"average_order_value_chart",
						"chart_metrics"=>[
							"0"=>[
								"label"=>__("Average order value","wc-order-reports"),
								"dimensions"=>"order_date",
								"metrics"=>"average_order_value"
							]
						]
					]
				]			
	    ];	    
	    return (!empty($chart_attr)) ? json_encode($chart_attr) : "";
	  }
		public function get_woocommerce_currency_symbol(){
			if(!empty($this->user_currency_symbol)){
				return $this->user_currency_symbol;
			}else{
				$code = get_woocommerce_currency();
				return get_woocommerce_currency_symbol($code);
			}
		}

		public function get_order_status_info($obj, $key){
			/*echo "<pre>";
			print_r($obj);
			echo "</pre>";*/
			$obj = isset($obj[$key])?$obj[$key]:"";
			$currency = $this->get_woocommerce_currency_symbol();
			$display  = array('order_total'=>"Total sale", 'line_total'=>"Net sale", 'line_qty'=>"Quantity",'discount_amount'=>'Discount', 'refund_amount'=>'Refund','order_tax' =>"Order TAX",'order_shipping_tax' =>"Shipping TAX",'shipping' => "Shipping");
			$html = "<ul>";
			foreach ($display as $key => $value) {
				if($key != "line_qty"){
					$html .= "<li><label>".$value."</label>PRO</li>";
				}else{
					$html .= "<li><label>".$value."</label>PRO</li>";
				}
			}
			return $html .="</ul>";
		}
	}
endif; // class_exists