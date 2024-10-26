<?php

namespace App\Http\Controllers\Api;

use App\Actions\PlaceOrderWithPaymentAction;
use App\Filters\OrderQueryFilters;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\WhereNumber;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use \Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[ApiResource('orders')]
#[WhereNumber('order')]
class OrderController
{
    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'sort_by' => ['in:created_at'],
            'order' => ['in:asc,desc'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ]);

        /**
         * @var \App\Models\User
         */
        $user = Auth::user();

        $paginatedOrders = Order::query()
            ->tap(new OrderQueryFilters(
                $user->hasCustomerRole() ? Auth::id() : null,
                $request->sort_by,
                $request->order,
            ))
            ->paginate($request->per_page ?? 10);

        return OrderResource::collection($paginatedOrders);
    }

    public function store(OrderStoreRequest $orderStoreRequest): JsonResponse
    {
        $validatedOrderPayload = $orderStoreRequest->validated();

        $cart = Cart::where('user_id', Auth::id())->with('cartItems')->first();

        if (!$cart) {
            throw new NotFoundHttpException(__(':reource not found', ['resource' => __('Cart')]));
        }

        if ($cart->cartItems->isEmpty()) {
            throw new BadRequestHttpException(__('Cart cannot be empty'));
        }

        $order = new Order([...$validatedOrderPayload]);

        if (!$order->save()) {
            throw new BadRequestHttpException(__("Order Could not be created"));
        }

        return OrderResource::make($order)->response()->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[Post('orders/place-order')]
    public function placeOrder(Request $request, PlaceOrderWithPaymentAction $placeOrderWithPaymentAction): JsonResponse
    {
        $request->validate([
            'payment_method_id' => ['required'],
        ]);

        $order = $placeOrderWithPaymentAction->handle($request->payment_method_id);

        return response()->json($order);

        // return OrderResource::make($order)->response()->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    public function show(int $orderId): OrderResource
    {
        /**
         * @var Order|null $order
         */
        $order = Order::find($orderId);

        if ($order === null) {
            throw new ResourceNotFoundException(__("Order Not Found"));
        }

        return OrderResource::make($order);
    }

    public function update(OrderUpdateRequest $orderUpdateRequest, int $orderId): OrderResource
    {
        $validatedOrderPayload = $orderUpdateRequest->validated();

        $affectedRowsCount = Order::where('id', $orderId)->update($validatedOrderPayload);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__("Order Not Found"));
        }

        return OrderResource::make(Order::find($orderId));
    }

    public function destroy(int $orderId): Response
    {
        $affectedRowsCount = Order::destroy($orderId);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('Order Not Found'));
        }

        return response()->noContent();
    }

    public function destroyMultiple(Request $request): Response
    {
        $validatedOrderIds = $request->validate([
            'orderIds' => ['required', 'array', 'min:1'],
            'orderIds.*' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $affectedRowsCount = Order::destroy($validatedOrderIds);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('Order Not Found'));
        }

        return response()->noContent();
    }
}
