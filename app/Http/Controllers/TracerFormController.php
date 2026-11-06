<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TracerForm;
use App\Models\TracerPhase;
use App\Models\TracerSection;
use App\Models\TracerQuestion;
use App\Models\TracerResponse;
use App\Models\Alumni;
use App\Services\BrevoMailService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TracerFormController extends Controller
{
    /**
     * Show the tracer admin page.
     */
    public function index()
    {
        return view('admin_alumni_tracer');
    }

    /**
     * Get all forms with full nested structure.
     */
    public function list()
    {
        try {
            $forms = TracerForm::with($this->nestedEagerLoad())
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

    /**
     * Get deleted forms only.
     */
    public function deleted()
    {
        try {
            $forms = TracerForm::with($this->nestedEagerLoad())
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

    /**
     * Get a single form with full nested structure.
     */
    public function show($id)
    {
        try {
            $form = TracerForm::with($this->nestedEagerLoad())
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

    /**
     * Create a new tracer form with phases, sections, and questions.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_title'       => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'status'           => 'integer|in:0,1,2,3',
            'phases'           => 'nullable|array',
            'phases.*.title'    => 'required|string|max:255',
            'phases.*.subtitle' => 'nullable|string|max:1000', 
            'phases.*.icon'     => 'nullable|string|max:100',
            'phases.*.color'    => 'nullable|string|max:20',
            'phases.*.target_alumni_type' => 'nullable|string|in:all,college,shs',
            'phases.*.sections' => 'nullable|array',
            'phases.*.sections.*.title'       => 'required_with:phases.*.sections|string|max:255',
            'phases.*.sections.*.description' => 'nullable|string|max:2000',
            'phases.*.sections.*.questions'   => 'nullable|array',
            'phases.*.sections.*.questions.*.question_text' => 'required_with:phases.*.sections.*.questions|string',
            
            'phases.*.sections.*.questions.*.type' => 'required_with:phases.*.sections.*.questions|string|in:short_answer,paragraph,multiple_choice,checkboxes,dropdown,likert_scale,multiple_choice_grid',
            
            'phases.*.sections.*.questions.*.description'  => 'nullable|string',
            'phases.*.sections.*.questions.*.placeholder'  => 'nullable|string|max:255',
            'phases.*.sections.*.questions.*.is_required'  => 'boolean',
            'phases.*.sections.*.questions.*.allow_other_option' => 'boolean',

            'phases.*.sections.*.questions.*.condition' => 'nullable|array',
            'phases.*.sections.*.questions.*.condition.depends_on_question_id' => 'nullable|integer',
            'phases.*.sections.*.questions.*.condition.match_type' => 'nullable|string|in:any,all,none',
            'phases.*.sections.*.questions.*.condition.option_labels' => 'nullable|array',
            'phases.*.sections.*.questions.*.condition.option_labels.*' => 'string|max:255',

            'phases.*.sections.*.questions.*.file_types'   => 'nullable|array',
            'phases.*.sections.*.questions.*.max_file_size' => 'nullable|integer',
            'phases.*.sections.*.questions.*.options'      => 'nullable|array',
            'phases.*.sections.*.questions.*.options.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_rows'    => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_rows.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_columns' => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_columns.*.label' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $form = TracerForm::create([
                'admin_id'         => session('admin_id') ?? 1,
                'form_title'       => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'status'           => $validated['status'] ?? TracerForm::STATUS_DRAFT,
            ]);

            if (!empty($validated['phases'])) {
                $this->savePhases($form, $validated['phases']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tracer form created successfully.',
                'form'    => $form->load($this->nestedEagerLoad())
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to create tracer form: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create tracer form.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing form (replace all phases/sections/questions).
     */
    public function update(Request $request, $id)
    {
        $form = TracerForm::findOrFail($id);

        $validated = $request->validate([
            'form_title'       => 'required|string|max:255',
            'form_description' => 'nullable|string',
            'status'           => 'integer|in:0,1,2,3',
            'phases'           => 'nullable|array',
            'phases.*.title'    => 'required|string|max:255',
            'phases.*.subtitle' => 'nullable|string|max:1000',
            'phases.*.icon'     => 'nullable|string|max:100',
            'phases.*.color'    => 'nullable|string|max:20',
            'phases.*.target_alumni_type' => 'nullable|string|in:all,college,shs',
            'phases.*.sections' => 'nullable|array',
            'phases.*.sections.*.title'       => 'required|string|max:255',
            'phases.*.sections.*.description' => 'nullable|string|max:2000',
            'phases.*.sections.*.questions'   => 'nullable|array',
            'phases.*.sections.*.questions.*.question_text' => 'required|string',
            
            'phases.*.sections.*.questions.*.type' => 'required|string|in:short_answer,paragraph,multiple_choice,checkboxes,dropdown,likert_scale,multiple_choice_grid',
            
            'phases.*.sections.*.questions.*.description'  => 'nullable|string',
            'phases.*.sections.*.questions.*.placeholder'  => 'nullable|string|max:255',
            'phases.*.sections.*.questions.*.is_required'  => 'boolean',
            'phases.*.sections.*.questions.*.file_types'   => 'nullable|array',
            'phases.*.sections.*.questions.*.max_file_size' => 'nullable|integer',
            'phases.*.sections.*.questions.*.options'      => 'nullable|array',
            'phases.*.sections.*.questions.*.options.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_rows'    => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_rows.*.label' => 'required|string',
            'phases.*.sections.*.questions.*.grid_columns' => 'nullable|array',
            'phases.*.sections.*.questions.*.grid_columns.*.label' => 'required|string',

            'phases.*.sections.*.questions.*.condition' => 'nullable|array',
            'phases.*.sections.*.questions.*.condition.depends_on_question_id' => 'nullable|integer',
            'phases.*.sections.*.questions.*.condition.match_type' => 'nullable|string|in:any,all,none',
            'phases.*.sections.*.questions.*.condition.option_labels' => 'nullable|array',
            'phases.*.sections.*.questions.*.condition.option_labels.*' => 'string|max:255',

        ]);

        DB::beginTransaction();

        try {
            $form->update([
                'form_title'       => $validated['form_title'],
                'form_description' => $validated['form_description'] ?? null,
                'status'           => $validated['status'] ?? $form->status,
            ]);

            // Delete old structure (ON DELETE CASCADE handles nested)
            $form->phases()->delete();

            // Save new structure
            if (!empty($validated['phases'])) {
                $this->savePhases($form, $validated['phases']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tracer form updated successfully.',
                'form'    => $form->load($this->nestedEagerLoad())
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to update tracer form: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update tracer form.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete a form.
     */
    public function destroy($id)
    {
        try {
            $form = TracerForm::findOrFail($id);

            // Delete form header image if exists (for backward compatibility)
            if ($form->form_header ?? null) {
                $this->deleteFormHeader($form->form_header);
            }

            $form->update(['status' => TracerForm::STATUS_DELETED]);

            return response()->json([
                'message' => 'Tracer form deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete tracer form: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete tracer form.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore a deleted form.
     */
    public function restore($id)
    {
        try {
            $form = TracerForm::findOrFail($id);

            if ($form->status !== TracerForm::STATUS_DELETED) {
                return response()->json(['message' => 'Form is not deleted.'], 400);
            }

            $form->update(['status' => TracerForm::STATUS_ACTIVE]);

            return response()->json([
                'message' => 'Tracer form restored successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to restore tracer form: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to restore tracer form.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle form status.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $form = TracerForm::findOrFail($id);
            $newStatus = $request->integer('status');

            if (!in_array($newStatus, [
                TracerForm::STATUS_DELETED,
                TracerForm::STATUS_ACTIVE,
                TracerForm::STATUS_DRAFT,
                TracerForm::STATUS_CLOSED
            ])) {
                return response()->json(['message' => 'Invalid status.'], 400);
            }

            $form->update(['status' => $newStatus]);

            return response()->json(['message' => 'Status updated successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════

    /**
     * Eager load array for nested relationships.
     */
    private function nestedEagerLoad(): array
    {
        return [
            'phases' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections.questions' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections.questions.options' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections.questions.gridRows' => function ($q) {
                $q->orderBy('order_priority');
            },
            'phases.sections.questions.gridColumns' => function ($q) {
                $q->orderBy('order_priority');
            },
        ];
    }

    /**
     * Save phases from request data.
     */
    private function savePhases(TracerForm $form, array $phases)
    {
        foreach ($phases as $phaseIdx => $phaseData) {
            $phase = $form->phases()->create([
                'title'               => $phaseData['title'],
                'subtitle'            => $phaseData['subtitle'] ?? null,
                'icon'                => $phaseData['icon'] ?? 'fa-user',
                'color'               => $phaseData['color'] ?? '#3b82f6',
                'target_alumni_type'  => $phaseData['target_alumni_type'] ?? 'all',
                'order_priority'      => $phaseIdx,
            ]);

            if (!empty($phaseData['sections'])) {
                $this->saveSections($phase, $phaseData['sections']);
            }
        }
    }

    /**
     * Save sections from request data.
     */
    private function saveSections(TracerPhase $phase, array $sections)
    {
        foreach ($sections as $sectionIdx => $sectionData) {
            $section = $phase->sections()->create([
                'title'          => $sectionData['title'],
                'description'    => $sectionData['description'] ?? null,
                'order_priority' => $sectionIdx,
            ]);

            if (!empty($sectionData['questions'])) {
                $this->saveQuestions($section, $sectionData['questions']);
            }
        }
    }

    private function saveQuestions(TracerSection $section, array $questions)
    {
        foreach ($questions as $qIdx => $qData) {
            $question = $section->questions()->create([
                'type'           => $qData['type'],
                'question_text'  => $qData['question_text'],
                'description'    => $qData['description'] ?? null,
                'placeholder'    => $qData['placeholder'] ?? null,
                'is_required'    => $qData['is_required'] ?? true,
                'order_priority' => $qIdx,
                'file_types'     => $qData['file_types'] ?? null,
                'max_file_size'  => $qData['max_file_size'] ?? null,
                'allow_other_option' => $qData['allow_other_option'] ?? false, 
            ]);

            // Save simple options (multiple_choice, checkboxes, dropdown)
            $isChoiceType = in_array($qData['type'], ['multiple_choice', 'checkboxes', 'dropdown']);

            if (!empty($qData['options'])) {
                foreach ($qData['options'] as $optIdx => $optionData) {
                    $question->options()->create([
                        'option_label'   => is_array($optionData) ? ($optionData['label'] ?? '') : $optionData,
                        'order_priority' => $optIdx,
                        'is_other'       => false,
                    ]);
                }
            }

            // Append the sentinel "Other" row for choice questions that allow it
            if ($isChoiceType && !empty($qData['allow_other_option'])) {
                $question->options()->create([
                    'option_label'   => 'Other',
                    'order_priority' => count($qData['options'] ?? []),
                    'is_other'       => true,
                ]);
            }

            // Save grid rows (likert_scale, multiple_choice_grid)
            if (!empty($qData['grid_rows'])) {
                foreach ($qData['grid_rows'] as $rowIdx => $rowData) {
                    $question->gridRows()->create([
                        'row_label'      => is_array($rowData) ? ($rowData['label'] ?? '') : $rowData,
                        'order_priority' => $rowIdx,
                    ]);
                }
            }

            // Save grid columns (likert_scale, multiple_choice_grid)
            if (!empty($qData['grid_columns'])) {
                foreach ($qData['grid_columns'] as $colIdx => $colData) {
                    $question->gridColumns()->create([
                        'column_label'   => is_array($colData) ? ($colData['label'] ?? '') : $colData,
                        'order_priority' => $colIdx,
                    ]);
                }
            }

            // ✅ Handle branching condition
            $this->syncQuestionCondition($question, $qData['condition'] ?? null);
        }
    }

    /**
     * Delete form header image from storage.
     */
    private function deleteFormHeader(string $imageUrl)
    {
        try {
            $path = parse_url($imageUrl, PHP_URL_PATH);
            if (preg_match('/luminus_assets\/(.+)$/', $path, $matches)) {
                \Storage::disk('supabase_admin')->delete($matches[1]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to delete form header: ' . $e->getMessage());
        }
    }

    /**
     * Get phase completion statistics.
     */
    private function getPhaseStats($form, $totalResponses)
    {
        try {
            $phaseStats = [];
            $canonicalResponseIds = $this->getCanonicalResponses($form->id)->pluck('id');
            foreach ($form->phases as $phase) {
                $phaseQuestionIds = TracerQuestion::whereHas('section', function ($q) use ($phase) {
                    $q->where('phase_id', $phase->id);
                })->pluck('id');
                
                $totalPhaseQuestions = $phaseQuestionIds->count();
                $completionRate = 0;
                
                if ($totalPhaseQuestions > 0 && $totalResponses > 0) {
                    $totalPossibleAnswers = $totalPhaseQuestions * $totalResponses;
                    
                    try {
                        // We use a raw DB query to count the base table
                        $actualAnswers = DB::table('tracer_answers_base')
                            ->whereIn('question_id', $phaseQuestionIds)
                            ->whereIn('tracer_response_id', $canonicalResponseIds)
                            ->where(function ($query) {
                                $query->where(function ($valueQuery) {
                                    $valueQuery->whereNotNull('answer_value')
                                        ->where('answer_value', '<>', '');
                                })->orWhereExists(function ($selectionQuery) {
                                    $selectionQuery->select(DB::raw(1))
                                        ->from('tracer_answer_selections')
                                        ->whereColumn(
                                            'tracer_answer_selections.tracer_answer_id',
                                            'tracer_answers_base.id'
                                        );
                                });
                            })
                            ->distinct('question_id')
                            ->count('question_id');
                    } catch (\Throwable $e) {
                        // If the table doesn't exist or query fails, default to 0
                        // instead of crashing the whole Dashboard.
                        \Log::error('getPhaseStats query failed, falling back to 0: ' . $e->getMessage());
                        $actualAnswers = 0;
                    }
                    
                    $completionRate = $totalPossibleAnswers > 0 
                        ? round(($actualAnswers / $totalPossibleAnswers) * 100, 1) : 0;
                }
                
                $phaseStats[] = [
                    'title'          => $phase->title,
                    'color'          => $phase->color,
                    'completionRate' => $completionRate,
                    'icon'           => $phase->icon,
                ];
            }
            return $phaseStats;
        } catch (\Throwable $e) {
            \Log::error('Failed to get phase stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get dashboard statistics for a form.
     */
    public function dashboardStats($formId)
    {
        try {
            $form = TracerForm::findOrFail($formId);
            $canonicalResponses = $this->getCanonicalResponses($formId);

            $totalAlumni = \App\Models\Alumni::count();
            $totalResponses = $canonicalResponses->count();
            $completedResponses = $canonicalResponses->where('status', 'completed')->count();
            $inProgressResponses = $totalResponses - $completedResponses;

            // Count total questions
            $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($formId) {
                $q->where('id', $formId);
            })->count();

            // Get phase count
            $phaseCount = $form->phases()->count();
            $sectionCount = TracerSection::whereHas('phase.form', function ($q) use ($formId) {
                $q->where('id', $formId);
            })->count();

            // NEW: Get phase, program, year, and top responder stats
            $phaseStats = $this->getPhaseStats($form, $totalResponses);
            $programStats = $this->getProgramStats($form);
            $yearStats = $this->getYearStats($form);
            $topResponders = $this->getTopResponders($form);

            return response()->json([
                'totalAlumni'        => $totalAlumni,
                'completedResponses' => $completedResponses,
                'inProgressResponses'=> $inProgressResponses,
                'totalQuestions'     => $totalQuestions,
                'phaseCount'         => $phaseCount,
                'sectionCount'       => $sectionCount,
                'responseRate'       => $totalAlumni > 0 ? round(($totalResponses / $totalAlumni) * 100, 1) : 0,
                'phaseStats'         => $phaseStats,
                'programStats'       => $programStats,
                'yearStats'          => $yearStats,
                'topResponders'      => $topResponders,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to load recent tracer submissions', [
                'form_id' => $formId,
                'exception' => $e,
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get recent submissions for dashboard.
     */
    public function recentSubmissions($formId)
    {
        try {
            $submissions = $this->getCanonicalResponses($formId)
                ->sortByDesc('updated_at')
                ->take(10)
                ->map(function ($response) {
                    $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($response) {
                        $q->where('id', $response->form_id);
                    })->count();

                    $answeredQuestions = $response->answers
                        ->filter(fn ($answer) => $this->answerHasValue($answer))
                        ->pluck('question_id')
                        ->unique()
                        ->count();
                    $completion = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;

                    return [
                        'id' => $response->id,
                        'alumni_name' => $response->alumni
                            ? trim($response->alumni->first_name . ' ' . $response->alumni->last_name)
                            : 'Unknown',
                        'program' => $response->alumni?->program ?? 'N/A',
                        'year_graduated' => $response->alumni?->year_graduated
                            ? date('Y', strtotime($response->alumni->year_graduated))
                            : 'N/A',
                        'completion' => $completion,
                        'status' => $response->status,
                        'submitted_at' => $response->submitted_at ? $response->submitted_at->format('M d, Y') : $response->created_at->format('M d, Y'),
                    ];
                })
                ->values();

            return response()->json($submissions);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

public function questionAnalytics(Request $request, $questionId)
{
    try {
        $alumniType = $request->query('alumni_type', 'all');
        $since = null;
        switch ($request->query('range', 'all')) {
            case '7d':  $since = now()->subDays(7); break;
            case '30d': $since = now()->subDays(30); break;
            case '90d': $since = now()->subDays(90); break;
        }

        // Eager-load EVERYTHING we need, including the phase chain
        $question = TracerQuestion::with([
            'options',
            'gridRows',
            'gridColumns',
            'section.phase',           // ← eager load section + its phase
        ])->findOrFail($questionId);

        // Resolve phase + target type safely
        $phase         = $question->section?->phase;
        $phaseId       = $phase?->id;
        $phaseTitle    = $phase?->title;
        $phaseColor    = $phase?->color;
        $phaseType     = $phase?->target_alumni_type ?? 'all';
        $sectionId     = $question->section?->id;
        $sectionTitle  = $question->section?->title;

        // ── Filter: this question is only for a specific alumni type ──
        $isApplicable = true;
        $reason = null;

        if ($alumniType !== 'all' && $phaseType !== 'all' && $phaseType !== $alumniType) {
            $isApplicable = false;
            $reason = "This question is only for {$phaseType} alumni.";
        }

        // Base payload — ALWAYS include phase/section context
        $data = [
            'question'      => $question,
            'type'          => $question->type,
            'applicable'    => $isApplicable,
            'reason'        => $reason,
            'phase_id'      => $phaseId,
            'phase_title'   => $phaseTitle,
            'phase_color'   => $phaseColor,
            'phase_target_type' => $phaseType,
            'section_id'    => $sectionId,
            'section_title' => $sectionTitle,
            'options'       => [],
            'gridData'      => [],
            'responseCount' => 0,
        ];

        if (!$isApplicable) {
            return response()->json($data);
        }

        // Helper closure — scope by the RESPONSE's own program_type
        // so a promoted alumnus's SHS answers don't leak into College analytics.
        $scopeAnswer = function ($q) use ($alumniType, $since) {
            $q->whereHas('answer.response', function ($r) use ($alumniType, $since) {
                if ($alumniType !== 'all') {
                    $r->where('program_type', $alumniType);
                }
                if ($since) {
                    $r->where('created_at', '>=', $since);
                }
            });
        };

        if (in_array($question->type, ['multiple_choice', 'dropdown', 'checkboxes'])) {
            $optionCounts = [];
            foreach ($question->options as $option) {
                $count = DB::table('tracer_answer_selections')
                    ->join('tracer_answers_base', 'tracer_answers_base.id', '=', 'tracer_answer_selections.tracer_answer_id')
                    ->join('tracer_responses', 'tracer_responses.id', '=', 'tracer_answers_base.tracer_response_id')
                    ->where('tracer_answers_base.question_id', $questionId)
                    ->where('tracer_answer_selections.option_id', $option->id)
                    ->when($alumniType !== 'all', fn($q) => $q->where('tracer_responses.program_type', $alumniType))
                    ->when($since, fn($q) => $q->where('tracer_responses.created_at', '>=', $since))
                    ->count();
                $optionCounts[] = ['label' => $option->option_label, 'count' => $count];
            }
            $data['options'] = $optionCounts;
            
        } elseif (in_array($question->type, ['likert_scale', 'multiple_choice_grid'])) {
            $rowData = [];
            foreach ($question->gridRows as $row) {
                $columnCounts = [];
                foreach ($question->gridColumns as $column) {
                    $count = DB::table('tracer_answer_selections')
                        ->join('tracer_answers_base', 'tracer_answers_base.id', '=', 'tracer_answer_selections.tracer_answer_id')
                        ->join('tracer_responses', 'tracer_responses.id', '=', 'tracer_answers_base.tracer_response_id')
                        ->where('tracer_answers_base.question_id', $questionId)
                        ->where('tracer_answers_base.grid_row_id', $row->id)
                        ->where('tracer_answer_selections.grid_column_id', $column->id)
                        ->when($alumniType !== 'all', fn($q) => $q->where('tracer_responses.program_type', $alumniType))
                        ->when($since, fn($q) => $q->where('tracer_responses.created_at', '>=', $since))
                        ->count();
                    $columnCounts[] = ['column_label' => $column->column_label, 'count' => $count];
                }
                $rowData[] = ['row_label' => $row->row_label, 'columns' => $columnCounts];
            }
            $data['gridData'] = $rowData;
            
        } elseif (in_array($question->type, ['short_answer', 'paragraph'])) {
            $responseCount = DB::table('tracer_answers_base')
                ->join('tracer_responses', 'tracer_responses.id', '=', 'tracer_answers_base.tracer_response_id')
                ->where('tracer_answers_base.question_id', $questionId)
                ->whereNotNull('tracer_answers_base.answer_value')
                ->when($alumniType !== 'all', fn($q) => $q->where('tracer_responses.program_type', $alumniType))
                ->when($since, fn($q) => $q->where('tracer_responses.created_at', '>=', $since))
                ->count();
            $data['responseCount'] = $responseCount;
        }

        return response()->json($data);
    } catch (\Throwable $e) {
        \Log::error('questionAnalytics failed: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function analyticsKPIs(Request $request, $formId)
{
    try {
        $form = TracerForm::findOrFail($formId);
        $range = $request->query('range', 'all');
        $alumniType = $request->query('alumni_type', 'all'); // 'all' | 'shs' | 'college'

        $since = null;
        $previousSince = null;
        $previousUntil = null;

        switch ($range) {
            case '7d':
                $since = now()->subDays(7);
                $previousSince = now()->subDays(14);
                $previousUntil = now()->subDays(7);
                break;
            case '30d':
                $since = now()->subDays(30);
                $previousSince = now()->subDays(60);
                $previousUntil = now()->subDays(30);
                break;
            case '90d':
                $since = now()->subDays(90);
                $previousSince = now()->subDays(180);
                $previousUntil = now()->subDays(90);
                break;
            case 'all':
            default:
                $since = null;
                break;
        }

        // ── Base alumni query — filter by program membership (not the cached column) ──
        $alumniQuery = \App\Models\Alumni::query();
        if ($alumniType !== 'all') {
            $alumniQuery->whereHas('programs', fn($q) => $q->where('program_type', $alumniType));
        }
        $totalAlumni = $alumniQuery->count();

        // ── Responses query — scope by the RESPONSE's own program_type ──
        $responsesBase = $form->responses();
        if ($since) $responsesBase->where('created_at', '>=', $since);
        if ($alumniType !== 'all') {
            $responsesBase->where('program_type', $alumniType);
        }
        $totalResponses = $responsesBase->count();

        $completedQuery = (clone $responsesBase)->where('status', 'completed');
        $completedResponses = $completedQuery->count();

        $responseRate = $totalAlumni > 0
            ? round(($totalResponses / $totalAlumni) * 100, 1)
            : 0;

        // ── Avg completion ──
        $avgCompletion = 0;
        $totalQuestions = TracerQuestion::whereHas('section.phase.form', fn($q) => $q->where('id', $formId))->count();

        if ($totalQuestions > 0 && $totalResponses > 0) {
            // Use DB::table for the base table instead of a broken Eloquent relationship
            $totalAnswers = DB::table('tracer_answers_base')
                ->join('tracer_responses', 'tracer_responses.id', '=', 'tracer_answers_base.tracer_response_id')
                ->where('tracer_responses.form_id', $formId)
                ->when($alumniType !== 'all', fn($q) => $q->where('tracer_responses.program_type', $alumniType))
                ->when($since, fn($q) => $q->where('tracer_responses.created_at', '>=', $since))
                ->count();
                
            $avgCompletion = round(($totalAnswers / ($totalQuestions * $totalResponses)) * 100, 1);
        }

        // ── Avg time ──
        $avgTime = null;
        if ($completedResponses > 0) {
            $timeQuery = (clone $responsesBase)
                ->where('status', 'completed')
                ->whereNotNull('submitted_at');
            $avgTime = $timeQuery->get()->avg(fn($r) => $r->created_at->diffInMinutes($r->submitted_at));
            $avgTime = $avgTime ? round($avgTime, 1) : null;
        }

        // ── Trends ──
        $trends = ['responseRate' => null, 'totalResponses' => null];
        if ($previousSince && $previousUntil) {
            $prevQuery = $form->responses()
                ->whereBetween('created_at', [$previousSince, $previousUntil]);
            if ($alumniType !== 'all') {
                $prevQuery->where('program_type', $alumniType);
            }
            $prevTotalResponses = $prevQuery->count();

            $prevRate = $totalAlumni > 0 ? round(($prevTotalResponses / $totalAlumni) * 100, 1) : 0;

            $trends['responseRate'] = $prevRate > 0
                ? round((($responseRate - $prevRate) / $prevRate) * 100, 1)
                : ($responseRate > 0 ? 100 : 0);

            $trends['totalResponses'] = $prevTotalResponses > 0
                ? round((($totalResponses - $prevTotalResponses) / $prevTotalResponses) * 100, 1)
                : ($totalResponses > 0 ? 100 : 0);
        }

        return response()->json([
            'responseRate'       => $responseRate,
            'avgCompletion'      => $avgCompletion,
            'totalResponses'     => $totalResponses,
            'completedResponses' => $completedResponses,
            'avgTimeToComplete'  => $avgTime,
            'range'              => $range,
            'alumniType'         => $alumniType,
            'trends'             => $trends,
        ]);
    } catch (\Throwable $e) {
        \Log::error('analyticsKPIs failed: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function analyticsInsights(Request $request, $formId)
{
    try {
        $alumniType = $request->query('alumni_type', 'all');

        $form = TracerForm::findOrFail($formId);
        $insights = [];

        // 1. Response rate this week — scoped by response's program_type
        $thisWeekQuery = $form->responses()
            ->where('created_at', '>=', now()->subDays(7));
        if ($alumniType !== 'all') {
            $thisWeekQuery->where('program_type', $alumniType);
        }
        $thisWeek = $thisWeekQuery->count();

        $lastWeekQuery = $form->responses()
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)]);
        if ($alumniType !== 'all') {
            $lastWeekQuery->where('program_type', $alumniType);
        }
        $lastWeek = $lastWeekQuery->count();

        if ($lastWeek > 0) {
            $delta = round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
            $direction = $delta >= 0 ? 'up' : 'down';
            $insights[] = [
                'icon' => $delta >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down',
                'color' => $delta >= 0 ? 'green' : 'amber',
                'text' => "{$thisWeek} responses this week — {$direction} " . abs($delta) . "% vs last week",
            ];
        } elseif ($thisWeek > 0) {
            $insights[] = [
                'icon' => 'fa-rocket',
                'color' => 'green',
                'text' => "{$thisWeek} new responses this week — first activity in a while!",
            ];
        }

        // 2. Top-performing program (scoped by response's program_type)
        $responsesAlumniQuery = $form->responses();
        if ($alumniType !== 'all') {
            $responsesAlumniQuery->where('program_type', $alumniType);
        }
        $alumniIds = $responsesAlumniQuery->pluck('alumni_id');

        $topProgram = \App\Models\Alumni::whereIn('id', $alumniIds)
            ->select('program', \DB::raw('count(*) as count'))
            ->whereNotNull('program')
            ->groupBy('program')
            ->orderByDesc('count')
            ->first();

        if ($topProgram && $topProgram->count > 0) {
            $insights[] = [
                'icon' => 'fa-graduation-cap',
                'color' => 'blue',
                'text' => "Top program: {$topProgram->program} ({$topProgram->count} responses)",
            ];
        }

        // 3. Most-skipped required question (scoped)
        $requiredQuestions = TracerQuestion::where('is_required', true)
            ->whereHas('section.phase.form', fn($q) => $q->where('id', $formId))
            ->get();

        $totalResps = $responsesAlumniQuery->count();
        $worstSkip = null;
        $worstPct = 0;

        foreach ($requiredQuestions as $q) {
            $answered = DB::table('tracer_answers_base')
                ->join('tracer_responses', 'tracer_responses.id', '=', 'tracer_answers_base.tracer_response_id')
                ->where('tracer_answers_base.question_id', $q->id)
                ->where('tracer_responses.form_id', $formId)
                ->when($alumniType !== 'all', fn($q2) => $q2->where('tracer_responses.program_type', $alumniType))
                ->distinct('tracer_responses.id')
                ->count('tracer_responses.id');

            $skippedPct = $totalResps > 0 ? round((($totalResps - $answered) / $totalResps) * 100, 1) : 0;
            if ($skippedPct > $worstPct) {
                $worstPct = $skippedPct;
                $worstSkip = $q;
            }
        }

        if ($worstSkip && $worstPct > 5) {
            $short = \Str::limit($worstSkip->question_text, 50);
            $insights[] = [
                'icon' => 'fa-triangle-exclamation',
                'color' => 'amber',
                'text' => "\"{$short}\" is skipped by {$worstPct}% of respondents",
            ];
        }

        return response()->json(['insights' => $insights]);
    } catch (\Throwable $e) {
        \Log::error('analyticsInsights failed: ' . $e->getMessage());
        return response()->json(['insights' => []]);
    }
}

/**
 * Drill down into a specific option — list alumni who chose it.
 */
public function chartDrilldown(Request $request, $questionId)
{
    try {
        $optionLabel = $request->query('option');
        $question = TracerQuestion::findOrFail($questionId);

        // Find the option
        $option = $question->options()->where('option_label', $optionLabel)->first();
        if (!$option) {
            return response()->json(['alumni' => []]);
        }

        $selections = \App\Models\TracerAnswerSelection::where('option_id', $option->id)
            ->with(['answer.response.alumni:id,first_name,last_name,email,program,year_graduated,alumni_photo'])
            ->get();

        $alumni = $selections
            ->map(fn($sel) => $sel->answer?->response?->alumni)
            ->filter()
            ->unique('id')
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => trim($a->first_name . ' ' . $a->last_name),
                    'email' => $a->email,
                    'program' => $a->program,
                    'year_graduated' => $a->year_graduated ? date('Y', strtotime($a->year_graduated)) : null,
                    'photo' => $a->alumni_photo,
                ];
            })
            ->values();

        return response()->json([
            'option' => $optionLabel,
            'question' => $question->question_text,
            'alumni' => $alumni,
            'total' => $alumni->count(),
        ]);
    } catch (\Throwable $e) {
        \Log::error('chartDrilldown failed: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    /**
     * Get alumni who haven't completed the tracer form.
     *
     * A promoted SHS→College alumnus appears in BOTH lists until
     * they have a completed response for EACH program type they belong to.
     */
    public function getIncompleteAlumni(Request $request, $formId)
    {
        try {
            $form = TracerForm::with('phases')->findOrFail($formId);
            $formTargetType = $request->query('alumni_type'); // 'all' | 'shs' | 'college' | null

            // Which program types does this form target?
            $targetTypes = $form->phases
                ->pluck('target_alumni_type')
                ->filter(fn($t) => $t && $t !== 'all')
                ->unique()
                ->values()
                ->toArray();

            if (empty($targetTypes)) {
                // Form has only 'all'-targeted phases — treat as applying to both programs
                $targetTypes = ['shs', 'college'];
            }

            // If the caller explicitly asked for a single type, narrow to it
            if ($formTargetType && in_array($formTargetType, ['shs', 'college'], true)) {
                $targetTypes = [$formTargetType];
            }

            // ── Candidate alumni: everyone who has EVER been in one of the target programs
            $candidates = Alumni::with(['programs'])
                ->whereNull('deleted_at')
                ->where('account_status', 1)
                ->whereHas('programs', function ($q) use ($targetTypes) {
                    $q->whereIn('program_type', $targetTypes)
                      ->where('enrollment_status', 'graduated');
                })
                ->select('id', 'first_name', 'last_name', 'email', 'program', 'year_graduated')
                ->orderBy('last_name')
                ->get();

            $incomplete      = [];
            $completedCount  = 0;
            $inProgressCount = 0;

            foreach ($candidates as $alumni) {
                // Which of the target types does THIS alumnus have?
                $alumniTypes = $alumni->programs
                    ->where('enrollment_status', 'graduated')
                    ->pluck('program_type')
                    ->intersect($targetTypes)
                    ->unique()
                    ->values()
                    ->toArray();

                // Legacy fallback
                if (empty($alumniTypes) && $alumni->alumni_type && in_array($alumni->alumni_type, $targetTypes, true)) {
                    $alumniTypes = [$alumni->alumni_type];
                }

                if (empty($alumniTypes)) {
                    continue;
                }

                // Which of those types still owe a completed response?
                $owedTypes = [];
                foreach ($alumniTypes as $type) {
                    $completed = $form->responses()
                        ->where('alumni_id', $alumni->id)
                        ->where('program_type', $type)
                        ->where('status', 'completed')
                        ->exists();

                    if (!$completed) {
                        $owedTypes[] = $type;
                    }
                }

                if (empty($owedTypes)) {
                    $completedCount++;
                    continue;
                }

                // Does this alumnus have at least one in-progress response?
                $inProgressResponse = $form->responses()
                    ->where('alumni_id', $alumni->id)
                    ->where('status', 'in_progress')
                    ->first();

                $hasStarted = !is_null($inProgressResponse);
                if ($hasStarted) {
                    $inProgressCount++;
                }

                // Completion percentage of the in-progress response
                $completion = 0;
                $lastActivity = null;
                if ($inProgressResponse) {
                    $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($form) {
                        $q->where('id', $form->id);
                    })->count();

                    $answered = DB::table('tracer_answers_base')
                        ->where('tracer_response_id', $inProgressResponse->id)
                        ->distinct('question_id')
                        ->count('question_id');
                }

                $incomplete[] = [
                    'id'                 => $alumni->id,
                    'name'               => trim($alumni->first_name . ' ' . $alumni->last_name),
                    'email'              => $alumni->email,
                    'program'            => $alumni->program ?? 'N/A',
                    'year_graduated'     => $alumni->year_graduated ? date('Y', strtotime($alumni->year_graduated)) : 'N/A',
                    'has_started'        => $hasStarted,
                    'completion'         => $completion,
                    'last_activity'      => $lastActivity,
                    'owed_program_types' => $owedTypes,
                ];
            }

            $totalAlumni = Alumni::whereNull('deleted_at')
                ->where('account_status', 1)
                ->whereHas('programs', fn($q) => $q
                    ->whereIn('program_type', $targetTypes)
                    ->where('enrollment_status', 'graduated')
                )
                ->count();

            $notStartedCount = max(0, count($incomplete) - $inProgressCount);

            return response()->json([
                'alumni' => $incomplete,
                'stats' => [
                    'total_alumni' => $totalAlumni,
                    'completed'    => $completedCount,
                    'in_progress'  => $inProgressCount,
                    'not_started'  => $notStartedCount,
                ],
                'form_title'   => $form->form_title,
                'target_types' => $targetTypes,
            ]);

        } catch (\Throwable $e) {
            \Log::error('Failed to get incomplete alumni: ' . $e->getMessage());
            return response()->json([
                'error'   => 'Failed to load alumni data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendReminder(Request $request, $formId, $alumniId)
{
    try {
        // Log the incoming parameters
        \Log::info('sendReminder called', [
            'formId' => $formId,
            'alumniId' => $alumniId,
            'alumniId_type' => gettype($alumniId),
        ]);

        // Cast to integer explicitly
        $alumniId = (int) $alumniId;
        $formId = (int) $formId;

        $form = TracerForm::findOrFail($formId);
        
        // Find alumni with error handling
        $alumni = Alumni::find($alumniId);
        
        if (!$alumni) {
            \Log::error('Alumni not found in sendReminder', [
                'alumni_id' => $alumniId,
                'table_name' => (new Alumni())->getTable(),
            ]);
            
            return response()->json([
                'error' => 'Alumni not found',
                'message' => "Could not find alumni with ID: {$alumniId}"
            ], 404);
        }

        $owedTypes = $alumni->programs()
            ->where('enrollment_status', 'graduated')
            ->pluck('program_type')
            ->unique()
            ->values()
            ->toArray();

        // Legacy fallback for alumni whose programs table is incomplete
        if (empty($owedTypes) && $alumni->alumni_type) {
            $owedTypes = [$alumni->alumni_type];
        }

        $completedTypes = $form->responses()
            ->where('alumni_id', $alumniId)
            ->where('status', 'completed')
            ->pluck('program_type')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $stillOwes = array_diff($owedTypes, $completedTypes);

        if (empty($stillOwes)) {
            return response()->json([
                'error' => 'This alumni has already completed the tracer.'
            ], 400);
        }

        // Send email using BrevoMailService (the working mail method)
        try {
            $service = new \App\Services\BrevoMailService();
            
            // Render the email view with data
            $htmlContent = view('emails.tracer_reminder', [
                'alumni' => $alumni,
                'form' => $form,
                'alumniName' => $alumni->first_name,
                'formTitle' => $form->form_title,
                'tracerUrl' => url('/alumni/tracer/' . $form->id),
            ])->render();
            
            $service->sendEmail(
                $alumni->email,
                'Reminder: Complete Your Alumni Tracer Survey',
                $htmlContent
            );
            
            \Log::info('Reminder email sent successfully via Brevo to alumni ' . $alumniId, [
                'alumni_id' => $alumniId,
                'email' => $alumni->email,
                'form_id' => $formId
            ]);
            
            return response()->json([
                'message' => 'Reminder sent successfully to ' . $alumni->first_name . ' ' . $alumni->last_name,
            ]);
            
        } catch (\Exception $mailError) {
            \Log::error('Mail sending failed via Brevo', [
                'error' => $mailError->getMessage(),
                'trace' => $mailError->getTraceAsString(),
                'alumni_id' => $alumniId,
                'email' => $alumni->email
            ]);
            
            // Try fallback: Use Laravel Mail as backup
            try {
                \Mail::to($alumni->email)->send(new \App\Mail\TracerReminderMail($alumni, $form));
                \Log::info('Reminder email sent via Laravel Mail fallback to alumni ' . $alumniId);
                
                return response()->json([
                    'message' => 'Reminder sent successfully to ' . $alumni->first_name . ' ' . $alumni->last_name,
                ]);
            } catch (\Exception $fallbackError) {
                \Log::error('Fallback mail also failed', [
                    'error' => $fallbackError->getMessage()
                ]);
                
                return response()->json([
                    'error' => 'Failed to send email',
                    'message' => $mailError->getMessage()
                ], 500);
            }
        }
        
    } catch (\Throwable $e) {
        \Log::error('Failed to send reminder: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'error' => 'Failed to send reminder',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function sendReminderToAll(Request $request, $formId)
{
    // Log the entire request for debugging
    \Log::info('sendReminderToAll called', [
        'formId' => $formId,
        'alumni_ids' => $request->input('alumni_ids'),
        'all_input' => $request->all()
    ]);

    try {
        $request->validate([
            'alumni_ids' => 'required|array',
            'alumni_ids.*' => 'integer',
        ]);

        $form = TracerForm::findOrFail($formId);
        \Log::info('Form found', ['form_id' => $form->id, 'form_title' => $form->form_title]);

        $sentCount = 0;
        $failedEmails = [];
        $failedIds = [];
        
        foreach ($request->alumni_ids as $alumniId) {
            try {
                \Log::info('Processing alumni', ['alumni_id' => $alumniId]);
                
                $alumni = Alumni::findOrFail($alumniId);
                \Log::info('Alumni found', ['alumni_id' => $alumni->id, 'email' => $alumni->email]);
                
                $owedTypes = $alumni->programs()
                    ->where('enrollment_status', 'graduated')
                    ->pluck('program_type')
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty($owedTypes) && $alumni->alumni_type) {
                    $owedTypes = [$alumni->alumni_type];
                }

                $completedTypes = $form->responses()
                    ->where('alumni_id', $alumniId)
                    ->where('status', 'completed')
                    ->pluck('program_type')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty(array_diff($owedTypes, $completedTypes))) {
                    \Log::info('Skipping alumni ' . $alumniId . ' - already completed all tracers');
                    continue;
                }
                
                // Send email using BrevoMailService
                try {
                    \Log::info('Attempting to send email via Brevo to alumni ' . $alumniId);
                    
                    $service = new \App\Services\BrevoMailService();
                    
                    // Render the email view with data
                    $htmlContent = view('emails.tracer_reminder', [
                        'alumni' => $alumni,
                        'form' => $form,
                        'alumniName' => $alumni->first_name,
                        'formTitle' => $form->form_title,
                        'tracerUrl' => url('/alumni/tracer/' . $form->id),
                    ])->render();
                    
                    \Log::info('Email view rendered successfully');
                    
                    $service->sendEmail(
                        $alumni->email,
                        'Reminder: Complete Your Alumni Tracer Survey',
                        $htmlContent
                    );
                    
                    $sentCount++;
                    \Log::info('Reminder sent via Brevo to alumni ' . $alumniId);
                    
                } catch (\Exception $mailError) {
                    \Log::error('Brevo failed for alumni ' . $alumniId . ': ' . $mailError->getMessage());
                    \Log::error('Brevo error trace: ' . $mailError->getTraceAsString());
                    
                    // Fallback to Laravel Mail
                    try {
                        \Log::info('Attempting Laravel Mail fallback for alumni ' . $alumniId);
                        \Mail::to($alumni->email)->send(new \App\Mail\TracerReminderMail($alumni, $form));
                        $sentCount++;
                        \Log::info('Reminder sent via Laravel Mail fallback to alumni ' . $alumniId);
                    } catch (\Exception $fallbackError) {
                        \Log::error('Laravel Mail fallback also failed for alumni ' . $alumniId . ': ' . $fallbackError->getMessage());
                        throw $fallbackError;
                    }
                }
                
            } catch (\Exception $e) {
                $alumniName = isset($alumni) ? ($alumni->first_name . ' ' . $alumni->last_name) : "Alumni ID: $alumniId";
                $failedEmails[] = $alumniName;
                $failedIds[] = $alumniId;
                \Log::error("Failed to send reminder to alumni $alumniId: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
            }
        }
        
        $message = "Successfully sent {$sentCount} reminders.";
        if (count($failedEmails) > 0) {
            $message .= " Failed (" . count($failedEmails) . "): " . implode(', ', $failedEmails);
        }
        
        \Log::info('sendReminderToAll completed', [
            'sent_count' => $sentCount,
            'failed_count' => count($failedEmails)
        ]);
        
        return response()->json([
            'message' => $message,
            'sent_count' => $sentCount,
            'failed_count' => count($failedEmails),
            'failed_ids' => $failedIds,
        ]);
        
    } catch (\Throwable $e) {
        \Log::error('Failed to send bulk reminders: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'error' => 'Failed to send reminders',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get program distribution statistics.
     */
    private function getProgramStats($form)
    {
        try {
            $alumniIds = $this->getCanonicalResponses($form->id)->pluck('alumni_id')->unique();
            
            $programData = \App\Models\Alumni::whereIn('id', $alumniIds)
                ->select('program', DB::raw('count(*) as count'))
                ->groupBy('program')
                ->orderByDesc('count')
                ->get();
            
            return $programData->map(function ($item) {
                return [
                    'program' => $item->program ?? 'Unknown',
                    'count' => $item->count,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            \Log::error('Failed to get program stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get graduation year distribution statistics.
     */
    private function getYearStats($form)
    {
        try {
            $alumniIds = $this->getCanonicalResponses($form->id)->pluck('alumni_id')->unique();
            
            $yearData = \App\Models\Alumni::whereIn('id', $alumniIds)
                ->select(DB::raw("COALESCE(EXTRACT(YEAR FROM year_graduated)::text, 'Unknown') as year"), DB::raw('count(*) as count'))
                ->groupBy(DB::raw("EXTRACT(YEAR FROM year_graduated)"))
                ->orderBy('year')
                ->get();
            
            return $yearData->map(function ($item) {
                return [
                    'year' => $item->year ?? 'Unknown',
                    'count' => $item->count,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            \Log::error('Failed to get year stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top responders by completion rate.
     */
    private function getTopResponders($form)
    {
        try {
            $totalQuestions = TracerQuestion::whereHas('section.phase.form', function ($q) use ($form) {
                $q->where('id', $form->id);
            })->count();
            
            $topResponders = $this->getCanonicalResponses($form->id)
                ->map(function ($response) use ($totalQuestions) {
                    $answeredQuestionIds = $response->answers
                        ->filter(fn ($answer) => $this->answerHasValue($answer))
                        ->pluck('question_id')
                        ->unique()
                        ->count();
                    
                    $completion = $totalQuestions > 0 ? round(($answeredQuestionIds / $totalQuestions) * 100) : 0;
                    
                    return [
                        'name'       => $response->alumni->first_name . ' ' . $response->alumni->last_name,
                        'program'    => $response->alumni->program ?? 'N/A',
                        'year'       => $response->alumni->year_graduated ? date('Y', strtotime($response->alumni->year_graduated)) : 'N/A',
                        'completion' => $completion,
                    ];
                })
                ->sortByDesc('completion')
                ->take(5)
                ->values()
                ->toArray();
            
            return $topResponders;
        } catch (\Throwable $e) {
            \Log::error('Failed to get top responders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Return one response per alumni for a form.
     *
     * Older mobile sessions can create a second response instead of updating
     * the existing one. Keep both records for auditability, but use one
     * canonical record for institutional counts and response listings.
     */
    private function getCanonicalResponses($formId)
    {
        return TracerResponse::with([
            'alumni:id,first_name,last_name,program,year_graduated',
            'answers.selections',
        ])
            ->where('form_id', $formId)
            ->get()
            ->groupBy('alumni_id')
            ->map(function ($responses) {
                return $responses->sort(function ($left, $right) {
                    $completedComparison = ((int) ($right->status === 'completed'))
                        <=> ((int) ($left->status === 'completed'));

                    if ($completedComparison !== 0) {
                        return $completedComparison;
                    }

                    $leftAnswered = $left->answers
                        ->filter(fn ($answer) => $this->answerHasValue($answer))
                        ->pluck('question_id')
                        ->unique()
                        ->count();
                    $rightAnswered = $right->answers
                        ->filter(fn ($answer) => $this->answerHasValue($answer))
                        ->pluck('question_id')
                        ->unique()
                        ->count();

                    if ($leftAnswered !== $rightAnswered) {
                        return $rightAnswered <=> $leftAnswered;
                    }

                    return $right->updated_at <=> $left->updated_at;
                })->first();
            })
            ->filter();
    }

    // ═══════════════════════════════════════
    // NEW: Get Phases Filtered by Alumni Type
    // ═══════════════════════════════════════

    /**
     * Get phases filtered by alumni type for the mobile app.
     * Route: GET /admin/alumni_tracer/{formId}/phases-for-alumni?alumni_id={alumniId}
     */
    public function getPhasesForAlumni(Request $request, $formId)
    {
        try {
            $alumniId = $request->input('alumni_id');
            
            if (!$alumniId) {
                return response()->json([
                    'error' => 'alumni_id parameter is required'
                ], 400);
            }
            
                        $alumni = \App\Models\Alumni::with('programs')->findOrFail($alumniId);

            // Only program rows the alumnus has actually GRADUATED from count
            // as "types they owe a tracer for". Enrolled/dropped college rows
            // do NOT grant access to the College tracer.
            $alumniTypes = $alumni->programs
                ->where('enrollment_status', 'graduated')
                ->pluck('program_type')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // Legacy fallback: alumni created before alumni_programs existed
            // (or with an incomplete programs table) fall back to their cached type.
            if (empty($alumniTypes)) {
                $alumniTypes = [$alumni->alumni_type ?: 'college'];
            }

            $form = TracerForm::with([
                'phases' => function ($q) use ($alumniTypes) {
                    $q->where(function ($query) use ($alumniTypes) {
                        $query->where('target_alumni_type', 'all')
                              ->orWhereIn('target_alumni_type', $alumniTypes);
                    })->orderBy('order_priority');
                },
                'phases.sections' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'phases.sections.questions' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'phases.sections.questions.options' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'phases.sections.questions.gridRows' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'phases.sections.questions.gridColumns' => function ($q) {
                    $q->orderBy('order_priority');
                },
            ])->findOrFail($formId);
            
            return response()->json([
                'form' => $form,
                'alumni_type' => $alumni->alumni_type,        // keep for backwards-compat
                'alumni_types' => $alumniTypes,               // ← NEW: array
                'phases_count' => $form->phases->count(),
                'filtered_phases' => $form->phases->map(function ($phase) {
                    return [
                        'id' => $phase->id,
                        'title' => $phase->title,
                        'subtitle' => $phase->subtitle,
                        'icon' => $phase->icon,
                        'color' => $phase->color,
                        'target_alumni_type' => $phase->target_alumni_type,
                        'sections_count' => $phase->sections->count(),
                        'questions_count' => $phase->sections->sum(function ($section) {
                            return $section->questions->count();
                        }),
                    ];
                }),
            ]);
            
        } catch (\Throwable $e) {
            \Log::error('Failed to get phases for alumni: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load phases',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPhasesDirectly()
    {
        try {
            $phases = TracerPhase::with([
                'sections' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'sections.questions' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'sections.questions.options' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'sections.questions.gridRows' => function ($q) {
                    $q->orderBy('order_priority');
                },
                'sections.questions.gridColumns' => function ($q) {
                    $q->orderBy('order_priority');
                },
            ])
            ->orderBy('order_priority')
            ->get();

            // ─── Attach conditions to each question ───
            $conditions = DB::table('tracer_question_conditions')
                ->get()
                ->keyBy('question_id');

            $optionLabelsByCondition = DB::table('tracer_question_condition_options')
                ->get()
                ->groupBy('condition_id');

            foreach ($phases as $phase) {
                foreach ($phase->sections as $section) {
                    foreach ($section->questions as $question) {
                        $cond = $conditions[$question->id] ?? null;
                        if ($cond) {
                            $question->condition = [
                                'depends_on_question_id' => $cond->depends_on_question_id,
                                'match_type'             => $cond->match_type,
                                'option_labels'          => isset($optionLabelsByCondition[$cond->id])
                                    ? $optionLabelsByCondition[$cond->id]->pluck('option_label')->values()->all()
                                    : [],
                            ];
                        } else {
                            $question->condition = null;
                        }
                    }
                }
            }

            return response()->json($phases);
        } catch (\Throwable $e) {
            \Log::error('Failed to load phases directly: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load phases',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function savePhasesDirectly(Request $request)
    {
        try {
            \Log::info('savePhasesDirectly called');
            
            $validated = $request->validate([
                'phases' => 'required|array',
                'phases.*.id' => 'nullable|integer',
                'phases.*.title' => 'required|string|max:255',
                'phases.*.subtitle' => 'nullable|string|max:1000',
                'phases.*.icon' => 'nullable|string|max:100',
                'phases.*.color' => 'nullable|string|max:20',
                'phases.*.target_alumni_type' => 'nullable|string|in:all,college,shs',
                'phases.*.sections' => 'nullable|array',
                'phases.*.sections.*.id' => 'nullable|integer',
                'phases.*.sections.*.title' => 'required|string|max:255',
                'phases.*.sections.*.description' => 'nullable|string|max:2000',
                'phases.*.sections.*.questions' => 'nullable|array',
                'phases.*.sections.*.questions.*.id' => 'nullable|integer',
                'phases.*.sections.*.questions.*.question_text' => 'required|string',

                'phases.*.sections.*.questions.*.type' => 'required|string|in:short_answer,paragraph,multiple_choice,checkboxes,dropdown,likert_scale,multiple_choice_grid',

                'phases.*.sections.*.questions.*.description' => 'nullable|string',
                'phases.*.sections.*.questions.*.placeholder' => 'nullable|string|max:255',
                'phases.*.sections.*.questions.*.is_required' => 'boolean',
                'phases.*.sections.*.questions.*.allow_other_option' => 'boolean',
                'phases.*.sections.*.questions.*.file_types' => 'nullable|array',
                'phases.*.sections.*.questions.*.max_file_size' => 'nullable|integer',
                'phases.*.sections.*.questions.*.options' => 'nullable|array',
                'phases.*.sections.*.questions.*.options.*.label' => 'required|string',
                'phases.*.sections.*.questions.*.grid_rows' => 'nullable|array',
                'phases.*.sections.*.questions.*.grid_rows.*.label' => 'required|string',
                'phases.*.sections.*.questions.*.grid_columns' => 'nullable|array',
                'phases.*.sections.*.questions.*.grid_columns.*.label' => 'required|string',
                
                'phases.*.sections.*.questions.*.condition' => 'nullable|array',
                'phases.*.sections.*.questions.*.condition.depends_on_question_id' => 'nullable|integer',
                'phases.*.sections.*.questions.*.condition.match_type' => 'nullable|string|in:any,all,none',
                'phases.*.sections.*.questions.*.condition.option_labels' => 'nullable|array',
                'phases.*.sections.*.questions.*.condition.option_labels.*' => 'string|max:255',
                                
            ]);

            DB::beginTransaction();

            try {
                // ✅ Resolve the canonical form once, use it for all new phases
                $canonicalForm = TracerForm::orderBy('id')->first();
                if (!$canonicalForm) {
                    $canonicalForm = TracerForm::create([
                        'admin_id'         => session('admin_id') ?? 1,
                        'form_title'       => 'Alumni Tracer',
                        'form_description' => 'Default tracer form',
                        'status'           => 1,
                    ]);
                }
                $canonicalFormId = $canonicalForm->id;

                // Get existing phase IDs
                $existingPhaseIds = TracerPhase::pluck('id')->toArray();
                $newPhaseIds = [];

                foreach ($validated['phases'] as $phaseIdx => $phaseData) {
                    
                    $phase = null;
                    
                    if (isset($phaseData['id']) && $phaseData['id'] !== null && $phaseData['id'] > 0) {
                        $phase = TracerPhase::find($phaseData['id']);
                    }
                    
                    // If phase doesn't exist or ID is null/0, create a new one
                    if (!$phase) {
                        $phase = new TracerPhase();
                        $phase->form_id = $canonicalFormId;
                    }

                    if (!$phase->form_id) {
                        $phase->form_id = $canonicalFormId;
                    }

                    $phase->title = $phaseData['title'];
                    $phase->subtitle = $phaseData['subtitle'] ?? null;
                    $phase->icon = $phaseData['icon'] ?? 'fa-user';
                    $phase->color = $phaseData['color'] ?? '#3b82f6';
                    $phase->target_alumni_type = $phaseData['target_alumni_type'] ?? 'all';
                    $phase->order_priority = $phaseIdx;
                    $phase->save();

                    $newPhaseIds[] = $phase->id;

                    // Handle sections (if any)
                    if (!empty($phaseData['sections'])) {
                        $existingSectionIds = $phase->sections()->pluck('id')->toArray();
                        $newSectionIds = [];

                        foreach ($phaseData['sections'] as $sectionIdx => $sectionData) {
                            // ✅ FIX: Check if section exists properly
                            $section = null;
                            
                            if (isset($sectionData['id']) && $sectionData['id'] !== null && $sectionData['id'] > 0) {
                                $section = TracerSection::find($sectionData['id']);
                            }
                            
                            if (!$section) {
                                $section = new TracerSection();
                            }

                            $section->phase_id = $phase->id;
                            $section->title = $sectionData['title'];
                            $section->description = $sectionData['description'] ?? null;
                            $section->order_priority = $sectionIdx;
                            $section->save();

                            $newSectionIds[] = $section->id;

                            // Handle questions
                            if (!empty($sectionData['questions'])) {
                                $existingQuestionIds = $section->questions()->pluck('id')->toArray();
                                $newQuestionIds = [];

                                foreach ($sectionData['questions'] as $qIdx => $qData) {
                                    // ✅ FIX: Check if question exists properly
                                    $question = null;
                                    
                                    if (isset($qData['id']) && $qData['id'] !== null && $qData['id'] > 0) {
                                        $question = TracerQuestion::find($qData['id']);
                                    }
                                    
                                    if (!$question) {
                                        $question = new TracerQuestion();
                                    }

                                    $question->section_id = $section->id;
                                    $question->type = $qData['type'];
                                    $question->question_text = $qData['question_text'];
                                    $question->description = $qData['description'] ?? null;
                                    $question->placeholder = $qData['placeholder'] ?? null;
                                    $question->is_required = $qData['is_required'] ?? true;

                                    \Log::info('savePhasesDirectly: allow_other_option received', [
                                        'question_id' => $question->id ?? null,
                                        'incoming_allow_other' => $qData['allow_other_option'] ?? 'MISSING',
                                        'incoming_options_count' => count($qData['options'] ?? []),
                                    ]);

                                    $question->allow_other_option = $qData['allow_other_option'] ?? false;
                                    
                                    $question->order_priority = $qIdx;
                                    $question->save();

                                    // 🔍 Debug — verify what actually landed in the DB
                                    \Log::info('savePhasesDirectly: question saved', [
                                        'question_id' => $question->id,
                                        'type' => $qData['type'],
                                        'allow_other_option' => $question->allow_other_option,
                                        'incoming_options_count' => count($qData['options'] ?? []),
                                        'incoming_options' => array_map(
                                            fn($o) => is_array($o) ? ($o['label'] ?? '') : $o,
                                            $qData['options'] ?? []
                                        ),
                                    ]);

                                    $newQuestionIds[] = $question->id;

                                    // Handle branching condition
                                    $this->syncQuestionCondition($question, $qData['condition'] ?? null);

                                    // Handle options — diff-based, preserves IDs
                                    if (in_array($qData['type'], ['multiple_choice', 'checkboxes', 'dropdown'])) {
                                        $this->syncQuestionOptions(
                                            $question,
                                            $qData['options'] ?? [],
                                            !empty($qData['allow_other_option'])
                                        );
                                    }

                                    // Handle grid rows — diff-based
                                    if (!empty($qData['grid_rows'])) {
                                        $existingRows = $question->gridRows->keyBy(fn($r) => $r->row_label);
                                        $seenRowLabels = [];
                                        foreach ($qData['grid_rows'] as $rowIdx => $rowData) {
                                            $label = trim((string) (is_array($rowData) ? ($rowData['label'] ?? '') : $rowData));
                                            if ($label === '') continue;
                                            $seenRowLabels[] = $label;

                                            if (isset($existingRows[$label])) {
                                                $existingRows[$label]->update(['order_priority' => $rowIdx]);
                                            } else {
                                                $question->gridRows()->create([
                                                    'row_label'      => $label,
                                                    'order_priority' => $rowIdx,
                                                ]);
                                            }
                                        }
                                        foreach ($existingRows as $label => $row) {
                                            if (!in_array($label, $seenRowLabels, true)) {
                                                $row->delete();
                                            }
                                        }
                                    }

                                    // Handle grid columns — diff-based
                                    if (!empty($qData['grid_columns'])) {
                                        $existingCols = $question->gridColumns->keyBy(fn($c) => $c->column_label);
                                        $seenColLabels = [];
                                        foreach ($qData['grid_columns'] as $colIdx => $colData) {
                                            $label = trim((string) (is_array($colData) ? ($colData['label'] ?? '') : $colData));
                                            if ($label === '') continue;
                                            $seenColLabels[] = $label;

                                            if (isset($existingCols[$label])) {
                                                $existingCols[$label]->update(['order_priority' => $colIdx]);
                                            } else {
                                                $question->gridColumns()->create([
                                                    'column_label'   => $label,
                                                    'order_priority' => $colIdx,
                                                ]);
                                            }
                                        }
                                        foreach ($existingCols as $label => $col) {
                                            if (!in_array($label, $seenColLabels, true)) {
                                                $col->delete();
                                            }
                                        }
                                    }
                                }

                                // Delete questions that were removed
                                $questionsToDelete = array_diff($existingQuestionIds, $newQuestionIds);
                                if (!empty($questionsToDelete)) {
                                    TracerQuestion::whereIn('id', $questionsToDelete)->delete();
                                }
                            }
                        }

                        // Delete sections that were removed
                        $sectionsToDelete = array_diff($existingSectionIds, $newSectionIds);
                        if (!empty($sectionsToDelete)) {
                            TracerSection::whereIn('id', $sectionsToDelete)->delete();
                        }
                    }
                }

                // Delete phases that were removed
                $phasesToDelete = array_diff($existingPhaseIds, $newPhaseIds);
                if (!empty($phasesToDelete)) {
                    TracerPhase::whereIn('id', $phasesToDelete)->delete();
                }

                DB::commit();

                // Return all phases WITH their branching conditions attached
                $allPhases = TracerPhase::with([
                    'sections.questions.options',
                    'sections.questions.gridRows',
                    'sections.questions.gridColumns',
                ])->orderBy('order_priority')->get();

                // ─── Attach conditions (same logic as getPhasesDirectly) ───
                $conditions = DB::table('tracer_question_conditions')
                    ->get()
                    ->keyBy('question_id');

                $optionLabelsByCondition = DB::table('tracer_question_condition_options')
                    ->get()
                    ->groupBy('condition_id');

                foreach ($allPhases as $phase) {
                    foreach ($phase->sections as $section) {
                        foreach ($section->questions as $question) {
                            $cond = $conditions[$question->id] ?? null;
                            if ($cond) {
                                $question->condition = [
                                    'depends_on_question_id' => $cond->depends_on_question_id,
                                    'match_type'             => $cond->match_type,
                                    'option_labels'          => isset($optionLabelsByCondition[$cond->id])
                                        ? $optionLabelsByCondition[$cond->id]->pluck('option_label')->values()->all()
                                        : [],
                                ];
                            } else {
                                $question->condition = null;
                            }
                        }
                    }
                }

                return response()->json([
                    'message' => 'Phases saved successfully.',
                    'phases' => $allPhases
                ]);
                
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error('Failed to save phases directly: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json([
                    'message' => 'Failed to save phases.',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Throwable $e) {
            \Log::error('Validation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Failed to save phases.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getActiveFormId()
    {
        try {
            $form = TracerForm::first();
            
            if (!$form) {
                // Create a default form if none exists
                $form = TracerForm::create([
                    'admin_id' => session('admin_id') ?? 1,
                    'form_title' => 'Alumni Tracer',
                    'form_description' => 'Default tracer form',
                    'status' => 1, // ✅ Use 1 for ACTIVE (not 2)
                ]);
            }
            
            return response()->json([
                'form_id' => $form->id,
                'form_title' => $form->form_title,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to get active form ID: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get form ID',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
    * Delete a single question directly.
    */
    public function deleteQuestionDirectly($questionId)
    {
        try {
            $question = TracerQuestion::findOrFail($questionId);
            $question->delete();
            
            return response()->json([
                'message' => 'Question deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete question: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete question.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single section directly.
     */
    public function deleteSectionDirectly($sectionId)
    {
        try {
            $section = TracerSection::findOrFail($sectionId);
            $section->delete();
            
            return response()->json([
                'message' => 'Section deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete section: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete section.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single phase directly.
     */
    public function deletePhaseDirectly($phaseId)
    {
        try {
            $phase = TracerPhase::findOrFail($phaseId);
            $phase->delete();
            
            return response()->json([
                'message' => 'Phase deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete phase: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete phase.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Duplicate a phase, including its sections, questions, options, grids,
 * and conditions (as-is — conditions still point to the original parents).
 *
 * The clone:
 *   - Has "(Copy)" appended to its title
 *   - Is always placed AFTER the source in order_priority
 *   - Is NEVER archived (so you can immediately work with it)
 *   - Preserves every boolean flag correctly (allow_other_option, is_other)
 */
public function duplicatePhase($phaseId)
{
    DB::beginTransaction();

    try {
        $source = TracerPhase::with([
            'sections.questions.options',
            'sections.questions.gridRows',
            'sections.questions.gridColumns',
        ])->findOrFail($phaseId);

        // ── Clone the phase itself ──
        $clone = $source->replicate();
        $clone->title         = $source->title . ' (Copy)';
        $clone->is_archived   = false;                       // always active
        $clone->order_priority = ($source->order_priority ?? 0) + 1;
        $clone->save();

        // Push every phase after the clone down by one
        TracerPhase::where('id', '!=', $clone->id)
            ->where('id', '!=', $source->id)
            ->where('order_priority', '>=', $clone->order_priority)
            ->increment('order_priority');

        // ── Clone sections, questions, options, grids ──
        // We track an old→new question ID map so we can rewrite conditions
        // that point to questions *within the same phase*.
        $questionIdMap = [];   // [old_question_id => new_question_id]

        foreach ($source->sections as $srcSection) {
            $newSection = $srcSection->replicate();
            $newSection->phase_id = $clone->id;
            $newSection->save();

            foreach ($srcSection->questions as $srcQuestion) {
                $newQuestion = $srcQuestion->replicate();
                $newQuestion->section_id         = $newSection->id;
                // 🔥 Force booleans explicitly
                $newQuestion->is_required       = (bool) $srcQuestion->is_required;
                $newQuestion->allow_other_option = (bool) $srcQuestion->allow_other_option;
                $newQuestion->save();

                $questionIdMap[$srcQuestion->id] = $newQuestion->id;

                // ── Clone options ──
                foreach ($srcQuestion->options as $srcOption) {
                    $newOption = $srcOption->replicate();
                    $newOption->question_id = $newQuestion->id;
                    $newOption->is_other    = (bool) $srcOption->is_other;   // 🔥 force bool
                    $newOption->save();
                }

                // ── Clone grid rows ──
                foreach ($srcQuestion->gridRows as $srcRow) {
                    $newRow = $srcRow->replicate();
                    $newRow->question_id = $newQuestion->id;
                    $newRow->save();
                }

                // ── Clone grid columns ──
                foreach ($srcQuestion->gridColumns as $srcCol) {
                    $newCol = $srcCol->replicate();
                    $newCol->question_id = $newQuestion->id;
                    $newCol->save();
                }

                // ── Clone conditions ──
                // We defer the condition insert so we can remap depends_on_question_id
                // if the parent question lives in the same phase (which is common).
                // Store the (question, condition) pair for a second pass.
            }
        }

        // ── Second pass: clone conditions with ID remapping ──
        foreach ($source->sections as $srcSection) {
            foreach ($srcSection->questions as $srcQuestion) {
                $srcCond = DB::table('tracer_question_conditions')
                    ->where('question_id', $srcQuestion->id)
                    ->first();

                if (!$srcCond) continue;

                $newQuestionId = $questionIdMap[$srcQuestion->id] ?? null;
                if (!$newQuestionId) continue;

                // If the parent question is inside the same phase, point the
                // condition to the *cloned* parent instead of the original.
                $newParentId = $questionIdMap[$srcCond->depends_on_question_id]
                    ?? $srcCond->depends_on_question_id;   // fallback: original parent

                $newCondId = DB::table('tracer_question_conditions')->insertGetId([
                    'question_id'            => $newQuestionId,
                    'depends_on_question_id' => $newParentId,
                    'match_type'             => $srcCond->match_type,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                $labels = DB::table('tracer_question_condition_options')
                    ->where('condition_id', $srcCond->id)
                    ->pluck('option_label');

                foreach ($labels as $label) {
                    DB::table('tracer_question_condition_options')->insert([
                        'condition_id' => $newCondId,
                        'option_label' => $label,
                        'created_at'   => now(),
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'message'  => 'Phase duplicated successfully.',
            'phase_id' => $clone->id,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Failed to duplicate phase: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'message' => 'Failed to duplicate phase.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

/**
 * Toggle the archive state of a phase.
 *
 * Archived phases:
 *   - Still visible in the admin builder (so you can un-archive them)
 *   - Excluded from the mobile app's tracer list
 */
public function togglePhaseArchive($phaseId)
{
    try {
        $phase = TracerPhase::findOrFail($phaseId);

        // Cast explicitly to avoid any PDO string weirdness
        $phase->is_archived = !((bool) $phase->is_archived);
        $phase->save();

        // Force-refresh so the response reflects the DB value
        $phase->refresh();

        \Log::info('Phase archive toggled', [
            'phase_id'     => $phase->id,
            'is_archived'  => $phase->is_archived,
            'performed_by' => session('admin_id'),
        ]);

        return response()->json([
            'message'     => $phase->is_archived
                ? 'Phase archived successfully.'
                : 'Phase restored successfully.',
            'is_archived' => (bool) $phase->is_archived,
        ]);
    } catch (\Throwable $e) {
        \Log::error('Failed to toggle phase archive: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to toggle archive state.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    /**
     * View a specific alumni response
     */
    public function viewResponse($responseId)
    {
        $response = TracerResponse::with([
            'alumni',
            'form',
            'answers' => function($query) {
                $query->orderBy('created_at', 'asc');
            },
            'answers.question',
            'answers.question.section',
            'answers.question.section.phase',
            'answers.selections',
            'answers.selections.option',
            'answers.selections.gridColumn',
            'answers.gridRow'
        ])->findOrFail($responseId);

        // Get all phases for this form to calculate completion
        $phases = TracerPhase::where('form_id', $response->form_id)
            ->with([
                'sections' => function($query) {
                    $query->orderBy('order_priority', 'asc');
                },
                'sections.questions' => function($query) {
                    $query->orderBy('order_priority', 'asc');
                },
                'sections.questions.gridRows',
                'sections.questions.gridColumns'
            ])
            ->orderBy('order_priority', 'asc')
            ->get();

        // Calculate phase completion
        $phaseProgress = [];
        $totalQuestions = 0;
        $answeredQuestions = 0;

        foreach ($phases as $phase) {
            $phaseQuestions = 0;
            $phaseAnswered = 0;
            
            foreach ($phase->sections as $section) {
                foreach ($section->questions as $question) {
                    $phaseQuestions++;
                    $totalQuestions++;
                    
                    // Check if this question has an answer
                    $hasAnswer = $response->answers->contains(function($answer) use ($question) {
                        return $answer->question_id == $question->id
                            && $this->answerHasValue($answer);
                    });
                    
                    if ($hasAnswer) {
                        $phaseAnswered++;
                        $answeredQuestions++;
                    }
                }
            }
            
            $phaseProgress[] = [
                'phase' => $phase,
                'total' => $phaseQuestions,
                'answered' => $phaseAnswered,
                'percentage' => $phaseQuestions > 0 ? round(($phaseAnswered / $phaseQuestions) * 100) : 0
            ];
        }

        $completionPercentage = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;

        return view('alumni_tracer.response_detail', compact(
            'response',
            'phases',
            'phaseProgress',
            'completionPercentage',
            'totalQuestions',
            'answeredQuestions'
        ));
    }

    /**
     * Get response data for AJAX
     */
    public function getResponseData($responseId)
    {
        $response = TracerResponse::with([
            'alumni',
            'answers' => function($query) {
                $query->orderBy('created_at', 'asc');
            },
            'answers.question',
            'answers.question.section',
            'answers.question.section.phase',
            'answers.selections',
            'answers.selections.option',
            'answers.selections.gridColumn',
            'answers.gridRow'
        ])->findOrFail($responseId);

        // Get phases with questions
        $phases = TracerPhase::where('form_id', $response->form_id)
            ->with([
                'sections' => function($query) {
                    $query->orderBy('order_priority', 'asc');
                },
                'sections.questions' => function($query) {
                    $query->orderBy('order_priority', 'asc');
                },
                'sections.questions.gridRows',
                'sections.questions.gridColumns'
            ])
            ->orderBy('order_priority', 'asc')
            ->get();

        // Build response data
        $responseData = [];
        
        foreach ($phases as $phase) {
            $phaseData = [
                'id' => $phase->id,
                'title' => $phase->title,
                'subtitle' => $phase->subtitle,
                'icon' => $phase->icon,
                'color' => $phase->color,
                'target_type' => $phase->target_alumni_type,
                'sections' => []
            ];

            foreach ($phase->sections as $section) {
                $sectionData = [
                    'id' => $section->id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'questions' => []
                ];

                foreach ($section->questions as $question) {
                    $answer = $response->answers->firstWhere('question_id', $question->id);
                    
                    $sectionData['questions'][] = [
                        'id' => $question->id,
                        'label' => $question->question_text,
                        'type' => $question->type,
                        'is_required' => $question->is_required,
                        'placeholder' => $question->placeholder,
                        'options' => $question->options ? $question->options->pluck('option_label') : [],
                        'grid_rows' => $question->gridRows ? $question->gridRows->pluck('row_label') : [],
                        'grid_columns' => $question->gridColumns ? $question->gridColumns->pluck('column_label') : [],
                        'answer' => $this->formatAnswer($answer, $question->type),
                        'has_answer' => !is_null($answer)
                    ];
                }

                $phaseData['sections'][] = $sectionData;
            }

            $responseData[] = $phaseData;
        }

        return response()->json([
            'success' => true,
            'alumni' => [
                'id' => $response->alumni->id,
                'name' => $response->alumni->first_name . ' ' . $response->alumni->last_name,
                'email' => $response->alumni->email,
                'program' => $response->alumni->program,
                'year_graduated' => $response->alumni->year_graduated,
                'alumni_type' => $response->alumni->alumni_type,
                'photo' => $response->alumni->alumni_photo,
                'student_id' => $response->alumni->student_id_number
            ],
            'response' => [
                'id' => $response->id,
                'status' => $response->status,
                'submitted_at' => $response->submitted_at,
                'created_at' => $response->created_at,
                'updated_at' => $response->updated_at
            ],
            'phases' => $responseData,
            'completion' => $this->calculateCompletion($response, $phases)
        ]);
    }

    private function formatAnswer($answer, $questionType)
    {
        if (!$answer) return null;

        switch ($questionType) {
            case 'short_answer':
            case 'paragraph':
                return $answer->answer_value;
                
            case 'multiple_choice':
                $selection = $answer->selections->first();
                return $selection ? $selection->option->option_label : null;
                
            case 'checkboxes':
                return $answer->selections->map(function($sel) {
                    return $sel->option->option_label;
                })->toArray();
                
            case 'dropdown':
                $selection = $answer->selections->first();
                return $selection ? $selection->option->option_label : null;
                
            case 'file_upload':
                return $answer->file_path ? [
                    'path' => $answer->file_path,
                    'name' => $answer->file_name
                ] : null;
                
            case 'likert_scale':
            case 'multiple_choice_grid':
                $gridData = [];
                foreach ($answer->selections as $sel) {
                    if ($answer->gridRow && $sel->gridColumn) {
                        $gridData[] = [
                            'row' => $answer->gridRow->row_label,
                            'column' => $sel->gridColumn->column_label
                        ];
                    }
                }
                return $gridData;
                
            default:
                return $answer->answer_value;
        }
    }

    private function calculateCompletion($response, $phases)
    {
        $totalQuestions = 0;
        $answeredQuestions = 0;
        $phaseProgress = [];

        foreach ($phases as $phase) {
            $phaseQuestions = 0;
            $phaseAnswered = 0;
            
            foreach ($phase->sections as $section) {
                foreach ($section->questions as $question) {
                    $phaseQuestions++;
                    $totalQuestions++;
                    
                    $hasAnswer = $response->answers->contains(function($answer) use ($question) {
                        return $answer->question_id == $question->id
                            && $this->answerHasValue($answer);
                    });
                    
                    if ($hasAnswer) {
                        $phaseAnswered++;
                        $answeredQuestions++;
                    }
                }
            }
            
            $phaseProgress[$phase->id] = [
                'total' => $phaseQuestions,
                'answered' => $phaseAnswered,
                'percentage' => $phaseQuestions > 0 ? round(($phaseAnswered / $phaseQuestions) * 100) : 0
            ];
        }

        return [
            'total' => $totalQuestions,
            'answered' => $answeredQuestions,
            'percentage' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0,
            'phases' => $phaseProgress
        ];
    }

    private function answerHasValue($answer): bool
    {
        if (!$answer) {
            return false;
        }

        if ($answer->selections && $answer->selections->isNotEmpty()) {
            return true;
        }

        return trim((string) ($answer->answer_value ?? '')) !== ''
            || !empty($answer->file_path);
    }

    /**
     * Sync options for a question without deleting existing rows.
     * Preserves option IDs so existing answer selections stay valid.
     */
    private function syncQuestionOptions(TracerQuestion $question, array $incomingOptions, bool $allowOther): void
    {
        // Load existing NON-other options, keyed by label
        $existing = $question->options()
            ->where('is_other', false)
            ->get()
            ->keyBy(fn($o) => $o->option_label);

        $seenLabels = [];

        foreach ($incomingOptions as $idx => $optData) {
            $label = is_array($optData) ? ($optData['label'] ?? '') : $optData;
            $label = trim((string) $label);
            if ($label === '') continue;

            $seenLabels[] = $label;

            if (isset($existing[$label])) {
                // Reuse existing row (keeps ID intact)
                $existing[$label]->update(['order_priority' => $idx]);
            } else {
                // Create brand-new option
                $question->options()->create([
                    'option_label'   => $label,
                    'order_priority' => $idx,
                    'is_other'       => false,
                ]);
            }
        }

        // Delete only options whose labels are gone
        foreach ($existing as $label => $option) {
            if (!in_array($label, $seenLabels, true)) {
                $option->delete();
            }
        }

        // Handle the sentinel "Other" option
        $otherOption = $question->options()->where('is_other', true)->first();

        if ($allowOther) {
            if (!$otherOption) {
                $question->options()->create([
                    'option_label'   => 'Other',
                    'order_priority' => count($incomingOptions),
                    'is_other'       => true,
                ]);
            } else {
                $otherOption->update(['order_priority' => count($incomingOptions)]);
            }
        } else {
            if ($otherOption) {
                $otherOption->delete();
            }
        }
    }

    /**
     * Sync the branching condition for a question.
     * Creates, updates, or deletes the rule for this question.
     * Preserves condition ID when possible.
     */
    private function syncQuestionCondition(TracerQuestion $question, ?array $conditionData): void
    {
        // Delete any existing rule for this question
        $existing = DB::table('tracer_question_conditions')
            ->where('question_id', $question->id)
            ->first();

        // No condition provided → ensure rule is removed
        if (empty($conditionData) || empty($conditionData['depends_on_question_id'])) {
            if ($existing) {
                DB::table('tracer_question_conditions')
                    ->where('id', $existing->id)
                    ->delete();
            }
            return;
        }

        // Validate the parent question exists and is not the child itself
        $parentId = (int) $conditionData['depends_on_question_id'];
        if ($parentId === (int) $question->id) {
            \Log::warning("Skipped self-referencing condition for question {$question->id}");
            return;
        }

        $parent = TracerQuestion::find($parentId);
        if (!$parent) {
            \Log::warning("Condition parent {$parentId} not found for question {$question->id}");
            return;
        }

        $matchType = in_array($conditionData['match_type'] ?? 'any', ['any', 'all', 'none'], true)
            ? $conditionData['match_type']
            : 'any';

        $labels = array_values(array_filter(
            array_map('trim', (array) ($conditionData['option_labels'] ?? [])),
            fn($l) => $l !== ''
        ));

        if (empty($labels)) {
            \Log::warning("Condition for question {$question->id} has no option labels — skipped");
            return;
        }

        // Upsert the condition row (unique on question_id)
        if ($existing) {
            DB::table('tracer_question_conditions')
                ->where('id', $existing->id)
                ->update([
                    'depends_on_question_id' => $parentId,
                    'match_type' => $matchType,
                    'updated_at' => now(),
                ]);
            $conditionId = $existing->id;
        } else {
            $conditionId = DB::table('tracer_question_conditions')->insertGetId([
                'question_id' => $question->id,
                'depends_on_question_id' => $parentId,
                'match_type' => $matchType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Replace option labels (simple delete-and-insert — these rows are tiny,
        // and nothing references them via FK cascade to alumni data)
        DB::table('tracer_question_condition_options')
            ->where('condition_id', $conditionId)
            ->delete();

        $rows = [];
        foreach ($labels as $label) {
            $rows[] = [
                'condition_id' => $conditionId,
                'option_label' => mb_substr($label, 0, 255),
                'created_at'   => now(),
            ];
        }
        if (!empty($rows)) {
            DB::table('tracer_question_condition_options')->insert($rows);
        }
    }

    /**
     * Delete a response
     */
    public function deleteResponse($responseId)
    {
        try {
            $response = TracerResponse::findOrFail($responseId);
            
            // Delete related data
            $response->answers()->delete();
            $response->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Response deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete response: ' . $e->getMessage()
            ], 500);
        }
    }


}

// This is the TracerFormController.php