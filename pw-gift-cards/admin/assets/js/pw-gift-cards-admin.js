document.addEventListener("DOMContentLoaded", function(event) {

    pwgcAdminBalanceSearch();

    jQuery('#pwgc-balance-search-form').on('submit', function(e) {
        pwgcAdminBalanceSearch();

        e.preventDefault();
        return false;
    });

    if (jQuery('#pwgc-balance-search').val()) {
        pwgcAdminBalanceSearch();
    }

    jQuery('#pwgc-create-gift-card-form').on('submit', function(e) {

        var form = jQuery(this);
        var submitButton = jQuery('#pwgc-create-gift-card-button');
        var amount = jQuery('#pwgc-create-amount').val();
        var quantity = jQuery('#pwgc-create-quantity').val();
        var expirationDate = jQuery('#pwgc-create-expiration-date').val();
        var note = jQuery('#pwgc-create-note').val();
        var fromString = jQuery('#pwgc-from').val();
        var recipient = jQuery('#pwgc-recipient').val();
        var messageContainer = jQuery('#pwgc-create-gift-card-message');

        submitButton.prop('disabled', true);
        messageContainer.html('<i class="fas fa-cog fa-spin fa-3x"></i>');

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-create_gift_card', 'amount': amount, 'quantity': quantity, 'expiration_date': expirationDate, 'note': note, 'from': fromString, 'recipient': recipient, 'security': pwgc.nonces.create_gift_card}, function(result) {
            jQuery('#pwgc-create-search-results').html(result.html);
            submitButton.prop('disabled', false);
            messageContainer.html('');
            pwgcAdminLoadBalanceSummary();
            form.trigger('reset');

        }).fail(function(xhr, textStatus, errorThrown) {
            submitButton.prop('disabled', false);
            messageContainer.html('');

            if (errorThrown) {
                alert(errorThrown);
            } else {
                alert('Unknown Error');
            }
        });

        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-import-gift-cards-form').on('submit', function(e) {
        pwgcImportGiftCards(false);

        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-save-settings-form').on('submit', function(e) {
        var messageContainer = jQuery('#pwgc-save-settings-message');
        var saveButton = jQuery('#pwgc-save-settings-button');
        var form = jQuery('#pwgc-save-settings-form').serialize();

        saveButton.hide();
        messageContainer.html('<i class="fas fa-cog fa-spin fa-3x"></i>');

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-save_settings', 'form': form, 'security': pwgc.nonces.save_settings }, function(result) {
            saveButton.show();
            messageContainer.html(result.data.html);
            jQuery('.pwgc-summary-item-date').toggle(document.getElementById('pwgc_show_balances_by_date').checked);
        }).fail(function(xhr, textStatus, errorThrown) {
            saveButton.show();
            if (errorThrown) {
                messageContainer.html(errorThrown);
            } else {
                messageContainer.text('Unknown ajax error');
            }
        });

        e.preventDefault();
        return false;
    });

    if (jQuery('#pwgc-setup-container').length == 0) {
        jQuery('#pwgc-balance-search').focus();
    }

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-check-license'}, function(registration) {
        if (!registration.active) {
            jQuery('.pwgc-main-content').css('display', 'none');
            jQuery('#pwgc-activation-main').css('display', 'inline-block');
            jQuery('#pwgc-license-key').focus();

            if (registration.error !== '') {
                jQuery('.pwgc-activation-error').text(registration.error).removeClass('pwgc-hidden');
            }
        }
    });

    // Hide the default meta fields and prevent Enter from submitting the form.
    jQuery('#pwgc-license-key').keydown(function(e){
        if (e.keyCode == 13) {
            pwgcActivate();
            e.preventDefault();
            return false;
        }
    });

    jQuery('#pwgc-activate-license').click(function(e) {
        pwgcActivate();
        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-renew-dismiss').click(function(e) {
        jQuery('#pwgc-renew-container').hide();
        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-hide_renew_notice'});
    });

    jQuery('#pwgc-setup-create-product').click(function(e) {
        var button = jQuery(this);
        button.html('<i class="fas fa-cog fa-spin"></i>');

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-create_product', 'security': pwgc.nonces.create_product }, function(result) {
            if (result.success) {
                button.hide();
                jQuery('#pwgc-setup-create-product-success').show();
            } else {
                button.text(button.attr('data-text'));
                jQuery('#pwgc-setup-error').text('Unknown error');
            }
        }).fail(function(xhr, textStatus, errorThrown) {
            button.text(button.attr('data-text'));
            if (errorThrown) {
                jQuery('#pwgc-setup-error').text(errorThrown);
            } else {
                jQuery('#pwgc-setup-error').text('Unknown ajax error');
            }
        });

        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-setup-create-balance-page').click(function(e) {
        var button = jQuery(this);
        button.html('<i class="fas fa-cog fa-spin"></i>');

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-create_balance_page', 'security': pwgc.nonces.create_balance_page }, function(result) {
            if (result.success) {
                button.hide();
                jQuery('#pwgc-setup-create-balance-page-success').show();
            } else {
                button.text(button.attr('data-text'));
                jQuery('#pwgc-setup-error').text('Unknown error');
            }
        }).fail(function(xhr, textStatus, errorThrown) {
            button.text(button.attr('data-text'));
            if (errorThrown) {
                jQuery('#pwgc-setup-error').text(errorThrown);
            } else {
                jQuery('#pwgc-setup-error').text('Unknown ajax error');
            }
        });

        e.preventDefault();
        return false;
    });

    jQuery('.pwgc-dashboard-item').click(function(e) {
        jQuery('.pwgc-dashboard-item').removeClass('pwgc-dashboard-item-selected');
        jQuery(this).addClass('pwgc-dashboard-item-selected');
        var section = jQuery(this).attr('data-section');
        jQuery('.pwgc-section').hide();
        jQuery('#pwgc-section-' + section).show();
    });

    jQuery('#pwgc-design-selector').change(function(e) {
        pwgcSelectDesign();
        e.preventDefault();
        return false;
    });

    jQuery('#pwgc-add-design-button').click(function(e) {
        pwgcCreateDesign();
        e.preventDefault();
        return false;
    });

    jQuery('#pwgc_no_expiration_date').change(function() {
        if (this.checked) {
            jQuery('.pwgc-expiration-date-element').hide();
        } else {
            jQuery('.pwgc-expiration-date-element').show();
        }
    });
});

