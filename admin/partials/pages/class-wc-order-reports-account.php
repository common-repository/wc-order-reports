<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Order_Report_For_Woocommerce
 * @package    Order_Report_For_Woocommerce/admin/partials
 * Order Reports for WooCommerce
 */

if(!defined('ABSPATH')){
  exit; // Exit if accessed directly
}
if(!class_exists('WC_Order_Account')){
  require_once( WC_ORDER_REPOSTS_PLUGIN_DIR . 'admin/partials/helper/class-wc-order-setting-helper.php');
  class WC_Order_Account extends WC_Order_Helper{
    protected $is_pro_version;
    protected $license_key;
    protected $disp_license_key;
    protected $store_data;
    protected $api_store;
    protected $plan_name;
    protected $SettingHelper;
    public function __construct( ) {
      $this->SettingHelper = new Wc_Order_Reports_SettingHelper();
      $this->api_store = (object)$this->get_wcor_api_store();
      $this->is_pro_version = $this->wcor_is_pro_version($this->api_store);
      $this->plan_name = $this->get_plan_name($this->api_store);           
      $this->req_int();
      $this->load_html();
    }
    public function req_int(){
    }
    protected function load_html(){
      $this->page_html();
      $this->page_js();
    }

    /**
     * Page HTML
     **/
    protected function page_html(){
      //echo $this->get_store_id();
      $current_user = wp_get_current_user();
      $wcor_option = $this->get_wcor_option();
      $email_id = isset($wcor_option['user']['email_id'])?$wcor_option['user']['email_id']:$current_user->user_email;
      $privecy_policy = isset($wcor_option['privecy_policy']['privecy_policy'])?$wcor_option['privecy_policy']['privecy_policy']:"";
      $is_theme_plugin_list = isset($wcor_option['privecy_policy']['is_theme_plugin_list'])?$wcor_option['privecy_policy']['is_theme_plugin_list']:"0";
      ?>      
      <?php
      if(!$this->is_pro_version){ 
        $fields = [ 
          "section_account" => [    
            [
              "type" => "section",
              "label" => __("Connect Account", "wc-order-reports"),
              "class" => "google_section_setting",
            ]
          ],
          "user" => [    
            [
              "type" => "text",
              "label" => __("Email Id", "wc-order-reports"),
              "name" => "email_id",
              "id" => "email_id",
              "value" => $email_id,
              "placeholder" => __("Enter Your Email", "wc-order-reports"),
              "class" => "email_id",
              "tooltip" =>[
                "title" => __("Enter your email.", "wc-order-reports")
              ]
            ]
          ], 
          "hidden" => [
            [
              "type" => "hidden",
              "name" => "privecy_policy",
              "id" => "privecy_policy",
              "value" => $privecy_policy
            ],[
              "type" => "hidden",
              "name" => "is_theme_plugin_list",
              "id" => "is_theme_plugin_list",
              "value" => $is_theme_plugin_list
            ],[
              "type" => "hidden",
              "id" => "pixels_save_action",
              "name" => "action",
              "value" => "wcor_check_privecy_policy"
            ]
          ],     
          "button" => [
              [
                "type" => "button",
                "name" => "pixels_save",
                "id" => "pixels_save",
                "class" => "pixels_save"
              ]
            ]
        ];
        $form = array("name" => "save-wcor-account-settings", "id" => "save-wcor-account-settings", "method" => "post");
        $this->SettingHelper->add_form_fields($fields, $form);
      } ?>
      <div class="pmw_form-wrapper active pmw_form-row rm-b pmw-account" id="sec-pmw-pixels">
        <?php
        if(!$this->is_pro_version){
          ?>       
          <div class="pmw_form-group"> 
            <section class="hero-section-banner">                      
              <div class="hero-caption">
                <h1><?php echo esc_attr__('Upgrade to the Pro Plan to unlock all reports and access enhanced charts.', 'wc-order-reports'); ?></h1>             
              </div>
                
              <div class="pmw-top-pro-btn">
                <a class="pmw_btn pmw_btn-light-default-pro" target="_blank" href="<?php echo esc_url_raw($this->get_price_plan_link());?>"><?php echo esc_attr__('Buy Now', 'wc-order-reports'); ?></a>
              </div>                    
            </section>
          </div>        
        <?php
        }
        ?>

        <div class="plan_details">
          <?php
          if(!$this->is_pro_version){ ?>
          <?php 
            $fields = [ 
              "section_account" => [    
                [
                  "type" => "section",
                  "label" => __("Activate PRO Account", "wc-order-reports"),
                  "class" => "google_section_setting",
                ]
              ],       
              "button" => [
                [
                  "type" => "button",
                  "name" => "pmw_active_key_steps",
                  "id" => "pmw_active_key_steps_btn",
                  "label" => "How to Activate Key"
                ]
              ]
            ];
            $form = array("name" => "pmw-show-step-active-key", "id" => "pmw-show-step-active-key", "method" => "post");
            $this->SettingHelper->add_form_fields($fields, $form);
          } ?>
          <div class="pmw_form-group pmw-active-key-steps pmw-hide">
            <div class="frrtopro">
              <h3><?php esc_attr_e('To activate the License key:','wc-order-reports'); ?></h3>
              <ul>
                <li><strong>Step 1:</strong> <?php esc_attr_e('Buy the Pro plan, and disable the existing free plugin, "Order Reports for WooCommerce".','wc-order-reports'); ?></li>
                <li><strong>Step 2:</strong> <?php esc_attr_e("Activate the PRO plugin, If you missed downloading the PRO Order Reports for WooCommerce plugin during checkout, please send an email to support@growcommerce.io","wc-order-reports"); ?></li>
                <li><strong>Step 3:</strong> <?php esc_attr_e('Access the plugin\'s account page, where you\'ll discover a section for activating the license key.','wc-order-reports'); ?></li>
              </ul>
            </div>
          </div>
        </div>

        <div class="plan_details">
          <div class="pmw_form-row">
            <div class="pmw_form-group ">
              <?php
              $this->SettingHelper->add_section([
                "type" => "section",
                "label" => __("Your plan details", "wc-order-reports"),
                "class" => "plan_details_section",
              ]); ?>
            </div>
          </div>
          <div class="pmw_form-row">
            <ul class="pmw_order-info ml-2">
              <li><label><?php esc_attr_e('Plan','wc-order-reports'); ?></label><span><?php echo esc_attr($this->plan_name); ?></span></li>
              <?php if($this->is_pro_version){?>
                <li><label><?php esc_attr_e('License Key','wc-order-reports'); ?></label><span><?php echo esc_attr($this->disp_license_key); ?></span></li>
              <?php }else{?>
                <li><label><?php esc_attr_e('Upgrade to Pro','wc-order-reports'); ?></label><strong><a target="_blank" href="<?php echo esc_url_raw($this->get_price_plan_link());?>"><?php echo esc_attr__('(Upgrade to PRO)', 'wc-order-reports'); ?></a></strong></li>
              <?php } ?>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div id="pmw_privacy_popup" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
          <!-- Modal content -->
          <div class="modal-content">
            <div class="modal-header">
              <span id="close" class="close">&times;</span>
            </div>
            <div class="modal-body">
              <div class="modal-top-area">
                <div class="logo-section">
                  <div class="logo_section-img"><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/wp.png"); ?>" alt="img"></div>
                  <div class="logo_section-img"><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/logo.png"); ?>" alt="img"></div>
                </div>
              </div>
              <div class="modal-middle-area">
                <p><strong>Hey <?php echo esc_attr(get_bloginfo()); ?>,</strong></p>
               <p><?php echo esc_attr__('Never miss an important update - opt in to our security and feature updates notifications, and non-sensitive diagnostic tracking with', 'wc-order-reports'); ?> <a target="_blank" href="<?php echo esc_url_raw("https://growcommerce.io/"); ?>">GrowCommerce</a></p>
                <p><a target="_blank" href="<?php echo esc_url_raw("https://growcommerce.io/privacy-terms/"); ?>"><?php echo esc_attr__('Privacy & Terms', 'wc-order-reports'); ?></a></p>
                <div class="modal_button-area">
                  <button class="pmw_btn pmw_btn-fill" id="wcor_accept_privecy_policy"><?php echo esc_attr__('Allow & Continue', 'wc-order-reports'); ?></button>
                  <?php /*<button class="pmw_btn pmw_btn-default">Skip</button>*/ ?>
                </div>
              </div>
              <div class="modal-bottom-area">
                <h2 class="toggle_title-text"><?php echo esc_attr__('What Permissions are being Granted?', 'wc-order-reports'); ?></h2>
                <div class="pmw_slide-down-area">
                  <ul>
                    <li>
                      <div class="pmw_slide-area-image"><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/Icon-profile.png"); ?>" alt="img"></div>
                      <div class="pmw_slide-area-content">
                        <span class="pmw_slide-area-title"><?php echo esc_attr__('Your Profile Overview', 'wc-order-reports'); ?></span>
                        <p><?php echo esc_attr__('Name and email address', 'wc-order-reports'); ?></p>
                      </div>
                    </li>
                    <li>
                      <div class="pmw_slide-area-image"><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/Icon-site-overview.png"); ?>" alt="img"></div>
                      <div class="pmw_slide-area-content">
                        <span class="pmw_slide-area-title"><?php echo esc_attr__('Your Site Overview', 'wc-order-reports'); ?></span>
                        <p><?php echo esc_attr__('Site URL, country, currency, WP version, PHP info', 'wc-order-reports'); ?></p>
                      </div>
                    </li>
                    <li>
                      <div class="pmw_slide-area-image"><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/Icon-notice.png"); ?>" alt="img"></div>
                      <div class="pmw_slide-area-content">
                        <span class="pmw_slide-area-title"><?php echo esc_attr__('Admin Notice', 'wc-order-reports'); ?></span>
                        <p><?php echo esc_attr__('Updates, announcements, marketing, no spam', 'wc-order-reports'); ?></p>
                      </div>
                    </li>
                    <li>
                      <div class="pmw_slide-area-image"><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/Icon-status.png"); ?>" alt="img"></div>
                      <div class="pmw_slide-area-content">
                        <span class="pmw_slide-area-title"><?php echo esc_attr__('Current Plugin Status', 'wc-order-reports'); ?></span>
                        <p><?php echo esc_attr__('Active, deactivated, or uninstalled, settings', 'wc-order-reports'); ?></p>
                      </div>
                    </li>
                    <li>
                      <div class="pmw_slide-area-image"><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/icon-plugin.png"); ?>" alt="img"></div>
                      <div class="pmw_slide-area-content">
                        <span class="pmw_slide-area-title"><?php echo esc_attr__('Plugins & Themes', 'wc-order-reports'); ?></span>
                        <p><?php echo esc_attr__('Title, slug, version, and is active', 'wc-order-reports'); ?></p>
                      </div>
                      <div class="custom-control custom-switch">
                        <input type="checkbox" class="pmw_custom-control-input" id="ch_is_theme_plugin_list" checked>
                        <label class="pmw_custom-control-label" for="ch_is_theme_plugin_list"></label>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <svg version="1.1" class="svg-filters" style="display:none;">
        <defs>
          <filter id="marker-shape">
            <feTurbulence type="fractalNoise" baseFrequency="0 0.15" numOctaves="1" result="warp" />
            <feDisplacementMap xChannelSelector="R" yChannelSelector="G" scale="30" in="SourceGraphic" in2="warp" />
          </filter>
        </defs>
      </svg>
      <?php
      //echo $this->get_sidebar_html($this->is_pro_version, $this->plan_name);
    }
    /**
     * Page JS
     **/
    protected function page_js(){
      ?>
      <script type="text/javascript">
        jQuery("#pmw_active_key_steps_btn").on("click", function (event) {
          console.log("call");
          event.preventDefault();
          jQuery(".pmw-active-key-steps").toggleClass("pmw-hide");
        });
        (function($){ 
          jQuery(document).ready(function(){
            jQuery("#close").on("click", function () {
              wcorder_helper.close_privacy_popup();
            });
            jQuery(".toggle_title-text").on("click", function () {
              jQuery(this).toggleClass("active");
              jQuery(this).next('.pmw_slide-down-area').slideToggle();
            });
            jQuery("#sec-pmw-pixels").toggleClass("active");
            jQuery(".save-wcor-account-settings .pmw_form-control").on("focus", function () {
              if( jQuery(this).attr("id") == "google_ads_conversion_id" || jQuery(this).attr("id") == "google_ads_conversion_label"){
                jQuery(this).parent().parent().addClass("active");
              }else{
                jQuery(this).parent().parent().parent().addClass("active");
              }
            });
            jQuery(".save-wcor-account-settings .pmw_form-control").on("focusout", function (event) {
              if(jQuery(this).val() == "" && ( jQuery(this).attr("id") == "google_ads_conversion_id" || jQuery(this).attr("id") == "google_ads_conversion_label")){
                jQuery(this).parent().parent().removeClass("active");
              }else if(jQuery(this).val() == ""){
                jQuery(this).parent().parent().parent().removeClass("active");
              }
            });
            jQuery(".save-wcor-account-settings .pmw_form-control").on("input", function (event) {
              event.preventDefault();
              if(jQuery(this).val() == ""){
                jQuery(this).parent().parent().parent().removeClass("active");
                var id = jQuery(this).attr("id").replace("id","is_enable");
                jQuery("#"+id).prop('checked', false);
              }else if(jQuery(this).val() != ""){
                var id = jQuery(this).attr("id").replace("id","is_enable");
                jQuery("#"+id).prop('checked', true);                 
              }
            });
            jQuery('.save-wcor-account-settings .pmw_form-control').each(function(){
              if(jQuery(this).val() != ""){
                jQuery(this).parent().parent().parent().addClass("active");
              }
            });
          });
        })( jQuery );
      </script>
      <?php
    }
  }
}