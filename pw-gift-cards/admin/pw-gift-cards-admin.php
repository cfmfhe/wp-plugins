<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'PW_Gift_Cards_Admin' ) ) :

final class PW_Gift_Cards_Admin {

    public $settings;

    function __construct() {
        global $pw_gift_cards;

        $this->settings = array(
            array(
                'title' => __( 'PW Gift Cards', 'pw-woocommerce-gift-cards' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'pw_gift_cards_options',
            ),
            array(
                'title'   => __( 'Allow Scheduled Delivery', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'Provides a "Delivery Date" for the purchasing customer to specify that the gift card should be delivered on a certain date. Otherwise gift cards are delivered as soon as the order is marked Complete. Default: Checked.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_allow_scheduled_delivery',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Auto Complete Orders', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'When an order only contains gift cards, automatically mark the order as Complete to send the gift cards immediately.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_autocomplete_gift_card_orders',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Send When Order Received', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'By default we wait until the order status is Complete before generating and emailing the gift card. Check this box to send the gift card immediately when the order is received. Scheduled gift cards will still be sent on the scheduled date. Default: Unchecked', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_send_when_processing',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Disable Expiration Date', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'If the law in your area prohibits an expiration date for gift cards, you can disable Expiration Dates system wide. This will hide all buttons, options, and mention of an expiration date. Default: Unchecked.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_no_expiration_date',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Buy Gift Cards with Gift Cards', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'Allow customers to purchase gift cards using another gift card. Disable this to prevent customers from extending the date on expiring gift cards. Default: Checked.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_allow_gift_card_purchasing',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Check Balance on My Account', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'When enabled, a link to the Check Balance page will be added to the My Account page. Default: checked', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_check_balance_show_on_my_account',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Allow Reloading', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'When enabled, customers can add funds to an existing gift card from the Check Balance page.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_allow_reloading',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Allow Manual Debit', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'When enabled, customers or store employees can debit funds from an existing gift card from the Check Balance page. Useful for physical stores where customers will redeem the card for in store purchases.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_check_balance_allow_manual_debit',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Show Activity on Check Balance Page', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'When enabled, detailed transaction history will be shown on the Check Balance page.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_check_balance_show_transactions',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Use the WooCommerce Transactional Email System', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'Enabled by default. If you are not receiving your gift card emails, try disabling this setting.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_use_wc_transactional_emails',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Apply Fix For Missing Fields', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'If you do not see the To / From / Message fields on your gift card product page, try checking this box and reloading. Some themes have out of date WooCommerce template files and need to be patched to work with the Gift Card product.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_before_add_to_cart_quantity_theme_fix',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Format Prices', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'For fixed gift card amounts, format the price with the system currency symbol. This is enabled by default. If you are having trouble with currency switchers, disable this setting. Note: You must remove and re-add your fixed gift card amounts if you change this setting.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_format_prices',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'     => __( 'Pre-populate the "From" Field', 'pw-woocommerce-gift-cards' ),
                'desc'      => __( 'If the customer is logged in, populate the From field with the Display Name value for their account. Default: Checked.', 'pw-woocommerce-gift-cards' ),
                'id'        => 'pwgc_current_user_populates_from_field',
                'default'   => 'yes',
                'type'      => 'checkbox',
            ),
            array(
                'title'     => __( 'Allow Multiple Recipients', 'pw-woocommerce-gift-cards' ),
                'desc'      => __( 'Similar to how Amazon works, allow multiple recipient email addresses when purchasing a gift card. Default: Checked.', 'pw-woocommerce-gift-cards' ),
                'id'        => 'pwgc_allow_multiple_recipients',
                'default'   => 'yes',
                'type'      => 'checkbox',
            ),
            array(
                'title'     => __( 'Carbon Copy', 'pw-woocommerce-gift-cards' ),
                'desc'      => __( 'When a gift card is emailed, carbon copy these addresses. Separate multiple emails with a comma.', 'pw-woocommerce-gift-cards' ),
                'id'        => 'pwgc_cc_email',
                'default'   => '',
                'type'      => 'text',
            ),
            array(
                'title'     => __( 'Blind Carbon Copy', 'pw-woocommerce-gift-cards' ),
                'desc'      => __( 'When a gift card is emailed, blind carbon copy these addresses. Separate multiple emails with a comma.', 'pw-woocommerce-gift-cards' ),
                'id'        => 'pwgc_bcc_email',
                'default'   => '',
                'type'      => 'text',
            ),
            array(
                'title'     => __( 'Send to the Buyer', 'pw-woocommerce-gift-cards' ),
                'desc'      => __( 'When a gift card is emailed, blind carbon copy the gift card to the purchasing customer.', 'pw-woocommerce-gift-cards' ),
                'id'        => 'pwgc_bcc_buyer',
                'default'   => 'no',
                'type'      => 'checkbox',
            ),
            array(
                'title'    => __( 'Redeem Location - Cart', 'pw-woocommerce-gift-cards' ),
                'desc'     => __( 'Specifies where the "Apply Gift Card" box appears on the Cart page.', 'pw-woocommerce-gift-cards' ),
                'id'       => 'pwgc_redeem_cart_location',
                'default'  => 'proceed_to_checkout',
                'type'     => 'select',
                'class'    => 'wc-enhanced-select',
                'css'      => 'min-width: 350px;',
                'desc_tip' => false,
                'options'  => array(
                    'proceed_to_checkout' => __( 'Above the "Proceed to Checkout" button.', 'pw-woocommerce-gift-cards' ),
                    'after_cart_contents' => __( 'Below the "Apply Coupon" area.', 'pw-woocommerce-gift-cards' ),
                    'none' => __( 'Do not display gift card field.', 'pw-woocommerce-gift-cards' ),
                ),
            ),
            array(
                'title'    => __( 'Redeem Location - Checkout', 'pw-woocommerce-gift-cards' ),
                'desc'     => __( 'Specifies where the "Apply Gift Card" box appears on the Checkout page.', 'pw-woocommerce-gift-cards' ),
                'id'       => 'pwgc_redeem_checkout_location',
                'default'  => 'review_order_before_submit',
                'type'     => 'select',
                'class'    => 'wc-enhanced-select',
                'css'      => 'min-width: 350px;',
                'desc_tip' => false,
                'options'  => array(
                    'review_order_before_submit' => __( 'Below the "Payment Methods" area.', 'pw-woocommerce-gift-cards' ),
                    'before_checkout_form' => __( 'Below the "Apply Coupon" area.', 'pw-woocommerce-gift-cards' ),
                    'none' => __( 'Do not display gift card field.', 'pw-woocommerce-gift-cards' ),
                ),
            ),
            array(
                'title'   => __( 'Coupon Code Field Input', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'Allow gift card numbers to be entered into the Coupon Code field to be redeemed. Default: checked.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_redeem_coupon_code',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Remove card after checkout', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'After an order is placed the gift card will remain in the session for use with future purchases. Check this box to automatically remove the card from the session instead. Default: Unchecked', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_remove_card_from_cart_after_checkout',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Use Font Awesome Icon', 'pw-woocommerce-gift-cards' ),
                'desc'    => sprintf( __( 'On the customer facing "Check Balance" page, show a gift card icon from the %s library.', 'pw-woocommerce-gift-cards' ), '<a href="https://fontawesome.com/" target="_blank">Font Awesome</a>' ),
                'id'      => 'pwgc_use_fontawesome',
                'default' => 'yes',
                'type'    => 'checkbox',
            ),
            array(
                'title'       => __( 'Default Redeem URL', 'pw-woocommerce-gift-cards' ),
                'desc'        => __( 'The link for the Redeem button on the gift card email. Include the https prefix, the Gift Card Number is automatically appended in the URL. Can be different per Design.', 'pw-woocommerce-gift-cards' ),
                'id'          => 'pwgc_default_redeem_url',
                'placeholder' => pwgc_shop_url(),
                'default'     => '',
                'type'        => 'text',
            ),
            array(
                'title'   => __( 'Show Balances by Date', 'pw-woocommerce-gift-cards' ),
                'desc'    => __( 'On the Balances page in the admin area, optionally show a date picker to show the balances as of a certain date.', 'pw-woocommerce-gift-cards' ),
                'id'      => 'pwgc_show_balances_by_date',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'type'  => 'sectionend',
                'id'    => 'pw_gift_cards_options',
            ),
        );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'woocommerce_product_data_tabs' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'woocommerce_product_data_panels' ) );
        add_action( 'admin_footer', array( $this, 'admin_footer' ) );
        add_filter( 'woocommerce_product_filters', array( $this, 'woocommerce_product_filters' ) );
        add_action( 'woocommerce_process_product_meta_' . PWGC_PRODUCT_TYPE_SLUG, array( $this, 'process_pw_gift_card_product_meta_data' ) );
        add_action( 'woocommerce_admin_order_totals_after_tax', array( $this, 'woocommerce_admin_order_totals_after_tax' ) );
        add_filter( 'woocommerce_get_sections_products', array( $this, 'woocommerce_get_sections_products' ) );
        add_filter( 'woocommerce_get_settings_products', array( $this, 'woocommerce_get_settings_products' ), 10, 2 );

        add_action( 'wp_ajax_pw-gift-cards-activation', array( $this, 'ajax_activation' ) );
        add_action( 'wp_ajax_pw-gift-cards-check-license', array( $this, 'ajax_check_license' ) );
        add_action( 'wp_ajax_pw-gift-cards-hide_renew_notice', array( $this, 'ajax_hide_renew_notice' ) );
        add_action( 'wp_ajax_pw-gift-cards-add_gift_card_amount', array( $this, 'ajax_add_gift_card_amount' ) );
        add_action( 'wp_ajax_pw-gift-cards-remove_gift_card_amount', array( $this, 'ajax_remove_gift_card_amount' ) );
        add_action( 'wp_ajax_pw-gift-cards-add_bonus_amount', array( $this, 'ajax_add_bonus_amount' ) );
        add_action( 'wp_ajax_pw-gift-cards-remove_bonus_amount', array( $this, 'ajax_remove_bonus_amount' ) );
        add_action( 'wp_ajax_pw-gift-cards-hide_partner_message', array( $this, 'ajax_hide_partner_message' ) );
        add_action( 'wp_ajax_pw-gift-cards-search', array( $this, 'ajax_search' ) );
        add_action( 'wp_ajax_pw-gift-cards-search_load_more', array( $this, 'ajax_search_load_more' ) );
        add_action( 'wp_ajax_pw-gift-cards-balance_summary', array( $this, 'ajax_balance_summary' ) );
        add_action( 'wp_ajax_pw-gift-cards-view_activity', array( $this, 'ajax_view_activity' ) );
        add_action( 'wp_ajax_pw-gift-cards-create_gift_card', array( $this, 'ajax_create_gift_card' ) );
        add_action( 'wp_ajax_pw-gift-cards-import_gift_cards', array( $this, 'ajax_import_gift_cards' ) );
        add_action( 'wp_ajax_pw-gift-cards-save_settings', array( $this, 'ajax_save_settings' ) );
        add_action( 'wp_ajax_pw-gift-cards-create_product', array( $this, 'ajax_create_product' ) );
        add_action( 'wp_ajax_pw-gift-cards-create_balance_page', array( $this, 'ajax_create_balance_page' ) );
        add_action( 'wp_ajax_pw-gift-cards-adjustment', array( $this, 'ajax_adjustment' ) );
        add_action( 'wp_ajax_pw-gift-cards-set_expiration_date', array( $this, 'ajax_set_expiration_date' ) );
        add_action( 'wp_ajax_pw-gift-cards-email_gift_card', array( $this, 'ajax_email_gift_card' ) );
        add_action( 'wp_ajax_pw-gift-cards-delete', array( $this, 'ajax_delete' ) );
        add_action( 'wp_ajax_pw-gift-cards-restore', array( $this, 'ajax_restore' ) );
        add_action( 'wp_ajax_pw-gift-cards-select_design', array( $this, 'ajax_select_design' ) );
        add_action( 'wp_ajax_pw-gift-cards-create_design', array( $this, 'ajax_create_design' ) );
        add_action( 'wp_ajax_pw-gift-cards-save_design', array( $this, 'ajax_save_design' ) );
        add_action( 'wp_ajax_pw-gift-cards-delete_design', array( $this, 'ajax_delete_design' ) );
        add_action( 'wp_ajax_pw-gift-cards-preview_email', array( $this, 'ajax_preview_email' ) );

        // Show an alert on the backend if we don't have the minimum required version.
        add_action( 'wp_ajax_pw-gift-cards-hide_paypal_ipn_pdt_bug_notice', array( $this, 'ajax_hide_paypal_ipn_pdt_bug_notice' ) );
        if ( ! get_option( 'pwgc_hide_paypal_ipn_pdt_bug_notice', false ) && pwgc_paypal_ipn_pdt_bug_exists() ) {
            add_action( 'admin_notices', array( $this, 'paypal_ipn_pdt_bug_exists' ) );
            return;
        }
    }

    function paypal_ipn_pdt_bug_exists() {
        ?>
        <div id="pwgc-paypal-ipn-pdt-bug-notice" class="error notice" style="padding: 14px;">
            <strong><?php _e( 'Configuration error detected by', 'pw-woocommerce-gift-cards' ); ?> <?php echo PWGC_PRODUCT_NAME; ?></strong>
            <p><?php _e( 'Your PayPal payment gateway is incorrectly configured. You should not have both IPN and PDT enabled. The "IPN Email Notifications" setting is enabled and there is also a "PayPal Identity Token" (PDT) value set. PayPal is reaching back to your site twice to confirm the payment. This causes things to happen twice including email notifications, stock reduction, and gift card generation. Go to WooCommerce -> Settings -> Payments -> PayPal and either clear the value from the "PayPal Identity Token" field or disable the "IPN Email Notifications" setting.', 'pw-woocommerce-gift-cards' ); ?></p>
            <button id="pwgc-paypal-ipn-pdt-bug-notice-dismiss" class="button"><?php _e( 'Dismiss this notice', 'pw-woocommerce-gift-cards' ); ?></button>
        </div>
        <script>
            jQuery('#pwgc-paypal-ipn-pdt-bug-notice-dismiss').on('click', function(e) {
                jQuery(this).attr( 'disabled', true);
                jQuery.post(ajaxurl, {'action': 'pw-gift-cards-hide_paypal_ipn_pdt_bug_notice'}, function( result ) {
                    jQuery('#pwgc-paypal-ipn-pdt-bug-notice').hide();
                });

                e.preventDefault();
                return false;
            });
        </script>
        <?php
    }

    function ajax_hide_paypal_ipn_pdt_bug_notice() {
        update_option( 'pwgc_hide_paypal_ipn_pdt_bug_notice', true );
        wp_send_json_success();
    }

    function admin_menu() {
        global $pw_gift_cards;

        if ( empty ( $GLOBALS['admin_page_hooks']['pimwick'] ) ) {
            add_menu_page(
                __( 'PW Gift Cards', 'pw-woocommerce-gift-cards' ),
                __( 'Pimwick Plugins', 'pw-woocommerce-gift-cards' ),
                PWGC_REQUIRES_PRIVILEGE,
                'pimwick',
                array( $this, 'index' ),
                $pw_gift_cards->relative_url( '/admin/assets/images/pimwick-icon-120x120.png' ),
                6
            );

            add_submenu_page(
                'pimwick',
                __( 'PW Gift Cards', 'pw-woocommerce-gift-cards' ),
                __( 'Pimwick Plugins', 'pw-woocommerce-gift-cards' ),
                PWGC_REQUIRES_PRIVILEGE,
                'pimwick',
                array( $this, 'index' )
            );

            remove_submenu_page( 'pimwick', 'pimwick' );
        }

        add_submenu_page(
            'pimwick',
            __( 'PW Gift Cards', 'pw-woocommerce-gift-cards' ),
            __( 'PW Gift Cards', 'pw-woocommerce-gift-cards' ),
            PWGC_REQUIRES_PRIVILEGE,
            'pw-gift-cards',
            array( $this, 'index' )
        );

        remove_submenu_page( 'pimwick', 'pimwick-plugins' );
        add_submenu_page(
            'pimwick',
            __( 'Pimwick Plugins', 'pw-woocommerce-gift-cards' ),
            __( 'Our Plugins', 'pw-woocommerce-gift-cards' ),
            PWGC_REQUIRES_PRIVILEGE,
            'pimwick-plugins',
            array( $this, 'other_plugins_page' )
        );

        add_submenu_page(
            'woocommerce',
            __( 'PW Gift Cards', 'pw-woocommerce-gift-cards' ),
            __( 'PW Gift Cards', 'pw-woocommerce-gift-cards' ),
            PWGC_REQUIRES_PRIVILEGE,
            'wc-pw-gift-cards',
            array( $this, 'index' )
        );
    }

    function other_plugins_page() {
        global $pimwick_more_handled;

        if ( !$pimwick_more_handled ) {
            $pimwick_more_handled = true;
            require( 'ui/more.php' );
        }
    }

    function index() {
        global $pw_gift_cards;

        if ( isset( $_POST['activate-license'] ) ) {
            $pw_gift_cards->license->activate_license( wc_clean( $_POST['license-key'] ) );
        }

        require( 'ui/index.php' );
    }

    function admin_enqueue_scripts( $hook ) {
        global $wp_scripts;
        global $pw_gift_cards;

        wp_register_style( 'pw-gift-cards-icon', $pw_gift_cards->relative_url( '/admin/assets/css/icon-style.css' ), array( 'admin-menu' ), PWGC_VERSION );
        wp_enqueue_style( 'pw-gift-cards-icon' );

        if ( !empty( $hook ) && substr( $hook, -strlen( 'pw-gift-cards' ) ) === 'pw-gift-cards' ) {
            wp_enqueue_style( 'pw-gift-cards-admin', $pw_gift_cards->relative_url( '/admin/assets/css/pw-gift-cards-admin.css' ), array(), PWGC_VERSION );
            wp_enqueue_style( 'pw-gift-cards-spectrum', $pw_gift_cards->relative_url( '/admin/assets/css/nano.min.css' ), array(), PWGC_VERSION );
            wp_enqueue_script( 'pw-gift-cards-spectrum', $pw_gift_cards->relative_url( '/admin/assets/js/pickr.min.js' ), array( 'jquery' ), PWGC_VERSION );

            wp_enqueue_style( 'jquery-ui-style', $pw_gift_cards->relative_url( '/admin/assets/css/jquery-ui-style.min.css' ), array(), PWGC_VERSION );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'wc-admin-meta-boxes' );
            wp_enqueue_style( 'woocommerce_admin_styles' );

            wp_enqueue_script( 'pw-gift-cards-admin', $pw_gift_cards->relative_url( '/admin/assets/js/pw-gift-cards-admin.js' ), array( 'jquery' ), PWGC_VERSION );
            wp_localize_script( 'pw-gift-cards-admin', 'pwgc', array(
                'admin_email' => get_option( 'admin_email' ),
                'i18n' => array(
                    'adjustment_amount_prompt' => __( 'Adjustment amount? Can be positive or negative.', 'pw-woocommerce-gift-cards' ),
                    'adjustment_note_prompt' => __( 'Note', 'pw-woocommerce-gift-cards' ),
                    'prompt_for_expiration_date' => __( 'Expiration Date (YYYY-MM-DD)', 'pw-woocommerce-gift-cards' ),
                    'prompt_for_email_address' => __( 'Recipient Email Address', 'pw-woocommerce-gift-cards' ),
                    'prompt_for_sender' => __( 'From', 'pw-woocommerce-gift-cards' ),
                    'prompt_for_note' => __( 'Note (optional)', 'pw-woocommerce-gift-cards' ),
                    'email_sent' => __( 'Email has been sent.', 'pw-woocommerce-gift-cards' ),
                    'delete_design_prompt' => __( 'Are you sure you want to delete this design?', 'pw-woocommerce-gift-cards' ),
                    'preview_email_notice' => __( 'Note: Be sure to save changes before sending a preview email.', 'pw-woocommerce-gift-cards' ),
                    'preview_email_prompt' => __( 'Recipient email address?', 'pw-woocommerce-gift-cards' ),
                    'gift_card_deleted_message' => __( 'The gift card was permanently deleted from the database.', 'pw-woocommerce-gift-cards' ),
                ),
                'nonces' => array(
                    'balance_summary' => wp_create_nonce( 'pw-gift-cards-balance-summary' ),
                    'search' => wp_create_nonce( 'pw-gift-cards-search' ),
                    'view_activity' => wp_create_nonce( 'pw-gift-cards-view-activity' ),
                    'create_gift_card' => wp_create_nonce( 'pw-gift-cards-create-gift-card' ),
                    'save_settings' => wp_create_nonce( 'pw-gift-cards-save-settings' ),
                    'create_product' => wp_create_nonce( 'pw-gift-cards-create-product' ),
                    'create_balance_page' => wp_create_nonce( 'pw-gift-cards-create-balance-page' ),
                    'adjustment' => wp_create_nonce( 'pw-gift-cards-adjustment' ),
                    'expiration_date' => wp_create_nonce( 'pw-gift-cards-expiration-date' ),
                    'email_gift_card' => wp_create_nonce( 'pw-gift-cards-email-gift-card' ),
                    'delete' => wp_create_nonce( 'pw-gift-cards-delete' ),
                    'restore' => wp_create_nonce( 'pw-gift-cards-restore' ),
                    'select_design' => wp_create_nonce( 'pw-gift-cards-select-design' ),
                    'create_design' => wp_create_nonce( 'pw-gift-cards-create-design' ),
                    'save_design' => wp_create_nonce( 'pw-gift-cards-save-design' ),
                    'delete_design' => wp_create_nonce( 'pw-gift-cards-delete-design' ),
                    'preview_email' => wp_create_nonce( 'pw-gift-cards-preview-email' ),
                )
            ) );

            wp_enqueue_script( 'fontawesome-all', $pw_gift_cards->relative_url( '/assets/js/fontawesome-all.min.js' ), array(), PWGC_FONT_AWESOME_VERSION );

            if ( class_exists( 'Better_Font_Awesome_Library' ) ) {
                $bfa = Better_Font_Awesome_Library::get_instance();
                if ( !empty( $bfa ) ) {
                    remove_action( 'admin_enqueue_scripts', array( $bfa, 'register_font_awesome_css' ), 15 );
                }
            }
        }

        if ( $screen = get_current_screen() ) {
            if ( $screen->id == 'product' ) {
                wp_enqueue_style( 'pw-gift-cards-product-data-panels', $pw_gift_cards->relative_url( '/admin/assets/css/product-data-panels.css' ), array(), PWGC_VERSION );

                wp_enqueue_script( 'pw-gift-cards-product-data-panels', $pw_gift_cards->relative_url( '/admin/assets/js/product-data-panels.js' ), array(), PWGC_VERSION );

                wp_localize_script( 'pw-gift-cards-product-data-panels', 'pwgc', array(
                    'i18n' => array(
                        'wait'                      => __( 'Wait', 'pw-woocommerce-gift-cards' ),
                        'add'                       => __( 'Add', 'pw-woocommerce-gift-cards' ),
                        'remove'                    => __( 'Remove', 'pw-woocommerce-gift-cards' ),
                        'error_greater_than_zero'   => __( 'Amount must be greater than zero.', 'pw-woocommerce-gift-cards' ),
                        'error_greater_than_min'    => __( 'Amount must be greater than minimum amount.', 'pw-woocommerce-gift-cards' ),
                        'error_less_than_max'       => __( 'Amount must be less than maximum amount.', 'pw-woocommerce-gift-cards' ),
                        'error'                     => __( 'Error', 'pw-woocommerce-gift-cards' ),
                        'add_bonus_amount'          => __( 'Add a bonus amount', 'pw-woocommerce-gift-cards' ),
                        'bonus_spend_prompt'        => __( 'Gift card amount that triggers the bonus?', 'pw-woocommerce-gift-cards' ),
                        'bonus_get_prompt'          => __( 'Bonus gift card amount?', 'pw-woocommerce-gift-cards' ),
                    ),
                    'nonces' => array(
                        'add_gift_card_amount'      => wp_create_nonce( 'pw-gift-cards-add-gift-card-amount' ),
                        'remove_gift_card_amount'   => wp_create_nonce( 'pw-gift-cards-remove-gift-card-amount' ),
                        'add_bonus_amount'          => wp_create_nonce( 'pw-gift-cards-add-bonus-amount' ),
                        'remove_bonus_amount'       => wp_create_nonce( 'pw-gift-cards-remove-bonus-amount' ),
                        'view_activity'             => wp_create_nonce( 'pw-gift-cards-view-activity' ),
                    )
                ) );
            }
        }
    }

