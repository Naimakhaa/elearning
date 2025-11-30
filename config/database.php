<?php

return [
    'host'     => '127.0.0.1',
    'database' => 'elearning_api',   // harus SAMA dengan nama database di phpMyAdmin !!!
    'username' => 'root',
    'password' => '',   // Laragon/XAMPP biasanya kosong
    'charset'  => 'utf8mb4',

    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
