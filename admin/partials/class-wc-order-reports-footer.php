<?php
/**
 * @since      1.1.0
 * Description: Footer Section for Order Reports for WooCommerce
 */
if ( ! class_exists( 'WC_Order_Footer' ) ) {
	class WC_Order_Footer {	
		public function __construct( ){
			add_action('wc_order_footer',array($this, 'before_end_footer'));
		}	
		public function before_end_footer(){ 
			?>
							</div>
						</div>
					</section>
				</main>
				<div id="pmw_form_message" class="toaster-bottom"></div>
				<div id="pmw_loader"></div>
			</div>
			<?php
		}
	}
}
new WC_Order_Footer();