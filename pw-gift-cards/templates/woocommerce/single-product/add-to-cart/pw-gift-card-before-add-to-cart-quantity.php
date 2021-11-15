<?php

defined( 'ABSPATH' ) or exit;

global $product;
global $pw_gift_cards;

$pwgc_custom_amount = isset( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && !empty( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) && is_numeric( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ? number_format( $_REQUEST[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ], wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ) : '';
$pwgc_to = isset( $_REQUEST[ PWGC_TO_META_KEY ] ) ? stripslashes( htmlentities( $_REQUEST[ PWGC_TO_META_KEY ] ) ) : '';
$pwgc_recipient_name = isset( $_REQUEST[ PWGC_RECIPIENT_NAME_META_KEY ] ) ? stripslashes( htmlentities( $_REQUEST[ PWGC_RECIPIENT_NAME_META_KEY ] ) ) : '';
$pwgc_message = isset( $_REQUEST[ PWGC_MESSAGE_META_KEY ] ) ? stripslashes( htmlentities( str_replace( '<br />', "\n", $_REQUEST[ PWGC_MESSAGE_META_KEY ] ) ) ) : '';
$pwgc_delivery_date = isset( $_REQUEST[ PWGC_DELIVERY_DATE_META_KEY ] ) ? stripslashes( htmlentities( $_REQUEST[ PWGC_DELIVERY_DATE_META_KEY ] ) ) : '';
$pwgc_physical_card = $product->get_pwgc_is_physical_card();
$pwgc_show_recipient_name = $product->get_pwgc_show_recipient_name();

$pwgc_from = '';
if ( isset( $_REQUEST[ PWGC_FROM_META_KEY ] ) ) {
    $pwgc_from = stripslashes( htmlentities( $_REQUEST[ PWGC_FROM_META_KEY ] ) );
} else if ( 'yes' === get_option( 'pwgc_current_user_populates_from_field', 'yes' ) ) {
    $current_user = wp_get_current_user();
    $pwgc_from = $current_user->display_name;
}

if ( isset( $_REQUEST[ 'attribute_' . PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) ) {
    $selected = true;
} else {
    $default_attribute = $product->get_variation_default_attribute( PWGC_DENOMINATION_ATTRIBUTE_NAME );
    $selected = !empty( $default_attribute );
}

if ( $pw_gift_cards->use_default_currency_in_cart() ) {
    $pwgc_custom_amount = apply_filters( 'pwgc_to_current_currency', $pwgc_custom_amount );
}

?>
<style>
    .pwgc-field-container {
        margin-bottom: 14px;
    }

    .pwgc-label {
        font-weight: 600;
        display: block;
    }

    .pwgc-subtitle {
        font-size: 11px;
        line-height: 1.465;
        color: #767676;
    }

    .pwgc-input-text {
        width: 95%;
    }

    #pwgc-recipient-count {
        font-weight: 600;
    }

    #pwgc-quantity-one-per-recipient {
        display: none;
    }

    #pwgc-message {
        display: block;
        height: 100px;
        width: 95%;
    }

    .pwgc-hidden {
        display: none;
    }

    <?php
        if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) && ! apply_filters( 'pwgc_show_amount_price', $product->has_amount_on_sale() ) ) {
            // Don't really need to repeat the amount on the Product Page unless there is a sale.
            ?>
            .woocommerce-variation-description, .woocommerce-variation-price, .woocommerce-variation-availability {
                display: none !important;
            }
            <?php
        }
    ?>

    .add_to_cart_wrapper {
        flex-wrap: wrap;
    }

    #pwgc-purchase-container {
        width: 100%;
        flex-basis: 100% !important;
    }

    .single_add_to_cart_button {
        flex: 1;
    }

    .woocommerce-variation-add-to-cart {
        flex-wrap: wrap !important;
    }
