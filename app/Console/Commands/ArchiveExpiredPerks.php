<?php

namespace App\Console\Commands;

use App\Models\Perks;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveExpiredPerks extends Command
{
    protected $signature = 'perks:archive-expired';
    protected $description = 'Archive perks that have passed their valid_until date';

    const PH_TIMEZONE = 'Asia/Manila';

    public function handle()
    {
        $now = Carbon::now(self::PH_TIMEZONE)->startOfDay();
        
        $this->info('🔍 Checking for expired perks...');
        $this->info('Current Philippines date: ' . $now->toDateString());
        
        // Find active perks that have expired (using Philippines time)
        $expiredPerks = Perks::where(function($query) {
                $query->where('status', 1)
                      ->orWhereNull('status');
            })
            ->where('valid_until', '<', $now)
            ->get();

        $count = $expiredPerks->count();
        
        if ($count === 0) {
            $this->info('✅ No expired perks found.');
            return Command::SUCCESS;
        }

        $this->info("📋 Found {$count} expired perk(s) to archive:");
        
        foreach ($expiredPerks as $perk) {
            // Convert UTC to Philippines time for display
            $validUntilPh = Carbon::parse($perk->valid_until)->setTimezone(self::PH_TIMEZONE);
            $this->line("   - [{$perk->id}] {$perk->title} (Valid until: {$validUntilPh->toDateString()})");
        }

        if ($this->input->isInteractive()) {
            if (!$this->confirm('Do you want to archive these perks?')) {
                $this->info('❌ Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $archivedCount = 0;
        foreach ($expiredPerks as $perk) {
            $perk->update(['status' => 0]);
            $archivedCount++;
            
            Log::info("Perk auto-archived", [
                'perk_id' => $perk->id,
                'title' => $perk->title,
                'valid_until_utc' => $perk->valid_until->toDateString(),
                'archived_at_ph' => $now->toDateString()
            ]);
        }

        $this->info("✅ Successfully archived {$archivedCount} perk(s).");
        
        return Command::SUCCESS;
    }
}