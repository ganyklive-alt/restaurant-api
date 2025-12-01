<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database;

$db = Database::connection();

$db->exec("
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    items TEXT NOT NULL,
    pickup_time TEXT NOT NULL,
    vip INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'active',
    created_at TEXT NOT NULL
)
");

echo "Migration completed.\n";