<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards_admin;

?>
<style>
    #pwgc-save-settings-form input[type="text"] {
        width: 400px !important;
    }
</style>
<div id="pwgc-section-settings" class="pwgc-section" style="<?php pwgc_dashboard_helper( 'settings', 'display: block;' ); ?>">
    <div style="background-color: #fff; border-style: solid; border-color: #000; border-width: 1px; border-radius: 6px; padding: 16px;">
        <div style="font-weight: 600; font-size: 1.2em;"><?php _e( 'Not receiving gift card emails?', 'pw-woocommerce-gift-cards' ); ?></div>
        <?php _e( 'Confirm that the Order Status is "Complete" for the gift cards purchased. You should see the Gift Card number along with the recipient email in the Order line items inside the admin area. If the order is Complete and it shows the Gift Card number, try disabling the WooCommerce Transactional Email System for the gift cards below.', 'pw-woocommerce-gift-cards' ); ?>
    </div>
    <form id="pwgc-save-settings-form" method="post">
        <?php
            $settings = $pw_gift_cards_admin->settings;
            $settings[0]['title'] = '';

            WC_Admin_Settings::output_fields( $settings );
        ?>
        <div id="pwgc-save-settings-message"></div>
        <p><input type="submit" id="pwgc-save-settings-button" class="button button-primary" value="<?php _e( 'Save Settings', 'pw-woocommerce-gift-cards' ); ?>"></p>
    </form>
</div>
