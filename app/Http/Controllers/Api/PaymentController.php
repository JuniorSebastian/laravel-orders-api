<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private OrderPaymentService $orderPaymentService
    ) {}

    /**
     * Store a newly created payment resource.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $order = Order::findOrFail($request->validated()['order_id']);
            
            $payment = $this->orderPaymentService->processPayment($order);
            
            return (new PaymentResource($payment))
                ->response()
                ->setStatusCode(201);
                
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
