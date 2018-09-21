# Ryo Payments Gateway for WooCommerce

![Ryo Payments Woocommerce Gateway](/assets/images/ryo-payments-woocommerce-logo.png?raw=true "Ryo Payments Woocommerce Gateway") 

## Features

* Payment validation done through either `ryo-wallet-rpc` or the [Ryo blockchain explorer](https://explorer.ryo-currency.com/).
* Validates payments with `cron`, so does not require users to stay on the order confirmation page for their order to validate.
* Order status updates are done through AJAX instead of Javascript page reloads.
* Customers can pay with multiple transactions and are notified as soon as transactions hit the mempool.
* Configurable block confirmations, from `0` for zero confirm to `60` for high ticket purchases.
* Live price updates every minute; total amount due is locked in after the order is placed for a configurable amount of time (default 60 minutes) so the price does not change after order has been made.
* Hooks into emails, order confirmation page, customer order history page, and admin order details page.
* View all payments received to your wallet with links to the blockchain explorer and associated orders.
* Optionally display all prices on your store in terms of Ryo.
* Shortcodes! Display exchange rates in numerous currencies.

## Requirements

* Ryo wallet to receive payments - [GUI](https://github.com/ryo-currency/ryo-wallet/releases) - [CLI](https://github.com/ryo-currency/ryo-currency/releases) - [Paper](https://ryo-currency.com/paper-wallet/)
* [BCMath](http://php.net/manual/en/book.bc.php) - A PHP extension used for arbitrary precision maths

## Installing the plugin

* Download the plugin from the [releases page](https://github.com/ryo-currency/ryo-payments-woocommerce-gateway/releases) or clone with `git clone https://github.com/ryo-currency/ryo-payments-woocommerce-gateway.git`
* Unzip or place the `ryo-payments-woocommerce-gateway` folder in the `wp-content/plugins` directory.
* Activate "Ryo Payments Woocommerce Gateway" in your WordPress admin dashboard.
* It is highly recommended that you use native cronjobs instead of WordPress's "Poor Man's Cron" by adding `define('DISABLE_WP_CRON', true);` into your `wp-config.php` file and adding `* * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1` to your crontab.

## Option 1: Use your wallet address and viewkey

This is the easiest way to start accepting Ryo on your website. You'll need:

* Your Ryo wallet address starting with `RYoL`
* Your wallet's secret viewkey

Then simply select the `viewkey` option in the settings page and paste your address and viewkey. You're all set!

Note on privacy: when you validate transactions with your private viewkey, your viewkey is sent to (but not stored on) explorer.ryo-currency.com over HTTPS. This could potentially allow an attacker to see your incoming, but not outgoing, transactions if they were to get his hands on your viewkey. Even if this were to happen, your funds would still be safe and it would be impossible for somebody to steal your money. For maximum privacy use your own `ryo-wallet-rpc` instance.

## Option 2: Using `ryo-wallet-rpc`

The most secure way to accept Ryo on your website. You'll need:

* Root access to your webserver
* Latest [Ryo-currency binaries](https://github.com/ryo-currency/ryo-currency/releases)

After downloading (or compiling) the Ryo binaries on your server, install the [systemd unit files](https://github.com/ryo-currency/ryo-payments-woocommerce-gateway/tree/master/assets/systemd-unit-files) or run `ryod` and `ryo-wallet-rpc` with `screen` or `tmux`. You can skip running `ryod` by using a remote node with `ryo-wallet-rpc` by adding `-daemon-address geo.ryoblocks.com:12211` to the `ryo-wallet-rpc.service` file.

Note on security: using this option, while the most secure, requires you to run the Ryo wallet RPC program on your server. Best practice for this is to use a view-only wallet since otherwise your server would be running a hot-wallet and a security breach could allow hackers to empty your funds. 

## Configuration

* `Enable / Disable` - Turn on or off Ryo payments. (Default: Disable)
* `Title` - Name of the payment gateway as displayed to the customer. (Default: Ryo Payments)
* `Discount for using Ryo` - Percentage discount applied to orders for paying with Ryo. Can also be negative to apply a surcharge. (Default: 0)
* `Order valid time` - Number of seconds after order is placed that the transaction must be seen in the mempool. (Default: 3600 [1 hour])
* `Number of confirmations` - Number of confirmations the transaction must recieve before the order is marked as complete. Use `0` for nearly instant confirmation. (Default: 5)
* `Confirmation Type` - Confirm transactions with either your viewkey, or by using `ryo-wallet-rpc`. (Default: viewkey)
* `Ryo Address` (if confirmation type is viewkey) - Your public Ryo address starting with RYoL. Kurz addresses are not supported. (No default)
* `Secret Viewkey` (if confirmation type is viewkey) - Your *private* viewkey (No default)
* `Ryo wallet RPC Host/IP` (if confirmation type is `ryo-wallet-rpc`) - IP address where the wallet rpc is running. It is highly discouraged to run the wallet anywhere other than the local server! (Default: 127.0.0.1)
* `Ryo wallet RPC port` (if confirmation type is `ryo-wallet-rpc`) - Port the wallet rpc is bound to with the `--rpc-bind-port` argument. (Default 12215)
* `Testnet` - Check this to change the blockchain explorer links to the testnet explorer. (Default: unchecked)
* `Show QR Code` - Show payment QR codes. There is no Ryo software that can read QR codes at this time (Default: unchecked)
* `Show Identicon` - Show address specific "identicons". These same icons are shown in the Ryo Wallet Atom software and helps users confirm they are sending to the correct address. (Default: checked)
* `Show Prices in Ryo` - Convert all prices on the frontend to Ryo. Experimental feature, only use if you do not accept any other payment option. (Default: unchecked)
* `Display Decimals` (if show prices in Ryo is enabled) - Number of decimals to round prices to on the frontend. The final order amount will not be rounded and will be displayed down to the nanoRyo. (Default: 2)

## Shortcodes

This plugin makes available two shortcodes that you can use in your theme.

#### Live price shortcode

This will display the price of Ryo in the selected currency. If no currency is provided, the store's default currency will be used.

```
[ryo-price]
[ryo-price currency="BTC"]
[ryo-price currency="USD"]
[ryo-price currency="CAD"]
[ryo-price currency="EUR"]
[ryo-price currency="GBP"]
```
Will display:
```
1 Ryo = 0.07672 USD
1 Ryo = 0.00001168 BTC
1 Ryo = 0.07672 USD
1 Ryo = 0.10620 CAD
1 Ryo = 0.06504 EUR
1 Ryo = 0.05771 GBP
```


#### Ryo accepted here badge

This will display a badge showing that you accept Ryo-currency.

`[ryo-accepted-here]`

![Ryo Accepted Here](/assets/images/ryo-accepted-here.png?raw=true "Ryo Accepted Here")  

## Screenshots

![Ryo Payments Customer Order Page](/assets/images/screenshots/ryo-payments-woocommerce-customer-payment-instructions.png?raw=true "Ryo Payments Customer Order Page")  
User-friendly payment page with responsive notifications as soon as the customer's order status has changed.

![Ryo Payments Admin Order Page](/assets/images/screenshots/ryo-payments-woocommerce-admin-order-page.png?raw=true "Ryo Payments Admin Order Page")  
Admin order page shows information about the customer's payments, including exchange rate and individual transaction details.

![Ryo Payments Admin Transaction List](/assets/images/screenshots/ryo-payments-woocommerce-admin-transaction-list.png?raw=true "Ryo Payments Admin Transaction List")  
Admin transaction list shows all payments made to your Ryo wallet with links to the blockchain explorer and the order it is associated with.

![Ryo Payments Admin Configuration Page](/assets/images/screenshots/ryo-payments-woocommerce-admin-settings.png?raw=true "Ryo Payments Admin Configuration Page")  
Admin configuration page lets you fully customize the plugin.

## Credits

Credit is due to [@cryptochangements34](https://github.com/cryptochangements34) and [@SerHack](https://github.com/serhack) for their [monerowp](https://github.com/monero-integrations/monerowp) plugin that this is based on.
