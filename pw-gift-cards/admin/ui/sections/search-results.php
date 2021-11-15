<?php

defined( 'ABSPATH' ) or exit;

if ( count( $gift_cards ) == 0 ) {
    ?>
    <h1><?php _e( 'No results', 'pw-woocommerce-gift-cards' ); ?></h1>
    <p><?php _e( 'There are no gift cards found matching your search terms.', 'pw-woocommerce-gift-cards' ); ?></p>
    <?php
} else {
    ?>
    <table id="pwgc-search-results-table" class="pwgc-admin-table">
        <tr>
            <th><?php _e( 'Card Number', 'pw-woocommerce-gift-cards' ); ?></th>
            <th><?php _e( 'Balance', 'pw-woocommerce-gift-cards' ); ?></th>
            <th class="pwgc-expiration-date-element" <?php if ( 'no' !== get_option( 'pwgc_no_expiration_date', 'no' ) ) { echo 'style="display: none;"'; } ?>><?php _e( 'Expiration Date', 'pw-woocommerce-gift-cards' ); ?></th>
            <th><?php _e( 'Recipient', 'pw-woocommerce-gift-cards' ); ?></th>
            <th>&nbsp;</th>
        </tr>
        <?php
            require_once( 'search-results-rows.php' );
        ?>
    </table>
    <?php
}
