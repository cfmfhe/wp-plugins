<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards;

?>
<div id="pwgc-activation-main" class="pwgc-bordered-container pwgc-hidden">
    <div class="pwgc-heading"><?php _e( 'Enter the license key that was sent to your email address.', 'pw-woocommerce-gift-cards' ); ?></div>
    <div class="pwgc-activation-error"></div>
    <form id="pwgc-activation">
        <input type="text" id="pwgc-license-key" name="license-key" class="regular-text" placeholder="<?php _e( 'License Key', 'pw-woocommerce-gift-cards' ); ?>" required>
        <input type="submit" id="pwgc-activate-license" name="activate-license" value="<?php _e( 'Activate', 'pw-woocommerce-gift-cards' ); ?>" class="button button-primary" />
    </form>
    <div style="margin-top: 1em;"><?php printf( __( 'If you need assistance please contact %s', 'pw-woocommerce-gift-cards' ), '<a href="mailto:us@pimwick.com">us@pimwick.com</a>' ); ?></div>
</div>
<?php

    if ( $pw_gift_cards->license->has_activated() && $pw_gift_cards->license->is_expired()  && !get_option( 'pw-gift-cards-hide-renew-notice' ) ) {
        ?>
        <div id="pwgc-renew-container" class="pwgc-bordered-container" style="margin-bottom: 25px;">
            <h3 style="font-weight: 600; color: red;">Your license has expired.</h3>
            <p><?php _e( 'You may continue using the plugin with all features enabled, however you will no longer receive new updates and features until you renew.', 'pw-woocommerce-gift-cards' ); ?></p>
            <p>
                <a href="<?php echo $pw_gift_cards->license->get_renew_url(); ?>" target="_blank" class="button button-primary"><?php _e( 'Renew your license', 'pw-woocommerce-gift-cards' ); ?></a>
                <span id="pwgc-renew-dismiss" class="button"><?php _e( 'Hide this notice', 'pw-woocommerce-gift-cards' ); ?></span>
            </p>
        </div>
        <?php
    }
