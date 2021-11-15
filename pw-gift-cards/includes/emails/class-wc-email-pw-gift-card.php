<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WC_Email_PW_Gift_Card' ) ) :

class WC_Email_PW_Gift_Card extends WC_Email {

    function __construct() {

        $this->id = 'pwgc_email';
        $this->customer_email = true;
        $this->title = __( 'PW Gift Card Email', 'pw-woocommerce-gift-cards' );
        $this->description = __( 'This email is sent to the recipients of a gift card.', 'pw-woocommerce-gift-cards' );

        $this->default_subject = __( 'You were given a gift card', 'pw-woocommerce-gift-cards' );
        $this->default_heading = __( '{sender} sent you a gift card', 'pw-woocommerce-gift-cards' );

        $this->template_html = 'emails/customer-pw-gift-card.php';
        $this->template_plain = 'emails/plain/customer-pw-gift-card.php';

        add_action( 'pw_gift_cards_pending_email_notification', array( $this, 'queue_order_notification' ) );
        add_action( 'pw_gift_cards_pending_manual_email_notification', array( $this, 'queue_manual_notification' ), 10, 6 );
        add_action( 'pw_gift_cards_recipient_email_notification', array( $this, 'trigger' ), 10, 7 );

        parent::__construct();

        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

        $this->template_base = PWGC_PLUGIN_ROOT . 'templates/woocommerce/';
    }

    public function queue_order_notification( $order_id ) {

        $has_scheduled_gift_cards = false;

        $order = new WC_Order( $order_id );
        foreach ( $order->get_items( 'line_item' ) as $order_item_id => $order_item ) {
            if ( ! is_a( $order_item->get_product(), 'WC_Product' ) ) {
                continue;
            }

            $delivery_date = $order_item->get_meta( PWGC_DELIVERY_DATE_META_KEY );
            if ( !empty( $delivery_date ) && (bool)strtotime( $delivery_date ) ) {
                $scheduled_date = strtotime( $delivery_date );
                $now = strtotime( 'today midnight', current_time( 'timestamp' ) );
                if ( $scheduled_date > $now ) {
                    $has_scheduled_gift_cards = true;
                    continue;
                }
            }

            $product_id = !empty( $order_item->get_product()->get_parent_id() ) ? $order_item->get_product()->get_parent_id() : $order_item->get_product()->get_id();
            $product =  wc_get_product( $product_id );
            if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {

                $notifications = (array) wc_get_order_item_meta( $order_item_id, PWGC_GIFT_CARD_NOTIFICATIONS_META_KEY );
                $gift_card_numbers = wc_get_order_item_meta( $order_item_id, PWGC_GIFT_CARD_NUMBER_META_KEY, false );
                $bonus_recipient = wc_get_order_item_meta( $order_item_id, PWGC_BONUS_RECIPIENT_META_KEY, true );
                $pwgc_to = wc_get_order_item_meta( $order_item_id, PWGC_TO_META_KEY );

                // If there isn't a "To" email address, default to the purchasing customer's email.
                if ( empty( $pwgc_to ) ) {
                    $pwgc_to = $order->get_billing_email();
                }

                if ( !empty( $pwgc_to ) ) {
                    $recipients = preg_split('/[\s,]+/', $pwgc_to, PWGC_RECIPIENT_LIMIT, PREG_SPLIT_NO_EMPTY);
                }

                if ( isset( $recipients ) && !empty( $recipients ) ) {
                    $recipient = $recipients[0];
                } else {
                    $recipient = get_option( 'admin_email' );
                }

                foreach ( $gift_card_numbers as $gift_card_number ) {
                    if ( !isset( $notifications[ $gift_card_number ] ) ) {

                        $amount = wc_get_order_item_meta( $order_item_id, PWGC_AMOUNT_META_KEY );

                        if ( $product->get_pwgc_is_physical_card() ) {
                            $recipient = $product->get_pwgc_physical_email();
                        } else {
                            // Bonus gift cards that should be sent to the purchasing customer need their email address.
                            $gift_card = new PW_Gift_Card( $gift_card_number );
                            if ( !empty( $gift_card->get_pimwick_gift_card_parent() ) && ( $bonus_recipient === 'purchasing_customer' || empty( $bonus_recipient ) ) ) {
                                $billing_email = $order->get_billing_email();
                                if ( ! empty( $billing_email ) ) {
                                    $recipient = $billing_email;
                                }

                            } else if ( isset( $recipients ) && !empty( $recipients ) ) {
                                // Get the next email address from the list, or continue sending to the last (or default) address.
                                $recipient = trim( array_shift( $recipients ) );
                            }

                            if ( !empty( $gift_card->get_pimwick_gift_card_parent() ) ) {
                                $amount = $gift_card->get_balance();
                            }
                        }

                        $notifications[ $gift_card_number ] = $recipient;
                        if ( 'yes' === get_option( 'pwgc_use_wc_transactional_emails', 'yes' ) ) {
                            wp_schedule_single_event( time(), 'pw_gift_cards_recipient_email', array( $order_item_id, $gift_card_number, $recipient, '', '', '', $amount ) );
                        } else {
                            $this->trigger( $order_item_id, $gift_card_number, $recipient, '', '', '', $amount );
                        }
                    }
                }

                wc_update_order_item_meta( $order_item_id, PWGC_GIFT_CARD_NOTIFICATIONS_META_KEY, $notifications );
            }
        }

        if ( false === $has_scheduled_gift_cards ) {
            delete_post_meta( $order_id, PWGC_DELIVERY_PENDING_META_KEY );
        }
    }

