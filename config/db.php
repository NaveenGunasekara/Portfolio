<?php
declare(strict_types=1);

return [
    'host'    => getenv('PORTFOLIO_DB_HOST') ?: '127.0.0.1',
    'port'    => getenv('PORTFOLIO_DB_PORT') ?: '3306',
    'name'    => getenv('PORTFOLIO_DB_NAME') ?: 'portfolio_cms',
    'user'    => getenv('PORTFOLIO_DB_USER') ?: 'root',
    'pass'    => getenv('PORTFOLIO_DB_PASS') !== false ? getenv('PORTFOLIO_DB_PASS') : '',
    'charset' => 'utf8mb4',
];