<?php

namespace App\Console\Commands;

use App\Services\Alignment\AlignmentService;
use Illuminate\Console\Command;

/**
 * php artisan alignment:verify
 *
 * Sanity-check the alignment rules loaded from config. Fails loudly
 * if counts don't match the seed (560 college rules, 112 SHS rules),
 * so you catch typos before they silently classify alumni wrong.
 */
class VerifyAlignmentRules extends Command
{
    protected $signature   = 'alignment:verify';
    protected $description = 'Verify that alignment rules loaded from config match the seed counts.';

    private const EXPECTED_COLLEGE_PROGRAMS  = 14;
    private const EXPECTED_COLLEGE_OCCUPATIONS = 40;
    private const EXPECTED_COLLEGE_RULES      = 560;

    private const EXPECTED_SHS_STRANDS        = 8;
    private const EXPECTED_SHS_COLLEGE_TOTAL  = 112;

    public function handle(AlignmentService $alignment): int
    {
        $this->info('────── Alignment Rules Verification ──────');
        $this->newLine();

        $collegeRules = $alignment->allCollegeRules();
        $shsRules     = $alignment->allShsRules();

        // ── College checks ───────────────────────────────────
        $collegeProgramCount = count($collegeRules);
        $totalCollegeRules   = 0;
        $perProgram          = [];
        $levelCounts         = [
            AlignmentService::DIRECT       => 0,
            AlignmentService::RELATED      => 0,
            AlignmentService::NOT_DIRECT   => 0,
            AlignmentService::UNDETERMINED => 0,
        ];

        foreach ($collegeRules as $program => $occupations) {
            $perProgram[$program] = count($occupations);
            $totalCollegeRules   += count($occupations);

            foreach ($occupations as $level) {
                if (isset($levelCounts[$level])) {
                    $levelCounts[$level]++;
                }
            }
        }

        $this->line('📘 <comment>College Programs Loaded:</comment> ' . $collegeProgramCount);
        $this->line('📘 <comment>Total College Rules:</comment> '    . $totalCollegeRules);
        $this->newLine();

        $this->table(
            ['Program', 'Rules'],
            collect($perProgram)
                ->map(fn ($count, $program) => [
                    $program,
                    $count === self::EXPECTED_COLLEGE_OCCUPATIONS
                        ? "<fg=green>$count</>"
                        : "<fg=red>$count (expected " . self::EXPECTED_COLLEGE_OCCUPATIONS . ')</>',
                ])
                ->values()
                ->toArray()
        );

        $this->newLine();
        $this->line('📊 <comment>Distribution across 4 levels:</comment>');
        $this->table(
            ['Level', 'Count'],
            [
                ['DIRECT',        $levelCounts[AlignmentService::DIRECT]],
                ['RELATED',       $levelCounts[AlignmentService::RELATED]],
                ['NOT_DIRECT',    $levelCounts[AlignmentService::NOT_DIRECT]],
                ['UNDETERMINED',  $levelCounts[AlignmentService::UNDETERMINED]],
            ]
        );

        // ── SHS checks ───────────────────────────────────────
        $shsStrandCount = count($shsRules);
        $totalShsRules  = 0;
        $perStrand      = [];
        foreach ($shsRules as $strand => $programs) {
            $perStrand[$strand] = count($programs);
            $totalShsRules      += count($programs);
        }

        $this->newLine();
        $this->line('🎓 <comment>SHS Strands Loaded:</comment> '      . $shsStrandCount);
        $this->line('🎓 <comment>Total SHS→College Rules:</comment> ' . $totalShsRules);
        $this->newLine();

        $this->table(
            ['Strand', 'Rules'],
            collect($perStrand)
                ->map(fn ($count, $strand) => [
                    $strand,
                    $count === self::EXPECTED_COLLEGE_PROGRAMS
                        ? "<fg=green>$count</>"
                        : "<fg=red>$count (expected " . self::EXPECTED_COLLEGE_PROGRAMS . ')</>',
                ])
                ->values()
                ->toArray()
        );

        // ── Sanity spot-checks ───────────────────────────────
        $this->newLine();
        $this->line('🔍 <comment>Spot checks (from seed):</comment>');
        $spots = [
            ['BSIT-MWA', 'SOFTWARE_DEV',          AlignmentService::DIRECT],
            ['BSIT-MWA', 'NURSE',                 AlignmentService::NOT_DIRECT],
            ['BSN',      'NURSE',                 AlignmentService::DIRECT],
            ['BSA',      'ACCOUNTANT',            AlignmentService::DIRECT],
            ['BSCS-ML',  'DATA_ML',               AlignmentService::DIRECT],
            ['BSCE',     'CIVIL_ENG',             AlignmentService::DIRECT],
            ['BMMA',     'GRAPHIC_DESIGN',        AlignmentService::DIRECT],
            ['BSIT-MWA', null,                    AlignmentService::UNDETERMINED],
            ['UNKNOWN',  'SOFTWARE_DEV',          AlignmentService::UNDETERMINED],
        ];

        $spotRows = [];
        $spotFailed = false;
        foreach ($spots as [$program, $occupation, $expected]) {
            $actual = $alignment->resolve($program, $occupation);
            $ok     = $actual === $expected;
            $spotFailed = $spotFailed || !$ok;
            $spotRows[] = [
                $program ?? 'null',
                $occupation ?? 'null',
                $ok ? "<fg=green>{$actual}</>" : "<fg=red>{$actual}</>",
                $ok ? '✅' : "❌ expected {$expected}",
            ];
        }
        $this->table(['Program', 'Occupation', 'Result', 'Status'], $spotRows);

        // ── SHS spot checks ──────────────────────────────────
        $this->newLine();
        $this->line('🔍 <comment>SHS spot checks:</comment>');
        $shsSpots = [
            ['CT',   'BSIT-MWA',  AlignmentService::DIRECT],
            ['STEM', 'BSCE',      AlignmentService::DIRECT],
            ['ABM',  'BSA',       AlignmentService::DIRECT],
            ['HUMSS','BSIT-MWA',  AlignmentService::NOT_DIRECT],
            ['CT',   'BSN',       AlignmentService::NOT_DIRECT],
        ];
        $shsRows = [];
        foreach ($shsSpots as [$strand, $program, $expected]) {
            $actual = $alignment->resolveShs($strand, $program);
            $ok     = $actual === $expected;
            $spotFailed = $spotFailed || !$ok;
            $shsRows[] = [
                $strand,
                $program,
                $ok ? "<fg=green>{$actual}</>" : "<fg=red>{$actual}</>",
                $ok ? '✅' : "❌ expected {$expected}",
            ];
        }
        $this->table(['Strand', 'College Program', 'Result', 'Status'], $shsRows);

        // ── Final verdict ────────────────────────────────────
        $this->newLine();
        $allGood =
            $collegeProgramCount === self::EXPECTED_COLLEGE_PROGRAMS &&
            $totalCollegeRules   === self::EXPECTED_COLLEGE_RULES &&
            $shsStrandCount      === self::EXPECTED_SHS_STRANDS &&
            $totalShsRules       === self::EXPECTED_SHS_COLLEGE_TOTAL &&
            !$spotFailed;

        if ($allGood) {
            $this->info('✅ All alignment rules loaded correctly.');
            return self::SUCCESS;
        }

        $this->error('❌ Alignment rules have issues. See the red rows above.');
        return self::FAILURE;
    }
}