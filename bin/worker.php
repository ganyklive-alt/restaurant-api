<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Services\OrderService;

$service = new OrderService();

// Worker settings
$checkIntervalSeconds = 30;          // Check every 30 seconds
$autoCompleteMinutes = 10;           // Auto-complete after 10 minutes

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

        // If older than PREP_MINUTES → auto complete
        if ($diff->i >= $autoCompleteMinutes) {
            echo "✔ Auto-completing order ID {$order['id']} (created at {$order['created_at']})\n";
            $service->autoComplete((int)$order['id']);
        }
    }

    echo "✔ Done. Sleeping {$checkIntervalSeconds} seconds...\n\n";
    sleep($checkIntervalSeconds);
}