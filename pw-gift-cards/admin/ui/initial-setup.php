<?php

defined( 'ABSPATH' ) or exit;

$gift_card_product = $pw_gift_cards->get_gift_card_product();
$balance_page = $pw_gift_cards->get_balance_page();

if ( empty( $gift_card_product ) || empty( $balance_page ) ) {
    ?>
    <div id="pwgc-setup-container">
        <div id="pwgc-setup-error"></div>
        <div class="pwgc-setup-section">
            <div class="pwgc-setup-header">
                <?php printf( __( 'Create a "%s" product to start selling Gift Cards.', 'pw-woocommerce-gift-cards' ), PWGC_PRODUCT_TYPE_NAME ); ?>
            </div>
            <?php
                if ( empty( $gift_card_product ) ) {
                    $create_product_button_text = __( 'Create the Gift Card product', 'pw-woocommerce-gift-cards' );

                    ?>
                    <a href="#" id="pwgc-setup-create-product" class="button button-secondary" data-text="<?php echo esc_attr( $create_product_button_text ); ?>"><?php echo esc_attr( $create_product_button_text ); ?></a>
                    <?php
                }

            ?>
            <div id="pwgc-setup-create-product-success" style="<?php echo empty( $gift_card_product ) ? '' : 'display: block;'; ?>">
                <div class="pwgc-setup-header" style="color: green;"><i class="far fa-check-circle"></i> <?php _e( 'Success!', 'pw-woocommerce-gift-cards' ); ?></div>
                <?php _e( 'The Gift Card product has been created. Click on the Products menu in the left to edit it.', 'pw-woocommerce-gift-cards' ); ?>
            </div>
        </div>
        <div class="pwgc-setup-section">
            <div class="pwgc-setup-header">
                <?php _e( 'Let your customers check their gift card balances.', 'pw-woocommerce-gift-cards' ); ?>
            </div>
            <?php
                if ( empty( $balance_page ) ) {
                    $create_balance_page_button_text = __( 'Create the Check Gift Card Balance page.', 'pw-woocommerce-gift-cards' );

                    ?>
                    <p><?php printf( __( 'Create a page with the %s', 'pw-woocommerce-gift-cards' ), '<a href="https://support.wordpress.com/shortcodes/" target="_blank">shortcode</a>' ); ?> <span class="pwgc-shortcode">[<?php echo PWGC_BALANCE_SHORTCODE; ?>]</span></p>
                    <a href="#" id="pwgc-setup-create-balance-page" class="button button-secondary" data-text="<?php echo esc_attr( $create_balance_page_button_text ); ?>"><?php echo esc_attr( $create_balance_page_button_text ); ?></a>
                    <?php
                }
            ?>
            <div id="pwgc-setup-create-balance-page-success" style="<?php echo empty( $balance_page ) ? '' : 'display: block;'; ?>">
                <div class="pwgc-setup-header" style="color: green;"><i class="far fa-check-circle"></i> <?php _e( 'Success!', 'pw-woocommerce-gift-cards' ); ?></div>
                <?php _e( 'The check gift card balance page has been created. Click on the Pages menu in the left to edit it.', 'pw-woocommerce-gift-cards' ); ?>
            </div>
        </div>
    </div>
    <?php
}