</style>
<div id="pwgc-purchase-container" style="<?php echo $selected ? '' : 'display: none;'; ?>" data-min-amount="<?php echo apply_filters( 'pwgc_to_current_currency', $product->get_pwgc_custom_amount_min() ); ?>" data-max-amount="<?php echo apply_filters( 'pwgc_to_current_currency', $product->get_pwgc_custom_amount_max() ); ?>">
    <div id="pwgc-custom-amount-form" class="pwgc-field-container <?php echo !empty( $pwgc_custom_amount ) ? '' : 'pwgc-hidden'; ?>">
        <label for="pwgc-custom-amount" class="pwgc-label"><?php echo __( pwgc_get_other_amount_prompt( $product ), 'pw-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?>&nbsp;</label>
        <input type="text" id="pwgc-custom-amount" class="short wc_input_price" name="<?php echo PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY; ?>" value="<?php echo esc_attr( $pwgc_custom_amount ); ?>" />
        <div id="pwgc-custom-amount-error" class="pwgc-subtitle" style="color: red !important;"></div>
    </div>

    <?php
        if ( $pwgc_physical_card ) {
            ?>
            <div id="pwgc-form-shipping-address-message" class="pwgc-field-container">
                <?php _e( 'Gift card will be delivered to the Shipping Address entered during checkout.', 'pw-woocommerce-gift-cards' ); ?>
            </div>
            <?php
        } else {
            ?>
            <div id="pwgc-form-to" class="pwgc-field-container">
                <label for="pwgc-to" class="pwgc-label"><?php echo __( PWGC_TO_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
                <?php
                    $placeholder = __( 'Enter an email address for each recipient', 'pw-woocommerce-gift-cards' );
                    $subtitle = __( 'Separate multiple email addresses with a comma.', 'pw-woocommerce-gift-cards' );

                    if ( 'yes' !== get_option( 'pwgc_allow_multiple_recipients', 'yes' ) ) {
                        $placeholder = __( 'Recipient email address', 'pw-woocommerce-gift-cards' );
                        $subtitle = '';
                    }

                    ?>
                    <input type="text" id="pwgc-to" name="<?php echo PWGC_TO_META_KEY; ?>" class="pwgc-input-text" placeholder="<?php echo $placeholder; ?>" value="<?php echo $pwgc_to; ?>" required>
                    <div class="pwgc-subtitle"><?php echo $subtitle; ?></div>
                    <?php
                ?>
            </div>
            <?php
        }
    ?>

    <?php
        if ( $pwgc_show_recipient_name ) {
            ?>
            <div id="pwgc-form-recipient-name" class="pwgc-field-container">
                <label for="pwgc-recipient-name" class="pwgc-label"><?php echo __( PWGC_RECIPIENT_NAME_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
                <input type="text" id="pwgc-recipient-name" name="<?php echo PWGC_RECIPIENT_NAME_META_KEY; ?>" class="pwgc-input-text" placeholder="<?php _e( 'Enter a friendly name for the recipient (optional).', 'pw-woocommerce-gift-cards' ); ?>" value="<?php echo $pwgc_recipient_name; ?>">
            </div>
            <?php
        }
    ?>

    <div id="pwgc-form-from" class="pwgc-field-container">
        <label for="pwgc-from" class="pwgc-label"><?php echo __( PWGC_FROM_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
        <input type="text" id="pwgc-from" name="<?php echo PWGC_FROM_META_KEY; ?>" class="pwgc-input-text" placeholder="<?php _e( 'Your name', 'pw-woocommerce-gift-cards' ); ?>" value="<?php echo $pwgc_from; ?>" required>
    </div>

    <div id="pwgc-form-message" class="pwgc-field-container">
        <label for="pwgc-message" class="pwgc-label"><?php echo __( PWGC_MESSAGE_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?> <?php _e( '(optional)', 'pw-woocommerce-gift-cards' ); ?></label>
        <textarea id="pwgc-message" name="<?php echo PWGC_MESSAGE_META_KEY; ?>" placeholder="<?php _e( 'Add a message', 'pw-woocommerce-gift-cards' ); ?>" maxlength="<?php echo PWGC_MAX_MESSAGE_CHARACTERS; ?>"><?php echo $pwgc_message; ?></textarea>
        <div class="pwgc-subtitle"><span id="pwgc-message-characters-remaining"><?php echo PWGC_MAX_MESSAGE_CHARACTERS; ?></span> <?php _e( 'characters remaining', 'pw-woocommerce-gift-cards' ); ?></div>
    </div>

    <?php
        if ( !$pwgc_physical_card && 'yes' === get_option( 'pwgc_allow_scheduled_delivery', 'yes' ) ) {
            ?>
            <div id="pwgc-form-delivery-date" class="pwgc-field-container">
                <label for="pwgc-delivery-date" class="pwgc-label"><?php echo __( PWGC_DELIVERY_DATE_META_DISPLAY_NAME, 'pw-woocommerce-gift-cards' ); ?></label>
                <input type="text" id="pwgc-delivery-date" name="<?php echo PWGC_DELIVERY_DATE_META_KEY; ?>" class="pwgc-input-text" placeholder="<?php _e( 'Now', 'pw-woocommerce-gift-cards' ); ?>" value="<?php echo $pwgc_delivery_date; ?>" autocomplete="off">
                <div class="pwgc-subtitle"><?php _e( 'Up to a year from today', 'pw-woocommerce-gift-cards' ); ?></div>
            </div>
            <?php
        }
    ?>

    <div id="pwgc-quantity-one-per-recipient" class="pwgc-field-container">
        <div class="pwgc-label"><?php _e( 'Quantity', 'pw-woocommerce-gift-cards' ); ?>: <span id="pwgc-recipient-count">1</span></div>
        <div class="pwgc-subtitle"><?php _e( '1 to each recipient', 'pw-woocommerce-gift-cards' ); ?></div>
    </div>
</div>
<?php
