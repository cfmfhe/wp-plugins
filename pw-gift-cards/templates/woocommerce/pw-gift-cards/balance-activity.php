<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards;
global $gift_card;

if ( 'yes' !== get_option( 'pwgc_check_balance_show_transactions', 'no' ) || empty( $gift_card ) ) {
    return;
}

?>
<style>
</style>
<table>
    <thead>
        <tr>
            <th><?php _e( 'Date', 'pw-woocommerce-gift-cards' ); ?></th>
            <th><?php _e( 'User', 'pw-woocommerce-gift-cards' ); ?></th>
            <th><?php _e( 'Note', 'pw-woocommerce-gift-cards' ); ?></th>
            <th><?php _e( 'Amount', 'pw-woocommerce-gift-cards' ); ?></th>
            <th><?php _e( 'Balance', 'pw-woocommerce-gift-cards' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $running_balance = $gift_card->get_balance();
            foreach ( $gift_card->get_activity() as $activity ) {
                ?>
                <tr>
                    <td>
                        <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $activity->activity_date ) ); ?>
                    </td>
                    <td>
                        <?php
                            echo esc_html( $activity->user );
                            if ( !empty( $activity->user_email ) ) {
                                ?>
                                <br />
                                <a href="mailto: <?php echo esc_attr( $activity->user_email ); ?>"><?php echo esc_attr( $activity->user_email ); ?></a>
                                <?php
                            } else {
                                _e( 'Guest', 'pw-woocommerce-gift-cards' );
                            }
                        ?>
                    </td>
                    <td>
                        <?php echo esc_html( $activity->note ); ?>
                    </td>
                    <td class="pwgc-balance-activity <?php echo ( $activity->amount < 0 ) ? 'pwgc-balance-activity-negative' : ''; ?>">
                        <?php
                            if ( $activity->amount != 0 ) {
                                $_REQUEST['woocs_block_price_hook'] = true; // Needed for WooCommerce Currency Switcher by realmag777
                                echo wc_price( apply_filters( 'pwgc_to_current_currency', $activity->amount ) );
                            }
                        ?>
                    </td>
                    <td class="pwgc-balance-activity">
                        <?php
                            $_REQUEST['woocs_block_price_hook'] = true; // Needed for WooCommerce Currency Switcher by realmag777
                            echo wc_price( apply_filters( 'pwgc_to_current_currency', $running_balance ) );
                        ?>
                    </td>
                </tr>
                <?php

                $running_balance -= $activity->amount;
            }
        ?>
    </tbody>
</table>
