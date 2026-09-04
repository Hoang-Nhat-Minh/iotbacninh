<?php

declare(strict_types=1);

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Repositories\MemoryRepository;

return [

    /*
    |--------------------------------------------------------------------------
    | Default MQTT Connection
    |--------------------------------------------------------------------------
    |
    | This setting defines the default MQTT connection to use when none is specified.
    | The name must match one of the connections configured below.
    |
    */

    'default_connection' => 'default',

    /*
    |--------------------------------------------------------------------------
    | MQTT Connections
    |--------------------------------------------------------------------------
    |
    | Configuration for different MQTT broker connections.
    |
    */

    'connections' => [

        'default' => [

            // Host and Port of the Mosquitto MQTT broker
            'host' => env('MQTT_HOST', '127.0.0.1'),
            'port' => (int) env('MQTT_PORT', 1883),

            // MQTT protocol version (MQTT 3.1 or 3.1.1)
            'protocol' => MqttClient::MQTT_3_1_1,

            // Client identifier used by Laravel backend
            'client_id' => env('MQTT_CLIENT_ID', 'laravel_backend_core'),

            // Clean session flag
            'use_clean_session' => (bool) env('MQTT_CLEAN_SESSION', false),

            // Repository for QoS 1 and 2 pending messages
            'repository' => MemoryRepository::class,

            // Logging settings - TẮT ngầm định để tránh spam file log mỗi mili-giây
            'enable_logging' => (bool) env('MQTT_ENABLE_LOGGING', false),
            'log_channel' => env('MQTT_LOG_CHANNEL', null),

            // All connection settings required by php-mqtt/laravel-client
            'connection_settings' => [

                // Authentication credentials
                'auth' => [
                    'username' => env('MQTT_USERNAME'),
                    'password' => env('MQTT_PASSWORD'),
                ],


                // Connection and socket timeout settings
                'connect_timeout' => (int) env('MQTT_CONNECT_TIMEOUT', 60),
                'socket_timeout' => (int) env('MQTT_SOCKET_TIMEOUT', 5),
                'resend_timeout' => (int) env('MQTT_RESEND_TIMEOUT', 10),
                'keep_alive_interval' => (int) env('MQTT_KEEP_ALIVE', 60),

                // TLS / SSL settings for encrypted connection
                'tls' => [
                    'enabled' => (bool) env('MQTT_TLS_ENABLED', false),
                    'allow_self_signed_certificate' => (bool) env('MQTT_TLS_ALLOW_SELF_SIGNED', false),
                    'verify_peer' => (bool) env('MQTT_TLS_VERIFY_PEER', true),
                    'verify_peer_name' => true,
                    'ca_file' => env('MQTT_TLS_CA_FILE', null),
                    'ca_path' => env('MQTT_TLS_CA_PATH', null),
                    'client_certificate_file' => env('MQTT_TLS_CLIENT_CERT_FILE', null),
                    'client_certificate_key_file' => env('MQTT_TLS_CLIENT_KEY_FILE', null),
                    'client_certificate_key_passphrase' => env('MQTT_TLS_CLIENT_KEY_PASSPHRASE', null),
                ],

                // Auto reconnect settings
                'auto_reconnect' => [
                    'enabled' => true,
                    'max_reconnect_attempts' => 10,
                    'delay_between_reconnect_attempts' => 2000,
                ],

            ],

        ],

    ],

];
