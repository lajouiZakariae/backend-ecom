<?php

namespace App\Services;

use App\Filters\CouponCodeQueryFilters;
use App\Models\CouponCode;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Auth;

class CouponCodeService
{
    private $notFoundMessage = "CouponCode Not Found";

    public function getAllCouponCodesMatchFilters(CouponCodeQueryFilters $couponCodeQueryFilters): LengthAwarePaginator
    {
        $couponCodesQuery = CouponCode::where('user_id', Auth::id())->tap($couponCodeQueryFilters)->paginate(10);

        return $couponCodesQuery;
    }

    public function getCouponCodeById(int $couponCodeId): CouponCode
    {
        $couponCode = CouponCode::where('user_id', Auth::id())->find($couponCodeId);

        if ($couponCode === null) {
            throw new ResourceNotFoundException(__(':resource Not Found', ['resource' => __('CouponCode')]));
        }

        return $couponCode;
    }

    public function createCouponCode(array $couponCodePayload): CouponCode
    {
        $couponCode = new CouponCode($couponCodePayload);

        $couponCodeSaved = $couponCode->save();

        if (!$couponCodeSaved) {
            throw new BadRequestHttpException(__("CouponCode Could not be Created"));
        }

        return $couponCode;
    }

    public function updateCouponCode(int $couponCodeId, array $couponCodePayload): CouponCode
    {
        $affectedRowsCount = CouponCode::where('id', $couponCodeId)->where('user_id', Auth::id())->update($couponCodePayload);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__(':resource Not Found', ['resource' => __('CouponCode')]));
        }

        return CouponCode::find($couponCodeId);
    }

    public function deleteCouponCodeById(int $storeId, int $userId): void
    {
        $this->deleteMultipleCouponCodesByIds([$storeId], $userId);
    }

    public function deleteMultipleCouponCodes(array $storeIds, int $userId): void
    {
        $this->deleteMultipleCouponCodesByIds($storeIds, $userId);
    }

    public function deleteMultipleCouponCodesByIds(array $storeIds, int $userId): void
    {
        $affectedRowsCount = CouponCode::where('user_id', $userId)->destroy($storeIds);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('CouponCode Not Found'));
        }
    }
}
