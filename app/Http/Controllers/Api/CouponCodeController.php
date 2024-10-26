<?php

namespace App\Http\Controllers\Api;

use App\Filters\CouponCodeQueryFilters;
use App\Http\Requests\CouponCodeStoreRequest;
use App\Http\Requests\CouponCodeUpdateRequest;
use App\Http\Resources\CouponCodeResource;
use App\Models\CouponCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\WhereNumber;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use \Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[ApiResource('coupon-codes')]
#[WhereNumber('couponCode')]
class CouponCodeController
{
    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'sort_by' => ['in:created_at'],
            'order' => ['in:asc,desc'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ]);

        $paginatedCouponCodes = CouponCode::query()
            ->tap(new CouponCodeQueryFilters(
                $request->sort_by,
                $request->order
            ))
            ->paginate($request->per_page ?? 10);

        return CouponCodeResource::collection($paginatedCouponCodes);
    }

    public function store(CouponCodeStoreRequest $couponCodeStoreRequest): JsonResponse
    {
        $validatedCouponCodePayload = $couponCodeStoreRequest->validated();

        $couponCode = new CouponCode($validatedCouponCodePayload);

        if (!$couponCode->save()) {
            throw new BadRequestHttpException("CouponCode Could not be created");
        }

        return CouponCodeResource::make($couponCode)->response()->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    public function show(int $couponCodeId): CouponCodeResource
    {
        $couponCode = CouponCode::find($couponCodeId);

        if ($couponCode === null) {
            throw new ResourceNotFoundException(__("CouponCode Not Found"));
        }

        return CouponCodeResource::make($couponCode);
    }

    public function update(CouponCodeUpdateRequest $couponCodeUpdateRequest, int $couponCodeId): CouponCodeResource
    {
        $validatedCouponCodePayload = $couponCodeUpdateRequest->validated();

        $affectedRowsCount = CouponCode::where('id', $couponCodeId)->update($validatedCouponCodePayload);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__("CouponCode Not Found"));
        }

        return CouponCodeResource::make(CouponCode::find($couponCodeId));
    }

    public function destroy(int $couponCodeId): Response
    {
        $affectedRowsCount = CouponCode::destroy($couponCodeId);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('CouponCode Not Found'));
        }

        return response()->noContent();
    }

    public function destroyMultiple(Request $request): Response
    {
        $validatedCouponCodeIds = $request->validate([
            'couponCodeIds' => ['required', 'array', 'min:1'],
            'couponCodeIds.*' => ['required', 'integer', 'exists:couponCodes,id'],
        ]);

        $affectedRowsCount = CouponCode::destroy($validatedCouponCodeIds);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('CouponCode Not Found'));
        }

        return response()->noContent();
    }
}
