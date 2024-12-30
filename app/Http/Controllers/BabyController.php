<?php

namespace App\Http\Controllers;

use App\Models\Baby;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BabyController extends Controller
{
    public function __construct()
    {
        // Remove this line as it's causing the error
        // $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        // Add detailed logging
        \Log::info('Baby data store attempt', [
            'user_id' => Auth::id(),
            'auth_header' => $request->header('Authorization'),
            'request_data' => $request->all()
        ]);

        if (!Auth::id()) {
            \Log::error('No authenticated user found');
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'No valid authentication token found'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'birth_date' => 'required|date|before_or_equal:today',
                'height' => 'required|numeric|between:20,120',
                'weight' => 'required|numeric|between:1,30',
                'head_size' => 'required|numeric|between:20,60',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);

            $baby = Baby::create([
                'user_id' => Auth::id(),
                ...$validated
            ]);

            \Log::info('Baby created successfully', ['baby_id' => $baby->id]);

            return response()->json([
                'message' => 'Baby information saved successfully',
                'data' => $baby
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error saving baby data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error saving baby information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show()
    {
        try {
            \Log::info('Fetching baby data for user:', ['user_id' => Auth::id()]);
            
            $baby = Baby::where('user_id', Auth::id())->first();
            
            if (!$baby) {
                \Log::info('No baby found for user:', ['user_id' => Auth::id()]);
                return response()->json(['message' => 'Baby not found'], 404);
            }

            // Clean and validate base64 data before sending
            if ($baby->photo_url) {
                try {
                    // Extract the base64 part
                    if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $baby->photo_url, $matches)) {
                        $imageType = $matches[1];
                        $base64Data = $matches[2];
                        
                        // Validate base64 data
                        if (base64_decode($base64Data, true) === false) {
                            \Log::error('Invalid base64 data detected, removing photo_url');
                            $baby->photo_url = null;
                            $baby->save();
                        }
                    } else {
                        \Log::error('Invalid image format detected, removing photo_url');
                        $baby->photo_url = null;
                        $baby->save();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error processing photo_url:', [
                        'error' => $e->getMessage(),
                        'photo_url_length' => strlen($baby->photo_url)
                    ]);
                    $baby->photo_url = null;
                    $baby->save();
                }
            }

            \Log::info('Baby data found:', [
                'has_photo' => !empty($baby->photo_url),
                'photo_length' => $baby->photo_url ? strlen($baby->photo_url) : 0,
                'photo_prefix' => $baby->photo_url ? substr($baby->photo_url, 0, 30) : null
            ]);
            
            return response()->json(['data' => $baby]);
        } catch (\Exception $e) {
            \Log::error('Error fetching baby data:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['message' => 'Error fetching baby data'], 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        try {
            \Log::info('Photo upload attempt', [
                'user_id' => Auth::id(),
                'has_file' => $request->hasFile('photo'),
                'content_type' => $request->header('Content-Type')
            ]);

            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120'
            ]);

            $baby = Baby::where('user_id', Auth::id())->first();
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            if ($request->hasFile('photo')) {
                try {
                    $image = $request->file('photo');
                    
                    // Log image details
                    \Log::info('Processing image:', [
                        'original_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getMimeType(),
                        'size' => $image->getSize()
                    ]);

                    // Read and encode image content
                    $imageContent = file_get_contents($image->getRealPath());
                    if ($imageContent === false) {
                        throw new \Exception('Failed to read image content');
                    }

                    // Clean and encode the image data
                    $imageData = base64_encode($imageContent);
                    if (empty($imageData)) {
                        throw new \Exception('Failed to encode image to base64');
                    }

                    // Create a clean base64 image string
                    $mimeType = $image->getMimeType();
                    $base64Image = "data:{$mimeType};base64,{$imageData}";

                    // Verify the base64 string is valid
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image) !== 1) {
                        throw new \Exception('Invalid base64 image format');
                    }
                    
                    // Store base64 string in database
                    $baby->photo_url = $base64Image;
                    $baby->save();

                    // Verify stored data
                    $baby->refresh();
                    \Log::info('Photo stored successfully', [
                        'baby_id' => $baby->id,
                        'stored_length' => strlen($baby->photo_url),
                        'mime_type' => $mimeType,
                        'base64_prefix' => substr($base64Image, 0, 50)
                    ]);

                    return response()->json([
                        'message' => 'Photo uploaded successfully',
                        'photo_url' => $baby->photo_url
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error processing image:', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'message' => 'Error processing image: ' . $e->getMessage()
                    ], 500);
                }
            }

            return response()->json(['message' => 'No photo file received'], 400);
        } catch (\Exception $e) {
            \Log::error('Error in upload process:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error uploading photo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            \Log::info('Baby profile update attempt', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'birth_date' => 'required|date|before_or_equal:today',
            ]);

            $baby = Baby::where('user_id', Auth::id())->first();
            
            if (!$baby) {
                return response()->json(['message' => 'Baby not found'], 404);
            }

            $baby->update($validated);

            \Log::info('Baby profile updated successfully', [
                'baby_id' => $baby->id,
                'updated_fields' => array_keys($validated)
            ]);

            return response()->json([
                'message' => 'Baby information updated successfully',
                'data' => $baby
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating baby profile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error updating baby information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 