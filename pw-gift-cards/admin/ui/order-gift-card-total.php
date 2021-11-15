<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards;

foreach( $order->get_items( 'pw_gift_card' ) as $line ) {
    $gift_card = new PW_Gift_Card( $line->get_card_number() );

    ?>
    <tr>
        <td class="label">
            <?php
                _e( 'PW Gift Card', 'pw-woocommerce-gift-cards' );

                if ( $gift_card->get_id() ) {
                    $status = '';
                    if ( !$gift_card->get_active() ) {
                        $status = __( ' (inactive)', 'pw-woocommerce-gift-cards' );
                    }

                    $check_balance_url = $gift_card->check_balance_url();
                    echo $status . ' <a href="' . $check_balance_url . '">' . $line->get_card_number() . '</a>: ';
                } else {
                    echo ' ' . __( '(deleted)', 'pw-woocommerce-gift-cards' ) . ' ' . $line->get_card_number() . ':';
                }
            ?>
        </td>
        <td width="1%"></td>
        <td class="total">
            <?php
                $amount = apply_filters( 'pwgc_to_order_currency', $line->get_amount() * -1, $order );
                if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) && isset( $GLOBALS['woocommerce-aelia-currencyswitcher'] ) ) {
                    ?>
                    <span class="woocommerce-Price-amount amount"><?php echo $amount; ?></span>
                    <?php
                } else {
                    $args = array();
                    if ( class_exists( 'WOOMC\App' ) ) {
                        $args['currency'] = get_post_meta( $order->get_id(), '_order_currency', true );
                    }

                    echo wc_price( $amount, $args );
                }
            ?>
        </td>
    </tr>
    <?php
}
