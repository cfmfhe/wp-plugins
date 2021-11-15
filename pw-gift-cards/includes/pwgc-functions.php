<?php

defined( 'ABSPATH' ) or exit;

if ( ! function_exists( 'pwgc_add_gift_card_to_order' ) ) {
    function pwgc_add_gift_card_to_order( $order_item_id, $credit_amount, $product, $create_note, $credit_note ) {
        $gift_card = PW_Gift_Card::create_card( $create_note );
        $gift_card->credit( $credit_amount, $credit_note );

        $expires_in_days = absint( $product->get_pwgc_expire_days() );
        if ( $expires_in_days > 0 ) {
            $expiration_date = date( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . " +$expires_in_days days" ) );
            $gift_card->set_expiration_date( $expiration_date );
        }

        wc_add_order_item_meta( $order_item_id, PWGC_GIFT_CARD_NUMBER_META_KEY, $gift_card->get_number() );

        return $gift_card;
    }
}

if ( ! function_exists( 'pwgc_is_first_gift_card' ) ) {
    function pwgc_is_first_gift_card( $items, $parameter_item ) {
        foreach ( $items as $item ) {
            if ( isset( $item['product_id'] ) ) {
                $product = wc_get_product( absint( $item['product_id'] ) );
                if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                    if ( $item == $parameter_item ) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        return false;
    }
}

if ( ! function_exists( 'pwgc_set_table_names' ) ) {
    function pwgc_set_table_names() {
        global $wpdb;

        if ( true === PWGC_MULTISITE_SHARED_DATABASE ) {
            $wpdb->pimwick_gift_card = $wpdb->base_prefix . 'pimwick_gift_card';
            $wpdb->pimwick_gift_card_activity = $wpdb->base_prefix . 'pimwick_gift_card_activity';
        } else {
            $wpdb->pimwick_gift_card = $wpdb->prefix . 'pimwick_gift_card';
            $wpdb->pimwick_gift_card_activity = $wpdb->prefix . 'pimwick_gift_card_activity';
        }
    }
}

if ( ! function_exists( 'pwgc_get_other_amount_prompt' ) ) {
    function pwgc_get_other_amount_prompt( $product ) {
        // If $product isn't a WooCommerce product, try and retrieve it.
        if ( !is_a( $product, 'WC_Product' ) ) {
            if ( is_numeric( $product ) ) {
                $product = wc_get_product( $product );
            } else if ( is_object( $product ) && property_exists( $product, 'ID' ) ) {
                $product = wc_get_product( $product->ID );
            } else {
                return PWGC_OTHER_AMOUNT_PROMPT;
            }
        }

        // We need to be working with a WooCommerce product.
        if ( !is_a( $product, 'WC_Product' ) ) {
            return PWGC_OTHER_AMOUNT_PROMPT;
        }

        // Make sure we have the parent product.
        if ( !empty( $product->get_parent_id() ) ) {
            $product = wc_get_product( $product->get_parent_id() );
        }

        // Only examine the Gift Card products.
        if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
            $variations = array_map( 'wc_get_product', $product->get_children() );
            foreach ( $variations as $variation ) {
                if ( $variation && is_a( $variation, 'WC_Product' ) && $variation->get_price() == 0 ) {
                    $other_amount_prompt = $variation->get_attribute( PWGC_DENOMINATION_ATTRIBUTE_SLUG );
                    if ( !empty( $other_amount_prompt ) ) {
                        break;
                    }
                }
            }
        }

        if ( !empty( $other_amount_prompt ) ) {
            return $other_amount_prompt;
        } else {
            return PWGC_OTHER_AMOUNT_PROMPT;
        }
    }
}

if ( ! function_exists( 'pwgc_get_designs' ) ) {
    function pwgc_get_designs() {
        global $pw_gift_cards;

        $designs = maybe_unserialize( get_option( 'pw_gift_card_designs', array() ) );
        if ( empty( $designs ) ) {
            $designs = $pw_gift_cards->default_designs;

            // Bring over the design elements from the free version if it exists.
            $free_designs = maybe_unserialize( get_option( 'pw_gift_card_designs_free', array() ) );
            if ( !empty( $free_designs ) ) {
                $designs[0]['gift_card_color'] = $free_designs[0]['gift_card_color'];
                $designs[0]['redeem_button_background_color'] = $free_designs[0]['redeem_button_background_color'];
                $designs[0]['redeem_button_color'] = $free_designs[0]['redeem_button_color'];
            }

            return $designs;
        }

        // Ensure that all saved designs have the current keys in case we've added new things.
        foreach ( $designs as &$design ) {
            $default_design = reset( $pw_gift_cards->default_designs );
            foreach( $default_design as $key => $value ) {
                if ( !isset( $design[ $key ] ) ) {
                    $design[ $key ] = $default_design[ $key ];
                }
            }
        }

        return $designs;
    }
}

if ( ! function_exists( 'pwgc_redeem_url' ) ) {
    function pwgc_redeem_url( $item_data ) {

        if ( isset( $item_data->design ) && isset( $item_data->design['redeem_url'] ) && !empty( $item_data->design['redeem_url'] ) ) {
            $redeem_url = $item_data->design['redeem_url'];
        } else {
            $redeem_url = pwgc_default_redeem_url();
        }

        $redeem_url = add_query_arg( 'pw_gift_card_number', urlencode( $item_data->gift_card_number ), $redeem_url );

        return apply_filters( 'pwgc_redeem_url', $redeem_url, $item_data );
    }
}

if ( ! function_exists( 'pwgc_default_redeem_url' ) ) {
    function pwgc_default_redeem_url() {
        $redeem_url = get_option( 'pwgc_default_redeem_url', '' );
        if ( empty( $redeem_url ) ) {
            $redeem_url = pwgc_shop_url();
        }

        return $redeem_url;
    }
}

if ( ! function_exists( 'pwgc_shop_url' ) ) {
    function pwgc_shop_url() {
        $shop_url = get_permalink( wc_get_page_id( 'shop' ) );
        if ( empty( $shop_url ) ) {
           $shop_url = site_url();
        }

        return $shop_url;
    }
}

if ( ! function_exists( 'pwgc_color_picker_field' ) ) {
    function pwgc_color_picker_field( $design, $key, $label ) {
        global $pw_gift_cards;

        if ( !empty( $design[ $key ] ) ) {
            $color = $design[ $key ];
        } else {
            $color = get_option( 'woocommerce_email_text_color', '#3c3c3c' );
        }
        $id = 'pwgc-designer-' . str_replace( '_', '-', $key );

        $preview_element = $pw_gift_cards->design_colors[ $key ][0];
        $preview_element_css = $pw_gift_cards->design_colors[ $key ][1];

        ?>
        <p class="form-field">
            <label class="pwgc-designer-label"><?php echo $label; ?></label>
            <input type="text" name="<?php echo $key; ?>" id="<?php echo $id; ?>" value="<?php echo $color; ?>" style="color: <?php echo $color; ?>; background-color: <?php echo $color; ?>; max-width: 75px;">
        </p>
        <script>
            jQuery(function() {
                pwgcAssignColorPicker('#<?php echo $id; ?>', '<?php echo $preview_element; ?>', '<?php echo $preview_element_css; ?>');
            });
        </script>
        <?php
    }
}

if ( ! function_exists( 'pwgc_dashboard_helper' ) ) {
    // Optionally set the selected CSS for the appropriate section.
    function pwgc_dashboard_helper( $item, $output = 'pwgc-dashboard-item-selected' ) {
        $selected = false;
        if ( isset( $_REQUEST['section'] ) ) {
            $selected = ( $_REQUEST['section'] == $item );
        } else if ( $item == 'balances' ) {
            $selected = true;
        }

        echo ( $selected ) ? $output : '';
    }
}

if ( ! function_exists( 'pwgc_paypal_ipn_pdt_bug_exists' ) ) {
    function pwgc_paypal_ipn_pdt_bug_exists() {
        $bug_exists = false;
        $ipn_enabled = false;
        $pdt_enabled = false;
        $woocommerce_paypal_settings = get_option( 'woocommerce_paypal_settings' );

        if ( empty( $woocommerce_paypal_settings['ipn_notification'] ) || 'no' !== $woocommerce_paypal_settings['ipn_notification'] ) {
            $ipn_enabled = true;
        }

        if ( ! empty( $woocommerce_paypal_settings['identity_token'] ) ) {
            $pdt_enabled = true;
        }

        if ( $ipn_enabled && $pdt_enabled ) {
            $bug_exists = true;
        }

        return apply_filters( 'pwgc_paypal_ipn_pdt_bug_exists', $bug_exists );
    }
}
