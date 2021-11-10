<?php
/**
 * REST API Reports composites query
 *
 * Class for parameter-based Products Stats Report querying
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Composite Products
 * @since    8.3.0-dev
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\API\Reports\Query as ReportsQuery;

/**
 * WC_CP_Analytics_Revenue_Query class.
 *
 * @version 8.3.0-dev
 */
class WC_CP_Analytics_Revenue_Query extends ReportsQuery {

	/**
	 * Valid fields for Products report.
	 *
	 * @return array
	 */
	protected function get_default_query_vars() {
		return array();
	}

	/**
	 * Get product data based on the current query vars.
	 *
	 * @return array
	 */
	public function get_data() {
		$args = apply_filters( 'woocommerce_analytics_composites_query_args', $this->get_query_vars() );

		/* @var WC_CP_Analytics_Revenue_Data_Store $data_store */
		$data_store = WC_Data_Store::load( 'report-composites-revenue' );
		$results    = $data_store->get_data( $args );
		return apply_filters( 'woocommerce_analytics_composites_select_query', $results, $args );
	}

}
