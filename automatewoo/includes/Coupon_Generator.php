<?php

namespace AutomateWoo;

/**
 * Generates new coupons based on existing coupons
 *
 * @class Coupon_Generator
 */
class Coupon_Generator {

	/** @var string : Coupon code to be cloned */
	public $template_coupon_code;

	/** @var integer */
	public $template_coupon_id;

	/** @var string */
	public $code;

	/** @var string */
	public $prefix = '';

	/** @var string */
	public $suffix = '';

	/*** @var string : Number of days till coupon expires */
	public $expires;

	/** @var int */
	public $usage_limit;

	/** @var string */
	public $email_restriction;

	/** @var string */
	public $description;


	/**
	 * Coupon_Generator constructor.
	 */
	public function __construct() {
		// set default values
		$this->prefix      = apply_filters( 'automatewoo_generate_coupon_default_prefix', 'aw-' );
		$this->description = __( 'Generated by AutomateWoo', 'automatewoo' );
		$this->usage_limit = 1;
	}


	/**
	 * @param string $code
	 */
	public function set_template_coupon_code( $code ) {
		if ( ! $code ) {
			return;
		}

		global $wpdb;
		$this->template_coupon_code = $code;

		$this->template_coupon_id = absint(
			$wpdb->get_var(
				$wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon'", $this->template_coupon_code )
			)
		);
	}


	/**
	 * @return integer
	 */
	public function get_template_coupon_id() {
		return absint( $this->template_coupon_id );
	}


	/**
	 * @param string $prefix
	 */
	public function set_prefix( $prefix ) {
		$this->prefix = $prefix;
	}


	/**
	 * @param string $code
	 */
	public function set_code( $code ) {
		$this->code = $code;
	}


	/**
	 * @param string $email
	 */
	public function set_email_restriction( $email ) {
		$this->email_restriction = is_email( $email );
	}


	/**
	 * @param integer $days
	 */
	public function set_expires( $days ) {
		$this->expires = absint( $days );
	}


	/**
	 * @param string $suffix
	 */
	public function set_suffix( $suffix ) {
		$this->suffix = $suffix;
	}


	/**
	 * @param integer $usage_limit
	 */
	public function set_usage_limit( $usage_limit ) {
		$this->usage_limit = absint( $usage_limit );
	}


	/**
	 * @param string $description
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}


	/**
	 * Generates a unique coupon code
	 *
	 * @return string
	 */
	public function generate_code() {
		$length = absint( apply_filters( 'automatewoo/coupon_generator/key_length', 10, $this ) );
		$code   = trim( $this->prefix ) . aw_generate_coupon_key( $length ) . trim( $this->suffix );
		$code   = apply_filters( 'automatewoo/coupon_generator/code', $code, $this );

		if ( $this->is_existing_coupon_code( $code ) ) {
			return $this->generate_code();
		}

		return $code;
	}


	/**
	 * @param string $code
	 * @return bool
	 */
	public function is_existing_coupon_code( $code ) {
		return (bool) wc_get_coupon_id_by_code( $code );
	}


	/**
	 * @return \WC_Coupon|bool
	 */
	public function generate_coupon() {

		if ( ! $this->get_template_coupon_id() ) {
			return false;
		}

		if ( ! $this->code ) {
			$this->code = $this->generate_code();
		}

		$coupon = [
			'post_title'   => $this->code,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_type'    => 'shop_coupon',
			'post_excerpt' => $this->description,
		];

		wp_insert_post( $coupon );
		$coupon = new \WC_Coupon( $this->code );

		if ( $this->email_restriction ) {
			$coupon->set_email_restrictions( [ $this->email_restriction ] );
		}

		// copy details from template coupon to new coupon
		$template_coupon = new \WC_Coupon( $this->get_template_coupon_id() );

		$coupon->set_discount_type( $template_coupon->get_discount_type() );
		$coupon->set_amount( $template_coupon->get_amount() );
		$coupon->set_individual_use( $template_coupon->get_individual_use() );
		$coupon->set_product_ids( $template_coupon->get_product_ids() );
		$coupon->set_excluded_product_ids( $template_coupon->get_excluded_product_ids() );
		$coupon->set_usage_limit_per_user( $template_coupon->get_usage_limit_per_user() );
		$coupon->set_limit_usage_to_x_items( $template_coupon->get_limit_usage_to_x_items() );
		$coupon->set_free_shipping( $template_coupon->get_free_shipping() );
		$coupon->set_exclude_sale_items( $template_coupon->get_exclude_sale_items() );
		$coupon->set_product_categories( $template_coupon->get_product_categories() );
		$coupon->set_excluded_product_categories( $template_coupon->get_excluded_product_categories() );
		$coupon->set_minimum_amount( $template_coupon->get_minimum_amount() );
		$coupon->set_maximum_amount( $template_coupon->get_maximum_amount() );
		$coupon->set_date_expires( $template_coupon->get_date_expires() );

		// support for WC_Free_Gift_Coupons
		if ( Integrations::is_free_gift_coupons_active() ) {
			$coupon->update_meta_data( '_wc_free_gift_coupon_data', $template_coupon->get_meta( '_wc_free_gift_coupon_data' ) );
			$coupon->update_meta_data( '_wc_free_gift_coupon_free_shipping', $template_coupon->get_meta( '_wc_free_gift_coupon_free_shipping' ) );
		}

		// support for subscription recurring coupons
		if ( Integrations::is_subscriptions_active() && \WC_Subscriptions_Coupon::coupon_is_limited( $this->get_template_coupon_id() ) ) {
			$coupon->update_meta_data( '_wcs_number_payments', $template_coupon->get_meta( '_wcs_number_payments' ) );
		}

		if ( $this->expires ) {
			$date = aw_normalize_date( "+$this->expires days" );
			$coupon->set_date_expires( $date->getTimestamp() );
		}

		$coupon->set_usage_limit( $this->usage_limit );
		$coupon->update_meta_data( '_is_aw_coupon', true );

		if ( Integrations::is_points_rewards_active() ) {
			$coupon->update_meta_data( '_wc_points_modifier', $template_coupon->get_meta( '_wc_points_modifier' ) );
		}

		/**
		 * Action fires before saving a coupon that is generated from a template coupon.
		 *
		 * @since 5.2.0
		 *
		 * @param \WC_Coupon                    $coupon          The newly generated coupon object.
		 * @param \WC_Coupon                    $template_coupon The template coupon object.
		 * @param \AutomateWoo\Coupon_Generator $this            The coupon generator object.
		 */
		do_action( 'automatewoo/coupon_generator/generate_from_template_coupon', $coupon, $template_coupon, $this );

		$coupon->save();

		return $coupon;
	}

}