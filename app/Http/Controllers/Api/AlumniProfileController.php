<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alumni;

class AlumniProfileController extends Controller
{
    public function show(Request $request, $id)
    {
        $alumni = Alumni::find($id);

        if (!$alumni) {
            return response()->json(['error' => 'Alumni not found'], 404);
        }

        $address = $alumni->addresses()
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'id' => $alumni->id,
            'first_name' => $alumni->first_name,
            'middle_name' => $alumni->middle_name,
            'last_name' => $alumni->last_name,
            'program' => $alumni->program,
            'alumni_photo' => $alumni->alumni_photo,
            'card_photo' => $alumni->card_photo,
            'year_graduated' => $alumni->year_graduated,
            'email' => $alumni->email,
            'sex' => $alumni->sex,
            'alumni_bio' => $alumni->alumni_bio,
            'alumni_type' => $alumni->alumni_type,
            'verification_status' => $alumni->verification_status,
            'student_id_number' => $alumni->student_id_number, // auto-decrypted by cast
            'date_of_birth' => $alumni->date_of_birth,         // auto-decrypted
            'phone_number' => $alumni->phone_number,           // auto-decrypted
            'address' => $address ? [
                'id' => $address->id,
                'address_type' => $address->address_type,
                'country' => $address->country,  
                'region' => $address->region,
                'province' => $address->province,
                'municipality' => $address->municipality,
                'barangay' => $address->barangay,
                'street' => $address->street,                  // auto-decrypted
                'zip_code' => $address->zip_code,
                'latitude' => $address->latitude,
                'longitude' => $address->longitude,
            ] : null,
        ]);
    }

    public function update(Request $request, $id)
    {
        $alumni = Alumni::find($id);
        if (!$alumni) {
            return response()->json(['error' => 'Alumni not found'], 404);
        }

        $data = $request->validate([
            'date_of_birth'  => 'nullable|string',
            'phone_number'   => 'nullable|string',
            'alumni_bio'     => 'nullable|string|max:500',
            'alumni_type'    => 'nullable|in:shs,college',
            'sex'            => 'nullable|in:male,female',
        ]);

        $alumni->fill($data);
        $alumni->save();   // 👈 Eloquent casts auto-encrypt DOB + phone

        return response()->json([
            'id' => $alumni->id,
            'date_of_birth' => $alumni->date_of_birth,
            'phone_number' => $alumni->phone_number,
            'alumni_bio' => $alumni->alumni_bio,
            'alumni_type' => $alumni->alumni_type,
            'sex' => $alumni->sex,
            'updated_at' => $alumni->updated_at,
        ]);
    }

    public function updateAddress(Request $request, $id)
    {
        $alumni = Alumni::find($id);
        if (!$alumni) {
            return response()->json(['error' => 'Alumni not found'], 404);
        }

        $data = $request->validate([
            'address_type' => 'required|string',
            'country' => 'required|string',
            'region'       => 'required|string',
            'province'     => 'required|string',
            'municipality' => 'required|string',
            'barangay'     => 'required|string',
            'street'       => 'nullable|string',
            'zip_code'     => 'nullable|string',
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
        ]);

        // Normalize coords to strings before casting so encryption is deterministic
        $data['latitude']  = (string) $data['latitude'];
        $data['longitude'] = (string) $data['longitude'];

        $address = $alumni->addresses()
            ->orderByDesc('created_at')
            ->first();

        if (!$address) {
            $address = new \App\Models\Address();
            $address->alumni_id = $alumni->id;
        }

        $address->fill($data);
        $address->save();

        return response()->json([
            'id' => $address->id,
            'address_type' => $address->address_type,
            'country' => $address->country,
            'region' => $address->region,
            'province' => $address->province,
            'municipality' => $address->municipality,
            'barangay' => $address->barangay,
            'street' => $address->street,
            'zip_code' => $address->zip_code,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
        ]);
    }

}