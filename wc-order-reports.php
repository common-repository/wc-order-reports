<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://growcommerce.io/
 * @since             1.0.0
 * @package           Wc_Order_Reports
 *
 * @wordpress-plugin
 * Plugin Name:       Order Reports for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/wc-order-reports
 * Description:       Product sales reports for woocommerce store, order overview, oreder status wise performance, sales report download and show options with product item details, advance reporting
 * Version:           1.2.1
 * Requires at least: 4.4.0
 * Tested up to: 6.5.3
 * WC requires at least:    4.0
 * WC tested up to:         8.8.3
 * Author:            GrowCommerce
 * Author URI:        https://growcommerce.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-order-reports
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0
 */
if( ! defined( 'PMW_API_URL' ) ){
  define( 'PMW_API_URL', 'https://growcommerceapi.com/api/' );
}
function WCOR_Check_Pro() {
  if( !defined( 'PRO_Wc_Order_Reports' )  ){
    if( ! defined( 'WCOR_PRODUCT_ID' ) ){
      define( 'WCOR_PRODUCT_ID', '3' );
    }
    define( 'Wc_Order_Reports_VERSION', '1.2.1' );
    if ( ! defined( 'WC_ORDER_REPOSTS_PLUGIN_DIR' ) ) {
        define( 'WC_ORDER_REPOSTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }
    if ( ! defined( 'WC_ORDER_REPOSTS_PLUGIN' ) ) {
        define( 'WC_ORDER_REPOSTS_PLUGIN', basename(__DIR__) );
    }
    if ( ! defined( 'WC_ORDER_REPOSTS_PLUGIN_URL' ) ) {
        define( 'WC_ORDER_REPOSTS_PLUGIN_URL', plugins_url() . '/'.WC_ORDER_REPOSTS_PLUGIN );
    }
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-wc-order-reports-activator.php
     */
    function activate_wc_order_reports() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-order-reports-activator.php';
        Wc_Order_Reports_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-wc-order-reports-deactivator.php
     */
    function deactivate_wc_order_reports() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-order-reports-deactivator.php';
        Wc_Order_Reports_Deactivator::deactivate();
    }

    register_activation_hook( __FILE__, 'activate_Wc_Order_Reports' );
    register_deactivation_hook( __FILE__, 'deactivate_Wc_Order_Reports' );

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-wc-order-reports.php';

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_wc_order_reports() {

        $plugin = new Wc_Order_Reports();
        $plugin->run();

    }
    run_wc_order_reports();
  }
}
add_action( 'init', 'WCOR_Check_Pro' );
add_action('before_woocommerce_init', function(){
  if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
      \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
  }
});