<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WC_Product_PW_Gift_Card' ) ) :

class WC_Product_PW_Gift_Card extends WC_Product_Variable {

    protected $extra_data = array(
        'pwgc_show_recipient_name' => false,
        'pwgc_custom_amount_allowed' => true,
        'pwgc_custom_amount_min' => '5',
        'pwgc_custom_amount_max' => '1000',
        'pwgc_is_physical_card' => false,
        'pwgc_no_coupons_allowed' => false,
        'pwgc_physical_email' => '',
        'pwgc_expire_days' => '',
        'pwgc_design_id' => '0',
        'pwgc_enable_bonus' => false,
        'pwgc_cumulative_bonus' => false,
        'pwgc_bonus_structure' => array(),
        'pwgc_bonus_recipient' => 'purchasing_customer',
    );


    /*
     *
     * Getters
     *
     */
    public function get_type() {
        if ( !is_admin() && defined( 'THEMECOMPLETE_EPO_PLUGIN_FILE' ) ) {
            return 'variable';
        } else {
            return PWGC_PRODUCT_TYPE_SLUG;
        }
    }

    public function is_type( $type ) {
        return (
            // Some themes/plugins will check to see if this is a Variable type before including files required for
            // the gift card product to work correctly. By checking for 'variable' we make this compatible with these
            // types of themes and plugins.
            $this->get_type() === $type || 'variable' === $type
            || ( is_array( $type ) && ( in_array( $this->get_type(), $type ) || in_array( 'variable', $type ) ) )
        );
    }

    public function get_variation_default_attribute( $attribute_name ) {
        $defaults       = $this->get_default_attributes();
        $attribute_name = sanitize_title( $attribute_name );

        if ( isset( $defaults[ $attribute_name ] ) ) {
            return $defaults[ $attribute_name ];
        }

        return '';
    }

    public function get_pwgc_show_recipient_name( $context = 'view' ) {
        return $this->get_prop( 'pwgc_show_recipient_name', $context );
    }

    public function get_pwgc_custom_amount_allowed( $context = 'view' ) {
        return $this->get_prop( 'pwgc_custom_amount_allowed', $context );
    }

    public function get_pwgc_custom_amount_min( $context = 'view' ) {
        return $this->get_prop( 'pwgc_custom_amount_min', $context );
    }

    public function get_pwgc_custom_amount_max( $context = 'view' ) {
        return $this->get_prop( 'pwgc_custom_amount_max', $context );
    }

    public function get_pwgc_is_physical_card( $context = 'view' ) {
        return $this->get_prop( 'pwgc_is_physical_card', $context );
    }

    public function get_pwgc_no_coupons_allowed( $context = 'view' ) {
        return $this->get_prop( 'pwgc_no_coupons_allowed', $context );
    }

    public function get_pwgc_physical_email( $context = 'view' ) {
        return $this->get_prop( 'pwgc_physical_email', $context );
    }

    public function get_pwgc_expire_days( $context = 'view' ) {
        return $this->get_prop( 'pwgc_expire_days', $context );
    }

    public function get_pwgc_design_id( $context = 'view' ) {
        return $this->get_prop( 'pwgc_design_id', $context );
    }

    public function get_pwgc_enable_bonus( $context = 'view' ) {
        return $this->get_prop( 'pwgc_enable_bonus', $context );
    }

    public function get_pwgc_cumulative_bonus( $context = 'view' ) {
        return $this->get_prop( 'pwgc_cumulative_bonus', $context );
    }

    public function get_pwgc_bonus_structure( $context = 'view' ) {
        return $this->get_prop( 'pwgc_bonus_structure', $context );
    }

    public function get_pwgc_bonus_recipient( $context = 'view' ) {
        return $this->get_prop( 'pwgc_bonus_recipient', $context );
    }

    // Overwritten to support WPML translations for fields.
    function get_prop( $prop, $context = 'view' ) {
        if ( strpos( $prop, 'pwgc' ) === 0 ) {
            global $sitepress;

            if ( function_exists( 'icl_object_id' ) && isset( $sitepress ) ) {
                $current_language = $sitepress->get_current_language();
                $default_language = $sitepress->get_default_language();
                if ( $current_language != $default_language ) {
                    $original_product_id = icl_object_id( $this->get_id(), 'product', TRUE, $default_language );
                    if ( !empty( $original_product_id ) && $original_product_id != $this->get_id() ) {
                        return get_post_meta( $original_product_id, '_' . $prop, true );
                    }
                }
            }
        }

        return parent::get_prop( $prop, $context );
    }



