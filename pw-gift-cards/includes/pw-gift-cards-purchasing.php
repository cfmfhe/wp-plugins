<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'PW_Gift_Cards_Purchasing' ) ) :

final class PW_Gift_Cards_Purchasing {

    function __construct() {

        add_filter( 'woocommerce_get_price_html', array( $this, 'woocommerce_get_price_html' ), 10, 2 );
        add_filter( 'woocommerce_cart_item_quantity', array( $this, 'woocommerce_cart_item_quantity' ), 10, 3 );
        add_filter( 'woocommerce_dropdown_variation_attribute_options_args', array( $this, 'woocommerce_dropdown_variation_attribute_options_args' ) );
        add_filter( 'woocommerce_add_to_cart_handler', array( $this, 'woocommerce_add_to_cart_handler' ), 10 , 2 );
        add_filter( 'woocommerce_add_cart_item', array( $this, 'woocommerce_add_cart_item' ) );
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woocommerce_add_cart_item_data' ), 10, 3 );
        add_filter( 'woocommerce_cart_item_product', array( $this, 'woocommerce_cart_item_product' ), 11, 2 );
        add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woocommerce_add_to_cart_validation' ), 10, 6 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'woocommerce_get_cart_item_from_session' ), 10, 2 );
        add_filter( 'woocommerce_get_item_data', array( $this, 'woocommerce_get_item_data' ), 10, 2 );
        add_filter( 'woocommerce_cart_item_permalink', array( $this, 'woocommerce_cart_item_permalink' ), 10, 3 );
        add_filter( 'woocommerce_checkout_create_order_line_item', array( $this, 'woocommerce_checkout_create_order_line_item' ), 10, 4 );

        if ( 'yes' == get_option( 'pwgc_send_when_processing', 'no' ) ) {
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woocommerce_checkout_update_order_meta' ), 11, 2 );
        }

        add_filter( 'woocommerce_order_status_completed', array( $this, 'woocommerce_order_status_completed' ), 11, 2 );
        add_filter( 'woocommerce_order_status_cancelled', array( $this, 'woocommerce_order_status_cancelled' ), 11, 2 );
        add_filter( 'woocommerce_order_status_refunded', array( $this, 'woocommerce_order_status_refunded' ), 11, 2 );
        add_filter( 'wp_trash_post', array( $this, 'order_deleted' ) );
        add_filter( 'untrash_post', array( $this, 'order_restored' ) );
        add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'woocommerce_order_item_needs_processing' ), 10, 3 );
        add_filter( 'woocommerce_order_item_permalink', array( $this, 'woocommerce_order_item_permalink' ), 10, 3 );
        add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'woocommerce_order_item_display_meta_key' ), 10, 3 );
        add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'woocommerce_order_item_get_formatted_meta_data' ), 10, 2 );
        add_action( 'woocommerce_order_again_cart_item_data', array( $this, 'woocommerce_order_again_cart_item_data' ), 10, 3 );
        add_filter( 'woocommerce_product_get_price', array( $this, 'woocommerce_product_get_price' ), 10, 2 );
        add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'woocommerce_coupon_get_discount_amount' ), 99, 5 );
        add_filter( 'woocommerce_email_headers', array( $this, 'woocommerce_email_headers' ), 10, 3 );
        add_filter( 'wc_memberships_exclude_product_from_member_discounts', array( $this, 'wc_memberships_exclude_product_from_member_discounts' ), 10, 2 );

        // Disable the AJAX add-to-cart for the PW Gift Cards product on certain themes since it does not correctly add the fields to the cart item data.
        add_filter( 'et_option_ajax_addtocart', array( $this, 'et_option_ajax_addtocart' ) );
        add_filter( 'theme_mod_disable_wc_sticky_cart', array( $this, 'theme_mod_disable_wc_sticky_cart' ) );
        add_filter( 'theme_mod_ocean_woo_product_ajax_add_to_cart', array( $this, 'theme_mod_ocean_woo_product_ajax_add_to_cart' ) );
        add_filter( 'option_rigid', array( $this, 'option_rigid' ), 10, 2 );
    }

    function woocommerce_get_price_html( $price, $product ) {
        if ( is_a( $product, 'WC_Product_Variation' ) && empty( $product->get_price() ) ) {
            $product_id = $product->get_parent_id();
            $parent_product = wc_get_product( $product_id );
            if ( is_a( $parent_product, 'WC_Product_PW_Gift_Card' ) ) {
                $price = '';
            }
        }

        return $price;
    }

    function woocommerce_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
        if ( isset( $cart_item[ PWGC_TO_META_KEY ] ) && strpos( $cart_item[ PWGC_TO_META_KEY ], ',' ) !== false ) {
            return $cart_item['quantity'];
        }

        return $product_quantity;
    }

    function woocommerce_dropdown_variation_attribute_options_args( $args ) {
        if ( $args['product'] && is_a( $args['product'], 'WC_Product_PW_Gift_Card' ) ) {
            $args['show_option_none'] = __( 'Choose an amount', 'pw-woocommerce-gift-cards' );
        }

        return $args;
    }

    function woocommerce_add_to_cart_handler( $product_type, $product ) {
        if ( $product_type == PWGC_PRODUCT_TYPE_SLUG ) {
            return 'variable';
        } else {
            return $product_type;
        }
    }

    function woocommerce_add_cart_item( $cart_item ) {
        global $pw_gift_cards;

        $product_id = absint( $cart_item['product_id'] );
        $variation_id = absint( $cart_item['variation_id'] );

        if ( $pw_gift_cards->allow_custom_amount( $product_id, $variation_id ) && isset( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) {
            $amount = $pw_gift_cards->numeric_price( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] );
            $cart_item['data']->set_price( $amount );
        }

        if ( isset( $cart_item[ PWGC_TO_META_KEY ] ) && !empty( $cart_item[ PWGC_TO_META_KEY ] ) ) {
            $recipients = preg_split('/[\s,]+/', $cart_item[ PWGC_TO_META_KEY ], PWGC_RECIPIENT_LIMIT, PREG_SPLIT_NO_EMPTY);
            if ( count( $recipients ) > 1 ) {
                $cart_item['quantity'] = count( $recipients );
            }
        }

        return $cart_item;
    }

    function woocommerce_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
        global $pw_gift_cards;

        if ( isset( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && $pw_gift_cards->allow_custom_amount( $product_id, $variation_id ) ) {
            $amount = $pw_gift_cards->sanitize_amount( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] );

            if ( $pw_gift_cards->use_default_currency_in_cart() ) {
                $amount = apply_filters( 'pwgc_to_default_currency', $amount );
            }

            $cart_item_data[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] = $amount;
        }

        $product = wc_get_product( $product_id );

        foreach ( $pw_gift_cards->gift_card_meta as $key => $display ) {
            if ( isset( $_REQUEST[ $key ] ) ) {
                if ( $key == PWGC_MESSAGE_META_KEY ) {
                    $cart_item_data[ $key ] = sanitize_textarea_field( stripslashes( $_REQUEST[ $key ] ) );
                } else if ( $key == PWGC_TO_META_KEY && property_exists( $product, 'get_pwgc_is_physical_card' ) && $product->get_pwgc_is_physical_card() ) {
                    continue;
                } else {
                    $cart_item_data[ $key ] = sanitize_text_field( stripslashes( $_REQUEST[ $key ] ) );
                }
            }
        }

        return $cart_item_data;
    }

    function woocommerce_cart_item_product( $cart_item_data, $cart_item ) {
        global $pw_gift_cards;

        $product_id = absint( $cart_item['product_id'] );
        $variation_id = absint( $cart_item['variation_id'] );

        if ( $pw_gift_cards->allow_custom_amount( $product_id, $variation_id ) && isset( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) {
            $amount = $pw_gift_cards->numeric_price( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] );
            $cart_item_data->set_price( $amount );
        }

        return $cart_item_data;
    }

    function woocommerce_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = '', $variations = '', $cart_item_data = '' ) {
        global $pw_gift_cards;

        if ( $passed && $pw_gift_cards->allow_custom_amount( $product_id, $variation_id ) ) {
            $product = wc_get_product( $product_id );

            $custom_amount = ( isset( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && !empty( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) ? $pw_gift_cards->sanitize_amount( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) : 0;

            if ( empty( $custom_amount ) ) {
                $custom_amount = ( isset( $cart_item_data[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && !empty( $cart_item_data[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) ? $pw_gift_cards->numeric_price( $cart_item_data[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) : 0;
            }

            if ( !empty( $custom_amount ) ) {
                if ( $custom_amount < apply_filters( 'pwgc_to_current_currency', $product->get_pwgc_custom_amount_min() ) ) {
                    wc_add_notice( sprintf( __( 'The minimum amount is %s.', 'pw-woocommerce-gift-cards' ), wc_price( $product->get_pwgc_custom_amount_min() ) ), 'error' );
                    return false;
                }

                if ( $custom_amount > apply_filters( 'pwgc_to_current_currency', $product->get_pwgc_custom_amount_max() ) ) {
                    wc_add_notice( sprintf( __( 'The maximum amount is %s.', 'pw-woocommerce-gift-cards' ), wc_price( $product->get_pwgc_custom_amount_max() ) ), 'error' );
                    return false;
                }
            } else {
                wc_add_notice( __( 'Amount is required', 'pw-woocommerce-gift-cards' ), 'error' );
                return false;
            }
        }

        return $passed;
    }

    function woocommerce_get_cart_item_from_session( $cart_item, $values ) {
        global $pw_gift_cards;

        if ( isset( $values[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && ! empty( $values[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) {
            $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] = $values[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ];
            $cart_item = $this->woocommerce_add_cart_item( $cart_item );
        }

        foreach ( $pw_gift_cards->gift_card_meta as $key => $display ) {
            if ( isset( $values[ $key ] ) ) {
                $cart_item[ $key ] = $values[ $key ];
            }
        }

        return $cart_item;
    }

    function woocommerce_get_item_data( $item_data, $cart_item ) {
        global $pw_gift_cards;

        $product = wc_get_product( absint( $cart_item['product_id'] ) );
        if ( ! is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
            return $item_data;
        }

        if ( isset( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && ! empty( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) {
            $item_data[] = array(
                'key' => PWGC_AMOUNT_META_DISPLAY_NAME,
                'value' => $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ]
            );
        }

        foreach ( $pw_gift_cards->gift_card_meta as $key => $display ) {
            if ( isset( $cart_item[ $key ] ) ) {
                $value = $cart_item[ $key ];
                if ( !empty( $value ) ) {
                    $item_data[] = array(
                        'key' => $display,
                        'value' => $value
                    );
                }
            }
        }

        if ( !empty( WC()->cart ) ) {
            $bonus = $product->get_bonus_amount( WC()->cart->get_cart(), $cart_item );
            if ( $bonus > 0 ) {
                $item_data[] = array(
                    'key' => PWGC_BONUS_AMOUNT_META_DISPLAY_NAME,
                    'value' => wc_price( $bonus ),
                );
            }
        }

        return $item_data;
    }

    function woocommerce_cart_item_permalink( $product_permalink, $cart_item, $cart_item_key ) {
        global $pw_gift_cards;

        if ( !empty( $product_permalink ) ) {
            if ( strpos( $product_permalink, PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ) === false && isset( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) {
                $product_permalink = add_query_arg( PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY, $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ], $product_permalink );
            }

            foreach ( $pw_gift_cards->gift_card_meta as $key => $display ) {
                if ( strpos( $product_permalink, $key ) === false && isset( $cart_item[ $key ] ) ) {
                    $product_permalink = add_query_arg( $key, urlencode( nl2br( $cart_item[ $key ] ) ), $product_permalink );
                }
            }
        }

        return $product_permalink;
    }

    function woocommerce_checkout_create_order_line_item( $order_item, $cart_item_key, $cart_item, $order ) {
        global $pw_gift_cards;

        $product = wc_get_product( $order_item->get_product_id() );
        if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
            foreach ( $pw_gift_cards->gift_card_meta as $key => $display ) {
                if ( $key == PWGC_AMOUNT_META_KEY ) {
                    if ( isset( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && ! empty( $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) {
                        $order_item->add_meta_data( $key, $cart_item[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] );
                    } else {
                        $price = $cart_item['data']->get_regular_price();

                        // WooCommerce Ultimate Multi Currency Suite
                        if ( class_exists( 'WooCommerce_Ultimate_Multi_Currency_Suite_Main' ) && isset( $GLOBALS['woocommerce_ultimate_multi_currency_suite'] ) ) {
                            $cs = $GLOBALS['woocommerce_ultimate_multi_currency_suite'];
                            if ( !empty( $cs->frontend ) ) {
                                remove_filter('woocommerce_product_variation_get_regular_price', array($cs->frontend, 'custom_item_price'), 9999, 2);
                                $price = $cart_item['data']->get_regular_price();
                                add_filter('woocommerce_product_variation_get_regular_price', array($cs->frontend, 'custom_item_price'), 9999, 2);
                            }
                        }

                        if (
                            // Multi Currency for WooCommerce by VillaTheme
                            function_exists( 'wmc_get_price' )

                            // WooCommerce Price Based on Country by Oscar Gare
                            || function_exists( 'wcpbc_get_zone_by_country' )

                            // Multi-Currency for WooCommerce by TIV.NET INC
                            || class_exists( 'WOOMC\App' )

                            // WooCommerce Currency Switcher by realmag777
                            || isset( $GLOBALS['WOOCS'] )
                        ) {
                            $price = apply_filters( 'pwgc_to_default_currency', $price );
                        }

                        $order_item->add_meta_data( $key, $price );
                    }
                } else if ( isset( $cart_item[ $key ] ) ) {
                    $order_item->add_meta_data( $key, $cart_item[ $key ] );
                }
            }

            $bonus = $product->get_bonus_amount( WC()->cart->get_cart(), $cart_item );
            if ( $bonus > 0 ) {
                $order_item->add_meta_data( PWGC_BONUS_AMOUNT_META_KEY, $bonus );
            }
        }
    }

    function woocommerce_checkout_update_order_meta( $order_id, $data ) {
        $order = wc_get_order( $order_id );
        $this->add_gift_cards_to_order( $order_id, $order, "order_id: $order_id created" );
    }

    function woocommerce_order_status_completed( $order_id, $order ) {
        $this->add_gift_cards_to_order( $order_id, $order, "order_id: $order_id completed" );
    }

    function woocommerce_order_status_cancelled( $order_id, $order ) {
        $this->deactivate_gift_cards_from_order( $order_id, $order, "order_id: $order_id cancelled" );
    }

    function woocommerce_order_status_refunded( $order_id, $order ) {
        $this->deactivate_gift_cards_from_order( $order_id, $order, "order_id: $order_id refunded" );
    }

    function order_deleted( $id ) {
        global $post_type;

        if ( $post_type !== 'shop_order' ) {
            return;
        }

        $order = wc_get_order( $id );
        if ( $order ) {
            $this->deactivate_gift_cards_from_order( $id, $order, "order_id: $id deleted" );
        }
    }

    function order_restored( $id ) {
        global $post_type;

        if ( $post_type !== 'shop_order' ) {
            return;
        }

        $order = wc_get_order( $id );
        if ( $order ) {
            $this->add_gift_cards_to_order( $id, $order, "order_id: $id restored" );
        }
    }

    function add_gift_cards_to_order( $order_id, $order, $note ) {

        $has_scheduled_gift_cards = false;
        $create_note = sprintf( __( 'Order %s purchased by %s %s', 'pw-woocommerce-gift-cards' ), $order->get_id(), $order->get_billing_first_name(), $order->get_billing_last_name() );

        foreach ( $order->get_items( 'line_item' ) as $order_item_id => $order_item ) {

            $bonus_amount = 0;

            // Make sure we have a quantity (should always be true, right? Oh well, prevents a divide-by-zero error just in case).
            if ( $order_item->get_quantity() <= 0 ) {
                continue;
            }

            // Get the product.
            $product_id = absint( $order_item['product_id'] );
            if ( !( $product = wc_get_product( $product_id ) ) ) {
                continue;
            }

            // We're only interested in these guys.
            if ( !is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                continue;
            }

            // If an email address is not specified for physical gift cards then we don't create one for the order.
            if ( $product->get_pwgc_is_physical_card() && empty( $product->get_pwgc_physical_email() ) ) {
                continue;
            }

            // Grab the Variation, otherwise there will be trouble.
            $variation_id = absint( $order_item['variation_id'] );
            if ( !( $variation = wc_get_product( $variation_id ) ) ) {
                wp_die( __( 'Unable to retrieve variation ', 'pw-woocommerce-gift-cards' ) . $variation_id );
            }

            $credit_amount = wc_get_order_item_meta( $order_item_id, PWGC_AMOUNT_META_KEY );
            if ( !is_numeric( $credit_amount ) || empty( $credit_amount ) ) {

                // Previously we didn't store the PWGC_AMOUNT_META_KEY so we need to calculate based on purchase price.
                $credit_amount = round( $order_item->get_subtotal() / $order_item->get_quantity(), wc_get_price_decimals() );
                if ( !is_numeric( $credit_amount ) || empty( $credit_amount ) ) {
                    continue;
                }
            }

            // WooCommerce Currency Switcher by realmag777
            if ( !isset( $GLOBALS['WOOCS'] ) ) {
                $credit_amount = apply_filters( 'pwgc_to_default_currency', $credit_amount );
            }
            $item_note = $note . ", order_item_id: $order_item_id";

            if ( false === $has_scheduled_gift_cards ) {
                $scheduled_date = wc_get_order_item_meta( $order_item_id, PWGC_DELIVERY_DATE_META_KEY );
                if ( !empty( $scheduled_date ) ) {
                    $has_scheduled_gift_cards = true;
                }
            }

            $reload_card_number = wc_get_order_item_meta( $order_item_id, PWGC_RELOAD_GIFT_CARD_NUMBER_META_KEY );
            if ( !empty( $reload_card_number ) ) {
                $already_reloaded = wc_get_order_item_meta( $order_item_id, PWGC_RELOAD_GIFT_CARD_RELOADED_META_KEY );
                if ( empty( $already_reloaded ) ) {
                    $reload_amount = $credit_amount * absint( $order_item['quantity'] );

                    $gift_card = new PW_Gift_Card( $reload_card_number );
                    $gift_card->credit( $reload_amount, $item_note );

                    wc_update_order_item_meta( $order_item_id, PWGC_AMOUNT_META_KEY, $reload_amount );
                    wc_add_order_item_meta( $order_item_id, PWGC_RELOAD_GIFT_CARD_RELOADED_META_KEY, 'yes' );
                }

            } else {
                // Create a gift card for each quantity ordered.
                $gift_card_numbers = (array) wc_get_order_item_meta( $order_item_id, PWGC_GIFT_CARD_NUMBER_META_KEY, false );

                // Make sure any existing gift cards are activated.
                foreach ( $gift_card_numbers as $gift_card_number ) {
                    $gift_card = new PW_Gift_Card( $gift_card_number );
                    $gift_card->reactivate( $item_note );
                }

                // Create any new/missing gift cards.
                for ( $x = count( $gift_card_numbers ); $x < $order_item['quantity']; $x++ ) {
                    $gift_card = pwgc_add_gift_card_to_order( $order_item_id, $credit_amount, $product, $create_note, $item_note );
                }
            }

            // Check to see if we have already generated the bonus card for this line item before proceeding.
            $bonus_recipient = wc_get_order_item_meta( $order_item_id, PWGC_BONUS_RECIPIENT_META_KEY );
            if ( empty( $bonus_recipient ) ) {
                // Add any bonus gift cards.
                $bonus_amount = $product->get_bonus_amount( $order->get_items( 'line_item' ), $order_item );
                if ( $bonus_amount > 0 ) {
                    wc_update_order_item_meta( $order_item_id, PWGC_BONUS_RECIPIENT_META_KEY, $product->get_pwgc_bonus_recipient() );
                    $bonus_gift_card = pwgc_add_gift_card_to_order( $order_item_id, $bonus_amount, $product, $create_note . ' (' . __( 'Bonus Gift Card', 'pw-woocommerce-gift-cards' ) . ')', $item_note );
                    $bonus_gift_card->set_pimwick_gift_card_parent( $gift_card->get_id() );
                }
            }
        }

        if ( true === $has_scheduled_gift_cards ) {
            update_post_meta( $order_id, PWGC_DELIVERY_PENDING_META_KEY, 'yes' );
        }

        do_action( 'pw_gift_cards_send_emails', $order_id );
    }

    function deactivate_gift_cards_from_order( $order_id, $order, $note ) {
        foreach ( $order->get_items( 'line_item' ) as $order_item_id => $order_item ) {
            $item_note = $note . ", order_item_id: $order_item_id";

            $reload_card_number = wc_get_order_item_meta( $order_item_id, PWGC_RELOAD_GIFT_CARD_NUMBER_META_KEY );
            if ( !empty( $reload_card_number ) ) {
                $reload_amount = wc_get_order_item_meta( $order_item_id, PWGC_AMOUNT_META_KEY );

                $gift_card = new PW_Gift_Card( $reload_card_number );
                $gift_card->debit( $reload_amount * -1, $item_note );

                $credit_amount = $reload_amount / absint( $order_item['quantity'] );
                wc_update_order_item_meta( $order_item_id, PWGC_AMOUNT_META_KEY, $credit_amount );
                wc_delete_order_item_meta( $order_item_id, PWGC_RELOAD_GIFT_CARD_RELOADED_META_KEY );

            } else {

                $gift_card_numbers = (array) wc_get_order_item_meta( $order_item_id, PWGC_GIFT_CARD_NUMBER_META_KEY, false );
                foreach ( $gift_card_numbers as $gift_card_number ) {
                    $gift_card = new PW_Gift_Card( $gift_card_number );
                    $gift_card->deactivate( $item_note );
                }
            }
        }
    }

    function woocommerce_order_item_needs_processing( $needs_processing, $product, $order_item_id ) {
        if ( is_a( $product, 'WC_Product_Variation' ) ) {
            if ( 'yes' === get_option( 'pwgc_autocomplete_gift_card_orders', 'yes' ) ) {
                $product_id = $product->get_parent_id();
                $parent_product = wc_get_product( $product_id );
                if ( is_a( $parent_product, 'WC_Product_PW_Gift_Card' ) ) {
                    $needs_processing = false;
                }
            }
        }

        return $needs_processing;
    }

    function woocommerce_order_item_permalink( $product_permalink, $order_item, $order ) {
        global $pw_gift_cards;

        if ( !empty( $product_permalink ) ) {
            $product = wc_get_product( $order_item->get_product_id() );
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {

                if ( strpos( $product_permalink, PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ) === false && !empty( $order_item->get_quantity() ) ) {
                    $amount = wc_get_order_item_meta( $order_item->get_id(), PWGC_AMOUNT_META_KEY );
                    if ( ( !is_numeric( $amount ) || empty( $amount ) ) && !empty( $order_item->get_quantity() ) ) {
                        // Previously we didn't store the PWGC_AMOUNT_META_KEY so we need to calculate based on purchase price.
                        $amount = round( $order_item->get_subtotal() / $order_item->get_quantity(), wc_get_price_decimals() );
                    }
                    $product_permalink = add_query_arg( PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY, $amount, $product_permalink );
                }

                foreach ( $pw_gift_cards->gift_card_meta as $key => $display ) {
                    if ( strpos( $product_permalink, $key ) === false && isset( $order_item[ $key ] ) ) {
                        $product_permalink = add_query_arg( $key, urlencode( $order_item[ $key ] ), $product_permalink );
                    }
                }
            }
        }

        return $product_permalink;
    }

    function woocommerce_order_item_display_meta_key( $display_key, $meta_data, $order_item ) {
        switch ( $display_key ) {
            case PWGC_GIFT_CARD_NUMBER_META_KEY:
                $display_key = PWGC_GIFT_CARD_NUMBER_META_DISPLAY_NAME;

                $gift_card = new PW_Gift_Card( $meta_data->value );
                if ( !$gift_card->get_id() ) {
                    $display_key .= ' ' . __( '(deleted)', 'pw-woocommerce-gift-cards' );
                } else if ( !$gift_card->get_active() ) {
                    $display_key .= __( ' (inactive)', 'pw-woocommerce-gift-cards' );
                }

                if ( !empty( $gift_card->get_pimwick_gift_card_parent() ) ) {
                    $display_key = __( 'Bonus', 'pw-woocommerce-gift-cards' ) . ' ' . $display_key;
                }
            break;

            case PWGC_RELOAD_GIFT_CARD_RELOADED_META_KEY:
                $display_key = PWGC_RELOAD_GIFT_CARD_RELOADED_DISPLAY_NAME;
            break;
        }

        return $display_key;
    }

    function woocommerce_order_item_get_formatted_meta_data( $formatted_meta, $order_item ) {
        // Make sure that Bonus Amount is always the last value displayed.
        $bonus_amount_id = 0;

        foreach ( $formatted_meta as $id => $meta ) {
            if ( $meta->key == PWGC_BONUS_AMOUNT_META_KEY ) {
                $bonus_amount_id = $id;
                break;
            }
        }

        if ( $bonus_amount_id > 0 && isset( $formatted_meta[ $bonus_amount_id ] ) ) {
            $value = $formatted_meta[ $bonus_amount_id ];
            unset( $formatted_meta[ $bonus_amount_id ] );
            $formatted_meta[ $bonus_amount_id ] = $value;
        }

        if ( is_admin() && is_a( $order_item, 'WC_Order_Item_Product' ) && !empty( $order_item->get_product_id() ) ) {
            $product = wc_get_product( $order_item->get_product_id() );
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                $has_gift_card_number = false;
                foreach ( $formatted_meta as $id => $meta ) {
                    if ( $meta->key == PWGC_GIFT_CARD_NUMBER_META_KEY ) {
                        $has_gift_card_number = true;
                        break;
                    }
                }

                if ( !$has_gift_card_number ) {
                    if ( $product->get_pwgc_is_physical_card() && empty( $product->get_pwgc_physical_email() ) ) {
                        // If an email address is not specified for physical gift cards then we don't create one for the order.
                    } else {
                        $meta = new stdClass();
                        $meta->key = PWGC_GIFT_CARD_NUMBER_META_KEY . '_placeholder';
                        $meta->value = false;
                        $meta->display_key = PWGC_GIFT_CARD_NUMBER_META_DISPLAY_NAME;
                        $meta->display_value = '<i>' . __( 'Generated and emailed after the order is marked Complete.', 'pw-woocommerce-gift-cards' ) . '</i>';
                        $formatted_meta[] = $meta;
                    }
                }
            }
        }

        return $formatted_meta;
    }

    function woocommerce_order_again_cart_item_data( $cart_item_data, $order_item, $order ) {
        global $pw_gift_cards;

        if ( $pw_gift_cards->allow_custom_amount( $order_item['product_id'], $order_item['variation_id'] ) ) {
            $cart_item_data[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] = $order_item->get_subtotal();
        }

        foreach ( $pw_gift_cards->gift_card_meta as $key => $display ) {
            if ( isset( $order_item[ $key ] ) ) {
                if ( $key == PWGC_MESSAGE_META_KEY ) {
                    $cart_item_data[ $key ] = sanitize_textarea_field( stripslashes( $order_item[ $key ] ) );
                } else {
                    $cart_item_data[ $key ] = sanitize_text_field( stripslashes( $order_item[ $key ] ) );
                }
            }
        }

        return $cart_item_data;
    }

    function woocommerce_product_get_price( $value, $product ) {
        if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) && '' === $value ) {
            return '0';
        } else {
            return $value;
        }
    }

    function woocommerce_coupon_get_discount_amount( $discount_amount, $discounting_amount, $cart_item, $single, $coupon ) {
        if ( isset( $cart_item['product_id'] ) ) {
            $product = wc_get_product( $cart_item['product_id'] );
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                if ( $product->get_pwgc_no_coupons_allowed() ) {
                    return 0;
                }
            }
        }

        return $discount_amount;
    }

    function woocommerce_email_headers( $header, $email_id, $gift_card ) {
        // Only for "PW Gift Card" email notification
        if( 'pwgc_email' !== $email_id ) {
            return $header;
        }

        $cc = trim( get_option( 'pwgc_cc_email', '' ) );
        if ( !empty( $cc ) ) {
            $header .= "Cc: " . utf8_decode( $cc ) . "\r\n";
        }

        $bcc = trim( get_option( 'pwgc_bcc_email', '' ) );
        if ( 'yes' == get_option( 'pwgc_bcc_buyer', 'no' ) && isset( $gift_card->order ) ) {
            $order = $gift_card->order;
            if ( !empty( $order ) && method_exists( $order, 'get_billing_email' ) ) {
                if ( !empty( $bcc ) ) {
                    $bcc .= ',';
                }
                $bcc .= $order->get_billing_email();
            }
        }

        if ( !empty( $bcc ) ) {
            $header .= "Bcc: " . utf8_decode( $bcc ) . "\r\n";
        }

        return $header;
    }

    function wc_memberships_exclude_product_from_member_discounts( $excluded, $product ) {
        if ( ! (bool) $excluded ) {
            if ( is_numeric( $product ) && $product > 0 ) {
                $product = wc_get_product( $product );
            }

            $product_id = !empty( $product->get_parent_id() ) ? $product->get_parent_id() : $product->get_id();
            $product =  wc_get_product( $product_id );

            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) && function_exists( 'wc_memberships' ) ) {
                $memberships = wc_memberships();
                if ( method_exists( $memberships, 'get_member_discounts_instance' ) ) {
                    $discounts = $memberships->get_member_discounts_instance();

                    if ( method_exists( $discounts, 'get_products_excluded_from_member_discounts' ) && method_exists( $discounts, 'excluding_on_sale_products_from_member_discounts' ) && method_exists( $discounts, 'product_is_on_sale_before_discount' ) ) {
                        $exclude_product = in_array( $product_id, $discounts->get_products_excluded_from_member_discounts(), false );
                        $exclude_on_sale = ! $exclude_product ? $discounts->excluding_on_sale_products_from_member_discounts() && $discounts->product_is_on_sale_before_discount( $product ) : false;

                        $excluded = $exclude_product || $exclude_on_sale;
                    }
                }
            }
        }

        return $excluded;
    }

    function et_option_ajax_addtocart( $value ) {
        global $product;

        if ( !empty( $product ) ) {
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                return false;
            }
        }

        return $value;
    }

    function theme_mod_disable_wc_sticky_cart( $value ) {
        global $product;

        if ( !empty( $product ) ) {
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                return 1;
            }
        }

        return $value;
    }

    function theme_mod_ocean_woo_product_ajax_add_to_cart( $value ) {
        global $post;

        if ( !empty( $post ) ) {
            $product = wc_get_product( $post->ID );
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                return false;
            }
        }

        return $value;
    }

    function option_rigid( $value, $option ) {
        global $post;

        if ( !empty( $post ) ) {
            $product = wc_get_product( $post->ID );
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                if ( !empty( $value ) && is_array( $value ) ) {
                    if ( isset( $value['ajax_to_cart_single'] ) && true === boolval( $value['ajax_to_cart_single'] ) ) {
                        $value['ajax_to_cart_single'] = 0;
                    }
                }
            }
        }

        return $value;
    }
}

global $pw_gift_cards_purchasing;
$pw_gift_cards_purchasing = new PW_Gift_Cards_Purchasing();

endif;
