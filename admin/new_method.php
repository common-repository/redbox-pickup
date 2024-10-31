<?php
    add_action('woocommerce_shipping_init', 'redbox_shipping');
    function redbox_shipping() {
        if ( ! class_exists( 'REDBOX_Shipping_Method' ) ) {
            class REDBOX_Shipping_Method extends WC_Shipping_Method {
                public $lang;

                public function __construct( $instance_id = 0) {
                    $this->lang = get_locale() == "ar" ? "ar" : "en";
                    $this->id = 'redbox_pickup_delivery';
                    $this->instance_id = absint( $instance_id );
                    $this->method_title = REDBOX_LANGUAGE[$this->lang]['method_title'];
                    $this->method_description = REDBOX_LANGUAGE[$this->lang]['method_description'];
                    $this->supports = array(
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal',
                    );
                    $this->enabled= "yes";
                    $this->init();
                }

                function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    $this->title = REDBOX_LANGUAGE[$this->lang]['method_title'];
                    $this->cost = $this->get_option( 'cost' );
                    $this->taxes = $this->get_option( 'taxes' );
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                function init_form_fields() {
                    $this->instance_form_fields = array(
                        'cost' => array(
                            'type'          => 'text',
                            'title'         => REDBOX_LANGUAGE[$this->lang]['cost'],
                            'default'       => '0',
                        ),
                        'taxes' => array(
                            'title'       =>  REDBOX_LANGUAGE[$this->lang]['tax_status'],
                            'type'        => 'select',
                            'description' => '',
                            'default'     => 'taxable',
                            'options'     => array(
                                'taxable' => REDBOX_LANGUAGE[$this->lang]['taxable'],
                                'none'    => REDBOX_LANGUAGE[$this->lang]['none'],
                            ),
                        ),
                    );
                }

                public function calculate_shipping( $packages = array() ) {
                    $rate = array(
                        'id'       => $this->id,
                        'label'    => $this->title,
                        'cost'     => $this->cost,
                        'taxes' => $this->taxes == "taxable" ? "" : false,
                        'calc_tax' => 'per_order'
                    );
                    $this->add_rate( $rate );
                }
            }
        }
    }

    add_filter('woocommerce_shipping_methods', 'redbox_add_request_shipping_method');
    function redbox_add_request_shipping_method( $methods ) {
        $methods['redbox_pickup_delivery'] = 'REDBOX_Shipping_Method';
        return $methods;
    }
?>
