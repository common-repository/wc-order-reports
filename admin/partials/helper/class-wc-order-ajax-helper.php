<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * Order Reports for WooCommerce
 */
require_once(WC_ORDER_REPOSTS_PLUGIN_DIR.'includes/packages/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}
if(!class_exists('WC_Order_Ajax_Helper')):	
	require_once( 'class-wc-order-helper.php');
  require_once( 'class-wc-order-admin-api-helper.php');	
	class WC_Order_Ajax_Helper extends WC_Order_Helper{
		protected $WC_Order_DB_Helper;
    protected $WCOR_API;
		public function __construct(){
			$this->req_int();
			$this->WC_Order_DB_Helper = new WC_Order_DB_Helper();
      $this->WCOR_API = new WCOR_AdminAPIHelper();
			add_action('wp_ajax_wc_order_show_data', array($this,'wc_order_show_data') );
			add_action('wp_ajax_wc_order_download_data', array($this,'wc_order_download_data') );
			add_action('wp_ajax_wc_order_dashboard_data', array($this,'wc_order_dashboard_data') );
			add_action('wp_ajax_get_wcorder_reports_chart', array($this,'get_wcorder_reports_chart') );

			add_action('wp_ajax_wcor_check_privecy_policy', array($this,'wcor_check_privecy_policy') );
			add_action('wp_ajax_wcor_account_settings_save', array($this,'wcor_account_settings_save') );
		}

		public function req_int(){
			require_once( 'class-wc-order-db-helper.php');
			//require_once(WC_ORDER_REPOSTS_PLUGIN_DIR.'includes/packages/excel/PHPExcel.php');
			
		}
		protected function admin_safe_ajax_call( $nonce, $registered_nonce_name ) {
			// only return results when the user is an admin with manage options
			if ( is_admin() && wp_verify_nonce($nonce,$registered_nonce_name) ) {
				return true;
			} else {
				return false;
			}
		}
		/**
     * Save Pixel data
     **/
    public function wcor_account_settings_save(){
      $ajax_nonce = isset($_POST["wcor_ajax_nonce"])?sanitize_text_field($_POST["wcor_ajax_nonce"]):"";
      if($this->admin_safe_ajax_call($ajax_nonce, 'wcor_ajax_nonce')){
        $pixels_option = $this->get_post_pmw_pixels_option_sanitize();
        $validate = $this->validate_pixels($pixels_option);
        if(isset($validate["error"]) && $validate["error"] == true){
          echo wp_send_json( $validate );
          exit;
        }else{
          $store_data = array();       
          $pixels_option = apply_filters("wcor_option_before_save", $pixels_option);
          //$old_pixels_option = $this->get_wcor_option();
          $this->save_wcor_option($pixels_option);
          $api_rs = $this->WCOR_API->save_product_store($pixels_option, 1);
          if (!empty($api_rs) && isset($api_rs->error) && $api_rs->error == '' && isset($api_rs->data) ) {
            $this->save_wcor_api_store((array)$api_rs->data);
          }                
          echo wp_send_json( array("error" => false, 'message' => __("Your settings saved.", "wc-order-reports")) );
          exit;
        }
      }else{
        echo wp_send_json( array("error" => true, 'message' => __("Your admin nonce is not valid.", "wc-order-reports")) );
        exit;
      }
    }
		/**
     * Check privecy policy base on user email
     **/
    public function wcor_check_privecy_policy(){
      $ajax_nonce = isset($_POST["wcor_ajax_nonce"])?sanitize_text_field($_POST["wcor_ajax_nonce"]):"";
      if($this->admin_safe_ajax_call($ajax_nonce, 'wcor_ajax_nonce')){
        $pixels_option = $this->get_post_pmw_pixels_option_sanitize();
        $validate = $this->validate_pixels($pixels_option);
        if(isset($validate["error"]) && $validate["error"] == true){
          echo wp_send_json( $validate );
          exit;
        }else{
          $pixels_option_old = $this->get_wcor_option();
          if( isset($pixels_option_old['privecy_policy']['privecy_policy']) && $pixels_option_old['privecy_policy']['privecy_policy'] == 1 && $pixels_option_old['user']['email_id'] ==  $pixels_option['user']['email_id']){
            echo wp_send_json( array( "error" => false ) );
            exit;
          }else{
            echo wp_send_json( array( "error" => true ) );
            exit;
          }
        }
      }else{
        echo wp_send_json( array("error" => true, 'message' => __("Your admin nonce is not valid.", "wc-order-reports")) );
        exit;
      }
    }

    public function get_post_pmw_pixels_option_sanitize(){
      //$pixels = array("facebook_pixel", "pinterest_pixel", "snapchat_pixel");
      $return = array(
        "user" => array(
          "email_id" => isset($_POST["email_id"])?sanitize_email($_POST["email_id"]):""
        ),
        "privecy_policy" => array(
          "is_theme_plugin_list" => isset($_POST["is_theme_plugin_list"])?sanitize_text_field($_POST["is_theme_plugin_list"]):0,
          "privecy_policy" => 1
        )
      );
      return $return;
    }

    public function validate_pixels(array $pixels_option){
     //print_r($pixels_option);
      $return = array();      
      if(!isset($pixels_option["user"]["email_id"]) || $pixels_option["user"]["email_id"] == "" || !is_email($pixels_option["user"]["email_id"]) ){
        $return = array("error" => true, "message" => __("Check your email ID.", "wc-order-reports"));
      }
      return $return;
    }
		/**
		 * Ajax code for show wc order data.
		 * @since    1.0.0
		 */
		public function get_wcorder_reports_chart(){
			$ajax_nonce = (isset($_POST['wc_order_nonce']))?sanitize_text_field($_POST['wc_order_nonce']):"";
			if($this->admin_safe_ajax_call($ajax_nonce, 'get_wcorder_reports_chart_nonce')){	
				$start_date = (isset($_POST['start_date']))?sanitize_text_field($_POST['start_date']):"";
				
				if($start_date != ""){
					$date = DateTime::createFromFormat('F-d-Y', $start_date);
					$start_date = $date->format('Y-m-d');
				}
				$start_date == (false !==strtotime( $start_date ))?date('Y-m-d', strtotime($start_date)):date( 'Y-m-d', strtotime( '-1 month' ));

				$end_date = (isset($_POST['end_date']))?sanitize_text_field($_POST['end_date']):"";
				if($end_date != ""){
					$date = DateTime::createFromFormat('F-d-Y', $end_date);
					$end_date = $date->format('Y-m-d');
				}
				$end_date == (false !==strtotime( $end_date ))?date('Y-m-d', strtotime($end_date)):date( 'Y-m-d', strtotime( 'now' ));

			  if($start_date && $end_date){
			    $data = $this->WC_Order_DB_Helper->get_sales_report_analysis($start_date, $end_date);
      		$data['currency'] = $this->get_woocommerce_currency_symbol();      		
			     echo wp_send_json(array("error"=>false, 'data'=>$data));
          exit;
			  }
			}else{
        echo wp_send_json(array("error"=>true, 'message'=> __("Your admin nonce is not valid.11","wc-order-reports")));
				exit;
			}
		}
		/**
		 * Ajax code for show wc order data.
		 * @since    1.0.0
		 */
		public function wc_order_dashboard_data(){
			$wc_order_nonce = (isset($_POST['wc_order_nonce']))?sanitize_text_field($_POST['wc_order_nonce']):"";
			if($this->admin_safe_ajax_call($wc_order_nonce, 'wc_order_dashboard_data_nonce')){	
				
			  $start_date = (isset($_POST['start_date']))?date('Y-m-d', strtotime( sanitize_text_field($_POST['start_date']) ) ):date( 'Y-m-d', strtotime( '-1 month' ));
			  $end_date = (isset($_POST['end_date'])) ? date('Y-m-d', strtotime( sanitize_text_field($_POST['end_date']) ) ) : date( 'Y-m-d', strtotime('now') );
			  if($start_date && $end_date){
			    $result = $this->WC_Order_DB_Helper->get_dashboard_data($start_date, $end_date);
			    $summury = isset($result['summury'])?$result['summury']:"";
      		$currency = $this->get_woocommerce_currency_symbol();
      		ob_start();
      		?>
      		<div class="product-card">
            <div class="row row-cols-5">
              <div class="col">
                <div class="card">
                  <h3 class="pro-count" id="total_sale"><?php echo $this->get_val_from_obj($summury, 'total_sale', $currency); ?></h3>
                  <p class="pro-title">Total Sale</p>                      
                </div>
              </div>
              <div class="col">
                <div class="card">
                  <h3 class="pro-count" id="net_sel"><?php echo $this->get_val_from_obj($summury, 'net_sale', $currency); ?></h3>
                  <p class="pro-title">Net Sale</p>                      
                </div>
              </div>
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count" id="orders"><?php echo $this->get_val_from_obj($summury, 'total_orders'); ?></h3>
                  <p class="pro-title">Orders</p>                        
                </div>
              </div>
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count" id="average_order_value"><?php echo $this->get_val_from_obj($summury, 'avrage_order_value', $currency); ?></h3>
                  <p class="pro-title">Average Order Value</p>                        
                </div>
              </div>
              <div class="col">
                <div class="card approved">
                  <h3 class="pro-count" id="returns"><?php echo $this->get_val_from_obj($summury, 'refund_order', $currency); ?></h3>
                  <p class="pro-title">Refund Orders</p>                        
                </div>
              </div>
              <div class="col">
                <div class="card approved">
                  <h3 class="pro-count" id="returns_value"><?php echo $this->get_val_from_obj($summury, 'refund_order_value', $currency); ?></h3>
                  <p class="pro-title">Refund amount</p>                        
                </div>
              </div>
              <div class="col">
                <div class="card disapproved">
                  <h3 class="pro-count" id="discount_amount"><?php echo $this->get_val_from_obj($summury, 'refund_order_value', $currency); ?></h3>
                  <p class="pro-title">Discount amount</p>                        
                </div>
              </div>
              <div class="col">
                <div class="card disapproved">
                  <h3 class="pro-count" id="total_tax"><?php echo $this->get_val_from_obj($summury, 'total_tax', $currency); ?></h3>
                  <p class="pro-title">Total TAX</p>                        
                </div>
              </div>
              <div class="col">
                <div class="card disapproved">
                  <h3 class="pro-count" id="order_tax"><?php echo $this->get_val_from_obj($summury, 'order_tax', $currency); ?></h3>
                  <p class="pro-title">Order TAX</p>                        
                </div>
              </div>
              <div class="col">
                <div class="card disapproved">
                  <h3 class="pro-count" id="shipping_tax"><?php echo $this->get_val_from_obj($summury, 'shipping_tax', $currency); ?></h3>
                  <p class="pro-title">Shipping TAX</p>                        
                </div>
              </div>
              <div class="col">
                <div class="card disapproved">
                  <h3 class="pro-count" id="shipping"><?php echo $this->get_val_from_obj($summury, 'shipping', $currency); ?></h3>
                  <p class="pro-title">Shipping</p>                        
                </div>
              </div>
            </div>
          </div>
      		<?php
      		$sec_1 = ob_get_contents();
  				ob_end_clean();
  				/*section 2*/
  				ob_start();
  				?>
  				<div class="product-card">
            <div class="row row-cols-4">
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count"><?php echo __('On Hold', 'wc-order-reports' ); ?><span>(<?php echo $this->get_val_from_obj($summury, 'wc_on_hold'); ?>)</span></h3>
                  <?php echo $this->get_order_status_info($result,'wc_on_hold'); ?>
                </div>
              </div>
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count"><?php echo __( 'Pending payment', 'wc-order-reports' ); ?><span>(<?php echo $this->get_val_from_obj($summury, 'wc_pending'); ?>)</span></h3>
                  <?php echo $this->get_order_status_info($result,'wc_pending'); ?>     
                </div>
              </div>
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count"><?php echo __( 'Processing', 'wc-order-reports' ); ?><span>(<?php echo $this->get_val_from_obj($summury, 'wc_processing'); ?>)</span></h3>
                  <?php echo $this->get_order_status_info($result,'wc_processing'); ?>
                </div>
              </div>
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count"><?php echo __( 'Completed', 'wc-order-reports' ); ?><span>(<?php echo $this->get_val_from_obj($summury, 'wc_completed'); ?>)</span></h3>
                  <?php echo $this->get_order_status_info($result,'wc_completed'); ?>  
                </div>
              </div>
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count"><?php echo __( 'Cancelled', 'wc-order-reports' ); ?><span>(<?php echo $this->get_val_from_obj($summury, 'wc_cancelled'); ?>)</span></h3>
                  <?php echo $this->get_order_status_info($result,'wc_cancelled'); ?>  
                </div>
              </div>
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count"><?php echo __( 'Refunded', 'wc-order-reports' ); ?><span>(<?php echo $this->get_val_from_obj($summury, 'wc_refunded'); ?>)</span></h3>
                  <?php echo $this->get_order_status_info($result,'wc_refunded'); ?>   
                </div>
              </div>
              <div class="col">
                <div class="card pending">
                  <h3 class="pro-count"><?php echo __( 'Failed', 'wc-order-reports' ); ?><span>(<?php echo $this->get_val_from_obj($summury, 'wc_failed'); ?>)</span></h3>
                  <?php echo $this->get_order_status_info($result,'wc_failed'); ?>     
                </div>
              </div>                   
            </div>
          </div>
  				<?php
  				$sec_2 = ob_get_contents();
  				ob_end_clean();
			    echo wp_send_json(array("sec_1"=>$sec_1, "sec_2"=>$sec_2));
			    exit;
			  }
			}else{
				echo "You admin security nonce is not verified.";
			}
		}
		/**
		 * Ajax code for show wc order data.
		 * @since    1.0.0
		 */
		public function wc_order_show_data(){
			$wc_order_nonce = (isset($_POST['wc_order_nonce']))?sanitize_text_field($_POST['wc_order_nonce']):"";
			if($this->admin_safe_ajax_call($wc_order_nonce, 'wc_order_show_data_nonce')){				
				
			  $start_date = (isset($_POST['start_date']))?date('Y-m-d', strtotime( sanitize_text_field($_POST['start_date']) ) ):date( 'Y-m-d', strtotime( '-1 month' ));
			  $end_date = (isset($_POST['end_date'])) ? date('Y-m-d', strtotime( sanitize_text_field($_POST['end_date']) ) ) : date( 'Y-m-d', strtotime('now') );
			  $html ="";
			  $data = array();
			  if($start_date && $end_date){
			    $results = $this->WC_Order_DB_Helper->get_order_data($start_date, $end_date);
			    if(!empty($results)){
			    	$html ='<table id="order-data-rs" class="table table-striped table-bordered dataTable display" style="width:100%"><thead><tr> <th>Order ID</th> <th>Date</th> <th width="150px">Row Type</th> <th width="120px" class="td-align-left">Item Name</th> <th>Qty</th> <th>Amount</th> <th>Shipping</th> <th>Discount</th> <th>Taxes</th> <th>Returns</th> <th>Total</th> </tr></thead><tbody>';
			      foreach ($results as $key_order => $row_order){
			        if(!empty($row_order)){
			          $order_id =$row_order['order_id'];
			          $order_total =$row_order['order_total'];
			          $order_date =$row_order['order_date'];
			          $order_status =$row_order['order_status'];
			          $user_id =$row_order['user_id'];
			          $billing_email =$row_order['billing_email'];
			          $order_tax =$row_order['order_tax'];

			          $sub_total =0;
			          $line_tax =0;
			          $qty =0;
			          $shipping = 0;
			          $discount_amount = 0;
			          $tax = 0;
			          $refund_amount = 0;
			          //header('Content-Type: application/json');
			          foreach ($row_order['order_item_type_data'] as $key => $row){  
				          if($this->WC_Order_DB_Helper->WC_is_woocommerce_custom_orders_table_enabled()){
				          	$sub_total+= $row["line_net_revenue"];
				          	$order_total+= $row["line_gross_revenue"];
			              $qty+= $row["line_qty"];
			              $order_tax+= $row["line_tax"];
			              $shipping += $row["shipping"];
			              $discount_amount += $row["discount_amount"];
			              $html.='<tr><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Product</td> <td class="td-align-left">'.$row["order_item_name"].'<br>SKU: '.$row["prod_sku"].'</td> <td>'.$row["line_qty"].'</td> <td>'.$row["line_net_revenue"].'</td> <td>'.$row["shipping"].'</td> <td>'.$row["discount_amount"].'</td> <td>'.$row["line_tax"].'</td><td></td> <td>'.$row["line_gross_revenue"].'</td></tr>';
				          }else if($row['order_item_type'] == 'line_item'){
			              $sub_total+= $row["line_subtotal"];
			              $line_tax+= $row["line_tax"];
			              $qty+= $row["line_qty"];
			              $tax+= $row["line_tax"];
			              $html.='<tr><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Product</td> <td class="td-align-left">'.$row["order_item_name"].'<br>SKU: '.$row["prod_sku"].'</td> <td>'.$row["line_qty"].'</td> <td>'.$row["line_subtotal"].'</td> <td></td> <td></td> <td>'.$row["line_tax"].'</td> <td></td> <td>'.$row["line_subtotal"].'</td></tr>';
			            }else if($row['order_item_type'] == 'shipping'){
			              $shipping = $row["shipping"];
			              $html.='<tr><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Shipping</td> <td class="td-align-left">'.$row["shipping_name"].'</td> <td></td> <td></td> <td>'.$row["shipping"].'</td> <td></td> <td>'.$row["order_shipping_tax"].'</td> <td></td> <td>'.$row["shipping"].'</td></tr>';
			            }else if($row['order_item_type'] == 'coupon'){
			              $coupon_data = maybe_unserialize($row['discount_coupon_data']);
			              $discount_amount = $row["discount_amount"];
			              $html.='<tr><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Discount</td> <td class="td-align-left">Code: '.$coupon_data["code"].'</td> <td></td> <td></td> <td></td> <td>-'.$row["discount_amount"].'</td> <td></td> <td></td> <td>-'.$row["discount_amount"].'</td></tr>';
			            }
			          }
			          if($row_order['refund_amount'] > 0){            
			            $f_total= $order_total - $row_order["refund_amount"];
			            $html.='<tr class="refund_highlighted"><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Refund</td> <td class="td-align-left">'.$row_order["refund_reason"].'</td> <td></td> <td></td> <td></td> <td></td> <td></td> <td>-'.$row_order["refund_amount"].'</td> <td>-'.$row_order["refund_amount"].'</td></tr>';

			            $html.='<tr class="highlighted"><td><a href="'.get_admin_url().'post.php?post='.$order_id.'&action=edit" target="_blank">'.$order_id.'</a></td> <td>'.$order_date.'</td> <td>email: '.$billing_email.'</td> <td class="td-align-left">status: '.$order_status.'</td> <td>'.$qty.'</td> <td>'.$sub_total.'</td> <td>'.$shipping.'</td> <td>'.$discount_amount.'</td> <td>'.$tax.'</td> <td>-'.$row_order["refund_amount"].'</td> <td>'.$f_total.'</td></tr>';
			          }else{
			            $html.='<tr class="highlighted"><td><a href="'.get_admin_url().'post.php?post='.$order_id.'&action=edit" target="_blank">'.$order_id.'</a></td> <td>'.$order_date.'</td> <td>email: '.$billing_email.'</td> <td class="td-align-left">status: '.$order_status.'</td> <td>'.$qty.'</td> <td>'.$sub_total.'</td> <td>'.$shipping.'</td> <td>'.$discount_amount.'</td> <td>'.$order_tax.'</td> <td></td> <td>'.$order_total.'</td></tr>';
			          }          

			        }
			      }
			      $html .='<tbody><tfoot><tr> <th>Order ID</th><th>Date</th><th>Row Type</th> <th class="td-align-left">Item Name</th> <th>Qty</th> <th>Amount</th> <th>Shipping</th> <th>Discount</th> <th>Taxes</th> <th>Returns</th> <th>Total</th> </tr></tfoot></table>';
			      $data = array('error' => false,'order_result' => $html);
			    }else{
			    	$html = "No order data available.";
			    	$data = array('error' => true,'order_result' => $html);
			    }
			  }else{
			    $html = "Something went wrong. Selected date range not fetch order data.";
			    $data = array('error' => true,'order_result' => $html);
			  } 
			  echo wp_send_json($data);
			  wp_die();
			}else{
				echo "You admin security nonce is not verified.";
			}
		}
		/**
		 * Ajax code for download wc order data.
		 * @since    1.0.0
		 */
		public function wc_order_download_data(){
			$wc_order_nonce = (isset($_POST['wc_order_nonce']))?sanitize_text_field($_POST['wc_order_nonce']):"";
			
			if($this->admin_safe_ajax_call($wc_order_nonce, 'wc_order_download_data_nonce')){
				global $wpdb;
				$file_type = (isset($_POST['file_type']))? sanitize_text_field($_POST['file_type']):"excel";
			  $start_date = (isset($_POST['start_date']))?date('Y-m-d', strtotime( sanitize_text_field($_POST['start_date']) ) ):date( 'Y-m-d', strtotime( '-1 month' ));
			  $end_date = (isset($_POST['end_date'])) ? date('Y-m-d', strtotime( sanitize_text_field($_POST['end_date']) ) ) : date( 'Y-m-d', strtotime('now') );

			  $html ="";
			  $file_name = "";
			  $data = array();
			  $woocommerce_order_itemmeta_t = $wpdb->prefix.'woocommerce_order_itemmeta';

			  if($start_date && $end_date){
			    $results = $this->WC_Order_DB_Helper->get_order_data($start_date, $end_date);
			    if(!empty($results)){
			    	$current_user = wp_get_current_user(); 
			    	$file_name ="";
			    	//Excel
			      //$phpExcel = new PHPExcel();
			      $phpExcel = new Spreadsheet();
			      $phpExcelSheet = "";
			      $e_row=1;
			      //CSV
			      $f = "";
			      $delimiter = ",";

			    	$html ='<table id="order-data-rs" class="table table-striped table-bordered dataTable display" style="width:100%"><thead><tr> <th>Order ID</th> <th>Date</th> <th>Row Type</th> <th width="120px" class="td-align-left">Item Name</th> <th>Qty</th> <th>Amount</th> <th>Shipping</th> <th>Discount</th> <th>Taxes</th> <th>Returns</th> <th>Total</th> </tr></thead><tbody>';			    	
			      if($file_type == "excel"){			      
				      $phpExcel->getProperties()->setCreator("WC Order")
				       ->setLastModifiedBy("WC Order")
				       ->setTitle("WC Order Reports")
				       ->setSubject("WC Order")
				       ->setDescription("WC Order Reports")
				       ->setKeywords("WC Order Reports")
				       ->setCategory("WC Order Reports");
				      
				      //$phpExcel->getDefaultStyle()->getFont()->setSize(11);
				      $phpExcelSheet = $phpExcel->setActiveSheetIndex(0);
				      //$phpExcelSheet->getDefaultStyle()->getFont()->setName('calibri')->setSize(11)->setBold(true);
				      $phpExcelSheet->mergeCells('A'.$e_row.':H'.$e_row);
				      $phpExcelSheet->setCellValue('A'.$e_row,'Order Report: '.$start_date.' to '.$end_date);
				      $phpExcelSheet->mergeCells('I'.$e_row.':K'.$e_row);
				      $phpExcelSheet->setCellValue('I'.$e_row,'Report Dt.: '.date('M-d-Y'));
				      $e_row++;
				      //$abcd = array('A','B');
				      $phpExcelSheet->setCellValue('A'.$e_row, 'Order ID')
				        ->setCellValue('B'.$e_row, 'Date')
				        ->setCellValue('C'.$e_row, 'Row Type')
				        ->setCellValue('D'.$e_row, 'Item Name')
				        ->setCellValue('E'.$e_row, 'SKU')
				        ->setCellValue('F'.$e_row, 'Variation Description')
				        ->setCellValue('G'.$e_row, 'Qty')
				        ->setCellValue('H'.$e_row, 'Amount')
				        ->setCellValue('I'.$e_row, 'Shipping')
				        ->setCellValue('J'.$e_row, 'Discount')
				        ->setCellValue('K'.$e_row, 'Taxes')
				        ->setCellValue('L'.$e_row, 'Returns')
				        ->setCellValue('M'.$e_row, 'Total')
				        ->setCellValue('N'.$e_row, 'User Id')
				        ->setCellValue('O'.$e_row, 'User Email')
				        ->setCellValue('P'.$e_row, 'Order Status')
				        ->setCellValue('Q'.$e_row, 'Order Qty')
				        ->setCellValue('R'.$e_row, 'Order TAX')
				        ->setCellValue('S'.$e_row, 'Order Pay');
				      }else if($file_type == "csv"){
				      	$file_name = 'wc-order-report-u-'.$current_user->ID.'.csv';
		            if (!file_exists(WP_CONTENT_DIR.'/upgrade/'.WC_ORDER_REPOSTS_PLUGIN)) {
		              mkdir(WP_CONTENT_DIR.'/upgrade/'.WC_ORDER_REPOSTS_PLUGIN, 0777, true);
		            }
		            $f = fopen(WP_CONTENT_DIR.'/upgrade/'.WC_ORDER_REPOSTS_PLUGIN.'/'.$file_name, 'w');
		            //csv
		            $fields = array('Order ID', 'Date', 'Row Type', 'Item Name', 'SKU', 'Qty', 'Amount', 'Shipping', 'Discount','Taxes','Refund','Total'); 
		            fputcsv($f, $fields, $delimiter);
				      }
			      $e_row++;
			      foreach ($results as $key_order => $row_order){
			        if(!empty($row_order)){
			          $order_id =$row_order['order_id'];
			          $order_total =$row_order['order_total'];
			          $order_date =$row_order['order_date'];
			          $order_status =$row_order['order_status'];
			          $user_id =($row_order['user_id'] != 0)?$row_order['user_id']:'Guest';
			          $billing_email =$row_order['billing_email'];
			          $order_tax =$row_order['order_tax'];

			          $sub_total =0;
			          $line_tax =0;
			          $qty =0;
			          $shipping = 0;
			          $discount_amount = 0;
			          $tax = 0;
			          if($file_type == "excel"){
				          //$phpExcelSheet->getDefaultStyle()->getFont()->setName('calibri')->setSize(11)->setBold(false);
				        }
			          $order_row = 0;
			          //print_r($row_order);
			          //exit;
			          foreach ($row_order['order_item_type_data'] as $key => $row){     
			            if($this->WC_Order_DB_Helper->WC_is_woocommerce_custom_orders_table_enabled()){
			              $variation_description ="";	              
			              if($row["prod_sku"] != ""){
			               	$sql =  $wpdb->prepare("SELECT meta_key, meta_value FROM ".$woocommerce_order_itemmeta_t." WHERE  order_item_id = %d and meta_key NOT IN ('_product_id','_variation_id','_qty','_tax_class','_line_subtotal','_line_subtotal_tax','_line_total','_line_tax','_line_tax_data','_fly_woo_discount_price_rules')",$row["order_item_id"]);
			                $o_results = $wpdb->get_results($sql, ARRAY_A);
			                if(!empty($o_results)){
			                  foreach ($o_results as $o_key => $o_row){
			                    $o_row['meta_key'] = str_replace('choose-your-', '', $o_row['meta_key']);
			                    if($variation_description ==""){
			                      $variation_description=str_replace('choose-the-', '', $o_row['meta_key']).':'.$o_row['meta_value'];
			                    }else{
			                      $variation_description.=', '.str_replace('choose-the-', '', $o_row['meta_key']).':'.$o_row['meta_value'];
			                    }                    
			                  }
			                }
			              }

			              $sub_total+= $row["line_net_revenue"];
			              $order_total+= $row["line_gross_revenue"];  
			              //$line_tax+= $row["line_tax"];
			              $order_tax+= $row["line_tax"];
			              $qty+= $row["line_qty"];
			             // $tax+= $row["line_tax"];
			              $shipping += $row["shipping"];
			              $discount_amount += $row["discount_amount"];
			              $html.='<tr><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Product</td> <td class="td-align-left">'.$row["order_item_name"].'<br>SKU: '.$row["prod_sku"].'</td> <td>'.$row["line_qty"].'</td> <td>'.$row["line_net_revenue"].'</td> <td>'.$row["shipping"].'</td> <td>'.$row["discount_amount"].'</td> <td>'.$row["line_tax"].'</td> <td></td> <td>'.$row["line_gross_revenue"].'</td></tr>';

			              /***start product items***/
			              if($file_type == "excel"){
				              $phpExcelSheet->setCellValue('A'.$e_row, $order_id)
				                ->setCellValue('B'.$e_row, $order_date)
				                ->setCellValue('C'.$e_row, 'Product')
				                ->setCellValue('D'.$e_row, $row["order_item_name"])
				                ->setCellValue('E'.$e_row, $row["prod_sku"])
				                ->setCellValue('F'.$e_row, $variation_description)
				                ->setCellValue('G'.$e_row, $row["line_qty"])
				                ->setCellValue('H'.$e_row, $row["line_net_revenue"])
				                ->setCellValue('I'.$e_row, $row["shipping"])
				                ->setCellValue('J'.$e_row, $row["discount_amount"])
				                ->setCellValue('K'.$e_row, $row["line_tax"])
				                ->setCellValue('L'.$e_row, '')
				                ->setCellValue('M'.$e_row, $row["line_gross_revenue"]);
			                //->setCellValue('K'.$e_row, $row["line_total"]);
				            }else if($file_type == "csv"){
				            	$fields = array($order_id, $order_date, 'Product', $row["order_item_name"], $row["prod_sku"], $row["line_qty"], $row["line_net_revenue"], $row["shipping"], $row["discount_amount"],$row["line_tax"],'',$row["line_gross_revenue"]);
                    	fputcsv($f, $fields, $delimiter);
				            }

			            }else if($row["order_item_type"] == "line_item"){
			              $variation_description ="";	              
			              if($row["prod_sku"] != ""){
			               	$sql =  $wpdb->prepare("SELECT meta_key, meta_value FROM ".$woocommerce_order_itemmeta_t." WHERE  order_item_id = %d and meta_key NOT IN ('_product_id','_variation_id','_qty','_tax_class','_line_subtotal','_line_subtotal_tax','_line_total','_line_tax','_line_tax_data','_fly_woo_discount_price_rules')",$row["order_item_id"]);
			                $o_results = $wpdb->get_results($sql, ARRAY_A);
			                if(!empty($o_results)){
			                  foreach ($o_results as $o_key => $o_row){
			                    $o_row['meta_key'] = str_replace('choose-your-', '', $o_row['meta_key']);
			                    if($variation_description ==""){
			                      $variation_description=str_replace('choose-the-', '', $o_row['meta_key']).':'.$o_row['meta_value'];
			                    }else{
			                      $variation_description.=', '.str_replace('choose-the-', '', $o_row['meta_key']).':'.$o_row['meta_value'];
			                    }                    
			                  }
			                }
			              }

			              $sub_total+= $row["line_subtotal"];  
			              $line_tax+= $row["line_tax"];
			              $qty+= $row["line_qty"];
			              $tax+= $row["line_tax"];
			              $html.='<tr><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Product</td> <td class="td-align-left">'.$row["order_item_name"].'<br>SKU: '.$row["prod_sku"].'</td> <td>'.$row["line_qty"].'</td> <td>'.$row["line_subtotal"].'</td> <td></td> <td></td> <td>'.$row["line_tax"].'</td> <td></td> <td>'.$row["line_subtotal"].'</td></tr>';

			              /***start product items***/
			              if($file_type == "excel"){
				              $phpExcelSheet->setCellValue('A'.$e_row, $order_id)
				                ->setCellValue('B'.$e_row, $order_date)
				                ->setCellValue('C'.$e_row, 'Product')
				                ->setCellValue('D'.$e_row, $row["order_item_name"])
				                ->setCellValue('E'.$e_row, $row["prod_sku"])
				                ->setCellValue('F'.$e_row, $variation_description)
				                ->setCellValue('G'.$e_row, $row["line_qty"])
				                ->setCellValue('H'.$e_row, $row["line_subtotal"])
				                ->setCellValue('I'.$e_row, '')
				                ->setCellValue('J'.$e_row, '')
				                ->setCellValue('K'.$e_row, $row["line_tax"])
				                ->setCellValue('L'.$e_row, '')
				                ->setCellValue('M'.$e_row, $row["line_subtotal"]);
			                //->setCellValue('K'.$e_row, $row["line_total"]);
				            }else if($file_type == "csv"){
				            	$fields = array($order_id, $order_date, 'Product', $row["order_item_name"], $row["prod_sku"], $row["line_qty"], $row["line_subtotal"], '', '',$row["line_tax"],'',$row["line_subtotal"]);
                    	fputcsv($f, $fields, $delimiter);
				            }

			            }else if($row["order_item_type"] == "shipping"){
			              $shipping = $row["shipping"];
			              $html.='<tr><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Shipping</td> <td class="td-align-left">'.$row["shipping_name"].'</td> <td></td> <td></td> <td>'.$row["shipping"].'</td> <td></td> <td>'.$row["order_shipping_tax"].'</td> <td></td> <td>'.$row["shipping"].'</td></tr>';
			              if($file_type == "excel"){
				              $phpExcelSheet->setCellValue('A'.$e_row, $order_id)
				                ->setCellValue('B'.$e_row, $order_date)
				                ->setCellValue('C'.$e_row, 'Shipping')
				                ->setCellValue('D'.$e_row, $row["shipping_name"])
				                ->setCellValue('E'.$e_row, '')
				                ->setCellValue('F'.$e_row, '')
				                ->setCellValue('G'.$e_row, '')
				                ->setCellValue('H'.$e_row, '')
				                ->setCellValue('I'.$e_row, $row["shipping"])
				                ->setCellValue('J'.$e_row, '')
				                ->setCellValue('K'.$e_row, $row["order_shipping_tax"])
				                ->setCellValue('L'.$e_row, '')
				                ->setCellValue('M'.$e_row, $row["shipping"]);
				              }else if($file_type == "csv"){
				              	//csv
                    		$fields = array($order_id, $order_date, 'Shipping', $row["shipping_name"], '', '', '', '', $row["shipping"],$row["order_shipping_tax"],'',$row["shipping"]);
                    		fputcsv($f, $fields, $delimiter);
				              }
			            }else if($row["order_item_type"] == "coupon"){
			              $coupon_data = maybe_unserialize($row['discount_coupon_data']);
			              $discount_amount = $row["discount_amount"];
			              $html.='<tr><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Discount</td> <td class="td-align-left">Code: '.$coupon_data["code"].'</td> <td></td> <td></td> <td></td> <td>-'.$row["discount_amount"].'</td> <td></td> <td></td> <td>-'.$row["discount_amount"].'</td></tr>';
			              if($file_type == "excel"){
				              $phpExcelSheet->setCellValue('A'.$e_row, $order_id)
				                ->setCellValue('B'.$e_row, $order_date)
				                ->setCellValue('C'.$e_row, 'Discount')
				                ->setCellValue('D'.$e_row, $coupon_data["code"])
				                ->setCellValue('E'.$e_row, '')
				                ->setCellValue('F'.$e_row, '')
				                ->setCellValue('G'.$e_row, '')
				                ->setCellValue('H'.$e_row, '')
				                ->setCellValue('I'.$e_row, '')
				                ->setCellValue('J'.$e_row, '-'.$row["discount_amount"])
				                ->setCellValue('K'.$e_row, '')
				                ->setCellValue('L'.$e_row, '')
				                ->setCellValue('M'.$e_row, '-'.$row["discount_amount"]);
				              }else if($file_type == "csv"){
				              	//csv
		                    $fields = array($order_id, $order_date, 'Discount', $coupon_data["code"], '', '', '', '', $row["discount_amount"],'','',$row["discount_amount"]);
		                    fputcsv($f, $fields, $delimiter);
				              }
			            }
			            //echo $row["order_item_type"]."-".$e_row;
			            if($row["order_item_type"] != "tax"){
			            	$e_row++;
			            	$order_row++;
			            }
			          }
			          if($row_order['refund_amount'] > 0){

			            $html.='<tr class="refund_highlighted"><td>'.$order_id.'</td> <td>'.$order_date.'</td> <td>Refund</td> <td class="td-align-left">'.$row_order["refund_reason"].'</td> <td></td> <td></td> <td></td> <td></td> <td></td> <td>-'.$row_order["refund_amount"].'</td> <td>-'.$row_order["refund_amount"].'</td></tr>';
			            if($file_type == "excel"){
			            	$phpExcelSheet->setCellValue('A'.$e_row, $order_id)
			                ->setCellValue('B'.$e_row, $order_date)
			                ->setCellValue('C'.$e_row, 'Refund')
			                ->setCellValue('D'.$e_row, $row_order["refund_reason"])
			                ->setCellValue('E'.$e_row, '')                
			                ->setCellValue('F'.$e_row, '')
			                ->setCellValue('G'.$e_row, '')
			                ->setCellValue('H'.$e_row, '')
			                ->setCellValue('I'.$e_row, '')
			                ->setCellValue('J'.$e_row, '')
			                ->setCellValue('K'.$e_row, '')
			                ->setCellValue('L'.$e_row, '-'.$row_order["refund_amount"])
			                ->setCellValue('M'.$e_row, '-'.$row["refund_amount"]);
			              }else if($file_type == "csv"){
			              	//csv
	                    $fields = array($order_id, $order_date, 'Refund', $row_order["refund_reason"], '', '', '', '', '','',$row_order["refund_amount"],$row["refund_amount"]);
	                    fputcsv($f, $fields, $delimiter);
			              }            
			            $e_row++;
			            $order_row++;
			          }

			          $m_start=($e_row) - $order_row;
			          $m_end=($e_row-1);
			          if($file_type == "excel"){
				          $phpExcelSheet->mergeCells('N'.$m_start.':N'.$m_end);
				          $phpExcelSheet->mergeCells('O'.$m_start.':O'.$m_end);
				          $phpExcelSheet->mergeCells('P'.$m_start.':P'.$m_end);
				          $phpExcelSheet->mergeCells('Q'.$m_start.':Q'.$m_end);
				          $phpExcelSheet->mergeCells('R'.$m_start.':R'.$m_end);
				          $phpExcelSheet->mergeCells('S'.$m_start.':S'.$m_end);
				          $phpExcelSheet->setCellValue('N'.$m_start, $user_id)
				            ->setCellValue('O'.$m_start, $billing_email)
				            ->setCellValue('P'.$m_start, $order_status)
				            ->setCellValue('Q'.$m_start, $qty)
				            ->setCellValue('R'.$m_start, $order_tax)
				            ->setCellValue('S'.$m_start, $order_total);
				        }else if($file_type == "csv"){

				        }
			        }
			      }
			      if (!file_exists(WP_CONTENT_DIR.'/upgrade/'.WC_ORDER_REPOSTS_PLUGIN)) {
						  mkdir(WP_CONTENT_DIR.'/upgrade/'.WC_ORDER_REPOSTS_PLUGIN, 0777, true);
						}
			      if($file_type == "excel"){
			      	//$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
			      	$writer = new Xlsx($phpExcel);
				      //$current_user = wp_get_current_user();				    
				      $file_name = 'wc-order-report-u-'.$current_user->ID.'.xlsx';
				      $writer->save(WP_CONTENT_DIR.'/upgrade/'.WC_ORDER_REPOSTS_PLUGIN.'/'.$file_name);
				    }else if($file_type == "csv"){

				    }
			      
			      $html .='<tbody><tfoot><tr> <th>Order ID</th><th>Date</th><th>Row Type</th> <th class="td-align-left">Item Name</th> <th>Qty</th> <th>Amount</th> <th>Shipping</th> <th>Discount</th> <th>Taxes</th> <th>Returns</th> <th>Total</th> </tr></tfoot></table>';
				    $file_url = content_url().'/upgrade/'.WC_ORDER_REPOSTS_PLUGIN.'/'.$file_name;
				    if($file_type == "excel"){
				    	$file_name= 'wc-report-date-'.sanitize_title(date("M-d-Y")).'.xlsx';
				    }else if($file_type == "csv"){
				    	$file_name= 'wc-report-date-'.sanitize_title(date("M-d-Y")).'.csv';
				    }
				    $data = array('error' => false,'order_result' => $html,'file_url'=>$file_url,'file_name'=>$file_name);
			    }else{
			    	$html = "No order data available.";
					  $data = array('error' => true,'order_result' => $html);
			    }	    
			  }else{
			    $html = "Something went wrong. Selected date range not fetch order data.";
			    $data = array('error' => true,'order_result' => $html);
			  }
			}else{
				echo "You admin security nonce is not verified.";
			}
		  echo wp_send_json($data);
		  wp_die();	  
		}
	}
endif; // class_exists
new WC_Order_Ajax_Helper();