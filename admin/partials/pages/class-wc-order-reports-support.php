<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Order_Report_For_Woocommerce
 * @package    Order_Report_For_Woocommerce/admin/partials
 * Order_Report_For_Woocommerce
 */

if(!defined('ABSPATH')){
  exit; // Exit if accessed directly
}
if(!class_exists('WC_Order_Support')){
  class WC_Order_Support extends WC_Order_Helper{
    public function __construct( ) {
      $this->req_int();
      $this->load_html();
    }
    public function req_int(){
    }
    protected function load_html(){
      $this->page_html();
    }
    /**
     * Page HTML
     **/
    protected function page_html(){
      //wp_redirect($this->get_support_page_link());
      ?>
      <script type="text/javascript">          
       var a = document.createElement("a");
        a.href = "<?php echo esc_url_raw($this->get_support_page_link()); ?>";
        document.body.appendChild(a);
        a.click();
        a.remove();        
      </script>
      <?php
      exit;
    }

  }
}