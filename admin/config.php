<?php
if (!defined('ABSPATH'))
  exit;

if (!class_exists('REDBOX_Admin_Menu')) {
    class REDBOX_Admin_Menu {
        protected static $redbox_instance;

            public $lang;

            public function __construct() {
                $this->lang = get_locale() == "ar" ? "ar" : "en";
            }

            function redbox_add_to_sub_menu() {
                add_submenu_page( 'woocommerce', 'Redbox Pickup', 'Redbox Pickup', 'manage_options', 'redbox-pickup',array($this, 'renderView'));
            }

            function renderView() {
            ?>    
                <div class="redbox-pick-up-admin" style="
                    width: 500px;
                    background: #fff;
                    padding: 20px;
                    margin-top: 20px;
                    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                    border-radius: 5px;
                ">
                    <div>
                        <h2 style="margin: 0px"><u style="text-decoration: unset;font-size: 20px;"><?php echo REDBOX_LANGUAGE[$this->lang]['redbox_pickup_setting'] ?></u></h2>
                        <?php if(isset($_REQUEST['message']) && $_REQUEST['message'] == 'success'){ ?>
                            <div class="notice notice-success is-dismissible"> 
                                <p><strong><?php echo REDBOX_LANGUAGE[$this->lang]['update_success'] ?></strong></p>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="ocwqv-container">
                        <form method="post" >
                            <div class="ocwma_cover_div" style="margin-top: 10px;margin-bottom: 20px;">
                                <p style="font-size: 14px; margin: 0px 0px 5px 0px">
                                    <?php echo REDBOX_LANGUAGE[$this->lang]['key_of_store_on_redbox_system'] ?>
                                    <span style="color: red; margin: 0px 5px">*</span>
                                </p>
                                <div>
                                    <input type="text" required name="redbox_key"
                                        value="<?php 
                                            if(!empty(get_option( 'redbox_key' ))) { 
                                                echo get_option( 'redbox_key' ); 
                                            } else {
                                                echo "";
                                            }
                                        ?>"
                                        style="
                                            width: 100%;
                                            height: 35px;
                                            line-height: 35px;
                                        " 
                                    >
                                </div>
                                <p style="font-size: 14px; margin: 10px 0px 5px 0px">
                                    <?php echo REDBOX_LANGUAGE[$this->lang]['min_total_price_for_free'] ?>
                                </p>
                                <div>
                                    <input type="number" min="0" step="any" name="min_price_for_free"
                                        value="<?php 
                                            if(!empty(get_option( 'min_price_for_free' ))) { 
                                                echo get_option( 'min_price_for_free' ); 
                                            } else {
                                                echo "";
                                            }
                                        ?>"
                                        style="
                                            width: 100%;
                                            height: 35px;
                                            line-height: 35px;
                                        " 
                                    >
                                </div>
                                <div style="margin-top: 20px;color: #f11000;font-size: 14px;">
                                    <i><?php echo REDBOX_LANGUAGE[$this->lang]['note_update'] ?></i>
                                    <a target="_blank" href="<?php echo esc_url(REDBOX_URL_GUILE_GET_KEY_WC); ?>"><?php echo REDBOX_LANGUAGE[$this->lang]['how_generate'] ?></a>
                                </div>
                                <p style="font-size: 14px; margin: 10px 0px 5px 0px">
                                    <?php echo REDBOX_LANGUAGE[$this->lang]['consumer_key'] ?>
                                </p>
                                <div>
                                    <input type="text" name="consumer_key"
                                        value="<?php 
                                            if(!empty(get_option( 'consumer_key' ))) { 
                                                echo get_option( 'consumer_key' ); 
                                            } else {
                                                echo "";
                                            }
                                        ?>"
                                        style="
                                            width: 100%;
                                            height: 35px;
                                            line-height: 35px;
                                        " 
                                    >
                                </div>
                                <p style="font-size: 14px; margin: 20px 0px 5px 0px">
                                    <?php echo REDBOX_LANGUAGE[$this->lang]['consumer_secret'] ?>
                                </p>
                                <div>
                                    <input type="text" name="consumer_secret"
                                        value="<?php 
                                            if(!empty(get_option( 'consumer_secret' ))) { 
                                                echo get_option( 'consumer_secret' ); 
                                            } else {
                                                echo "";
                                            }
                                        ?>"
                                        style="
                                            width: 100%;
                                            height: 35px;
                                            line-height: 35px;
                                        " 
                                    >
                                </div>
                            </div>
                            <input type="hidden" name="action" value="ocwma_save_option">
                            <input type="submit" value="<?php echo REDBOX_LANGUAGE[$this->lang]['save_change'] ?>" name="submit" class="button-primary" id="wfc-btn-space">
                        </form>  
                    </div>
                </div>
            <?php
            }
            
            function redbox_save_options(){
                if( current_user_can('administrator') ) { 
                    if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'ocwma_save_option'){
                        if(!isset( $_POST['redbox_key'] )){
                            print 'Sorry, your nonce did not verify.';
                            exit;
                        } else {
                            update_option('redbox_key', sanitize_text_field( $_REQUEST['redbox_key'] ), 'yes');
                            $storeUrl = get_home_url();
                            $dataStore = [
                                "store_url" => $storeUrl
                            ];
                            if (isset($_POST['min_price_for_free'])) {
                                update_option('min_price_for_free', sanitize_text_field( $_REQUEST['min_price_for_free'] ), 'yes');
                            }
                            if (isset($_POST['consumer_key'])) {
                                $dataStore['consumer_key'] = $_POST['consumer_key'];
                                update_option('consumer_key', sanitize_text_field( $_REQUEST['consumer_key'] ), 'yes');
                            }
                            if (isset($_POST['consumer_secret'])) {
                                $dataStore['consumer_secret'] = $_POST['consumer_secret'];
                                update_option('consumer_secret', sanitize_text_field( $_REQUEST['consumer_secret'] ), 'yes');
                            }
                            $urlQuery = REDBOX_URL_SAVE_STORE_INFO;
                            $options = array(
                                'headers' => array(
                                    'Authorization' => 'Bearer ' . get_option('redbox_key')
                                ),
                                'body' => $dataStore
                            ); 
                            $response = wp_remote_post($urlQuery, $options);
                        }
                    }
                }
            }

            function init() {
                add_action( 'admin_menu',  array($this, 'redbox_add_to_sub_menu'));
                add_action( 'init',  array($this, 'redbox_save_options')); 
            }

            public static function redbox_instance() {
                if (!isset(self::$redbox_instance)) {
                    self::$redbox_instance = new self();
                    self::$redbox_instance->init();
                }
            return self::$redbox_instance;
        }
    }

    REDBOX_Admin_Menu::redbox_instance();
}

