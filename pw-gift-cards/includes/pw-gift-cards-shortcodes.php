<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'PW_Gift_Cards_Shortcodes' ) ) :

final class PW_Gift_Cards_Shortcodes {

    function __construct() {
        add_shortcode( PWGC_BALANCE_SHORTCODE, array( $this, 'check_balance_shortcode' ) );

        add_action( 'wp_ajax_nopriv_pw-gift-cards-balance', array( $this, 'ajax_balance' ) );
        add_action( 'wp_ajax_pw-gift-cards-balance', array( $this, 'ajax_balance' ) );

        if ( 'yes' === get_option( 'pwgc_check_balance_allow_manual_debit', 'no' ) ) {
            add_action( 'wp_ajax_nopriv_pw-gift-cards-debit', array( $this, 'ajax_debit' ) );
            add_action( 'wp_ajax_pw-gift-cards-debit', array( $this, 'ajax_debit' ) );
        }

        if ( get_option( 'pwgc_check_balance_show_on_my_account', 'yes' ) === 'yes' ) {
            add_filter( 'woocommerce_account_menu_items', array( $this, 'woocommerce_account_menu_items' ), 11, 2 );
            add_filter( 'woocommerce_get_endpoint_url', array( $this, 'woocommerce_get_endpoint_url' ), 11, 4 );
        }
    }

    function check_balance_shortcode( $shortcode_attributes, $shortcode_content = '' ) {
        return $this->load_shortcode_template( 'balance.php', $shortcode_attributes, $shortcode_content );
    }

    function ajax_balance( ) {
        global $gift_card;

        check_ajax_referer( 'pw-gift-cards-check-balance', 'security' );

        $number = wc_clean( $_REQUEST['card_number'] );

        $gift_card = new PW_Gift_Card( $number );
        if ( $gift_card->get_id() ) {

            if ( $gift_card->get_active() ) {
                $balance = $gift_card->get_balance();
                if ( $balance !== null ) {
                    $_REQUEST['woocs_block_price_hook'] = true; // Needed for WooCommerce Currency Switcher by realmag777
                    $balance = apply_filters( 'pwgc_to_current_currency', $balance );
                    $balance = '<span id="pwgc-balance-amount-value">' . wc_price( $balance ) . '</span>';

                    $activity = '';
                    if ( 'yes' === get_option( 'pwgc_check_balance_show_transactions', 'no' ) ) {
                        ob_start();
                        wc_get_template( 'pw-gift-cards/balance-activity.php', array(), '', PWGC_PLUGIN_ROOT . 'templates/woocommerce/' );
                        $activity = ob_get_clean();
                    }

                    $expiration_date = $gift_card->get_expiration_date();
                    if ( !empty( $expiration_date ) ) {
                        $expiration_date = date_i18n( wc_date_format(), strtotime( $gift_card->get_expiration_date() ) );
                    }

                    wp_send_json_success( array( 'balance' => $balance, 'activity' => $activity, 'expiration_date' => $expiration_date, 'card_number' => $gift_card->get_number() ) );
                }
            } else {
                wp_send_json_error( array( 'message' => __( 'Card is inactive.', 'pw-woocommerce-gift-cards' ) ) );
            }
        } else {
            // Tar-pit to make brute-force guessing inefficient.
            sleep(3);
        }

        wp_send_json_error( array( 'message' => $gift_card->get_error_message() ) );
    }

    function load_shortcode_template( $template_file, $shortcode_attributes, $shortcode_content = '' ) {
        ob_start();

        global $pwgc_shortcode_attributes;
        global $pwgc_shortcode_content;

        $pwgc_shortcode_attributes = $shortcode_attributes;
        $pwgc_shortcode_content = $shortcode_content;

        wp_enqueue_script( 'pw-gift-cards' );

        wc_get_template( "pw-gift-cards/$template_file", array(), '', PWGC_PLUGIN_ROOT . 'templates/woocommerce/' );

        return ob_get_clean();
    }

    function ajax_debit() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-debit-balance', 'security' );

        $number = wc_clean( $_POST['card_number'] );

        $pw_gift_cards->set_current_currency_to_default();

        $balance = 0;

        $gift_card = new PW_Gift_Card( $number );
        if ( $gift_card->get_id() ) {
            $amount = $pw_gift_cards->sanitize_amount( $_POST['amount'] );
            $amount = floatval( $amount );
            $amount = abs( $amount ) * -1; // Ensure it's always negative.

            if ( isset( $_POST['note'] ) ) {
                $note = stripslashes( wc_clean( $_POST['note'] ) );
            } else {
                $note = '';
            }

            if ( ( $gift_card->get_balance() + $amount ) < 0 ) {
                $amount = $gift_card->get_balance() * -1;
            }

            $gift_card->adjust_balance( $amount, $note );

            $balance = $gift_card->get_balance();
            if ( $balance !== null ) {
                $_REQUEST['woocs_block_price_hook'] = true; // Needed for WooCommerce Currency Switcher by realmag777
                $balance = apply_filters( 'pwgc_to_current_currency', $balance );
                $balance = '<span id="pwgc-balance-amount-value">' . wc_price( $balance ) . '</span>';
            }

            wp_send_json( array( 'message' => __( 'Balance adjusted.', 'pw-woocommerce-gift-cards' ), 'balance' => $balance ) );
        } else {
            // Tar-pit to make brute-force guessing inefficient.
            sleep(3);
        }

        wp_send_json_error( array( 'message' => $gift_card->get_error_message() ) );
    }

    function woocommerce_account_menu_items( $items, $endpoints ) {

        // Originally this was a support-related topic with custom code so we'll try to detect
        // that and if the customer has already enabled this menu item we won't add it again.
        if ( isset( $items['0'] ) && $items['0'] == 'Gift Card Balance' ) {
            return $items;
        }

        // The name that is displayed in the menu
        $check_balance_page_title = apply_filters( 'pwgc_my_account_check_balance_title', __( 'Gift Card Balance', 'pw-woocommerce-gift-cards' ) );

        // Where to place this new menu entry.
        $insert_after = apply_filters( 'pwgc_my_account_check_balance_after_menu', __( 'Orders', 'woocommerce' ), $items, $endpoints );

        $insert_index = 0;
        foreach( $items as $endpoint => $name ) {
            $insert_index++;
            if ( $name === $insert_after ) {
                break;
            }
        }

        $items = array_slice( $items, 0, $insert_index, true ) + array( 'pw-gift-card-balance' => $check_balance_page_title ) + array_slice( $items, $insert_index, NULL, true );

        return $items;
    }

    function woocommerce_get_endpoint_url( $url, $endpoint, $value, $permalink ) {
        global $pw_gift_cards;

        if ( $endpoint == 'pw-gift-card-balance' ) {
            return get_permalink( $pw_gift_cards->get_balance_page() );
        }

        return $url;
    }
}

global $pw_gift_cards_shortcodes;
$pw_gift_cards_shortcodes = new PW_Gift_Cards_Shortcodes();

endif;
