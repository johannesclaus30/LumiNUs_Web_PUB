<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MessageCryptoService
{
    private string $secretKey;

    public function __construct()
    {
        // ✅ Use config(), not env() — works with config:cache
        $this->secretKey = (string) config('services.messages.secret_key', '');
    }

    /**
     * Encrypt plaintext using CryptoJS-compatible format.
     * Returns "enc:" + base64("Salted__" + salt + ciphertext)
     * or the original plaintext if the key is missing.
     */
    public function encrypt(string $plaintext): string
    {
        if ($plaintext === '') {
            return '';
        }

        if ($this->secretKey === '') {
            Log::error('[CRYPTO] MESSAGE_SECRET_KEY is not configured; refusing to encrypt.');
            return $plaintext; // fail open — better than crashing the send
        }

        // Idempotency guard: don't double-encrypt
        if ($this->looksEncrypted($plaintext)) {
            return $plaintext;
        }

        try {
            $salt = random_bytes(8);

            [$key, $iv] = $this->deriveKeyAndIv($this->secretKey, $salt);

            $ciphertext = openssl_encrypt(
                $plaintext,
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($ciphertext === false) {
                Log::error('[CRYPTO] openssl_encrypt failed.');
                return $plaintext;
            }

            return 'enc:' . base64_encode('Salted__' . $salt . $ciphertext);

        } catch (\Throwable $e) {
            Log::error('[CRYPTO] Encrypt exception: ' . $e->getMessage());
            return $plaintext;
        }
    }

    /**
     * Decrypt CryptoJS-compatible payload. Falls back to the input
     * if not encrypted or if decryption fails.
     */
    public function decrypt(?string $content): string
    {
        if ($content === null || $content === '') {
            return '';
        }

        $input = (string) $content;

        if (!$this->looksEncrypted($input)) {
            return $input; // plaintext passthrough
        }

        $payload  = str_starts_with($input, 'enc:') ? substr($input, 4) : $input;
        $decoded  = base64_decode($payload, true);

        if ($decoded === false || substr($decoded, 0, 8) !== 'Salted__') {
            return $input;
        }

        $salt       = substr($decoded, 8, 8);
        $ciphertext = substr($decoded, 16);

        // Try new key first, then legacy keys for old rows
        foreach ($this->candidateKeys() as $password) {
            if ($password === '') {
                continue;
            }

            [$key, $iv] = $this->deriveKeyAndIv($password, $salt);

            $plaintext = openssl_decrypt(
                $ciphertext,
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($plaintext !== false) {
                return $plaintext;
            }
        }

        Log::error('[CRYPTO] Decryption failed for all key variants.');
        return $input;
    }

    // ───────────────────────────────────────────────────────────
    // Internals
    // ───────────────────────────────────────────────────────────

    private function looksEncrypted(string $input): bool
    {
        return str_starts_with($input, 'enc:')
            || str_starts_with($input, 'U2FsdGVkX1');
    }

    /**
     * EVP_BytesToKey (MD5) — matches CryptoJS's default passphrase mode.
     * Returns [key(32), iv(16)].
     */
    private function deriveKeyAndIv(string $password, string $salt): array
    {
        $derived = '';
        $block   = '';

        while (strlen($derived) < 48) {
            $block   = md5($block . $password . $salt, true);
            $derived .= $block;
        }

        return [
            substr($derived, 0, 32),
            substr($derived, 32, 16),
        ];
    }

    /**
     * Primary key first, then legacy keys — covers old messages
     * encrypted before a key rotation.
     */
    private function candidateKeys(): array
    {
        $new = $this->secretKey;
        $old = (string) config('services.messages.old_secret_key', '');

        return array_filter([
            $new,
            $old,
            str_replace('$', '\$', $old),
            str_replace('$2', '', $old),
            str_replace('$', '', $old),
            $old !== '' ? '$' . str_replace('$', '', $old) : '',
        ], fn ($k) => $k !== '');
    }
}

// This is the MessageCryptoService.php file