function pwgcDatePickerSelect( datepicker ) {
    var option         = jQuery( datepicker ).next().is( '.hasDatepicker' ) ? 'minDate' : 'maxDate',
        otherDateField = 'minDate' === option ? jQuery( datepicker ).next() : jQuery( datepicker ).prev(),
        date           = jQuery( datepicker ).datepicker( 'getDate' );

    jQuery( otherDateField ).datepicker( 'option', option, date );
    jQuery( datepicker ).change();
}

var pwgcPickrOptions = {
    theme: 'nano',

    swatches: [
        'rgba(244, 67, 54, 1)',
        'rgba(233, 30, 99, 0.95)',
        'rgba(156, 39, 176, 0.9)',
        'rgba(103, 58, 183, 0.85)',
        'rgba(63, 81, 181, 0.8)',
        'rgba(33, 150, 243, 0.75)',
        'rgba(3, 169, 244, 0.7)',
        'rgba(0, 188, 212, 0.7)',
        'rgba(0, 150, 136, 0.75)',
        'rgba(76, 175, 80, 0.8)',
        'rgba(139, 195, 74, 0.85)',
        'rgba(205, 220, 57, 0.9)',
        'rgba(255, 235, 59, 0.95)',
        'rgba(255, 193, 7, 1)'
    ],

    useAsButton: true,
    defaultRepresentation: 'HEX',

    components: {

        // Main components
        preview: true,
        opacity: true,
        hue: true,

        // Input / output Options
        interaction: {
            hex: false,
            rgba: false,
            hsla: false,
            hsva: false,
            cmyk: false,
            input: true,
            clear: false,
            cancel: true,
            save: true
        }
    }
};

function pwgcAssignColorPicker(formElement, designerElement, designerCssAttribute) {
    pwgcPickrOptions.el = document.querySelector(formElement);
    pwgcPickrOptions.default = pwgcPickrOptions.el.value;

    const giftCardColorPickr = Pickr.create(pwgcPickrOptions);
    giftCardColorPickr.on('save', (color, instance) => {
        instance.hide();
    }).on('change', (color, instance) => {
        jQuery(designerElement).css(designerCssAttribute, color.toHEXA() );
        jQuery(instance.options.el).val(color.toHEXA().toString(0));
        jQuery(instance.options.el).css('background-color', color.toHEXA().toString(0));
        jQuery(instance.options.el).css('color', color.toHEXA().toString(0));
        instance.applyColor(true);
    }).on('cancel', instance => {
        jQuery(designerElement).css(designerCssAttribute, instance.getSelectedColor().toHEXA() );
        instance.hide();
    });
}

