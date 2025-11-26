<?php

return [
    'mode' => env('EVENT_BUS_MODE', 'kafka'), // kafka, local
    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),
    'client_id' => env('KAFKA_CLIENT_ID', 'laravmo-app'),
    'consumer_group_id' => env('KAFKA_CONSUMER_GROUP', 'laravmo-consumers'),
    'security_protocol' => env('KAFKA_SECURITY_PROTOCOL', 'PLAINTEXT'),
    'topics' => [
        'user_created' => env('KAFKA_TOPIC_USERS', 'users.created'),
        'order_created' => env('KAFKA_TOPIC_ORDERS', 'orders.created'),
        'payment_completed' => env('KAFKA_TOPIC_PAYMENTS', 'payments.completed'),
    ],
];

