<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait HasPaginatedJson
{
    /**
     * Paginasi JSON standar: { data, total, page, per_page, total_pages }.
     *
     * @param  array{per_page?: int, max_per_page?: int, order_by?: string, order_dir?: string}  $options
     */
    protected function paginatedJson(Request $request, Builder $query, array $columns = ['*'], array $options = []): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', $options['per_page'] ?? 25), $options['max_per_page'] ?? 200);
        $page = max((int) $request->input('page', 1), 1);

        $total = (clone $query)->count();

        $data = $query
            ->orderBy($options['order_by'] ?? 'id', $options['order_dir'] ?? 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get($columns);

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / max($perPage, 1)),
        ]);
    }
}