    function woocommerce_product_data_tabs( $tabs ) {
        global $post;

        $is_physical_card = false;
        if ( is_a( $post, 'WP_Post' ) && !empty( $post->ID ) ) {
            $product = wc_get_product( $post->ID );
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                $is_physical_card = $product->get_pwgc_is_physical_card( 'edit' );
            }
        }

        if ( !$is_physical_card ) {
            $tabs['shipping']['class'][] = 'hide_if_' . PWGC_PRODUCT_TYPE_SLUG;
        }

        $tabs['inventory']['class'][] = 'show_if_' . PWGC_PRODUCT_TYPE_SLUG;
        $tabs['variations']['class'][] = 'show_if_' . PWGC_PRODUCT_TYPE_SLUG;

        $tabs[ PWGC_PRODUCT_TYPE_SLUG . '_cards' ] = array(
            'label'     => __( 'Gift Card', 'pw-woocommerce-gift-cards' ),
            'target'    => PWGC_PRODUCT_TYPE_SLUG . '_cards',
            'class'     => array( 'show_if_' . PWGC_PRODUCT_TYPE_SLUG ),
            'priority'  => 5
        );

        return $tabs;
    }

    function woocommerce_product_data_panels() {
        require( 'ui/product-data-panel-cards.php' );
    }

    function admin_footer() {
        if ( 'product' != get_post_type() ) {
            return;
        }

        ?>
        <script type='text/javascript'>
            jQuery('.inventory_options').addClass('show_if_<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>');
            jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>');
            jQuery('#inventory_product_data ._sold_individually_field').addClass('show_if_<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>');
            jQuery('#general_product_data ._tax_status_field').closest('.options_group').addClass('show_if_<?php echo PWGC_PRODUCT_TYPE_SLUG; ?>');
        </script>
        <?php
    }

    function woocommerce_product_filters( $output ) {
        return str_replace( 'Pw-gift-card</option>', PWGC_PRODUCT_TYPE_NAME . '</option>', $output );
    }

    function process_pw_gift_card_product_meta_data( $post_id ) {
        global $pw_gift_cards;

        $product = new WC_Product_PW_Gift_Card( $post_id );

        $new_amount = wc_clean( $_POST['pwgc_new_amount'] );
        if ( !empty( $new_amount ) ) {
            $result = $product->add_amount( $new_amount );
            if ( !is_numeric( $result ) ) {
                wp_die( $result );
            }
        }

        $errors = $product->set_props( array(
            'pwgc_show_recipient_name'      => isset( $_POST['_pwgc_show_recipient_name'] ),
            'pwgc_custom_amount_allowed'    => isset( $_POST['_pwgc_custom_amount_allowed'] ),
            'pwgc_custom_amount_min'        => $pw_gift_cards->numeric_price( wc_clean( $_POST['_pwgc_custom_amount_min'] ) ),
            'pwgc_custom_amount_max'        => $pw_gift_cards->numeric_price( wc_clean( $_POST['_pwgc_custom_amount_max'] ) ),
            'pwgc_is_physical_card'         => isset( $_POST['_pwgc_is_physical_card'] ),
            'pwgc_no_coupons_allowed'       => isset( $_POST['_pwgc_no_coupons_allowed'] ),
            'pwgc_physical_email'           => wc_clean( $_POST['_pwgc_physical_email'] ),
            'pwgc_expire_days'              => absint( $_POST['_pwgc_expire_days'] ),
            'pwgc_design_id'                => absint( $_POST['_pwgc_design_id'] ),
            'pwgc_enable_bonus'             => isset( $_POST['_pwgc_enable_bonus'] ),
            'pwgc_cumulative_bonus'         => isset( $_POST['_pwgc_cumulative_bonus'] ),
            'pwgc_bonus_recipient'          => wc_clean( $_POST['_pwgc_bonus_recipient'] ),
        ) );

        if ( is_wp_error( $errors ) ) {
            WC_Admin_Meta_Boxes::add_error( $errors->get_error_message() );
        }

        $product->set_default_attributes( array( PWGC_DENOMINATION_ATTRIBUTE_SLUG => wc_clean( $_POST['_pwgc_default_amount'] ) ) );

        $product->save();
    }

    function woocommerce_admin_order_totals_after_tax( $order_id ) {
        $order = wc_get_order( $order_id );
        require( 'ui/order-gift-card-total.php' );
    }

    function woocommerce_get_sections_products( $sections ) {
        $sections['pw_gift_cards'] = __( 'PW Gift Cards', 'pw-woocommerce-gift-cards' );

        return $sections;
    }

    function woocommerce_get_settings_products( $settings, $current_section ) {
        if ( 'pw_gift_cards' === $current_section ) {
            $settings = $this->settings;
        }

        return $settings;
    }

    function ajax_activation() {
        global $pw_gift_cards;

        $license_key = sanitize_title( $_POST['license-key'] );
        $pw_gift_cards->license->activate_license( $license_key );

        // FAILING?
        $registration['active'] = $pw_gift_cards->license->is_premium();
        $registration['error'] = $pw_gift_cards->license->error;

        wp_send_json( $registration );
    }

    function ajax_check_license() {
        global $pw_gift_cards;

        $registration['active'] = $pw_gift_cards->license->is_premium();
        $registration['error'] = $pw_gift_cards->license->error;

        wp_send_json( $registration );
    }

    function ajax_hide_renew_notice() {
        update_option( 'pw-gift-cards-hide-renew-notice', true );
        wp_send_json_success();
    }

    function ajax_add_gift_card_amount() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-add-gift-card-amount', 'security' );

        $pw_gift_cards->set_current_currency_to_default();

        if ( ! current_user_can( 'edit_products' ) ) {
            wp_die( -1 );
        }

        $product_id = absint( $_POST['product_id'] );
        $new_amount = wc_clean( $_POST['amount'] );
        $new_amount = $pw_gift_cards->sanitize_amount( $new_amount );

        if ( $product = new WC_Product_PW_Gift_Card( $product_id ) ) {
            $result = $product->add_amount( $new_amount );

            if ( is_numeric( $result ) ) {
                wp_send_json_success( array( 'amount' => $pw_gift_cards->pretty_price( $new_amount ), 'variation_id' => $result ) );
            } else {
                wp_send_json_error( array( 'message' => $result ) );
            }
        } else {
            wp_send_json_error( array( 'message' => sprintf( __( 'Could not locate product id %s', 'pw-woocommerce-gift-cards' ), $product_id ) ) );
        }
    }

    function ajax_remove_gift_card_amount() {
        check_ajax_referer( 'pw-gift-cards-remove-gift-card-amount', 'security' );

        if ( ! current_user_can( 'edit_products' ) ) {
            wp_die( -1 );
        }

        $product_id = absint( $_POST['product_id'] );
        $variation_id = absint( $_POST['variation_id'] );

        if ( $product = new WC_Product_PW_Gift_Card( $product_id ) ) {

            $result = $product->delete_amount( $variation_id );
            if ( $result === true ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( array( 'message' => $result ) );
            }

        } else {
            wp_send_json_error( array( 'message' => __( 'Could not locate product using product_id ', 'pw-woocommerce-gift-cards' ) . $product_id ) );
        }
    }

    function ajax_add_bonus_amount() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-add-bonus-amount', 'security' );

        $pw_gift_cards->set_current_currency_to_default();

        if ( ! current_user_can( 'edit_products' ) ) {
            wp_die( -1 );
        }

        $product_id = absint( $_POST['product_id'] );
        $spend = $pw_gift_cards->numeric_price( wc_clean( $_POST['spend'] ) );
        $get = $pw_gift_cards->numeric_price( wc_clean( $_POST['get'] ) );

        if ( $spend <= 0 || $get <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'Gift Card Amount and Bonus amount are both required.', 'pw-woocommerce-gift-cards' ) ) );
        }

        if ( $product = new WC_Product_PW_Gift_Card( $product_id ) ) {
            $bonus_structure = $product->get_pwgc_bonus_structure();

            if ( empty( $bonus_structure ) ) { $bonus_structure = array(); }

            $bonus_structure[ $spend ] = $get;

            $product->set_pwgc_bonus_structure( $bonus_structure );
            $product->save();

            wp_send_json_success( array( 'message' => sprintf( __( 'Buy a %s gift card, get a %s bonus card', 'pw-woocommerce-gift-cards' ), $pw_gift_cards->pretty_price( $spend ), $pw_gift_cards->pretty_price( $get ) ) ) );
        } else {
            wp_send_json_error( array( 'message' => sprintf( __( 'Could not locate product id %s', 'pw-woocommerce-gift-cards' ), $product_id ) ) );
        }
    }

    function ajax_remove_bonus_amount() {
        check_ajax_referer( 'pw-gift-cards-remove-bonus-amount', 'security' );

        if ( ! current_user_can( 'edit_products' ) ) {
            wp_die( -1 );
        }

        $product_id = absint( $_POST['product_id'] );
        $key = absint( $_POST['key'] );

        if ( $product = new WC_Product_PW_Gift_Card( $product_id ) ) {
            $bonus_structure = $product->get_pwgc_bonus_structure();
            $new_bonus_structure = array();

            if ( !empty( $bonus_structure ) ) {
                foreach ( $bonus_structure as $spend => $get ) {
                    if ( $key != $spend ) {
                        $new_bonus_structure[ $spend ] = $get;
                    }
                }
            }

            $product->set_pwgc_bonus_structure( $new_bonus_structure );
            $product->save();

            wp_send_json_success();

        } else {
            wp_send_json_error( array( 'message' => __( 'Could not locate product using product_id ', 'pw-woocommerce-gift-cards' ) . $product_id ) );
        }
    }

    function ajax_hide_partner_message() {
        update_option( 'pwgc_hide_partner_message', true );
        wp_send_json_success();
    }

    function ajax_search() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-search', 'security' );

        $gift_cards = $this->search_gift_cards( $_POST['search_terms'], 0 );

        $pw_gift_cards->set_current_currency_to_default();

        ob_start();
        require( 'ui/sections/search-results.php' );
        $html = ob_get_clean();

        wp_send_json( array( 'html' => $html ) );
    }

    function ajax_search_load_more() {
        global $pw_gift_cards;

        $gift_cards = $this->search_gift_cards( $_POST['search_terms'], $_POST['offset'] );

        $pw_gift_cards->set_current_currency_to_default();

        ob_start();
        require( 'ui/sections/search-results-rows.php' );
        $html = ob_get_clean();

        wp_send_json( array( 'html' => $html ) );
    }

    function search_gift_cards( $search_terms, $offset ) {
        global $wpdb;

        $gift_cards = array();
        $active_sql = '';
        if ( !empty( $search_terms ) ) {
            $search_terms = '%' . wc_clean( $search_terms ) . '%';
        } else {
            $search_terms = '%';
            $active_sql = 'AND gift_card.active = true';
        }

        $offset = absint( $offset );

        $date_sql_activity = '';
        $date_sql_where = '';
        if ( isset( $_REQUEST['date'] ) && !empty( $_REQUEST['date'] ) ) {
            $date_sql_activity = $wpdb->prepare( "AND a.activity_date <= %s", $_REQUEST['date'] . ' 23:59:59' );
            $date_sql_where = $wpdb->prepare( "AND gift_card.create_date <= %s", $_REQUEST['date'] . ' 23:59:59' );
        }

        $sql = $wpdb->prepare( "
            SELECT
                gift_card.*,
                (SELECT SUM(amount) FROM {$wpdb->pimwick_gift_card_activity} AS a WHERE a.pimwick_gift_card_id = gift_card.pimwick_gift_card_id $date_sql_activity) AS balance
            FROM
                `{$wpdb->pimwick_gift_card}` AS gift_card
            WHERE
                (gift_card.number LIKE %s OR gift_card.recipient_email LIKE %s)
                $active_sql
                $date_sql_where
            ORDER BY
                gift_card.create_date DESC,
                gift_card.pimwick_gift_card_id DESC
            LIMIT
                $offset, " . PWGC_ADMIN_MAX_ROWS . "
        ", $search_terms, $search_terms );

        $results = $wpdb->get_results( $sql );
        if ( $results !== null ) {
            foreach ( $results as $row ) {
                $gift_cards[] = new PW_Gift_Card( $row );
            }
        }

        return $gift_cards;
    }

    function ajax_balance_summary() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-balance-summary', 'security' );

        $pw_gift_cards->set_current_currency_to_default();

        require_once( 'ui/sections/balance-summary.php' );

        wp_die();
    }

    function ajax_view_activity() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-view-activity', 'security' );

        $pw_gift_cards->set_current_currency_to_default();

        $card_number = wc_clean( $_POST['card_number'] );

        $gift_card = new PW_Gift_Card( $card_number );
        if ( $gift_card->get_id() ) {
            ob_start();
            require( 'ui/sections/activity-records.php' );
            $html = ob_get_clean();

            wp_send_json( array( 'html' => $html ) );
        }

        wp_send_json( array( 'html' => '<div class="pwgc-balance-error">' . $gift_card->get_error_message() . '</div>' ) );
    }

    function ajax_create_gift_card() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-create-gift-card', 'security' );

        $amount = wc_clean( $_POST['amount'] );
        $quantity = absint( $_POST['quantity'] );
        $note = stripslashes( wc_clean( $_POST['note'] ) );
        $recipient = stripslashes( wc_clean( $_POST['recipient'] ) );
        $from = stripslashes( wc_clean( $_POST['from'] ) );

        $expiration_date = '';
        if ( !empty( $_POST['expiration_date'] ) ) {
            $date_array = date_parse( $_POST['expiration_date'] );
            if ( $date_array !== false ) {
                $expiration_date = date('Y-m-d H:i:s', mktime( $date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year'] ));
            }
        }

        $gift_cards = array();

        for ( $x = 0; $x < $quantity; $x++ ) {
            $gift_card = PW_Gift_Card::create_card( $note );

            if ( !empty( $amount ) && $amount > 0 ) {
                $gift_card->credit( $amount );
                $gift_card->get_balance( true );
            }

            if ( !empty( $expiration_date ) ) {
                $gift_card->set_expiration_date( $expiration_date );
            }

            $gift_cards[] = $gift_card;

            if ( !empty( $recipient ) ) {
                $pw_gift_cards->set_current_currency_to_default();
                $gift_card->set_recipient_email( $recipient );
                do_action( 'pw_gift_cards_send_email_manually', $gift_card->get_number(), $recipient, $from, '', $note, $amount );
            }
        }

        ob_start();

        ?>
        <h3><?php printf( __( 'Created %s gift cards.', 'pw-woocommerce-gift-cards' ), number_format( count( $gift_cards ) ) ); ?></h3>
        <?php

        $pw_gift_cards->set_current_currency_to_default();

        require( 'ui/sections/search-results.php' );
        $html = ob_get_clean();

        wp_send_json( array( 'html' => $html ) );
    }

    function ajax_import_gift_cards() {
        global $pw_gift_cards;

        if ( isset( $_FILES['file'] ) ) {
            $upload = $_FILES['file'];

            if ( $upload['error'] > 0 ) {
                wp_die( sprintf( __( 'Error: %s', 'pw-woocommerce-gift-cards' ), $_FILES['file']['error'] ) );
            }

            ini_set( 'auto_detect_line_endings', true );
            $csv_data = array_map( 'str_getcsv', file( $upload['tmp_name'] ) );
            $success_count = 0;
            $failure_count = 0;
            $create_note = __( 'Imported', 'pw-woocommerce-gift-cards' );

            $from = get_option( 'admin_email' );
            $message = '';

            $import_results = array();

            foreach ( $csv_data as $index => $columns ) {
                $result = true;
                $number = '';
                $balance = 0;
                $recipient = '';
                $expiration_date = '';
                $expiration_date_html = '';

                if ( count( $columns ) > 4 ) {
                    $result = __( 'Too many columns.', 'pw-woocommerce-gift-cards' );
                } else if ( count( $columns ) == 1 && empty( $columns[0] ) ) {
                    // Ignore blank lines.
                    continue;
                } else {
                    if ( count( $columns ) > 0 ) {
                        if ( !empty( trim( $columns[0] ) ) ) {
                            $number = $columns[0];

                            $gift_card = new PW_Gift_Card( $number );
                            if ( $gift_card->get_id() ) {
                                $result = __( 'Gift card number already exists.', 'pw-woocommerce-gift-cards' );
                            }

                        } else {
                            $result = sprintf( __( 'Invalid gift card number: %s', 'pw-woocommerce-gift-cards' ), substr( $columns[0], 0, 20 ) );
                        }
                    }

                    if ( count( $columns ) > 1 && $result === true ) {
                        if ( is_numeric( $columns[1] ) ) {
                            $balance = $columns[1];
                        } else {
                            $result = sprintf( __( 'Invalid balance amount: %s', 'pw-woocommerce-gift-cards' ), substr( $columns[1], 0, 20 ) );
                        }
                    }

                    if ( count( $columns ) > 2 && $result === true ) {
                        if ( !empty( $columns[2] ) ) {
                            $date_array = date_parse( $columns[2] );
                            if ( $date_array !== false ) {
                                $expiration_date = date('Y-m-d H:i:s', mktime( $date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year'] ));
                                $expiration_date_html = date_i18n( wc_date_format(), strtotime( $expiration_date ) );
                            } else {
                                $result = sprintf( __( 'Invalid expiration date: %s', 'pw-woocommerce-gift-cards' ), substr( $columns[2], 0, 20 ) );
                            }
                        }
                    }

                    if ( count( $columns ) > 3 && $result === true ) {
                        if ( !empty( $columns[3] ) ) {
                            $recipient = $columns[3];
                        }
                    }

                    if ( isset( $_POST['confirm'] ) && $result === true ) {
                        $gift_card = PW_Gift_Card::add_card( $number, $create_note );
                        if ( is_a( $gift_card, 'PW_Gift_Card' ) ) {
                            if ( $balance > 0 ) {
                                $gift_card->credit( $balance, $create_note );
                            }

                            if ( !empty( $expiration_date ) ) {
                                $gift_card->set_expiration_date( $expiration_date );
                            }

                            if ( !empty( $recipient ) ) {
                                $gift_card->set_recipient_email( $recipient );

                                if ( isset( $_POST['send_email'] ) ) {
                                    do_action( 'pw_gift_cards_send_email_manually', $gift_card->get_number(), $recipient, $from, '', $message, $balance );
                                }
                            }
                        } else {
                            $result = $gift_card;
                        }
                    }
                }

                $import_results[] = array(
                    'number' => $number,
                    'balance' => $balance,
                    'expiration_date' => !empty( $expiration_date_html ) ? $expiration_date_html : __( 'None', 'pw-woocommerce-gift-cards' ),
                    'recipient' => $recipient,
                    'result' => $result,
                );

                if ( $result === true ) {
                    $success_count++;
                } else {
                    $failure_count++;
                }
            }

            $pw_gift_cards->set_current_currency_to_default();

            ob_start();
            require( 'ui/sections/import-results.php' );
            $html = ob_get_clean();

            wp_send_json_success( array( 'html' => $html ) );
        }

        wp_send_json_error( array( 'message' => __( 'Malformed request.', 'pw-woocommerce-gift-cards' ) ) );
    }

    function ajax_save_settings() {
        check_ajax_referer( 'pw-gift-cards-save-settings', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        WC_Admin_Settings::save_fields( $this->settings, $form );

        $html = '<span style="color: blue;">' . __( 'Settings saved.', 'pw-woocommerce-gift-cards' ) . '</span>';

        wp_send_json_success( array( 'html' => $html ) );
    }

    function ajax_create_product() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-create-product', 'security' );

        $pw_gift_cards->set_current_currency_to_default();

        $gift_card_product = $pw_gift_cards->get_gift_card_product();
        if ( empty( $gift_card_product ) ) {
            $gift_card_product = new WC_Product_PW_Gift_Card();
            $gift_card_product->set_props( array(
                'name'                       => __( 'Gift Card', 'pw-woocommerce-gift-cards' ),
                'pwgc_custom_amount_allowed' => true,
                'pwgc_custom_amount_min'     => '5',
                'pwgc_custom_amount_max'     => '1000',
                'tax_status'                 => PWGC_PURCHASE_TAX_STATUS,
                'pwgc_is_physical_card'      => false,
                'pwgc_no_coupons_allowed'    => false,
                'pwgc_physical_email'        => '',
                'pwgc_expire_days'           => '',
                'pwgc_design_id'             => '0',
            ) );
            $gift_card_product->save();

            $gift_card_product->add_amount( '10' );
            $gift_card_product->add_amount( '25' );
            $gift_card_product->add_amount( '50' );
            $gift_card_product->add_amount( '100' );

            $this->attach_default_image( $gift_card_product->get_id() );
        }

        wp_send_json_success();
    }

    function attach_default_image( $product_id ) {

        // Get the uploads directory, we'll need it in a bit.
        $wp_upload_dir = wp_upload_dir();

        // Copy our generic gift card image from the plugin directory to the uploads directory.
        $source_file = trailingslashit( PWGC_PLUGIN_ROOT ) . 'assets/images/pw-gift-card.png';
        $upload_file = trailingslashit( $wp_upload_dir['path'] ) . basename( 'pw-gift-card.png' );

        if ( !file_exists( $upload_file ) ) {
            copy( $source_file, $upload_file );
        }

        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype( basename( $upload_file ), null );

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $upload_file ),
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $upload_file ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment( $attachment, $upload_file, $product_id );

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        set_post_thumbnail( $product_id, $attach_id );
    }

    function ajax_create_balance_page() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-create-balance-page', 'security' );

        $balance_page = $pw_gift_cards->get_balance_page();
        if ( empty( $balance_page ) ) {

            $slug = 'balance';

            $existing_page = get_page_by_path( $slug );
            if ( $existing_page ) {

                $slug = 'check-balance';
                $existing_page = get_page_by_path( $slug );
                if ( $existing_page ) {

                    $slug = 'check-pw-gift-card-balance';
                    $existing_page = get_page_by_path( $slug );
                    if ( $existing_page ) {
                        wp_send_json_error( array( 'message' => __( 'Unable to find suitable slug for check balance page.', 'pw-woocommerce-gift-cards' ) ) );
                    }
                }
            }

            $page_id = wp_insert_post(
                array(
                    'comment_status'    =>  'closed',
                    'ping_status'       =>  'closed',
                    'post_name'         =>  $slug,
                    'post_title'        =>  __( 'Gift Card Balance', 'pw-woocommerce-gift-cards' ),
                    'post_status'       =>  'publish',
                    'post_type'         =>  'page',
                    'post_content'      => '[' . PWGC_BALANCE_SHORTCODE . ']',
                )
            );
        }

        wp_send_json_success();
    }

    function ajax_adjustment() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-adjustment', 'security' );

        $number = wc_clean( $_POST['card_number'] );

        $pw_gift_cards->set_current_currency_to_default();

        $balance = 0;

        $gift_card = new PW_Gift_Card( $number );
        if ( $gift_card->get_id() ) {
            $amount = $pw_gift_cards->sanitize_amount( $_POST['amount'] );
            $amount = floatval( $amount );
            if ( isset( $_POST['note'] ) ) {
                $note = stripslashes( wc_clean( $_POST['note'] ) );
            } else {
                $note = '';
            }

            if ( !empty( $amount ) || !empty( $note ) ) {
                $gift_card->adjust_balance( $amount, $note );
            }

            $balance = wc_price( $gift_card->get_balance() );
        }

        if ( !empty( $amount ) ) {
            wp_send_json( array( 'message' => __( 'Balance adjusted.', 'pw-woocommerce-gift-cards' ), 'balance' => $balance ) );
        } else {
            wp_send_json( array( 'message' => __( 'Note added.', 'pw-woocommerce-gift-cards' ), 'balance' => $balance ) );
        }
    }

    function ajax_set_expiration_date() {
        check_ajax_referer( 'pw-gift-cards-expiration-date', 'security' );

        $number = wc_clean( $_POST['card_number'] );

        $gift_card = new PW_Gift_Card( $number );
        if ( $gift_card->get_id() ) {
            $new_expiration_date = wc_clean( $_POST['expiration_date'] );
            if ( empty( $new_expiration_date ) ) {
                $gift_card->set_expiration_date( null );
            } else {
                $date_array = date_parse( $new_expiration_date );
                if ( $date_array !== false ) {
                    $expiration_date = date('Y-m-d H:i:s', mktime( $date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year'] ));
                    $gift_card->set_expiration_date( $expiration_date );
                } else {
                    wp_send_json_error( array( 'message' => __( 'Unable to parse expiration date.', 'pw-woocommerce-gift-cards' ) ) );
                }
            }

        } else {
            wp_send_json_error( array( 'message' => __( 'Gift card not found.', 'pw-woocommerce-gift-cards' ) ) );
        }

        wp_send_json( array( 'expiration_date' => $gift_card->get_expiration_date_html() ) );
    }

    function ajax_email_gift_card() {
        check_ajax_referer( 'pw-gift-cards-email-gift-card', 'security' );

        $number = wc_clean( $_POST['card_number'] );
        $email_address = stripslashes( wc_clean( $_POST['email_address'] ) );
        $from = stripslashes( wc_clean( $_POST['from'] ) );
        $note = stripslashes( wc_clean( $_POST['note'] ) );

        if ( empty( $number ) || empty( $email_address ) ) {
            wp_send_json_error( array( 'message' => __( 'Gift Card Number and Email Address are both required.', 'pw-woocommerce-gift-cards' ) ) );
        }

        $gift_card = new PW_Gift_Card( $number );
        if ( $gift_card->get_id() ) {
            do_action( 'pw_gift_cards_send_email_manually', $gift_card->get_number(), $email_address, $from, '', $note, $gift_card->get_balance() );
        } else {
            wp_send_json_error( array( 'message' => __( 'Gift card not found.', 'pw-woocommerce-gift-cards' ) ) );
        }

        wp_send_json_success();
    }

    function ajax_delete() {
        check_ajax_referer( 'pw-gift-cards-delete', 'security' );

        $number = wc_clean( $_POST['card_number'] );

        $gift_card = new PW_Gift_Card( $number );
        if ( $gift_card->get_id() ) {
            $deleted = false;

            if ( $gift_card->get_active() ) {
                $gift_card->deactivate();
            } else {
                $gift_card->delete();
                $deleted = true;
            }

            wp_send_json_success( array( 'deleted' => $deleted ) );
        } else {
            wp_send_json_error( array( 'message' => _e( 'Gift card not found.', 'pw-woocommerce-gift-cards' ) ) );
        }
    }

    function ajax_restore() {
        check_ajax_referer( 'pw-gift-cards-restore', 'security' );

        $number = wc_clean( $_POST['card_number'] );

        $gift_card = new PW_Gift_Card( $number );
        if ( $gift_card->get_id() ) {
            $gift_card->reactivate();
            wp_send_json_success();
        } else {
            wp_send_json_error( array( 'message' => _e( 'Gift card not found.', 'pw-woocommerce-gift-cards' ) ) );
        }
    }

    function ajax_select_design() {
        check_ajax_referer( 'pw-gift-cards-select-design', 'security' );

        $design_id = absint( $_REQUEST['design_id'] );

        $designs = pwgc_get_designs();

        if ( isset( $designs[ $design_id ] ) ) {
            $design = $designs[ $design_id ];

            ob_start();
            require( 'ui/sections/designer-panel.php' );
            $html = ob_get_clean();

        } else {
            $html = sprintf( __( 'Error: Invalid Design ID %s.', 'pw-woocommerce-gift-cards' ), $design_id );
        }

        wp_send_json( array( 'html' => $html ) );
    }

    function ajax_create_design() {
        check_ajax_referer( 'pw-gift-cards-create-design', 'security' );

        $designs = pwgc_get_designs();

        $design_id = max( array_keys( $designs ) ) + 1;

        // Start with the default design for the new design.
        $designs[] = reset( $designs );

        // Change the name.
        $name = sprintf( __( 'Design %s', 'pw-woocommerce-gift-cards' ), ( $design_id + 1 ) );
        $designs[ $design_id ]['name'] = $name;

        // Save the designs.
        update_option( 'pw_gift_card_designs', $designs );

        wp_send_json( array( 'design_id' => $design_id, 'name' => $name ) );
    }

    function ajax_save_design() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-save-design', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $designs = pwgc_get_designs();

        $design_id = absint( $form['design_id'] );
        if ( isset( $designs[ $design_id ] ) ) {

            $default_design = reset( $pw_gift_cards->default_designs );
            foreach( $default_design as $key => $value ) {
                $designs[ $design_id ][ $key ] = isset( $form[ $key ] ) ? stripslashes( $form[ $key ] ) : '';
            }

            update_option( 'pw_gift_card_designs', $designs );

            $html = '<span style="color: blue;">' . __( 'Design saved.', 'pw-woocommerce-gift-cards' ) . '</span>';
        } else {
            $html = '<span style="color: red;">' . sprintf( __( 'Error: Invalid Design ID %s.', 'pw-woocommerce-gift-cards' ), $design_id ) . '</span>';
        }

        wp_send_json( array( 'html' => $html ) );
    }

    function ajax_delete_design() {
        global $pw_gift_cards;

        check_ajax_referer( 'pw-gift-cards-delete-design', 'security' );

        $designs = pwgc_get_designs();

        $design_id = absint( $_REQUEST['design_id'] );
        if ( isset( $designs[ $design_id ] ) ) {
            unset( $designs[ $design_id ] );

            // Make sure we always have designs;
            if ( 0 == count( $designs ) ) {
                $designs = $pw_gift_cards->default_designs;
            }

            update_option( 'pw_gift_card_designs', $designs );
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    function ajax_preview_email() {
        global $pw_gift_card_design_id;

        check_ajax_referer( 'pw-gift-cards-preview-email', 'security' );

        $designs = pwgc_get_designs();

        $design_id = absint( $_REQUEST['design_id'] );
        if ( isset( $designs[ $design_id ] ) ) {

            $pw_gift_card_design_id = $design_id;

            $gift_card_number = PW_Gift_Card::random_card_number();
            $recipient = wc_clean( $_REQUEST['email_address'] );
            $from = __( 'Preview email system', 'pw-woocommerce-gift-cards' );
            $recipient_name = __( 'Recipient Name', 'pw-woocommerce-gift-cards' );
            $message = __( 'Gift card message to the recipient from the sender.', 'pw-woocommerce-gift-cards' );
            $amount = '123.45';

            do_action( 'pw_gift_cards_send_email_manually', $gift_card_number, $recipient, $from, $recipient_name, $message, $amount );

            $html = '<span style="color: blue;">' . __( 'Email sent.', 'pw-woocommerce-gift-cards' ) . '</span>';
        } else {
            $html = '<span style="color: red;">' . sprintf( __( 'Error: Invalid Design ID %s.', 'pw-woocommerce-gift-cards' ), $design_id ) . '</span>';
        }

        wp_send_json( array( 'html' => $html ) );
    }
}

global $pw_gift_cards_admin;
$pw_gift_cards_admin = new PW_Gift_Cards_Admin();

endif;
