<?php

namespace App\Services;

use App\Infrastructure\Database;
use PDO;
use DateTime;
use DateInterval;

class OrderService
{
    private const CAPACITY = 5;           // kitchen limit
    private const PREP_MINUTES = 10;      // each order takes 10 mins

    /**
     * Create a new order
     */
    public function createOrder(array $input): array
    {
        if (empty($input['items']) || empty($input['pickup_time'])) {
            http_response_code(400);
            return ["error" => "items and pickup_time are required"];
        }

        $db = Database::connection();
        $vip = !empty($input['VIP']) ? 1 : 0;

        // Count active orders
        $activeCount = (int) $db->query("SELECT COUNT(*) FROM orders WHERE status='active'")
                                ->fetchColumn();

        // If full and not VIP → suggest next available pickup time
        if ($activeCount >= self::CAPACITY && $vip === 0) {
            http_response_code(429);

            $nextAvailable = $this->calculateNextAvailablePickupTime($activeCount);

            return [
                "error" => "Kitchen is full",
                "next_available_pickup_time" => $nextAvailable
            ];
        }

        // Insert new order
        $stmt = $db->prepare("
            INSERT INTO orders (items, pickup_time, vip, status, created_at)
            VALUES (:items, :pickup_time, :vip, 'active', :created_at)
        ");

        $stmt->execute([
            ':items'       => json_encode($input['items']),
            ':pickup_time' => $input['pickup_time'],
            ':vip'         => $vip,
            ':created_at'  => date('c')
        ]);

        http_response_code(201);

        return [
            "message" => "Order accepted",
            "id"      => (int) $db->lastInsertId(),
            "vip"     => (bool) $vip
        ];
    }

    /**
     * Priority: VIP first, earliest orders next
     */
    public function getActiveOrders(): array
    {
        $db = Database::connection();
        $stmt = $db->query("
            SELECT * FROM orders 
            WHERE status='active'
            ORDER BY vip DESC, created_at ASC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['items'] = json_decode($row['items'], true);
            $row['vip']   = (bool)$row['vip'];
        }

        return $rows;
    }

    /**
     * Complete order
     */
    public function completeOrder(int $id): array
    {
        $db = Database::connection();

        $stmt = $db->prepare("SELECT * FROM orders WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            http_response_code(404);
            return ["error" => "Order not found"];
        }

        $db->prepare("UPDATE orders SET status='completed' WHERE id=:id")
           ->execute(['id' => $id]);

        return ["message" => "Order completed"];
    }

    /**
     * Bonus 1 — Suggest next available pickup time
     * Based on: active_orders * 10 minutes
     */
    private function calculateNextAvailablePickupTime(int $activeOrders): string
    {
        $now = new DateTime();
        $minutesToAdd = $activeOrders * self::PREP_MINUTES;

        $now->add(new DateInterval("PT{$minutesToAdd}M"));

        return $now->format(DateTime::ATOM);
    }

    /**
     * Used by background worker — returns all active orders
     */
    public function getOrdersForWorker(): array
    {
        $db = Database::connection();

        $stmt = $db->query("
            SELECT *
            FROM orders
            WHERE status='active'
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Auto-complete an order (used by worker)
     */
    public function autoComplete(int $id): void
    {
        $db = Database::connection();
        $db->prepare("UPDATE orders SET status='completed' WHERE id=:id")
           ->execute(['id' => $id]);
    }
}