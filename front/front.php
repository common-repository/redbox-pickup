<?php
	if (!class_exists('REDBOX_Front')) {
    	class REDBOX_Front {
    		protected static $redbox_instance;
    		public $lang;
    		public $redboxKey;
    		public $minimumAmount;

    		public function __construct(){
		       $this->lang = get_locale() == "ar" ? "ar" : "en";
		       $this->redboxKey = get_option('redbox_key');
		       $this->minimumAmount = get_option('min_price_for_free') && is_numeric(get_option('min_price_for_free')) ?floatval(get_option('min_price_for_free')) : false;            
		    }

		    private function redbox_get_data_customer($order) {
				$shippingFirstName = $order->get_shipping_first_name();
		    	$shippingLastName = $order->get_shipping_last_name();
		    	$shippingPhone = $order->get_shipping_phone();
				$shippingAddress = $order->get_shipping_address_1();

		    	$billingFirstName = $order->get_billing_first_name();
		    	$billingLastName = $order->get_billing_last_name();
		    	$billingPhone = $order->get_billing_phone();
		    	$billingEmail = $order->get_billing_email();
				$billingAddress = $order->get_billing_address_1();

		    	$data = [
		    		"name" => $shippingFirstName ? $shippingFirstName . ' ' . $shippingLastName : $billingFirstName . ' ' . $billingLastName,
		    		'phone' => $shippingPhone ? $shippingPhone : $billingPhone,
		    		'email' => $billingEmail,
		    		'adddress' => $shippingAddress ? $shippingAddress : $billingAddress
		    	];
		    	return $data;
		    }
		    private function redbox_get_product($order)
		    {
		    	$products = [];
		    	foreach ( $order->get_items() as $item_id => $item ) {
				    $products[] = [
				   		'name' => $item->get_name(),
				   		'quantity' => $item->get_quantity(),
				   		'currency' => $order->get_currency(),
                        'unit_price' => $item->get_product()->get_price()
				    ];
				}
				return $products;
		    }
		    private function redbox_get_other_info($order)
		    {
		    	return [
		    		"curency" => $order->get_currency(),
		    		"shipping_total" => $order->get_shipping_total(),
		    		"order_total" => $order->get_total()
		    	];
		    }

    		function redbox_create_modal_redbox() {
    			$classRTL = $this->lang == "ar" ? "redbox-rtl" : "";
    			$dir = $this->lang == "ar" ? "rtl" : "ltr" ;
    			?>
    				<div class="redbox redbox-hide <?php echo $classRTL; ?>" lang="<?php echo $this->lang; ?>" dir="<?php echo $dir; ?>" path="<?php echo REDBOX_BASE_HOST; ?>">
		          		<div class="redbox-pickup">
		          			<img src="<?php echo REDBOX_BASE_HOST; ?>/image_plugin/close.png" class="close-modal-redbox" id="close-modal-redbox">
		          			<div class="redbox-waiting-response">
	    						<i class="fa fa-spinner fa-spin"></i>
	    					</div>
		          			<div class="main-title">
		          				<?php echo REDBOX_LANGUAGE[$this->lang]['label_title_redbox_pickup'] ?>
		          			</div>
			                <div class="redbox-content-info">
			                	<div class="title">
			                		<?php echo REDBOX_LANGUAGE[$this->lang]['label_choose_redbox_point'] ?>
			                	</div>
			                	<div class="sub-title">
			                		<?php echo REDBOX_LANGUAGE[$this->lang]['label_choose_redbox_point_sub'] ?>
			                	</div>
			                	<div class="msg-choose-point-required">
			                		<?php echo REDBOX_LANGUAGE[$this->lang]['label_waring_selecte_point_required'] ?>
			                	</div>
	                			<div class="area-map">
	                				<div class="pac-card" id="pac-card">
			                			
			                		</div>
			                		<div id="pac-container">
		                				<input type="text" id="pac-input" placeholder= "<?php echo REDBOX_LANGUAGE[$this->lang]['search']; ?>...">
                                        <div class="mapSearchResults" id="results"></div>
		                				<img src="<?php echo REDBOX_BASE_HOST; ?>/image_plugin/search.png" class="search-icon">
		                			</div>
	                				<div id="area-map">
			                		
			                		</div>	
			                		<div class="wrap-area" id="wrap-area-choose-point">
			                			
			                		</div>			
	                			</div>
			                </div>
			            </div>
			        </div>
                <script>
                    <?php
                        $urlQueryTokenMap = REDBOX_URL_GET_TOKEN_APPLE;
                        $options = array(
                            'headers' => array(
                                'Authorization' => 'Bearer ' . $this->redboxKey
                            ),
                            'timeout' => 10
                        );
                        $response = wp_remote_get($urlQueryTokenMap, $options);
                        $bodyTokenMap = json_decode( wp_remote_retrieve_body( $response ), true );
                    ?>
                    document.addEventListener("DOMContentLoaded", () => {
                        mapkit.init({
                            authorizationCallback: function (done) {
                                done("<?php echo $bodyTokenMap['token']; ?>");
                            },
                            language: "<?php echo $this->lang; ?>"
                        });
                    });
                </script>
		        <?php
			}

			function redbox_get_list_point(){
				$lat = sanitize_text_field($_REQUEST['lat']);
				$lng = sanitize_text_field($_REQUEST['lng']);
				$distance = sanitize_text_field($_REQUEST['distance']);
				$urlQuery = REDBOX_URL_GET_LIST_POINTS . '?lat=' . $lat . '&lng=' . $lng . '&distance=' . $distance;
				$options = array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $this->redboxKey
					),
					'timeout' => 10
				); 
				$response = wp_remote_get($urlQuery, $options);
				$body = wp_remote_retrieve_body($response);
				echo ($body);
				wp_die();
			}

			private function redbox_validate_body_create_shipment($dataShipment = []){
				if (isset($dataShipment["store_url"]) && !empty($dataShipment["store_url"]) &&
					isset($dataShipment["reference"]) && !empty($dataShipment["reference"]) &&
					isset($dataShipment["point_id"]) && !empty($dataShipment["point_id"]) &&
					isset($dataShipment["customer_phone"]) && !empty($dataShipment["customer_phone"])
				) {
					return true;
				} else {
					return false;
				}
			}

			function redbox_create_shipment( $order_id) {
				$order = new WC_Order( $order_id );
				$note = 'Creating redbox shipments';
				$order->add_order_note( $note );
			    if (!$order) {
			    	$note = 'RedBox error: Order not found';
					$order->add_order_note( $note );
			    }
		        $orderKey = $order->get_order_key();
		        $orderNumber = $order->get_order_number();

		        $customerData = $this->redbox_get_data_customer($order);
		        $priceData = $this->redbox_get_other_info($order);

		        $dataShipment = [
		        	"store_url" => get_home_url(),
		        	"reference" => $order_id,
		        	"point_id" => get_post_meta( $order_id, '_redbox_point_id', true ),
		        	"customer_name" => $customerData['name'],
		        	"customer_phone" => $customerData['phone'],
		        	"customer_email" => $customerData['email'],
		        	"customer_address" => $customerData['adddress'],
		        	"cod_amount" => "",
		        	"cod_currency" => "",
		        	"shipping_price" => $priceData['shipping_total'],
		        	"shipping_price_currency" => $order->get_currency(),
		        	"items" => $this->redbox_get_product($order),
		        	"from_platform" => "woo_commerce"
		        ];
				$dataShipment["is_paid"] = $order->is_paid();
				$dataShipment["get_payment_method"] = $order->get_payment_method();
		        if ($order->get_payment_method() == "cod") {
					$dataShipment['cod_amount'] = $priceData['order_total'];
					$dataShipment['cod_currency'] = $priceData['curency'];
				} else {
                    $dataShipment['cod_amount'] = 0;
                    $dataShipment['cod_currency'] = $priceData['curency'];
				}
		        if ($this->redbox_validate_body_create_shipment($dataShipment)) {
			        $urlQuery = REDBOX_URL_CREATE_SHIPMENT;
					$options = array(
						'headers' => array(
							'Authorization' => 'Bearer ' . $this->redboxKey
						),
						'body' => $dataShipment,
						'timeout' => 60
					); 
					$response = wp_remote_post($urlQuery, $options);
					$body = json_decode( wp_remote_retrieve_body( $response ), true );
					if ($body['success']) {
						$note = 'RedBox tracking number: ' . $body['tracking_number'];
						$order->add_order_note( $note );
						$order->update_meta_data( 'redbox_shipment_url_shipping_label', $body['shipping_label_url'] );
					} else {
						$note = 'RedBox error: ' . $body['msg'];
						$order->add_order_note( $note );
					}
			        
			        $order->save();
			    }
			}

			private function redbox_validate_body_update_shipment($dataShipment = []){
				if (isset($dataShipment["customer_phone"]) && !empty($dataShipment["customer_phone"])) {
					return true;
				} else {
					return false;
				}
			}

            function redbox_update_shipment( $order_id, $order ) {
                if ( is_admin() ) {
                    $orderKey = $order->get_order_key();
                    $orderNumber = $order->get_order_number();

                    $customerData = $this->redbox_get_data_customer($order);
                    $priceData = $this->redbox_get_other_info($order);
                    $current_user = wp_get_current_user();

                    $dataShipment = [
                        "reference" => $order_id,
                        "point_id" => get_post_meta( $order_id, '_redbox_point_id', true ),
                        "customer_name" => $customerData['name'],
                        "customer_phone" => $customerData['phone'],
                        "customer_email" => $customerData['email'],
                        "customer_address" => $customerData['adddress'],
                        "cod_amount" => "",
                        "cod_currency" => "",
                        "shipping_price" => $priceData['shipping_total'],
                        "shipping_price_currency" => $order->get_currency(),
                        "items" => $this->redbox_get_product($order),
                        "status" => $order->get_status(),
                        "action_by" => $current_user ? $current_user->user_login : "business_admin"
                    ];
                    if ($order->get_payment_method() == "cod") {
                        $dataShipment['cod_amount'] = $priceData['order_total'];
                        $dataShipment['cod_currency'] = $priceData['curency'];
                    } else {
                        $dataShipment['cod_amount'] = 0;
                        $dataShipment['cod_currency'] = $priceData['curency'];
                    }
                    if ($this->redbox_validate_body_update_shipment($dataShipment)) {
                        $urlQuery = REDBOX_URL_UPDATE_SHIPMENT;
                        $options = array(
                            'headers' => array(
                                'Authorization' => 'Bearer ' . $this->redboxKey
                            ),
                            'body' => $dataShipment,
                            'timeout' => 60
                        );
                        $response = wp_remote_post($urlQuery, $options);
                    }
                }
			}
  
			function redbox_add_point_field( $checkout ) { 
				echo '<div id="area-point-selected" class="area-point-selected">';
    			echo '<h3>' . REDBOX_LANGUAGE[$this->lang]['label_redbox_point'] . '</h3>';
				woocommerce_form_field( 'redbox_point', array(        
				  'type' => 'text',        
				  'class' => array( 'form-row-wide' ),        
				  'required' => false,     
				  'readonly' => true   
				), $checkout->get_value( 'redbox_point' ) );
				echo '<span class="bt-change-point">'. REDBOX_LANGUAGE[$this->lang]['label_edit_point'] .'</span>'; 
				woocommerce_form_field( 'redbox_point_id', array(        
				  'type' => 'text',        
				  'class' => array( 'form-row-wide' ),        
				  'required' => false,     
				  'readonly' => true   
				), $checkout->get_value( 'redbox_point_id' ) );
				echo '</div>';
			}
  
			function redbox_save_redbox_point_when_create_order( $order_id ) { 
			    if ($_POST['redbox_point']) {
			    	$point_info_full = REDBOX_LANGUAGE[$this->lang]['label_redbox_point']. ": " . $_POST['redbox_point'];
			    	update_post_meta( $order_id, '_redbox_point', sanitize_text_field($point_info_full));
			    	update_post_meta( $order_id, '_shipping_address_1', sanitize_text_field($point_info_full));
			    }
			    if ($_POST['redbox_point_id']) {
			    	update_post_meta($order_id, '_redbox_point_id', sanitize_text_field( $_POST['redbox_point_id']));
			    }
			} 
			   
			function redbox_show_redbox_point_in_admin_order_detail(  $order ) {    
			   $order_id = $order->get_id();
			   if (get_post_meta( $order_id, '_redbox_point', true )) {
			   		echo '<p><strong>'. REDBOX_LANGUAGE[$this->lang]['label_redbox_point'] .':</strong> ' . esc_html(get_post_meta( $order_id, '_redbox_point', true )) . '</p>';
			   }
			}

			function redbox_show_redbox_point_when_create_order_success( $order_id ){  
			    $order = wc_get_order( $order_id );

			    if ($order->get_meta( '_redbox_point' )) {
			    	echo '<h2>'. REDBOX_LANGUAGE[$this->lang]['label_redbox_point'] . '</h2><p>'. esc_html($order->get_meta( '_redbox_point' )) . '</p>' ;
			    }
			}

			function redbox_validate_with_redbox_pickup() {
				$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
				$chosen_shipping = $chosen_methods[0];

				if ( $chosen_shipping == 'redbox_pickup_delivery' && empty( $_POST['redbox_point'] ) ){
					wc_add_notice(REDBOX_LANGUAGE[$this->lang]['label_waring_selecte_point_required'], 'error' );
				}
			}
			function redbox_add_button_print_label( $order_id ) {
				$order = wc_get_order( $order_id );
				if ($order->get_meta( 'redbox_shipment_url_shipping_label' )) {
			    	echo '<div><a target="_blank" href="'. $order->get_meta( 'redbox_shipment_url_shipping_label' ) .'" style="line-height: 30px;border: 0px;background: #BB1E10;color: #fff;border-radius: 3px;cursor: pointer;display: inline-block;padding: 0px 20px;text-decoration: initial;">'. REDBOX_LANGUAGE[$this->lang]['print_shipping_label'] .'</a></div>';
			    }
			}

			function bbloomer_woocommerce_tiered_shipping( $rates, $package ) {
				 if ($this->minimumAmount == false) {
				 	return $rates;
				 }
				 $total = WC()->cart->subtotal;
				 if ($total >= $this->minimumAmount) {
				 	if ($rates["redbox_pickup_delivery"]) {
				 		$rates["redbox_pickup_delivery"]->cost = 0;
				 		$rates["redbox_pickup_delivery"]->taxes = 0;
				 	}
				 }
			     return $rates;
			}

    		function init() {
	            add_action('woocommerce_after_checkout_form', array( $this, 'redbox_create_modal_redbox'));
	            add_action('wp_ajax_getlispoint', array( $this, 'redbox_get_list_point' ));
	            add_action('wp_ajax_nopriv_getlispoint', array( $this, 'redbox_get_list_point' ));
	            add_action('woocommerce_thankyou', array( $this, 'redbox_create_shipment' ));
	            add_action( 'woocommerce_after_checkout_billing_form', array($this, 'redbox_add_point_field') );


	            add_action( 'woocommerce_checkout_update_order_meta', array($this, 'redbox_save_redbox_point_when_create_order') );
	            add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this, 'redbox_show_redbox_point_in_admin_order_detail'));
	            add_action( 'woocommerce_checkout_order_processed', array($this, 'redbox_show_redbox_point_when_create_order_success') );
	            add_action( 'woocommerce_view_order', array($this, 'redbox_show_redbox_point_when_create_order_success') );
	            add_action('woocommerce_checkout_process', array($this, 'redbox_validate_with_redbox_pickup'));
	            add_action( 'woocommerce_update_order', array($this, 'redbox_update_shipment'), 10, 2);
	            add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this, 'redbox_add_button_print_label'));
	            add_filter( 'woocommerce_package_rates', array($this, 'bbloomer_woocommerce_tiered_shipping'), 10, 2 );
         	}
          
			public static function redbox_instance() {
				if (!isset(self::$redbox_instance)) {
				    self::$redbox_instance = new self();
				    self::$redbox_instance->init();
				}
				return self::$redbox_instance;
			} 
    	}
    	REDBOX_Front::redbox_instance();
    }
?>