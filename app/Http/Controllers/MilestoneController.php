<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Baby;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    private function checkBabyAccess($babyId)
    {
        $baby = Baby::where('id', $babyId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$baby) {
            throw new \Exception('Unauthorized access to baby data');
        }

        return $baby;
    }

    public function index($babyId)
    {
        try {
            $baby = $this->checkBabyAccess($babyId);

            $milestones = Milestone::where('baby_id', $baby->id)
                ->orderBy('category')
                ->orderBy('id')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $milestones
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching milestones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching milestones'
            ], 500);
        }
    }

    public function toggle($babyId, $milestoneId)
    {
        try {
            $baby = $this->checkBabyAccess($babyId);

            $milestone = Milestone::where('id', $milestoneId)
                ->where('baby_id', $baby->id)
                ->first();

            if (!$milestone) {
                return response()->json(['message' => 'Milestone not found'], 404);
            }

            $milestone->completed = !$milestone->completed;
            $milestone->completed_at = $milestone->completed ? now() : null;
            $milestone->save();

            return response()->json([
                'success' => true,
                'data' => $milestone
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling milestone: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling milestone'
            ], 500);
        }
    }

    public function initializeMilestones($babyId)
    {
        try {
            $baby = $this->checkBabyAccess($babyId);

            // Default milestones by age category
            $defaultMilestones = [
                '0-3 months' => [
                    'Raises head and chest when lying on stomach',
                    'Stretches and kicks while lying on back',
                    'Opens and shuts hands',
                    'Brings hands to face',
                    'Smiles at the sound of your voice',
                ],
                '4-6 months' => [
                    'Rolls over in both directions',
                    'Begins to sit without support',
                    'Supports whole weight on legs',
                    'Reaches for toy with one hand',
                    'Responds to own name',
                ],
                '7-9 months' => [
                    'Stands holding on',
                    'Can get into sitting position',
                    'Sits without support',
                    'Crawls forward on belly',
                    'Babbles chains of sounds',
                ],
                '10-12 months' => [
                    'Pulls up to stand',
                    'Walks holding on to furniture',
                    'Takes a few steps without holding on',
                    'Says "mama" and "dada"',
                    'Uses simple gestures like waving',
                ]
            ];

            foreach ($defaultMilestones as $category => $milestones) {
                foreach ($milestones as $title) {
                    Milestone::firstOrCreate([
                        'baby_id' => $baby->id,
                        'category' => $category,
                        'title' => $title
                    ], [
                        'completed' => false,
                        'completed_at' => null
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Milestones initialized successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error initializing milestones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error initializing milestones'
            ], 500);
        }
    }
} 