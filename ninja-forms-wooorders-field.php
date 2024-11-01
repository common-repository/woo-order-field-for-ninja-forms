<?php
/**
 * Plugin Name: Woo Order Field for Ninja Forms
 * Plugin URI: https://codeboxr.com/product/ninja-forms-woocommerce-order-field/
 * Description: This plugin add woocommerce orders of loggedin users in dropdown
 * Version: 1.0.4
 * Author: codeboxr
 * Author URI: http://www.codeboxr.com
 * License:  GPLv2 or later
 */

class CBXNinjaformWooOrdersField{
    public function __construct()
    {
        //loading translation
        load_plugin_textdomain('ninjaformwooordersfields', false, basename(dirname(__FILE__)) . '/languages/');

        add_action( 'init', array($this, 'ninja_forms_register_field_wooorders') );

    }//end method constructor

    /**
     * Register new Ninja Forms field
     */
    public function ninja_forms_register_field_wooorders()
    {
        if(function_exists('ninja_forms_register_field')){
            $args = array(
                'name'               =>  esc_html__( 'Woo Orders', 'ninjaformwooordersfields' ),
                'sidebar'            =>  'template_fields',
                'edit_function'      =>  array($this, 'ninja_forms_field_wooorders_edit'),
                'display_function'   =>  array($this, 'ninja_forms_field_wooorders_display'),
                'save_function'      =>  '',
                'group'              =>  'standard_fields',
                'edit_label'         =>  true,
                'edit_label_pos'     =>  true,
                'edit_req'           =>  true,
                'edit_custom_class'  =>  true,
                'edit_help'          =>  true,
                'edit_desc'          =>  true,
                'edit_meta'          =>  false,
                'edit_conditional'   =>  true
            );

            ninja_forms_register_field( '_wooorders', $args );
        }
    }//end method

    /**
     * Edit field in admin
     */
    public function ninja_forms_field_wooorders_edit( $field_id, $data )
    {
        $plugin_settings = nf_get_settings();


        $custom = '';

        // Default Value
        if( isset( $data['default_value'] ) ) {
            $default_value = $data['default_value'];
        } else {
            $default_value = '';
        }
        if( $default_value == 'none' ) {
            $default_value = '';
        }

        ?>
        <div class="description description-thin">

            <label for="" id="default_value_label_<?php echo $field_id;?>" style="<?php if( $custom == 'no' ) { echo 'display:none;'; } ?>">
			<span class="field-option">
			<?php _e( 'Default Value' , 'ninjaformwooordersfields' ); ?><br />
			<input type="text" class="widefat code" name="ninja_forms_field_<?php echo $field_id; ?>[default_value]" id="ninja_forms_field_<?php echo $field_id; ?>_default_value" value="<?php echo $default_value; ?>" />
			</span>
            </label>

        </div>

        <?php
    }


    /**
     * Display field on front-end
     */
    public function ninja_forms_field_wooorders_display( $field_id, $data )
    {
        global $current_user;
        $field_class = ninja_forms_get_field_class( $field_id );

        if( isset( $data['default_value'] ) ) {
            $default_value = $data['default_value'];
        } else {
            $default_value = '';
        }

        if( isset( $data['label_pos'] ) ) {
            $label_pos = $data['label_pos'];
        } else {
            $label_pos = "left";
        }

        if( isset( $data['label'] ) ) {
            $label = $data['label'];
        } else {
            $label = '';
        }





        $values     = array();
        $labels     = array();

        if(get_current_user_id() > 0){
            $customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
                'numberposts' => -1,
                'meta_key'    => '_customer_user',
                'meta_value'  => get_current_user_id(),
                'post_type'   => wc_get_order_types( 'view-orders' ),
                'post_status' => array_keys( wc_get_order_statuses() )
            ) ) );


            $i = 0;

            foreach ( $customer_orders as $customer_order ) {
                //$order = wc_get_order( $customer_order );
                $order = new WC_Order($customer_order );
                //$order->populate( $customer_order );
                //$item_count = $order->get_item_count();

                $labels[$i] = '#'.$order->get_order_number().'( '.esc_html__('Status', 'ninjaformwooordersfields').': '.wc_get_order_status_name( $order->get_status()) .')';
                $values[$i] = $order->get_order_number();
                $i++;
            }
        }



        ?>
        <select name="ninja_forms_field_<?php echo $field_id;?>" id="ninja_forms_field_<?php echo $field_id;?>" class="<?php echo $field_class;?>" rel="<?php echo $field_id;?>">

            <option value=""><?php esc_html_e('Select Order', 'ninjaformwooordersfields'); ?></option>
            <?php
            foreach($labels as $k => $label){

                $value  = $values[$k];

                $value = htmlspecialchars( $value, ENT_QUOTES );
                $label = htmlspecialchars( $label, ENT_QUOTES );
                $label = stripslashes( $label );
                $label = str_replace( '&amp;', '&', $label );

                ?>
                <option value="<?php echo $value;?>"  >  <?php echo $label;?> </option>
                <?php
            }
            ?>
        </select>
        <?php
    }
}

/**
 * Loading the plugin
 */
function ninjaform_wooorders_loader()
{
    if(class_exists('Ninja_Forms') && function_exists('wc')){
        new CBXNinjaformWooOrdersField();
    }
}//end function ninjaform_wooorders_loader

add_action('plugins_loaded', 'ninjaform_wooorders_loader');