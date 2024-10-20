<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class StoreController
{
    public function __construct(
        private StoreService $storeService
    ) {
    }

    public function index(Request $request): ResourceCollection
    {
        $validatedFilters = $request->validate([
            'sortBy' => ['in:oldest,latest'],
            'order' => ['in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        $stores = $this->storeService->getPaginatedStores($validatedFilters);

        return StoreResource::collection($stores);
    }

    public function store(): StoreResource
    {
        Gate::authorize('create', Store::class);

        $validatedStorePayload = request()->validate([
            'image' => ['required', 'image', 'max:2048'],
            'name' => ['required', 'min:1', 'max:255'],
            'address' => ['nullable', 'min:1', 'max:500'],
        ]);

        $createdStore = $this->storeService->createStore([
            'user_id' => Auth::id(),
            ...$validatedStorePayload,
        ]);

        return StoreResource::make($createdStore);
    }

    public function show(int $storeId): StoreResource
    {
        return StoreResource::make($this->storeService->getStoreById($storeId));
    }

    public function update(int $storeId): StoreResource
    {
        Gate::authorize('update', Store::class);

        $validatedStorePayload = request()->validate([
            'name' => ['required', 'min:1', 'max:255'],
            'address' => ['nullable', 'min:1', 'max:500'],
        ]);

        return StoreResource::make($this->storeService->updateStore($storeId, $validatedStorePayload));
    }

    public function destroy(int $storeId): Response
    {
        Gate::authorize('delete', Store::class);

        $this->storeService->deleteCatgoryById($storeId, Auth::id());

        return response()->noContent();
    }

    public function destroyMultiple(Request $request): Response
    {
        Gate::authorize('delete', Store::class);

        $validatedStoreIds = $request->validate([
            'storeIds' => ['required', 'array', 'min:1'],
            'storeIds.*' => ['required', 'integer', 'exists:stores,id'],
        ]);

        $this->storeService->deleteMultipleStores($validatedStoreIds['storeIds'], Auth::id());

        return response()->noContent();
    }
}
