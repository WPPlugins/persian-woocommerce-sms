<?php
class WoocommerceIR_Settings_Fields_SMS {
	
    private $settings_sections = array();
    private $settings_fields = array();
    private static $_instance;
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }
	
    function admin_enqueue_scripts() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_media();
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery' );
    }

    function set_sections( $sections ) {
        $this->settings_sections = $sections;
        return $this;
    }

    function add_section( $section ) {
        $this->settings_sections[] = $section;
        return $this;
    }

    function set_fields( $fields ) {
        $this->settings_fields = $fields;
        return $this;
    }

    function add_field( $section, $field ) {
        $defaults = array(
            'name' => '',
            'label' => '',
            'desc' => '',
            'type' => 'text'
        );
        $arg = wp_parse_args( $field, $defaults );
        $this->settings_fields[$section][] = $arg;
        return $this;
    }

    function admin_init() {
        foreach ( $this->settings_sections as $section ) {
            if ( false == get_option( $section['id'] ) ) {
                add_option( $section['id'] );
            }
            if ( isset($section['desc']) && !empty($section['desc']) ) {
                $section['desc'] = '<div class="inside">'.$section['desc'].'</div>';
                $callback = create_function('', 'echo "'.str_replace('"', '\"', $section['desc']).'";');
            } else {
                $callback = '__return_false';
            }
            add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
        }
		
        foreach ( $this->settings_fields as $section => $field ) {
            foreach ( $field as $option ) {
                $type = isset( $option['type'] ) ? $option['type'] : 'text';
                $args = array(
                    'id' => $option['name'],
                    'desc' => isset( $option['desc'] ) ? $option['desc'] : '',
                    'name' => $option['label'],
                    'section' => $section,
                    'size' => isset( $option['size'] ) ? $option['size'] : null,
                    'options' => isset( $option['options'] ) ? $option['options'] : '',
                    'std' => isset( $option['default'] ) ? $option['default'] : '',
                    'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
                );
                add_settings_field( $section . '[' . $option['name'] . ']', $option['label'], array( $this, 'callback_' . $type ), $section, $section, $args );
            }
        }
		
        foreach ( $this->settings_sections as $section ) {
            register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
        }
    }

    function callback_text( $args ) {
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $html = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
        $html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
        echo $html;
    }
	
    function callback_checkbox( $args ) {
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $html = sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
        $html .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s />', $args['section'], $args['id'], $value, checked( $value, 'on', false ) );
        $html .= sprintf( '<label for="wpuf-%1$s[%2$s]"> %3$s</label>', $args['section'], $args['id'], $args['desc'] );
        echo $html;
    }

    function callback_multicheck( $args ) {
        $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
        $html = '';
        foreach ( $args['options'] as $key => $label ) {
            $checked = isset( $value[$key] ) ? $value[$key] : '0';
            $html .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
            $html .= sprintf( '<label for="wpuf-%1$s[%2$s][%4$s]"> %3$s</label><br>', $args['section'], $args['id'], $label, $key );
        }
        $html .= sprintf( '<span class="description"> %s</label>', $args['desc'] );
        echo $html;
    }

    function callback_radio( $args ) {
        $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
        $html = '';
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
            $html .= sprintf( '<label for="wpuf-%1$s[%2$s][%4$s]"> %3$s</label><br>', $args['section'], $args['id'], $label, $key );
        }
        $html .= sprintf( '<span class="description"> %s</label>', $args['desc'] );
        echo $html;
    }

    function callback_select( $args ) {
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'wc-enhanced-select regular';
        $html = sprintf( '<div style="max-width:350px"><select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
        }
        $html .= sprintf( '</select></div>' );
        $html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
        echo $html;
    }
	
    function callback_textarea( $args ) {
        $value = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value );
        $html .= sprintf( '<br><span class="description"> %s</span>', $args['desc'] );
        echo $html;
    }

    function callback_html( $args ) {
        echo sprintf( '<span class="description"> %s</span>', $args['desc'] );
    }

    function callback_wysiwyg( $args ) {
        $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : '500px';
        echo '<div style="width: ' . $size . ';">';
        wp_editor( $value, $args['section'] . '-' . $args['id'] . '', array( 'teeny' => true, 'textarea_name' => $args['section'] . '[' . $args['id'] . ']', 'textarea_rows' => 10 ) );
        echo '</div>';
        echo sprintf( '<br><span class="description"> %s</span>', $args['desc'] );
    }

    function callback_file( $args ) {
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $id = $args['section']  . '[' . $args['id'] . ']';
        $html  = sprintf( '<input type="text" class="%1$s-text ps-sms-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
        $html .= '<input type="button" class="button ps-sms-browse" value="'.__( 'Browse' ).'" />';
        $html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
        echo $html;
    }

    function callback_password( $args ) {
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $html = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
        $html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
        echo $html;
    }

    function callback_color( $args ) {
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $html = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
        $html .= sprintf( '<span class="description" style="display:block;"> %s</span>', $args['desc'] );
        echo $html;
    }

    function sanitize_options( $options ) {
        foreach( $options as $option_slug => $option_value ) {
            $sanitize_callback = $this->get_sanitize_callback( $option_slug );
            if ( $sanitize_callback ) {
                $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
                continue;
            }
        }
        return $options;
    }

    function get_sanitize_callback( $slug = '' ) {
        if ( empty( $slug ) ) {
            return false;
        }
        foreach( $this->settings_fields as $section => $options ) {
            foreach ( $options as $option ) {
                if ( $option['name'] != $slug ) {
                    continue;
                }
                return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
            }
        }
        return false;
    }

    function get_option( $option, $section, $default = '' ) {
        $options = get_option( $section );
        if ( isset( $options[$option] ) ) {
            return $options[$option];
        }
        return $default;
    }
	
    function show_navigation() {
        $html = '<h2 class="nav-tab-wrapper">';
        foreach ( $this->settings_sections as $tab ) {
            $html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
        }
        $html .= '</h2>';
        echo $html;
    }

    function show_forms() {
		if ( defined( 'WC_VERSION' ) )  {
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			wp_enqueue_script( 'wc-enhanced-select' );
		}
		if( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
			?>
			<div class="updated">
				<p>تنظمیات ذخیره شدند .</p>
			</div>
			<?php
		}
		?>
		<style>
			table.form-table th {
				padding-left: 24px !important;
				position: relative;
			}
		</style>
        <div class="metabox-holder">
			<?php foreach ( $this->settings_sections as $form ) { ?>
				<div id="<?php echo $form['id']; ?>" class="group">
					<form method="post" action="options.php">
						<?php do_action( 'ps_woo_sms_form_top_' . $form['id'], $form ); ?>
						<?php settings_fields( $form['id'] ); ?>
						<?php do_settings_sections( $form['id'] ); ?>
						<?php do_action( 'ps_woo_sms_form_bottom_' . $form['id'], $form ); ?>
						<div style="padding-right: 10px">
							<?php do_action( 'ps_woo_sms_form_submit_' . $form['id'], $form ); ?>
							
						</div>
					</form>
				</div>
			<?php } ?>
		</div>
        <?php
        $this->script();
    }

    function script() {
        ?>
        <script>
            jQuery(document).ready(function($) {
                $('.wp-color-picker-field').wpColorPicker();
                $('.group').hide();
			<?php if ( isset( $_GET['send'] ) && $_GET['send']=='true' ) { ?>
                    $('.group:last').fadeIn();
                $('.group .collapsed').each(function(){
                    $(this).find('input:checked').parent().parent().parent().nextAll().each(
                    function(){
                        if ($(this).hasClass('last')) {
                            $(this).removeClass('hidden');
                            return false;
                        }
                        $(this).filter('.hidden').removeClass('hidden');
                    });
                });
                    $('.nav-tab-wrapper a:last').addClass('nav-tab-active');
                $('.nav-tab-wrapper a').click(function(evt) {
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active').blur();
                    var clicked_group = $(this).attr('href');
				<?php } else { ?>
				var activetab = '';
				if (typeof(localStorage) != 'undefined' ) {
					activetab = localStorage.getItem("activetab");
				}
				if (activetab != '' && $(activetab).length ) {
					$(activetab).fadeIn();
				} else {
					$('.group:first').fadeIn();
                   $('.group:last').fadeIn();
				}
                $('.group .collapsed').each(function(){
                    $(this).find('input:checked').parent().parent().parent().nextAll().each(
                    function(){
                        if ($(this).hasClass('last')) {
                            $(this).removeClass('hidden');
                            return false;
                        }
                        $(this).filter('.hidden').removeClass('hidden');
                    });
                });

				if (activetab != '' && $(activetab + '-tab').length ) {
					$(activetab + '-tab').addClass('nav-tab-active');
				}
				else {
					$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                }
                $('.nav-tab-wrapper a').click(function(evt) {
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active').blur();
                    var clicked_group = $(this).attr('href');
					if (typeof(localStorage) != 'undefined' ) {
						localStorage.setItem("activetab", $(this).attr('href'));
					}
				<?php } ?>
                    $('.group').hide();
                    $(clicked_group).fadeIn();
                    evt.preventDefault();
                });
                var file_frame = null;
                $('.ps-sms-browse').on('click', function (event) {
                    event.preventDefault();
                    var self = $(this);
                    if ( file_frame ) {
                        file_frame.open();
                        return false;
                    }
                    file_frame = wp.media.frames.file_frame = wp.media({
                        title: self.data('uploader_title'),
                        button: {
                            text: self.data('uploader_button_text'),
                        },
                        multiple: false
                    });
                    file_frame.on('select', function () {
                        attachment = file_frame.state().get('selection').first().toJSON();

                        self.prev('.ps-sms-url').val(attachment.url);
                    });
                    file_frame.open();
                });
        });
        </script>
        <?php
    }
}

function ps_sms_options( $option, $section, $default = '' ) {
    $options = get_option( $section );
	return isset( $options[$option] ) ? $options[$option] : $default;
}

function get_all_woo_status_ps_sms() {
	if ( !function_exists('wc_get_order_statuses') )
		return;
	$statuses = wc_get_order_statuses() ? wc_get_order_statuses() : array();
	$opt_statuses = array();
	foreach ( (array) $statuses as $status_val => $status_name ) {
		$opt_statuses[substr( $status_val, 3 )] = $status_name;
	}
	return $opt_statuses;
}

function get_all_woo_status_ps_sms_for_super_admin() {
	if ( !function_exists('wc_get_order_statuses') )
		return;
	$statuses = wc_get_order_statuses() ? wc_get_order_statuses() : array();
	$opt_statuses = array();
	foreach ( (array) $statuses as $status_val => $status_name ) {
		$opt_statuses[substr( $status_val, 3 )] = $status_name;
	}
	$opt_statuses['low'] = __( 'کم بودن موجودی انبار', 'persianwoosms');
	$opt_statuses['out'] = __( 'تمام شدن موجودی انبار' , 'persianwoosms');
	return $opt_statuses;
}

function get_all_woo_status_ps_sms_for_product_admin() {
	if ( !function_exists('wc_get_order_statuses') )
		return;
	$statuses = wc_get_order_statuses() ? wc_get_order_statuses() : array();
	$opt_statuses = array();
	
	if ( (ps_sms_options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) ps_sms_options( 'pm_type_product_admin', 'sms_product_admin_settings', array()) ) )) &&
	( ps_sms_options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) ps_sms_options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) )	) {
		$opt_statuses['sms'] = __( 'دریافت اس ام اس', 'persianwoosms');
		$opt_statuses['tg'] = __( 'دریافت تلگرام' , 'persianwoosms');
	}
	else if ( ps_sms_options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) ps_sms_options( 'pm_type_product_admin', 'sms_product_admin_settings', array()) ) )  ) {
		$opt_statuses['sms'] = __( 'دریافت اس ام اس', 'persianwoosms');
		$opt_statuses['tg'] = __( 'دریافت تلگرام (غیرفعال)' , 'persianwoosms');
	}
	else if ( ps_sms_options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) ps_sms_options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) ) {
		$opt_statuses['sms'] = __( 'دریافت اس ام اس (غیرفعال)', 'persianwoosms');
		$opt_statuses['tg'] = __( 'دریافت تلگرام' , 'persianwoosms');
	}
	else {
		$opt_statuses['sms'] = __( 'دریافت اس ام اس (غیرفعال)', 'persianwoosms');
		$opt_statuses['tg'] = __( 'دریافت تلگرام (غیرفعال)' , 'persianwoosms');
	}
	
	foreach ( (array) $statuses as $status_val => $status_name ) {
		$opt_statuses[substr( $status_val, 3 )] = $status_name;
	}
	$opt_statuses['low'] = __( 'کم بودن موجودی انبار', 'persianwoosms');
	$opt_statuses['out'] = __( 'تمام شدن موجودی انبار' , 'persianwoosms');
	return $opt_statuses;
}

