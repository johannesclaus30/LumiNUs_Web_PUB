<?php

namespace App\Http\Controllers;

use App\Services\Alignment\AlignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstitutionalAnalyticsController extends Controller
{
    protected AlignmentService $alignment;

    // UI Mapping: Map our 4 states to the dashboard's expected keys
    protected array $stateMap = [
        'DIRECT'       => 'directly_related',
        'RELATED'      => 'somewhat_related',
        'NOT_DIRECT'   => 'not_related',
        'UNDETERMINED' => 'not_applicable',
    ];

    public function __construct(AlignmentService $alignment)
    {
        $this->alignment = $alignment;
    }

    // ═══════════════════════════════════════════════════════
    // 1. EXECUTIVE KPIs
    // ═══════════════════════════════════════════════════════
    public function kpis(Request $request, $formId)
    {
        $alumniType = $request->query('alumni_type', 'all');

        // 1. Total Alumni (only college alumni can have career alignment)
        $baseQuery = DB::table('alumnis_base')
            ->whereNull('deleted_at')
            ->where('account_status', 1);

        if ($alumniType === 'shs') {
            $totalAlumni = $baseQuery->where('alumni_type', 'shs')->count();
            return response()->json([
                'totalAlumni' => $totalAlumni, 'respondentCount' => 0, 'coverage' => 0,
                'employedCount' => 0, 'employmentRate' => 0, 'alignmentRate' => 0, 'programsTracked' => 0
            ]);
        }

        // For 'all' or 'college', we analyze the college pipeline
        $totalAlumni = $baseQuery->where('alumni_type', 'college')->count();

        // 2. Fetch all relevant data in one go (Eager loading via raw joins)
        $data = $this->fetchAlumniAnalyticsData($alumniType);

        $employedCount = 0;
        $directCount = 0;
        $classifiableCount = 0;

        foreach ($data as $row) {
            if ($row->occupation) {
                $employedCount++;
                $status = $this->alignment->resolve($row->college_program, $row->occupation);
                
                // Only DIRECT and RELATED count toward the denominator for "% aligned"
                if ($status === 'DIRECT' || $status === 'RELATED' || $status === 'NOT_DIRECT') {
                    $classifiableCount++;
                    if ($status === 'DIRECT') {
                        $directCount++;
                    }
                }
            }
        }

        $employmentRate = $totalAlumni > 0 ? round(($employedCount / $totalAlumni) * 100, 1) : 0;
        $alignmentRate = $classifiableCount > 0 ? round(($directCount / $classifiableCount) * 100, 1) : 0;

        $programsTracked = DB::table('alumni_programs')
            ->where('program_type', 'college')
            ->whereNotNull('program_name')
            ->distinct('program_name')
            ->count('program_name');

        return response()->json([
            'totalAlumni'      => $totalAlumni,
            'respondentCount'  => $employedCount, // Using employed as "responded" for career section
            'coverage'         => $totalAlumni > 0 ? round(($employedCount / $totalAlumni) * 100, 1) : 0,
            'employedCount'    => $employedCount,
            'employmentRate'   => $employmentRate,
            'alignmentRate'    => $alignmentRate,
            'programsTracked'  => $programsTracked,
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // 2. PROGRAM × ALIGNMENT MATRIX
    // ═══════════════════════════════════════════════════════
    public function programAlignmentMatrix(Request $request, $formId)
    {
        $alumniType = $request->query('alumni_type', 'all');
        if ($alumniType === 'shs') {
            return response()->json(['rows' => [], 'columns' => array_values($this->stateMap), 'mapped' => true]);
        }

        $data = $this->fetchAlumniAnalyticsData($alumniType);
        
        $matrix = [];
        $programTotals = [];
        $allowed = array_values($this->stateMap);

        foreach ($data as $row) {
            $prog = $row->college_program ?: 'Unknown';
            $status = $this->alignment->resolve($row->college_program, $row->occupation);
            $mappedCol = $this->stateMap[$status];

            if (!isset($matrix[$prog])) {
                $matrix[$prog] = array_fill_keys($allowed, 0);
                $programTotals[$prog] = 0;
            }

            // We ONLY count distinct alumni per cell. Since a user can have 
            // multiple jobs, we use a temporary set to avoid double counting.
            $key = $prog . '|' . $mappedCol;
            if (!isset($matrix[$prog]['_seen_' . $mappedCol])) {
                $matrix[$prog]['_seen_' . $mappedCol] = [];
            }
            
            if (!in_array($row->alumni_id, $matrix[$prog]['_seen_' . $mappedCol])) {
                $matrix[$prog]['_seen_' . $mappedCol][] = $row->alumni_id;
                $matrix[$prog][$mappedCol]++;
                $programTotals[$prog]++;
            }
        }

        $out = [];
        foreach ($matrix as $program => $counts) {
            $row = ['program' => $program, 'total' => $programTotals[$program]];
            foreach ($allowed as $val) {
                $row[$val] = $counts[$val] ?? 0;
            }
            $out[] = $row;
        }

        usort($out, fn($a, $b) => $b['total'] <=> $a['total']);

        return response()->json([
            'rows'    => $out,
            'columns' => $allowed,
            'mapped'  => true,
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // 3. EMPLOYMENT & COVERAGE BY PROGRAM
    // ═══════════════════════════════════════════════════════
    public function employmentByProgram(Request $request, $formId)
    {
        $alumniType = $request->query('alumni_type', 'all');
        if ($alumniType === 'shs') return response()->json([]);

        $data = $this->fetchAlumniAnalyticsData($alumniType);
        
        $stats = [];
        foreach ($data as $row) {
            $prog = $row->college_program ?: 'Unknown';
            if (!isset($stats[$prog])) {
                $stats[$prog] = ['total' => [], 'employed' => []];
            }
            
            $stats[$prog]['total'][$row->alumni_id] = true;
            if ($row->occupation) {
                $stats[$prog]['employed'][$row->alumni_id] = true;
            }
        }

        $out = [];
        foreach ($stats as $prog => $counts) {
            $total = count($counts['total']);
            $employed = count($counts['employed']);
            $out[] = [
                'program'  => $prog,
                'total'    => $total,
                'employed' => $employed,
                'rate'     => $total > 0 ? round(($employed / $total) * 100, 1) : 0,
            ];
        }

        usort($out, fn($a, $b) => $b['rate'] <=> $a['rate']);
        return response()->json($out);
    }

    // ═══════════════════════════════════════════════════════
    // 4. SHS CONTINUATION (SHS → College)
    // ═══════════════════════════════════════════════════════
    public function shsCollegeChoice(Request $request, $formId)
    {
        // 1. Get SHS alumni, their strand, and whether they pursued college
        $shsAlumni = DB::table('alumnis_base as a')
            ->join('alumni_programs as p', 'p.alumni_id', '=', 'a.id')
            ->leftJoin('alumni_checkpoints as cp', 'cp.alumni_id', '=', 'a.id')
            ->where('p.program_type', 'shs')
            ->where('p.enrollment_status', 'graduated')
            ->whereNull('a.deleted_at')
            ->select('a.id', DB::raw("COALESCE(cp.college_program, a.program) as program"), 'cp.shs_strand')
            ->get();

        if ($shsAlumni->isEmpty()) {
            return response()->json(['has_data' => false]);
        }

        // 2. Group by SHS Strand
        $byStrand = [];
        $totalContinued = 0;
        $totalShs = $shsAlumni->count();
        $topPrograms = [];
        $shsIds = $shsAlumni->pluck('id')->unique();
        $collegeStatuses = DB::table('alumni_programs')
            ->whereIn('alumni_id', $shsIds)
            ->where('program_type', 'college')
            ->whereIn('enrollment_status', ['enrolled', 'dropped', 'graduated'])
            ->select('alumni_id', 'enrollment_status')
            ->get()
            ->groupBy('alumni_id');
        $statusCounts = ['enrolled' => 0, 'dropped' => 0, 'graduated' => 0, 'not_continued' => 0];
        $statusRank = ['dropped' => 1, 'enrolled' => 2, 'graduated' => 3];

        foreach ($shsAlumni as $alum) {
            // If the strand is missing in the checkpoint, mark as 'Unknown'
            $strand = $alum->shs_strand ?: 'Unknown';
            
            if (!isset($byStrand[$strand])) {
                $byStrand[$strand] = ['total' => 0, 'continued' => 0];
            }
            $byStrand[$strand]['total']++;
            
            // Check if they pursued college (program is not empty and not 'SHS')
            $prog = trim((string) $alum->program);
            if (!empty($prog) && strtoupper($prog) !== 'SHS') {
                $byStrand[$strand]['continued']++;
                $totalContinued++;
                
                // Also track top programs
                $topPrograms[$prog] = ($topPrograms[$prog] ?? 0) + 1;
            }
        }

        foreach ($shsIds as $alumniId) {
            $status = $collegeStatuses->get($alumniId, collect())
                ->sortByDesc(fn($row) => $statusRank[$row->enrollment_status] ?? 0)
                ->first()?->enrollment_status;
            $statusCounts[$status ?: 'not_continued']++;
        }
        $totalContinued = $statusCounts['enrolled'] + $statusCounts['dropped'] + $statusCounts['graduated'];

        // 3. Format for Chart.js (Sort by highest total alumni first)
        arsort($byStrand); 
        
        $labels = [];
        $data = [];

        foreach ($byStrand as $strand => $stats) {
            $labels[] = $strand;
            $rate = $stats['total'] > 0 ? round(($stats['continued'] / $stats['total']) * 100, 1) : 0;
            $data[] = $rate;
        }

        // Format Top Programs
        arsort($topPrograms);
        $topProgramsArray = [];
        foreach ($topPrograms as $name => $count) {
            $topProgramsArray[] = ['program_name' => $name, 'count' => $count];
        }

        return response()->json([
            'has_data'              => true,
            'total_shs_alumni'      => $totalShs,
            'continued_count'       => $totalContinued,
            'continuation_rate'     => round(($totalContinued / $totalShs) * 100, 1),
            'status_counts'         => $statusCounts,
            'chart_labels'          => $labels,
            'chart_data'            => $data,
            'top_college_programs'  => $topProgramsArray
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // 5. SHS → COLLEGE ALIGNMENT MATRIX
    // ═══════════════════════════════════════════════════════
    public function shsAlignmentMatrix(Request $request, $formId)
    {
        // Get SHS strands + the college program they ended up in
        $data = DB::table('alumnis_base as a')
            ->leftJoin('alumni_checkpoints as cp', 'cp.alumni_id', '=', 'a.id')
            ->where(function ($query) {
                $query->whereNotNull('cp.college_program')
                    ->orWhereNotNull('a.program');
            })
            ->whereNotNull('cp.shs_strand') // They have an SHS strand record
            ->whereNull('a.deleted_at')
            ->select(
                'a.id as alumni_id',
                DB::raw("COALESCE(cp.college_program, a.program) as college_program"),
                'cp.shs_strand'
            )
            ->get();

        $matrix = [];
        $strandTotals = [];
        $allowed = ['directly_related', 'somewhat_related', 'not_related', 'not_applicable'];

        foreach ($data as $row) {
            $strand = $row->shs_strand;
            // Since they ended up in a college program, it defaults to their `program` field.
            // But we need the short code. For simplicity, we'll map it.
            $progCode = $this->mapProgramNameToCode($row->college_program);
            
            $status = $this->alignment->resolveShs($strand, $progCode);
            $mappedCol = $this->stateMap[$status] ?? 'not_applicable';

            if (!isset($matrix[$strand])) {
                $matrix[$strand] = array_fill_keys($allowed, 0);
                $strandTotals[$strand] = 0;
            }

            $key = $strand . '|' . $mappedCol;
            if (!isset($matrix[$strand]['_seen_' . $mappedCol])) {
                $matrix[$strand]['_seen_' . $mappedCol] = [];
            }
            
            if (!in_array($row->alumni_id, $matrix[$strand]['_seen_' . $mappedCol])) {
                $matrix[$strand]['_seen_' . $mappedCol][] = $row->alumni_id;
                $matrix[$strand][$mappedCol]++;
                $strandTotals[$strand]++;
            }
        }

        $out = [];
        foreach ($matrix as $strand => $counts) {
            $row = ['program' => $strand, 'total' => $strandTotals[$strand]];
            foreach ($allowed as $val) {
                $row[$val] = $counts[$val] ?? 0;
            }
            $out[] = $row;
        }

        usort($out, fn($a, $b) => $b['total'] <=> $a['total']);

        return response()->json([
            'rows' => $out,
            'columns' => $allowed,
            'mapped' => true
        ]);
    }

        // ═══════════════════════════════════════════════════════
    // 6. TREND BY YEAR
    // ═══════════════════════════════════════════════════════
    public function trendByYear(Request $request, $formId)
    {
        $alumniType = $request->query('alumni_type', 'all');
        if ($alumniType === 'shs') return response()->json([]);

        // Get graduation year from alumni_programs and join with employment data
        $data = DB::table('alumnis_base as a')
            ->join('alumni_programs as p', 'p.alumni_id', '=', 'a.id')
            ->leftJoin('alumni_employments as e', function($join) {
                $join->on('e.alumni_id', '=', 'a.id')
                     ->where('e.is_current', true);
            })
            ->where('a.alumni_type', 'college')
            ->where('p.program_type', 'college')
            ->where('p.enrollment_status', 'graduated')
            ->whereNull('a.deleted_at')
            ->whereNotNull('p.year_graduated')
            ->select(
                DB::raw("EXTRACT(YEAR FROM p.year_graduated)::int as year"),
                DB::raw('COUNT(DISTINCT a.id) as total'),
                DB::raw('COUNT(DISTINCT e.alumni_id) as employed')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $out = $data->map(fn($r) => [
            'year'      => (int) $r->year,
            'total'     => (int) $r->total,
            'employed'  => (int) $r->employed,
            'rate'      => $r->total > 0 ? round(($r->employed / $r->total) * 100, 1) : 0,
        ]);

        return response()->json($out);
    }

        // ═══════════════════════════════════════════════════════
    // 7. CHECKPOINT INSIGHTS (Satisfaction, Preparedness, etc.)
    // ═══════════════════════════════════════════════════════
    public function checkpointInsights(Request $request, $formId)
    {
        $alumniType = $request->query('alumni_type', 'all');
        if ($alumniType === 'shs') return response()->json([]);

        // Fetch Checkpoint data + Program
        $data = DB::table('alumnis_base as a')
            ->join('alumni_checkpoints as cp', 'cp.alumni_id', '=', 'a.id')
            ->leftJoin('alumni_employments as e', function($join) {
                $join->on('e.alumni_id', '=', 'a.id')->where('e.is_current', true);
            })
            ->where('a.alumni_type', 'college')
            ->whereNull('a.deleted_at')
            ->select(
                'a.id',
                'cp.college_program',
                'cp.college_satisfaction',
                'cp.college_prepared',
                'cp.college_college_influence',
                'cp.college_useful_skills',
                'cp.college_would_choose_again',
                'cp.college_further_study',
                'cp.college_perceived_relation',
                'cp.college_reason_not_related',
                'cp.shs_satisfaction',
                'cp.shs_match_interests',
                'cp.shs_prepared',
                'cp.shs_primary_activity',
                'cp.shs_usefulness',
                'cp.shs_satisfaction_choice',
                'e.occupation'
            )
            ->get();

        $programs = [];
        $shs = [
            'satisfaction' => [0, 0], 'match_interests' => [0, 0],
            'preparedness' => [0, 0], 'usefulness' => [0, 0],
            'satisfaction_choice' => [0, 0], 'primary_activity' => [],
        ];
        $wouldChooseAgainCounts = [
            'definitely_yes' => 0, 'probably_yes' => 0, 'unsure' => 0, 'probably_not' => 0, 'definitely_not' => 0
        ];
        $perceivedRelationCounts = [];
        $furtherStudyCounts = [];
        $employmentStatusCounts = [];
        $reasonNotRelatedCounts = [];
        $normalizeResponse = static function ($value): string {
            $value = strtolower(trim((string) $value));
            return match ($value) {
                'directly related', 'directly_related' => 'Directly Related',
                'somewhat related', 'somewhat_related' => 'Somewhat Related',
                'not related', 'not_related' => 'Not Related',
                'unsure', 'undetermined' => 'Unsure',
                default => ucwords(str_replace(['_', '-'], ' ', $value)),
            };
        };

        foreach ($data as $row) {
            $prog = $row->college_program ?: 'Unknown';
            
            if (!isset($programs[$prog])) {
                $programs[$prog] = [
                    'satisfaction_sum' => 0, 'satisfaction_count' => 0,
                    'prepared_sum' => 0, 'prepared_count' => 0,
                    'influence_sum' => 0, 'influence_count' => 0,
                    'useful_skills_sum' => 0, 'useful_skills_count' => 0,
                    'aligned_count' => 0, 'total_employed' => 0
                ];
            }

            foreach ([
                'college_satisfaction' => ['satisfaction_sum', 'satisfaction_count'],
                'college_prepared' => ['prepared_sum', 'prepared_count'],
                'college_college_influence' => ['influence_sum', 'influence_count'],
                'college_useful_skills' => ['useful_skills_sum', 'useful_skills_count'],
            ] as $field => [$sumKey, $countKey]) {
                if ($row->{$field} !== null) {
                    $programs[$prog][$sumKey] += (int) $row->{$field};
                    $programs[$prog][$countKey]++;
                }
            }

            foreach ([
                'shs_satisfaction' => 'satisfaction',
                'shs_match_interests' => 'match_interests',
                'shs_prepared' => 'preparedness',
                'shs_usefulness' => 'usefulness',
                'shs_satisfaction_choice' => 'satisfaction_choice',
            ] as $field => $key) {
                if ($row->{$field} !== null) {
                    $shs[$key][0] += (int) $row->{$field};
                    $shs[$key][1]++;
                }
            }

            if ($row->shs_primary_activity) {
                $activity = (string) $row->shs_primary_activity;
                $shs['primary_activity'][$activity] = ($shs['primary_activity'][$activity] ?? 0) + 1;
            }

            foreach ([
                'college_perceived_relation' => 'perceived_relation',
                'college_further_study' => 'further_study',
                'college_reason_not_related' => 'reason_not_related',
            ] as $field => $key) {
                if ($row->{$field} !== null && $row->{$field} !== '') {
                    $countKey = $normalizeResponse($row->{$field});
                    if ($key === 'perceived_relation') {
                        $perceivedRelationCounts[$countKey] = ($perceivedRelationCounts[$countKey] ?? 0) + 1;
                    } elseif ($key === 'further_study') {
                        $furtherStudyCounts[$countKey] = ($furtherStudyCounts[$countKey] ?? 0) + 1;
                    } elseif ($key === 'reason_not_related') {
                        $reasonNotRelatedCounts[$countKey] = ($reasonNotRelatedCounts[$countKey] ?? 0) + 1;
                    }
                }
            }

            $employmentStatus = $row->occupation ? 'employed' : 'not_reported';
            $employmentStatusCounts[$employmentStatus] = ($employmentStatusCounts[$employmentStatus] ?? 0) + 1;

            if ($row->college_would_choose_again && isset($wouldChooseAgainCounts[$row->college_would_choose_again])) {
                $wouldChooseAgainCounts[$row->college_would_choose_again]++;
            }

            if ($row->occupation) {
                $programs[$prog]['total_employed']++;
                $status = $this->alignment->resolve($row->college_program, $row->occupation);
                if ($status === 'DIRECT') {
                    $programs[$prog]['aligned_count']++;
                }
            }
        }

        // Format for Chart.js
        $chartLabels = [];
        $scatterData = [];
        $preparedData = [];
        $influenceData = [];
        $skillsData = [];
        $priorities = [];

        foreach ($programs as $prog => $stats) {
            $chartLabels[] = $prog;
            
            // Avg Satisfaction (1-5 scale)
            $avgSat = $stats['satisfaction_count'] > 0 ? round($stats['satisfaction_sum'] / $stats['satisfaction_count'], 1) : 0;
            
            // Alignment % (for scatter)
            $alignRate = $stats['total_employed'] > 0 ? round(($stats['aligned_count'] / $stats['total_employed']) * 100, 1) : 0;
            
            $scatterData[] = ['x' => $avgSat, 'y' => $alignRate, 'label' => $prog];
            
            $preparedData[] = $stats['prepared_count'] > 0 ? round($stats['prepared_sum'] / $stats['prepared_count'], 1) : 0;
            $influenceData[] = $stats['influence_count'] > 0 ? round($stats['influence_sum'] / $stats['influence_count'], 1) : 0;
            $skills = $stats['useful_skills_count'] > 0 ? round($stats['useful_skills_sum'] / $stats['useful_skills_count'], 1) : 0;
            $skillsData[] = $skills;

            $signals = [];
            if ($stats['prepared_count'] > 0 && $preparedData[array_key_last($preparedData)] < 3) $signals[] = 'low workplace preparedness';
            if ($stats['useful_skills_count'] > 0 && $skills < 3) $signals[] = 'skills need strengthening';
            if ($stats['total_employed'] > 0 && $alignRate < 50) $signals[] = 'low direct career alignment';
            if ($stats['satisfaction_count'] > 0 && $avgSat < 3) $signals[] = 'low graduate satisfaction';
            $checkpointResponses = max(
                $stats['satisfaction_count'],
                $stats['prepared_count'],
                $stats['influence_count'],
                $stats['useful_skills_count']
            );
            if ($checkpointResponses === 0) $signals[] = 'insufficient checkpoint evidence';
            $priorities[] = [
                'program' => $prog,
                'signals' => $signals ?: ['available indicators meet current thresholds'],
                'priority' => count($signals) > 1 ? 'high' : ($signals ? 'watch' : 'stable'),
                'evidence' => [
                    'respondents' => max($checkpointResponses, $stats['total_employed']),
                    'satisfaction' => $stats['satisfaction_count'] > 0 ? $avgSat : null,
                    'preparedness' => $stats['prepared_count'] > 0
                        ? round($stats['prepared_sum'] / $stats['prepared_count'], 1) : null,
                    'useful_skills' => $skills,
                    'direct_alignment' => $stats['total_employed'] > 0 ? $alignRate : null,
                ],
            ];
        }
        usort($priorities, fn($a, $b) => ['high' => 0, 'watch' => 1, 'stable' => 2][$a['priority']] <=> ['high' => 0, 'watch' => 1, 'stable' => 2][$b['priority']]);
        $average = fn($key) => $shs[$key][1] > 0 ? round($shs[$key][0] / $shs[$key][1], 1) : 0;

        return response()->json([
            'scatter_data' => $scatterData,
            'chart_labels' => $chartLabels,
            'preparedness' => $preparedData,
            'influence'    => $influenceData,
            'useful_skills' => $skillsData,
            'would_choose_again' => $wouldChooseAgainCounts,
            'college' => [
                'perceived_relation' => $perceivedRelationCounts,
                'further_study' => $furtherStudyCounts,
                'reason_not_related' => $reasonNotRelatedCounts,
                'employment_status' => $employmentStatusCounts,
            ],
            'shs' => [
                'averages' => [
                    'satisfaction' => $average('satisfaction'),
                    'match_interests' => $average('match_interests'),
                    'preparedness' => $average('preparedness'),
                    'usefulness' => $average('usefulness'),
                    'satisfaction_choice' => $average('satisfaction_choice'),
                ],
                'primary_activity' => $shs['primary_activity'],
            ],
            'curriculum_priorities' => array_slice($priorities, 0, 8),
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // 8. INDUSTRY BREAKDOWN BY PROGRAM
    // ═══════════════════════════════════════════════════════
    public function industryByProgram(Request $request, $formId)
    {
        $programCode = $request->query('program', 'all');
        $alumniType  = $request->query('alumni_type', 'all');

        if ($alumniType === 'shs') {
            return response()->json([]);
        }

        // 1. Build the base query for College Alumni with current jobs
        $query = DB::table('alumnis_base as a')
            ->join('alumni_employments as e', 'e.alumni_id', '=', 'a.id')
            ->where('a.alumni_type', 'college')
            ->whereNull('a.deleted_at')
            ->where('a.account_status', 1)
            ->where('e.is_current', true);

        // 2. If a specific program is selected, filter by it
        // We use a subquery to check alumni_programs so we don't break the main query
        if ($programCode !== 'all') {
            $query->whereExists(function ($sub) use ($programCode) {
                $sub->select(DB::raw(1))
                    ->from('alumni_programs as p')
                    ->whereColumn('p.alumni_id', 'a.id')
                    ->where('p.program_type', 'college')
                    ->where('p.program_name', $programCode);
            });
        }

        // 3. Fetch the counts. 
        // We group by industry, and coalesce empty strings to 'Not Specified' so they show up
        $results = $query->select(
                DB::raw("CASE WHEN e.industry IS NULL OR e.industry = '' THEN 'Not Specified' ELSE e.industry END as industry_code"),
                DB::raw('COUNT(DISTINCT a.id) as count')
            )
            ->groupBy('industry_code')
            ->orderByDesc('count')
            ->get();

        // 4. Map industry codes to readable labels
        $industryMap = [
            'IND_01' => 'IT & Digital',
            'IND_02' => 'Healthcare',
            'IND_03' => 'Education',
            'IND_04' => 'Finance & Banking',
            'IND_05' => 'Business',
            'IND_06' => 'Marketing & Media',
            'IND_07' => 'Manufacturing',
            'IND_08' => 'Engineering & Construction',
            'IND_09' => 'Retail & E-Commerce',
            'IND_10' => 'Hospitality & Tourism',
            'IND_11' => 'Transportation & Logistics',
            'IND_12' => 'Government',
            'IND_13' => 'Agriculture & Environment',
            'IND_14' => 'Real Estate',
            'IND_15' => 'Research & Science',
            'IND_16' => 'Other Services',
            'Not Specified' => 'Not Specified (Empty)',
        ];

        $out = $results->map(function ($row) use ($industryMap) {
            return [
                'code'  => $row->industry_code,
                'label' => $industryMap[$row->industry_code] ?? $row->industry_code,
                'count' => (int) $row->count,
            ];
        });

        return response()->json($out);
    }

    // ═══════════════════════════════════════════════════════
    // 9. GET PROGRAM LIST (For Frontend Dropdown)
    // ═══════════════════════════════════════════════════════
    public function getProgramList(Request $request, $formId)
    {
        $programs = DB::table('alumni_programs')
            ->where('program_type', 'college')
            ->where('enrollment_status', 'graduated')
            ->whereNotNull('program_name')
            ->where('program_name', '!=', '')
            ->distinct()
            ->orderBy('program_name')
            ->pluck('program_name')
            ->toArray();

        // Re-index to ensure it's a clean JSON array
        return response()->json(array_values($programs));
    }

    /**
     * Deterministic institution-facing narratives generated from current
     * checkpoint and employment evidence. No generative model is involved.
     */
    public function narratives(Request $request, $formId)
    {
        $shsRows = DB::table('alumnis_base as a')
            ->leftJoin('alumni_checkpoints as cp', 'cp.alumni_id', '=', 'a.id')
            ->leftJoin('alumni_programs as p', function ($join) {
                $join->on('p.alumni_id', '=', 'a.id')
                    ->where('p.program_type', 'shs')
                    ->where('p.enrollment_status', 'graduated');
            })
            ->whereNull('a.deleted_at')
            ->whereNotNull('cp.shs_strand')
            ->select(
                'a.id',
                'cp.shs_strand',
                'cp.shs_satisfaction',
                'cp.shs_match_interests',
                'cp.shs_prepared',
                'cp.shs_usefulness',
                DB::raw("COALESCE(cp.college_program, a.program) as college_program"),
                'p.id as shs_program_id'
            )
            ->get();

        $shsCount = $shsRows->count();
        $continued = $shsRows->filter(function ($row) {
            return $row->college_program && strtoupper(trim((string) $row->college_program)) !== 'SHS';
        })->count();
        $shsAverage = function (string $field) use ($shsRows): ?float {
            $values = $shsRows->pluck($field)->filter(fn($value) => $value !== null)->map(fn($value) => (int) $value);
            return $values->isNotEmpty() ? round($values->avg(), 1) : null;
        };
        $continuationRate = $shsCount > 0 ? round(($continued / $shsCount) * 100, 1) : null;
        $shsPrepared = $shsAverage('shs_prepared');
        $shsInterestMatch = $shsAverage('shs_match_interests');
        $shsUsefulness = $shsAverage('shs_usefulness');

        $shsParagraphs = [];
        $shsRecommendations = [];
        if ($shsCount === 0) {
            $shsParagraphs[] = 'There is not enough SHS checkpoint evidence to produce a reliable institutional interpretation.';
            $shsRecommendations[] = 'Increase completed SHS checkpoints, especially strand, preparedness, and post-graduation activity responses.';
        } else {
            $shsParagraphs[] = "{$shsCount} SHS alumni records with strand information are available. " .
                ($continuationRate !== null ? "{$continuationRate}% continued to a college program." : 'College continuation cannot yet be estimated.');
            if ($continuationRate !== null && $continuationRate < 70) {
                $shsParagraphs[] = 'The continuation result suggests that the institution should examine advising, transition support, and the accessibility of post-secondary pathways.';
                $shsRecommendations[] = 'Review strand-to-college advising and create targeted transition support for graduates who do not continue immediately.';
            }
            if ($shsPrepared !== null && $shsPrepared < 3) {
                $shsParagraphs[] = "Average SHS preparedness is {$shsPrepared}/5, indicating a potential readiness gap before college entry.";
                $shsRecommendations[] = 'Strengthen career guidance, study-readiness activities, and strand-based preparation before graduation.';
            }
            if ($shsInterestMatch !== null && $shsInterestMatch < 3) {
                $shsRecommendations[] = 'Use interest-matching results to improve strand orientation and individualized career counseling.';
            }
            if ($shsUsefulness !== null && $shsUsefulness < 3) {
                $shsRecommendations[] = 'Review SHS learning activities and connect classroom outcomes more explicitly to college and workplace pathways.';
            }
            if (!$shsRecommendations) {
                $shsRecommendations[] = 'Maintain current SHS pathway supports and continue monitoring strand continuation and preparedness by cohort.';
            }
        }

        $collegeRows = DB::table('alumnis_base as a')
            ->join('alumni_checkpoints as cp', 'cp.alumni_id', '=', 'a.id')
            ->leftJoin('alumni_employments as e', function ($join) {
                $join->on('e.alumni_id', '=', 'a.id')->where('e.is_current', true);
            })
            ->where('a.alumni_type', 'college')
            ->whereNull('a.deleted_at')
            ->select(
                'cp.college_program',
                'cp.college_satisfaction',
                'cp.college_prepared',
                'cp.college_useful_skills',
                'e.occupation'
            )
            ->get();

        $collegeCount = $collegeRows->count();
        $averageCollege = function (string $field) use ($collegeRows): ?float {
            $values = $collegeRows->pluck($field)->filter(fn($value) => $value !== null)->map(fn($value) => (int) $value);
            return $values->isNotEmpty() ? round($values->avg(), 1) : null;
        };
        $collegeEmploymentRate = $collegeCount > 0
            ? round(($collegeRows->filter(fn($row) => $row->occupation)->count() / $collegeCount) * 100, 1)
            : null;
        $collegeSatisfaction = $averageCollege('college_satisfaction');
        $collegePrepared = $averageCollege('college_prepared');
        $collegeSkills = $averageCollege('college_useful_skills');

        $collegeParagraphs = [];
        $collegeRecommendations = [];
        if ($collegeCount === 0) {
            $collegeParagraphs[] = 'There is not enough College checkpoint evidence to produce a reliable program-performance interpretation.';
            $collegeRecommendations[] = 'Increase completed College checkpoints and current employment records before using program comparisons for curriculum decisions.';
        } else {
            $collegeParagraphs[] = "{$collegeCount} College checkpoint records are available. " .
                ($collegeEmploymentRate !== null ? "{$collegeEmploymentRate}% have a current occupation record." : 'Employment coverage cannot yet be estimated.');
            if ($collegePrepared !== null && $collegePrepared < 3) {
                $collegeParagraphs[] = "Average workplace preparedness is {$collegePrepared}/5, which indicates that industry-readiness support should be reviewed.";
                $collegeRecommendations[] = 'Expand internships, applied projects, employer engagement, and workplace-readiness activities.';
            }
            if ($collegeSkills !== null && $collegeSkills < 3) {
                $collegeRecommendations[] = 'Map the skills reported by alumni to course outcomes and prioritize practical skill-building where ratings are weakest.';
            }
            if ($collegeSatisfaction !== null && $collegeSatisfaction < 3) {
                $collegeRecommendations[] = "Average program satisfaction is {$collegeSatisfaction}/5; review student-to-alumni feedback for curriculum and support-service improvements.";
                $collegeRecommendations[] = 'Conduct program-level curriculum review using the lowest-rated checkpoint dimensions and alumni comments.';
            }
            if ($collegeEmploymentRate !== null && $collegeEmploymentRate < 70) {
                $collegeRecommendations[] = 'Strengthen graduate employment follow-up, career placement, and employer partnerships in programs with lower employment coverage.';
            }
            if (!$collegeRecommendations) {
                $collegeRecommendations[] = 'Maintain current curriculum strengths and continue monitoring preparedness, useful skills, satisfaction, and employment by program.';
            }
        }

        return response()->json([
            'shs' => [
                'title' => 'SHS improvement narrative',
                'paragraphs' => $shsParagraphs,
                'recommendations' => $shsRecommendations,
                'metrics' => [
                    'records' => $shsCount,
                    'continuation_rate' => $continuationRate,
                    'preparedness' => $shsPrepared,
                    'interest_match' => $shsInterestMatch,
                    'usefulness' => $shsUsefulness,
                ],
            ],
            'college' => [
                'title' => 'College improvement narrative',
                'paragraphs' => $collegeParagraphs,
                'recommendations' => $collegeRecommendations,
                'metrics' => [
                    'records' => $collegeCount,
                    'employment_rate' => $collegeEmploymentRate,
                    'satisfaction' => $collegeSatisfaction,
                    'preparedness' => $collegePrepared,
                    'useful_skills' => $collegeSkills,
                ],
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════
    
    /**
     * Fetches the base dataset for institutional analytics.
     * Handles multiple current jobs (is_current = true) per alumni.
     */
    private function fetchAlumniAnalyticsData(?string $type = 'all')
    {
        $query = DB::table('alumnis_base as a')
            ->leftJoin('alumni_checkpoints as cp', 'cp.alumni_id', '=', 'a.id')
            ->leftJoin('alumni_employments as e', function($join) {
                $join->on('e.alumni_id', '=', 'a.id')
                     ->where('e.is_current', true); // Use all current jobs
            })
            ->whereNull('a.deleted_at')
            ->where('a.account_status', 1)
            ->where('a.alumni_type', 'college') // Base dataset is only college grads
            ->select(
                'a.id as alumni_id',
                'cp.college_program',
                'e.occupation',
                'e.job_title',
                'e.company'
            );

        return $query->get();
    }

    /**
     * Helper to turn "BS Information Technology" into "BSIT-MWA"
     */
    private function mapProgramNameToCode(?string $name): ?string
    {
        if (!$name) return null;
        $name = strtoupper($name);
        $knownCodes = [
            'BSA', 'BSMA', 'BSBA-FM', 'BSBA-MM', 'BSTM', 'BSMT', 'BSN',
            'BSPSY', 'BSARCH', 'BSCS-ML', 'BSIT-MWA', 'BSCE', 'BSCPE', 'BMMA',
        ];
        if (in_array($name, $knownCodes, true)) return $name;
        if (str_contains($name, 'INFORMATION TECHNOLOGY')) return 'BSIT-MWA';
        if (str_contains($name, 'COMPUTER SCIENCE')) return 'BSCS-ML';
        if (str_contains($name, 'NURSING')) return 'BSN';
        if (str_contains($name, 'ACCOUNTANCY')) return 'BSA';
        if (str_contains($name, 'PSYCHOLOGY')) return 'BSPSY';
        if (str_contains($name, 'CIVIL ENGINEERING')) return 'BSCE';
        if (str_contains($name, 'COMPUTER ENGINEERING')) return 'BSCPE';
        if (str_contains($name, 'ARCHITECTURE')) return 'BSARCH';
        if (str_contains($name, 'MEDICAL TECHNOLOGY')) return 'BSMT';
        if (str_contains($name, 'TOURISM')) return 'BSTM';
        if (str_contains($name, 'MULTIMEDIA')) return 'BMMA';
        if (str_contains($name, 'FINANCIAL MANAGEMENT')) return 'BSBA-FM';
        if (str_contains($name, 'MARKETING')) return 'BSBA-MM';
        if (str_contains($name, 'MANAGEMENT ACCOUNTING')) return 'BSMA';
        return null;
    }
}