<?php
/**
 * Database configuration for portfolio CMS.
 * XAMPP defaults: host 127.0.0.1, user root, empty password.
 * Override via environment variables on shared hosting if supported.
 */
declare(strict_types=1);

return [
    'host'    => getenv('PORTFOLIO_DB_HOST') ?: '127.0.0.1',
    'name'    => getenv('PORTFOLIO_DB_NAME') ?: 'portfolio_cms',
    'user'    => getenv('PORTFOLIO_DB_USER') ?: 'root',
    'pass'    => getenv('PORTFOLIO_DB_PASS') !== false ? getenv('PORTFOLIO_DB_PASS') : '',
    'charset' => 'utf8mb4',
];
