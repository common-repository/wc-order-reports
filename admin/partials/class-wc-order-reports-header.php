<?php
/**
 * @since      1.1.0
 * Description: Header Section for Order Reports for WooCommerce
 */
if ( ! class_exists( 'WC_Order_Header' ) ) {
	class WC_Order_Header extends WC_Order_Helper{
		protected $is_pro_version;
		public function __construct( ){
			$this->site_url = "admin.php?page=";
			$this->is_pro_version = $this->wcor_is_pro_version();
			add_action('wc_order_header',array($this, 'before_start_header'));
			//add_action('wc_order_header',array($this, 'header_notices'));
			add_action('wc_order_header',array($this, 'page_header'));
			add_action('wc_order_header',array($this, 'header_menu'));
		}	
		
		/**
     * before start header section
     *
     * @since    1.1.0
     */
		public function before_start_header(){
			?>
			<div class="wc-order-reports">
			<?php
		}
		/**
     * header notices section
     *
     * @since    1.1.0
     */
		public function header_notices(){
			?>
			<div class="top_bar">
        <div class="pmw_container">
        </div>
      </div>
			<?php
		}
		/**
     * header section
     *
     * @since    1.1.0
     */
		public function page_header(){			
			?>
			<main>
				<div class="pmw_container-header">
					<?php if(!$this->is_pro_version){?>
						<section class="hero-section">
	    				<section class="hero-section-logo">
	    					<a target="_blank" href="<?php echo esc_url_raw($this->get_price_plan_link());?>?utm_source=Plugin+WordPress+Screen&utm_medium=Top+Logo+Img&m_campaign=Upsell+at+Order+Report+Plugin" class=""><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/logo.png"); ?>" alt="rate-us" /></a>
	    				</section>
			      	<section class="hero-section-banner">			  					    
								<div class="hero-caption">
								  <h1><?php echo esc_attr__('Upgrade to the Pro Plan to unlock all reports and access enhanced charts.', 'wc-order-reports'); ?></h1>						  
								</div>
						    <div class="pmw-top-pro-btn">
						    	<a class="pmw_btn pmw_btn-light-default-pro" target="_blank" href="<?php echo esc_url_raw($this->get_price_plan_link());?>"><?php echo esc_attr__('Upgrade Pro', 'wc-order-reports'); ?></a>
						    </div>					  				
							</section>
						</section>
					<?php }?>
					<div class="pmw_rate_us_header">
	        	<a class="pmw-rate-us" href="https://wordpress.org/support/plugin/wc-order-reports/reviews/" target="_blank"><?php echo esc_attr__('add your review! ', 'wc-order-reports'); ?><img src="<?php echo esc_url_raw(WC_ORDER_REPOSTS_PLUGIN_URL."/admin/images/rate-us.png"); ?>" alt="rate-us" /></a>
	        </div>
				</div>
				<section class="pmw_section-tabbing">
        	<div class="pmw_container">
			<?php
		}

		/* add active tab class */
	  protected function is_active_menu($page=""){
	      if($page!="" && isset($_GET['page']) && sanitize_text_field($_GET['page']) == $page){
	          return "active";
	      }
	      return ;
	  }
	  /**
     * header section
     *
     * @since    1.1.0
     */
	  public function menu_list(){
	  	//slug => arra();
	  	$menu_list = array(
	  		'wc-order-reports' => array('icon'=>'','css-icon'=>'pmw_icon-overview','acitve_icon'=>'','title'=>'Order Overview'),
	  		'wc-order-reports-download'=>array('icon'=>'','css-icon'=>'pmw_icon-download','acitve_icon'=>'','title'=>'Download'),
	  		'wc-order-reports-chart'=>array('icon'=>'','css-icon'=>'pmw_icon-chart','acitve_icon'=>'','title'=>'Chart'),
	  		'wc-order-reports-account'=>array(
	  			'title'=>__('Account', 'wc-order-reports'),
	  			'css-icon'=>'pmw_icon-account',
	  			'icon'=>'',
	  			'acitve_icon'=>''
	  		),'wc-order-reports-support'=>array(
	  			'title'=>__('Support', 'wc-order-reports'),
	  			'icon'=>'im_icon im_icon-support',
	  			'css-icon'=>'pmw_icon-support',
	  			'acitve_icon'=>''
	  		)
	  		 );
	  	return apply_filters('wc_order_menu_list', $menu_list, $menu_list);
	  }
		/**
     * header menu section
     *
     * @since    1.1.0
     */
		public function header_menu(){
			$menu_list = $this->menu_list();
			if(!empty($menu_list)){
				?>
				<div class="pmw_main-top-menu pmw_d-flex pmw_justify-content-beetween align-items-center">
      		<ul class="pmw_main-tab-list">
						<?php
						foreach ($menu_list as $key => $value) {
							if(isset($value['title']) && $value['title']){
								$is_active = $this->is_active_menu($key);
								$icon = "";
								if(!isset($value['icon']) && !isset($value['acitve_icon'])){
									$icon = Order_Report_For_Woocommerce_URL.'/admin/images/'.$key.'-menu.png';					
									if($is_active == 'active'){
										$icon = Order_Report_For_Woocommerce_URL.'/admin/images/'.$is_active.'-'.$key.'-menu.png';
									}
								}else{
									$icon = (isset($value['icon']))?$value['icon']:((isset($value['acitve_icon']))?$value['acitve_icon']:"");
									if($is_active == 'active' && isset($value['acitve_icon'])){
										$icon =$value['acitve_icon'];
									}
								}
								?>
								<li class="pmw_main-tab-item">
	              <a href="<?php echo esc_url_raw($this->site_url.$key); ?>" class="pmw_main-tab-link <?php echo esc_attr($is_active); ?>">
	              	<?php if( isset($value['css-icon']) && $value['css-icon'] ){?>
	              		<i class="pmw_icon <?php echo esc_attr($value['css-icon']); ?>"></i>
	              	<?php }else if($icon!=""){?>
	                	<span class="navinfoicon"><img src="<?php echo esc_url_raw($icon); ?>" /></span>
	              	<?php } ?>
	                <span class="navinfonavtext"><?php echo esc_attr($value['title']); ?></span>
	              </a>
		          </li>
								<?php	
							}
						}?>
						</ul>			
					</nav>
				</div>
				<div class="pmw_section-tab-box">
				<?php
			}
			
		}

	}
}
new WC_Order_Header();