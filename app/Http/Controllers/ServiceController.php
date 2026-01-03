<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::where('tenant_id', app('tenant_id'))->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'duration' => 'required|integer|min:5',
            'price' => 'nullable|numeric',
        ]);

        $data['tenant_id'] = app('tenant_id');

        return Service::create($data);
    }

    public function show(Service $service)
    {
        $this->authorizeTenant($service);
        return $service;
    }

    public function update(Request $request, Service $service)
    {
        $this->authorizeTenant($service);

        $service->update($request->only('name', 'duration', 'price', 'active'));

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
