<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PaymentProcessingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private OrderPaymentService $orderPaymentService
    ) {}

    /**
     * Process a payment for an order
     *
     * @param StorePaymentRequest $request
     * @return JsonResponse
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $order = Order::findOrFail($request->validated()['order_id']);
            
            $payment = $this->orderPaymentService->processPayment($order);
            
            return (new PaymentResource($payment))
                ->response()
                ->setStatusCode(201);
                
        } catch (PaymentProcessingException $e) {
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ], $e->getCode());
            
        } catch (\Exception $e) {
            Log::error('Unexpected error processing payment', [
                'order_id' => $request->validated()['order_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }
}