    public function queue_manual_notification( $gift_card_number, $recipient, $from, $recipient_name, $message, $amount ) {
        $this->trigger( 0, $gift_card_number, $recipient, $from, $recipient_name, $message, $amount );
    }

    function trigger( $order_item_id, $gift_card_number = '', $recipient = '', $from = '', $recipient_name = '', $message = '', $amount = '' ) {
        global $woocommerce_wpml;

        if ( !empty( $recipient ) ) {
            $this->recipient = $recipient;
        }

        if ( empty( $gift_card_number ) && defined( 'WOO_PREVIEW_EMAILS_DIR' ) ) {
            $order = wc_get_order( $order_item_id );
            foreach ( $order->get_items( 'line_item' ) as $order_item_id => $order_item ) {
                if ( is_a( $order_item->get_product(), 'WC_Product' ) ) {
                    $product_id = !empty( $order_item->get_product()->get_parent_id() ) ? $order_item->get_product()->get_parent_id() : $order_item->get_product()->get_id();
                    $product =  wc_get_product( $product_id );
                    if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                        $gift_card_number = wc_get_order_item_meta( $order_item_id, PWGC_GIFT_CARD_NUMBER_META_KEY );
                        if ( empty( $gift_card_number ) ) {
                            $gift_card_number = wc_get_order_item_meta( $order_item_id, PWGC_RELOAD_GIFT_CARD_NUMBER_META_KEY );
                        }
                        break;
                    }
                }
            }
        }

        if ( !empty( $gift_card_number ) ) {
            $gift_card = new PW_Gift_Card( $gift_card_number );
        }

        if ( isset( $gift_card ) && empty( $order_item_id ) ) {
            $order_item_id = $gift_card->get_original_order_item_id();
        }

        $this->object = $this->create_object( $order_item_id, $gift_card_number, $recipient, $from, $recipient_name, $message, $amount );

        $this->add_placeholder( '{gift_card_number}', $this->object->gift_card_number );

        if ( 'no' === get_option( 'pwgc_no_expiration_date', 'no' ) ) {
            $this->add_placeholder( '{expiration_date}', $this->object->expiration_date );
        }

        $this->add_placeholder( '{amount}', $this->object->amount );
        $this->add_placeholder( '{sender}', $this->object->from );
        $this->add_placeholder( '{recipient_name}', $this->object->recipient_name );
        $this->add_placeholder( '{message}', $this->object->message );

        if ( is_a( $this->object->product, 'WC_Product' ) ) {
            $this->add_placeholder( '{product_title}', $this->object->product->get_title() );
        }

        if ( ! $this->get_recipient() ) {
            return;
        }

        $attachments = $this->get_attachments();
        if ( empty( $attachments ) ) {
            $attachments = array();
        }

        $subject = $this->get_subject();