function get_allowed_woo_status_ps_sms() {
	if ( !function_exists('wc_get_order_statuses') )
		return;
	$statuses = wc_get_order_statuses() ? wc_get_order_statuses() : array();
	$order_status_settings  = ps_sms_options( 'order_status', 'sms_buyer_settings', array() );
	$allowed_statuses = array();
	foreach ( (array) $statuses as $status_val => $status_name ) {
		if ( in_array( substr( $status_val, 3 ) , $order_status_settings ) )
			$allowed_statuses[substr( $status_val, 3 )] = $status_name;
	}
	return $allowed_statuses;
}
	
function get_product_list_ps_sms( $order ) {        
	$product_list = '';
	$order_item = $order->get_items();
	$prodct_name = $prodct_id = array();
	foreach( (array) $order_item as $product ) {
		$prodct_id[] = $product['product_id'];
		$prodct_name[] = $product['name']; 
		$prodct_name_qty[] = $product['name'] . '(' . $product['qty'] . ')'; 
	}
	$product_names = implode( '-', $prodct_name );
	$prodct_name_qtys = implode( '-', $prodct_name_qty );
	$prodct_ids = implode( ',', $prodct_id );
	return array ( 
		'names_qty' => $prodct_name_qtys,
		'names' => $product_names , 
		'ids' => $prodct_ids
	);
}

