<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function index()
    {
        return Appointment::query()
            ->with('client:first_name,last_name')
            ->when(request('status'), function ($query) {
                return $query->where('status', AppointmentStatus::from(request('status')));
            })
            ->latest()
            ->paginate()
            ->through(fn ($appointment) => [
                'id' => $appointment->id,
                'start_time' => $appointment->start_time->format('Y-m-d h:i A'),
                
                'status' => [
                    'name' => $appointment->status->name,
                    'color' => $appointment->status->color(),
                ],
                'requestor_name' => $appointment->requestor_name,
                'description' => $appointment->description,
                'exit_pass_code' => $appointment->exit_pass_code,
                'transfer_location_from' => $appointment->transfer_location_from,
                'transfer_location_to' => $appointment->transfer_location_to,
                'item_condition' => $appointment->item_condition,
            ]);
    }

    public function store()
    {
        $validated = request()->validate([
            'requestor_name' => 'required',
            'description' => 'required',
            'start_time' => 'required',
            'transfer_location_from' => 'required',
            'exit_pass_code' => 'required',
            'transfer_location_to' => 'required',
            'item_condition' => 'required',
        ]
        );

        Appointment::create([
            'requestor_name' => $validated['requestor_name'], // "client_id" => "requestor_name
            'start_time' => $validated['start_time'],
            
            'description' => $validated['description'],
            'status' => AppointmentStatus::SCHEDULED,
            'exit_pass_code' => $validated['exit_pass_code'],
            'transfer_location_from' => $validated['transfer_location_from'],
            'transfer_location_to' => $validated['transfer_location_to'],
            'item_condition' => $validated['item_condition'],
        ]);

        return response()->json(['message' => 'success']);
    }

    public function edit(Appointment $appointment)
    {
        return $appointment;
    }

    public function update(Appointment $appointment)
    {
        $validated = request()->validate([
            'requestor_name' => 'required', // 'client_id' => 'required
            'description' => 'required',
            'start_time' => 'required',
            
            'exit_pass_code' => 'required',
            'transfer_location_from' => 'required',
            'transfer_location_to' => 'required',
            'item_condition' => 'required',
        ]);
        
        $appointment->update($validated);

        return response()->json(['success' => true]);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json(['success' => true], 200);
    }
}
