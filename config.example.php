<?php
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'travel',
        'user' => 'root',
        'pass' => '',
    ],
    'smtp' => [
        'host' => 'mail.example.com',
        'port' => 465,
        'secure' => 'ssl',
        'user' => 'user@example.com',
        'pass' => 'changeme',
        'from_name' => 'Travel Support',
    ],
    'admin' => [
        'user' => 'admin',
        'pass_hash' => '', // password_hash('yourpassword', PASSWORD_DEFAULT)
    ],
];
