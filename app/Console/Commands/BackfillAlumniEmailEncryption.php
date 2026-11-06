<?php

namespace App\Console\Commands;

use App\Models\Alumni;
use Illuminate\Console\Command;

class BackfillAlumniEmailEncryption extends Command
{
    protected $signature = 'alumni:encrypt-email
        {--dry-run : Report changes without writing data}
        {--chunk=100 : Number of alumni records processed per batch}';

    protected $description = 'Encrypt alumni emails and populate their SHA-256 blind index';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));
        $seen = 0;
        $updated = 0;
        $skipped = 0;
        $overLimit = 0;

        Alumni::withTrashed()->chunkById($chunk, function ($alumni) use (
            $dryRun,
            &$seen,
            &$updated,
            &$skipped,
            &$overLimit
        ) {
            foreach ($alumni as $record) {
                $seen++;
                $email = trim((string) $record->email);

                if ($email === '') {
                    $skipped++;
                    continue;
                }

                $hash = hash('sha256', strtolower($email));
                $encryptedLength = strlen(\Illuminate\Support\Facades\Crypt::encryptString($email));
                if ($encryptedLength > 255) {
                    $overLimit++;
                    $this->warn("Alumni {$record->id} has an encrypted email exceeding 255 bytes; change email to text first.");
                    continue;
                }

                if ($record->email_hash === $hash && $this->isLaravelEncrypted($record->getRawOriginal('email'))) {
                    $skipped++;
                    continue;
                }

                $updated++;
                if (!$dryRun) {
                    $record->email = $email;
                    $record->email_hash = $hash;
                    $record->saveQuietly();
                }
            }
        });

        $this->info(sprintf(
            '%s %d record(s); %d updated, %d skipped.',
            $dryRun ? 'Would process' : 'Processed',
            $seen,
            $updated,
            $skipped
        ));

        if ($overLimit > 0) {
            $this->error("{$overLimit} record(s) require the policy-safe email type change before encryption.");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function isLaravelEncrypted(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        try {
            \Illuminate\Support\Facades\Crypt::decryptString($value);
            return true;
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return false;
        }
    }
}
