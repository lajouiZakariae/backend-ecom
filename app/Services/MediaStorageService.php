<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Request;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaStorageService
{
    /**
     * Store Image to Specific Model
     * 
     * @param \Spatie\MediaLibrary\HasMedia $model
     * @param \Illuminate\Http\UploadedFile|string $image an uploaded file or name of request input
     * @param string $collectionName
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     */
    public function storeImageAndAssignToModel(HasMedia $model, UploadedFile|string $image, string $collectionName = 'default'): Media
    {
        $image = $image instanceof UploadedFile ? $image : Request::file($image);

        return $model
            ->addMedia($image)
            ->usingFileName(Str::uuid()->toString() . '.' . $image->extension())
            ->toMediaCollection($collectionName, config('diabolus-config.disk', 'public'));
    }

    public function clearMediaOfModel(HasMedia $model, string $collectionName = 'default'): void
    {
        $model->clearMediaCollection($collectionName);
    }
}
