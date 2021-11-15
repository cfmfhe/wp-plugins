<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards;
global $product_object;

$default_denomination_options = array( '' => __( 'No default', 'pw-woocommerce-gift-cards' ) );
$bonus_recipient_options = array(
    'purchasing_customer' => __( 'The purchasing customer', 'pw-woocommerce-gift-cards' ),
    'gift_card_recipient' => __( 'The gift card recipient.', 'pw-woocommerce-gift-cards' ),
);
$variations = array_map( 'wc_get_product', $product_object->get_children() );

$designs = pwgc_get_designs();
$design_options = array();
foreach ( $designs as $design_id => $design ) {
    $design_options[ $design_id ] = $design['name'];
}

if ( is_a( $product_object, 'WC_Product_PW_Gift_Card' ) ) {
    $other_amount_prompt = pwgc_get_other_amount_prompt( $product_object );

    foreach ( $variations as $variation ) {
        if ( is_a( $variation, 'WC_Product' ) ) {
            if ( $variation->get_regular_price() > 0 ) {
                $price = $pw_gift_cards->pretty_price( $variation->get_regular_price() );
                $default_denomination_options[ esc_attr( $price ) ] = $price;
            } else {
                $default_denomination_options[ esc_attr( $other_amount_prompt ) ] = $other_amount_prompt;
            }
        }
    }

    $default_attributes = $product_object->get_default_attributes();
    $default_amount = isset( $default_attributes[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) ? $default_attributes[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] : '';

    $show_recipient_name = $product_object->get_pwgc_show_recipient_name( 'edit' );
    $custom_amount_allowed = $product_object->get_pwgc_custom_amount_allowed( 'edit' );
    $custom_amount_min = $product_object->get_pwgc_custom_amount_min( 'edit' );
    $custom_amount_max = $product_object->get_pwgc_custom_amount_max( 'edit' );
    $is_physical_card = $product_object->get_pwgc_is_physical_card( 'edit' );
    $no_coupons = $product_object->get_pwgc_no_coupons_allowed( 'edit' );
    $physical_email = $product_object->get_pwgc_physical_email( 'edit' );
    $expire_days = $product_object->get_pwgc_expire_days( 'edit' );
    $design_id = $product_object->get_pwgc_design_id( 'edit' );
    $enable_bonus = $product_object->get_pwgc_enable_bonus( 'edit' );
    $cumulative_bonus = $product_object->get_pwgc_cumulative_bonus( 'edit' );
    $bonus_structure = $product_object->get_pwgc_bonus_structure( 'edit' );
    $bonus_recipient = $product_object->get_pwgc_bonus_recipient( 'edit' );

    // Validate the design
    if ( !isset( $designs[ $design_id ] ) ) {
        reset( $designs );
        $design_id = key( $designs );
    }
} else {
    $default_amount = '';
    $show_recipient_name = false;
    $custom_amount_allowed = true;
    $custom_amount_min = '5';
    $custom_amount_max = '1000';
    $is_physical_card = false;
    $no_coupons = false;
    $physical_email = get_option( 'admin_email' );
    $expire_days = '';
    $design_id = '0';
    $enable_bonus = false;
    $cumulative_bonus = false;
    $bonus_structure = array();
    $bonus_recipient = 'purchasing_customer';
}

?>
<div id='<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>_cards' class='panel woocommerce_options_panel' style="display: none;">
    <?php
        /*
        if ( !get_option( 'pwgc_hide_partner_message', false ) ) {
            $partner_name = 'ACME Gift Cards';
            $partner_url = 'https://www.pimwick.com/';
            $partner_offer = 'Use the code "PIMWICK" and get 10% off your first order!';

            ?>
            <div id="pwgc-partner-container" class="options_group" style="padding: 18px; background-color: #FFFFE3; display: flex;">
                <div style="margin: 0 16px;">
                    <a href="<?php echo $partner_url; ?>" target="_blank"><img src="<?php echo $pw_gift_cards->relative_url( '/admin/assets/images/partner-logo.png' ); ?>" style="height: 75px; width: 120px; display: inline;" alt="Purchase gift cards"></a>
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 130%; margin-bottom: 0.5em;">Order beautiful gift cards, customized with your logo.</div>
                    <div>
                        <a href="<?php echo $partner_url; ?>" target="_blank"><strong><?php echo $partner_name; ?></strong></a> is our premier partner offering high quality, affordable gift cards that can be customized with your store logo.
                        <?php echo $partner_offer; ?>
                        Guaranteed to work with <?php echo PWGC_PRODUCT_NAME; ?>.
                    </div>
                </div>
                <span style="font-size: 70%; white-space: nowrap;"><a href="#" id="pwgc-partner-dismiss">Dismiss</a></span>
            </div>
            <?php
        }
        */
    ?>
    <div class='options_group show_if_<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>'>
        <?php
            woocommerce_wp_text_input( array(
                'id'                => 'pwgc_new_amount',
                'value'             => '',
                'label'             => __( 'Gift card amounts', 'pw-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')',
                'data_type'         => 'price',
                'class'             => 'pwgc-short-text-field',
                'desc_tip'          => 'true',
                'description'       => sprintf( __( 'The available denominations that can be purchased. For example: %1$s25.00, %1$s50.00, %1$s100.00', 'pw-woocommerce-gift-cards' ), get_woocommerce_currency_symbol() ),
            ) );

        ?>
        <div id="pwgc-amounts-container" class="pwbf-form-text">
            <div id="pwgc-amount-container-template" class="pwgc-amount-container pwgc-hidden"><span class="pwgc-remove-amount-button">×</span> <span class="pwgc-amount"></span></div>
            <?php
                foreach ( $variations as $variation ) {
                    if ( is_a( $variation, 'WC_Product' ) ) {
                        if ( $variation->get_regular_price() > 0 ) {
                            ?>
                            <div class="pwgc-amount-container" data-variation_id="<?php echo $variation->get_id(); ?>">
                                <span class="pwgc-remove-amount-button" role="presentation" aria-hidden="true">×</span>
                                <span class="pwgc-amount">
                                    <?php echo $pw_gift_cards->pretty_price( $variation->get_regular_price() ); ?>
                                </span>
                            </div>
                            <?php
                        }
                    }
                }
            ?>
        </div>
        <?php

            woocommerce_wp_checkbox( array(
                'id'                => '_pwgc_custom_amount_allowed',
                'value'             => $custom_amount_allowed ? 'yes' : 'no',
                'cbvalue'           => 'yes',
                'label'             => __( 'Allow custom amounts', 'pw-woocommerce-gift-cards' ),
                'desc_tip'          => 'true',
                'description'       => __( 'Allow the customer to specify the gift card amount when purchasing.', 'pw-woocommerce-gift-cards' )
            ) );

            woocommerce_wp_text_input( array(
                'id'                => '_pwgc_custom_amount_min',
                'value'             => $custom_amount_min,
                'label'             => __( 'Minimum amount', 'pw-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')',
                'data_type'         => 'price',
                'class'             => 'pwgc-short-text-field',
                'custom_attributes' => $custom_amount_allowed ? array( 'required' => 'required' ) : '',
                'wrapper_class'     => $custom_amount_allowed ? '' : 'pwgc-hidden',
                'desc_tip'          => 'true',
                'description'       => __( 'The minimum gift card amount that can be chosen by the customer. Required.', 'pw-woocommerce-gift-cards' )
            ) );

            woocommerce_wp_text_input( array(
                'id'                => '_pwgc_custom_amount_max',
                'value'             => $custom_amount_max,
                'label'             => __( 'Maximum amount', 'pw-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')',
                'data_type'         => 'price',
                'class'             => 'pwgc-short-text-field',
                'custom_attributes' => $custom_amount_allowed ? array( 'required' => 'required' ) : '',
                'wrapper_class'     => $custom_amount_allowed ? '' : 'pwgc-hidden',
                'desc_tip'          => 'true',
                'description'       => __( 'The maximum gift card amount that can be chosen by the customer. Required.', 'pw-woocommerce-gift-cards' )
            ) );

            woocommerce_wp_checkbox( array(
                'id'                => '_pwgc_show_recipient_name',
                'value'             => $show_recipient_name ? 'yes' : 'no',
                'cbvalue'           => 'yes',
                'label'             => __( 'Include recipient name', 'pw-woocommerce-gift-cards' ),
                'desc_tip'          => 'true',
                'description'       => __( 'Allow the customer to specify a friendly name for the gift card recipient when purchasing. (Dad, Mom, Uncle Joe, etc)', 'pw-woocommerce-gift-cards' )
            ) );

            woocommerce_wp_checkbox( array(
                'id'                => '_pwgc_no_coupons_allowed',
                'value'             => $no_coupons ? 'yes' : 'no',
                'cbvalue'           => 'yes',
                'label'             => __( 'Ignore coupons', 'pw-woocommerce-gift-cards' ),
                'desc_tip'          => 'true',
                'description'       => __( 'Check this box and coupons will not discount the purchase price of a gift card, although they will still apply to other items in the cart. Default: Unchecked.', 'pw-woocommerce-gift-cards' )
            ) );

            woocommerce_wp_select( array(
                'id'                => '_pwgc_default_amount',
                'value'             => $default_amount,
                'label'             => __( 'Default amount', 'pw-woocommerce-gift-cards' ),
                'options'           => $default_denomination_options,
                'desc_tip'          => 'true',
                'description'       => __( 'The amount that is pre-selected when a customer first views this product. They can choose another amount.', 'pw-woocommerce-gift-cards' )
            ) );

            woocommerce_wp_checkbox( array(
                'id'                => '_pwgc_is_physical_card',
                'value'             => $is_physical_card ? 'yes' : 'no',
                'cbvalue'           => 'yes',
                'label'             => __( 'Physical gift card?', 'pw-woocommerce-gift-cards' ),
                'desc_tip'          => 'true',
                'description'       => __( 'When checked, the generated gift card number will be emailed to address specified below. A physical gift card should be created and mailed to the Shipping Address on the order. Leave unchecked for traditional eGift cards. Default: Unchecked.', 'pw-woocommerce-gift-cards' )
            ) );

            woocommerce_wp_text_input( array(
                'id'                => '_pwgc_physical_email',
                'value'             => $physical_email,
                'label'             => __( 'Recipient email address', 'pw-woocommerce-gift-cards' ),
                'wrapper_class'     => $is_physical_card ? '' : 'pwgc-hidden',
                'desc_tip'          => 'true',
                'description'       => __( 'The email address that will receive the generated gift card number. This person will print the gift card and mail it to the shipping address. Leave blank if you do not want to generate a gift card number for this product (for example, if you have pre-printed gift cards).', 'pw-woocommerce-gift-cards' )
            ) );

            if ( 'no' === get_option( 'pwgc_no_expiration_date', 'no' ) ) {
                woocommerce_wp_text_input( array(
                    'id'                => '_pwgc_expire_days',
                    'value'             => empty( $expire_days ) ? '' : $expire_days,
                    'label'             => __( 'Expires in X days', 'pw-woocommerce-gift-cards' ),
                    'data_type'         => 'number',
                    'class'             => 'pwgc-short-text-field',
                    'description'       => __( '(optional) The number of days after the purchase date when the gift card will expire. If blank, the gift card will not expire.', 'pw-woocommerce-gift-cards' )
                ) );
            }

            $default_country = get_option( 'woocommerce_default_country', '' );
            if ( empty( $default_country ) || strpos( $default_country, 'US:' ) === 0 ) {
                ?>
                <div id="pwbf-expire-days-us-warning" class="pwbf-form-text">
                    <?php
                        printf( __( 'In accordance with the %s, US-based gift cards should not expire for at least 5 years (1,825 days).', 'pw-woocommerce-gift-cards' ), '<a href="https://www.gpo.gov/fdsys/pkg/BILLS-111hr627enr/pdf/BILLS-111hr627enr.pdf" target="_blank">Credit CARD Act of 2009</a>' );
                    ?>
                </div>
                <?php
            }
        ?>
    </div>
    <div class="options_group show_if_<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>">
        <?php
            woocommerce_wp_select( array(
                'id'                => '_pwgc_design_id',
                'value'             => $design_id,
                'label'             => __( 'Email Design', 'pw-woocommerce-gift-cards' ),
                'options'           => $design_options,
                'desc_tip'          => 'true',
                'description'       => __( 'The email sent to the recipient will feature this design.', 'pw-woocommerce-gift-cards' )
            ) );

            $designer_url = add_query_arg( 'page', 'pw-gift-cards', admin_url( 'admin.php' ) );
            $designer_url = add_query_arg( 'section', 'designer', $designer_url );
        ?>
        <p class="form-field">
            <a href="<?php echo $designer_url; ?>" target="_blank" class="button"><?php _e( 'Launch the Gift Card Designer', 'pw-woocommerce-gift-cards' ); ?></a>
        </p>
    </div>
    <div class="options_group show_if_<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>">
        <?php
            woocommerce_wp_checkbox( array(
                'id'                => '_pwgc_enable_bonus',
                'value'             => $enable_bonus ? 'yes' : 'no',
                'cbvalue'           => 'yes',
                'label'             => __( 'Enable bonus gift cards?', 'pw-woocommerce-gift-cards' ),
                'description'       => __( 'Offering a bonus for purchasing gift cards is a great way to incentivise customers. For example: Buy a $25 gift card, get a $5 bonus card!', 'pw-woocommerce-gift-cards' ),
            ) );
        ?>
        <div id="pwgc_bonus_container" class="<?php echo $enable_bonus ? '' : 'pwgc-hidden'; ?>">
            <p class="form-field">
                <a id="pwgc-add-bonus-amount-button" class="button"><?php _e( 'Add a bonus amount', 'pw-woocommerce-gift-cards' ); ?></a>
            </p>
            <div id="pwgc-bonus-amounts-container" class="pwbf-form-text" style="display: inline-block;">
                <div id="pwgc-bonus-amount-container-template" class="pwgc-bonus-amount-container pwgc-hidden"><span class="pwgc-remove-bonus-amount-button">×</span> <span class="pwgc-bonus-amount"></span></div>
                <?php
                    if ( !empty( $bonus_structure ) ) {
                        ksort( $bonus_structure );

                        foreach ( $bonus_structure as $spend => $get ) {
                            ?>
                            <div class="pwgc-bonus-amount-container" data-key="<?php echo $spend; ?>">
                                <span class="pwgc-remove-bonus-amount-button">×</span>
                                <span class="pwgc-bonus-amount">
                                    <?php printf( __( 'Buy a %s gift card, get a %s bonus card', 'pw-woocommerce-gift-cards' ), $pw_gift_cards->pretty_price( $spend ), $pw_gift_cards->pretty_price( $get ) ); ?>
                                </span>
                            </div>
                            <?php
                        }
                    }
                ?>
            </div>
            <?php
                woocommerce_wp_checkbox( array(
                    'id'                => '_pwgc_cumulative_bonus',
                    'value'             => $cumulative_bonus ? 'yes' : 'no',
                    'cbvalue'           => 'yes',
                    'label'             => __( 'Cumulative bonus', 'pw-woocommerce-gift-cards' ),
                    'description'       => __( 'When checked, customer can purchase multiple gift cards to trigger the bonus. Otherwise, exact amount must be purchased to trigger bonus.', 'pw-woocommerce-gift-cards' ),
                ) );
            ?>
            <?php
                woocommerce_wp_select( array(
                    'id'                => '_pwgc_bonus_recipient',
                    'value'             => $bonus_recipient,
                    'label'             => __( 'Who will receive the bonus gift card?', 'pw-woocommerce-gift-cards' ),
                    'options'           => $bonus_recipient_options,
                ) );
            ?>
        </div>
    </div>
    <?php
        if ( defined( 'PWWA_VERSION' ) ) {
            ?>
            <div class="options_group show_if_<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>">
                <?php
                    woocommerce_wp_text_input(
                        array(
                            'id'          => '_pw_affiliate_commission',
                            'name'        => 'pw_affiliate_commission',
                            'value'       => $product_object->get_meta( '_pw_affiliate_commission' ),
                            'label'       => __( 'Commission (%)', 'pw-woocommerce-affiliates' ),
                            'placeholder' => number_format( pwwa_get_product_commission( $product_object, '', true ), 4 ) . '%',
                            'desc_tip'    => 'true',
                            'description' => __( 'Set the commission for this product.', 'pw-woocommerce-affiliates' ),
                        )
                    );
                ?>
            </div>
            <?php
        }
    ?>
</div>
<?php
