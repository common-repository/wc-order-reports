<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Order_Report_For_Woocommerce
 * @package    Wc_Order_Reports/admin/partials
 * Order Report for WooCommerce
 */

if(!defined('ABSPATH')){
	exit; // Exit if accessed directly
}
if(!class_exists('WC_Order_Chart')):
  require_once( 'helper/class-wc-order-helper.php');
	class WC_Order_Chart extends WC_Order_Helper{
    protected $WC_Order_DB_Helper;
    public function __construct( ) {
      $this->WC_Order_DB_Helper = new WC_Order_DB_Helper();
      $this->req_int();
      $this->load_html();
    }

		public function req_int(){
      wp_enqueue_style('wor-jquery-ui-style',  WC_ORDER_REPOSTS_PLUGIN_URL.'/admin/css/jquery-ui.css');     
      wp_enqueue_script('jquery-ui-datepicker');
      wp_enqueue_script( 'wor-chart-js', WC_ORDER_REPOSTS_PLUGIN_URL.'/admin/js/chart.js', array( 'jquery' ) );
      wp_enqueue_script( 'wor-chart-plugin-table-js', WC_ORDER_REPOSTS_PLUGIN_URL.'/admin/js/chartjs-plugin-datalabels.js', array( 'jquery' ) );
		}

		public function load_html(){
      $this->current_html();
      $this->current_js();
    }
    /**
     * Page custom js code
     *
     * @since    1.0.0
     */
    public function current_js(){
      ?>
      <script type="text/javascript">
      (function($){
        jQuery(document).ready(function(){
          var is_rtl = '<?php echo is_rtl(); ?>';
          var global_chart_json = <?php echo $this->get_ChartAttributes(); ?>;

          jQuery('.start_date').datepicker({            
            dateFormat : 'M-dd-yy',
            maxDate: '0'            
          }); 

          jQuery('.end_date').datepicker({
            dateFormat : 'M-dd-yy',
            maxDate: '0'          
          });

          var $form = jQuery("#wor-order-report-btn").closest('form.wor-date-range'),
              start_date = $form.find('input[name=start_date]').val() || 0,
              end_date = $form.find('input[name=end_date]').val() || 0;
          var data = {
            action:'get_wcorder_reports_chart',                
            plugin_url:'<?php echo WC_ORDER_REPOSTS_PLUGIN_URL; ?>',
            start_date :start_date,
            end_date :end_date,
            global_chart_json: global_chart_json,
            wc_order_nonce: '<?php echo wp_create_nonce( 'get_wcorder_reports_chart_nonce' ); ?>'
          };
          wcorder_helper.get_sales_report_analysis(data);

          jQuery("#wor-order-report").on('click', '#wor-order-report-btn', function (e) {
            e.preventDefault();
            $('#wor-date-range-msg').html("");
            //$("#hup-free-product-msg").html("");
            var $thisbutton = $(this),
              $form = $thisbutton.closest('form.wor-date-range'),            
              start_date = $form.find('input[name=start_date]').val() || 0,
              end_date = $form.find('input[name=end_date]').val() || 0;

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
                      
            var data = {
                action:'get_wcorder_reports_chart',                
                plugin_url:'<?php echo WC_ORDER_REPOSTS_PLUGIN_URL; ?>',
                start_date :start_date,
                end_date :end_date,
                global_chart_json: global_chart_json,
                wc_order_nonce: '<?php echo wp_create_nonce( 'get_wcorder_reports_chart_nonce' ); ?>'
              };
              wcorder_helper.get_sales_report_analysis(data);
            
          });
        });
      })(jQuery);
      </script>
      <?php
    }
    public function current_html(){
      $start_date = date( 'M-d-Y', strtotime( '-1 month' ));
      $today_date = date( 'M-d-Y', strtotime( 'now' ));
      ?>
      <div class="wor-contener wor-sales-analysis-wrap">        
        <div class="wor-layout" id="wor-order-report">
          <div class="wor-main-section">
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
            <div class="wor-order-report-section">
              <div class="wor-sec" id="wor-sales-rep-sec-1">
                <h2 class="wor-main-section-header__title wor-main-section-header__header-item icon icon-cart"><?php _e("Sales performance","wc-order-reports"); ?></h2>
                <hr role="presentation">
                <div class="product-card"  id="product-card-1">
                  <div class="row row-cols-1">
                    <div class="col" >
                      <div class="card">
                        <div>
                          <div id="s1_total_sale">
                            <h3 class="pro-count sales-smry-value">-</h3>
                            <p class="pro-title sales-smry-title"><?php _e("Total sales","wc-order-reports"); ?></p> 
                          </div>
                          <div id="s1_net_sale">
                            <h3 class="pro-count sales-smry-value">-</h3>
                            <p class="pro-title sales-smry-title"><?php _e("Net sales","wc-order-reports"); ?></p>
                          </div>
                        </div>
                        <div class="total-sale-chart" id="s1_total_sale_chart">
                          <canvas id="total_sale_chart" width="400" height="200"></canvas>
                        </div>                     
                      </div>                      
                    </div>
                  </div>
                  <div class="row row-cols-2">
                    <div class="col">
                      <div class="card total_orders" id="s1_total_orders">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Total order","wc-order-reports"); ?></p>
                        <div class="total-orders-chart" id="s1_total_orders_chart">
                          <canvas id="total_orders_chart" width="400" height="200"></canvas>
                        </div>                        
                      </div>
                    </div>
                    <div class="col">
                      <div class="card average_order_value" id="s1_average_order_value">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Average order value","wc-order-reports"); ?></p>
                        <div class="average-order-value-chart" id="s1_average_order_value_chart">
                          <canvas id="average_order_value_chart" width="400" height="200"></canvas>
                        </div>                     
                      </div>
                    </div>
                    <div class="col">
                      <div class="card refund-order" id="s1_refund_order">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Refund orders","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p>
                        <div class="refund-order-chart" id="s1_refund_order_chart">
                          <canvas id="refund_order_chart" width="400" height="200"></canvas>
                        </div>                        
                      </div>
                    </div>
                    <div class="col">
                      <div class="card refund_order_value" id="s1_refund_order_value">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Refund amount","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p> 
                        <div class="refund-order-value-chart" id="s1_refund_order_value_chart">
                          <canvas id="refund_order_value_chart" width="400" height="200"></canvas>
                        </div>                       
                      </div>
                    </div>
                    <div class="col">
                      <div class="card discount_amount" id="s1_discount_amount">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Discount amount","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p>
                        <div class="discount-amount-chart" id="s1_discount_amount_chart">
                          <canvas id="discount_amount_chart" width="400" height="200"></canvas>
                        </div>                      
                      </div>
                    </div>
                    <div class="col">
                      <div class="card total_tax" id="s1_total_tax">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Total TAX","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p>
                        <div class="total-tax-chart" id="s1_total_tax_chart">
                          <canvas id="total_tax_chart" width="400" height="200"></canvas>
                        </div>                       
                      </div>
                    </div>
                    <div class="col">
                      <div class="card order_tax" id="s1_order_tax">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Order TAX","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p>
                        <div class="order-tax-chart" id="s1_order_tax_chart">
                          <canvas id="order_tax_chart" width="400" height="200"></canvas>
                        </div>                        
                      </div>
                    </div>
                    <div class="col">
                      <div class="card shipping_tax" id="s1_shipping_tax">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Shipping TAX","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p>
                        <div class="shipping-tax-chart" id="s1_shipping_tax_chart">
                          <canvas id="shipping_tax_chart" width="400" height="200"></canvas>
                        </div>                       
                      </div>
                    </div>
                    <div class="col">
                      <div class="card shipping"  id="s1_shipping">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Total shipping","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p>
                        <div class="shipping-chart" id="s1_shipping_chart">
                          <canvas id="shipping_chart" width="400" height="200"></canvas>
                        </div>                       
                      </div>
                    </div>
                    <div class="col">
                      <div class="card">
                        <div>
                          <div id="s1_total_users">
                            <h3 class="pro-count sales-smry-value">-</h3>
                            <p class="pro-title sales-smry-title"><?php _e("Total users","wc-order-reports"); ?></p>
                          </div>
                          <div id="s1_unique_users">
                            <h3 class="pro-count sales-smry-value">-</h3>
                            <p class="pro-title sales-smry-title"><?php _e("Unique users","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p>
                          </div>
                        </div>                        
                        <div class="total-users-chart" id="s1_total_users_chart">
                          <canvas id="total_users_chart" width="400" height="200"></canvas>
                        </div>                       
                      </div>
                    </div>
                    <div class="col">
                      <div class="card order_status" id="s1_order_status">
                        <h3 class="pro-count sales-smry-value">-</h3>
                        <p class="pro-title sales-smry-title"><?php _e("Order Status","wc-order-reports"); ?><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong></p>
                        <div class="order-status-chart" id="s1_order_status_chart">
                          <canvas id="order_status_chart" width="400" height="200"></canvas>
                        </div>                        
                      </div>
                    </div>                    

                  </div>
                </div>
              </div>
            </div>            
          </div>
        </div>        
      </div>
      <?php
    }
	}
endif; // class_exists