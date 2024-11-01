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
if(!class_exists('WC_Order_Download')):
	require_once( 'helper/class-wc-order-download-helper.php');
	class WC_Order_Download extends WC_Order_Download_Helper{
    protected $version;
		public function __construct( ) {
			$this->req_int();
      $this->load_html();
      $this->version = Wc_Order_Reports_VERSION;
		}

		public function req_int(){
			wp_enqueue_style('wor-jquery-ui-style', WC_ORDER_REPOSTS_PLUGIN_URL.'/admin/css/jquery-ui.css');    
      wp_register_style('tvc-dataTables-css', WC_ORDER_REPOSTS_PLUGIN_URL.'/admin/css/dataTables.bootstrap4.min.css');
      wp_enqueue_style('tvc-dataTables-css');
      wp_enqueue_script( 'tvc-ee-dataTables-js', WC_ORDER_REPOSTS_PLUGIN_URL . '/admin/js/jquery.dataTables.min.js', array( 'jquery' ), $this->version, false );
      wp_enqueue_script( 'tvc-ee-dataTables-1-js', WC_ORDER_REPOSTS_PLUGIN_URL . '/admin/js/dataTables.bootstrap4.min.js', array( 'jquery' ), $this->version, false );
      wp_enqueue_script('jquery-ui-datepicker');			
		}

		public function load_html(){
			
			$this->current_html();
			
		}

		public function current_html(){
      $current = current_time( 'timestamp' );
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
                  <span class="wor-filters-date__button"><button type="button" id="wor-order-report-btn" class="pmw_btn pmw_btn-fill wor-order-re-genrate-button">Show</button></span>
	                 
                  <div class="wor-download-btn-sec">
                    <select name="file_type" id="file_type">
                      <option value="excel">Excel</option>
                      <option value="csv">csv</option>
                    </select>
                    <span class="wor-filters-date__button"><button type="button" id="wor-order-report-excel" class="pmw_btn pmw_btn-fill wor-order-re-genrate-button disabled" disabled>Download</button></span><strong><?php echo $this->display_proplan_with_link("Upgrade to PRO"); ?></strong>
                  </div>
								</form>	
								<div id="wor-date-range-msg"></div>					
							</div>
						</div>
            <hr role="presentation">
						<div class="wor-order-report-section">
							<div class="wor-order-report-data" id="wor-order-report-data">
							</div>
						</div>						
  				</div>
  			</div>    		
			</div>
			<script type="text/javascript">
    	(function($){
    		jQuery(document).ready(function(){
          jQuery("#wor-order-report-btn").trigger("click");
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
            action: 'wc_order_show_data',
            //file_type:file_type,
            start_date: start_date,
            end_date: end_date,
            wc_order_nonce: '<?php echo wp_create_nonce( 'wc_order_show_data_nonce' ); ?>'
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
          $("#wor-order-report-data").html("");
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
              }else if(response.order_result){
                $("#wor-order-report-data").hide();
                $("#wor-order-report-data").html(response.order_result);

                $("#order-data-rs").DataTable({
                  "initComplete": function(settings, json) {
                    $("#wor-order-report-data").show();
                    $thisbutton.removeClass('loading');
                    $thisbutton.prop('disabled', false);
                  },
                  "aoColumnDefs": [
                      { "bSortable": false, "aTargets": [ 5, 6, 7, 8, 9 ] }, 
                      { "bSearchable": false, "aTargets": [ 5, 6, 7, 8, 9 ] }
                    ],
                  "order": [[ 0, "desc" ]],
                  "lengthMenu": [[100, 200, 300, 500, 1000, -1], [100, 200, 300, 500, 1000, "All"]]
                });
              }
            },
          });
          /*end ajax*/
				});

        /*start excel download*/
        jQuery("#wor-order-report").on('click', '#wor-order-report-excel', function (e) {
          e.preventDefault();
          $('#wor-date-range-msg').html("");          
          var $thisbutton = $(this),
            $form = $thisbutton.closest('form.wor-date-range'),
            file_type = $form.find('select[name=file_type]').val() || "excel",          
            start_date = $form.find('input[name=start_date]').val() || 0,
            end_date = $form.find('input[name=end_date]').val() || 0;

          var data = {
            action: 'wc_order_download_data',
            file_type: file_type,
            start_date: start_date,
            end_date: end_date,
            wc_order_nonce: '<?php echo wp_create_nonce( 'wc_order_download_data_nonce' ); ?>',          
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
          $("#wor-order-report-data").html("");
          $.ajax({
            type: 'post',
            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
            data: data,
            beforeSend: function (response) {
              $thisbutton.addClass('loading'); 
              $thisbutton.prop('disabled', true);                 
            },
            complete: function (response) {              
            },
            success: function (response) {
              if (response.error){
                $('#wor-date-range-msg').html('<div class="error"><p>'+response.order_result+'</p></div>');
                $thisbutton.removeClass('loading');
                $thisbutton.prop('disabled', false); 
                return false;
              }else if(response.order_result){
                $("#wor-order-report-data").hide();
                $("#wor-order-report-data").html(response.order_result);                
                $("#order-data-rs").DataTable({
                  "initComplete": function(settings, json) {
                    $("#wor-order-report-data").show();
                    $thisbutton.removeClass('loading'); 
                    $thisbutton.prop('disabled', false);                  
                  },
                  "aoColumnDefs": [
                    { "bSortable": false, "aTargets": [ 5, 6, 9 ] }, 
                    { "bSearchable": false, "aTargets": [ 5, 6, 7, 8, 9 ] }
                  ],
                  "order": [[ 0, "desc" ]],
                  "lengthMenu": [[100, 200, 300, 500, 1000, -1], [100, 200, 300, 500, 1000, "All"]]
                });
                
                if(response.file_url){
                  setTimeout(function(){
                    //window.open(response.file_url,"mywin");
                     var a = document.createElement("a");
                      a.href = response.file_url;                       
                      a.download = response.file_name;
                      document.body.appendChild(a);
                      a.click();
                      a.remove();
                  }, 1000);
                }
              }              
            },
          });
          /*end ajax*/
        });
        /*end excel*/
			})(jQuery);
    	</script>
			<?php
		}
	}
endif; // class_exists