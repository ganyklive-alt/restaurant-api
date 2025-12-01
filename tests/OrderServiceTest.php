<?php

use PHPUnit\Framework\TestCase;
use App\Services\OrderService;
use App\Infrastructure\Database;

class OrderServiceTest extends TestCase
{
    private string $dbFile;

    protected function setUp(): void
    {
        // Use a separate test database file
        $this->dbFile = __DIR__ . '/../storage/orders_test.sqlite';

        // Remove old test DB if exists
        if (file_exists($this->dbFile)) {
            unlink($this->dbFile);
        }

        // Override the database path for testing
        Database::setTestDatabase($this->dbFile);

        // Recreate fresh test database schema
        $db = Database::connection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                items TEXT NOT NULL,
                pickup_time TEXT NOT NULL,
                vip INTEGER NOT NULL DEFAULT 0,
                status TEXT NOT NULL DEFAULT 'active',
                created_at TEXT NOT NULL
            );
        ");
    }

    public function testOrderAcceptance()
    {
        $service = new OrderService();

        $result = $service->createOrder([
            "items" => ["burger"],
            "pickup_time" => "2025-10-10T10:00:00Z"
        ]);

        $this->assertEquals("Order accepted", $result["message"]);
        $this->assertEquals(1, $result["id"]);
    }

    public function testKitchenCapacityLimit()
    {
        $service = new OrderService();

        // Fill kitchen to 5 orders
        for ($i = 0; $i < 5; $i++) {
            $service->createOrder([
                "items" => ["item$i"],
                "pickup_time" => "2025-10-10T10:00:00Z"
            ]);
        }

        // Try to add 6th non-VIP order → should fail with suggestion
        $result = $service->createOrder([
            "items" => ["extra"],
            "pickup_time" => "2025-10-10T10:00:00Z"
        ]);

        $this->assertArrayHasKey("error", $result);
        $this->assertArrayHasKey("next_available_pickup_time", $result);
    }

    public function testVipBypassesCapacity()
    {
        $service = new OrderService();

        // Fill kitchen with 5 orders
        for ($i = 0; $i < 5; $i++) {
            $service->createOrder([
                "items" => ["item$i"],
                "pickup_time" => "2025-10-10T10:00:00Z"
            ]);
        }

        // VIP should still be accepted
        $result = $service->createOrder([
            "items" => ["vip"],
            "pickup_time" => "2025-10-10T10:00:00Z",
            "VIP" => true
        ]);

        $this->assertEquals("Order accepted", $result["message"]);
        $this->assertTrue($result["vip"]);
    }

    public function testOrderCompletion()
    {
        $service = new OrderService();

        $create = $service->createOrder([
            "items" => ["tea"],
            "pickup_time" => "2025-10-10T10:00:00Z"
        ]);

        $id = $create["id"];

        $result = $service->completeOrder($id);

        $this->assertEquals("Order completed", $result["message"]);
    }

    public function testCalculatePriorityQueue()
    {
        $service = new OrderService();

        // Non-VIP order
        $service->createOrder([
            "items" => ["normal"],
            "pickup_time" => "2025-10-10T10:00:00Z"
        ]);

        // VIP order
        $service->createOrder([
            "items" => ["vip"],
            "VIP" => true,
            "pickup_time" => "2025-10-10T10:00:00Z"
        ]);

        $orders = $service->getActiveOrders();

        // VIP must always be first
        $this->assertTrue($orders[0]["vip"]);
        $this->assertFalse($orders[1]["vip"]);
    }
}