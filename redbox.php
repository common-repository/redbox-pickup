<?php
	/**
		* Plugin Name: RedBox Pickup
		* Description: This plugin allows customers pickup package at RedBox Locker.
		* Plugin URI: https://woocommerce.com/
		* Version: 1.35
		* Author: RedBox
		* Author URI: https://redboxsa.com
		* Copyright: 2020 
	*/
	if (!defined('ABSPATH')) {
		die('-1');
	}
	if (!defined('REDBOX_PLUGIN_NAME')) {
		define('REDBOX_PLUGIN_NAME', 'Redbox delivery');
	}
	if (!defined('REDBOX_PLUGIN_VERSION')) {
		define('REDBOX_PLUGIN_VERSION', '1.0.0');
	}
	if (!defined('REDBOX_PLUGIN_FILE')) {
		define('REDBOX_PLUGIN_FILE', __FILE__);
	}
	if (!defined('REDBOX_PLUGIN_DIR')) {
		define('REDBOX_PLUGIN_DIR',plugins_url('', __FILE__));
	}
	if (!defined('REDBOX_DOMAIN')) {
		define('REDBOX_DOMAIN', 'ocwcp');
	}
	if (!defined('REDBOX_BASE_NAME')) {
		define('REDBOX_BASE_NAME', plugin_basename(REDBOX_PLUGIN_FILE));
	}
	if (!defined('REDBOX_BASE_URL')) {
			define('REDBOX_BASE_URL', 'https://app.redboxsa.com/api/business/v1');
	}
	if (!defined('REDBOX_BASE_HOST')) {
			define('REDBOX_BASE_HOST', 'https://app.redboxsa.com');
	}
	if (!defined('REDBOX_URL_GET_LIST_POINTS')) {
		define('REDBOX_URL_GET_LIST_POINTS', REDBOX_BASE_URL . "/get-points");
	}
	if (!defined('REDBOX_URL_CREATE_SHIPMENT')) {
		define('REDBOX_URL_CREATE_SHIPMENT', REDBOX_BASE_URL . "/create-shipment-v2");
	}
	if (!defined('REDBOX_URL_UPDATE_SHIPMENT')) {
		define('REDBOX_URL_UPDATE_SHIPMENT', REDBOX_BASE_URL . "/update-shipment-from-store");
	}
	if (!defined('REDBOX_URL_SAVE_STORE_INFO')) {
		define('REDBOX_URL_SAVE_STORE_INFO', REDBOX_BASE_URL . "/create-info-store-from-wc");
	}
    if (!defined('REDBOX_URL_GET_TOKEN_APPLE')) {
        define('REDBOX_URL_GET_TOKEN_APPLE', REDBOX_BASE_URL . "/apple-map-token");
    }
	if (!defined('REDBOX_URL_GUILE_GET_KEY_WC')) {
		define('REDBOX_URL_GUILE_GET_KEY_WC', "https://docs.woocommerce.com/document/woocommerce-rest-api/");
	}
	if (!defined('REDBOX_URL_SHIPMENT_DETAIL')) {
		define('REDBOX_URL_SHIPMENT_DETAIL', REDBOX_BASE_URL . "/shipment-detail");
	}
	if (!defined('REDBOX_LANGUAGE')) {
		define('REDBOX_LANGUAGE', [
			"en" => [
				"label_redbox_point" => "Redbox Point",
				"label_title_redbox_pickup" => "Collect from RedBox Points at your convenient time (store for 48 HR) Delivery",
				"label_choose_redbox_point" => "Search for a RedBox Pickup point near you",
				"label_choose_redbox_point_sub" => "RedBox Pickup points enable you to pick up your package at your convenience from a nearby self-service Locker or a staffed location",
				"label_waring_selecte_point_required" => "Please choose one Redbox point",
				"label_cancel" => "Cancel",
				"label_complete" => "Next",
				"label_edit_point" => "Select a Pickup point",
				"method_title" => "Collect from RedBox Points at your convenient time (store for 48 HR) Delivery (1-3 Days)",
	            "method_description" => "Allow customers to collect orders from RedBox Lockers, which are available around the city 24/7.",
	            "cost" => "Cost",
	            "cost_place_holder" => "Enter cost",
	            "key_of_store_on_redbox_system" => "Key of store on redbox system",
	            "update_success" => "Update successfully",
	            "save_change" => "Save change",
	            "search" => "Search by address",
	            "redbox_pickup_setting" => "Settings",
	            "note_update" => "Enter consumer key and consumer secret. We will update your order status when shipment status change.",
	            "consumer_key" => "Consumer key",
	            "consumer_secret" => "Consumer secret",
	            "how_generate" => "Guide to generate API keys",
	            "print_shipping_label" => "Print label",
	            "min_total_price_for_free" => "Minimum order amount for free shipping",
	            "tax_status" => "Tax status",
	            "taxable" => "Taxable",
	            "none" => "None"
			],
			"ar" => [
				"label_redbox_point" => "نقطة ريد بوكس",
				"label_title_redbox_pickup" => "استلم طلبك من خزائن ريد بوكس الذكية بالوقت المناسب لك (تخزين لمدة 48 ساعة) توصيل من ١-٣ أيام",
				"label_choose_redbox_point" => "الرجاء اختيار الخزانة الأقرب لك لإستلام شحنتك",
				"label_choose_redbox_point_sub" => "نقطة استلام ريد بوكس تتيح لك استلام شحناتك بالوقت والمكان المناسب لك من خزائن ذكية أو مركز استلام.",
				"label_waring_selecte_point_required" => "اختار نقطة ريد بوكس",
				"label_cancel" => "ملغية",
				"label_complete" => "التالي",
				"label_edit_point" => "اختر نقطة الاستلام",
				"method_title" => "‎استلم طلبك من خزائن ريد بوكس الذكية RedBox بالوقت المناسب لك (تخزين لمدة 48 ساعة) توصيل من ١-٣ أيام",
	            "method_description" => "استقبال الطلبات عبر خزائن ريد بوكس الذكية، متوفرة على مدى ٢٤ ساعة وبأنحاء متفرقة من المدينة.",
	            "cost" => "السعر",
	            "cost_place_holder" => "ادخل السعر",
	            "key_of_store_on_redbox_system" => "ادخل مفتاح الربط",
	            "update_success" => "تم التحديث بنجاح",
	            "save_change" => "حفظ التغيرات",
	            "search" => "بحث",
	            "redbox_pickup_setting" => "إعدادات",
	            "note_update" => "ادخل مفتاح المستهلك و سر المستهلك. سنقوم بتحديث حالة طلبك عند تغيير حالة الشحن",
	            "consumer_key" => "مفتاح المستهلك",
	            "consumer_secret" => "سر المستهلك",
	            "how_generate" => "دليل لتوليد مفاتيح واجهة برمجة التطبيقات",
	            "print_shipping_label" => "طباعة البوليصة",
	            "min_total_price_for_free" => "الحد الأدنى لمبلغ الطلب للشحن المجاني",
	            "tax_status" => "الحالة الضريبية",
	            "taxable" => "خاضع للضريبة",
	            "none" => "لا شيْ"
			]
		]);
	}

	//Main class  
	if (!class_exists('REDBOX')) {

		class REDBOX {

			protected static $redbox_instance;
			function __construct() {
					include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					add_action('admin_init', array($this, 'redboxCheckPluginState'));
			}

			function redbox_load_scrip_and_style_front() {
				wp_enqueue_style( 'redbox_font_awesome', 'https://pro.fontawesome.com/releases/v5.10.0/css/all.css' );
				wp_enqueue_style( 'redbox_front_css', REDBOX_PLUGIN_DIR . '/css/front.css', false, '1.0.21' );
				wp_enqueue_style( 'redbox_font_roboto', 'https://fonts.googleapis.com/css?family=Roboto' );
				wp_enqueue_style( 'redbox_font_cario', 'https://fonts.googleapis.com/css?family=Cairo' );
				wp_enqueue_script( 'redbox_front_js', REDBOX_PLUGIN_DIR . '/js/front.js', false, '1.0.21' );
				wp_localize_script( 'redbox_front_js', 'ajax_url', admin_url('admin-ajax.php') );
                wp_enqueue_script( 'redbox-mapkit-script', "https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js");
				$translation_array_img = REDBOX_PLUGIN_DIR;
				
				wp_localize_script( 'redbox_front_js', 'redbox', array(
                    'stylesheetUri' => $translation_array_img
                ) );
			}

			function redbox_show_notice() {

					if ( get_transient( get_current_user_id() . 'ocwmaerror' ) ) {

						deactivate_plugins( plugin_basename( __FILE__ ) );

						delete_transient( get_current_user_id() . 'ocwmaerror' );

						echo '<div class="error"><p> This plugin is deactivated because it require <a href="plugin-install.php?tab=search&s=woocommerce">WooCommerce</a> plugin installed and activated.</p></div>';

					}
			}


			function redboxCheckPluginState(){
				if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) ) {
					set_transient( get_current_user_id() . 'ocwmaerror', 'message' );
				}
			}


			function init() {
				add_action('admin_notices', array($this, 'redbox_show_notice'));
				add_action('wp_enqueue_scripts',  array($this, 'redbox_load_scrip_and_style_front'));
			}

			function includes() {
				include_once('admin/new_method.php');
				include_once('admin/config.php');
				include_once('front/front.php');
			}


			public static function redbox_do_activation() {
				set_transient('ocwma-first-rating', true, MONTH_IN_SECONDS);
			}


			public static function redbox_instance() {
				if (!isset(self::$redbox_instance)) {
					self::$redbox_instance = new self();
					self::$redbox_instance->init();
					self::$redbox_instance->includes();
				}
				return self::$redbox_instance;
			}
		}
		add_action('plugins_loaded', array('REDBOX', 'redbox_instance'));
		register_activation_hook(REDBOX_PLUGIN_FILE, array('REDBOX', 'redbox_do_activation'));
	}
?>