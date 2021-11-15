<?php

defined( 'ABSPATH' ) or exit;

foreach ( $gift_cards as $gift_card ) {
    $original_recipient_email = $gift_card->get_original_recipient();

    if ( empty( $original_recipient_email ) ) {
        $original_recipient_email = $gift_card->get_recipient_email();
    } else if ( empty( $gift_card->get_recipient_email() ) ) {
        $gift_card->set_recipient_email( $original_recipient_email );
    }

    $original_from = $gift_card->get_original_from();
    if ( empty( $original_from ) ) {
        $original_from = get_option( 'blogname' );
    }

    ?>
    <tr data-gift-card-number="<?php echo esc_html( $gift_card->get_number() ); ?>"
        data-original-to="<?php echo esc_html( $original_recipient_email ); ?>"
        data-original-from="<?php echo esc_html( $original_from ); ?>"
        data-original-note="<?php echo esc_html( $gift_card->get_original_note() ); ?>"
    >
        <td class="pwgc-search-result-card-number">
            <?php echo esc_html( $gift_card->get_number() ); ?>
            <div class="pwgc-inactive-card pwgc-balance-error <?php if ( $gift_card->get_active() ) { echo 'pwgc-hidden'; } ?>">
                <?php _e( 'Card has been deleted.', 'pw-woocommerce-gift-cards' ); ?>
            </div>
        </td>
        <td class="pwgc-search-result-balance">
            <?php echo wc_price( $gift_card->get_balance() ); ?>
        </td>
        <td class="pwgc-search-result-expiration-date pwgc-expiration-date-element" <?php if ( 'no' !== get_option( 'pwgc_no_expiration_date', 'no' ) ) { echo 'style="display: none;"'; } ?>>
            <?php
                echo $gift_card->get_expiration_date_html();
            ?>
        </td>
        <td class="pwgc-search-result-recipient-email">
            <?php
                if ( !empty( $gift_card->get_recipient_email() ) ) {
                    ?>
                    <a href="mailto: <?php echo esc_html( $gift_card->get_recipient_email() ); ?>"><?php echo esc_html( $gift_card->get_recipient_email() ); ?></a>
                    <?php
                }
            ?>
        </td>
        <td class="pwgc-search-result-buttons">
            <a href="#" class="pwgc-view-activity button button-secondary"><i class="fas fa-history"></i> <?php _e( 'View activity', 'pw-woocommerce-gift-cards' ); ?></a>
            <span class="pwgc-buttons-active <?php if ( !$gift_card->get_active() ) { echo 'pwgc-hidden'; } ?>">
                <a href="#" class="pwgc-add-note button button-secondary"><i class="far fa-sticky-note"></i> <?php _e( 'Add a note', 'pw-woocommerce-gift-cards' ); ?></a>
                <a href="#" class="pwgc-adjust-balance button button-secondary"><i class="fas fa-balance-scale"></i> <?php _e( 'Adjust balance', 'pw-woocommerce-gift-cards' ); ?></a>
                <a href="#" class="pwgc-expiration-date button button-secondary pwgc-expiration-date-element" <?php if ( 'no' !== get_option( 'pwgc_no_expiration_date', 'no' ) ) { echo 'style="display: none;"'; } ?>><i class="fas fa-calendar-alt"></i> <?php _e( 'Set expiration date', 'pw-woocommerce-gift-cards' ); ?></a>
                <a href="#" class="pwgc-email button button-secondary"><i class="fas fa-envelope"></i> <?php _e( 'Email Gift Card', 'pw-woocommerce-gift-cards' ); ?></a>
                <a href="#" class="pwgc-delete button button-secondary"><i class="fas fa-times"></i> <?php _e( 'Delete', 'pw-woocommerce-gift-cards' ); ?></a>
            </span>
            <span class="pwgc-buttons-inactive <?php if ( $gift_card->get_active() ) { echo 'pwgc-hidden'; } ?>">
                <a href="#" class="pwgc-restore button button-secondary"><i class="fas fa-history"></i> <?php _e( 'Restore', 'pw-woocommerce-gift-cards' ); ?></a>
                <a href="#" class="pwgc-delete-permanently button button-secondary"><i class="fas fa-times"></i> <?php _e( 'Delete Permanently', 'pw-woocommerce-gift-cards' ); ?></a>
            </span>
        </td>
    </tr>
    <?php
}
    if ( count( $gift_cards ) == PWGC_ADMIN_MAX_ROWS ) {
        ?>
        <tr class="pwgc-search-results-more" style="background-color: initial;">
            <td colspan="6">
                <a href="#" id="pwgc-search-results-more-button" class="button button-primary"><?php _e( 'Load more', 'pw-woocommerce-gift-cards' ); ?></a>
                <script>
                    jQuery('#pwgc-search-results-more-button').click(function(e) {
                        var messageContainer = jQuery('.pwgc-search-results-more');
                        messageContainer.html('<i class="fas fa-cog fa-spin fa-3x"></i>');

                        var searchTerms = jQuery('#pwgc-balance-search');
                        var offset = jQuery('#pwgc-search-results-table tr').length - 2; // Accounts for this row and the header row.

                        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-search_load_more', 'search_terms': searchTerms.val(), 'offset': offset}, function(result) {
                            jQuery('#pwgc-search-results-table tr:last').after(result.html);
                            messageContainer.remove();
                        }).fail(function(xhr, textStatus, errorThrown) {
                            messageContainer.remove();
                            if (errorThrown) {
                                alert(errorThrown);
                            } else {
                                alert('Unknown Error');
                            }
                        });

                        e.preventDefault();
                        return false;
                    });
                </script>
            </td>
        </tr>
        <?php
    }
