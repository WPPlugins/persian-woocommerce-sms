<?php
class WoocommerceIR_Tab_SMS {

	private $tab_data = false;

	public function __construct() {
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'render_custom_product_tabs' ) );
		add_action( 'woocommerce_product_write_panels',     array( $this, 'product_page_hannanstd_custom_tabs_panel' ) );
		add_action( 'woocommerce_process_product_meta',     array( $this, 'product_save_data' ), 10, 2 );
	}

	public function render_custom_product_tabs() {
		echo "<li class=\"hannanstd_wc_product_tabs_tab\"><a href=\"#persian_woo_hs\">" . __( 'پیامک', 'persianwoosms' ) . "</a></li>";
	}

	public function product_page_hannanstd_custom_tabs_panel() {
		global $post;
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) { ?>
			<style type="text/css">#woocommerce-product-data ul.product_data_tabs li.hannanstd_wc_product_tabs_tab a { padding:5px 5px 5px 28px;background-repeat:no-repeat;background-position:5px 7px; }</style>
			<?php
		}
		
		$tab_data = maybe_unserialize( get_post_meta( $post->ID, '_hannanstd_woo_products_tabs', true ) );
			
		if ( empty( $tab_data ) ) {
			$tab_data['1'] = array( 'title' => '', 'content' => '' , 'duplicate' => '' );
		}			
		$i = 1;
		
		echo '<div id="persian_woo_hs" class="panel wc-metaboxes-wrapper woocommerce_options_panel">';		
		do_action( 'woocommerce_product_sms', $post->ID );
		
		
		if ( ( ps_sms_options( 'enable_product_admin_sms', 'sms_product_admin_settings', 'on' ) == 'on' ) 
			&& ( ( ps_sms_options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) ps_sms_options( 'pm_type_product_admin', 'sms_product_admin_settings', array()) ) )  ) 
			|| ( ps_sms_options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) ps_sms_options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) ) ) ) {
		
			echo '<div class="hannanstd-woo-tabs-hidden-how-to-info"><h3 style="padding-top:0;padding-bottom:0;">' . __( "راهنما !" , 'persianwoosms' ) . ':</h3>
				<p style="margin:0;padding-left:13px;">' . __( "شماره های افرادی که مایل به دریافت اطلاع فروش از طریق پیامک هستید را وارد نمایید ." , 'persianwoosms' ) . '</p> 
				<p style="margin:0;padding-left:13px;">' . __( "برای انتخاب وضعیت های دریافت پیامک نیز از دکمه Control به همراه کلیک چپ استفاده کنید ." , 'persianwoosms' ) . '</p> 
			</div>';
			echo '<div class="dashicons dashicons-editor-help hannanstd-tabs-how-to-toggle" title="' . __( "راهنمایی" , 'persianwoosms' ) . '"></div>';
													
			foreach ( $tab_data as $tab ) {
				if ( $i != 1 ) { ?>
					<section class="button-holder-sms" alt="<?php echo $i; ?>">
						<a href="#" onclick="return false;" class="button-secondary number_of_tabs_sms">
						<span class="dashicons dashicons-no-alt" style="line-height:1.3;"></span><?php echo __( 'حذف گیرنده' , 'persianwoosms' ); ?></a>
					</section>
				<?php } else { ?>
				<section class="button-holder-sms" alt="<?php echo $i; ?>"></section>
				<?php }
				woocommerce_wp_text_input( array( 'id' => '_hannanstd_wc_custom_repeatable_product_tabs_tab_title_' . $i , 'label' => __( 'شماره گیرنده', 'persianwoosms' ), 'description' => '', 'value' => $tab['title'] , 'placeholder' => 'با کاما جدا کنید' , 'class' => 'hannanstd_woo_tabs_title_field') );
				$this->woocommerce_select_status( array( 'id' => '_hannanstd_wc_custom_repeatable_product_tabs_tab_content_' . $i , 'label' => __( 'وضعیت', 'persianwoosms' ), 'placeholder' => __( '', 'persianwoosms' ), 'value' => $tab['content'], 'style' => 'width:70%;height:10.5em;' , 'class' => 'hannanstd_woo_tabs_content_field' ) );
				if ( $i != count( $tab_data ) ) { 
					echo '<div class="hannanstd-woo-custom-tab-divider"></div>';
				}
				$i++;
			}			
			?>
			<div id="duplicate_this_row_sms">
				<a href="#" onclick="return false;" class="button-secondary number_of_tabs_sms" style="float:right;margin-right:4.25em;"><span class="dashicons dashicons-no-alt" style="line-height:1.3;"></span><?php echo __( 'حذف گیرنده' , 'persianwoosms' ); ?></a>
				<?php
				woocommerce_wp_text_input( array( 'id' => 'hidden_duplicator_row_title' , 'label' => __( 'شماره گیرنده', 'persianwoosms' ), 'description' => '', 'placeholder' => 'با کاما جدا کنید' , 'class' => 'hannanstd_woo_tabs_title_field' ) );
				$this->woocommerce_select_status( array( 'id' => 'hidden_duplicator_row_content' , 'label' => __( 'وضعیت', 'persianwoosms' ), 'placeholder' => __( '', 'persianwoosms' ), 'style' => 'width:70%;height:10.5em;' , 'class' => 'hannanstd_woo_tabs_content_field' ) );
				?>
				<section class="button-holder-sms" alt="<?php echo $i; ?>"></section>
			</div>						
			<p>
				<label style="display:block;" for="_hannanstd_wc_custom_repeatable_product_tabs_tab_content_<?php echo $i; ?>"></label>
				<a href="#" class="button-secondary" id="add_another_sms_tab"><em class="dashicons dashicons-plus-alt" style="line-height:1.8;font-size:14px;"></em><?php echo __( 'افزودن گیرنده' , 'persianwoosms' ); ?></a>
			</p>
			<?php
			echo '<input type="hidden" value="' . count( $tab_data ) . '" id="number_of_tabs_sms" name="number_of_tabs_sms" >';		
			echo '<p>در صورتی که هیچ کدام از موارد اس ام اس یا تلگرام انتخاب نشوند ، به صورت پیشفرض گزینه اس ام اس انتخاب شده در نظر گرفته خواهد شد . این قسمت برای جلوگیری از تنظیم مجدد شماره مدیران محصول تنظیم شده قبل از به روز رسانی به نسخه 3.2.0 لحاظ شده است . </p>';
		
		}			
			echo '</div>';			
	}

	public function product_save_data( $_post_id = 0, $arg = '' ) {
		
		global $thepostid, $post;

		$the_post_id = $_post_id;
		
		if ( ! $the_post_id && !empty($thepostid) ) $the_post_id = $thepostid;
		
		if ( ! $the_post_id && is_object($post) ) $the_post_id = $post->ID;
		
		
		if( ps_sms_options( 'enable_product_admin_sms', 'sms_product_admin_settings', 'on' ) == 'on' ) {
			$tab_data = array();
			$number_of_tabs_sms = intval($_POST['number_of_tabs_sms']);
			$new_number_of_tab = 1;	
			$i = 1;
			$j = 1;
			for ( $i = 1; $i <= $number_of_tabs_sms; $i++ ) {	
				if ( !empty($_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_title_'.$i]) ) {
					$new_number_of_tab = $j;
					
					if ( is_array( $_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_title_'.$j] )) {
						$_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_title_'.$j] = array_map( 'sanitize_text_field' , $_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_title_'.$i]);
					}
					else {
						$_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_title_'.$j] = sanitize_text_field($_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_title_'.$i]);
					}
					
					if ( !empty( $_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_content_'.$j] )) {
						if ( is_array( $_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_content_'.$j] )) {
							$_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_content_'.$j] = array_map( 'sanitize_text_field' , $_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_content_'.$i]);
						}
						else {
							$_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_content_'.$j] = sanitize_text_field($_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_content_'.$i]);
						}
					}
					
					$j++;
				}
			}
			
			$j = 1;
			while( $j <= $new_number_of_tab ) {
				
				$tab_title = stripslashes( $_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_title_'.$j] );
				$tab_content =  isset( $_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_content_'.$j] ) ? implode( '-sv-', ( (array) $_POST['_hannanstd_wc_custom_repeatable_product_tabs_tab_content_'.$j]) ) : '';
			
				if ( empty( $tab_title ) && empty( $tab_content ) ) {
					unset( $tab_data[$j] );	
				} elseif ( !empty( $tab_title ) || !empty( $tab_content ) ) {
					$tab_id = '';
					
					if ( $tab_title ) {
						if ( strlen( $tab_title ) != strlen( utf8_encode( $tab_title ) ) ) {
							$tab_id = "tab-custom-" . $j;
						} else {
							$tab_id = strtolower( $tab_title );
							$tab_id = preg_replace( "/[^\w\s]/", '', $tab_id );
							$tab_id = preg_replace( "/_+/", ' ', $tab_id );
							$tab_id = preg_replace( "/\s+/", '-', $tab_id );
							$tab_id = 'tab-' . $tab_id;
						}
					}
					$tab_data[$j] = array( 'title' => $tab_title, 'id' => $tab_id, 'content' => $tab_content );
				}	
				$j++;	
			}
			$tab_data = array_values( $tab_data );
		
			update_post_meta( $the_post_id, '_hannanstd_woo_products_tabs', $tab_data );
		}	
	}

	public function woocommerce_select_status( $field ) {
		global $thepostid, $post;

		if ( ! $thepostid ) $thepostid = $post->ID;
		if ( ! isset( $field['placeholder'] ) ) $field['placeholder'] = '';
		if ( ! isset( $field['class'] ) ) $field['class'] = 'short';
		if ( ! isset( $field['value'] ) ) $field['value'] = get_post_meta( $thepostid, $field['id'], true );

		echo '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		echo '<select multiple="multiple" class="' . $field['class'] . '" name="' . $field['id'] . '[]" id="' . $field['id'] . '" ' .  '>';
			
			$selected_statuses = isset($field['value']) ? explode( '-sv-', $field['value'] ) : array();
			$statuses   = function_exists('get_all_woo_status_ps_sms_for_product_admin') ? get_all_woo_status_ps_sms_for_product_admin() : array();
						
			if ( $statuses ) foreach ( $statuses as $status_value => $status_name ) {
				echo '<option value="' . esc_attr( $status_value ) . '"' . selected( in_array( $status_value, $selected_statuses ), true, false ) . '>' . esc_attr( $status_name ) . '</option>';
			}
			
		echo '</select></p>';
		
	}

}