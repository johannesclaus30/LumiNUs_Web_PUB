<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $table = 'addresses_base';

    protected $fillable = [
        'alumni_id',
        'address_type',
        'country',
        'region',
        'province',
        'municipality',
        'barangay',
        'street',
        'street_hash', // 👈 ADDED
        'zip_code',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'street'    => 'encrypted',
        'latitude'  => 'encrypted',
        'longitude' => 'encrypted',
    ];

    protected static function booted()
    {
        static::saving(function ($address) {
            if ($address->isDirty('street') || $address->street_hash === null) {
                $plain = $address->street;
                $address->street_hash = ($plain === null || $plain === '')
                    ? null
                    : hash('sha256', trim($plain));
            }
        });
    }

    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        if (in_array($key, ['street', 'latitude', 'longitude'])) {
            try {
                return parent::castAttribute($key, $value);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                \Log::warning('Undecryptable encrypted column on address', [
                    'address_id' => $this->id ?? null,
                    'column'     => $key,
                ]);
                return null;   // 👈 null, not 'NEEDS_MANUAL_FIX'
            }
        }

        return parent::castAttribute($key, $value);
    }

    /**
     * Get the alumni that owns this address.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }
}