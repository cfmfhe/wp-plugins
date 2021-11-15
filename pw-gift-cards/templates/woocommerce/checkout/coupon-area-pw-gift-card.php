<?php

defined( 'ABSPATH' ) || exit;

global $pw_gift_cards_redeeming;
if ( $pw_gift_cards_redeeming->cart_contains_gift_card() && 'yes' !== get_option( 'pwgc_allow_gift_card_purchasing', 'yes' ) ) {
    return;
}

if ( 'before_checkout_form' === get_option( 'pwgc_redeem_checkout_location', 'review_order_before_submit' ) ) {
	?>
	<div class="woocommerce-form-coupon-toggle">
		<?php wc_print_notice( __( 'Have a gift card?', 'pw-woocommerce-gift-cards' ) . ' <a href="#" class="show-pw-gift-card">' . __( 'Click here to enter your gift card number', 'pw-woocommerce-gift-cards' ) . '</a>', 'notice' ); ?>
	</div>

	<form class="checkout_pw_gift_card woocommerce-form-coupon" method="post" style="display:none">

		<p><?php esc_html_e( 'If you have a gift card number, please apply it below.', 'pw-woocommerce-gift-cards' ); ?></p>

		<p class="form-row form-row-first">
			<input type="text" name="pw_gift_card_number" class="input-text" placeholder="<?php esc_attr_e( 'Gift card number', 'pw-woocommerce-gift-cards' ); ?>" id="pwgc-redeem-gift-card-number" value="" />
		</p>

		<p class="form-row form-row-last">
			<input type="submit" class="button" name="apply_pw_gift_card" id="pwgc-apply-gift-card-checkout" value="<?php esc_attr_e( 'Apply gift card', 'pw-woocommerce-gift-cards' ); ?>" data-wait-text="<?php esc_html_e( 'Please wait...', 'pw-woocommerce-gift-cards' ); ?>">
		</p>

		<p>
			<div id="pwgc-redeem-error" style="color: red;"></div>
		</p>

		<div class="clear"></div>
	</form>
	<?php
}
