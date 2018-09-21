<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled' => array(
        'title' => __('Enable / Disable', 'ryo_gateway'),
        'label' => __('Enable this payment gateway', 'ryo_gateway'),
        'type' => 'checkbox',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'ryo_gateway'),
        'type' => 'text',
        'desc_tip' => __('Payment title the customer will see during the checkout process.', 'ryo_gateway'),
        'default' => __('Ryo Payments', 'ryo_gateway')
    ),
    'description' => array(
        'title' => __('Description', 'ryo_gateway'),
        'type' => 'textarea',
        'desc_tip' => __('Payment description the customer will see during the checkout process.', 'ryo_gateway'),
        'default' => __('Pay securely using Ryo-currency. You will be provided payment details after checkout.', 'ryo_gateway')
    ),
    'discount' => array(
        'title' => __('Discount for using Ryo', 'ryo_gateway'),
        'desc_tip' => __('Provide a discount to your customers for making a private payment with Ryo', 'ryo_gateway'),
        'description' => __('Enter a percentage discount (i.e. 5 for 5%) or leave this empty if you do not wish to provide a discount', 'ryo_gateway'),
        'type' => __('number'),
        'default' => '0'
    ),
    'valid_time' => array(
        'title' => __('Order valid time', 'ryo_gateway'),
        'desc_tip' => __('Amount of time order is valid before expiring', 'ryo_gateway'),
        'description' => __('Enter the number of seconds that the funds must be received in after order is placed. 3600 seconds = 1 hour', 'ryo_gateway'),
        'type' => __('number'),
        'default' => '3600'
    ),
    'confirms' => array(
        'title' => __('Number of confirmations', 'ryo_gateway'),
        'desc_tip' => __('Number of confirms a transaction must have to be valid', 'ryo_gateway'),
        'description' => __('Enter the number of confirms that transactions must have. Enter 0 to zero-confim. Each confirm will take approximately four minutes', 'ryo_gateway'),
        'type' => __('number'),
        'default' => '5'
    ),
    'confirm_type' => array(
        'title' => __('Confirmation Type', 'ryo_gateway'),
        'desc_tip' => __('Select the method for confirming transactions', 'ryo_gateway'),
        'description' => __('Select the method for confirming transactions', 'ryo_gateway'),
        'type' => 'select',
        'options' => array(
            'viewkey'        => __('viewkey', 'ryo_gateway'),
            'ryo-wallet-rpc' => __('ryo-wallet-rpc', 'ryo_gateway')
        ),
        'default' => 'viewkey'
    ),
    'ryo_address' => array(
        'title' => __('Ryo Address', 'ryo_gateway'),
        'label' => __('Useful for people that have not a daemon online'),
        'type' => 'text',
        'desc_tip' => __('Ryo Wallet Address (RYoL)', 'ryo_gateway')
    ),
    'viewkey' => array(
        'title' => __('Secret Viewkey', 'ryo_gateway'),
        'label' => __('Secret Viewkey'),
        'type' => 'text',
        'desc_tip' => __('Your secret Viewkey', 'ryo_gateway')
    ),
    'daemon_host' => array(
        'title' => __('Ryo wallet RPC Host/IP', 'ryo_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the Daemon Host/IP to authorize the payment with', 'ryo_gateway'),
        'default' => '127.0.0.1',
    ),
    'daemon_port' => array(
        'title' => __('Ryo wallet RPC port', 'ryo_gateway'),
        'type' => __('number'),
        'desc_tip' => __('This is the Wallet RPC port to authorize the payment with', 'ryo_gateway'),
        'default' => '12215',
    ),
    'testnet' => array(
        'title' => __(' Testnet', 'ryo_gateway'),
        'label' => __(' Check this if you are using testnet ', 'ryo_gateway'),
        'type' => 'checkbox',
        'description' => __('Advanced usage only', 'ryo_gateway'),
        'default' => 'no'
    ),
    'show_qr' => array(
        'title' => __('Show QR Code', 'ryo_gateway'),
        'label' => __('Show QR Code', 'ryo_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to show a QR code after checkout with payment details.'),
        'default' => 'no'
    ),
    'show_identicon' => array(
        'title' => __('Show Identicon', 'ryo_gateway'),
        'label' => __('Show Identicon', 'ryo_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to an identicon after checkout with payment details.'),
        'default' => 'yes'
    ),
    'use_ryo_price' => array(
        'title' => __('Show Prices in Ryo', 'ryo_gateway'),
        'label' => __('Show Prices in Ryo', 'ryo_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to convert ALL prices on the frontend to Ryo (experimental)'),
        'default' => 'no'
    ),
    'use_ryo_price_decimals' => array(
        'title' => __('Display Decimals', 'ryo_gateway'),
        'type' => __('number'),
        'description' => __('Number of decimal places to display on frontend. Upon checkout exact price will be displayed.'),
        'default' => 2,
    ),
);
