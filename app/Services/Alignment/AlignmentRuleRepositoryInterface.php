<?php

namespace App\Services\Alignment;

/**
 * Contract for alignment-rule sources.
 *
 * Today the rules come from config/*.php. When you later move them
 * into a database table, just implement this interface with a DB-backed
 * repository and swap the binding in AlignmentServiceProvider — every
 * caller keeps working unchanged.
 */
interface AlignmentRuleRepositoryInterface
{
    /**
     * Resolve the alignment level for a (college program, occupation) pair.
     *
     * @return string|null  One of DIRECT|RELATED|NOT_DIRECT|UNDETERMINED,
     *                      or null if the pair is completely unknown.
     */
    public function resolveCollegeOccupation(?string $programCode, ?string $occupationCode): ?string;

    /**
     * Resolve the alignment level for a (SHS strand, college program) pair.
     *
     * @return string|null  One of DIRECT|RELATED|NOT_DIRECT,
     *                      or null if the pair is unknown.
     */
    public function resolveShsCollege(?string $shsStrand, ?string $collegeProgramCode): ?string;

    /**
     * Return every college→occupation rule as a nested array, for debugging / export.
     *
     * @return array<string, array<string, string>>
     */
    public function allCollegeRules(): array;

    /**
     * Return every SHS→college rule as a nested array, for debugging / export.
     *
     * @return array<string, array<string, string>>
     */
    public function allShsRules(): array;
}