function pwgcActivate() {
    jQuery('.pwgc-activation-error').text('');
    jQuery('#pwgc-activate-license').prop('disabled', true).val('Activating, please wait...');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-activation', 'license-key': jQuery('#pwgc-license-key').val() }, function(result) {
        if (result.active === true) {
            location.reload();
        } else {
            jQuery('.pwgc-activation-error').text(result.error);
            jQuery('#pwgc-activate-license').prop('disabled', false).val('Activate');
        }
    });
}

function pwgcAdminLoadBalanceSummary() {
    var balanceSummary = jQuery('#pwgc-balance-summary-container');
    var date = jQuery('#pwgc-balance-search-date');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-balance_summary', 'date': date.val(), 'security': pwgc.nonces.balance_summary}, function(result) {
        balanceSummary.html(result);
    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            balanceSummary.html(errorThrown);
        } else {
            balanceSummary.html('Unknown Error');
        }
    });
}

function pwgcAdminBalanceSearch() {
    jQuery('#pwgc-balance-search-results,#pwgc-balance-card-activity').text('');
    jQuery('#pwgc-balance-search-results').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    var submitButton = jQuery('#pwgc-balance-search-button');
    var dateSubmitButton = jQuery('#pwgc-balance-search-date-refresh');
    submitButton.prop('disabled', true);
    dateSubmitButton.prop('disabled', true);

    var searchTerms = jQuery('#pwgc-balance-search');
    var date = jQuery('#pwgc-balance-search-date');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-search', 'search_terms': searchTerms.val(), 'date': date.val(), 'security': pwgc.nonces.search}, function(result) {
        jQuery('#pwgc-balance-search-results').html(result.html);
        searchTerms.focus();
        submitButton.prop('disabled', false);
        dateSubmitButton.prop('disabled', false);
    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            alert(errorThrown);
        } else {
            alert('Unknown Error');
        }
        searchTerms.focus();
        submitButton.prop('disabled', false);
        dateSubmitButton.prop('disabled', false);
    });
}

function pwgcAdminGiftCardActivityLoadStart(row) {
    var buttonCell = row.find('.pwgc-search-result-buttons').first();
    var activity = buttonCell.find('.pwgc-balance-activity-container');
    if (activity.length == 0) {
        activity = jQuery('<div class="pwgc-balance-activity-container"></div>').appendTo(buttonCell);
    }
    activity.html('<i class="fas fa-cog fa-spin fa-2x"></i>');
}

function pwgcAdminGiftCardActivity(row) {
    pwgcAdminGiftCardActivityLoadStart(row);

    var cardNumber = row.attr('data-gift-card-number');
    var buttonCell = row.find('.pwgc-search-result-buttons').first();
    var activity = buttonCell.find('.pwgc-balance-activity-container');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-view_activity', 'card_number': cardNumber, 'security': pwgc.nonces.view_activity}, function(result) {
        activity.html(result.html);
    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            alert(errorThrown);
        } else {
            alert('Unknown Error');
        }
    });
}

function pwgcAdjustBalance(row, amount) {
    var note = prompt( pwgc.i18n.adjustment_note_prompt );
    if (note !== null) {
        pwgcAdminGiftCardActivityLoadStart(row);

        var cardNumber = row.attr('data-gift-card-number');
        var balance = row.find('.pwgc-search-result-balance');

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-adjustment', 'card_number': cardNumber, 'amount': amount, 'note': note, 'security': pwgc.nonces.adjustment}, function(result) {
            pwgcAdminGiftCardActivity(row);
            balance.html(result.balance);
        }).fail(function(xhr, textStatus, errorThrown) {
            if (errorThrown) {
                alert(errorThrown);
            } else {
                alert('Unknown ajax error');
            }
        });
    }
}

function pwgcSetExpirationDate(row) {
    var expirationDate = prompt(pwgc.i18n.prompt_for_expiration_date);

    if (expirationDate !== null) {
        var cardNumber = row.attr('data-gift-card-number');
        var expirationDateField = row.find('.pwgc-search-result-expiration-date');
        expirationDateField.html('<i class="fas fa-cog fa-spin fa-2x"></i>');

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-set_expiration_date', 'card_number': cardNumber, 'expiration_date': expirationDate, 'security': pwgc.nonces.expiration_date}, function(result) {
            expirationDateField.html(result.expiration_date);
        }).fail(function(xhr, textStatus, errorThrown) {
            if (errorThrown) {
                expirationDateField.html(errorThrown);
            } else {
                expirationDateField.html('Unknown ajax error');
            }
        });
    }
}

