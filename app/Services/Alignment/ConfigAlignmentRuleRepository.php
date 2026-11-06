<?php

namespace App\Services\Alignment;

/**
 * Config-backed implementation of AlignmentRuleRepositoryInterface.
 *
 * Reads rules from config/alignment_rules.php and
 * config/shs_alignment_rules.php (published on every deploy).
 */
class ConfigAlignmentRuleRepository implements AlignmentRuleRepositoryInterface
{
    public function resolveCollegeOccupation(?string $programCode, ?string $occupationCode): ?string
    {
        if ($programCode === null || $occupationCode === null) {
            return null;
        }

        $rules = config('alignment_rules', []);

        return $rules[$programCode][$occupationCode] ?? null;
    }

    public function resolveShsCollege(?string $shsStrand, ?string $collegeProgramCode): ?string
    {
        if ($shsStrand === null || $collegeProgramCode === null) {
            return null;
        }

        $rules = config('shs_alignment_rules', []);

        return $rules[$shsStrand][$collegeProgramCode] ?? null;
    }

    public function allCollegeRules(): array
    {
        return config('alignment_rules', []);
    }

    public function allShsRules(): array
    {
        return config('shs_alignment_rules', []);
    }
}