    /*
     *
     * Setters
     *
     */
    public function set_pwgc_show_recipient_name( $pwgc_show_recipient_name ) {
        $this->set_prop( 'pwgc_show_recipient_name', $pwgc_show_recipient_name );
    }

    public function set_pwgc_custom_amount_allowed( $pwgc_custom_amount_allowed ) {
        $this->set_prop( 'pwgc_custom_amount_allowed', $pwgc_custom_amount_allowed );
    }

    public function set_pwgc_custom_amount_min( $pwgc_custom_amount_min ) {
        $this->set_prop( 'pwgc_custom_amount_min', $pwgc_custom_amount_min );
    }

    public function set_pwgc_custom_amount_max( $pwgc_custom_amount_max ) {
        $this->set_prop( 'pwgc_custom_amount_max', $pwgc_custom_amount_max );
    }

    public function set_pwgc_is_physical_card( $pwgc_is_physical_card ) {
        $this->set_prop( 'pwgc_is_physical_card', $pwgc_is_physical_card );
    }

    public function set_pwgc_no_coupons_allowed( $pwgc_no_coupons_allowed ) {
        $this->set_prop( 'pwgc_no_coupons_allowed', $pwgc_no_coupons_allowed );
    }

    public function set_pwgc_physical_email( $pwgc_physical_email ) {
        $this->set_prop( 'pwgc_physical_email', $pwgc_physical_email );
    }

    public function set_pwgc_expire_days( $pwgc_expire_days ) {
        $this->set_prop( 'pwgc_expire_days', $pwgc_expire_days );
    }

    public function set_pwgc_design_id( $pwgc_design_id ) {
        $this->set_prop( 'pwgc_design_id', $pwgc_design_id );
    }

    public function set_pwgc_enable_bonus( $pwgc_enable_bonus ) {
        $this->set_prop( 'pwgc_enable_bonus', $pwgc_enable_bonus );
    }

    public function set_pwgc_cumulative_bonus( $pwgc_cumulative_bonus ) {
        $this->set_prop( 'pwgc_cumulative_bonus', $pwgc_cumulative_bonus );
    }

    public function set_pwgc_bonus_structure( $pwgc_bonus_structure ) {
        $this->set_prop( 'pwgc_bonus_structure', $pwgc_bonus_structure );
    }

    public function set_pwgc_bonus_recipient( $pwgc_bonus_recipient ) {
        $this->set_prop( 'pwgc_bonus_recipient', $pwgc_bonus_recipient );
    }


    /*
     *
     * Other public methods
     *
     */
    public function save() {
        $this->sync_other_amount_variation();

        if ( $this->get_pwgc_is_physical_card() ) {
            $this->set_variations_as_virtual( '0' );
        } else {
            $this->set_variations_as_virtual( '1' );
        }

        parent::save();
    }

    public function get_price_html( $price = '' ) {
        if ( $this->get_pwgc_custom_amount_allowed() ) {
            $min = apply_filters( 'pwgc_to_current_currency', $this->get_pwgc_custom_amount_min() );
            $max = apply_filters( 'pwgc_to_current_currency', $this->get_pwgc_custom_amount_max() );

            $price = wc_format_price_range( $min, $max );
            $price = apply_filters( 'woocommerce_variable_price_html', $price . $this->get_price_suffix(), $this );

            return apply_filters( 'woocommerce_get_price_html', $price, $this );

        } else {
            return parent::get_price_html( $price );
        }
    }

