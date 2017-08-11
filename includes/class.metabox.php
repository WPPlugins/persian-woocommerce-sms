<?php
class WoocommerceIR_Metabox_SMS {
	
	public function __construct() {
		if ( ps_sms_options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' || ps_sms_options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' ) {
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box_woocommerce' ) ); 
			add_action( 'wp_ajax_persianwoosms_send_sms_to_buyer', array( $this, 'send_sms_from_woocommerce_pages' ) );	
			add_action( 'wp_ajax_nopriv_persianwoosms_send_sms_to_buyer', array( $this, 'send_sms_from_woocommerce_pages' ) );	
		}
	}
	
    public function add_meta_box_woocommerce( $post_type ) {
		global $post;
        if( $post_type == 'shop_order' && ps_sms_options( 'enable_metabox', 'sms_buyer_settings', 'off' ) == 'on' ) 
            add_meta_box( 'send_sms_to_buyer', 'ارسال پیامک به خریدار', array( $this, 'metabox_in_shop_order' ), 'shop_order', 'side', 'high' );
        
		if( $post->ID && $post_type == 'product' && ps_sms_options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' )
            add_meta_box( 'send_sms_to_buyer', 'ارسال پیامک به مشترکین این محصول', array( $this, 'metabox_in_product' ), 'product', 'side', 'low' );
    }
	
    public function metabox_in_shop_order( $post ) {
		if ( get_post_meta( $post->ID, '_billing_phone', 'true' ) ) { 
			?>
			<div class="persianwoosms_send_sms" style="position:relative">
				<div class="persianwoosms_send_sms_result"></div>
				<h4>ارسال پیامک دلخواه به خریدار</h4>
				<p>تمامی پیامک های ارسال شده از طرف شما به شماره<code><?php echo get_post_meta( $post->ID, '_billing_phone', 'true' ) ?></code> ارسال می گردد.</p>
				<p>
					<textarea rows="5" cols="20" class="input-text" id="persianwoosms_sms_to_buyer" name="persianwoosms_sms_to_buyer" style="width: 100%; height: 78px;"></textarea>
				</p>
				<p> 
					<?php wp_nonce_field('persianwoosms_send_sms_action','persianwoosms_send_sms_nonce'); ?>
					<input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
					<input type="hidden" name="post_type" value="shop_order">
					
					<p>
					<?php if ( ps_sms_options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) ps_sms_options( 'pm_type_buyer', 'sms_buyer_settings', array()) ) ) ) { ?>
						<input type="checkbox" name="persianwoosms_pm_type_sms" id="persianwoosms_pm_type_sms" checked="checked"  />
						<label for="persianwoosms_pm_type_sms">اس ام اس</label>
					<?php } if ( ps_sms_options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) ps_sms_options( 'pm_type_buyer', 'sms_buyer_settings', array()) ) ) ) { ?>
						<input type="checkbox" name="persianwoosms_pm_type_tg" id="persianwoosms_pm_type_tg" checked="checked" />
						<label for="persianwoosms_pm_type_tg">تلگرام</label>
					<?php }  ?>
				
						&nbsp;&nbsp;&nbsp;<input type="submit" class="button" name="persianwoosms_send_sms" id="persianwoosms_send_sms_button" value="ارسال پیامک" style="float:left">
					</p>
				</p>
				<div id="persianwoosms_send_sms_overlay_block"><img src="<?php echo PS_WOO_SMS_PLUGIN_PATH.'/assets/images/ajax-loader.gif'; ?>" alt=""></div>
			</div>
			<?php
		}
		else { ?>	
			<div class="persianwoosms_send_sms" style="position:relative">
				<div class="persianwoosms_send_sms_result"></div>
				<h4>ارسال پیامک دلخواه به خریدار</h4>
				<p>شماره ای برای ارسال پیامک وجود ندارد</p>
			</div>
		<?php
		}
    }
	
	
    public function metabox_in_product( $post ) {
		$thepostid = is_object( $post ) ? $post->ID : 0;
		if ( empty( $thepostid ) )
			return;
        ?>
        <div class="persianwoosms_send_sms" style="position:relative">
            <div class="persianwoosms_send_sms_result"></div>
            <h4>ارسال پیامک دلخواه به مشترکین این محصول</h4>
            <p>
				<select name="select_group" class="wc-enhanced-select" id="select_group">
					<?php
					$options = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_options', true ) :  ps_sms_options( 'notif_options', 'sms_notif_settings', '' );
					$options = !empty($options) ? $options : array();
					$options = explode ( PHP_EOL , $options);
					foreach ( ( array ) $options as $option )  {
						list( $code , $text) = explode ( ":", $option);
						if ( strlen($text) > 1) {
						?>
						<option id="sms_qroup_check_<?php echo $code; ?>" value="<?php echo $code; ?>"><?php echo $text;?></option>
						<?php
						}
					}

					$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_onsale_text', true ) : ps_sms_options( 'notif_onsale_text', 'sms_notif_settings', '' );
					$code = '_onsale';
					?>
					<option id="sms_qroup_check_<?php echo $code; ?>" value="<?php echo $code; ?>"><?php echo $text;?></option>
					<?php
					$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_low_stock_text', true ) : ps_sms_options( 'notif_low_stock_text', 'sms_notif_settings', '' );
					$code = '_low';
					?>
					<option id="sms_qroup_check_<?php echo $code; ?>" value="<?php echo $code; ?>"><?php echo $text;?></option>
					<?php
					$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_no_stock_text', true ) : ps_sms_options( 'notif_no_stock_text', 'sms_notif_settings', '' );
					$code = '_in';
					?>
					<option id="sms_qroup_check_<?php echo $code; ?>" value="<?php echo $code; ?>"><?php echo $text;?></option>
					
				</select>
			</p>
			<p>
                <textarea class="input-text" id="persianwoosms_sms_to_buyer" name="persianwoosms_sms_to_buyer" style="width: 100%; height: 78px;"></textarea>
            </p>
            
			<p> 
                <?php wp_nonce_field('persianwoosms_send_sms_action','persianwoosms_send_sms_nonce'); ?>
                <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
				<input type="hidden" name="post_type" value="product">
				
					<p>
					<?php if ( ps_sms_options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on'&& in_array( 'sms', ( (array) ps_sms_options( 'pm_type_notif', 'sms_notif_settings', array()) ) ) ) { ?>
						<input type="checkbox" name="persianwoosms_pm_type_sms" id="persianwoosms_pm_type_sms" checked="checked"  />
						<label for="persianwoosms_pm_type_sms">اس ام اس</label>
					<?php } if ( ps_sms_options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) ps_sms_options( 'pm_type_notif', 'sms_notif_settings', array()) ) ) ) { ?>
						<input type="checkbox" name="persianwoosms_pm_type_tg" id="persianwoosms_pm_type_tg" checked="checked" />
						<label for="persianwoosms_pm_type_tg">تلگرام</label>
					<?php }  ?>
				
						&nbsp;&nbsp;&nbsp;<input type="submit" class="button" name="persianwoosms_send_sms" id="persianwoosms_send_sms_button" value="ارسال پیامک" style="float:left">
					</p>
					
            </p>
			
			
            <div id="persianwoosms_send_sms_overlay_block"><img src="<?php echo PS_WOO_SMS_PLUGIN_PATH.'/assets/images/ajax-loader.gif'; ?>" alt=""></div>
        </div>
        <?php
    }
	
	
    function send_sms_from_woocommerce_pages() {

		$active_sms_gateway = ps_sms_options( 'sms_gateway', 'sms_main_settings', '' );
		$active_tg_gateway = ps_sms_options( 'tg_gateway', 'sms_main_settings', '' );
			
		$sms = isset( $_POST['sms'] ) ? true : false;
		$tg = isset( $_POST['tg'] ) ? true : false;
			
			
		if ( isset($_POST['post_type']) && $_POST['post_type'] == 'shop_order' ) {
			
			$order = new WC_Order( intval($_POST['post_id']) );
			$phone = get_post_meta( intval($_POST['post_id']), '_billing_phone', true );
			$buyer_sms_data['number']   = explode( ',', $phone);
			$buyer_sms_data['number'] = fa_en_mobile_woo_sms($buyer_sms_data['number']);
			
			$buyer_sms_data['sms_body'] = esc_textarea($_POST['textareavalue']);
			
			if ( !$buyer_sms_data['number'] || empty($buyer_sms_data['number']) ) {
				wp_send_json_error( array('message' => 'شماره ای برای دریافت وجود ندارد') );
				exit;
			}
			elseif ( !$buyer_sms_data['sms_body'] || empty($buyer_sms_data['sms_body']) ) {
				wp_send_json_error( array('message' => 'متن پیامک خالی است') );
				exit;
			}
			else {
				
				if ( $sms && $tg ) {
	
					$buyer_response_sms = ( ! empty( $active_sms_gateway ) && $active_sms_gateway != 'none' )  ? WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $buyer_sms_data ) : false;
					$buyer_response_tg = ( ! empty( $active_tg_gateway ) && $active_tg_gateway != 'none' )  ? WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $buyer_sms_data ) : false;
					
					if( ob_get_length() )
						ob_clean();
					header('Content-Type: application/json');
			
					if( $buyer_response_sms && $buyer_response_tg ) {
						$order->add_order_note( sprintf('پیام ها ( پیامک و تلگرام ) با موفقیت به خریدار با شماره موبایل %s ارسال شدند . <br/>متن پیام : %s' , $phone , $buyer_sms_data['sms_body'] ));
						wp_send_json_success( array('message' => 'پیام ها ( پیامک و تلگرام ) با موفقیت ارسال شدند') );
						exit;
					}
					if( $buyer_response_sms && ! $buyer_response_tg ) {
						$order->add_order_note( sprintf('پیامک با موفقیت به خریدار با شماره موبایل %s ارسال شد ولی تلگرام با خطا مواجه شد. <br/>متن پیام : %s' , $phone , $buyer_sms_data['sms_body'] ));
						wp_send_json_success( array('message' => 'پیامک با موفقیت ارسال شد ولی تلگرام با خطا مواجه شد') );
						exit;
					}					
					if( ! $buyer_response_sms && $buyer_response_tg ) {
						$order->add_order_note( sprintf('تلگرام با موفقیت به خریدار با شماره موبایل %s ارسال شد ولی پیامک با خطا مواجه شد. <br/>متن پیام : %s' , $phone , $buyer_sms_data['sms_body'] ));
						wp_send_json_success( array('message' => 'تلگرام با موفقیت ارسال شد ولی پیامک با خطا مواجه شد') );
						exit;
					}else {
						$order->add_order_note( sprintf('پیام ها ( پیامک و تلگرام ) به خریدار با شماره موبایل %s ارسال نشدند . خطایی رخ داده است .<br/>متن پیامک : %s' , $phone , $buyer_sms_data['sms_body'] ));
						wp_send_json_success( array('message' => 'پیام ها ( پیامک و تلگرام ) ارسال نشدند . خطایی رخ داده است') );
						exit;
					}
				
				}
				else if ( $sms ) {
					
					$buyer_response_sms = ( ! empty( $active_sms_gateway ) && $active_sms_gateway != 'none' )  ? WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $buyer_sms_data ) : false;
					if( ob_get_length() )
						ob_clean();
					header('Content-Type: application/json');
			
					if( $buyer_response_sms ) {
						$order->add_order_note( sprintf('پیامک با موفقیت به خریدار با شماره موبایل %s ارسال شد . <br/>متن پیامک : %s' , $phone , $buyer_sms_data['sms_body'] ));
						wp_send_json_success( array('message' => 'پیامک با موفقیت ارسال شد') );
						exit;
					} else {
						$order->add_order_note( sprintf('پیامک به خریدار با شماره موبایل %s ارسال نشد . خطایی رخ داده است .<br/>متن پیامک : %s' , $phone , $buyer_sms_data['sms_body'] ));
						wp_send_json_success( array('message' => 'پیامک ارسال نشد. خطایی رخ داده است') );
						exit;
					}
					
					
				}
				else if ( $tg ) {
					
					$buyer_response_tg = ( ! empty( $active_tg_gateway ) && $active_tg_gateway != 'none' )  ? WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $buyer_sms_data ) : false;
					if( ob_get_length() )
						ob_clean();
					header('Content-Type: application/json');
			
					if( $buyer_response_tg ) {
						$order->add_order_note( sprintf('تلگرام با موفقیت به خریدار با شماره موبایل %s ارسال شد . <br/>متن تلگرام : %s' , $phone , $buyer_sms_data['sms_body'] ));
						wp_send_json_success( array('message' => 'تلگرام با موفقیت ارسال شد') );
						exit;
					} else {
						$order->add_order_note( sprintf('تلگرام به خریدار با شماره موبایل %s ارسال نشد . خطایی رخ داده است .<br/>متن تلگرام : %s' , $phone , $buyer_sms_data['sms_body'] ));
						wp_send_json_success( array('message' => 'تلگرام ارسال نشد. خطایی رخ داده است'));
						exit;
					}
					
				}
				
				
				
				
				
			}
		}
		
		
		if ( isset($_POST['post_type']) && $_POST['post_type'] == 'product' ) {
			
			$buyer_sms_data['sms_body'] = esc_textarea($_POST['textareavalue']);
			if ( !$buyer_sms_data['sms_body'] || empty($buyer_sms_data['sms_body']) ) {
				wp_send_json_error( array('message' => 'متن پیامک خالی است') );
				exit;
			}
			
			$product_id = intval($_POST['post_id']);
			$group = isset($_POST['group']) ? $_POST['group'] : '';
			if ( $group ) {
				$product_metas = get_post_meta( $product_id, '_hannanstd_sms_notification',  true) ? get_post_meta( $product_id, '_hannanstd_sms_notification',  true) : '';
				$contacts = explode ( '***', $product_metas );
				$numbers_list_sms = array();
				$numbers_list_tg = array();
				
				foreach ( (array) $contacts as $contact_type ) {
					$contact_types = explode ( '_vsh_', $contact_type);
					if ( count ($contact_types) == 2 ) {
						list( $contact , $type ) = $contact_types;
					}
					else {
						$contact = $contact_type;
						$type = '';
					}
					
					if ( strlen( $contact) < 2)
						break;
					
					list( $number , $groups ) = explode ( '|', $contact);
					$groups = explode ( ',' , $groups);
					$type = $type == '' ? '' : explode ( ',' , $type);
					if ( in_array( $group, $groups ) ) {
						if ( strlen($number) > 5 ) {
							if (( empty($type) || ( !empty($type) && in_array( '_sms' , $type) ) ) && $sms ) 
								$numbers_list_sms[] = $number;
							if (( empty($type) || ( !empty($type) && in_array( '_tg' , $type) )) && $tg )
								$numbers_list_tg[] = $number;
							
						}
					}
				}
						
				$numbers_list_sms = array_unique( explode( ',', implode( ',', $numbers_list_sms )) );
				$numbers_list_tg = array_unique( explode( ',', implode( ',', $numbers_list_tg )) );
	
				$numbers_list_sms = array_filter($numbers_list_sms);
				$numbers_list_tg = array_filter($numbers_list_tg);
	
				$count_sms = count( $numbers_list_sms );
				$count_tg = count( $numbers_list_tg );
				
				
				if ( $sms ) {
					
					if ( $count_sms < 1 || empty($numbers_list_sms) ) {
						wp_send_json_error( array('message' => 'شماره ای برای دریافت وجود ندارد') );
						exit;
					}
					
					$buyer_sms_data['number']   = $numbers_list_sms;
					
					$buyer_sms_data['number'] = fa_en_mobile_woo_sms($buyer_sms_data['number']);
					
					$buyer_response_sms = ( ! empty( $active_sms_gateway ) && $active_sms_gateway != 'none' )  ? WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $buyer_sms_data ) : false;
				}
				
				if ( $tg ) {
					
					if ( $count_tg < 1 || empty($numbers_list_tg) ) {
						wp_send_json_error( array('message' => 'شماره ای برای دریافت وجود ندارد') );
						exit;
					}
					
					$buyer_sms_data['number']   = $numbers_list_tg;
					
					$buyer_sms_data['number'] = fa_en_mobile_woo_sms($buyer_sms_data['number']);
					
					$buyer_response_tg = ( ! empty( $active_tg_gateway ) && $active_tg_gateway != 'none' )  ? WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $buyer_sms_data ) : false;
				}
				
				if( ob_get_length() )
					ob_clean();
				header('Content-Type: application/json');

				if ( $sms && $tg ) {
				
					if( $buyer_response_sms && $buyer_response_tg ) {
						wp_send_json_success( array('message' => sprintf('پیامک با موفقیت به %s شماره و تلگرام نیز به %s شماره موبایل ارسال شدند .' , $count_sms , $count_tg) ) );
						exit;
					}
					if( $buyer_response_sms && ! $buyer_response_tg ) {
						wp_send_json_success( array('message' => sprintf('پیامک با موفقیت به %s شماره موبایل ارسال شد ولی تلگرام با خطا مواجه شد' , $count_sms) ) );
						exit;
					}					
					if( ! $buyer_response_sms && $buyer_response_tg ) {
						wp_send_json_success( array('message' => sprintf('تلگرام با موفقیت به %s شماره موبایل ارسال شد ولی پیامک با خطا مواجه شد .' , $count_tg) ) );
						exit;
					}else {
						wp_send_json_success( array('message' => 'پیام ها ( پیامک و تلگرام ) ارسال نشدند . خطایی رخ داده است') );
						exit;
					}
				
				}
				else if ( $sms ) {
					if( $buyer_response_sms ) {
						wp_send_json_success( array('message' => sprintf('پیامک با موفقیت به %s شماره موبایل ارسال شد' , $count_sms) ) );
						exit;
					} else {
						wp_send_json_success( array('message' => 'پیامک ارسال نشد. خطایی رخ داده است'));
						exit;
					}
				}
				else if ( $tg ) {
					if( $buyer_response_tg ) {
						wp_send_json_success( array('message' => sprintf('تلگرام با موفقیت به %s شماره موبایل ارسال شد' , $count_tg) ) );
						exit;
					} else {
						wp_send_json_success( array('message' => 'تلگرام ارسال نشد. خطایی رخ داده است'));
						exit;
					}
				}
				
				
			}
		}
		
    }
}	