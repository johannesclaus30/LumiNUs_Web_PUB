<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveExpiredEvents extends Command
{
    protected $signature = 'events:archive-expired';
    protected $description = 'Archive events that have passed their end date';

    const PH_TIMEZONE = 'Asia/Manila';

    public function handle()
    {
        $now = Carbon::now(self::PH_TIMEZONE);
        
        $this->info('🔍 Checking for expired events...');
        $this->info('Current Philippines time: ' . $now->toDateTimeString());
        
        // Find active events that have ended (using Philippines time)
        $expiredEvents = Event::where(function($query) {
                $query->where('status', 1)
                      ->orWhereNull('status');
            })
            ->where('end_date', '<', $now)
            ->get();

        $count = $expiredEvents->count();
        
        if ($count === 0) {
            $this->info('✅ No expired events found.');
            return Command::SUCCESS;
        }

        $this->info("📋 Found {$count} expired event(s) to archive:");
        
        foreach ($expiredEvents as $event) {
            // Convert UTC to Philippines time for display
            $endDatePh = Carbon::parse($event->end_date)->setTimezone(self::PH_TIMEZONE);
            $this->line("   - [{$event->id}] {$event->title} (Ended: {$endDatePh->toDateTimeString()})");
        }

        if ($this->input->isInteractive()) {
            if (!$this->confirm('Do you want to archive these events?')) {
                $this->info('❌ Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $archivedCount = 0;
        foreach ($expiredEvents as $event) {
            $event->update(['status' => 0]);
            $archivedCount++;
            
            Log::info("Event auto-archived", [
                'event_id' => $event->id,
                'title' => $event->title,
                'end_date_utc' => $event->end_date->toDateTimeString(),
                'archived_at_ph' => $now->toDateTimeString()
            ]);
        }

        $this->info("✅ Successfully archived {$archivedCount} event(s).");
        
        return Command::SUCCESS;
    }
}