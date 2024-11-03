<?php

namespace App\Http\Controllers\BookController;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @tags Book
 */
class BookController
{
    /**
     * Get All Books
     */
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', Book::class);

        $request->validate([
            'sort_by' => ['nullable', 'string'],
            'order' => ['nullable', 'string', 'in:asc,desc'],
            'paginate' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Book::query();

        $book = $request->boolean('paginate', true)
            ? $query->paginate($request->per_page ?? 15)
            : $query->get();

        return BookResource::collection($book);
    }

    /**
     * Store Book.
     */
    public function store(Request $request): BookResource
    {
        Gate::authorize('create', Book::class);

        $validatedPayload = $request->validate([]);

        $book = new Book($validatedPayload);

        if (!$book->save()) {
            throw new BadRequestHttpException(__(':resource Could not be created', ['resource' => __('Book')]));
        }

        return BookResource::make($book);
    }

    /**
     * Get Book
     */
    public function show(int $id): BookResource
    {
        Gate::authorize('view', Book::class);

        $book = Book::find($id);

        if (!$book) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('Book')]));
        }

        return new BookResource($book);
    }

    /**
     * Update Book
     */
    public function update(Request $request, int $id): BookResource
    {
        Gate::authorize('update', Book::class);

        $validatedPayload = $request->validate([]);

        $book = Book::findOrFail($id);

        $book->fill($validatedPayload);

        if (!$book->save()) {
            throw new BadRequestHttpException(__(':resource Could not be updated', ['resource' => __('Book')]));
        }

        return BookResource::make($book);
    }

    /**
     * Delete Book
     */
    public function destroy(int $id): Response
    {
        Gate::authorize('delete', Book::class);

        $affectedRowsCount = Book::destroy($id);

        if ($affectedRowsCount === 0) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('Book')]));
        }

        return response()->noContent();
    }

    /**
     * Bulk Delete Book
     */
    public function destroyMany(Request $request): Response
    {
        Gate::authorize('delete', Book::class);

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', Rule::exists(Book::class, 'id')],
        ]);

        $affectedRowsCount = Book::destroy($request->ids);

        if ($affectedRowsCount === 0) {
            throw new NotFoundHttpException(__(':resource not found', ['resource' => __('Book')]));
        }

        return response()->noContent();
    }
}