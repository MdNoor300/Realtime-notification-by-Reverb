<?php

namespace App\Http\Resources\Pagination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasePaginationResource extends JsonResource
{
    private $pagination;

    public function __construct($resource)
    {
        $previous_url = parse_url($resource->previousPageUrl());
        if (isset($previous_url['query'])) parse_str($previous_url['query'], $prev);
        else $prev['page'] = null;
        $next_url = parse_url($resource->nextPageUrl());
        if (isset($next_url['query'])) parse_str($next_url['query'], $next);
        else $next['page'] = null;

        $this->pagination = [
            'from'         => ($resource->currentPage() - 1) * $resource->perPage() + 1,
            'to'           => ($resource->currentPage() - 1) * $resource->perPage() + $resource->count(),
            'current_page' => $resource->currentPage(),
            'prev_page'    => $prev['page'] ?? null,
            'next_page'    => $next['page'] ?? NULL,
            'per_page'     => $resource->perPage(),
            'total'        => $resource->total(),
            'total_page'   => ceil($resource->total() / $resource->perPage())
        ];
        $resource = $resource->getCollection();
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'docs' => $this->resource,
            'page' => $this->pagination['current_page'],
            'limit' => $this->pagination['per_page'],
            'totalDocs' => $this->pagination['total'],
            'totalPages' => $this->pagination['total_page'],
            'hasNextPage' => $this->pagination['next_page'] ? true : false,
            'hasPrevPage' => $this->pagination['prev_page'] ? true : false
        ];
    }
}
