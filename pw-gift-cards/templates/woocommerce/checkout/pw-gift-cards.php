<?php

defined( 'ABSPATH' ) or exit;

?>
<style>
    .pwgc-checkout-subtitle {
        line-height: 1.4;
        font-size: 80%;
        font-weight: 300;
    }
</style>
<?php

$session_data = (array) WC()->session->get( PWGC_SESSION_KEY );
if ( isset( $session_data['gift_cards'] ) ) {

    foreach ( $session_data['gift_cards'] as $card_number => $discount_amount ) {
        $pw_gift_card = new PW_Gift_Card( $card_number );
        if ( $pw_gift_card->get_id() ) {
            $balance = apply_filters( 'pwgc_to_current_currency', $pw_gift_card->get_balance() );
            $balance -= $discount_amount;
            $balance = apply_filters( 'pwgc_remaining_balance_checkout', $balance, $pw_gift_card );
            ?>
            <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $card_number ) ); ?>">
                <th>
                    <?php _e( 'Gift card', 'pw-woocommerce-gift-cards' ); ?>
                    <div class="pwgc-checkout-subtitle">
                        <?php echo $pw_gift_card->get_number(); ?><br />
                        <?php echo sprintf( __( 'Remaining balance is %s', 'pw-woocommerce-gift-cards' ), wc_price( $balance ) ); ?>
                        <?php
                            if ( $pw_gift_card->has_expired() ) {
                                ?>
                                <br />
                                <span style="color: red; font-weight: 600;">
                                    <?php _e( 'Expired', 'pw-woocommerce-gift-cards' ); ?>
                                </span>
                                <?php
                            }
                        ?>
                    </div>
                </th>
                <td>
                    <?php echo wc_price( $discount_amount * -1 ); ?>
                    <a href="#" class="pwgc-remove-card" data-card-number="<?php esc_attr_e( $card_number ); ?>"><?php _e( '[Remove]', 'pw-woocommerce-gift-cards' ); ?></a>
                </td>
            </tr>
            <?php
        }
    }
}
