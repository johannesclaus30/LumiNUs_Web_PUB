<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TracerForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TracerFormController extends Controller
{
    protected $supabaseDisk;

    public function __construct()
    {
        // Initialize Supabase storage disk
        $this->supabaseDisk = Storage::disk('supabase_admin');
    }

    public function index()
    {
        return view('admin_alumni_tracer');
    }

    public function list()
    {
        try {
            // Get all non-deleted forms (status != 0)
            $forms = TracerForm::with(['questions.options'])
                ->where('status', '!=', TracerForm::STATUS_DELETED)
                ->orderByDesc('created_at')
                ->get();

            return response()->json($forms);
        } catch (\Throwable $e) {
            \Log::error('Failed to load tracer forms: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load forms',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleted()
    {
        try {
            // Get only deleted forms (status = 0)
            $forms = TracerForm::with(['questions.options'])
                ->where('status', TracerForm::STATUS_DELETED)
                ->orderByDesc('updated_at')
                ->get();

            return response()->json($forms);
        } catch (\Throwable $e) {
            \Log::error('Failed to load deleted tracer forms: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load deleted forms',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $form = TracerForm::with(['questions.options'])
                ->where('status', '!=', TracerForm::STATUS_DELETED)
                ->findOrFail($id);

            return response()->json($form);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Form not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_title' => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'form_header' => 'nullable|string',
            'status' => 'integer|in:1,2,3', // 1=active, 2=draft, 3=closed
            'questions' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // Process form header image if present
            $formHeaderPath = null;
            if (!empty($validated['form_header'])) {
                $formHeaderPath = $this->uploadFormHeader($validated['form_header'], null);
            }

            $form = TracerForm::create([
                'admin_id' => auth()->id() ?? 1,
                'form_title' => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'form_header' => $formHeaderPath,
                'status' => $validated['status'] ?? TracerForm::STATUS_ACTIVE,
            ]);

            if (!empty($validated['questions'])) {
                $this->saveQuestions($form, $validated['questions']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tracer form saved successfully.',
                'form' => $form->load('questions.options')
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to save tracer form: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to save tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $form = TracerForm::findOrFail($id);

        $validated = $request->validate([
            'form_title' => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'form_header' => 'nullable|string',
            'status' => 'integer|in:1,2,3',
            'questions' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // Process form header image if present
            $formHeaderPath = $form->form_header;
            if (!empty($validated['form_header'])) {
                // Check if it's base64 data (new upload) or existing URL
                if (Str::startsWith($validated['form_header'], 'data:image')) {
                    $formHeaderPath = $this->uploadFormHeader($validated['form_header'], $form->id);
                    
                    // Delete old image if exists
                    if ($form->form_header) {
                        $this->deleteFormHeader($form->form_header);
                    }
                } else {
                    // It's already a URL/path, keep it
                    $formHeaderPath = $validated['form_header'];
                }
            }

            $form->update([
                'form_title' => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'form_header' => $formHeaderPath,
                'status' => $validated['status'] ?? $form->status,
            ]);

            // Delete existing questions and options
            foreach ($form->questions as $question) {
                $question->options()->delete();
            }
            $form->questions()->delete();

            // Save new questions
            if (!empty($validated['questions'])) {
                $this->saveQuestions($form, $validated['questions']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tracer form updated successfully.',
                'form' => $form->load('questions.options')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to update tracer form: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $form = TracerForm::findOrFail($id);
            
            // Delete form header image if exists
            if ($form->form_header) {
                $this->deleteFormHeader($form->form_header);
            }
            
            // Set status to deleted (0)
            $form->update(['status' => TracerForm::STATUS_DELETED]);

            return response()->json([
                'message' => 'Tracer form deleted successfully.',
                'form' => $form->load('questions.options'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete tracer form: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $form = TracerForm::findOrFail($id);
            
            // Only restore if actually deleted
            if ($form->status !== TracerForm::STATUS_DELETED) {
                return response()->json([
                    'message' => 'Form is not deleted.',
                    'form' => $form->load('questions.options'),
                ], 400);
            }
            
            // Restore by setting status back to active (1)
            $form->update(['status' => TracerForm::STATUS_ACTIVE]);

            return response()->json([
                'message' => 'Tracer form restored successfully.',
                'form' => $form->load('questions.options'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to restore tracer form: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to restore tracer form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $form = TracerForm::findOrFail($id);
            $newStatus = $request->integer('status');
            
            // Validate status
            if (!in_array($newStatus, [TracerForm::STATUS_ACTIVE, TracerForm::STATUS_CLOSED, TracerForm::STATUS_DRAFT])) {
                return response()->json(['message' => 'Invalid status.'], 400);
            }
            
            $form->update(['status' => $newStatus]);

            return response()->json(['message' => 'Status updated successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload form header image to Supabase storage
     */
    private function uploadFormHeader(string $base64Image, ?int $formId)
    {
        try {
            // Decode base64 image
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif, etc.
                
                $base64Image = str_replace(' ', '+', $base64Image);
                $imageData = base64_decode($base64Image);
                
                if ($imageData === false) {
                    throw new \Exception('Failed to decode base64 image');
                }
                
                // Generate unique filename
                $filename = 'header_' . time() . '_' . Str::random(10) . '.' . $type;
                
                // Determine folder path
                $folderPath = $formId 
                    ? "tracer_images/{$formId}/" 
                    : "tracer_images/temp_" . Str::random(10) . "/";
                
                $fullPath = $folderPath . $filename;
                
                // Upload to Supabase
                $this->supabaseDisk->put($fullPath, $imageData, [
                    'visibility' => 'public',
                    'Content-Type' => 'image/' . $type
                ]);
                
                // Get public URL
                $url = $this->supabaseDisk->url($fullPath);
                
                return $url;
            }
            
            throw new \Exception('Invalid image format');
            
        } catch (\Exception $e) {
            \Log::error('Failed to upload form header: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete form header image from Supabase storage
     */
    private function deleteFormHeader(string $imageUrl)
    {
        try {
            // Extract path from URL
            // Assuming URL format: https://your-project.supabase.co/storage/v1/object/public/luminus_assets/tracer_images/...
            $path = parse_url($imageUrl, PHP_URL_PATH);
            
            // Extract the path after the bucket name
            if (preg_match('/luminus_assets\/(.+)$/', $path, $matches)) {
                $filePath = $matches[1];
                $this->supabaseDisk->delete($filePath);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to delete form header: ' . $e->getMessage());
            // Don't throw exception, just log it
        }
    }

    private function saveQuestions(TracerForm $form, array $questions)
    {
        foreach ($questions as $index => $questionData) {
            $question = $form->questions()->create([
                'type' => $questionData['type'] ?? 'text',
                'question_text' => $questionData['question_text'] ?? '',
                'description' => $questionData['subtitle'] ?? null,
                'is_required' => $questionData['required'] ?? false,
                'order_priority' => $index,
                'settings' => [
                    'scale_points' => $questionData['scale_points'] ?? null,
                    'scale_labels' => $questionData['scale_labels'] ?? null,
                    'other_enabled' => $questionData['other_enabled'] ?? false,
                    'display_type' => $questionData['display_type'] ?? 'list',
                    'statements' => $questionData['statements'] ?? [],
                ],
            ]);

            if (!empty($questionData['options']) && is_array($questionData['options'])) {
                foreach ($questionData['options'] as $optIndex => $optionData) {
                    $question->options()->create([
                        'option_label' => $optionData['label'] ?? '',
                        'option_value' => $optionData['label'] ?? '',
                        'order_priority' => $optIndex,
                    ]);
                }
            }
        }
    }
}