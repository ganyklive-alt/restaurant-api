<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Services\OrderService;

$service = new OrderService();


$checkIntervalSeconds = 30;          
$autoCompleteMinutes = 10;           

echo "=== Kitchen Worker Started ===\n";
echo "Checking for orders every {$checkIntervalSeconds} seconds...\n";
echo "Auto-completing orders older than {$autoCompleteMinutes} minutes.\n\n";

while (true) {
    echo "⏳ Checking active orders...\n";

    $orders = $service->getOrdersForWorker();
    $now = new DateTime();

    foreach ($orders as $order) {
        $createdAt = new DateTime($order['created_at']);
        $diff = $createdAt->diff($now);

        
        if ($diff->i >= $autoCompleteMinutes) {
            echo "✔ Auto-completing order ID {$order['id']} (created at {$order['created_at']})\n";
            $service->autoComplete((int)$order['id']);
        }
    }

    echo "✔ Done. Sleeping {$checkIntervalSeconds} seconds...\n\n";
    sleep($checkIntervalSeconds);
}