jQuery(function() {
    var addButton = jQuery('<div id="pwgc-add-amount-button" class="button button-primary">' + pwgc.i18n.add + '</div>');
    addButton.click(pwgc_add_amount);
    jQuery('#pwgc_new_amount').after(addButton);

    jQuery('#pwgc-add-bonus-amount-button').click(pwgc_add_bonus_amount);


    jQuery('#pwgc_new_amount').on('keypress', function (e) {
        if (e.keyCode == 13) {
            jQuery('#pwgc-add-amount-button').click();
            e.preventDefault();
            return false;
        }
    });

    jQuery('.pwgc-remove-amount-button').on('click', function() {
        pwgc_remove_amount(jQuery(this));
    });

    jQuery('.pwgc-remove-bonus-amount-button').on('click', function() {
        pwgc_remove_bonus_amount(jQuery(this));
    });

    jQuery('#_pwgc_custom_amount_allowed').on('change', function() {
        jQuery('#_pwgc_custom_amount_min,#_pwgc_custom_amount_max').closest('.form-field').toggle(jQuery(this).is(':checked'));
        if (jQuery(this).is(':checked')) {
            jQuery('#_pwgc_custom_amount_min,#_pwgc_custom_amount_max').attr('required', 'required');
        } else {
            jQuery('#_pwgc_custom_amount_min,#_pwgc_custom_amount_max').removeAttr('required');
        }
    });

    jQuery('#_pwgc_is_physical_card').on('change', function() {
        jQuery('#_pwgc_physical_email').closest('.form-field').toggle(jQuery(this).is(':checked'));
        if (jQuery(this).is(':checked')) {
            jQuery('.shipping_options').show();
        } else {
            jQuery('.shipping_options').hide();
        }
    });

    jQuery('#pwgc-partner-dismiss').on('click', function(e) {
        jQuery('#pwgc-partner-container').hide();

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-hide_partner_message'}, function( result ) {
        }).fail(function(xhr, textStatus, errorThrown) {
            if (errorThrown) {
                alert(pwgc.i18n.error + ': ' + errorThrown + '\n\n pw-gift-cards-hide_partner_message');
            }
        });

        e.preventDefault();
        return false;
    });

    jQuery('#_pwgc_expire_days').on('propertychange keyup input cut paste', function() {
        pwgc_maybe_show_expiration_warning();
    });

    jQuery('#_pwgc_enable_bonus').on('change', function() {
        jQuery('#pwgc_bonus_container').toggle(jQuery(this).is(':checked'));
    });

    pwgc_maybe_show_expiration_warning();
});

function pwgc_add_amount() {
    var newAmount = jQuery('#pwgc_new_amount');
    if (newAmount.val()) {

        // Validation
        var minAmount = parseFloat(jQuery('#_pwgc_custom_amount_min').val());
        var maxAmount = parseFloat(jQuery('#_pwgc_custom_amount_max').val());

        if ( parseFloat( newAmount.val() ) <= 0 ) {
            alert(pwgc.i18n.error_greater_than_zero);
            return;
        } else if (parseFloat( newAmount.val() ) < minAmount) {
            alert(pwgc.i18n.error_greater_than_min + ' (' + minAmount + ')');
            return;
        } else if (parseFloat( newAmount.val() ) > maxAmount) {
            alert(pwgc.i18n.error_less_than_max + ' (' + maxAmount + ')');
            return;
        }

        jQuery('#pwgc-add-amount-button').text(pwgc.i18n.wait + '...');

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-add_gift_card_amount', 'product_id': jQuery('#post_ID').val(), 'amount': newAmount.val(), 'security': pwgc.nonces.add_gift_card_amount}, function( result ) {
            if (result.success) {
                var prettyAmount = result.data.amount;
                var amountContainer = jQuery('#pwgc-amount-container-template').clone().removeAttr('id').removeClass('pwgc-hidden');
                amountContainer.find('.pwgc-amount').text(prettyAmount);
                amountContainer.attr('data-variation_id', result.data.variation_id);
                amountContainer.find('.pwgc-remove-amount-button').click(function() {
                    pwgc_remove_amount(jQuery(this));
                });
                newAmount.val('').focus();
                jQuery('#pwgc-amounts-container').append(amountContainer);

                pwgc_add_default_amount_option(prettyAmount);

                jQuery('#pwgc-add-amount-button').text(pwgc.i18n.add);

            } else {
                jQuery('#pwgc-add-amount-button').text(pwgc.i18n.add);
                alert(result.data.message);
                newAmount.focus();
            }

        }).fail(function(xhr, textStatus, errorThrown) {
            if (errorThrown) {
                alert(pwgc.i18n.error + ': ' + errorThrown + '\n\n pw-gift-cards-add_gift_card_amount');
                jQuery('#pwgc-add-amount-button').text(pwgc.i18n.add);
            }
        });
    }
}

