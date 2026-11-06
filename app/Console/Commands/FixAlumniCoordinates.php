<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FixAlumniCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'luminus:fix-coordinates';

    /**
     * The console command description.
     */
    protected $description = 'Re-geocode alumni addresses that have invalid or zeroed coordinates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting coordinate fix for LumiNUs alumni...');

        // Grab any address where the coordinates are 0
        $addresses = DB::table('addresses')
            ->where('latitude', 0)
            ->orWhere('longitude', 0)
            ->get();

        $this->info("Found {$addresses->count()} addresses to fix.");

        foreach ($addresses as $address) {
            $coords = $this->fetchCoordinates($address->barangay, $address->municipality, $address->province);

            if ($coords['latitude'] != 0) {
                DB::table('addresses')
                    ->where('id', $address->id)
                    ->update([
                        'latitude' => $coords['latitude'],
                        'longitude' => $coords['longitude']
                    ]);
                $this->line("✅ Updated Address ID {$address->id} in {$address->municipality}");
            } else {
                $this->error("❌ Could not find coordinates for Address ID {$address->id}");
            }
            
            // Nominatim strictly limits requests to 1 per second. 
            // DO NOT remove this sleep or they will block your server's IP!
            sleep(1);
        }

        $this->info('Finished updating coordinates!');
    }

    /**
     * The fallback geocoding logic
     */
    private function fetchCoordinates($barangay, $municipality, $province)
    {
        $baseUrl = 'https://nominatim.openstreetmap.org/search';
        
        $attempts = [
            "{$barangay}, {$municipality}, {$province}", 
            "{$municipality}, {$province}",              
            "{$province}"                                
        ];

        foreach ($attempts as $query) {
            $query = trim(trim($query, ', '));
            if (empty($query)) continue;

            $response = Http::withHeaders([
                'User-Agent' => 'LumiNUs-Alumni-Portal/1.0' 
            ])->get($baseUrl, [
                'q' => $query,
                'countrycodes' => 'ph', 
                'format' => 'json',
                'limit' => 1
            ]);

            $data = $response->json();

            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                return [
                    'latitude' => $data[0]['lat'],
                    'longitude' => $data[0]['lon']
                ];
            }
        }

        // Return 0s instead of nulls to respect the database constraints
        return [
            'latitude' => 0, 
            'longitude' => 0
        ];
    }
}