?>
<script>
    jQuery('.pwgc-view-activity').off('click').on('click', function(e) {
        var row = jQuery(this).closest('tr');
        if (row.find('.pwgc-balance-activity-table').length > 0) {
            row.find('.pwgc-balance-activity-container').remove();
        } else {
            pwgcAdminGiftCardActivity(row);
        }

        e.preventDefault();
        return false;
    });

    jQuery('.pwgc-adjust-balance').off('click').on('click', function(e) {
        var row = jQuery(this).closest('tr');
        var amount = prompt( pwgc.i18n.adjustment_amount_prompt );
        if (amount) {
            pwgcAdjustBalance(row, amount);
        }

        e.preventDefault();
        return false;
    });

    jQuery('.pwgc-add-note').off('click').on('click', function(e) {
        var row = jQuery(this).closest('tr');
        pwgcAdjustBalance(row, 0);

        e.preventDefault();
        return false;
    });

    jQuery('.pwgc-expiration-date').off('click').on('click', function(e) {
        var row = jQuery(this).closest('tr');
        pwgcSetExpirationDate(row);

        e.preventDefault();
        return false;
    });

    jQuery('.pwgc-email').off('click').on('click', function(e) {
        var row = jQuery(this).closest('tr');
        pwgcEmailGiftCard(row);

        e.preventDefault();
        return false;
    });

    jQuery('.pwgc-delete').off('click').on('click', function(e) {
        var row = jQuery(this).closest('tr');
        if (confirm('<?php _e( 'Are you sure you want to delete this gift card?', 'pw-woocommerce-gift-cards' ); ?>')) {
            pwgcDelete(row);
        }

        e.preventDefault();
        return false;
    });

    jQuery('.pwgc-delete-permanently').off('click').on('click', function(e) {
        var row = jQuery(this).closest('tr');
        if (confirm('<?php _e( 'Are you sure you want to PERMANENTLY delete this gift card? This action cannot be undone!', 'pw-woocommerce-gift-cards' ); ?>')) {
            pwgcDelete(row);
        }

        e.preventDefault();
        return false;
    });

    jQuery('.pwgc-restore').off('click').on('click', function(e) {
        var row = jQuery(this).closest('tr');
        pwgcRestore(row);

        e.preventDefault();
        return false;
    });
</script>
<?php
