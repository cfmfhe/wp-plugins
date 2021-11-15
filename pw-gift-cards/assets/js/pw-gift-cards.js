document.addEventListener("DOMContentLoaded", function(event) {
    var checkBalanceForm = jQuery('#pwgc-balance-form');
    if ( checkBalanceForm.length ) {
        checkBalanceForm.on('submit', function(e) {
            pwgc_check_balance();

            e.preventDefault();
            return false;
        });
        pwgc_check_balance();
    }

    jQuery('#pwgc-balance-reload').on('click', function(e) {
        var cardNumber = jQuery(this).attr('data-card-number');
        var productUrl = jQuery(this).attr('data-url');
        if (cardNumber) {
            var separator = '?';
            if (productUrl.includes('?')) {
                separator = '&';
            }

            location.href = productUrl + separator + pwgc.reload_key + '=' + cardNumber;
        }
        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-manual-debit').on('click', function(e) {
        jQuery(this).prop('disabled', true);
        var errorMessage = jQuery('#pwgc-balance-error');
        errorMessage.text('');

        var cardNumber = jQuery(this).attr('data-card-number');
        if (cardNumber) {
            var amount = prompt( pwgc.i18n.debit_amount_prompt );
            if (amount) {
                var note = prompt( pwgc.i18n.debit_note_prompt );
                if (note !== null) {
                    var balance = jQuery('#pwgc-balance-amount');
                    var balanceMessage = jQuery('#pwgc-balance-message');

                    jQuery.post(pwgc.ajaxurl, {'action': 'pw-gift-cards-debit', 'card_number': cardNumber, 'amount': amount, 'note': note, 'security': pwgc.nonces.debit_balance}, function(result) {
                        balance.html(result.balance);
                        balanceMessage.html(result.message);
                    }).fail(function(xhr, textStatus, errorThrown) {
                        if (errorThrown) {
                            errorMessage.text(errorThrown);
                        } else {
                            errorMessage.text('Unknown ajax error');
                        }
                    });
                }
            }
        }
        jQuery(this).prop('disabled', false);
        e.preventDefault();
        return false;
    });

    jQuery(document.body).on('updated_wc_div', pwgc_bind_redeem_form);
    pwgc_bind_redeem_form();

    jQuery(document.body).on('updated_wc_div', pwgc_bind_remove_link);
    pwgc_bind_remove_link();

    jQuery('#pwgc-message').on('input propertychange', function() {
        pwgc_message_characters_remaining();
    });
    pwgc_message_characters_remaining();

    jQuery('form.variations_form').on('show_variation', function() {
        jQuery('#pwgc-purchase-container').show();
        pwgc_toggle_custom_amount_visibility();
    });

    jQuery('form.variations_form').on('hide_variation', function() {
        jQuery('#pwgc-purchase-container').hide();
        pwgc_toggle_custom_amount_visibility();
    });

    var attributeSelector = jQuery(document.getElementById(pwgc.denomination_attribute_slug));
    if (attributeSelector.length == 0) {
        attributeSelector = jQuery(document.getElementsByName('attribute_' + pwgc.denomination_attribute_slug));
    }

    if (attributeSelector.val()) {
        jQuery('#pwgc-purchase-container').show();
    } else {
        jQuery('#pwgc-purchase-container').hide();
    }

    pwgc_toggle_custom_amount_visibility();

    jQuery('#pwgc-custom-amount').on('blur', function() {
        jQuery('#pwgc-custom-amount-error').text('');

        var amount = jQuery(this).val();

        amount = amount.replace(pwgc.thousand_separator, '');
        amount = amount.replace(pwgc.decimal_separator, '.');
        amount = amount.replace(/[^0-9.]/g,'');
        if (amount) {
            var floatAmount = parseFloat(amount);
            var container = jQuery('#pwgc-purchase-container');
            var minAmount = parseFloat(container.attr('data-min-amount'));
            var maxAmount = parseFloat(container.attr('data-max-amount'));

            if (floatAmount < minAmount) {
                jQuery('#pwgc-custom-amount-error').html(pwgc.i18n.min_amount_error + minAmount.toFixed(pwgc.decimal_places).replace('.', pwgc.decimal_separator));
            } else if (floatAmount > maxAmount) {
                jQuery('#pwgc-custom-amount-error').html(pwgc.i18n.max_amount_error + maxAmount.toFixed(pwgc.decimal_places).replace('.', pwgc.decimal_separator));
            } else {
                jQuery(this).val(floatAmount.toFixed(pwgc.decimal_places).replace('.', pwgc.decimal_separator).replace(/\B(?=(\d{3})+(?!\d))/g, pwgc.thousand_separator));
            }
        } else {
            jQuery('#pwgc-custom-amount-error').html(pwgc.i18n.custom_amount_required_error);
        }
    });

    jQuery('#pwgc-to').on('blur', function() {
        var recipients = jQuery(this).val();
        if (recipients) {
            // For clarity, ensure we do a comma followed by a space.
            // Babel translation of this line:
            // jQuery(this).val(recipients.trim().split(/[ ,]+/).map(item=>item.trim()).join(', '));

            // This is compatible with IE11

            recipients = recipients.trim().split(/[ ,]+/).map(function (item) {
                return item.trim();
            });

            if (pwgc.allow_multiple_recipients != 'yes' && recipients.length > 1) {
                recipients = [recipients[0]];
            }

            jQuery(this).val(recipients.join(', '));
        }

        pwgc_toggle_quantity();
    });

    jQuery('.variations_form').on( 'found_variation.wc-variation-form', function() {
        pwgc_toggle_quantity();
    });

    jQuery('.variations_form').on( 'reset_data', function() {
        pwgc_toggle_quantity();
    });

    jQuery('.variations_form').on( 'submit', function(e) {
        if (jQuery('#pwgc-to').length) {
            var recipients = jQuery('#pwgc-to').val().split(/[ ,]+/);
            var badRecipients = [];

            for (var i = 0; i < recipients.length; i++) {
                if (!pwgc_is_email(recipients[i])) {
                    badRecipients.push(recipients[i]);
                }
            }

            if (badRecipients.length) {
                alert(pwgc.i18n.invalid_recipient_error + '\n\n' + badRecipients.join('\n'));
                e.preventDefault();
                return false;
            }
        }
    });

    jQuery('.show-pw-gift-card').off('click').on('click', function(e) {
        jQuery('.checkout_pw_gift_card').slideToggle(400, function() {
            jQuery('.checkout_pw_gift_card').find(':input:eq(0)').focus();
        });

        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-apply-gift-card-checkout').off('click.pimwick').on('click.pimwick', function(e) {
        pwgc_checkout_redeem_gift_card(jQuery(this));
        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-redeem-gift-card-number').off('keypress.pimwick').on('keypress.pimwick', function(e) {
        if (e.keyCode == 13) {
            pwgc_checkout_redeem_gift_card(jQuery('#pwgc-apply-gift-card-checkout'));

            e.preventDefault();
            return false;
        }
    });

    var deliveryDateField = document.getElementById('pwgc-delivery-date');
    if (deliveryDateField) {
        var today = new Date();
        today.setDate(today.getDate());

        var nextYear = new Date();
        nextYear.setDate(nextYear.getDate() + 365);

        var picker = new Pikaday({
            field: deliveryDateField,
            minDate: today,
            maxDate: nextYear,
            format: 'YYYY-MM-DD',
            i18n: {
                previousMonth : pwgc.i18n.previousMonth,
                nextMonth     : pwgc.i18n.nextMonth,
                months        : [pwgc.i18n.jan, pwgc.i18n.feb, pwgc.i18n.mar, pwgc.i18n.apr, pwgc.i18n.may, pwgc.i18n.jun, pwgc.i18n.jul, pwgc.i18n.aug, pwgc.i18n.sep, pwgc.i18n.oct, pwgc.i18n.nov, pwgc.i18n.dec],
                weekdays      : [pwgc.i18n.sunday, pwgc.i18n.monday, pwgc.i18n.tuesday, pwgc.i18n.wednesday, pwgc.i18n.thursday, pwgc.i18n.friday, pwgc.i18n.saturday],
                weekdaysShort : [pwgc.i18n.sun, pwgc.i18n.mon, pwgc.i18n.tue, pwgc.i18n.wed, pwgc.i18n.thu, pwgc.i18n.fri, pwgc.i18n.sat]
            },
            toString: function(date, format) {
                const year = date.getFullYear();
                const month = date.getMonth() + 1;
                const day = date.getDate();
                return pwgc_pad(year, 4) + '-' + pwgc_pad(month, 2) + '-' + pwgc_pad(day, 2);
            },
        });
    }
});

