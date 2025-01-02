<?php

namespace App\Http\Controllers;

use App\Models\Symptom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SymptomController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'date|required_with:end_date',
                'end_date' => 'date|required_with:start_date|after_or_equal:start_date',
                'severity' => 'string|in:mild,moderate,severe',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $query = Symptom::where('baby_id', $request->baby->id)
                ->orderBy('onset_date', 'desc');

            if ($request->has(['start_date', 'end_date'])) {
                $query->whereBetween('onset_date', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            if ($request->has('severity')) {
                $query->where('severity', $request->severity);
            }

            if ($request->has('is_active')) {
                if ($request->is_active) {
                    $query->whereNull('resolved_date');
                } else {
                    $query->whereNotNull('resolved_date');
                }
            }

            $symptoms = $query->paginate(20);
            return response()->json($symptoms);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch symptoms'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'onset_date' => 'required|date',
                'severity' => 'required|string|in:mild,moderate,severe',
                'description' => 'required|string',
                'triggers' => 'nullable|string',
                'related_conditions' => 'nullable|string',
                'notes' => 'nullable|string',
                'resolved_date' => 'nullable|date|after_or_equal:onset_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $symptom = new Symptom($request->all());
            $symptom->baby_id = $request->baby->id;
            $symptom->save();

            return response()->json($symptom, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create symptom'], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $symptom = Symptom::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            return response()->json($symptom);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Symptom not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'onset_date' => 'required|date',
                'severity' => 'required|string|in:mild,moderate,severe',
                'description' => 'required|string',
                'triggers' => 'nullable|string',
                'related_conditions' => 'nullable|string',
                'notes' => 'nullable|string',
                'resolved_date' => 'nullable|date|after_or_equal:onset_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $symptom = Symptom::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            $symptom->update($request->all());

            return response()->json($symptom);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update symptom'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $symptom = Symptom::where('baby_id', $request->baby->id)
                ->findOrFail($id);
            $symptom->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete symptom'], 500);
        }
    }

    public function getTrends(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $symptom = Symptom::where('baby_id', $request->baby->id)
                ->findOrFail($id);

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            // Get all symptoms with the same name in the date range
            $relatedSymptoms = Symptom::where('baby_id', $request->baby->id)
                ->where('name', $symptom->name)
                ->whereBetween('onset_date', [$startDate, $endDate])
                ->orderBy('onset_date')
                ->get();

            // Calculate trends
            $trends = [
                'total_occurrences' => $relatedSymptoms->count(),
                'average_duration' => $relatedSymptoms->filter(function ($s) {
                    return $s->resolved_date !== null;
                })->avg(function ($s) {
                    return Carbon::parse($s->onset_date)->diffInDays($s->resolved_date);
                }),
                'severity_distribution' => [
                    'mild' => $relatedSymptoms->where('severity', 'mild')->count(),
                    'moderate' => $relatedSymptoms->where('severity', 'moderate')->count(),
                    'severe' => $relatedSymptoms->where('severity', 'severe')->count(),
                ],
                'monthly_occurrences' => $relatedSymptoms->groupBy(function ($s) {
                    return Carbon::parse($s->onset_date)->format('Y-m');
                })->map->count(),
            ];

            return response()->json($trends);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch symptom trends'], 500);
        }
    }
} 