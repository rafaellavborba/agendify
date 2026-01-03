<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Traits\HasFilters;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    use HasFilters;

    public function index(Request $request) 
    {
        $query = Service::where('tenant_id', app('tenant_id'));

        $query = $this->applyFilters($query, $request, ['q', 'duration', 'price', 'status']);

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        $allowedSorts = ['name', 'duration', 'price', 'status'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }

        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $services = $query->paginate($perPage);

        return response()->json($services);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $data['tenant_id'] = app('tenant_id');

        $service = Service::create($data);

        return response()->json($service, 201);
    }

    public function show(Service $service)
    {
        $this->authorizeTenant($service);
        return $service;
    }

    public function update(Request $request, Service $service)
    {
        $this->authorizeTenant($service);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'duration' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
        ]);

        $service->update($data);

        return $service;
    }

    public function destroy(Service $service)
    {
        $this->authorizeTenant($service);
        $service->delete();

        return response()->noContent();
    }

    private function authorizeTenant(Service $service)
    {
        if ($service->tenant_id !== app('tenant_id')) {
            abort(403, 'Acesso negado');
        }
    }
}