function add_multi_select_checkbox_to_checkout_ps_sms( $field, $key, $args, $value ) {
		if ( ( ! empty( $args['clear'] ) ) )
			$after = '<div class="clear"></div>';
		else
			$after = '';
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'persian_woo_sms'  ) . '">*</abbr>';
		} else
			$required = '';
		$custom_attributes = array();
		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		if ( $args['type'] == "persian_woo_sms_multiselect" ) {
			$value = is_array( $value ) ? $value : array( $value );
			if ( ! empty( $args['options'] ) ) {
				$options = '';
				foreach ( $args['options'] as $option_key => $option_text ) {
					$options .= '<option value="' . esc_attr( $option_key ) . '" '. selected( in_array( $option_key, $value ), 1, false ) . '>' . esc_attr( $option_text ) .'</option>';
				}
				$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field">';
				if ( $args['label'] ) {
					$field .= '<label for="' . esc_attr( $key ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . $args['label']. $required . '</label>';
				}
				$field .= '<select name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '" class="select" multiple="multiple" ' . implode( ' ', $custom_attributes ) . '>'
						. $options
						. ' </select>';
						
					if ( $args['description'] ) {
						$field .= '<span class="description">' . ( $args['description'] ) . '</span>';
					}
					
					$field .= '</p>'. $after;
			}
		}
		if ( $args['type'] == "persian_woo_sms_multicheckbox" ) {	
				$value = is_array( $value ) ? $value : array( $value );
				if ( ! empty( $args['options'] ) ) {
					$field .= '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field">';
					if ( $args['label'] ) {
						$field .= '<label for="' . esc_attr( current( array_keys( $args['options'] ) ) ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . $args['label']. $required  . '</label>';
					}
					foreach ( $args['options'] as $option_key => $option_text ) {
						$field .= '<input type="checkbox" class="input-checkbox" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '_' . esc_attr( $option_key ) . '"' . checked( in_array( $option_key, $value ), 1, false ) . ' />';
						$field .= '<label for="' . esc_attr( $key ) . '_' . esc_attr( $option_key ) . '" class="checkbox ' . implode( ' ', $args['label_class'] ) .'">' . $option_text . '</label><br>';
					}
					if ( $args['description'] ) {
						$field .= '<span class="description">' . ( $args['description'] ) . '</span>';
					}
					$field .= '</p>' . $after;
				}
		}
		return $field;
}

function Shamsi_HANNANStd($g_y,$g_m,$g_d,$mod=''){
	$d_4=$g_y%4;
	$g_a=array(0,0,31,59,90,120,151,181,212,243,273,304,334);
	$doy_g=$g_a[(int)$g_m]+$g_d;
	if($d_4==0 and $g_m>2)
		$doy_g++;
	$d_33=(int)((($g_y-16)%132)*.0305);
	$a=($d_33==3 or $d_33<($d_4-1) or $d_4==0)?286:287;
	$b=(($d_33==1 or $d_33==2) and ($d_33==$d_4 or $d_4==1))?78:(($d_33==3 and $d_4==0)?80:79);
	if((int)(($g_y-10)/63)==30){
		$a--;$b++;
	}
	if($doy_g>$b){
		$jy=$g_y-621; $doy_j=$doy_g-$b;
	}else{
		$jy=$g_y-622; $doy_j=$doy_g+$a;
	}
	if($doy_j<187){
		$jm=(int)(($doy_j-1)/31); $jd=$doy_j-(31*$jm++);
	}else{
		$jm=(int)(($doy_j-187)/30); $jd=$doy_j-186-($jm*30); $jm+=7;
	}
	return($mod=='')?array($jy,$jm,$jd):$jy.$mod.$jm.$mod.$jd;
}


	
function sms_text_order_shortcode () {
		
	$shortcode_list = apply_filters( 'persian_woo_sms_shortcode_list' , '' );	
		
	return "
		<strong>جزییات سفارش : </strong><br/>
		<code>{phone}</code> = شماره موبایل خریدار   ، 
		<code>{email}</code> = ایمیل خریدار   ، 	
		<code>{order_id}</code> = شماره سفارش  ،    
		<code>{post_id}</code> = شماره پست (شماره سفارش اصلی)  ،  
		<code>{status}</code> = وضعیت سفارش<br/> 
		<code>{price}</code> = مبلغ سفارش   ، 
		<code>{all_items}</code> = آیتم های سفارش  ، 
		<code>{all_items_qty}</code> = آیتم های سفارش همراه تعداد ، 
		<code>{count_items}</code> = تعداد آیتم های سفارش  <br/> 
		<code>{payment_method}</code> = روش پرداخت  ، 
		<code>{shipping_method}</code> = روش ارسال  ، 
		<code>{description}</code> = توضیحات خریدار  ، 
		<code>{transaction_id}</code> = شماره تراکنش<br/><br/>
		
		<strong>جزییات صورت حساب : </strong><br/>
		<code>{b_first_name}</code> = نام خریدار   ، 
		<code>{b_last_name}</code> = نام خانوادگی خریدار   ، 
		<code>{b_company}</code> = نام شرکت   <br/> 
		<code>{b_country}</code> = کشور   ، 
		<code>{b_state}</code> = ایالت/استان   ، 
		<code>{b_city}</code> = شهر   ، 
		<code>{b_address_1}</code> = آدرس 1   ، 
		<code>{b_address_2}</code> = آدرس 2   ، 
		<code>{b_postcode}</code> = کد پستی<br/><br/>
		
		
		<strong>جزییات حمل و نقل : </strong><br/>
		<code>{sh_first_name}</code> = نام خریدار   ، 
		<code>{sh_last_name}</code> = نام خانوادگی خریدار   ، 
		<code>{sh_company}</code> = نام شرکت   <br/> 
		<code>{sh_country}</code> = کشور   ، 
		<code>{sh_state}</code> = ایالت/استان   ، 
		<code>{sh_city}</code> = شهر   ، 
		<code>{sh_address_1}</code> = آدرس 1   ،
		<code>{sh_address_2}</code> = آدرس 2   ، 
		<code>{sh_postcode}</code> = کد پستی<br/><br/>
		
		{$shortcode_list}
		
	";
}
	
function str_replace_tags_order( $content, $order_status, $order_id, $order , $all_items, $vendor_items ) {
	$price = intval($order->order_total). ' '. sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( $order->get_order_currency() ), '' ); 
	$count_items = count(explode( '-' , $all_items));
	list($all_items, $all_items_qty) = explode ( '__vsh__' , $all_items );
	
	$payment_gateways = array();
	if ( WC()->payment_gateways() )
		$payment_gateways = WC()->payment_gateways->payment_gateways();
	$payment_method = ! empty( $order->payment_method ) ? $order->payment_method : '';
	$payment_method = ( isset( $payment_gateways[ $payment_method ] ) ? esc_html( $payment_gateways[ $payment_method ]->get_title() ) : esc_html( $payment_method ) );
	$shipping_method = esc_html( $order->get_shipping_method() );
	
	$country = WC()->countries;
	
	$bill_country = ( isset( $country->countries[ $order->billing_country ] ) ) ?$country->countries[ $order->billing_country ] : $order->billing_country;
	$bill_state = ( $order->billing_country && $order->billing_state && isset( $country->states[ $order->billing_country ][ $order->billing_state ] ) ) ? $country->states[ $order->billing_country ][ $order->billing_state ] : $order->billing_state;
	
	$shipp_country = ( isset( $country->countries[ $order->shipping_country ] ) ) ?$country->countries[ $order->shipping_country ] : $order->shipping_country;
	$shipp_state = ( $order->shipping_country && $order->shipping_state && isset( $country->states[ $order->shipping_country ][ $order->shipping_state ] ) ) ? $country->states[ $order->shipping_country ][ $order->shipping_state ] : $order->shipping_state;
	
	$post = get_post( $order_id );
	
	$find = array(
		'{b_first_name}',
		'{b_last_name}',    
		'{b_company}',
		'{b_address_1}',
		'{b_address_2}',
		'{b_state}',
		'{b_city}',
		'{b_postcode}',
		'{b_country}',
		'{sh_first_name}',
		'{sh_last_name}',
		'{sh_company}',
		'{sh_address_1}',
		'{sh_address_2}',
		'{sh_state}',
		'{sh_city}',
		'{sh_postcode}',
		'{sh_country}',
		'{phone}',    
		'{email}',
		'{order_id}',
		'{post_id}',
		'{status}',
		'{price}',
		'{all_items}',
		'{all_items_qty}',
		'{count_items}',
		'{vendor_items}',
		'{transaction_id}',
		'{payment_method}',
		'{shipping_method}',
		'{description}',
    );
	
	$replace = array(
		$order->billing_first_name,
		$order->billing_last_name,
		$order->billing_company,
		$order->billing_address_1,
		$order->billing_address_2,
		$bill_state,
		$order->billing_city,
		$order->billing_postcode,
		$bill_country,
		$order->shipping_first_name,
		$order->shipping_last_name,
		$order->shipping_company,
		$order->shipping_address_1,
		$order->shipping_address_2,
		$shipp_state,
		$order->shipping_city,
		$order->shipping_postcode,
		$shipp_country,
		get_post_meta( $order_id, '_billing_phone', true ),
		$order->billing_email,
		$order->get_order_number(),
		$order_id,
		wc_get_order_status_name($order_status),
        $price,
		$all_items,
		$all_items_qty,
		$count_items,
		$vendor_items,
		get_post_meta( $order_id, '_transaction_id', true ),
		$payment_method,
		$shipping_method,
		nl2br( esc_html( $post->post_excerpt ) ),
	);
	
	$product_list	= get_product_list_ps_sms( $order );
	$product_ids = explode( ',' , $product_list['ids']);
	
	$content = apply_filters( 'persian_woo_sms_content_replace' , $content, $find , $replace , $order_id, $order , $product_ids );
	
    $content = str_replace( array( '<br>' , '<br/>' , '<br />', '&nbsp;' ), array( '' , '' , '', ' ' ), str_replace( $find, $replace, $content ) );   
	
	$content = apply_filters( 'persian_woo_sms_content' , $content, $order_id, $order , $product_ids );
	
	return $content;
}

