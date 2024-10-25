<?php

namespace App\Http\Controllers;

use App\Filters\CouponCodeQueryFilters;
use App\Http\Requests\CouponCodeStoreRequest;
use App\Http\Requests\CouponCodeUpdateRequest;
use App\Http\Resources\CouponCodeResource;
use App\Models\CouponCode;
use App\Services\CouponCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CouponCodeController extends Controller
{
    public function __construct(protected CouponCodeService $couponCodeService)
    {
    }

    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'sortBy' => ['nullable', 'in:name,created_at'],
            'order' => ['nullable', 'in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        $couponCodeFilters = new CouponCodeQueryFilters(
            $request->sortBy,
            $request->order
        );

        $paginatedCouponCodes = $this->couponCodeService->getAllCouponCodesMatchFilters($couponCodeFilters);

        return CouponCodeResource::collection($paginatedCouponCodes);
    }

    public function store(CouponCodeStoreRequest $couponCodeStoreRequest): CouponCodeResource
    {
        $validatedCouponCodePayload = $couponCodeStoreRequest->validated();

        $createdCouponCode = $this->couponCodeService->createCouponCode([
            'user_id' => Auth::id(),
            ...$validatedCouponCodePayload,
        ]);

        return CouponCodeResource::make($createdCouponCode);
    }

    public function show(int $couponCodeId): CouponCodeResource
    {
        return CouponCodeResource::make($this->couponCodeService->getCouponCodeById($couponCodeId));
    }

    public function update(CouponCodeUpdateRequest $couponCodeUpdateRequest, int $couponCodeId): CouponCodeResource
    {
        $validatedCouponCodePayload = $couponCodeUpdateRequest->validated();

        return CouponCodeResource::make($this->couponCodeService->updateCouponCode($couponCodeId, $validatedCouponCodePayload));
    }

    public function destroy(int $couponCodeId): Response
    {
        $this->couponCodeService->deleteCouponCodeById($couponCodeId, Auth::id());

        return response()->noContent();
    }

    public function destroyMultiple(Request $request): Response
    {
        $validatedCouponCodeIds = $request->validate([
            'couponCodeIds' => ['required', 'array', 'min:1'],
            'couponCodeIds.*' => ['required', 'integer', Rule::exists(CouponCode::class, 'id')],
        ]);

        $this->couponCodeService->deleteMultipleCouponCodes($validatedCouponCodeIds['couponCodeIds'], Auth::id());

        return response()->noContent();
    }
}
