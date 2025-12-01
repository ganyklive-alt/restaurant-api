<?php

namespace App\Controllers;

use App\Services\OrderService;

class OrderController
{
    private OrderService $service;

    public function __construct()
    {
        header("Content-Type: application/json");
        $this->service = new OrderService();
    }

    public function create()
    {
        $input = json_decode(file_get_contents("php://input"), true) ?? [];
        echo json_encode($this->service->createOrder($input));
    }

    public function listActive()
    {
        echo json_encode($this->service->getActiveOrders());
    }

    public function complete($id)
    {
        echo json_encode($this->service->completeOrder((int)$id));
    }
}