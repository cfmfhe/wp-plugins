<?php

defined( 'ABSPATH' ) or exit;

?>
<div class="pwgc-footer">
    <div class="pwgc-footer-site-logo">
        <a href="https://pimwick.com" target="_blank"><img src="<?php echo $pw_gift_cards->relative_url( '/admin/assets/images/pimwick.png' ); ?>" alt="Pimwick, LLC" width="50" border="0" /></a><br>
        <a href="https://pimwick.com" target="_blank" class="pwgc-footer-site-title">Pimwick, LLC</a>
    </div>
    <div class="pwgc-rating">
        <?php printf( __( 'Need help? Email %s', 'pw-woocommerce-gift-cards' ), '<a href="mailto:us@pimwick.com" class="pwgc-link">us@pimwick.com</a>' ); ?>
        <br>
        <a href="https://wordpress.org/support/plugin/pw-woocommerce-gift-cards/reviews/#new-post" target="_blank" class="pwgc-link"><?php _e( 'Leave a review on WordPress.org.', 'pw-woocommerce-gift-cards') ; ?></a>
    </div>
</div>