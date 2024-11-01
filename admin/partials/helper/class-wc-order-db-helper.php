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
if(!class_exists('WC_Order_DB_Helper')):
	class WC_Order_DB_Helper {
		public function __construct(  ) {
		}
		public function WC_is_woocommerce_custom_orders_table_enabled(){
			$is_enabled = get_option('woocommerce_custom_orders_table_enabled');
			if($is_enabled == "yes"){
				return true;
			}else{
				return false;
			}
		}
		public function get_sales_report_analysis($start_date, $end_date){ 
			global $wpdb;
		  $posts_table = $wpdb->prefix."posts";
		  $postmeta_table = $wpdb->prefix."postmeta";
		  $woocommerce_order_itemmeta = $wpdb->prefix."woocommerce_order_itemmeta";
		  $woocommerce_order_items = $wpdb->prefix."woocommerce_order_items";
		  $wc_orders_table = $wpdb->prefix."wc_orders";
		  $wc_order_operational_data = $wpdb->prefix."wc_order_operational_data";
		  $wc_order_product_lookup = $wpdb->prefix."wc_order_product_lookup";
		  $results = [];
		  $sub_results_1 = [];
		  $sub_results_2 = [];
		  if($this->WC_is_woocommerce_custom_orders_table_enabled()){
		  	//ROUND(SUM(wc_order_product_lookup.product_gross_revenue),2) as total,
		  	//ROUND(SUM(wc_order_product_lookup.tax_amount),2) as line_tax,
		  	$sql =  $wpdb->prepare("SELECT DATE_FORMAT(w_c_o.date_created_gmt, '%Y-%b-%e') as order_date, w_c_o.date_created_gmt as order_date_t, 
			      SUM(wc_order_product_lookup.product_qty) as line_qty,
			      ROUND(SUM(wc_order_product_lookup.product_net_revenue),2) as line_total,
			      ROUND(SUM(wc_order_product_lookup.shipping_amount),2) as shipping,
			      ROUND(SUM(wc_order_product_lookup.coupon_amount),2) as discount_amount
			      FROM $wc_orders_table as w_c_o
			      INNER JOIN $wc_order_product_lookup as wc_order_product_lookup on w_c_o.id = wc_order_product_lookup.order_id
			      WHERE  DATE(w_c_o.date_created_gmt) >= '%s' AND DATE(w_c_o.date_created_gmt) <= '%s' AND w_c_o.type != 'shop_order_refund' GROUP By order_date ORDER by order_date_t ASC", $start_date, $end_date);

		  	$results = $wpdb->get_results($sql, ARRAY_A);
		  	//ROUND(SUM(wc_order_operational_data.shipping_total_amount),2) as shipping_total,
		  	$sql_1 =  $wpdb->prepare("SELECT SUM(CASE WHEN w_c_o.id IS NOT NULL THEN 1 ELSE 0 END) AS total_orders, DATE_FORMAT(w_c_o.date_created_gmt, '%Y-%b-%e') as order_date, w_c_o.date_created_gmt as order_date_t, 
					SUM(CASE WHEN w_c_o.customer_id IS NOT NULL THEN 1 ELSE 0 END) as total_users, count( DISTINCT w_c_o.customer_id) as unique_users,
					ROUND(SUM(w_c_o.tax_amount),2) as order_tax,
					ROUND(SUM(wc_order_operational_data.shipping_tax_amount),2) as shipping_tax,
					ROUND(SUM(wc_order_operational_data.discount_tax_amount),2) as order_discount_tax,
					ROUND(SUM(wc_order_operational_data.discount_total_amount),2) as discount_total,
					ROUND(SUM(wc_order_operational_data.prices_include_tax),2) as prices_include_tax,
					ROUND(SUM(w_c_o.total_amount),2) as order_total, 
					ROUND(SUM((SELECT r_m_refund_amount.meta_value FROM $postmeta_table as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount')),2) as refund_amount,
					SUM( CASE WHEN (SELECT r_m_refund_amount.meta_value FROM $postmeta_table as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount') IS NOT NULL THEN 1 ELSE 0 END) as refund_order     
					FROM $wc_orders_table as w_c_o
					INNER JOIN $wc_order_operational_data as wc_order_operational_data on w_c_o.id = wc_order_operational_data.order_id
					LEFT JOIN $posts_table as refund_post on refund_post.post_parent = w_c_o.id and refund_post.post_status = 'wc-completed' and refund_post.post_type='shop_order_refund'         
					WHERE  DATE(w_c_o.date_created_gmt) >= '%s' AND DATE(w_c_o.date_created_gmt) <= '%s' and w_c_o.type != 'shop_order_refund' GROUP By order_date ORDER by order_date_t ASC", $start_date, $end_date);	
					//printf($sql_1);
					//exit; 	
		  	$sub_results_1 = $wpdb->get_results($sql_1, ARRAY_A);

		  	$sql_2 =  $wpdb->prepare("SELECT SUM(CASE WHEN w_c_o.id IS NOT NULL THEN 1 ELSE 0 END) AS total_orders, w_c_o.status as order_status FROM $wc_orders_table as w_c_o WHERE  DATE(w_c_o.date_created_gmt) >= '%s' AND DATE(w_c_o.date_created_gmt) <= '%s' and w_c_o.type != 'shop_order_refund' GROUP By w_c_o.status ORDER by w_c_o.status DESC", $start_date, $end_date);
		  	$sub_results_2 = $wpdb->get_results($sql_2, ARRAY_A);
		  }else{
			 	$sql =  $wpdb->prepare("SELECT DATE_FORMAT(w_p.post_date, '%Y-%b-%e') as order_date, w_p.post_date as order_date_t, ROUND(SUM((SELECT oim_price.meta_value FROM ".$woocommerce_order_itemmeta." as oim_price WHERE  oim_price.order_item_id = oit.order_item_id and oim_price.meta_key = '_line_subtotal' )),2) as line_subtotal, 
			      SUM((SELECT oim_qty.meta_value FROM ".$woocommerce_order_itemmeta." as oim_qty WHERE  oim_qty.order_item_id = oit.order_item_id and oim_qty.meta_key = '_qty')) as line_qty,
			      ROUND(SUM((SELECT oim_line_tax.meta_value FROM ".$woocommerce_order_itemmeta." as oim_line_tax WHERE  oim_line_tax.order_item_id = oit.order_item_id and oim_line_tax.meta_key = '_line_tax')),1) as total_tax,
			      ROUND(SUM((SELECT oim_line_total.meta_value FROM ".$woocommerce_order_itemmeta." as oim_line_total WHERE  oim_line_total.order_item_id = oit.order_item_id and oim_line_total.meta_key = '_line_total')),1) as line_total,
			      ROUND(SUM((SELECT oim_shipping.meta_value FROM ".$woocommerce_order_itemmeta." as oim_shipping WHERE  oim_shipping.order_item_id = oit1.order_item_id and oim_shipping.order_item_id = oit.order_item_id and oim_shipping.meta_key = 'cost')),1) as shipping,
			      ROUND(SUM((SELECT oim_coupon.meta_value FROM ".$woocommerce_order_itemmeta." as oim_coupon WHERE  oim_coupon.order_item_id = oit2.order_item_id and oim_coupon.order_item_id = oit.order_item_id and  oim_coupon.meta_key = 'discount_amount')),1) as discount_amount
			      FROM ".$posts_table." as w_p
			      INNER JOIN ".$woocommerce_order_items." as oit on w_p.ID = oit.order_id
			      LEFT JOIN ".$woocommerce_order_items." as oit1 on w_p.ID = oit1.order_id and oit1.order_item_type = 'shipping'
			      LEFT JOIN ".$woocommerce_order_items." as oit2 on w_p.ID = oit2.order_id and oit2.order_item_type = 'coupon' 	         
			      WHERE w_p.post_type = 'shop_order' AND DATE(w_p.post_date) >= '%s' AND DATE(w_p.`post_date`) <= '%s' GROUP By order_date ORDER by order_date_t ASC", $start_date, $end_date); 
			  $results = $wpdb->get_results($sql, ARRAY_A);
			  $sql_1 =  $wpdb->prepare("SELECT SUM(CASE WHEN w_p.ID IS NOT NULL THEN 1 ELSE 0 END) AS total_orders, DATE_FORMAT(w_p.post_date, '%Y-%b-%e') as order_date, w_p.post_date as order_date_t, SUM(CASE WHEN pm1.meta_value IS NOT NULL THEN 1 ELSE 0 END) as total_users, count( DISTINCT pm1.meta_value) as unique_users,
			  		ROUND(SUM(pm3.meta_value),1) as order_tax,
			      ROUND(SUM(pm5.meta_value),1) as shipping_tax,
			      ROUND(SUM(pm4.meta_value),1) as order_total, 
			      ROUND(SUM((SELECT r_m_refund_amount.meta_value FROM ".$postmeta_table." as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount')),2) as refund_amount,
			      SUM( CASE WHEN (SELECT r_m_refund_amount.meta_value FROM ".$postmeta_table." as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount') IS NOT NULL THEN 1 ELSE 0 END) as refund_order     
			      FROM ".$posts_table." as w_p
			      INNER JOIN ".$postmeta_table." as pm3 on w_p.ID = pm3.post_id and pm3.meta_key = '_order_tax'
			      INNER JOIN ".$postmeta_table." as pm4 on w_p.ID = pm4.post_id and pm4.meta_key = '_order_total'
			      INNER JOIN ".$postmeta_table." as pm5 on w_p.ID = pm5.post_id and pm5.meta_key = '_order_shipping_tax'  
			      INNER JOIN ".$postmeta_table." as pm1 on w_p.ID = pm1.post_id and pm1.meta_key = '_customer_user'	
			      LEFT JOIN ".$posts_table." as refund_post on refund_post.post_parent = w_p.ID and refund_post.post_status = 'wc-completed' and refund_post.post_type='shop_order_refund'         
			      WHERE w_p.post_type = 'shop_order' AND DATE(w_p.post_date) >= '%s' AND DATE(w_p.`post_date`) <= '%s' GROUP By order_date ORDER by order_date_t ASC", $start_date, $end_date); 
			  $sub_results_1 = $wpdb->get_results($sql_1, ARRAY_A);

			  $sql_2 =  $wpdb->prepare("SELECT w_p.post_status as order_status, SUM(CASE WHEN w_p.ID IS NOT NULL THEN 1 ELSE 0 END) AS total_orders, w_p.post_status as order_status FROM ".$posts_table." as w_p WHERE w_p.post_type = 'shop_order' AND DATE(w_p.post_date) >= '%s' AND DATE(w_p.`post_date`) <= '%s' GROUP By w_p.post_status ORDER by w_p.post_status DESC", $start_date, $end_date);	  
			  $sub_results_2 = $wpdb->get_results($sql_2, ARRAY_A);
			}
		  $total_sale = 0;
		 	$net_sale = 0;
		 	$total_orders = 0;
		 	$average_order_value = 0;
		 	$item_sold = 0;
		 	$refund_order = 0;
		 	$refund_order_value = 0;
		 	$discount_amount = 0;
		 	$total_tax = 0;
		 	$order_tax = 0;
		 	$shipping_tax = 0;
		 	$shipping = 0;
		 	$total_users = 0;
		 	$unique_users = 0;
		 	$order_status = array();
		 	/*echo "<pre>";
		 	print_r($results);
		 	print_r($sub_results_1);
		 	print_r($sub_results_2);*/
		 	if(!empty($results)){
			 	foreach ($results as $key => $value) {
			 		if( strtolower($value['order_date']) == strtolower($sub_results_1[$key]['order_date']) ){
			 			$order_key_slug = str_replace("-", "_", $value['order_date']);
			 			$results['date'][$order_key_slug] = array_merge($results[$key],$sub_results_1[$key]);

			 			$t_order_total =$results['date'][$order_key_slug]['order_total'];
			 			$t_total_orders =$results['date'][$order_key_slug]['total_orders'];
			 			$t_average_order_value = 0;
			 			if($t_order_total >0){
			 				$t_average_order_value = $t_order_total/$t_total_orders;
			 			}
			 			
			 			$results['date'][$order_key_slug]['average_order_value'] = number_format($t_average_order_value,1);
			 			unset($results[$key]);
			 			//$order_status[$order_key_slug] = $results[$order_key_slug]['total_orders'];
			 			$total_sale += $results['date'][$order_key_slug]['order_total'];
			 			$net_sale+=$results['date'][$order_key_slug]['line_total'];
			 			$total_orders+=$results['date'][$order_key_slug]['total_orders'];
			 			$refund_order+=$results['date'][$order_key_slug]['refund_order'];
			 			$refund_order_value+=$results['date'][$order_key_slug]['refund_amount'];
			 			$discount_amount+=$results['date'][$order_key_slug]['discount_amount'];
			 			$total_tax+=$results['date'][$order_key_slug]['order_tax']+$results['date'][$order_key_slug]['shipping_tax'];
			 			$order_tax+=$results['date'][$order_key_slug]['order_tax'];
			 			$shipping_tax+=$results['date'][$order_key_slug]['shipping_tax'];
			 			$shipping+=$results['date'][$order_key_slug]['shipping'];
			 			$total_users += $results['date'][$order_key_slug]['total_users'];
			 			$unique_users += $results['date'][$order_key_slug]['unique_users'];
			 		}
			 	}		 	
		 	
			 	if($total_sale>0){
			 		$average_order_value =$total_sale/$total_orders;
			  }
			 	$results['summury'] = array(
			 		'total_sale' =>number_format($total_sale,0),
			 		'net_sale' =>number_format($net_sale,0),
			 		'total_orders' =>number_format($total_orders,0),
			 		'average_order_value'=>number_format($average_order_value,0),
			 		'refund_order' =>number_format($refund_order,0),
			 		'refund_order_value' =>number_format($refund_order_value,0),
			 		'discount_amount' =>number_format($discount_amount,0),
			 		'total_tax' =>number_format($total_tax,0),
			 		'order_tax' =>number_format($order_tax,0),
			 		'shipping_tax' =>number_format($shipping_tax,0),
			 		'shipping' =>number_format($shipping,0),
			 		'total_users' =>number_format($total_users,0),
			 		'unique_users' =>number_format($unique_users,0)
			 		);
			 	if(!empty($sub_results_2)){
			 		foreach ($sub_results_2 as $key => $value) {
			 			$key = str_replace('wc-', '', $value['order_status']);
			 			$results['summury']['order_status'][$key] = $value; 
			 		}
			 	}
			}
			
			/*echo "<pre>";
			print_r($results);
			exit;*/
		 	return  $results;		  
		}
		/* For order Overview Page */
		function get_dashboard_data($start_date, $end_date){ 
			global $wpdb;
		  $posts_table = $wpdb->prefix."posts";
		  $postmeta_table = $wpdb->prefix."postmeta";
		  $woocommerce_order_itemmeta = $wpdb->prefix."woocommerce_order_itemmeta";
		  $woocommerce_order_items = $wpdb->prefix."woocommerce_order_items";
		  $wc_orders_table = $wpdb->prefix."wc_orders";
		  $wc_order_operational_data = $wpdb->prefix."wc_order_operational_data";
		  $wc_order_product_lookup = $wpdb->prefix."wc_order_product_lookup";
		  $results = [];
		  $sub_results_1 = [];
		  if($this->WC_is_woocommerce_custom_orders_table_enabled()){
		  	//ROUND(SUM(wc_order_product_lookup.product_gross_revenue),2) as total,
		  	//ROUND(SUM(wc_order_product_lookup.tax_amount),2) as line_tax,
		  	$sql =  $wpdb->prepare("SELECT w_c_o.status as order_status, 
			      SUM(wc_order_product_lookup.product_qty) as line_qty,
			      ROUND(SUM(wc_order_product_lookup.product_net_revenue),2) as line_total,
			      ROUND(SUM(wc_order_product_lookup.shipping_amount),2) as shipping,
			      ROUND(SUM(wc_order_product_lookup.coupon_amount),2) as discount_amount
			      FROM $wc_orders_table as w_c_o
			      INNER JOIN $wc_order_product_lookup as wc_order_product_lookup on w_c_o.id = wc_order_product_lookup.order_id
			      WHERE  DATE(w_c_o.date_created_gmt) >= '%s' AND DATE(w_c_o.date_created_gmt) <= '%s' and w_c_o.type != 'shop_order_refund' GROUP By w_c_o.status ORDER by w_c_o.status", $start_date, $end_date);

		  	$results = $wpdb->get_results($sql, ARRAY_A);
		  	//echo "<pre>";
		  	//print_r($results);
		  	//ROUND(SUM(wc_order_operational_data.shipping_total_amount),2) as shipping_total,
		  	$sql_1 =  $wpdb->prepare("SELECT SUM(CASE WHEN w_c_o.id IS NOT NULL THEN 1 ELSE 0 END) AS total_orders, w_c_o.status as order_status, 
					SUM(CASE WHEN w_c_o.customer_id IS NOT NULL THEN 1 ELSE 0 END) as user_id,
					ROUND(SUM(w_c_o.tax_amount),2) as order_tax,
					ROUND(SUM(wc_order_operational_data.shipping_tax_amount),2) as order_shipping_tax,
					ROUND(SUM(wc_order_operational_data.discount_tax_amount),2) as order_discount_tax,
					ROUND(SUM(wc_order_operational_data.discount_total_amount),2) as discount_total,
					ROUND(SUM(wc_order_operational_data.prices_include_tax),2) as prices_include_tax,
					ROUND(SUM(w_c_o.total_amount),2) as order_total, 
					ROUND(SUM((SELECT r_m_refund_amount.meta_value FROM $postmeta_table as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount')),2) as refund_amount,
					SUM( CASE WHEN (SELECT r_m_refund_amount.meta_value FROM $postmeta_table as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount') IS NOT NULL THEN 1 ELSE 0 END) as refund_order     
					FROM $wc_orders_table as w_c_o
					INNER JOIN $wc_order_operational_data as wc_order_operational_data on w_c_o.id = wc_order_operational_data.order_id
					LEFT JOIN $posts_table as refund_post on refund_post.post_parent = w_c_o.id and refund_post.post_status = 'wc-completed' and refund_post.post_type='shop_order_refund'         
					WHERE  DATE(w_c_o.date_created_gmt) >= '%s' AND DATE(w_c_o.date_created_gmt) <= '%s' and w_c_o.type != 'shop_order_refund' GROUP By w_c_o.status ORDER by w_c_o.status", $start_date, $end_date);		  	
		  	$sub_results_1 = $wpdb->get_results($sql_1, ARRAY_A);
		  	
		  }else{		  
			 	$sql =  $wpdb->prepare("SELECT w_p.post_status as order_status,		     
			      ROUND(SUM((SELECT oim_price.meta_value FROM ".$woocommerce_order_itemmeta." as oim_price WHERE  oim_price.order_item_id = oit.order_item_id and oim_price.meta_key = '_line_subtotal' )),2) as line_subtotal, 
			      SUM((SELECT oim_qty.meta_value FROM ".$woocommerce_order_itemmeta." as oim_qty WHERE  oim_qty.order_item_id = oit.order_item_id and oim_qty.meta_key = '_qty')) as line_qty,
			      ROUND(SUM((SELECT oim_line_tax.meta_value FROM ".$woocommerce_order_itemmeta." as oim_line_tax WHERE  oim_line_tax.order_item_id = oit.order_item_id and oim_line_tax.meta_key = '_line_tax')),2) as line_tax,
			      ROUND(SUM((SELECT oim_line_total.meta_value FROM ".$woocommerce_order_itemmeta." as oim_line_total WHERE  oim_line_total.order_item_id = oit.order_item_id and oim_line_total.meta_key = '_line_total')),2) as line_total,
			      ROUND(SUM((SELECT oim_shipping.meta_value FROM ".$woocommerce_order_itemmeta." as oim_shipping WHERE  oim_shipping.order_item_id = oit1.order_item_id and oim_shipping.order_item_id = oit.order_item_id and oim_shipping.meta_key = 'cost')),2) as shipping,
			      ROUND(SUM((SELECT oim_coupon.meta_value FROM ".$woocommerce_order_itemmeta." as oim_coupon WHERE  oim_coupon.order_item_id = oit2.order_item_id and oim_coupon.order_item_id = oit.order_item_id and  oim_coupon.meta_key = 'discount_amount')),2) as discount_amount
			      FROM ".$posts_table." as w_p
			      INNER JOIN ".$woocommerce_order_items." as oit on w_p.ID = oit.order_id
			      LEFT JOIN ".$woocommerce_order_items." as oit1 on w_p.ID = oit1.order_id and oit1.order_item_type = 'shipping'
			      LEFT JOIN ".$woocommerce_order_items." as oit2 on w_p.ID = oit2.order_id and oit2.order_item_type = 'coupon' 	         
			      WHERE w_p.post_type = 'shop_order' AND DATE(w_p.post_date) >= '%s' AND DATE(w_p.`post_date`) <= '%s' GROUP By w_p.post_status ORDER by w_p.post_status", $start_date, $end_date);
			  //printf($sql); 
			  $results = $wpdb->get_results($sql, ARRAY_A);
			  //print_r($results);
			  $sql_1 =  $wpdb->prepare("SELECT SUM(CASE WHEN w_p.ID IS NOT NULL THEN 1 ELSE 0 END) AS total_orders, w_p.post_status as order_status, SUM(CASE WHEN pm1.meta_value IS NOT NULL THEN 1 ELSE 0 END) as user_id,
			  		ROUND(SUM(pm3.meta_value),2) as order_tax,
			      ROUND(SUM(pm5.meta_value),2) as order_shipping_tax,
			      ROUND(SUM(pm4.meta_value),2) as order_total, 
			      ROUND(SUM((SELECT r_m_refund_amount.meta_value FROM ".$postmeta_table." as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount')),2) as refund_amount,
			      SUM( CASE WHEN (SELECT r_m_refund_amount.meta_value FROM ".$postmeta_table." as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount') IS NOT NULL THEN 1 ELSE 0 END) as refund_order     
			      FROM ".$posts_table." as w_p
			      INNER JOIN ".$postmeta_table." as pm3 on w_p.ID = pm3.post_id and pm3.meta_key = '_order_tax'
			      INNER JOIN ".$postmeta_table." as pm4 on w_p.ID = pm4.post_id and pm4.meta_key = '_order_total'
			      INNER JOIN ".$postmeta_table." as pm5 on w_p.ID = pm5.post_id and pm5.meta_key = '_order_shipping_tax'  
			      INNER JOIN ".$postmeta_table." as pm1 on w_p.ID = pm1.post_id and pm1.meta_key = '_customer_user'	
			      LEFT JOIN ".$posts_table." as refund_post on refund_post.post_parent = w_p.ID and refund_post.post_status = 'wc-completed' and refund_post.post_type='shop_order_refund'         
			      WHERE w_p.post_type = 'shop_order' AND DATE(w_p.post_date) >= '%s' AND DATE(w_p.`post_date`) <= '%s' GROUP By w_p.post_status  ORDER by w_p.post_status", $start_date, $end_date); 
			  $sub_results_1 = $wpdb->get_results($sql_1, ARRAY_A);
			}
			if(!empty($results)){
			 	$total_sale = 0;
			 	$net_sale = 0;
			 	$total_orders = 0;
			 	$avrage_order_value = 0;
			 	$item_sold = 0;
			 	$refund_order = 0;
			 	$refund_order_value = 0;
			 	$discount_amount = 0;
			 	$total_tax = 0;
			 	$order_tax = 0;
			 	$shipping_tax = 0;
			 	$shipping = 0;
			 	$order_status = array();

			 	foreach ($results as $key => $value) {
			 		if($value['order_status'] == $sub_results_1[$key]['order_status']){
			 			$order_status_slug = str_replace("-", "_", $value['order_status']);
			 			$results[$order_status_slug] = array_merge($results[$key],$sub_results_1[$key]);
			 			unset($results[$key]);
			 			$order_status[$order_status_slug] = $results[$order_status_slug]['total_orders'];
			 			$total_sale += $results[$order_status_slug]['order_total'];
			 			$net_sale+=$results[$order_status_slug]['line_total'];
			 			$total_orders+=$results[$order_status_slug]['total_orders'];
			 			$refund_order+=$results[$order_status_slug]['refund_order'];
			 			$refund_order_value+=$results[$order_status_slug]['refund_amount'];
			 			$discount_amount+=$results[$order_status_slug]['discount_amount'];
			 			$total_tax+=$results[$order_status_slug]['order_tax']+$results[$order_status_slug]['order_shipping_tax'];
			 			$order_tax+=$results[$order_status_slug]['order_tax'];
			 			$shipping_tax+=$results[$order_status_slug]['order_shipping_tax'];
			 			$shipping+=$results[$order_status_slug]['shipping'];
			 		}
			 	}
			 	$avrage_order_value =$total_sale/$total_orders;
			 	$results['summury'] = array(
			 		'total_sale' =>$total_sale,
			 		'net_sale' =>$net_sale,
			 		'total_orders' =>$total_orders,
			 		'avrage_order_value'=>number_format($avrage_order_value,0),
			 		'refund_order' =>$refund_order,
			 		'refund_order_value' =>$refund_order_value,
			 		'discount_amount' =>$discount_amount,
			 		'total_tax' =>$total_tax,
			 		'order_tax' =>$order_tax,
			 		'shipping_tax' =>$shipping_tax,
			 		'shipping' =>$shipping 
			 	);
			 	$results['summury'] = array_merge($results['summury'],$order_status);			 	
			}
		 	/*echo "<pre>";
		 	print_r($results);
		 	exit;*/
		 	return  $results;		  
		}

		function get_order_data($start_date, $end_date){
		  global $wpdb;
		  $posts_table = $wpdb->prefix."posts";
		  $postmeta_table = $wpdb->prefix."postmeta";
		  $woocommerce_order_itemmeta = $wpdb->prefix."woocommerce_order_itemmeta";
		  $woocommerce_order_items = $wpdb->prefix."woocommerce_order_items";

		  $wc_orders_table = $wpdb->prefix."wc_orders";
		  $wc_order_operational_data = $wpdb->prefix."wc_order_operational_data";
		  $wc_order_product_lookup = $wpdb->prefix."wc_order_product_lookup";

		  $sql = "";
		  if($this->WC_is_woocommerce_custom_orders_table_enabled()){
		  	$sql =  $wpdb->prepare("SELECT w_c_o.id as order_id, w_c_o.date_created_gmt as order_date, w_c_o.status as order_status, w_c_o.customer_id as user_id, w_c_o.billing_email, oit.order_item_name, wc_order_product_lookup.product_id as line_product_id, wc_order_product_lookup.variation_id as line_variation_id, wc_order_product_lookup.variation_id as line_variation_id, wc_order_product_lookup.product_qty as line_qty, wc_order_product_lookup.tax_amount as line_tax, wc_order_product_lookup.product_qty as line_qty, wc_order_product_lookup.product_net_revenue as line_net_revenue, wc_order_product_lookup.product_gross_revenue as line_gross_revenue, wc_order_product_lookup.shipping_amount as shipping, wc_order_product_lookup.coupon_amount as discount_amount,(SELECT r_m_refund_amount.meta_value FROM ".$postmeta_table." as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount') as refund_amount,
		      (SELECT r_m_refund_reason.meta_value FROM ".$postmeta_table." as r_m_refund_reason WHERE  r_m_refund_reason.post_id = refund_post.ID and r_m_refund_reason.meta_key = '_refund_reason') as refund_reason,
		      (SELECT pro_sku.meta_value FROM ".$postmeta_table." as pro_sku WHERE  pro_sku.post_id = CASE WHEN line_variation_id != 0 THEN line_variation_id ELSE line_product_id END and pro_sku.meta_key = '_sku') as prod_sku
					FROM $wc_orders_table as w_c_o
					INNER JOIN $wc_order_product_lookup as wc_order_product_lookup on w_c_o.id = wc_order_product_lookup.order_id
					INNER JOIN ".$woocommerce_order_items." as oit on wc_order_product_lookup.order_item_id = oit.order_item_id
					INNER JOIN $wc_order_operational_data as wc_order_operational_data on w_c_o.id = wc_order_operational_data.order_id
					LEFT JOIN $posts_table as refund_post on refund_post.post_parent = w_c_o.id and refund_post.post_status = 'wc-completed' and refund_post.post_type='shop_order_refund'      
					WHERE  DATE(w_c_o.date_created_gmt) >= '%s' AND DATE(w_c_o.date_created_gmt) <= '%s' and w_c_o.type != 'shop_order_refund' ORDER by w_c_o.status", $start_date, $end_date);	
		  }else{
		  	$sql =  $wpdb->prepare("SELECT  w_p.ID as order_id, w_p.post_date as order_date, w_p.post_status as order_status,  pm1.meta_value as user_id, pm2.meta_value as billing_email, oit.order_item_name, oit.order_item_id, oit.order_item_type, oit1.order_item_name as shipping_name,
		      (SELECT oim_product_id.meta_value FROM ".$woocommerce_order_itemmeta." as oim_product_id WHERE  oim_product_id.order_item_id = oit.order_item_id and oim_product_id.meta_key = '_product_id') as line_product_id,
		      (SELECT oim_variation_id.meta_value FROM ".$woocommerce_order_itemmeta." as oim_variation_id WHERE  oim_variation_id.order_item_id = oit.order_item_id and oim_variation_id.meta_key = '_variation_id') as line_variation_id,
		      (SELECT oim_price.meta_value FROM ".$woocommerce_order_itemmeta." as oim_price WHERE  oim_price.order_item_id = oit.order_item_id and oim_price.meta_key = '_line_subtotal') as line_subtotal, 
		      (SELECT oim_qty.meta_value FROM ".$woocommerce_order_itemmeta." as oim_qty WHERE  oim_qty.order_item_id = oit.order_item_id and oim_qty.meta_key = '_qty') as line_qty,
		      (SELECT oim_line_tax.meta_value FROM ".$woocommerce_order_itemmeta." as oim_line_tax WHERE  oim_line_tax.order_item_id = oit.order_item_id and oim_line_tax.meta_key = '_line_tax') as line_tax,
		      (SELECT oim_line_total.meta_value FROM ".$woocommerce_order_itemmeta." as oim_line_total WHERE  oim_line_total.order_item_id = oit.order_item_id and oim_line_total.meta_key = '_line_total') as line_total,
		      (SELECT oim_shipping.meta_value FROM ".$woocommerce_order_itemmeta." as oim_shipping WHERE  oim_shipping.order_item_id = oit1.order_item_id and oim_shipping.order_item_id = oit.order_item_id and oim_shipping.meta_key = 'cost') as shipping,
		      (SELECT oim_coupon.meta_value FROM ".$woocommerce_order_itemmeta." as oim_coupon WHERE  oim_coupon.order_item_id = oit2.order_item_id and oim_coupon.order_item_id = oit.order_item_id and  oim_coupon.meta_key = 'discount_amount') as discount_amount,
		      (SELECT oim_coupon_data.meta_value FROM ".$woocommerce_order_itemmeta." as oim_coupon_data WHERE  oim_coupon_data.order_item_id = oit2.order_item_id and oim_coupon_data.order_item_id = oit.order_item_id and oim_coupon_data.meta_key = 'coupon_data') as discount_coupon_data,
		      pm3.meta_value as order_tax,
		      pm5.meta_value as order_shipping_tax,
		      pm4.meta_value as order_total,
		      (SELECT r_m_refund_amount.meta_value FROM ".$postmeta_table." as r_m_refund_amount WHERE  r_m_refund_amount.post_id = refund_post.ID and r_m_refund_amount.meta_key = '_refund_amount') as refund_amount,
		      (SELECT r_m_refund_reason.meta_value FROM ".$postmeta_table." as r_m_refund_reason WHERE  r_m_refund_reason.post_id = refund_post.ID and r_m_refund_reason.meta_key = '_refund_reason') as refund_reason,
		      (SELECT pro_sku.meta_value FROM ".$postmeta_table." as pro_sku WHERE  pro_sku.post_id = CASE WHEN line_variation_id != 0 THEN line_variation_id ELSE line_product_id END and pro_sku.meta_key = '_sku') as prod_sku
		      FROM ".$posts_table." as w_p
		      INNER JOIN ".$woocommerce_order_items." as oit on w_p.ID = oit.order_id
		      LEFT JOIN ".$woocommerce_order_items." as oit1 on w_p.ID = oit1.order_id and oit1.order_item_type = 'shipping'
		      LEFT JOIN ".$woocommerce_order_items." as oit2 on w_p.ID = oit2.order_id and oit2.order_item_type = 'coupon'
		      LEFT JOIN ".$posts_table." as refund_post on refund_post.post_parent = w_p.ID and refund_post.post_status = 'wc-completed' and refund_post.post_type='shop_order_refund'  
		      INNER JOIN ".$postmeta_table." as pm1 on w_p.ID = pm1.post_id and pm1.meta_key = '_customer_user'
		      INNER JOIN ".$postmeta_table." as pm2 on w_p.ID = pm2.post_id and pm2.meta_key = '_billing_email'
		      INNER JOIN ".$postmeta_table." as pm3 on w_p.ID = pm3.post_id and pm3.meta_key = '_order_tax'
		      INNER JOIN ".$postmeta_table." as pm4 on w_p.ID = pm4.post_id and pm4.meta_key = '_order_total'
		      INNER JOIN ".$postmeta_table." as pm5 on w_p.ID = pm5.post_id and pm5.meta_key = '_order_shipping_tax'
		         
		      WHERE w_p.post_type = 'shop_order' AND DATE(w_p.post_date) >= '%s' AND DATE(w_p.`post_date`) <= '%s'
		      ORDER by w_p.post_date DESC, w_p.post_author ASC", $start_date, $end_date); 
			}
		  //,(SELECT pro_sku.meta_value FROM ".$postmeta_table." as pro_sku WHERE  pro_sku.post_id = refund_post.ID and pro_sku.meta_key = '_sku') as prod_sku
		  //printf($sql);
		  $results = $wpdb->get_results($sql, ARRAY_A);
		  //print_r($results);
		  //exit;
		  $f_results = array();
		  if(!empty($results)){
		    foreach ($results as $key => $row){
		      $order_id = $row['order_id'];        
		      if(!isset($f_results[$order_id]['order_total'])){
		        $f_results[$order_id]['order_total'] = $row['order_total'];
		      }
		      if(!isset($f_results[$order_id]['order_id'])){
		        $f_results[$order_id]['order_id'] =$row['order_id'];
		      }
		      if(!isset($f_results[$order_id]['order_date'])){
		        $f_results[$order_id]['order_date'] = date('Y-m-d',strtotime($row['order_date']));
		      }
		      if(!isset($f_results[$order_id]['order_status'])){
		        $f_results[$order_id]['order_status'] = str_replace('wc-', '', $row['order_status']);
		      }
		      if(!isset($f_results[$order_id]['user_id'])){
		        $f_results[$order_id]['user_id'] = $row['user_id'];
		      }
		      if(!isset($f_results[$order_id]['billing_email'])){
		        $f_results[$order_id]['billing_email'] = $row['billing_email'];
		      }
		      if(!isset($f_results[$order_id]['order_tax'])){
		        $f_results[$order_id]['order_tax'] = $row['order_tax']+ $row['order_shipping_tax'];
		      }
		      if(!isset($f_results[$order_id]['refund_amount'])){
		        $f_results[$order_id]['refund_amount'] = $row['refund_amount'];
		      }
		      if(!isset($f_results[$order_id]['refund_reason'])){
		        $f_results[$order_id]['refund_reason'] = $row['refund_reason'];
		      }
		      
		      unset($row['order_id']);
		      unset($row['order_total']);
		      unset($row['order_date']);
		      unset($row['order_status']);
		      unset($row['user_id']);
		      unset($row['billing_email']);
		      unset($row['order_tax']);
		      unset($row['refund_amount']);
		      unset($row['refund_reason']);
		      $f_results[$order_id]['order_item_type_data'][] = $row;        
		    }
		  }
		  //print_r($f_results);
		  return $f_results;
		}
	}
endif; // class_exists