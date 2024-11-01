<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Wc_Order_Reports
 * @subpackage Wc_Order_Reports/admin/partials
 * Order Reports for WooCommerce
 */

if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}
if(!class_exists('WC_Order_Dashboard')):
	require_once( 'helper/class-wc-order-dashboard-helper.php');
	class WC_Order_Dashboard extends WC_Order_Dashboard_Helper{
    protected $WC_Order_DB_Helper;
		public function __construct( ) {
      $this->WC_Order_DB_Helper = new WC_Order_DB_Helper();
			$this->req_int();
      $this->load_html();
		}

		public function req_int(){
			wp_enqueue_style('wor-jquery-ui-style',  WC_ORDER_REPOSTS_PLUGIN_URL.'/admin/css/jquery-ui.css');			
      wp_enqueue_script('jquery-ui-datepicker');
		}

		public function load_html(){
			$this->current_html();
		}

		public function current_html(){
      $current = current_time( 'timestamp' );
      $start_date = date( 'Y-m-d', strtotime( '-1 month' ));
      $today_date = date( 'Y-m-d', strtotime( 'now' ));
      $result = $this->WC_Order_DB_Helper->get_dashboard_data($start_date, $today_date);
      $summury = isset($result['summury'])?$result['summury']:"";
      $currency = $this->get_woocommerce_currency_symbol();
      $start_date = date( 'M-d-Y', strtotime( '-1 month' ));
      $today_date = date( 'M-d-Y', strtotime( 'now' ));
			?>
			<div class="wor-page-contener wor-report">				
				<div class="wor-layout" id="wor-order-report">
					<div class="wor-section">
						<div class="wor-section-header">					
							<div class="worwoocommerce order-date-select">
								<label class="wor-field-title">Orders date range:</label>
								<form class="wor-date-range">
									<span><input type="text" name="start_date" value="<?php echo esc_attr($start_date); ?>" placeholder="Start Date" class="example-datepicker start_date" /></span> - 
									<span><input type="text" name="end_date" value="<?php echo esc_attr($today_date); ?>" placeholder="End Date" class="example-datepicker end_date" /></span>
									<span class="wor-filters-date__button"><button type="button" id="wor-order-report-btn" class="pmw_btn pmw_btn-fill wor-order-re-genrate-button">Show Data</button></span>
								</form>	
								<div id="wor-date-range-msg"></div>					
							</div>
						</div>
            <hr role="presentation">
						<div class="wor-order-report-dashboard-section">
							<div class="wor-dashboard-sec" id="wor-dashboard-sec-1">
                <h2 class="wor-section-header__title wor-section-header__header-item icon icon-cart">Order performance</h2>
                <hr role="presentation">
                <div class="product-card"  id="product-card-1">
                  <div class="row row-cols-5">
                    <div class="col">
                      <div class="card">
                        <h3 class="pro-count" id="total_sale"><?php echo $this->get_val_from_obj($summury, 'total_sale', $currency); ?></h3>
                        <p class="pro-title">Total sale</p>                      
                      </div>
                    </div>
                    <div class="col">
                      <div class="card">
                        <h3 class="pro-count" id="net_sel"><?php echo $this->get_val_from_obj($summury, 'net_sale', $currency); ?></h3>
                        <p class="pro-title">Net sale</p>                      
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
                        <p class="pro-title">Average order value</p>                        
                      </div>
                    </div>
                    <div class="col">
                      <div class="card approved">
                        <h3 class="pro-count" id="returns"><?php echo $this->get_val_from_obj($summury, 'refund_order'); ?></h3>
                        <p class="pro-title">Refund orders</p>                        
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
                        <p class="pro-title">Total shipping</p>                        
                      </div>
                    </div>
                  </div>
                </div>
							</div>
              <div class="wor-dashboard-sec mt40 wor-dashboard-sec-2" id="wor-dashboard-sec-2">
                <h2 class="wor-section-header__title wor-section-header__header-item icon icon-status">Order status wise performance <?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></h2>
                <hr role="presentation">
                <div class="product-card" id="product-card-2">
                  <div class="row row-cols-4">
                    <div class="col">
                      <div class="card pending">
                        <h3 class="pro-count"><?php echo __('On hold', 'wc-order-reports' ); ?><span>(<?php echo $this->get_val_from_obj($summury, 'wc_on_hold'); ?>)</span></h3>
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
              </div>
						</div>						
  				</div>
  			</div>    		
			</div>
			<script type="text/javascript">
    	(function($){
    		jQuery(document).ready(function(){
				  jQuery('.start_date').datepicker({				  	
				  	dateFormat : 'M-dd-yy',
				  	maxDate: '0'				  	
				  }); 

				  jQuery('.end_date').datepicker({
				  	dateFormat : 'M-dd-yy',
				  	maxDate: '0'			  	
				  }); 
				});
				jQuery("#wor-order-report").on('click', '#wor-order-report-btn', function (e) {
					e.preventDefault();
					$('#wor-date-range-msg').html("");
          //$("#hup-free-product-msg").html("");
          var $thisbutton = $(this),
            $form = $thisbutton.closest('form.wor-date-range'),            
            start_date = $form.find('input[name=start_date]').val() || 0,
            end_date = $form.find('input[name=end_date]').val() || 0;

          var data = {
            action: 'wc_order_dashboard_data',
            start_date: start_date,
            end_date: end_date,
            wc_order_nonce: '<?php echo wp_create_nonce( 'wc_order_dashboard_data_nonce' ); ?>'
          }; 
          if(start_date <=0){
          	$('#wor-date-range-msg').html('<div class="error"><p>Start Date is required.</p></div>');
          	return false;
          }else if(end_date <=0){
          	$('#wor-date-range-msg').html('<div class="error"><p>End Date is required.</p></div>');
          	return false;
          }else if(new Date(start_date) > new Date(end_date)){
          	$('#wor-date-range-msg').html('<div class="error"><p>End Date is always bigger than start date.</p></div>');
          	return false;
          }
          /*start ajax*/
          $.ajax({
            type: 'post',
            dataType : "json",
            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
            data: data,
            beforeSend: function (response) {
              $thisbutton.removeClass('added').addClass('loading');
              $thisbutton.prop('disabled', true);                
            },
            complete: function (response) {                            
            },
            success: function (response) {
              //console.log(response);
              if (response.error){
                $('#wor-date-range-msg').html('<div class="error"><p>'+response.order_result+'</p></div>');
                $thisbutton.removeClass('loading');
                $thisbutton.prop('disabled', false);
                return false;
              }else{
                if(response.sec_1){
                  $("#product-card-1").html(response.sec_1); 
                } 
                if(response.sec_2){
                  $("#product-card-2").html(response.sec_2); 
                }  
                $thisbutton.removeClass('loading');
                $thisbutton.prop('disabled', false);             
              }
            },
          });
          /*end ajax*/
				});
			})(jQuery);
    	</script>
			<?php
		}
	}
endif; // class_exists