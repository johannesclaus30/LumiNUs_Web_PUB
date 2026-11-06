<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BrevoMailService
{
    public function sendEmail($to, $subject, $htmlContent)
    {
        // 1. Get the API key from .env
        $apiKey = env('BREVO_API_KEY');
        
        // 2. Make an HTTPS POST request to Brevo's API
        $response = Http::withHeaders([
            'api-key' => $apiKey,           // Your authentication
            'Content-Type' => 'application/json',  // Tell Brevo we're sending JSON
        ])->post('https://api.brevo.com/v3/smtp/email', [
            // 3. Who is sending the email?
            'sender' => [
                'name' => 'LumiNUs',
                'email' => 'luminus.nulipa@gmail.com',
            ],
            // 4. Who is receiving the email?
            'to' => [
                ['email' => $to],  // You can add multiple recipients
            ],
            // 5. Email subject
            'subject' => $subject,
            // 6. The actual email content (HTML)
            'htmlContent' => $htmlContent,
        ]);
        
        // 7. Return true if successful, false otherwise
        return $response->successful();
    }
}