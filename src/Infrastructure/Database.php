<?php

namespace App\Infrastructure;

use PDO;

class Database
{
    private static ?PDO $conn = null;
    private static ?string $overridePath = null; 

    
    public static function setTestDatabase(string $path): void
    {
        self::$overridePath = $path;
        self::$conn = null; 
    }

    public static function connection(): PDO
    {
        if (self::$conn === null) {

            
            $dbPath = self::$overridePath ??
                (__DIR__ . '/../../storage/orders.sqlite');

            
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            self::$conn = new PDO('sqlite:' . $dbPath);
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$conn;
    }
}