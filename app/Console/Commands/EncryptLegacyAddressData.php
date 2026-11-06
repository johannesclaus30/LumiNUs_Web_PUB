<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class EncryptLegacyAddressData extends Command
{
    protected $signature = 'encrypt:legacy-addresses {--dry-run}';
    protected $description = 'Encrypt plaintext street, latitude, longitude in addresses_base (idempotent)';

    public function handle()
    {
        $isDry = $this->option('dry-run');
        $streetCount = 0;
        $coordCount = 0;
        $skipped = 0;

        DB::table('addresses_base')
            ->orderBy('id')
            ->chunk(200, function ($rows) use (&$streetCount, &$coordCount, &$skipped, $isDry) {
                foreach ($rows as $row) {
                    $updates = [];

                    // --- street ---
                    $street = $row->street;
                    if ($street !== null && $street !== '' && !str_starts_with($street, 'eyJ')) {
                        $updates['street'] = Crypt::encryptString($street);
                        $updates['street_hash'] = hash('sha256', trim($street));
                        $streetCount++;
                    } elseif ($street === null || $street === '') {
                        // ensure hash matches empty/null street
                        if ($row->street_hash !== null) {
                            $updates['street_hash'] = null;
                        }
                    } else {
                        $skipped++;
                    }

                    // --- latitude ---
                    $lat = $row->latitude;
                    if ($lat !== null && $lat !== '' && !str_starts_with((string) $lat, 'eyJ')) {
                        $updates['latitude'] = Crypt::encryptString((string) $lat);
                        $coordCount++;
                    }

                    // --- longitude ---
                    $lng = $row->longitude;
                    if ($lng !== null && $lng !== '' && !str_starts_with((string) $lng, 'eyJ')) {
                        $updates['longitude'] = Crypt::encryptString((string) $lng);
                        $coordCount++;
                    }

                    if (empty($updates)) {
                        continue;
                    }

                    if ($isDry) {
                        $cols = implode(',', array_keys($updates));
                        $this->line("Would update address id={$row->id} ({$cols})");
                        continue;
                    }

                    DB::table('addresses_base')->where('id', $row->id)->update($updates);
                }
            });

        $this->info(
            "Encrypted: street={$streetCount}, coords={$coordCount}, " .
            "skipped_street_already_encrypted={$skipped}"
        );
        return 0;
    }
}