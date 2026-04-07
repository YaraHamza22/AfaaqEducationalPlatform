<?php

namespace Modules\LearningModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Enrollment Collection Resource
 *
 * Transforms a collection of Enrollment models into a consistent JSON structure.
 * Provides pagination metadata and standardized collection responses.
 *
 * Features:
 * - Applies EnrollmentResource transformation to each item
 * - Includes pagination metadata
 * - Supports filtered and sorted lists
 * - Provides collection-level metadata
 */
class EnrollmentCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = EnrollmentResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Get additional metadata about the resource collection.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'total' => $this->resource->total() ?? 0,
                'count' => $this->collection->count(),
                'per_page' => $this->resource->perPage() ?? 15,
                'current_page' => $this->resource->currentPage() ?? 1,
                'last_page' => $this->resource->lastPage() ?? 1,
                'from' => $this->resource->firstItem() ?? null,
                'to' => $this->resource->lastItem() ?? null,
            ],
            'links' => [
                'first' => $this->resource->url(1) ?? null,
                'last' => $this->resource->url($this->resource->lastPage() ?? 1) ?? null,
                'prev' => $this->resource->previousPageUrl() ?? null,
                'next' => $this->resource->nextPageUrl() ?? null,
            ],
        ];
    }
}