    public function add_amount( $amount ) {
        global $pw_gift_cards;

        if ( $pw_gift_cards->numeric_price( $amount ) <= 0 ) {
            return __( 'Amount must be greater than zero.', 'pw-woocommerce-gift-cards' );
        }

        $variations = array_map( 'wc_get_product', $this->get_children() );

        // Check for existing amount.
        foreach ( $variations as $variation ) {
            $variation_attributes = $variation->get_attributes();

            if ( isset( $variation_attributes[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) ) {
                $variation_option = $variation_attributes[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ];

                if ( $pw_gift_cards->equal_prices( $variation_option, $amount ) ) {
                    return __( 'Amount already exists: ', 'pw-woocommerce-gift-cards' ) . $amount;
                }
            }
        }

        $variation_id = $this->create_variation( $amount );

        if ( $variation_id ) {

            $this->save();

            return $variation_id;
        } else {
            return __( 'Could not create variation.', 'pw-woocommerce-gift-cards' );
        }
    }

    public function delete_amount( $variation_id ) {
        if ( $variation = wc_get_product( $variation_id ) ) {
            $variation->delete( true );

            // Add the new variation to the current object's children list.
            $children = $this->get_children();
            if ( ( $key = array_search( $variation_id, $children ) ) !== false ) {
                unset( $children[ $key ] );
            }
            $this->set_children( $children );

            $this->sync_gift_card_amount_attributes();

            $this->save();

            return true;
        } else {
            return __( 'Could not locate variation using variation_id ', 'pw-woocommerce-gift-cards' ) . $variation_id;
        }
    }

    // 10/29/2019
    // $items may be an array of $cart_items or an array of $order_items
    // Likewise, $item is either a $cart_item or an $order_item. As of the
    // date this was written, both had the same signatures.
    public function get_bonus_amount( $items, $item ) {
        // No point doing anything if they haven't enabled the bonus.
        if ( ! $this->get_pwgc_enable_bonus() ) {
            return 0;
        }

        // This shouldn't happen often but in case the user has
        // enabled the bonus but did not specify amounts we bail.
        $bonus_structure = $this->get_pwgc_bonus_structure();
        if ( empty( $bonus_structure ) ) {
            return 0;
        }

        // In order to award the highest bonus amount that qualifies,
        // sort in reverse order from largest bonus amount to smallest.
        krsort( $bonus_structure );

        // Calculate the total amount purchased for this gift card.
        // This will either be a cumulative total or a single amount, depending
        // on the configuration for this card.
        $total = 0;
        $bonus_amount = 0;
        if ( $this->get_pwgc_cumulative_bonus() ) {
            // Bonus card will be added to the first gift card in the cart only for cumulative bonus.
            // This way the customer can purchase multiple gift cards to reach the bonus amount.
            if ( pwgc_is_first_gift_card( $items, $item ) ) {
                foreach ( $items as $loop_item ) {
                    // Only sum for the same gift card type so that we don't mis-calculate from other cards.
                    if ( isset( $loop_item['product_id'] ) && $loop_item['product_id'] == $item['product_id'] ) {
                        $total += $loop_item['line_subtotal'];
                    }
                }
            }
        } else {
            // Non-cumulative means the gift card must match exactly.
            $total = $item['line_subtotal'] / $item['quantity'];
        }

        // Allow other plugins to hook in at this point.
        $total = floatval( apply_filters( 'pwgc_bonus_line_total', $total, $items, $item ) );

        // Now that we have the total amount purchased, we need to see if they reached
        // the bonus.
        if ( $total > 0 ) {
            // Step through each bonus option and let's see if it qualifies.
            foreach( $bonus_structure as $spend => $get ) {
                $spend = floatval( $spend );

                // At a minimum they have to spend the qualifying amount.
                if ( $total >= $spend ) {

                    // Cumulative bonuses are added together here.
                    if ( $this->get_pwgc_cumulative_bonus() ) {
                        $bonus_amount = $get * floor( $total / $spend );
                    } else {
                        $bonus_amount = $get;
                    }

                    // Stop after our first match since we're checking in
                    // reverse order from highest bonus to lowest.
                    break;
                }
            }

            // Non-cumulative bonuses are awarded for each gift card purchased,
            // rather than a total amount.
            if ( ! $this->get_pwgc_cumulative_bonus() ) {
                $bonus_amount *= $item['quantity'];
            }
        }

        // Allow other plugins a chance to override the bonus calculation.
        return apply_filters( 'pwgc_calculated_bonus', $bonus_amount, $this, $total, $items, $item );
    }

    public function has_amount_on_sale() {
        $result = false;

        $variations = array_map( 'wc_get_product', $this->get_children() );
        foreach( $variations as $variation ) {
            if ( $variation->is_on_sale() ) {
                $result = true;
                break;
            }
        }

        return $result;
    }



    /*
     *
     * Protected methods
     *
     */
    protected function create_variation( $amount ) {
        global $pw_gift_cards;

        $variation = new WC_Product_Variation();
        $variation->set_parent_id( $this->get_id() );
        if ( $this->get_pwgc_is_physical_card() ) {
            $variation->set_virtual( '0' );
        } else {
            $variation->set_virtual( '1' );
        }

        $other_amount_prompt = pwgc_get_other_amount_prompt( $this );
        if ( $amount == $other_amount_prompt ) {
            $variation->set_regular_price( 0 );
            $variation->set_attributes( array( PWGC_DENOMINATION_ATTRIBUTE_SLUG => $other_amount_prompt ) );
        } else {
            $variation->set_regular_price( $pw_gift_cards->numeric_price( $amount ) );
            $variation->set_attributes( array( PWGC_DENOMINATION_ATTRIBUTE_SLUG => $pw_gift_cards->pretty_price( $amount ) ) );
        }

        do_action( 'product_variation_linked', $variation->save() );

        // Add the new variation to the current object's children list.
        $children = $this->get_children();
        array_push( $children, $variation->get_id() );
        $this->set_children( $children );

        $this->sync_gift_card_amount_attributes();

        return $variation->get_id();
    }

    public function sync_gift_card_amount_attributes() {
        global $post;
        global $pw_gift_cards;
        global $pw_gift_cards_admin;
        global $wpdb;

        $pw_gift_cards->set_current_currency_to_default();

        $variations = array_map( 'wc_get_product', $this->get_children() );

        // Re-order all Variations based on the amount.
        uasort( $variations, array( $pw_gift_cards, 'price_sort' ) );

        $index = 0;
        foreach( $variations as $variation ) {
            $wpdb->update( $wpdb->posts, array( 'menu_order' => $index ), array( 'ID' => absint( $variation->get_id() ) ) );
            $index++;

            // Ensure that the attributes are correct on the variations.
            $amount = $variation->get_regular_price();
            if ( $amount == 0 ) {
                $variation->set_attributes( array( PWGC_DENOMINATION_ATTRIBUTE_SLUG => pwgc_get_other_amount_prompt( $this ) ) );
            } else {
                $variation->set_attributes( array( PWGC_DENOMINATION_ATTRIBUTE_SLUG => $pw_gift_cards->pretty_price( $amount ) ) );
            }
            $variation->save();
        }

        $options = array();
        foreach ( $variations as $variation ) {
            $price = apply_filters( 'pwgc_to_default_currency', $variation->get_regular_price() );
            if ( !in_array( $price, $options ) && $price > 0 ) {
                $options[] = $price;
            }
        }

        $other_amount_prompt = pwgc_get_other_amount_prompt( $this );
        if ( $this->get_pwgc_custom_amount_allowed() && !in_array( $other_amount_prompt, $options ) ) {
            $options[] = $other_amount_prompt;
        }

        $attributes = $this->get_attributes();

        $attribute = new WC_Product_Attribute();
        $attribute->set_name( 'Gift Card Amount' );
        $attribute->is_taxonomy( 0 );
        $attribute->set_position( 0 );
        $attribute->set_visible( apply_filters( 'pw_gift_cards_amount_attribute_visible_on_product_page', true, $this ) );
        $attribute->set_variation( '1' );

        $options = array_map( array( $pw_gift_cards, 'pretty_price' ), $options );

        $attribute->set_options( $options );

        $attributes[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] = $attribute;

        $this->set_attributes( $attributes );

        if ( !empty( $post ) && $post->post_type == 'product' && method_exists( $this, 'save' ) ) {
            $this->save();
        }
    }

    protected function sync_other_amount_variation() {

        $custom_amount_variation = false;
        $other_amount_prompt = pwgc_get_other_amount_prompt( $this );

        $variations = array_map( 'wc_get_product', $this->get_children() );
        foreach ( $variations as $index => $variation ) {
            if ( is_a( $variation, 'WC_Product' ) ) {
                $variation_attributes = $variation->get_attributes();
                if ( isset( $variation_attributes[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) && strtolower( trim( $variation_attributes[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) ) == strtolower( trim( $other_amount_prompt ) ) ) {
                    $custom_amount_variation = $variation;
                    break;
                }
            }
        }

        if ( $this->get_pwgc_custom_amount_allowed() ) {
            if ( !$custom_amount_variation ) {
                $this->create_variation( $other_amount_prompt );
            }

        } else if ( $custom_amount_variation ) {
            $custom_amount_variation->delete( true );
        }
    }

    protected function set_variations_as_virtual( $virtual ) {
        $variations = array_map( 'wc_get_product', $this->get_children() );
        foreach ( $variations as $index => $variation ) {
            if ( is_a( $variation, 'WC_Product' ) && $variation->get_virtual() != $virtual ) {
                $variation->set_virtual( $virtual );
                $variation->save();
            }
        }
    }
}

// Uses the Variable template for the gift card product type.
add_action( 'woocommerce_' . PWGC_PRODUCT_TYPE_SLUG . '_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );

if ( 'yes' === get_option( 'pwgc_before_add_to_cart_quantity_theme_fix', 'no' ) ) {
    function pwgc_before_add_to_cart_quantity_theme_fix() {
        global $product;

        if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) && !isset( $GLOBALS['pwgc_theme_fix_applied'] ) ) {
            $GLOBALS['pwgc_theme_fix_applied'] = true;
            do_action( 'woocommerce_before_add_to_cart_quantity' );
        }
    }

    add_action( 'woocommerce_before_single_variation', 'pwgc_before_add_to_cart_quantity_theme_fix', 9 );
    add_action( 'woocommerce_single_variation', 'pwgc_before_add_to_cart_quantity_theme_fix', 9 );
    add_action( 'woocommerce_after_single_variation', 'pwgc_before_add_to_cart_quantity_theme_fix', 9 );
}

function pwgc_woocommerce_before_add_to_cart_quantity() {
    global $product;

    if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
        wp_enqueue_script( 'pw-gift-cards' );

        if ( isset( $_REQUEST[ PWGC_RELOAD_GIFT_CARD_NUMBER_META_KEY ] ) || !empty( $product->get_meta( PWGC_RELOAD_GIFT_CARD_NUMBER_META_KEY ) ) ) {
            wc_get_template( 'single-product/add-to-cart/pw-gift-card-reload.php', array(), '', PWGC_PLUGIN_ROOT . 'templates/woocommerce/' );
        } else {
            if ( 'yes' === get_option( 'pwgc_allow_scheduled_delivery', 'yes' ) ) {
                wp_enqueue_script( 'pikaday' );
                wp_enqueue_style( 'pikaday' );
            }

            wc_get_template( 'single-product/add-to-cart/pw-gift-card-before-add-to-cart-quantity.php', array(), '', PWGC_PLUGIN_ROOT . 'templates/woocommerce/' );
        }

        // A customer's theme was calling woocommerce_before_add_to_cart_quantity multiple times so this is a fix for that scenario.
        if ( !defined( 'PWGC_BEFORE_ADD_TO_CART_QUANTITY_FIX' ) || PWGC_BEFORE_ADD_TO_CART_QUANTITY_FIX === false ) {
            remove_action( 'woocommerce_before_add_to_cart_quantity', 'pwgc_woocommerce_before_add_to_cart_quantity', 30 );
        }
    }
}
add_action( 'woocommerce_before_add_to_cart_quantity', 'pwgc_woocommerce_before_add_to_cart_quantity', 30 );


function pwgc_product_type_selector( $types ) {
    $types[ PWGC_PRODUCT_TYPE_SLUG ] = PWGC_PRODUCT_TYPE_NAME;

    return $types;
}
add_filter( 'product_type_selector', 'pwgc_product_type_selector' );


function pwgc_woocommerce_data_stores( $stores ) {
    if ( !isset( $stores[ 'product-' . PWGC_PRODUCT_TYPE_SLUG ] ) ) {
        $stores[ 'product-' . PWGC_PRODUCT_TYPE_SLUG ] = 'WC_Product_Variable_Data_Store_CPT';
    }

    return $stores;
}
add_filter( 'woocommerce_data_stores', 'pwgc_woocommerce_data_stores' );

function pwgc_process_pw_gift_card_product_meta_data( $post_id ) {
    $product = new WC_Product_PW_Gift_Card( $post_id );
    $product->sync_gift_card_amount_attributes();
}
add_action( 'woocommerce_process_product_meta_' . PWGC_PRODUCT_TYPE_SLUG, 'pwgc_process_pw_gift_card_product_meta_data' );

function pwgc_woocommerce_product_add_to_cart_text( $text, $product ) {
    if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
        return apply_filters( 'pwgc_select_amount_text', __( 'Select amount', 'pw-woocommerce-gift-cards' ), $product );
    } else {
        return $text;
    }
}
add_filter( 'woocommerce_product_add_to_cart_text', 'pwgc_woocommerce_product_add_to_cart_text', 10, 2 );

function pwgc_woocommerce_variation_option_name( $name ) {
    global $product;
    global $pw_gift_cards;

    if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
        $other_amount_prompt = pwgc_get_other_amount_prompt( $product );
        if ( $name === $other_amount_prompt ) {
            return __( $other_amount_prompt, 'pw-woocommerce-gift-cards' );
        } else if ( 'yes' === get_option( 'pwgc_format_prices', 'yes' ) && ! class_exists( 'Woo_Variation_Swatches' ) ) {
            $name = $pw_gift_cards->sanitize_amount( $name );
            $price = $pw_gift_cards->numeric_price( $name );

            $_REQUEST['woocs_block_price_hook'] = true; // Needed for WooCommerce Currency Switcher by realmag777
            $price = apply_filters( 'pwgc_to_current_currency', $price );

            return strip_tags( wc_price( $price ) );
        }
    }

    return $name;
}
add_filter( 'woocommerce_variation_option_name', 'pwgc_woocommerce_variation_option_name', 10, 2 );

endif;
