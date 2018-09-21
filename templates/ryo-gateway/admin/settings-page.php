<?php foreach($errors as $error): ?>
<div class="error"><p><strong>Ryo Payments Error</strong>: <?php echo $error; ?></p></div>
<?php endforeach; ?>

<h1>Ryo Payments Settings</h1>

<?php if($confirm_type === 'ryo-wallet-rpc'): ?>
<div style="border:1px solid #ddd;padding:5px 10px;">
    <?php
         echo 'Wallet height: ' . $balance['height'] . '</br>';
         echo 'Your balance is: ' . $balance['balance'] . '</br>';
         echo 'Unlocked balance: ' . $balance['unlocked_balance'] . '</br>';
         ?>
</div>
<?php endif; ?>

<table class="form-table">
    <?php echo $settings_html ?>
</table>

<h4><a href="https://github.com/ryo-currency/ryo-payments-woocommerce-gateway/blob/master/README.md">Learn more about using the Ryo payment gateway</a></h4>

<script>
function ryoUpdateFields() {
    var confirmType = jQuery("#woocommerce_ryo_gateway_confirm_type").val();
    if(confirmType == "ryo-wallet-rpc") {
        jQuery("#woocommerce_ryo_gateway_ryo_address").closest("tr").hide();
        jQuery("#woocommerce_ryo_gateway_viewkey").closest("tr").hide();
        jQuery("#woocommerce_ryo_gateway_daemon_host").closest("tr").show();
        jQuery("#woocommerce_ryo_gateway_daemon_port").closest("tr").show();
    } else {
        jQuery("#woocommerce_ryo_gateway_ryo_address").closest("tr").show();
        jQuery("#woocommerce_ryo_gateway_viewkey").closest("tr").show();
        jQuery("#woocommerce_ryo_gateway_daemon_host").closest("tr").hide();
        jQuery("#woocommerce_ryo_gateway_daemon_port").closest("tr").hide();
    }
    var useRyoPrices = jQuery("#woocommerce_ryo_gateway_use_ryo_price").is(":checked");
    if(useRyoPrices) {
        jQuery("#woocommerce_ryo_gateway_use_ryo_price_decimals").closest("tr").show();
    } else {
        jQuery("#woocommerce_ryo_gateway_use_ryo_price_decimals").closest("tr").hide();
    }
}
ryoUpdateFields();
jQuery("#woocommerce_ryo_gateway_confirm_type").change(ryoUpdateFields);
jQuery("#woocommerce_ryo_gateway_use_ryo_price").change(ryoUpdateFields);
</script>

<style>
#woocommerce_ryo_gateway_ryo_address,
#woocommerce_ryo_gateway_viewkey {
    width: 100%;
}
</style>