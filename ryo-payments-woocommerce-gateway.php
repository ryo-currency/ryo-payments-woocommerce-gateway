<?php
/*
Plugin Name: Ryo Payments Woocommerce Gateway
Plugin URI: https://github.com/ryo-currency/ryo-payments-woocommerce-gateway
Description: Extends WooCommerce by adding a Ryo-currency Gateway
Version: 3.0.0
Tested up to: 4.9.8
Author: mosu-forge, SerHack
Author URI: https://ryo-currency.com/
*/
// This code isn't for Dark Net Markets, please report them to Authority!

defined( 'ABSPATH' ) || exit;

// Constants, you can edit these if you fork this repo
define('RYO_GATEWAY_MAINNET_EXPLORER_URL', 'https://explorer.ryo-currency.com');
define('RYO_GATEWAY_TESTNET_EXPLORER_URL', 'https://tnexp.ryo-currency.com');
define('RYO_GATEWAY_ADDRESS_PREFIX', 0x2ce192);            // RYoL
define('RYO_GATEWAY_ADDRESS_PREFIX_INTEGRATED', 0x2de192); // RYoN
define('RYO_GATEWAY_ATOMIC_UNITS', 9);
define('RYO_GATEWAY_ATOMIC_UNIT_THRESHOLD', 10); // Amount payment can be under in atomic units and still be valid
define('RYO_GATEWAY_DIFFICULTY_TARGET', 240);

