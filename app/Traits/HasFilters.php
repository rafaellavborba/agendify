<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasFilters
{
    /**
     * Aplica filtros e ordenação em uma query de forma genérica.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $filters ['campo' => 'tipo'] (tipo: 'like' ou 'exact')
     * @param array $searchableForQ Campos para pesquisa genérica 'q'
     * @param array $allowedSorts
     * @param string $defaultSort
     * @return Builder
     */
    public function applyFilters(
        Builder $query,
        Request $request,
        array $filters = [],
        array $searchableForQ = [],
        array $allowedSorts = [],
        string $defaultSort = 'id'
    ) {
        foreach ($filters as $field => $type) {
            $type = $type ?? 'exact'; // padrão exact

            if ($field === 'q') continue; // q será tratado separado

            if ($value = $request->get($field)) {
                if ($type === 'like') {
                    $query->where($field, 'like', "%{$value}%");
                } else {
                    $query->where($field, $value);
                }
            }
        }

        // Filtro genérico 'q' em múltiplos campos
        if ($q = $request->get('q')) {
            $query->where(function ($qQuery) use ($q, $searchableForQ) {
                foreach ($searchableForQ as $field) {
                    $qQuery->orWhere($field, 'like', "%{$q}%");
                }
            });
        }

        // Ordenação
        $sortBy = $request->get('sort_by', $defaultSort);
        $sortOrder = $request->get('sort_order', 'asc');

        if (!empty($allowedSorts) && !in_array($sortBy, $allowedSorts)) {
            $sortBy = $defaultSort;
        }

        return $query->orderBy($sortBy, $sortOrder);
    }
}
