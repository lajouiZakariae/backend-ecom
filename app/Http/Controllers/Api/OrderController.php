<?php

namespace App\Http\Controllers\Api;

use App\Filters\OrderQueryFilters;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\WhereNumber;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use \Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[ApiResource('orders')]
#[WhereNumber('order')]
class OrderController
{
    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'sortBy' => ['in:oldest,latest'],
            'order' => ['in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        /**
         * @var \App\Models\User
         */
        $user = Auth::user();

        $paginatedOrders = Order::query()
            ->tap(new OrderQueryFilters(
                $user->hasCustomerRole() ? Auth::id() : null,
                $request->sortBy,
                $request->order,
            ))
            ->paginate($request->perPage ?? 10);

        return OrderResource::collection($paginatedOrders);
    }

    public function store(OrderStoreRequest $orderStoreRequest): JsonResponse
    {
        $validatedOrderPayload = $orderStoreRequest->validated();

        $order = new Order($validatedOrderPayload);

        if (!$order->save()) {
            throw new BadRequestHttpException(__("Order Could not be created"));
        }

        return OrderResource::make($order)->response()->setStatusCode(SymfonyResponse::HTTP_CREATED);
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
