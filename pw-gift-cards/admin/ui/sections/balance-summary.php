<?php

defined( 'ABSPATH' ) or exit;

global $wpdb;

$active_count = 0;
$outstanding_balance = 0;

$date_sql_activity = '';
$date_sql_where = '';
if ( isset( $_REQUEST['date'] ) && !empty( $_REQUEST['date'] ) ) {
    $date_sql_activity = $wpdb->prepare( "AND activity.activity_date <= %s", $_REQUEST['date'] . ' 23:59:59' );
    $date_sql_where = $wpdb->prepare( "AND card.create_date <= %s", $_REQUEST['date'] . ' 23:59:59' );
}

$results = $wpdb->get_row( "
    SELECT
        COUNT(DISTINCT card.pimwick_gift_card_id) AS active_count,
        SUM(activity.amount) AS outstanding_balance
    FROM
        {$wpdb->pimwick_gift_card} AS card
    JOIN
        {$wpdb->pimwick_gift_card_activity} AS activity ON (activity.pimwick_gift_card_id = card.pimwick_gift_card_id $date_sql_activity)
    WHERE
        card.active = 1
        AND (card.expiration_date IS NULL OR card.expiration_date >= NOW())
        $date_sql_where
    ORDER BY
        card.create_date
" );
if ( null !== $results ) {
    $active_count = $results->active_count;
    $outstanding_balance = $results->outstanding_balance;
}

?>
<div class="pwgc-summary-item">
    <div class="pwgc-summary-item-header"><?php echo number_format( $active_count ); ?></div>
    <div><?php _e( 'Active gift cards', 'pw-woocommerce-gift-cards' ); ?></div>
</div>
<div class="pwgc-summary-item">
    <div class="pwgc-summary-item-header"><?php echo wc_price( $outstanding_balance ); ?></div>
    <div><?php _e( 'Outstanding balances', 'pw-woocommerce-gift-cards' ); ?></div>
</div>
<div class="pwgc-summary-item pwgc-summary-item-date <?php echo ( 'yes' !== get_option( 'pwgc_show_balances_by_date', 'no' ) ) ? 'pwgc-hidden' : ''; ?>">
    <div>
        <input type="text" class="short" name="date" id="pwgc-balance-search-date" value="<?php echo isset( $_REQUEST['date'] ) ? esc_attr( $_REQUEST['date'] ) : ''; ?>" placeholder="YYYY-MM-DD" maxlength="10" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" />
        <input type="button" id="pwgc-balance-search-date-refresh" class="button button-primary" value="<?php _e( 'Apply', 'pw-woocommerce-gift-cards' ); ?>">
        <div><?php _e( 'Balances as of date', 'pw-woocommerce-gift-cards' ); ?></div>
    </div>
</div>
<script>
    jQuery(function() {
        jQuery('#pwgc-balance-search-date').datepicker({
            defaultDate: '',
            dateFormat: 'yy-mm-dd',
            numberOfMonths: 1,
            showButtonPanel: true,
            onSelect: function() {
                pwgcDatePickerSelect( jQuery( this ) );
            }
        });
        pwgcDatePickerSelect(jQuery('#pwgc-balance-search-date'));

        jQuery('#pwgc-balance-search-date-refresh').on('click', function(e) {
            pwgcAdminLoadBalanceSummary();
            pwgcAdminBalanceSearch();
            e.preventDefault();
            return false;
        });
    });
</script>