function pwgcEmailGiftCard(row) {
    var cardNumber = row.attr('data-gift-card-number');
    var originalTo = row.attr('data-original-to');
    var originalFrom = row.attr('data-original-from');
    var originalNote = row.attr('data-original-note');

    var emailAddress = prompt(pwgc.i18n.prompt_for_email_address, originalTo);
    if (emailAddress === null || emailAddress.trim() === '') { return; }

    var sender = prompt(pwgc.i18n.prompt_for_sender, originalFrom);
    if (sender === null || sender.trim() === '') { return; }

    var note = prompt(pwgc.i18n.prompt_for_note, originalNote);
    if (note === null) { return; }

    var emailButtonIcon = row.find('.pwgc-email .fas');
    emailButtonIcon.removeClass('fa-envelope').addClass('fa-cog').addClass('fa-spin');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-email_gift_card', 'card_number': cardNumber, 'email_address': emailAddress, 'from': sender, 'note': note, 'security': pwgc.nonces.email_gift_card}, function(result) {
        emailButtonIcon.removeClass('fa-cog').removeClass('fa-spin').addClass('fa-envelope');
        alert(pwgc.i18n.email_sent);
    }).fail(function(xhr, textStatus, errorThrown) {
        emailButtonIcon.removeClass('fa-cog').removeClass('fa-spin').addClass('fa-envelope');
        if (errorThrown) {
            alert(errorThrown);
        } else {
            alert('Unknown ajax error');
        }
    });
}

function pwgcDelete(row) {
    var cardNumber = row.attr('data-gift-card-number');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-delete', 'card_number': cardNumber, 'security': pwgc.nonces.delete}, function(result) {
        if (result.data.deleted) {
            jQuery('#pwgc-balance-search').val('');
            pwgcAdminBalanceSearch();
            alert(pwgc.i18n.gift_card_deleted_message);
        } else {
            row.find('.pwgc-buttons-inactive, .pwgc-inactive-card').removeClass('pwgc-hidden');
            row.find('.pwgc-buttons-active').addClass('pwgc-hidden');
            pwgcAdminLoadBalanceSummary();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            alert(errorThrown);
        } else {
            alert('Unknown ajax error');
        }
    });
}

function pwgcRestore(row) {
    var cardNumber = row.attr('data-gift-card-number');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-restore', 'card_number': cardNumber, 'security': pwgc.nonces.restore}, function(result) {
        row.find('.pwgc-buttons-inactive, .pwgc-inactive-card').addClass('pwgc-hidden');
        row.find('.pwgc-buttons-active').removeClass('pwgc-hidden');
        pwgcAdminLoadBalanceSummary();
    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            alert(errorThrown);
        } else {
            alert('Unknown ajax error');
        }
    });
}

function pwgcImportGiftCards(confirm) {
    var importResults = jQuery('#pwgc-import-results');
    var submitButton = jQuery('#pwgc-import-file-submit-button');
    var importFile = jQuery('#pwgc-import-file');
    var sendEmail = jQuery('#pwgc-admin-send-email').is(':checked');

    importResults.html('<i class="pwgc-wait fas fa-cog fa-spin fa-3x"></i>');
    submitButton.prop('disabled', true);
    importFile.prop('disabled', true);

    var files = importFile.prop('files');
    if (files.length > 0) {

        var formData = new FormData();

        formData.append('file', files[0]);
        formData.append('action', 'pw-gift-cards-import_gift_cards');
        if (confirm) {
            formData.append('confirm', true);
        }
        if (sendEmail) {
            formData.append('send_email', true);
        }

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            contentType: false,
            processData: false,
            data: formData,
            success: function (response) {
                if (response.success) {
                    importResults.html(response.data.html);
                    pwgcAdminLoadBalanceSummary();
                } else if ( response.data && response.data.message ) {
                    importResults.html('<div style="color: red;">Error ' + response.data.message + '</div>');
                } else {
                    importResults.html('<div style="color: red;">Unhandled Error</div>');
                }

                submitButton.prop('disabled', false);
                importFile.prop('disabled', false);
            },
            error: function (xhr, textStatus, errorThrown) {
                importResults.html('<div class="pwgc-import-error-message">Error: ' + errorThrown + '</div>');
                submitButton.prop('disabled', false);
                importFile.prop('disabled', false);
            }

        });
    }
}

