<?php

namespace App\Http\Controllers\Api\V1\Admin\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Contracts\Admin\Order\OrderManagementInterface;
use App\Support\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ResponseTrait;

    public function __construct(protected OrderManagementInterface $orderInterface)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only('status', 'user_id');
        $perPage = min((int) $request->query('per_page', 15), 100);
        $orders = $this->orderInterface->listOrders($filters, $perPage);

        return $this->successResponse([
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], 'Orders retrieved successfully.');
    }

    public function show(Order $order): JsonResponse
    {
        $order = $this->orderInterface->show($order);

        return $this->successResponse(new OrderResource($order), 'Order retrieved successfully.');
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderInterface->store($request->validated());

        return $this->successResponse(new OrderResource($order), 'Order created successfully.', 201);
    }

    public function update(Order $order, UpdateOrderRequest $request): JsonResponse
    {
        $order = $this->orderInterface->update($order, $request->validated());

        return $this->successResponse(new OrderResource($order), 'Order updated successfully.');
    }

    public function destroy(Order $order): JsonResponse
    {
        $result = $this->orderInterface->delete($order);
        $message = $result === 'cancelled'
            ? 'Order cancelled successfully.'
            : 'Order deleted successfully.';

        return $this->successResponse([], $message);
    }
}
