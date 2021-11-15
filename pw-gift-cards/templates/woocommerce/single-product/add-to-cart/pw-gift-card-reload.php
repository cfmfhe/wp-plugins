<?php

defined( 'ABSPATH' ) or exit;

global $product;

$card_number = $_REQUEST[ PWGC_RELOAD_GIFT_CARD_NUMBER_META_KEY ];
if ( empty( $card_number ) ) {
    return;
}

$pwgc_custom_amount = isset( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && !empty( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ? number_format( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ], wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ) : '';

if ( isset( $_REQUEST[ 'attribute_' . PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) ) {
    $selected = true;
} else {
    $default_attribute = $product->get_variation_default_attribute( PWGC_DENOMINATION_ATTRIBUTE_NAME );
    $selected = !empty( $default_attribute );
}

?>
<style>
    .pwgc-field-container {
        margin-bottom: 14px;
    }

    .pwgc-label {
        font-weight: 600;
    }

    .pwgc-subtitle {
        font-size: 11px !important;
        line-height: 1.465 !important;
        color: #767676 !important;
    }

    .pwgc-hidden {
        display: none;
    }

    .pwgc-reload-gift-card-number {
        font-size: 125%;
        margin-bottom: 1.0em;
    }

    /* Don't really need to repeat this on the Product Page */
    .woocommerce-variation-description, .woocommerce-variation-price, .woocommerce-variation-availability {
        display: none;
    }
</style>
<div id="pwgc-purchase-container" style="<?php echo $selected ? '' : 'display: none;'; ?>" data-min-amount="<?php echo $product->get_pwgc_custom_amount_min(); ?>" data-max-amount="<?php echo $product->get_pwgc_custom_amount_max(); ?>">
    <input type="hidden" name="<?php echo PWGC_RELOAD_GIFT_CARD_NUMBER_META_KEY; ?>" value="<?php echo esc_attr( $card_number ); ?>">
    <div>
        <?php _e( 'You are adding funds to the following gift card:', 'pw-woocommerce-gift-cards' ); ?>
    </div>
    <div class="pwgc-reload-gift-card-number">
        <?php echo $card_number; ?>
    </div>

    <div id="pwgc-custom-amount-form" class="pwgc-field-container <?php echo !empty( $pwgc_custom_amount ) ? '' : 'pwgc-hidden'; ?>">
        <label for="pwgc-custom-amount" class="pwgc-label"><?php echo __( pwgc_get_other_amount_prompt( $product ), 'pw-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?>&nbsp;</label>
        <input type="text" id="pwgc-custom-amount" class="short wc_input_price" name="<?php echo PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY; ?>" value="<?php echo esc_attr( $pwgc_custom_amount ); ?>" />
        <div id="pwgc-custom-amount-error" class="pwgc-subtitle" style="color: red !important;"></div>
    </div>
</div>
<?php
