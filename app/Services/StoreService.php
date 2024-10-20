<?php

namespace App\Services;

use App\Filters\StoreFilters;
use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class StoreService
{
    public function getPaginatedStores(array $filters): LengthAwarePaginator
    {
        $paginatedStores = Store::query()
            ->tap(new StoreFilters($filters['sortBy'], $filters['order']))
            ->paginate($filters['perPage'] ?? 10);

        return $paginatedStores;
    }

    public function getStoreById(int $storeId): Store
    {
        $store = Store::find($storeId);

        if ($store === null) {
            throw new ResourceNotFoundException(__('Store Not Found'));
        }

        return $store;
    }

    public function createStore(array $storePayload): Store
    {
        $store = new Store($storePayload);

        if (!$store->save()) {
            throw new BadRequestHttpException('Store Could not be created');
        }

        return $store;
    }

    public function updateStore(int $storeId, array $storePayload): Store
    {
        $affectedRowsCount = Store::where('id', $storeId)->update($storePayload);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('Store Not Found'));
        }

        return Store::find($storeId);
    }

    public function deleteCatgoryById(int $storeId, int $userId): void
    {
        $affectedRowsCount = Store::where('user_id', $userId)->destroy($storeId);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('Store Not Found'));
        }
    }

    public function deleteMultipleStores(array $storeIds, int $userId): void
    {
        $affectedRowsCount = Store::where('user_id', $userId)->destroy($storeIds);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('Store Not Found'));
        }
    }
}
