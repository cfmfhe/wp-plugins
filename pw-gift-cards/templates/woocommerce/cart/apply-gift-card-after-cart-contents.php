<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards_redeeming;
if ( $pw_gift_cards_redeeming->cart_contains_gift_card() && 'yes' !== get_option( 'pwgc_allow_gift_card_purchasing', 'yes' ) ) {
    return;
}

if ( 'after_cart_contents' === get_option( 'pwgc_redeem_cart_location', 'proceed_to_checkout' ) ) {
    ?>
    <style>
        #pwgc-redeem-error {
            color: red;
        }
    </style>
    <tr>
        <td colspan="6" class="actions">
            <div class="coupon">
                <div id="pwgc-redeem-error"></div>
                <label for="pwgc-redeem-gift-card-number"><?php esc_html_e( 'Gift Card:', 'pw-woocommerce-gift-cards' ); ?></label>
                <input type="text" name="pw_gift_card" class="input-text" id="pwgc-redeem-gift-card-number" value="" placeholder="<?php esc_attr_e( 'Gift card number', 'pw-woocommerce-gift-cards' ); ?>" />
                <input type="submit" class="button" id="pwgc-apply-gift-card" name="apply_pw_gift_card" value="<?php esc_attr_e( 'Apply gift card', 'pw-woocommerce-gift-cards' ); ?>" data-wait-text="<?php esc_html_e( 'Please wait...', 'pw-woocommerce-gift-cards' ); ?>">
            </div>
        </td>
    </tr>
    <?php
}
