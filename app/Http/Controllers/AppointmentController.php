<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Tenant;
use App\Traits\HasFilters;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    use HasFilters;

    public function index(Request $request)
    {
         
        $query = Appointment::where('tenant_id', app('tenant_id'));

        $filters = [
            'date' => 'exact',
            'service_id' => 'exact',
            'status' => 'exact',
            'q' => 'like',
        ];

        $searchable = ['client_name', 'client_phone']; // campos pesquisáveis pelo 'q'
        $allowedSorts = ['date', 'start_time', 'client_name', 'status'];

        $query = $this->applyFilters($query, $request, $filters, $searchable, $allowedSorts, 'date');

        $appointments = $query->paginate($request->get('per_page', 15));

        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'client_name' => 'required|string',
            'client_phone' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'required',
        ]);

        $data['tenant_id'] = app('tenant_id');
        $data['user_id'] = auth()->id();

        $service = Service::findOrFail($data['service_id']);
        $data['end_time'] = date('H:i', strtotime($data['start_time'] . " + {$service->duration} minutes"));

        $data['status'] = 'pending';

        $exists = Appointment::where('tenant_id', $data['tenant_id'])
            ->where('date', $data['date'])
            ->where(function($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                ->orWhere(function($q) use ($data) {
                    $q->where('start_time', '<', $data['start_time'])
                        ->where('end_time', '>', $data['end_time']);
                });
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'start_time' => ['Já existe um agendamento nesse horário.']
            ]);
        }

        $appointment = Appointment::create($data);

        return response()->json($appointment, 201);
    }

    public function show(Appointment $appointment)
    {
        $this->authorizeTenant($appointment);
        return $appointment->load('service');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorizeTenant($appointment);

        $data = $request->validate([
            'date' => 'sometimes|date',
            'start_time' => 'sometimes',
            'status' => 'sometimes|string',
            'client_name' => 'sometimes|string'
        ]);

        if (isset($data['start_time'])) {
            $service = $appointment->service; // pega o serviço existente
            $data['end_time'] = date('H:i', strtotime($data['start_time'] . " + {$service->duration} minutes"));
        }

        if (isset($data['start_time']) || isset($data['date'])) {
            $newDate = $data['date'] ?? $appointment->date;
            $newStart = $data['start_time'] ?? $appointment->start_time;
            $newEnd = $data['end_time'] ?? $appointment->end_time;

            $conflict = Appointment::where('service_id', $appointment->service_id)
                ->where('date', $newDate)
                ->where('id', '<>', $appointment->id) // ignora a própria
                ->where(function($query) use ($newStart, $newEnd) {
                    $query->whereBetween('start_time', [$newStart, $newEnd])
                        ->orWhereBetween('end_time', [$newStart, $newEnd])
                        ->orWhere(function($q) use ($newStart, $newEnd) {
                            $q->where('start_time', '<', $newStart)
                                ->where('end_time', '>', $newEnd);
                        });
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'Conflito de horário: já existe uma appointment para esse serviço nesse horário.'
                ], 422);
            }
        }

        $appointment->update($data);
        $appointment->refresh();
        return $appointment->load('service');
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorizeTenant($appointment);
        $appointment->delete();

        return response()->noContent();
    }

    private function authorizeTenant(Appointment $appointment)
    {
        if ($appointment->tenant_id !== app('tenant_id')) {
            abort(403, 'Acesso negado');
        }
    }
}