function is_mobile_woo_sms( $phone ) {
	
	//return preg_match('/^(((\+|00)98)|0)?9[0-9]{9}$/', $phone );
	
	$phone = fa_en_mobile_woo_sms( $phone );
	
	$_phone = str_replace( array('+' , '-' , '_' , ',' , '(' , ')' ) , array( '' , '' ,'' ,'' ,'' ,'' ) , $phone);
	
	return is_numeric($_phone);
}

function fa_en_mobile_woo_sms( $phone ) {
	if ( is_array( $phone )) {
		return array_map( '_fa_en_mobile_woo_sms' , $phone );
	}
	else {
		return _fa_en_mobile_woo_sms( $phone );
	}
}

function _fa_en_mobile_woo_sms( $phone ) {
	
	$phone =  str_ireplace( array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹' ) , array('0','1','2','3','4','5','6','7','8','9'),  $phone ); //farsi
	
	$phone =  str_ireplace( array( '٠','١','٢','٣','٤','٥','٦','٧','٨','٩' ) , array('0','1','2','3','4','5','6','7','8','9'),  $phone ); //arabi
	
	return $phone;
}


function str_replace_tags_product( $content, $product_id ) {
	$regular_price = intval(get_post_meta( $product_id, '_regular_price', true )). ' '. sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(  get_woocommerce_currency() ), '' ); 
	$sale_price = intval(get_post_meta( $product_id, '_sale_price', true )). ' '. sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( get_woocommerce_currency() ), '' ); 
	$sale_price_dates_from = ( $date = get_post_meta( $product_id, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
	if ( $sale_price_dates_from != '' ) {
		list( $year , $month , $day ) = explode( '-', $sale_price_dates_from );
		$sale_price_dates_from = Shamsi_HANNANStd( $year , $month , $day, '/' );	
	}
	$sale_price_dates_to   = ( $date = get_post_meta( $product_id, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
	if ( $sale_price_dates_to != '' ) {
		list( $year , $month , $day ) = explode( '-', $sale_price_dates_to );
		$sale_price_dates_to = Shamsi_HANNANStd( $year , $month , $day, '/' );
	}
	$find = array(
		'{product_id}',
		'{sku}',
		'{product_title}',
		'{regular_price}',
		'{onsale_price}',
		'{onsale_from}',
		'{onsale_to}',
		'{stock}',
    );
	$replace = array(
		$product_id,
		get_post_meta( $product_id, '_sku', true ),
		get_the_title( $product_id ),
		$regular_price,
		$sale_price,
		$sale_price_dates_from,
		$sale_price_dates_to,
		((int) get_post_meta( $product_id, '_stock', true )),
	);
    return str_replace( array( '<br>' , '<br/>' , '<br />', '&nbsp;' ), array( '' , '' , '', ' ' ), str_replace( $find, $replace, $content ) );   
}