<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'date|required_with:end_date',
                'end_date' => 'date|required_with:start_date|after_or_equal:start_date',
                'status' => 'string|in:scheduled,completed,cancelled,rescheduled'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $query = Appointment::where('baby_id', $request->baby->id)
                ->orderBy('appointment_date', 'desc');

            if ($request->has(['start_date', 'end_date'])) {
                $timezone = $request->header('Timezone', 'Asia/Manila');
                $startDate = Carbon::parse($request->start_date, $timezone)->setTimezone('UTC');
                $endDate = Carbon::parse($request->end_date, $timezone)->setTimezone('UTC');
                
                $query->whereBetween('appointment_date', [
                    $startDate,
                    $endDate
                ]);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $appointments = $query->paginate(20);
            return response()->json($appointments);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch appointments', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $timezone = $request->header('Timezone', 'Asia/Manila');
            $now = Carbon::now($timezone);

            $validator = Validator::make($request->all(), [
                'appointment_date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($now, $timezone) {
                        $appointmentDate = Carbon::parse($value, $timezone);
                        if ($appointmentDate->lt($now)) {
                            $fail('The appointment date must be in the future.');
                        }
                    },
                ],
                'doctor_name' => 'required|string|max:255',
                'clinic_location' => 'nullable|string|max:255',
                'purpose' => 'required|string',
                'notes' => 'nullable|string',
                'status' => 'string|in:scheduled,completed,cancelled,rescheduled',
                'reminder_enabled' => 'boolean',
                'reminder_minutes_before' => 'integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $appointment = new Appointment($request->all());
            $appointment->baby_id = $request->baby->id;
            $appointment->timezone = $timezone;
            $appointment->save();

            return response()->json($appointment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create appointment', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $appointment = Appointment::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            return response()->json($appointment);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $timezone = $request->header('Timezone', 'Asia/Manila');
            $now = Carbon::now($timezone);

            $validator = Validator::make($request->all(), [
                'appointment_date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($now, $timezone) {
                        $appointmentDate = Carbon::parse($value, $timezone);
                        if ($appointmentDate->lt($now)) {
                            $fail('The appointment date must be in the future.');
                        }
                    },
                ],
                'doctor_name' => 'required|string|max:255',
                'clinic_location' => 'nullable|string|max:255',
                'purpose' => 'required|string',
                'notes' => 'nullable|string',
                'status' => 'string|in:scheduled,completed,cancelled,rescheduled',
                'reminder_enabled' => 'boolean',
                'reminder_minutes_before' => 'integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $appointment = Appointment::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            
            $data = $request->all();
            $data['timezone'] = $timezone;
            $appointment->update($data);

            return response()->json($appointment);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update appointment', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $appointment = Appointment::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            $appointment->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete appointment'], 500);
        }
    }

    public function getUpcoming(Request $request)
    {
        try {
            $timezone = $request->header('Timezone', 'Asia/Manila');
            $now = Carbon::now($timezone)->setTimezone('UTC');

            $appointments = Appointment::where('baby_id', $request->baby->id)
                ->where('appointment_date', '>', $now)
                ->where('status', 'scheduled')
                ->orderBy('appointment_date', 'asc')
                ->take(5)
                ->get();

            return response()->json($appointments);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch upcoming appointments', 'error' => $e->getMessage()], 500);
        }
    }
} 