function pwgc_pad(n, width, z) {
    z = z || '0';
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

function pwgc_is_email(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function pwgc_toggle_quantity() {
    if (jQuery('#pwgc-to').length) {
        var recipients = jQuery('#pwgc-to').val().split(/[ ,]+/);
        if (recipients.length > 1) {
            jQuery('#pwgc-recipient-count').text(recipients.length);
            jQuery('#pwgc-quantity-one-per-recipient').show();
            jQuery('input.qty').val('1');
            jQuery('.quantity').hide();
        } else {
            jQuery('#pwgc-quantity-one-per-recipient').hide();
            jQuery('.quantity').show();
        }
    }
}

function pwgc_toggle_custom_amount_visibility() {
    var showOtherAmount = false;
    var attributeSelector = jQuery(document.getElementById(pwgc.denomination_attribute_slug));
    if (attributeSelector.length != 0) {
        showOtherAmount = (attributeSelector.val() == pwgc.other_amount_prompt);
    } else {
        var selectedRadioButton = jQuery("input[type='radio'][name='attribute_" + pwgc.denomination_attribute_slug + "']:checked");
        if (selectedRadioButton.length > 0) {
            showOtherAmount = (selectedRadioButton.val() == pwgc.other_amount_prompt);
        } else {
            attributeSelector = jQuery(document.getElementsByName('attribute_' + pwgc.denomination_attribute_slug));
            showOtherAmount = (attributeSelector.val() == pwgc.other_amount_prompt);
        }
    }

    if (showOtherAmount) {
        jQuery('#pwgc-custom-amount-form').show();
        jQuery('#pwgc-custom-amount').focus();
    } else {
        jQuery('#pwgc-custom-amount-form').hide();
    }
}

function pwgc_check_balance() {
    var cardNumber = jQuery('#pwgc-balance-number');
    if (!cardNumber.val()) { return; }

    jQuery('#pwgc-balance-error').text('');
    jQuery('#pwgc-balance-activity,#pwgc-balance-expiration-date-container,#pwgc-balance-reload,#pwgc-manual-debit').hide();
    jQuery('#pwgc-balance-amount').html(pwgc.balance_check_icon);

    jQuery.post(pwgc.ajaxurl, {'action': 'pw-gift-cards-balance', 'card_number': cardNumber.val(), 'security': pwgc.nonces.check_balance}, function( result ) {
        if (result.success) {

            jQuery('#pwgc-balance-amount').html(result.data.balance);

            if (result.data.expiration_date) {
                jQuery('#pwgc-balance-expiration-date').html(result.data.expiration_date);
                jQuery('#pwgc-balance-expiration-date-container').show();
            }

            jQuery('#pwgc-balance-activity').html(result.data.activity).show();
            jQuery('#pwgc-balance-reload,#pwgc-manual-debit').attr('data-card-number', result.data.card_number).show();

        } else {
            jQuery('#pwgc-balance-error').text(result.data.message);
            jQuery('#pwgc-balance-amount').text('');
            jQuery('#pwgc-balance-activity,#pwgc-balance-expiration-date-container,#pwgc-balance-reload,#pwgc-manual-debit').hide();
        }
        cardNumber.focus();

    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            jQuery('#pwgc-balance-error').text(errorThrown);
        } else {
            jQuery('#pwgc-balance-error').text('Unknown Error');
        }
        jQuery('#pwgc-balance-amount').text('');
        jQuery('#pwgc-balance-reload,#pwgc-manual-debit').hide();
        cardNumber.focus();
    });
}

function pwgc_bind_remove_link() {
    jQuery('.pwgc-remove-card').on('click', function(e) {
        var cardNumber = jQuery(this).attr('data-card-number');

        jQuery.post(pwgc.ajaxurl, {'action': 'pw-gift-cards-remove', 'card_number': cardNumber, 'security': pwgc.nonces.remove_card}, function( result ) {
            location.reload();
        }).fail(function(xhr, textStatus, errorThrown) {
            if (errorThrown) {
                alert(errorThrown);
            } else {
                alert('Unknown Error');
            }
            location.reload();
        });

        e.preventDefault();
        return false;
    });
}

function pwgc_bind_redeem_form() {
    jQuery('#pwgc-redeem-form').off('submit.pimwick').on('submit.pimwick', function(e) {
        var redeemButton = jQuery('#pwgc-redeem-button');

        pwgc_redeem_gift_card(redeemButton);

        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-apply-gift-card,#pwgc-redeem-button').off('click.pimwick').on('click.pimwick', function(e) {
        pwgc_redeem_gift_card(jQuery(this));

        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-redeem-gift-card-number').off('keypress.pimwick').on('keypress.pimwick', function(e) {
        if (e.keyCode == 13) {
            pwgc_redeem_gift_card(jQuery('#pwgc-apply-gift-card'));

            e.preventDefault();
            return false;
        }
    });
}

function pwgc_redeem_gift_card(redeemButton) {
    var cardNumber = jQuery('#pwgc-redeem-gift-card-number');
    var errorContainer = jQuery('#pwgc-redeem-error');

    errorContainer.text('');
    redeemButton.attr('data-apply-text', redeemButton.attr('value')).attr('value', redeemButton.attr('data-wait-text')).prop('disabled', true);

    jQuery.post(pwgc.ajaxurl, {'action': 'pw-gift-cards-redeem', 'card_number': cardNumber.val(), 'security': pwgc.nonces.apply_gift_card}, function( result ) {
        if (result.success) {
            // We could hook into the cart's ajax calls, but for now we'll just reload.
            location.reload();
        } else {
            errorContainer.text(result.data.message);
            redeemButton.attr('value', redeemButton.attr('data-apply-text')).prop('disabled', false);
            cardNumber.focus();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            errorContainer.text(errorThrown);
        } else {
            errorContainer.text('Unknown Error');
        }
        redeemButton.attr('value', redeemButton.attr('data-apply-text')).prop('disabled', false);
        cardNumber.focus();
    });
}

function pwgc_message_characters_remaining() {
    var charsRemaining = pwgc.max_message_characters;

    var messageElement = jQuery('#pwgc-message').val();
    if (messageElement) {
        charsRemaining -= messageElement.length;
    }

    jQuery('#pwgc-message-characters-remaining').text(charsRemaining);
}

function pwgc_checkout_redeem_gift_card(redeemButton) {
    var errorContainer = jQuery('#pwgc-redeem-error');
    var cardNumber = jQuery('#pwgc-redeem-gift-card-number');

    errorContainer.text('');
    redeemButton.attr('data-apply-text', redeemButton.attr('value')).attr('value', redeemButton.attr('data-wait-text')).prop('disabled', true);

    jQuery.post(pwgc.ajaxurl, {'action': 'pw-gift-cards-redeem', 'card_number': cardNumber.val(), 'security': pwgc.nonces.apply_gift_card}, function( result ) {
        if (result.success) {
            // We could hook into the cart's ajax calls, but for now we'll just reload.
            location.reload();
        } else {
            errorContainer.text(result.data.message);
            redeemButton.attr('value', redeemButton.attr('data-apply-text')).prop('disabled', false);
            cardNumber.focus();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            errorContainer.text(errorThrown);
        } else {
            errorContainer.text('Unknown Error');
        }
        redeemButton.attr('value', redeemButton.attr('data-apply-text')).prop('disabled', false);
        cardNumber.focus();
    });
}
