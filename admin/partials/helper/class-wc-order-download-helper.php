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
if(!class_exists('WC_Order_Download_Helper')):
	require_once( 'class-wc-order-helper.php');
	class WC_Order_Download_Helper extends WC_Order_Helper{

	}
endif; // class_exists