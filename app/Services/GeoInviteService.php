<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class GeoInviteService
{
    /**
     * Earth's mean radius in kilometers.
     */
    const EARTH_RADIUS_KM = 6371;

public function findNearbyAlumni(Event $event, float $radiusKm, bool $skipRecent): array
{
    $venue = $event->venue;
    $vLat  = (float) $venue->latitude;
    $vLng  = (float) $venue->longitude;

    $query = Alumni::active()->with('addresses');

    if ($skipRecent) {
        $query->where(function ($q) {
            $q->whereNull('last_invite_sent_at')
              ->orWhere('last_invite_sent_at', '<', now()->subDays(7));
        });
    }

    $all = $query->get();

    $nearby = [];
    $missing = 0;
    $withAddress = 0;

    foreach ($all as $alumnus) {
        $address = $alumnus->addresses->first();
        if (!$address) { $missing++; continue; }

        $aLat = $address->latitude;   // decrypts via cast
        $aLng = $address->longitude;  // decrypts via cast

        if ($aLat === null || $aLng === null) { $missing++; continue; }

        $aLat = (float) $aLat;
        $aLng = (float) $aLng;
        if ($aLat === 0.0 && $aLng === 0.0) { $missing++; continue; }

        $withAddress++;

        $dist = $this->haversine($vLat, $vLng, $aLat, $aLng);
        if ($dist > $radiusKm) continue;

        $nearby[] = [
            'id'           => $alumnus->id,
            'full_name'    => $alumnus->full_name,
            'email'        => $alumnus->email,
            'photo_url'    => $alumnus->alumni_photo_url,
            'program'      => $alumnus->program,
            'municipality' => $address->municipality,
            'province'     => $address->province,
            'latitude'     => $aLat,
            'longitude'    => $aLng,
            'distance_km'  => round($dist, 2),
        ];
    }

    usort($nearby, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);

    return [
        'radius_km'             => $radiusKm,
        'venue'                 => [
            'name'      => $venue->name,
            'address'   => $venue->address,
            'latitude'  => $vLat,
            'longitude' => $vLng,
        ],
        'alumni'                => $nearby,
        'total_with_address'    => $withAddress,
        'total_active_alumni'   => $all->count(),
        'missing_address_count' => $missing,
    ];
}

private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat/2)**2
       + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2)**2;
    return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

    /**
     * Resolve a usable photo URL for the map popup / list.
     */
    protected function resolveAlumniPhotoUrl($row): ?string
    {
        $path = trim((string) ($row->alumni_photo ?: $row->card_photo));

        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }
        if (str_starts_with($path, '/storage/')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/')) {
            return '/' . $path;
        }
        if (str_starts_with($path, '/')) {
            return $path;
        }

        $base = trim((string) config('filesystems.disks.s3.url'));
        if ($base !== '') {
            return rtrim($base, '/') . '/' . ltrim($path, '/');
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}