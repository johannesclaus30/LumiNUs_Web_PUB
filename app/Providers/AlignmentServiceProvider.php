<?php

namespace App\Providers;

use App\Services\Alignment\AlignmentRuleRepositoryInterface;
use App\Services\Alignment\AlignmentService;
use App\Services\Alignment\ConfigAlignmentRuleRepository;
use Illuminate\Support\ServiceProvider;

class AlignmentServiceProvider extends ServiceProvider
{
    /**
     * Bind the alignment engine into the container.
     *
     * To swap the rule source later (e.g. from config → DB),
     * change ONLY the first binding:
     *
     *   $this->app->singleton(
     *       AlignmentRuleRepositoryInterface::class,
     *       DbAlignmentRuleRepository::class,
     *   );
     *
     * Everything else — controllers, exports, insights — keeps working.
     */
    public function register(): void
    {
        $this->app->singleton(
            AlignmentRuleRepositoryInterface::class,
            ConfigAlignmentRuleRepository::class,
        );

        $this->app->singleton(AlignmentService::class, function ($app) {
            return new AlignmentService(
                $app->make(AlignmentRuleRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}