// Do not edit these constants
define('RYO_GATEWAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RYO_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RYO_GATEWAY_ATOMIC_UNITS_POW', pow(10, RYO_GATEWAY_ATOMIC_UNITS));
define('RYO_GATEWAY_ATOMIC_UNITS_SPRINTF', '%.'.RYO_GATEWAY_ATOMIC_UNITS.'f');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'ryo_init', 1);
function ryo_init() {

    // If the class doesn't exist (== WooCommerce isn't installed), return NULL
    if (!class_exists('WC_Payment_Gateway')) return;

    // If we made it this far, then include our Gateway Class
    require_once('include/class-ryo-gateway.php');

    // Create a new instance of the gateway so we have static variables set up
    new Ryo_Gateway($add_action=false);

    // Include our Admin interface class
    require_once('include/admin/class-ryo-admin-interface.php');

    add_filter('woocommerce_payment_gateways', 'ryo_gateway');
    function ryo_gateway($methods) {
        $methods[] = 'Ryo_Gateway';
        return $methods;
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ryo_payment');
    function ryo_payment($links) {
        $plugin_links = array(
            '<a href="'.admin_url('admin.php?page=ryo_gateway_settings').'">'.__('Settings', 'ryo_gateway').'</a>'
        );
        return array_merge($plugin_links, $links);
    }

    add_filter('cron_schedules', 'ryo_cron_add_one_minute');
    function ryo_cron_add_one_minute($schedules) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display' => __('Once every minute', 'ryo_gateway')
        );
        return $schedules;
    }

    add_action('wp', 'ryo_activate_cron');
    function ryo_activate_cron() {
        if(!wp_next_scheduled('ryo_update_event')) {
            wp_schedule_event(time(), 'one_minute', 'ryo_update_event');
        }
    }

    add_action('ryo_update_event', 'ryo_update_event');
    function ryo_update_event() {
        Ryo_Gateway::do_update_event();
    }

    add_action('woocommerce_thankyou_'.Ryo_Gateway::get_id(), 'ryo_order_confirm_page');
    add_action('woocommerce_order_details_after_order_table', 'ryo_order_page');
    add_action('woocommerce_email_after_order_table', 'ryo_order_email');

    function ryo_order_confirm_page($order_id) {
        Ryo_Gateway::customer_order_page($order_id);
    }
    function ryo_order_page($order) {
        if(!is_wc_endpoint_url('order-received'))
            Ryo_Gateway::customer_order_page($order);
    }
    function ryo_order_email($order) {
        Ryo_Gateway::customer_order_email($order);
    }

    add_action('wc_ajax_ryo_gateway_payment_details', 'ryo_get_payment_details_ajax');
    function ryo_get_payment_details_ajax() {
        Ryo_Gateway::get_payment_details_ajax();
    }

    add_filter('woocommerce_currencies', 'ryo_add_currency');
    function ryo_add_currency($currencies) {
        $currencies['Ryo'] = __('Ryo', 'ryo_gateway');
        return $currencies;
    }

    add_filter('woocommerce_currency_symbol', 'ryo_add_currency_symbol', 10, 2);
    function ryo_add_currency_symbol($currency_symbol, $currency) {
        switch ($currency) {
        case 'Ryo':
            $currency_symbol = 'Ryo';
            break;
        }
        return $currency_symbol;
    }

    if(Ryo_Gateway::use_ryo_price()) {

        // This filter will replace all prices with amount in Ryo (live rates)
        add_filter('wc_price', 'ryo_live_price_format', 10, 3);
        function ryo_live_price_format($price_html, $price_float, $args) {
            if(!isset($args['currency']) || !$args['currency']) {
                global $woocommerce;
                $currency = strtoupper(get_woocommerce_currency());
            } else {
                $currency = strtoupper($args['currency']);
            }
            return Ryo_Gateway::convert_wc_price($price_float, $currency);
        }

        // These filters will replace the live rate with the exchange rate locked in for the order
        // We must be careful to hit all the hooks for price displays associated with an order,
        // else the exchange rate can change dynamically (which it should not for an order)
        add_filter('woocommerce_order_formatted_line_subtotal', 'ryo_order_item_price_format', 10, 3);
        function ryo_order_item_price_format($price_html, $item, $order) {
            return Ryo_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_formatted_order_total', 'ryo_order_total_price_format', 10, 2);
        function ryo_order_total_price_format($price_html, $order) {
            return Ryo_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_order_item_totals', 'ryo_order_totals_price_format', 10, 3);
        function ryo_order_totals_price_format($total_rows, $order, $tax_display) {
            foreach($total_rows as &$row) {
                $price_html = $row['value'];
                $row['value'] = Ryo_Gateway::convert_wc_price_order($price_html, $order);
            }
            return $total_rows;
        }

    }

    add_action('wp_enqueue_scripts', 'ryo_enqueue_scripts');
    function ryo_enqueue_scripts() {
        if(Ryo_Gateway::use_ryo_price())
            wp_dequeue_script('wc-cart-fragments');
        if(Ryo_Gateway::use_qr_code())
            wp_enqueue_script('ryo-qr-code', RYO_GATEWAY_PLUGIN_URL.'assets/js/qrcode.min.js');
        if(Ryo_Gateway::use_identicons())
            wp_enqueue_script('ryo-identicon', RYO_GATEWAY_PLUGIN_URL.'assets/js/blockies.min.js');

        wp_enqueue_script('ryo-clipboard-js', RYO_GATEWAY_PLUGIN_URL.'assets/js/clipboard.min.js');
        wp_enqueue_script('ryo-gateway', RYO_GATEWAY_PLUGIN_URL.'assets/js/ryo-gateway-order-page.js');
        wp_enqueue_style('ryo-gateway', RYO_GATEWAY_PLUGIN_URL.'assets/css/ryo-gateway-order-page.css');
    }

    // [ryo-price currency="USD"]
    // currency: BTC, GBP, etc
    // if no none, then default store currency
    function ryo_price_func( $atts ) {
        global  $woocommerce;
        $a = shortcode_atts( array(
            'currency' => get_woocommerce_currency()
        ), $atts );

        $currency = strtoupper($a['currency']);
        $rate = Ryo_Gateway::get_live_rate($currency);
        if($currency == 'BTC')
            $rate_formatted = sprintf('%.8f', $rate / 1e8);
        else
            $rate_formatted = sprintf('%.5f', $rate / 1e8);

        return "<span class=\"ryo-price\">1 Ryo = $rate_formatted $currency</span>";
    }
    add_shortcode('ryo-price', 'ryo_price_func');


    // [ryo-accepted-here]
    function ryo_accepted_func() {
        return '<img src="'.RYO_GATEWAY_PLUGIN_URL.'assets/images/ryo-accepted-here.png" />';
    }
    add_shortcode('ryo-accepted-here', 'ryo_accepted_func');

}

register_deactivation_hook(__FILE__, 'ryo_deactivate');
function ryo_deactivate() {
    $timestamp = wp_next_scheduled('ryo_update_event');
    wp_unschedule_event($timestamp, 'ryo_update_event');
}

register_activation_hook(__FILE__, 'ryo_install');
function ryo_install() {
    global $wpdb;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "ryo_gateway_quotes";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               order_id BIGINT(20) UNSIGNED NOT NULL,
               payment_id VARCHAR(16) DEFAULT '' NOT NULL,
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               paid TINYINT NOT NULL DEFAULT 0,
               confirmed TINYINT NOT NULL DEFAULT 0,
               pending TINYINT NOT NULL DEFAULT 1,
               created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (order_id)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "ryo_gateway_quotes_txids";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               payment_id VARCHAR(16) DEFAULT '' NOT NULL,
               txid VARCHAR(64) DEFAULT '' NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               height MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
               PRIMARY KEY (id),
               UNIQUE KEY (payment_id, txid, amount)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "ryo_gateway_live_rates";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (currency)
               ) $charset_collate;";
        dbDelta($sql);
    }
}
