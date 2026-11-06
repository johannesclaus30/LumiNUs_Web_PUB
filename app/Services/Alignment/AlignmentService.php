<?php

namespace App\Services\Alignment;

/**
 * ─────────────────────────────────────────────────────────────
 *  ALIGNMENT ENGINE
 * ─────────────────────────────────────────────────────────────
 *
 *  Central place that turns raw codes into human-readable
 *  alignment levels. All controllers, exports, and insights
 *  go through this service so classification logic is
 *  consistent across the entire application.
 *
 *  Public contract:
 *    ->resolve('BSIT-MWA', 'SOFTWARE_DEV')  => 'DIRECT'
 *    ->resolveShs('CT', 'BSIT-MWA')         => 'DIRECT'
 */
class AlignmentService
{
    public const DIRECT       = 'DIRECT';
    public const RELATED      = 'RELATED';
    public const NOT_DIRECT   = 'NOT_DIRECT';
    public const UNDETERMINED = 'UNDETERMINED';

    /** All possible college→occupation outcomes, in display order. */
    public const ALL_LEVELS = [
        self::DIRECT,
        self::RELATED,
        self::NOT_DIRECT,
        self::UNDETERMINED,
    ];

    public function __construct(
        protected AlignmentRuleRepositoryInterface $rules
    ) {}

    /**
     * Classify a college program + occupation pairing.
     *
     * Returns UNDETERMINED whenever:
     *   - occupation is missing (unemployed, no job row)
     *   - program is missing
     *   - either code is not present in the rules
     *   - the rule itself is UNDETERMINED (Entrepreneur, Unclassified, etc.)
     */
    public function resolve(?string $programCode, ?string $occupationCode): string
    {
        if ($programCode === null || $occupationCode === null) {
            return self::UNDETERMINED;
        }

        $result = $this->rules->resolveCollegeOccupation($programCode, $occupationCode);

        return $result ?? self::UNDETERMINED;
    }

    /**
     * Classify an SHS strand + college program pairing.
     */
    public function resolveShs(?string $shsStrand, ?string $collegeProgramCode): string
    {
        if ($shsStrand === null || $collegeProgramCode === null) {
            return self::UNDETERMINED;
        }

        $result = $this->rules->resolveShsCollege($shsStrand, $collegeProgramCode);

        return $result ?? self::UNDETERMINED;
    }

    /**
     * Whether a level counts toward "positive" alignment in aggregate scores.
     *
     * Weighted composite:
     *   DIRECT      = 1.0
     *   RELATED     = 0.5
     *   NOT_DIRECT  = 0.0
     *   UNDETERMINED = excluded (returns null)
     */
    public function weight(string $level): ?float
    {
        return match ($level) {
            self::DIRECT       => 1.0,
            self::RELATED      => 0.5,
            self::NOT_DIRECT   => 0.0,
            default            => null,
        };
    }

    /**
     * All college→occupation rules (for exports and debugging).
     */
    public function allCollegeRules(): array
    {
        return $this->rules->allCollegeRules();
    }

    /**
     * All SHS→college rules.
     */
    public function allShsRules(): array
    {
        return $this->rules->allShsRules();
    }
}