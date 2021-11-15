<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards_redeeming;
if ( $pw_gift_cards_redeeming->cart_contains_gift_card() && 'yes' !== get_option( 'pwgc_allow_gift_card_purchasing', 'yes' ) ) {
    return;
}

if ( 'proceed_to_checkout' === get_option( 'pwgc_redeem_cart_location', 'proceed_to_checkout' ) ) {
    ?>
    <style>
        #pwgc-redeem-gift-card-container {
            margin-bottom: 1.0em;
        }

        #pwgc-redeem-gift-card-number {
            width: auto;
        }

        #pwgc-redeem-button {
            display: inline-block;
        }

        #pwgc-redeem-error {
            color: red;
        }
    </style>
    <div id="pwgc-redeem-gift-card-form">
        <form id="pwgc-redeem-form">
            <div id="pwgc-redeem-gift-card-container">
                <label for="pwgc-redeem-gift-card-number"><?php _e( 'Have a gift card?', 'pw-woocommerce-gift-cards' ); ?></label><br>
                <div id="pwgc-redeem-error"></div>
                <input type="text" id="pwgc-redeem-gift-card-number" name="card_number" autocomplete="off" placeholder="<?php esc_html_e( 'Gift card number', 'pw-woocommerce-gift-cards' ); ?>">
                <input type="submit" id="pwgc-redeem-button" class="button" name="redeem_gift_card" value="<?php esc_html_e( 'Apply', 'pw-woocommerce-gift-cards' ); ?>" data-wait-text="<?php esc_html_e( 'Please wait...', 'pw-woocommerce-gift-cards' ); ?>">
            </div>
        </form>
    </div>
    <?php
}
