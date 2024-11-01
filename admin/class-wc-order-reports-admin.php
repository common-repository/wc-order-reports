<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Wc_Order_Reports
 * @subpackage Wc_Order_Reports/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wc_Order_Reports
 * @subpackage Wc_Order_Reports/admin
 * @author     Ramn <wcorder.reports@gmail.com>
 */
if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}

if(!class_exists('Wc_Order_Reports_Admin')):
class Wc_Order_Reports_Admin{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	protected $wc_order_url;
	protected $screen_id;
	public function __construct( $plugin_name, $version ) {
		$this->includes();
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->wc_order_url = "admin.php?page=".$plugin_name;
		$this->screen_id = isset($_GET['page'])?sanitize_text_field($_GET['page']):"";
	}

	public function includes() {
    if (!class_exists('WC_Order_Header')) {
      require_once(WC_ORDER_REPOSTS_PLUGIN_DIR . 'admin/partials/class-wc-order-reports-header.php');
    }
    if (!class_exists('WC_Order_Footer')) {
      require_once(WC_ORDER_REPOSTS_PLUGIN_DIR . 'admin/partials/class-wc-order-reports-footer.php');
    }   
  }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wc_order_enqueue_styles() {
		if(strpos($this->screen_id, 'wc-order-reports') !== false){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-order-reports-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wc_order_enqueue_scripts() {		
		if(strpos($this->screen_id, 'wc-order-reports') !== false){
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-order-reports-admin.js', array( 'jquery' ), $this->version, false );
		}
	}
	/**
	 * Add Menu for the admin area.	 *
	 * @since    1.0.0
	 */
	public function wc_order_admin_menu(){
		add_menu_page(
      'WC Order Reports', 'WC Order Reports', 'manage_options', "wc-order-reports", array($this, 'show_page'), plugin_dir_url(__FILE__) . 'images/logo-icon.png', 56
  	);
  	add_submenu_page('wc-order-reports', 'Order Overview', 'Order Overview', 'manage_options', 'wc-order-reports' );
  	add_submenu_page('wc-order-reports', 'Download', 'Download', 'manage_options', 'wc-order-reports-download', array($this, 'show_page'));
  	add_submenu_page('wc-order-reports', 'Chart', 'Chart', 'manage_options', 'wc-order-reports-chart', array($this, 'show_page'));
  	add_submenu_page('wc-order-reports', 'Account', 'Account', 'manage_options', 'wc-order-reports-account', array($this, 'show_page'));
  	add_submenu_page('wc-order-reports', 'Support', 'Support', 'manage_options', 'wc-order-reports-support', array($this, 'show_page'));
	}

	/**
	 * Load page for the admin area.	 *
	 * @since    1.0.0
	 */
	public function show_page() {
		do_action('wc_order_header');
		$get_action = "wc_order_reports";
   	if(isset($_GET['page'])) {
      $get_action = str_replace("-", "_", sanitize_text_field($_GET['page']));
    }
     //echo $get_action ='get_'.$get_action.'_page';
    if(method_exists($this, $get_action)){
      $this->$get_action();
    }
    do_action('wc_order_footer');
  }

  /**
	 * Load dashboard page for the admin area.	 *
	 * @since    1.0.0
	 */
  public function wc_order_reports(){
  	require_once( 'partials/class-wc-order-reports.php');
  	new WC_Order_Dashboard();
  }
  /**
	 * Load dashboard page for the admin area.	 *
	 * @since    1.0.0
	 */
  public function wc_order_reports_download(){
  	require_once( 'partials/class-wc-order-reports-download.php');
  	new WC_Order_Download();
  }
  /**
	 * Load dashboard page for the admin area.	 *
	 * @since    1.0.0
	 */
  public function wc_order_reports_chart(){
  	require_once( 'partials/class-wc-order-reports-chart.php');
  	new WC_Order_Chart();
  }

  public function wc_order_reports_account(){
  	require_once( 'partials/pages/class-wc-order-reports-account.php');
  	new WC_Order_Account();
  }
  public function wc_order_reports_support(){
  	require_once( 'partials/pages/class-wc-order-reports-support.php');
  	new WC_Order_Support();
  }
	/**
	 * Add for language translations.	 *
	 * @since    1.0.0
	 */
	public function wc_order_translations(){
		$locale ="";
		if ( function_exists( 'determine_locale' ) ) { // WP5.0+
			$locale = determine_locale();
		} else {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		}
		load_plugin_textdomain( 'wc-order-reports', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
	}
} //Wc_Order_Reports_Admin
endif; // class_exists