        $order_item = false;
        if ( !empty( $order_item_id ) ) {
            $order_item = WC_Order_Factory::get_order_item( $order_item_id );

            if ( is_a( $order_item, 'WC_Order_item' ) && is_a( $order_item->get_product(), 'WC_Product' ) ) {
                $product_id = !empty( $order_item->get_product()->get_parent_id() ) ? $order_item->get_product()->get_parent_id() : $order_item->get_product()->get_id();
                $product =  wc_get_product( $product_id );

                if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
                    if ( $product->get_pwgc_is_physical_card() ) {
                        $order = wc_get_order( wc_get_order_id_by_order_item_id( $order_item_id ) );
                        if ( is_a( $order, 'WC_Order' ) ) {
                            $subject = __( 'Gift Card for Order #', 'pw-woocommerce-gift-cards' ) . $order->get_order_number();
                        }
                    }
                }
            }
        }

        if ( is_a( $order_item, 'WC_Order_Item' ) && isset( $woocommerce_wpml ) && is_object( $woocommerce_wpml ) && property_exists( $woocommerce_wpml, 'emails' ) && is_object( $woocommerce_wpml->emails ) && method_exists( $woocommerce_wpml->emails, 'refresh_email_lang' ) ) {
            $order_id = apply_filters( 'wcml_send_email_order_id', $order_item->get_order_id() );

            if ( $order_id ) {
                $woocommerce_wpml->emails->refresh_email_lang( $order_id );
            }
        }

        if ( $this->is_enabled() ) {
            $this->send( $this->get_recipient(), $subject, $this->get_content(), $this->get_headers(), $attachments );
        }

        if ( isset( $gift_card ) && empty( $gift_card->get_recipient_email() ) ) {
            $gift_card->set_recipient_email( $this->get_recipient() );
        }
    }

    public static function create_object( $order_item_id, $gift_card_number, $recipient, $from, $recipient_name, $message, $amount ) {
        global $pw_gift_cards;
        global $pw_gift_card_design_id;

        $item_data = new PW_Gift_Card_Item_Data();

        $gift_card = new PW_Gift_Card( $gift_card_number );

        $expiration_date = '';
        if ( $gift_card->get_id() && !empty( $gift_card->get_expiration_date() ) ) {
            $expiration_date = date_i18n( wc_date_format(), strtotime( $gift_card->get_expiration_date() ) );
        }

        // WooCommerce Currency Switcher by realmag777
        if ( isset( $GLOBALS['WOOCS'] ) ) {
            $amount = apply_filters( 'pwgc_to_current_currency', $amount );
        }

        $item_data->gift_card_number = $gift_card_number;
        $item_data->expiration_date = $expiration_date;
        $item_data->recipient = $recipient;
        $item_data->from = $from;
        $item_data->recipient_name = $recipient_name;
        $item_data->message = $message;
        $item_data->amount = $amount;
        $item_data->product = $pw_gift_cards->get_gift_card_product();
        $item_data->parent_product = $item_data->product; // At this point we only have the main product, not an individual amount variation.

        if ( !empty( $order_item_id ) ) {
            if ( empty( $from ) ) {
                $item_data->from = wc_get_order_item_meta( $order_item_id, PWGC_FROM_META_KEY );
            }

            if ( empty( $recipient_name ) ) {
                $item_data->recipient_name = wc_get_order_item_meta( $order_item_id, PWGC_RECIPIENT_NAME_META_KEY );
            }

            if ( empty( $message ) ) {
                $item_data->message = wc_get_order_item_meta( $order_item_id, PWGC_MESSAGE_META_KEY );
            }

            if ( empty( $amount ) ) {
                $item_data->amount = wc_get_order_item_meta( $order_item_id, PWGC_AMOUNT_META_KEY );
            }

            $order_item = new WC_Order_Item_Product( $order_item_id );
            $item_data->order = wc_get_order( $order_item->get_order_id() );
            $item_data->product_id = $order_item->get_product_id();
            $item_data->variation_id = $order_item->get_variation_id();

            $order_item_product = $order_item->get_product();
            if ( is_a( $order_item_product, 'WC_Product' ) ) {
                $item_data->product = $order_item_product;

                if ( is_a( $order_item_product, 'WC_Product_Variation' ) ) {
                    $item_data->parent_product = wc_get_product( $order_item_product->get_parent_id() );
                }
            }

            if ( !is_numeric( $item_data->amount ) || empty( $item_data->amount ) ) {
                // Previously we didn't store the PWGC_AMOUNT_META_KEY so we need to calculate based on purchase price.
                $item_data->amount = round( $order_item->get_subtotal() / $order_item->get_quantity(), wc_get_price_decimals() );
            }

            // Aelia Currency Switcher - display gift card in the ordered currency.
            if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) && isset( $GLOBALS['woocommerce-aelia-currencyswitcher'] ) ) {
                $cs = $GLOBALS['woocommerce-aelia-currencyswitcher'];
                $cs->get_order( $order_item->get_order_id() );
                $cs->track_order_notification( $order_item->get_order_id() );
                if ( !has_filter( 'woocommerce_currency', array( $cs, 'woocommerce_currency' ), 5 ) ) {
                    add_filter( 'woocommerce_currency', array( $cs, 'woocommerce_currency' ), 5 );
                }
            }
        }

        // Backwards compatibility for old templates.
        $item_data->redeem_url = pwgc_redeem_url( $item_data );

        if ( empty( $item_data->from ) ) {
            $item_data->from = get_option( 'blogname' );
        }

        $design_id = $pw_gift_card_design_id;
        if ( empty( $design_id ) && $design_id !== 0 && !empty( $item_data->parent_product ) && method_exists( $item_data->parent_product, 'get_pwgc_design_id' ) ) {
            $design_id = $item_data->parent_product->get_pwgc_design_id();
        }

        $designs = pwgc_get_designs();
        $item_data->design = reset( $designs );
        if ( isset( $designs[ $design_id ] ) ) {
            $item_data->design = $designs[ $design_id ];
        }

        return $item_data;
    }

    function get_content_html() {
        ob_start();

        wc_get_template(
            $this->template_html,
            array(
                'email' => $this,
                'item_data' => $this->object,
                'email_heading' => $this->get_heading()
            ),
            '',
            $this->template_base
        );

        return ob_get_clean();
    }

    function get_content_plain() {
        ob_start();

        wc_get_template(
            $this->template_plain,
            array(
                'email' => $this,
                'item_data' => $this->object,
                'email_heading' => $this->get_heading()
            ),
            '',
            $this->template_base
        );

        return ob_get_clean();
    }

    // form fields that are displayed in WooCommerce->Settings->Emails
    function init_form_fields() {
        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __( 'Enable/Disable', 'pw-woocommerce-gift-cards' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'pw-woocommerce-gift-cards' ),
                'default' => 'yes',
            ),
            'subject' => array(
                'title'         => __( 'Subject', 'pw-woocommerce-gift-cards' ),
                'type'          => 'text',
                'description'   => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'pw-woocommerce-gift-cards' ), $this->subject ),
                'placeholder'   => '',
                'default'       => $this->default_subject
            ),
            'heading' => array(
                'title'         => __( 'Email Heading', 'pw-woocommerce-gift-cards' ),
                'type'          => 'text',
                'description'   => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'pw-woocommerce-gift-cards' ), $this->heading ),
                'placeholder'   => '',
                'default'       => $this->default_heading
            ),
            'email_type' => array(
                'title'         => __( 'Email type', 'pw-woocommerce-gift-cards' ),
                'type'          => 'select',
                'description'   => __( 'Choose which format of email to send.', 'pw-woocommerce-gift-cards' ),
                'default'       => 'html',
                'class'         => 'email_type',
                'options'       => array(
                    'plain'         => __( 'Plain text', 'pw-woocommerce-gift-cards' ),
                    'html'          => __( 'HTML', 'pw-woocommerce-gift-cards' ),
                    'multipart'     => __( 'Multipart', 'pw-woocommerce-gift-cards' ),
                )
            )
        );
    }

    function add_placeholder( $find, $replace ) {
        global $pw_gift_cards;

        if ( $pw_gift_cards->wc_min_version( '3.2.0' ) ) {
            $this->placeholders[ $find ] = $replace;
        } else {
            $index = array_search( $find, $this->find, true );
            if ( $index === false ) {
                $this->find[] = $find;
                $this->replace[] = $replace;
            } else {
                $this->find[ $index ] = $find;
                $this->replace[ $index ] = $replace;
            }
        }
    }
}

endif;

return new WC_Email_PW_Gift_Card();
