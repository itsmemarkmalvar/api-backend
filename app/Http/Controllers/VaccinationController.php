<?php

namespace App\Http\Controllers;

use App\Models\Baby;
use App\Models\VaccinationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VaccinationController extends Controller
{
    // Master list of all possible vaccines
    private $defaultVaccines = [
        ['ageGroup' => 'Birth', 'vaccines' => [
            ['id' => 'bcg', 'name' => 'BCG'],
            ['id' => 'hepb1', 'name' => 'Hepatitis B (1st dose)']
        ]],
        ['ageGroup' => '6 Weeks', 'vaccines' => [
            ['id' => 'dtap1', 'name' => 'DTaP (1st dose)'],
            ['id' => 'ipv1', 'name' => 'IPV (1st dose)'],
            ['id' => 'hib1', 'name' => 'Hib (1st dose)'],
            ['id' => 'pcv1', 'name' => 'PCV (1st dose)'],
            ['id' => 'rv1', 'name' => 'Rotavirus (1st dose)']
        ]],
        ['ageGroup' => '10 Weeks', 'vaccines' => [
            ['id' => 'dtap2', 'name' => 'DTaP (2nd dose)'],
            ['id' => 'ipv2', 'name' => 'IPV (2nd dose)'],
            ['id' => 'hib2', 'name' => 'Hib (2nd dose)'],
            ['id' => 'pcv2', 'name' => 'PCV (2nd dose)'],
            ['id' => 'rv2', 'name' => 'Rotavirus (2nd dose)']
        ]],
        ['ageGroup' => '14 Weeks', 'vaccines' => [
            ['id' => 'dtap3', 'name' => 'DTaP (3rd dose)'],
            ['id' => 'ipv3', 'name' => 'IPV (3rd dose)'],
            ['id' => 'hib3', 'name' => 'Hib (3rd dose)'],
            ['id' => 'pcv3', 'name' => 'PCV (3rd dose)'],
            ['id' => 'rv3', 'name' => 'Rotavirus (3rd dose)']
        ]],
        ['ageGroup' => '6 Months', 'vaccines' => [
            ['id' => 'hepb2', 'name' => 'Hepatitis B (2nd dose)'],
            ['id' => 'flu1', 'name' => 'Influenza (1st dose)']
        ]],
        ['ageGroup' => '9 Months', 'vaccines' => [
            ['id' => 'mmr1', 'name' => 'MMR (1st dose)'],
            ['id' => 'var1', 'name' => 'Varicella (1st dose)']
        ]]
    ];

    public function index(Request $request)
    {
        try {
            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            // Get all vaccination logs for this baby
            $vaccineLogs = VaccinationLog::where('baby_id', $baby->id)
                ->get()
                ->groupBy('vaccine_id')
                ->map(function ($logs) {
                    // Get the most recent log for each vaccine
                    return $logs->sortByDesc('created_at')->first();
                });

            // Merge the vaccination status with the master list
            $vaccineList = collect($this->defaultVaccines)->map(function ($ageGroup) use ($vaccineLogs) {
                return [
                    'ageGroup' => $ageGroup['ageGroup'],
                    'vaccines' => collect($ageGroup['vaccines'])->map(function ($vaccine) use ($vaccineLogs) {
                        $log = $vaccineLogs->get($vaccine['id']);
                        return [
                            'id' => $vaccine['id'],
                            'name' => $vaccine['name'],
                            'completed' => $log ? $log->status === 'completed' : false,
                            'date' => $log ? ($log->status === 'completed' ? $log->given_at : $log->scheduled_date) : null
                        ];
                    })->values()->all()
                ];
            })->values();

            return response()->json($vaccineList);
        } catch (\Exception $e) {
            \Log::error('Error in VaccinationController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to fetch vaccinations'], 500);
        }
    }

    public function markAsCompleted(Request $request)
    {
        try {
            $validated = $request->validate([
                'vaccine_id' => 'required|string',
                'given_at' => 'required|date',
                'administered_by' => 'nullable|string',
                'administered_at' => 'nullable|string',
                'notes' => 'nullable|string'
            ]);

            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            // Find vaccine info from master list
            $vaccineInfo = null;
            foreach ($this->defaultVaccines as $ageGroup) {
                foreach ($ageGroup['vaccines'] as $vaccine) {
                    if ($vaccine['id'] === $validated['vaccine_id']) {
                        $vaccineInfo = [
                            'name' => $vaccine['name'],
                            'age_group' => $ageGroup['ageGroup']
                        ];
                        break 2;
                    }
                }
            }

            if (!$vaccineInfo) {
                return response()->json(['message' => 'Invalid vaccine ID'], 400);
            }

            // Create or update vaccination log
            $vaccinationLog = VaccinationLog::updateOrCreate(
                [
                    'baby_id' => $baby->id,
                    'vaccine_id' => $validated['vaccine_id']
                ],
                [
                    'user_id' => Auth::id(),
                    'vaccine_name' => $vaccineInfo['name'],
                    'age_group' => $vaccineInfo['age_group'],
                    'status' => 'completed',
                    'given_at' => $validated['given_at'],
                    'administered_by' => $validated['administered_by'],
                    'administered_at' => $validated['administered_at'],
                    'notes' => $validated['notes'],
                    'scheduled_date' => null // Clear any scheduled date
                ]
            );

            return response()->json([
                'message' => 'Vaccination marked as completed',
                'vaccination' => $vaccinationLog
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking vaccination as completed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'Failed to mark vaccination as completed'], 500);
        }
    }

    public function getVaccinationHistory(Request $request)
    {
        try {
            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            $logs = VaccinationLog::where('baby_id', $baby->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($logs);
        } catch (\Exception $e) {
            \Log::error('Error fetching vaccination history', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to fetch vaccination history'], 500);
        }
    }

    public function schedule(Request $request)
    {
        try {
            $validated = $request->validate([
                'vaccine_id' => 'required|string',
                'scheduled_date' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            // Find vaccine info from master list
            $vaccineInfo = null;
            foreach ($this->defaultVaccines as $ageGroup) {
                foreach ($ageGroup['vaccines'] as $vaccine) {
                    if ($vaccine['id'] === $validated['vaccine_id']) {
                        $vaccineInfo = [
                            'name' => $vaccine['name'],
                            'age_group' => $ageGroup['ageGroup']
                        ];
                        break 2;
                    }
                }
            }

            if (!$vaccineInfo) {
                return response()->json(['message' => 'Invalid vaccine ID'], 400);
            }

            // Check if vaccine is already completed
            $existingLog = VaccinationLog::where('baby_id', $baby->id)
                ->where('vaccine_id', $validated['vaccine_id'])
                ->where('status', 'completed')
                ->first();

            if ($existingLog) {
                return response()->json(['message' => 'This vaccine has already been completed'], 400);
            }

            // Create or update vaccination log
            $vaccinationLog = VaccinationLog::updateOrCreate(
                [
                    'baby_id' => $baby->id,
                    'vaccine_id' => $validated['vaccine_id']
                ],
                [
                    'user_id' => Auth::id(),
                    'vaccine_name' => $vaccineInfo['name'],
                    'age_group' => $vaccineInfo['age_group'],
                    'status' => 'scheduled',
                    'scheduled_date' => $validated['scheduled_date'],
                    'notes' => $validated['notes'],
                    'given_at' => null // Clear any completion date
                ]
            );

            return response()->json([
                'message' => 'Vaccination scheduled successfully',
                'vaccination' => $vaccinationLog
            ]);
        } catch (\Exception $e) {
            \Log::error('Error scheduling vaccination', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'Failed to schedule vaccination'], 500);
        }
    }
} 