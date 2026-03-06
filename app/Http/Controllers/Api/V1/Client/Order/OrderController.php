<?php

namespace App\Http\Controllers\Api\V1\Client\Order;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Client\Payment\PaymentGatewayManager;
use App\Services\Contracts\Client\Checkout\CheckoutInterface;
use App\Services\Contracts\Client\Order\OrderInterface;
use App\Support\Traits\Orderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    use Orderable;

    public function __construct(
        protected OrderInterface $orderService,
        protected CheckoutInterface $checkoutInterface,
        protected PaymentGatewayManager $paymentGatewayManager
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = array_merge(
            ['user_id' => auth('api')->id()],
            $request->only('status')
        );
        $perPage = min((int) $request->query('per_page', 15), 100);
        $orders = $this->orderService->list($filters, true, $perPage);

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

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $userId = auth('api')->id();
        $order = $this->orderService->store($request->validated(), $userId);
        $order->load('orderItems');

        $paymentMethod = $request->input('payment_method', config('payment.default', 'credit_card'));
        $payUrl = $this->paymentGatewayManager->has($paymentMethod)
            ? url('/api/payment/checkout?order_id=' . $order->getKey() . '&method=' . $paymentMethod)
            : null;

        return $this->successResponse([
            'order' => new OrderResource($order),
            'pay_url' => $payUrl,
        ], 'Order created successfully.', 201);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $order->load(['orderItems', 'transactions']);

        return $this->successResponse(new OrderResource($order), 'Order retrieved successfully.');
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $order = $this->orderService->update($order, $request->validated());

        return $this->successResponse(new OrderResource($order), 'Order updated successfully.');
    }

    public function destroy(Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $result = $this->orderService->delete($order);
        $message = $result === 'cancelled'
            ? 'Order cancelled successfully.'
            : 'Order deleted successfully.';

        return $this->successResponse([], $message);
    }

    public function showByOrderNumber(string $orderNumber): JsonResponse
    {
        $order = Order::with(['orderItems', 'transactions'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $this->authorizeOrder($order);

        return $this->successResponse(new OrderResource($order), 'Order retrieved successfully.');
    }

    public function checkout(Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $payload = array_merge($request->all(), ['user_id' => $userId]);
        [$order, $paymentUrl] = $this->checkoutInterface->store($payload);

        return $this->successResponse([
            'order_id' => $order->getKey(),
            'order_number' => $order->order_number,
            'pay_url' => $paymentUrl,
        ], 'Checkout initiated', 201);
    }

    public function handleCallback(Request $request): JsonResponse
    {
        $orderId = $request->input('order_id');
        $tranRef = $request->input('tran_ref');
        $currency = $request->input('tran_currency');
        $amount = $request->input('cart_amount');
        $paymentResult = $request->input('payment_result', []);
        $status = $paymentResult['response_status'] ?? null;
        $responseMessage = $paymentResult['response_message'] ?? null;

        $order = Order::find($orderId);

        if (! $order) {
            return $this->errorResponse('Order not found', 404);
        }

        $this->logTransaction($order, [
            'transaction_status' => $status,
            'transaction_status_reason' => $responseMessage,
            'meta' => $paymentResult,
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $tranRef,
        ]);

        if ($status === 'A' && Schema::hasColumn($order->getTable(), 'payment_status')) {
            $order->update(['payment_status' => PaymentStatus::Paid]);
        }

        return $this->successResponse([], 'Callback processed successfully.');
    }

    protected function authorizeOrder(Order $order): void
    {
        $userId = auth('api')->id();
        if ($userId !== null && (int) $order->user_id !== (int) $userId) {
            abort(403, 'Unauthorized.');
        }
    }
}