function pwgcSelectDesign() {
    jQuery('#pwgc-designer-panel-container').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    var designId = jQuery('#pwgc-design-selector').val();
    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-select_design', 'design_id': designId, 'security': pwgc.nonces.select_design}, function(result) {
        jQuery('#pwgc-designer-panel-container').html(result.html);
    }).fail(function(xhr, textStatus, errorThrown) {
        if (!errorThrown) {
            errorThrown = 'Unknown Error';
        }
        jQuery('#pwgc-designer-panel-container').text(errorThrown);
    });
}

function pwgcCreateDesign() {
    jQuery('#pwgc-designer-panel-container').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-create_design', 'security': pwgc.nonces.create_design}, function(result) {
        jQuery('#pwgc-design-selector').append('<option value=' + result.design_id + '>' + result.name + '</option>');
        jQuery('#pwgc-design-selector option[value=' + result.design_id + ']').prop('selected', true).trigger('change');
    }).fail(function(xhr, textStatus, errorThrown) {
        if (!errorThrown) {
            errorThrown = 'Unknown Error';
        }
        jQuery('#pwgc-designer-panel-container').text(errorThrown);
    });
}

function pwgcSaveDesign() {
    var designSelector = jQuery("#pwgc-design-selector option:selected");
    var designName = jQuery('#pwgc-design-name');
    if (!designName.val()) {
        designName.val(designSelector.text());
    }

    var messageContainer = jQuery('#pwgc-save-design-message');
    var saveButton = jQuery('#pwgc-save-design-button');
    var form = jQuery('#pwgc-designer-form').serialize();

    saveButton.attr('disabled', true);
    messageContainer.clearQueue().html('<i class="fas fa-cog fa-spin fa-3x"></i>').show();

    jQuery.post(ajaxurl, {'action': 'pw-gift-cards-save_design', 'form': form, 'security': pwgc.nonces.save_design }, function(result) {
        designSelector.text(designName.val());
        saveButton.attr('disabled', false);
        messageContainer.html(result.html).delay(2000).fadeOut('slow');
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.attr('disabled', false);
        if (errorThrown) {
            messageContainer.html(errorThrown);
        } else {
            messageContainer.text('Unknown ajax error');
        }
    });
}

function pwgcDeleteDesign() {
    var designId = jQuery('#pwgc-design-id').val();
    if (confirm(pwgc.i18n.delete_design_prompt)) {

        jQuery('#pwgc-designer-panel-container').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-delete_design', 'design_id': designId, 'security': pwgc.nonces.delete_design}, function(result) {
            jQuery('#pwgc-design-selector option:selected').remove();
            if ( jQuery('#pwgc-design-selector option').length > 0 ) {
                jQuery('#pwgc-design-selector').trigger('change');
            } else {
                location.reload();
            }
        }).fail(function(xhr, textStatus, errorThrown) {
            if (!errorThrown) {
                errorThrown = 'Unknown Error';
            }
            jQuery('#pwgc-designer-panel-container').text(errorThrown);
        });
    }
}

function pwgcSendEmailDesignPreview() {
    var designId = jQuery('#pwgc-design-id').val();
    var emailAddress = prompt(pwgc.i18n.preview_email_notice + '\n\n' + pwgc.i18n.preview_email_prompt, jQuery('#pwgc-preview-email-button').attr('data-email'));
    if (emailAddress) {
        // Save it for later.
        jQuery('#pwgc-preview-email-button').attr('data-email', emailAddress);

        var previewButton = jQuery('#pwgc-preview-email-button');
        var messageContainer = jQuery('#pwgc-preview-email-message');

        previewButton.attr('disabled', true);
        messageContainer.clearQueue().html('<i class="fas fa-cog fa-spin"></i>').show();

        jQuery.post(ajaxurl, {'action': 'pw-gift-cards-preview_email', 'design_id': designId, 'email_address': emailAddress, 'security': pwgc.nonces.preview_email}, function(result) {
            messageContainer.html(result.html).delay(2000).fadeOut('slow');
            previewButton.attr('disabled', false);
        }).fail(function(xhr, textStatus, errorThrown) {
            previewButton.attr('disabled', false);
            if (!errorThrown) {
                errorThrown = 'Unknown Error';
            }
            messageContainer.html(errorThrown);
        });
    }
}
