/*
 * Copyright (c) 2018, Ryo Currency Project
*/
function ryo_showNotification(message, type='success') {
    var toast = jQuery('<div class="' + type + '"><span>' + message + '</span></div>');
    jQuery('#ryo_toast').append(toast);
    toast.animate({ "right": "12px" }, "fast");
    setInterval(function() {
        toast.animate({ "right": "-400px" }, "fast", function() {
            toast.remove();
        });
    }, 2500)
}
function ryo_showQR(show=true) {
    jQuery('#ryo_qr_code_container').toggle(show);
}
function ryo_fetchDetails() {
    var data = {
        '_': jQuery.now(),
        'order_id': ryo_details.order_id
    };
    jQuery.get(ryo_ajax_url, data, function(response) {
        if (typeof response.error !== 'undefined') {
            console.log(response.error);
        } else {
            ryo_details = response;
            ryo_updateDetails();
        }
    });
}

function ryo_updateDetails() {

    var details = ryo_details;

    jQuery('#ryo_payment_messages').children().hide();
    switch(details.status) {
        case 'unpaid':
            jQuery('.ryo_payment_unpaid').show();
            jQuery('.ryo_payment_expire_time').html(details.order_expires);
            break;
        case 'partial':
            jQuery('.ryo_payment_partial').show();
            jQuery('.ryo_payment_expire_time').html(details.order_expires);
            break;
        case 'paid':
            jQuery('.ryo_payment_paid').show();
            jQuery('.ryo_confirm_time').html(details.time_to_confirm);
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'confirmed':
            jQuery('.ryo_payment_confirmed').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired':
            jQuery('.ryo_payment_expired').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired_partial':
            jQuery('.ryo_payment_expired_partial').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
    }

    jQuery('#ryo_exchange_rate').html('1 Ryo = '+details.rate_formatted+' '+details.currency);
    jQuery('#ryo_total_amount').html(details.amount_total_formatted);
    jQuery('#ryo_total_paid').html(details.amount_paid_formatted);
    jQuery('#ryo_total_due').html(details.amount_due_formatted);

    jQuery('#ryo_integrated_address').html(details.integrated_address);

    if(ryo_show_identicon) {
        var icon = blockies.create({
            scale: 5,
            seed: details.integrated_address
        });
        jQuery('#ryo_identicon').html('').append(icon);
    }

    if(ryo_show_qr) {
        var qr = jQuery('#ryo_qr_code').html('');
        new QRCode(qr.get(0), details.qrcode_uri);
    }

    if(details.txs.length) {
        jQuery('#ryo_tx_table').show();
        jQuery('#ryo_tx_none').hide();
        jQuery('#ryo_tx_table tbody').html('');
        for(var i=0; i < details.txs.length; i++) {
            var tx = details.txs[i];
            var height = tx.height == 0 ? 'N/A' : tx.height;
            var row = ''+
                '<tr>'+
                '<td style="word-break: break-all">'+
                '<a href="'+ryo_explorer_url+'/tx/'+tx.txid+'" target="_blank">'+tx.txid+'</a>'+
                '</td>'+
                '<td>'+height+'</td>'+
                '<td>'+tx.amount_formatted+' Ryo</td>'+
                '</tr>';

            jQuery('#ryo_tx_table tbody').append(row);
        }
    } else {
        jQuery('#ryo_tx_table').hide();
        jQuery('#ryo_tx_none').show();
    }

    // Show state change notifications
    var new_txs = details.txs;
    var old_txs = ryo_order_state.txs;
    if(new_txs.length != old_txs.length) {
        for(var i = 0; i < new_txs.length; i++) {
            var is_new_tx = true;
            for(var j = 0; j < old_txs.length; j++) {
                if(new_txs[i].txid == old_txs[j].txid && new_txs[i].amount == old_txs[j].amount) {
                    is_new_tx = false;
                    break;
                }
            }
            if(is_new_tx) {
                ryo_showNotification('Transaction received for '+new_txs[i].amount_formatted+' Ryo');
            }
        }
    }

    if(details.status != ryo_order_state.status) {
        switch(details.status) {
            case 'paid':
                ryo_showNotification('Your order has been paid in full');
                break;
            case 'confirmed':
                ryo_showNotification('Your order has been confirmed');
                break;
            case 'expired':
            case 'expired_partial':
                ryo_showNotification('Your order has expired', 'error');
                break;
        }
    }

    ryo_order_state = {
        status: ryo_details.status,
        txs: ryo_details.txs
    };

}
jQuery(document).ready(function($) {
    if (typeof ryo_details !== 'undefined') {
        ryo_order_state = {
            status: ryo_details.status,
            txs: ryo_details.txs
        };
        setInterval(ryo_fetchDetails, 30000);
        ryo_updateDetails();
        new ClipboardJS('.clipboard').on('success', function(e) {
            e.clearSelection();
            if(e.trigger.disabled) return;
            switch(e.trigger.getAttribute('data-clipboard-target')) {
                case '#ryo_integrated_address':
                    ryo_showNotification('Copied destination address!');
                    break;
                case '#ryo_total_due':
                    ryo_showNotification('Copied total amount due!');
                    break;
            }
            e.clearSelection();
        });
    }
});