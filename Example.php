<?php

require('./Comkort/Adapter.php');

$public_key = '[YOUR PUBLIC KEY]';
$secret_key = '[YOUR SECRET KEY]';

$adapter = new Comkort_Adapter($secret_key, $public_key);

$adapter->api_query('order/buy', 'POST', array(
    'market_alias' => 'ppc_ltc',
    'amount' => 0.0001,
    'price' => 0.0001
));

// List all orders for specified market
$result = $adapter->api_query('order/list', 'POST', array(
    'market_alias' => 'ppc_ltc',
));

$result = json_decode($result, true);
$orders = isset($result['orders']) && is_array($result['orders']) ? $result['orders'] : array();

// Cancel each order
foreach($orders as $order) {
    if(!isset($order['id']))
        continue;
    $result = $adapter->api_query('order/cancel', 'POST', array(
        'order_id' => $order['id']
    ));
}

// Should return empty array
$result = $adapter->api_query('order/list', 'POST', array(
    'market_alias' => 'ppc_ltc',
));