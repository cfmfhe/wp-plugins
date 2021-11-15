<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards;

?>
<style>
    #pwgc-balance-container {
        text-align: center;
    }

    #pwgc-balance-title {
        font-size: 150%;
        margin-bottom: 16px;
    }

    #pwgc-balance-error {
        color: red;
        font-weight: 600;
    }

    #pwgc-balance-message {
        color: blue;
        font-weight: 600;
    }

    #pwgc-balance-amount-value {
        font-size: 200%;
        font-weight: 600;
        color: #329926;
    }

    #pwgc-balance-expiration-date-container {
        font-size: 80%;
        color: #999999;
        display: none;
    }

    #pwgc-balance-number-container {
        text-align: center;
        margin: 32px auto;
    }

    #pwgc-balance-number {
        width: 300px;
        margin-bottom: 10px;
        display: inline-block;
    }

    #pwgc-balance-reload, #pwgc-manual-debit {
        display: none;
    }

    #pwgc-balance-activity {
        margin-top: 24px;
    }

    #pwgc-balance-buttons {
        margin-top: 24px;
    }

</style>
<form id="pwgc-balance-form">
<div id="pwgc-balance-container">
    <?php
        $icon = apply_filters( 'pwgc_check_balance_image', '' );

        if ( !empty( $icon ) ) {
            echo $icon;
        } else if ( 'yes' === get_option( 'pwgc_use_fontawesome', 'yes' ) ) {
            wp_enqueue_script( 'fontawesome-all' );
            ?>
            <i class="fas fa-gift fa-7x"></i>
            <?php
        }
    ?>
    <div id="pwgc-balance-title"><?php _e( 'Check Gift Card Balance', 'pw-woocommerce-gift-cards' ); ?></div>

    <div id="pwgc-balance-number-container">
        <input type="text" id="pwgc-balance-number" name="card_number" autocomplete="off" placeholder="<?php _e( 'Gift Card Number', 'pw-woocommerce-gift-cards' ); ?>" value="<?php echo isset( $_GET['card_number'] ) ? esc_html( $_GET['card_number'] ) : ''; ?>" required>
        <input type="submit" id="pwgc-balance-button" value="<?php _e( 'Check Balance', 'pw-woocommerce-gift-cards' ); ?>">
    </div>

    <div id="pwgc-balance-error"></div>
    <div id="pwgc-balance-message"></div>
    <div id="pwgc-balance-amount"></div>

    <?php
        if ( 'no' === get_option( 'pwgc_no_expiration_date', 'no' ) ) {
            ?>
            <div id="pwgc-balance-expiration-date-container">
                <?php _e( 'Expires', 'pw-woocommerce-gift-cards' ); ?> <span id="pwgc-balance-expiration-date"></span>
            </div>
            <?php
        }
    ?>
    <div id="pwgc-balance-buttons">
        <?php
            if ( 'yes' === get_option( 'pwgc_allow_reloading', 'yes' ) ) {
                $gift_card_product = $pw_gift_cards->get_gift_card_product();
                if ( !empty( $gift_card_product ) ) {
                    ?>
                    <input type="button" id="pwgc-balance-reload" data-url="<?php echo $gift_card_product->get_permalink(); ?>" data-card-number="" value="<?php echo esc_html( __( 'Add more funds to this gift card.', 'pw-woocommerce-gift-cards' ) ); ?>">
                    <?php
                }
            }

            if ( 'yes' === get_option( 'pwgc_check_balance_allow_manual_debit', 'no' ) ) {
                ?>
                <input type="button" id="pwgc-manual-debit" value="<?php echo esc_html( __( 'Debit Balance', 'pw-woocommerce-gift-cards' ) ); ?>">
                <?php
            }
        ?>
    </div>

    <div id="pwgc-balance-activity"></div>
</div>
</form>