function pwgc_remove_amount(element) {
    var amountContainer = jQuery(element).closest('.pwgc-amount-container');
    var amount = amountContainer.find('.pwgc-amount').text().trim();

    if (confirm(pwgc.i18n.remove + ' ' + amount + '?')) {

        pwgc_remove_default_amount_option( amount );

        amountContainer.fadeOut(400, function() {
            var productId = jQuery('#post_ID').val();
            var variationId = amountContainer.attr('data-variation_id');

            jQuery.post(ajaxurl, {'action': 'pw-gift-cards-remove_gift_card_amount', 'product_id': productId, 'variation_id': variationId, 'security': pwgc.nonces.remove_gift_card_amount}, function( result ) {
                if (result.success) {
                    amountContainer.remove();
                } else {
                    pwgc_add_default_amount_option(amount);
                    amountContainer.show();
                    alert(result.data.message);
                }

            }).fail(function(xhr, textStatus, errorThrown) {
                if (errorThrown) {
                    pwgc_add_default_amount_option(amount);
                    amountContainer.show();
                    alert(pwgc.i18n.error + ': ' + errorThrown + '\n\n pw-gift-cards-remove_gift_card_amount');
                }
            });
        });
    }
}

function pwgc_add_bonus_amount() {
    var spend = prompt( pwgc.i18n.bonus_spend_prompt );
    if (spend === null && spend !== '') { return; }

    var get = prompt( pwgc.i18n.bonus_get_prompt );
    if (get === null && spend !== '') { return; }

    jQuery('#pwgc-add-bonus-amount-button').text(pwgc.i18n.wait + '...');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-add_bonus_amount', 'product_id': jQuery('#post_ID').val(), 'spend': spend, 'get': get, 'security': pwgc.nonces.add_bonus_amount}, function( result ) {
        if (result.success) {
            var amountContainer = jQuery('#pwgc-bonus-amount-container-template').clone().removeAttr('id').removeClass('pwgc-hidden');
            amountContainer.find('.pwgc-bonus-amount').text(result.data.message);
            amountContainer.attr('data-key', spend);
            amountContainer.find('.pwgc-remove-bonus-amount-button').click(function() {
                pwgc_remove_bonus_amount(jQuery(this));
            });
            jQuery('#pwgc-bonus-amounts-container').append(amountContainer);
            jQuery('#pwgc-add-bonus-amount-button').text(pwgc.i18n.add_bonus_amount);

        } else {
            jQuery('#pwgc-add-bonus-amount-button').text(pwgc.i18n.add_bonus_amount);
            alert(result.data.message);
            newAmount.focus();
        }

    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            alert(pwgc.i18n.error + ': ' + errorThrown + '\n\n pw-gift-cards-add_bonus_amount');
            jQuery('#pwgc-add-bonus-amount-button').text(pwgc.i18n.add_bonus_amount);
        }
    });
}

function pwgc_remove_bonus_amount(element) {
    var amountContainer = jQuery(element).closest('.pwgc-bonus-amount-container');
    var amount = amountContainer.find('.pwgc-bonus-amount').text();

    if (confirm(pwgc.i18n.remove + ' ' + amount.trim() + '?')) {

        amountContainer.fadeOut(400, function() {
            var productId = jQuery('#post_ID').val();
            var key = amountContainer.attr('data-key');

            jQuery.post(ajaxurl, {'action': 'pw-gift-cards-remove_bonus_amount', 'product_id': productId, 'key': key, 'security': pwgc.nonces.remove_bonus_amount}, function( result ) {
                if (result.success) {
                    amountContainer.remove();
                } else {
                    amountContainer.show();
                    alert(result.data.message);
                }

            }).fail(function(xhr, textStatus, errorThrown) {
                if (errorThrown) {
                    amountContainer.show();
                    alert(pwgc.i18n.error + ': ' + errorThrown + '\n\n pw-gift-cards-remove_bonus_amount');
                }
            });
        });
    }
}

function pwgc_add_default_amount_option(amount) {
    jQuery('#_pwgc_default_amount').append(jQuery('<option></option>').attr('value', amount).text(amount));
}

function pwgc_remove_default_amount_option(amount) {
    jQuery("#_pwgc_default_amount option[value='" + amount + "']").remove();
}

function pwgc_maybe_show_expiration_warning() {
    var expireDays = jQuery('#_pwgc_expire_days');
    if (expireDays.val() && parseInt(expireDays.val()) < 1825) {
        jQuery('#pwbf-expire-days-us-warning').show();
    } else {
        jQuery('#pwbf-expire-days-us-warning').hide();
    }
}
