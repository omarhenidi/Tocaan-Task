<?php

namespace App\Http\Controllers\Api\V1\Client\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\Client\Payment\PaymentApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentApiService $paymentService
    ) {
    }

    public function process(ProcessPaymentRequest $request): JsonResponse
    {
        $order = Order::findOrFail($request->validated('order_id'));
        $this->authorizeOrder($order);

        $simulateSuccess = $request->boolean('simulate_success', true);

        try {
            $transaction = $this->paymentService->process(
                $order,
                $request->validated('payment_method'),
                $request->validated('payment_id'),
                $simulateSuccess
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        return $this->successResponse(
            new PaymentResource($transaction->load('order')),
            'Payment processed successfully',
            201
        );
    }

    public function index(Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $perPage = min((int) $request->query('per_page', 15), 100);
        $payments = $this->paymentService->list($userId, $perPage);

        return $this->successResponse([
            'data' => PaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ], 'Payments retrieved successfully');
    }

    public function byOrder(Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $payments = $this->paymentService->listByOrder($order);

        return $this->successResponse(
            PaymentResource::collection($payments),
            'Payments for order retrieved successfully'
        );
    }

    protected function authorizeOrder(Order $order): void
    {
        $userId = auth('api')->id();
        if ($userId !== null && (int) $order->user_id !== (int) $userId) {
            abort(403, 'Unauthorized.');
        }
    }
}
