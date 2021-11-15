<?php

defined( 'ABSPATH' ) or exit;

?>
<div id="pwgc-section-balances" class="pwgc-section" style="<?php pwgc_dashboard_helper( 'balances', 'display: block;' ); ?>">
    <div id="pwgc-balance-summary-container">
        <?php
            require_once( 'balance-summary.php' );
        ?>
    </div>

    <form id="pwgc-balance-search-form">
        <input type="text" id="pwgc-balance-search" name="card_number" autocomplete="off" placeholder="<?php _e( 'Gift card number or recipient email (leave blank for all)', 'pw-woocommerce-gift-cards' ); ?>" value="<?php echo isset( $_GET['card_number'] ) ? esc_html( stripslashes( $_GET['card_number'] ) ) : ''; ?>">
        <input type="submit" id="pwgc-balance-search-button" class="button button-primary" value="<?php _e( 'Search', 'pw-woocommerce-gift-cards' ); ?>">
    </form>

    <div id="pwgc-balance-main-container">
        <div id="pwgc-balance-search-results"></div>
    </div>
</div>
