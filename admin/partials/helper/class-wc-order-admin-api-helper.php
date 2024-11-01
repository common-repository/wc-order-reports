<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    
 * @package    PMW_Helper
 * 
 */
if(!defined('ABSPATH')){
  exit; // Exit if accessed directly
}
if(!class_exists('WCOR_AdminAPIHelper')):
  class WCOR_AdminAPIHelper{
    protected $WC_Order_Helper;
    public function __construct() {
      if(class_exists( 'WC_Order_Helper' )){
        $this->WC_Order_Helper = new WC_Order_Helper();
      }
      //$this->includes();
      //add_action('admin_init',array($this, 'init'));
    }
    public function includes(){
    }
    public function init(){      
    }
    /**
     * API call function
     **/
    public function pmw_api_call( string $end_point, array $args ){
      try {
        if( !empty($args) && $end_point ){ 
          $url = PMW_API_URL.$end_point;
          $args['timeout']= "1000";
          $request = wp_remote_post(esc_url_raw($url), $args);
          return json_decode(wp_remote_retrieve_body($request));
        }
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }

    public function get_product_data(array $wcor_option= array(), $product_status = "1"){
      $product_data = array();
      if(empty($wcor_option) && class_exists( 'WC_Order_Helper' ) ){
        $wcor_option = $this->WC_Order_Helper->get_wcor_option();
      }else if(!class_exists( 'WC_Order_Helper' )){
        $wcor_option =  unserialize( get_option("pmw_pixels_option"));
      }
      return array(
        "settings" => $wcor_option,
        "status" => $product_status,
        "version" => Wc_Order_Reports_VERSION,
        "domain" => esc_url_raw(get_site_url()),
        "update_date" => date("Y-m-d")
      );
    }

    public function save_product_store( $wcor_option = array(), $product_status = "1"){
      if(empty($wcor_option) && class_exists( 'WC_Order_Helper' ) ){
        $wcor_option = $this->WC_Order_Helper->get_wcor_option();
      }else if(!class_exists( 'WC_Order_Helper' )){
        $wcor_option =  unserialize( get_option("wcor_option"));
      }
      if(empty($wcor_option)){
        return;
      }

      //$current_user = wp_get_current_user();
      $country_data = get_option('woocommerce_default_country');
      $country_data_array = array();
      if($country_data){
        $country_data_array = explode(":", $country_data);
      }
      $store_data = $store_data = array(
        'store_info' => array(
          'country_code' => (isset($country_data_array[0]))?$country_data_array[0]:$country_data,
          'state_code' => (isset($country_data_array[1]))?$country_data_array[1]:"",
          'is_multisite' => is_multisite(),
          'currency_code' => get_option('woocommerce_currency'),
          'language_code' => get_locale()
        )
      );
      if(isset($wcor_option["privecy_policy"]["is_theme_plugin_list"]) && $wcor_option["privecy_policy"]["is_theme_plugin_list"]){
        //$store_data['active_plugins'] = get_plugins();
        $store_data['active_plugins'] = get_option('active_plugins');
      }

      $data = array(
        "email" => sanitize_email($wcor_option['user']['email_id']),
        //"first_name" => "",
        //"last_name" => "",
        "website" => esc_url_raw(get_site_url()),            
        "product_id" => ( defined( 'WCOR_PRODUCT_ID' ) )?WCOR_PRODUCT_ID:3,
        "store_data" => $store_data,
        "product_data" => $this->get_product_data($wcor_option, $product_status)
      );

      $args = array(
        'timeout' => 10000,
        'headers' => array(
          'Authorization' => "Bearer PMDZCXJL==",
          'Content-Type' => 'application/json'
        ),
      'body' => wp_json_encode($data)
      );
      return $this->pmw_api_call("store/save", $args);
    }
    public function update_store_api_data(){
      $store_id = $this->WC_Order_Helper->get_store_id();
      if($store_id != ""){
        $data = array(
          "store_id" => sanitize_text_field($store_id),
          "website" => esc_url_raw(get_site_url())
        );
        $args = array(
          'timeout' => 10000,
          'headers' => array(
            'Authorization' => "Bearer PMDZCXJL==",
            'Content-Type' => 'application/json'
          ),
        'body' => wp_json_encode($data)
        );
        $api_rs = $this->pmw_api_call("store/get", $args);
        if (isset($api_rs->error) && $api_rs->error == '' ) {
          $this->WC_Order_Helper->save_wcor_api_store((array)$api_rs->data);
        }
      }
    }
  }
endif;