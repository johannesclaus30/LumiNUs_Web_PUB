<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Alumni;
use App\Models\AlumniProgram;
use App\Exports\AlumniExport;
use App\Services\AlumniPdfExportService;
use App\Models\AdminPermission;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\RateLimiter;

use App\Services\BrevoMailService; 

use App\Mail\AlumniWelcomeMail;
use App\Mail\TestAlumniEmail;
use App\Mail\AdminInvitationMail;
use App\Mail\AdminPasswordResetMail;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{

    /**
     * Dummy bcrypt hash of the literal string "password".
     * Used only to equalize response timing on "user not found" login attempts.
     * Public value — safe to expose. Never assigned to any database row.
     */
    private const DUMMY_HASH = '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW';

    public function showLogin(Request $request)
    {
        $admin = $this->getAuthenticatedAdmin($request);
        
        if ($admin) {
            return redirect('/admin/dashboard');
        }
        
        return view('admin_login');
    }

public function authenticate(Request $request)
{
    // ============================================================
    // 1. VALIDATION — blocks malformed emails before they touch the DB
    // ============================================================
    $credentials = $request->validate([
        'admin_email' => ['required', 'string', 'email:rfc', 'max:255'],
        'password'    => ['required', 'string', 'max:255'],
    ], [
        'admin_email.email'    => 'Incorrect email or password.',
        'admin_email.required' => 'Incorrect email or password.',
        'password.required'    => 'Incorrect email or password.',
    ]);

    // ============================================================
    // 2. RATE LIMITING — per (IP + email) and per-IP global
    // ============================================================
    $throttleKey = 'login:' . $request->ip() . '|' . strtolower($credentials['admin_email']);
    $ipKey       = 'login-ip:' . $request->ip();
    $maxAttempts = 5;
    $lockoutSeconds = 300;   // 5 minutes

    // Per (IP + email) limit — 5 attempts per 5 minutes
    if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
        $seconds = RateLimiter::availableIn($throttleKey);
        \Log::warning('Login rate limit hit (email+IP)', [
            'ip'          => $request->ip(),
            'email'       => $credentials['admin_email'],
            'retry_after' => $seconds,
        ]);
        throw ValidationException::withMessages([
            'admin_email' => 'Too many login attempts. Please try again in '
                . ceil($seconds / 60) . ' minute(s).',
        ]);
    }

    // Per-IP global limit — 20 attempts per 5 minutes across all emails
    if (RateLimiter::tooManyAttempts($ipKey, 20)) {
        $seconds = RateLimiter::availableIn($ipKey);
        \Log::warning('Login rate limit hit (IP global)', [
            'ip'          => $request->ip(),
            'retry_after' => $seconds,
        ]);
        throw ValidationException::withMessages([
            'admin_email' => 'Too many login attempts from your network. Please wait a few minutes.',
        ]);
    }

    // ============================================================
    // 3. FETCH ADMIN — bound parameter via Eloquent (SQL-injection safe)
    // ============================================================
    $admin = Admin::matchingEmail($credentials['admin_email'])->first();

    // ============================================================
    // 4. USER NOT FOUND — constant-time + rate limit + generic error
    // ============================================================
    if (! $admin) {
        // Dummy bcrypt check to equalize timing with the real password-check path
        // This prevents attackers from enumerating valid emails via response time
        Hash::check($credentials['password'], self::DUMMY_HASH);

        RateLimiter::hit($throttleKey, $lockoutSeconds);
        RateLimiter::hit($ipKey, $lockoutSeconds);

        throw ValidationException::withMessages([
            'admin_email' => 'Incorrect email or password.',
        ]);
    }

    // ============================================================
    // 5. PASSWORD CHECK — constant-time comparison
    // ============================================================
    $storedPassword   = (string) ($admin->admin_password_hash ?? '');

    $passwordInfo = password_get_info($storedPassword);
    $isHashedPassword = ($passwordInfo['algoName'] ?? 'unknown') !== 'unknown';

    $passwordMatches = $isHashedPassword
        ? Hash::check($credentials['password'], $storedPassword)
        : hash_equals($storedPassword, $credentials['password']);

    if (! $passwordMatches) {
        RateLimiter::hit($throttleKey, $lockoutSeconds);
        RateLimiter::hit($ipKey, $lockoutSeconds);

        throw ValidationException::withMessages([
            'admin_email' => 'Incorrect email or password.',
        ]);
    }

    // ============================================================
    // 6. SUCCESS — clear rate limiters
    // ============================================================
    RateLimiter::clear($throttleKey);
    RateLimiter::clear($ipKey);

    // ============================================================
    // 7. RESTRICTED ACCOUNT CHECK
    // ============================================================
    if (($admin->account_status ?? 1) == 0) {
        return redirect()
            ->route('admin.restricted')
            ->with('restricted_email', $admin->admin_email);
    }

    // ============================================================
    // 8. SESSION — regenerate, store admin data, link session row
    // ============================================================
    $request->session()->regenerate();
    $request->session()->put([
        'admin_id'    => $admin->id,
        'admin_email' => $admin->admin_email,
        'admin_name'  => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
        'admin_role'  => $admin->admin_role,
    ]);

    // Link the admin_id to the sessions table's user_id column
    \DB::table('sessions')
        ->where('id', $request->session()->getId())
        ->update(['user_id' => $admin->id]);

    return redirect('/admin/dashboard');
}

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch ONLY active alumni (not archived, not restricted)
        $alumni = Alumni::active()
        ->with(['programs'])
        ->latest('created_at')
        ->get();

        // --- DIRECTORY STATS ---
        
        // 1. Total Alumni Count (active only)
        $totalAlumni = Alumni::active()->count();

        // 2. Recent Graduates (active alumni who graduated in the current year)
        $recentGraduates = Alumni::active()
            ->whereYear('year_graduated', now()->year)
            ->count();

        // 3. Unique Programs Offered (active alumni)
        $uniquePrograms = Alumni::active()
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->distinct('program')
            ->count('program');

        // 4. Alumni With Email Addresses (active alumni)
        $withEmails = Alumni::active()
            ->whereNotNull('email_hash')
            ->where('email_hash', '!=', '')
            ->count();

        // Pass everything to the view
        return view('admin_directory', compact(
            'alumni', 
            'totalAlumni', 
            'recentGraduates', 
            'uniquePrograms', 
            'withEmails'
        ));
    }

    public function settings(Request $request)
    {
        $currentAdmin = $this->getAuthenticatedAdmin($request);

        // 🔒 Only Coordinators can access Administration sections
        $section = $request->query('section', 'account');
        if (in_array($section, ['roles', 'add-admin'])) {
            if (!$currentAdmin || $currentAdmin->admin_role !== 'Coordinator') {
                return redirect()
                    ->route('admin.settings', ['section' => 'account'])
                    ->with('error', 'You do not have permission to access that section.');
            }
        }

        $currentAdminPhotoUrl = $this->resolveAdminPhotoUrl($currentAdmin?->photo);
        $admins = Admin::query()->latest('created_at')->get();

        return view('admin_settings', compact('admins', 'currentAdmin', 'currentAdminPhotoUrl'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created admin and send invitation email.
     */
    public function store(Request $request)
    {
        $this->requireCoordinator($request);
        $validated = $request->validate([
            'admin_first_name' => ['required', 'string', 'max:255'],
            'admin_middle_name' => ['nullable', 'string', 'max:255'],
            'admin_last_name' => ['required', 'string', 'max:255'],

            'admin_email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (Admin::matchingEmail($value)->exists()) {
                        $fail('This email address is already registered.');
                    }
                },
            ],

            'phone_number' => ['required', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'admin_role' => ['required', Rule::in(['Executive Director', 'Academic Director', 'Coordinator', 'Assistant Coordinator'])],
        ]);

        // Generate a secure random temporary password
        $temporaryPassword = $this->generateTemporaryPassword(10);

        $admin = Admin::create([
            'admin_first_name' => $validated['admin_first_name'],
            'admin_middle_name' => $validated['admin_middle_name'] ?? null,
            'admin_last_name' => $validated['admin_last_name'],
            'admin_email' => $validated['admin_email'],
            'phone_number' => $validated['phone_number'],
            'photo' => null,
            'admin_password_hash' => Hash::make($temporaryPassword),
            'admin_role' => $validated['admin_role'],
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $admin->photo = $this->storeAdminPhoto($request, 'photo', $admin, null);
            $admin->save();
        }

        $admin->setupDefaultPermissions();

        // Send invitation email using Brevo API
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_invitation', [
                'admin' => $admin,
                'temporaryPassword' => $temporaryPassword
            ])->render();
            
            $service->sendEmail(
                $admin->admin_email,
                'Welcome to LumiNUs - Your Admin Account',
                $htmlContent
            );
            
            return redirect()
                ->route('admin.settings', ['section' => 'add-admin'])
                ->with('status', 'Admin account created successfully! An invitation email has been sent to ' . $admin->admin_email . '.')
                ->with('temporary_password', $temporaryPassword);
                
        } catch (\Throwable $e) {
            \Log::error('Failed to send invitation email to ' . $admin->admin_email . ': ' . $e->getMessage());
            
            // Still return success but with a warning
            return redirect()
                ->route('admin.settings', ['section' => 'add-admin'])
                ->with('status', 'Admin account created, but the invitation email could not be sent. Please provide these credentials manually.')
                ->with('temporary_password', $temporaryPassword);
        }
    }

    public function updateProfile(Request $request)
    {
        $admin = $this->getAuthenticatedAdmin($request);

        if (! $admin) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_first_name' => ['required', 'string', 'max:255'],
            'admin_middle_name' => ['nullable', 'string', 'max:255'],
            'admin_last_name'  => ['required', 'string', 'max:255'],

            'admin_email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($admin) {
                    if (Admin::matchingEmail($value)
                        ->where('id', '!=', $admin->id)
                        ->exists()) {
                        $fail('This email address is already registered.');
                    }
                },
            ],

            'phone_number'     => ['required', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'remove_photo' => ['nullable', 'string'],
        ]);

        // Handle photo removal (priority over new upload)
        if ($request->has('remove_photo') && $request->input('remove_photo') == '1') {
            $this->deleteAdminPhoto($admin->photo);
            $admin->photo = null;
        } elseif ($request->hasFile('photo')) {
            $admin->photo = $this->storeAdminPhoto($request, 'photo', $admin, $admin->photo);
        }

        $oldEmail = $admin->admin_email;

        // Update other fields
        $admin->admin_first_name = $validated['admin_first_name'];
        $admin->admin_middle_name = $validated['admin_middle_name'] ?? null;
        $admin->admin_last_name  = $validated['admin_last_name'];
        $admin->admin_email      = $validated['admin_email'];
        $admin->phone_number     = $validated['phone_number'];
        $admin->save();

        // ✅ Regenerate session FIRST if email changed
        if ($oldEmail !== $admin->admin_email) {
            $request->session()->regenerate();
        }

        // ✅ THEN put the new data (so it persists in the new session)
        $request->session()->put([
            'admin_email' => $admin->admin_email,
            'admin_name'  => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
        ]);

        // ✅ Also re-attach the user_id to the new session row
        \DB::table('sessions')
            ->where('id', $request->session()->getId())
            ->update(['user_id' => $admin->id]);

        return redirect()
            ->route('admin.settings', ['section' => 'account'])
            ->with('status', 'Account information updated successfully.');
    }

    public function storeAlumni(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'student_id_number' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],   // ← no Rule::unique here
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'max:50'],
            'year_graduated' => ['required', 'date'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:255'],
            'card_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'alumni_type' => ['required', Rule::in(['shs', 'college'])],
        ]);

        // ── Student ID uniqueness (encrypted column → use hash)
        $idHash = hash('sha256', trim($validated['student_id_number']));
        if (Alumni::where('student_id_hash', $idHash)->exists()) {
            throw ValidationException::withMessages([
                'student_id_number' => 'This Student ID number is already registered to another alumni.',
            ]);
        }

        // ── Email uniqueness (encrypted column → use hash)  ← ADD THIS
        $emailHash = hash('sha256', strtolower(trim($validated['email'])));
        if (Alumni::where('email_hash', $emailHash)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already registered to another alumni.',
            ]);
        }

        // Reject duplicate mobile numbers before creating the alumni account.
        $phoneNumber = preg_replace('/\D+/', '', $validated['phone_number'] ?? '');

        if ($phoneNumber !== '') {
            foreach (Alumni::cursor() as $existingAlumnus) {
                $existingPhone = preg_replace(
                    '/\D+/',
                    '',
                    $existingAlumnus->phone_number ?? ''
                );

                if ($existingPhone === $phoneNumber) {
                    throw ValidationException::withMessages([
                        'phone_number' => 'This mobile number is already registered to another alumni.',
                    ]);
                }
            }
        }

        // Handle card photo upload to S3
        $cardPhotoPath = null;
        if ($request->hasFile('card_photo')) {
            $storedPath = $request->file('card_photo')->store('card_photo', 's3');
            if (! $storedPath) {
                throw ValidationException::withMessages([
                    'card_photo' => 'The card photo could not be uploaded to Supabase.',
                ]);
            }
            $cardPhotoPath = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($storedPath, '/');
        }

        // Generate a clean, mobile-friendly temporary password
        $temporaryPassword = $this->generateTemporaryPassword(10);

        $alumnus = \DB::transaction(function () use ($validated, $cardPhotoPath, $temporaryPassword) {
            $alumnus = Alumni::create([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'sex' => $validated['sex'] ?? null,
                'year_graduated' => $validated['year_graduated'],
                'student_id_number' => $validated['student_id_number'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'password_hash' => Hash::make($temporaryPassword),
                'verification_status' => 'verified',
                'needs_password_change' => true,
                'program' => $validated['program'] ?? null,
                'card_photo' => $cardPhotoPath,
                'alumni_type' => $validated['alumni_type'],
            ]);

            AlumniProgram::create([
                'alumni_id'         => $alumnus->id,
                'program_type'      => $validated['alumni_type'],
                'program_name'      => $validated['program'] ?? null,
                'student_id_number' => $validated['student_id_number'],
                'year_graduated'    => $validated['year_graduated'],
                'enrollment_status' => 'graduated',
                'is_primary'        => true,
            ]);

            return $alumnus;
        });

        // 🔑 CRITICAL: Create the Supabase Auth user
        $authCreated = $this->createSupabaseAuthUser(
            $alumnus->email,
            $temporaryPassword,
            $alumnus->first_name,
            $alumnus->last_name
        );

        if (!$authCreated) {
            \Log::error('Failed to create Supabase auth user for: ' . $alumnus->email);
        }

        // Send welcome email with the temporary password
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.welcome-alumni', [
                'alumnus' => $alumnus,
                'temporaryPassword' => $temporaryPassword,
            ])->render();
            
            $service->sendEmail(
                $alumnus->email,
                'Welcome to LumiNUs',
                $htmlContent
            );
        } catch (\Throwable $e) {
            \Log::error('Failed to send welcome email to ' . $alumnus->email . ': ' . $e->getMessage());
        }

        // If this is an AJAX request (from bulk import), return JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alumni account created successfully.',
                'alumnus' => [
                    'id' => $alumnus->id,
                    'name' => trim($alumnus->first_name . ' ' . $alumnus->last_name),
                    'email' => $alumnus->email,
                ]
            ], 201);
        }

        // Regular form submission redirect
        return redirect()
            ->route('admin.directory')
            ->with('status', 'Alumni account created successfully. Temporary password has been emailed.');
    }

    /**
     * Display the specified resource.
     */
  public function show(string $id)
    {
        $alumnus = Alumni::with([
            'addresses',
            'employments',
            'skills',
            'tracerResponses',
            'eventRegistrations.event',
            'followers',
            'following',
            'posts.images',
            'posts.comments',
            'posts.reactions'
        ])->findOrFail($id);
        
        // Process posts to add full image URLs
        $alumnus->posts->each(function($post) {
            $post->images->each(function($image) {
                $image->full_url = $this->getSupabaseStorageUrl($image->image_path);
            });
        });
        
        return view('directory.show', compact('alumnus'));
    }

    /**
     * Send a test email to the alumni.
     */
    public function sendTestEmail($id)
    {
        $alumnus = Alumni::findOrFail($id);

        if (empty($alumnus->email) || !filter_var($alumnus->email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'This alumni does not have a valid email address.');
        }

        try {
            // 1. Create the service
            $service = new BrevoMailService();
            
            // 2. Render your email template to HTML
            $htmlContent = view('emails.test-alumni', [
                'alumnus' => $alumnus
            ])->render();
            
            // 3. Send the email using Brevo's API
            $service->sendEmail(
                $alumnus->email,
                'Test Email from LumiNUs',
                $htmlContent
            );
            
            return redirect()->back()->with('success', "Test email successfully sent.");
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Mail error: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $alumnus = Alumni::findOrFail($id);

        try {
            // 1. Delete the alumni's photo from S3 to save storage space
            if ($alumnus->card_photo) {
                $this->deleteAlumniPhoto($alumnus->card_photo);
            }

            // 2. Delete the alumni record from the database
            $alumnus->delete();

            // 3. Return a JSON response since the frontend uses AJAX (fetch)
            return response()->json([
                'success' => true,
                'message' => 'Alumni account deleted successfully.'
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint errors (e.g., if they have tracer forms or messages)
            // Error code 23503 is standard for foreign key violations in PostgreSQL
            if ($e->getCode() === '23503' || str_contains($e->getMessage(), 'foreign key constraint')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this alumni because they have existing records (e.g., Tracer Forms). Please archive or remove those first.'
                ], 409); // 409 Conflict
            }

            // Fallback for other database errors
            \Log::error('Failed to delete alumni ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'A database error occurred while deleting the account.'
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('Failed to delete alumni ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    protected function getAuthenticatedAdmin(Request $request): ?Admin
    {
        $adminId = $request->session()->get('admin_id');
        if ($adminId && is_numeric($adminId)) {
                $admin = Admin::query()
                    ->where('id', (int) $adminId)
                    ->where('account_status', 1) 
                    ->first();
                if ($admin) {
                    return $admin;
                }
            }

        $adminEmail = $request->session()->get('admin_email');

        if ($adminEmail && is_string($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return Admin::matchingEmail($adminEmail)
                ->where('account_status', 1)
                ->first();
        }

        return null;
    }

    protected function resolveAdminPhotoUrl(?string $photoPath): ?string
    {
        $photoPath = trim((string) $photoPath);

        if ($photoPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $photoPath)) {
            return $photoPath;
        }

        if (str_starts_with($photoPath, '/storage/')) {
            return $photoPath;
        }

        if (str_starts_with($photoPath, 'storage/')) {
            return '/' . $photoPath;
        }

        if (str_starts_with($photoPath, '/')) {
            return $photoPath;
        }

        return Storage::disk('supabase_admin')->url($photoPath);
    }

    protected function storeAdminPhoto(Request $request, string $fieldName, Admin $admin, ?string $existingPhoto = null): string
    {
        $photo = $request->file($fieldName);

        if (! $photo) {
            return (string) $existingPhoto;
        }

        $this->deleteAdminPhoto($existingPhoto);

        $extension = strtolower($photo->getClientOriginalExtension() ?: $photo->extension() ?: 'jpg');
        $fileName = 'admin_' . $admin->id . '.' . $extension;
        Storage::disk('supabase_admin')->putFileAs('admin_photos', $photo, $fileName, 'public');

        return 'admin_photos/' . $fileName;
    }

    protected function deleteAdminPhoto(?string $photoPath): void
    {
        $normalizedPath = $this->normalizeAdminPhotoPath($photoPath);

        if (! $normalizedPath) {
            return;
        }

        $disk = Storage::disk('supabase_admin');

        if ($disk->exists($normalizedPath)) {
            $disk->delete($normalizedPath);
        }
    }

    protected function normalizeAdminPhotoPath(?string $photoPath): ?string
    {
        $photoPath = trim((string) $photoPath);

        if ($photoPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $photoPath)) {
            $parsedPath = parse_url($photoPath, PHP_URL_PATH) ?: '';
            $parsedPath = ltrim($parsedPath, '/');

            if (str_contains($parsedPath, 'admin_photos/')) {
                return substr($parsedPath, strpos($parsedPath, 'admin_photos/'));
            }

            if (str_contains($parsedPath, 'luminus_assets/')) {
                return substr($parsedPath, strpos($parsedPath, 'luminus_assets/') + strlen('luminus_assets/'));
            }

            return $parsedPath ?: null;
        }

        if (str_starts_with($photoPath, '/')) {
            $photoPath = ltrim($photoPath, '/');
        }

        if (str_starts_with($photoPath, 'luminus_assets/')) {
            return substr($photoPath, strlen('luminus_assets/'));
        }

        return $photoPath;
    }

    /**
     * Show the form for editing the specified alumni.
     */
    public function editAlumni(string $id)
    {
        $alumnus = Alumni::findOrFail($id);
        
        return view('directory.edit', compact('alumnus'));
    }

    /**
     * Update the specified alumni in storage.
     */
    public function updateAlumni(Request $request, string $id)
    {
        $alumnus = Alumni::findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'student_id_number' => ['required', 'string', 'max:255'],            
            'email' => ['required', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'max:50'],
            'year_graduated' => ['required', 'date'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:255'],
            'card_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'remove_photo' => ['nullable', 'boolean'],
            'alumni_type' => ['required', Rule::in(['shs', 'college'])], 
        ]);

        // Add manual check for duplicate Student ID, excluding current alumnus
        $idHash = hash('sha256', trim($validated['student_id_number']));
        $idExists = \App\Models\Alumni::where('student_id_hash', $idHash)
            ->where('id', '!=', $alumnus->id)
            ->exists();

        if ($idExists) {
            throw ValidationException::withMessages([
                'student_id_number' => 'This Student ID number is already registered to another alumni.',
            ]);
        }

        $emailHash = hash('sha256', strtolower(trim($validated['email'])));
        $emailTaken = Alumni::where('email_hash', $emailHash)
            ->where('id', '!=', $alumnus->id)
            ->exists();

        if ($emailTaken) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already registered to another alumni.',
            ]);
        }

        // Check duplicate mobile numbers, excluding the current alumnus.
        $phoneNumber = preg_replace('/\D+/', '', $validated['phone_number'] ?? '');

        if ($phoneNumber !== '') {
            $otherAlumni = Alumni::where('id', '!=', $alumnus->id)->cursor();

            foreach ($otherAlumni as $existingAlumnus) {
                $existingPhone = preg_replace(
                    '/\D+/',
                    '',
                    $existingAlumnus->phone_number ?? ''
                );

                if ($existingPhone === $phoneNumber) {
                    throw ValidationException::withMessages([
                        'phone_number' => 'This mobile number is already registered to another alumni.',
                    ]);
                }
            }
        }

        // Handle photo removal
        if ($request->has('remove_photo') && $request->input('remove_photo')) {
            if ($alumnus->card_photo) {
                $this->deleteAlumniPhoto($alumnus->card_photo);
                $alumnus->card_photo = null;
            }
        } elseif ($request->hasFile('card_photo')) {
            // Delete old photo if exists
            if ($alumnus->card_photo) {
                $this->deleteAlumniPhoto($alumnus->card_photo);
            }
            
            // Upload new photo
            $storedPath = $request->file('card_photo')->store('card_photo', 's3');
            
            if ($storedPath) {
                $alumnus->card_photo = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($storedPath, '/');
            }
        }

        // Update alumni information
        $alumnus->forceFill([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'student_id_number' => $validated['student_id_number'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'year_graduated' => $validated['year_graduated'],
            'phone_number' => $validated['phone_number'] ?? null,
            'program' => $validated['program'] ?? null,
        ])->save();

        $this->recomputeAlumniType($alumnus->fresh(['programs']));

        // ── Sync the primary program row so the two stay consistent ──
        $primary = $alumnus->primaryProgram;

        if ($primary) {
            // If the admin tried to flip alumni_type (e.g. promoted SHS→College back to SHS),
            // we log it but do NOT touch the primary program's program_type.
            // Business rule: type changes go through promoteToCollege, not the edit form.
            if ($primary->program_type !== $validated['alumni_type']) {
                \Log::warning('Blocked alumni_type change via edit form', [
                    'alumni_id'      => $alumnus->id,
                    'primary_type'   => $primary->program_type,
                    'requested_type' => $validated['alumni_type'],
                    'performed_by'   => optional($this->getAuthenticatedAdmin($request))->id,
                ]);
            }

            $primary->update([
                'program_name'      => $validated['program']         ?? $primary->program_name,
                'student_id_number' => $validated['student_id_number'] ?? $primary->student_id_number,
                'student_id_hash'   => hash('sha256', trim($validated['student_id_number'])), // 👈 ADDED
                'year_graduated'    => $validated['year_graduated']   ?? $primary->year_graduated,
            ]);
        }

        return redirect()
            ->route('admin.directory')
            ->with('status', 'Alumni information updated successfully.');
    }

    /**
     * Add a College program enrollment for an SHS alumnus.
     *
     * IMPORTANT: this does NOT flip the alumnus to "college" alumni_type.
     * The alumnus remains SHS until the College program is marked 'graduated'.
     * The DB trigger handles the alumni_type flip automatically.
     */
    public function addCollegeProgram(Request $request, $id)
    {
        $validated = $request->validate([
            'program'           => ['required', 'string', 'max:255'],
            'student_id_number' => ['required', 'string', 'max:255'],
            'year_graduated'    => ['required', 'date'],
            'enrollment_status' => ['nullable', 'string', 'in:enrolled,graduated,dropped'],
        ]);

        $alumnus = Alumni::with(['programs'])->findOrFail($id);
        $currentAdmin = $this->getAuthenticatedAdmin($request);

        $enrollmentStatus = $validated['enrollment_status'] ?? 'enrolled';

        // ── Guard 1: student_id uniqueness
        $newHash = hash('sha256', trim($validated['student_id_number']));
        $idTaken = Alumni::where('student_id_hash', $newHash)
            ->where('id', '!=', $alumnus->id)
            ->exists();

        if ($idTaken) {
            return response()->json([
                'success' => false,
                'message' => 'That student ID number is already used by another alumnus.',
            ], 422);
        }

        // ── Guard 2: must currently be SHS (per current alumni_type)
        if ($alumnus->alumni_type !== 'shs') {
            return response()->json([
                'success' => false,
                'message' => 'Only SHS alumni can have a College program added.',
            ], 422);
        }

        // ── Guard 3: prevent duplicate College rows
        if ($alumnus->programs->contains('program_type', 'college')) {
            return response()->json([
                'success' => false,
                'message' => 'This alumnus already has a College program record.',
            ], 422);
        }

        try {
            \DB::transaction(function () use ($alumnus, $validated, $enrollmentStatus) {
                // Insert the College row as a SECONDARY program (is_primary = false)
                // Unless enrollment_status is 'graduated', in which case we make
                // it primary so the trigger fires.
                $isPrimaryNow = ($enrollmentStatus === 'graduated');

                AlumniProgram::create([
                    'alumni_id'         => $alumnus->id,
                    'program_type'      => 'college',
                    'program_name'      => $validated['program'],
                    'student_id_number' => $validated['student_id_number'],
                    'year_graduated'    => $validated['year_graduated'],
                    'enrollment_status' => $enrollmentStatus,
                    'is_primary'        => $isPrimaryNow,
                ]);

                // If graduated, flip SHS row to non-primary
                if ($isPrimaryNow) {
                    AlumniProgram::where('alumni_id', $alumnus->id)
                        ->where('program_type', 'shs')
                        ->update(['is_primary' => false]);
                }

                // Sync cached columns on alumnis IF graduated (new primary)
                // Otherwise leave them alone (SHS stays the identity)
                if ($isPrimaryNow) {
                    $alumnus->update([
                        'program'                => $validated['program'],
                        'student_id_number'      => $validated['student_id_number'],
                        'year_graduated'         => $validated['year_graduated'],
                        'promoted_to_college_at' => now(),
                    ]);
                }
                // If just enrolled/dropped, we DON'T touch alumnis — the trigger
                // will keep alumni_type = 'shs' since the College row isn't graduated.
            });

            $this->recomputeAlumniType($alumnus->fresh(['programs']));

            \Log::info('College program added', [
                'alumni_id'    => $alumnus->id,
                'email'        => $alumnus->email,
                'new_program'  => $validated['program'],
                'status'       => $enrollmentStatus,
                'performed_by' => $currentAdmin->id ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => $alumnus->first_name . ' now has a College program record (' . $enrollmentStatus . ').',
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Add College program failed (DB): ' . $e->getMessage(), [
                'alumni_id' => $id,
                'sql_state' => $e->errorInfo[0] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add College program. Please try again.',
            ], 500);
        } catch (\Throwable $e) {
            \Log::error('Add College program failed: ' . $e->getMessage(), [
                'alumni_id' => $id,
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Keep old method as alias so existing code paths don't break.
     */
    public function promoteToCollege(Request $request, $id)
    {
        return $this->addCollegeProgram($request, $id);
    }

    /**
     * Update the enrollment status of a specific program row.
     */
    public function updateProgramEnrollment(Request $request, $alumniId, $programId)
    {
        $validated = $request->validate([
            'enrollment_status' => ['required', 'string', 'in:enrolled,graduated,dropped'],
        ]);

        $alumnus = Alumni::findOrFail($alumniId);
        $program = AlumniProgram::where('alumni_id', $alumnus->id)->findOrFail($programId);
        $currentAdmin = $this->getAuthenticatedAdmin($request);

        try {
            \DB::transaction(function () use ($alumnus, $program, $validated) {
                $newStatus = $validated['enrollment_status'];

                // Update the row
                $program->update(['enrollment_status' => $newStatus]);

                // If we're flipping College to 'graduated', we need to
                // make it primary and SHS non-primary.
                if ($program->program_type === 'college' && $newStatus === 'graduated') {
                    // Make college primary
                    AlumniProgram::where('alumni_id', $alumnus->id)
                        ->where('id', '!=', $program->id)
                        ->update(['is_primary' => false]);

                    $program->update(['is_primary' => true]);

                    // Sync cached fields on alumnis
                    $alumnus->update([
                        'program'                => $program->program_name,
                        'student_id_number'      => $program->student_id_number,
                        'year_graduated'         => $program->year_graduated,
                        'promoted_to_college_at' => now(),
                    ]);
                }

                // If College was 'graduated' but is being flipped to dropped/enrolled,
                // make SHS primary again.
                if ($program->program_type === 'college' && $newStatus !== 'graduated') {
                    $program->update(['is_primary' => false]);

                    AlumniProgram::where('alumni_id', $alumnus->id)
                        ->where('program_type', 'shs')
                        ->update(['is_primary' => true]);
                }
            });

            $this->recomputeAlumniType($alumnus->fresh(['programs']));

            \Log::info('Program enrollment status updated', [
                'alumni_id'  => $alumnus->id,
                'program_id' => $program->id,
                'new_status' => $validated['enrollment_status'],
                'by'         => $currentAdmin->id ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enrollment status updated successfully.',
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Update enrollment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send message to alumni (placeholder)
     */
    public function messageAlumni(string $id)
    {
        $alumnus = Alumni::findOrFail($id);
        
        // For now, just redirect back with a message
        // You can implement actual messaging functionality here
        return redirect()
            ->route('admin.directory')
            ->with('status', 'Message feature coming soon for ' . $alumnus->first_name . ' ' . $alumnus->last_name);
    }

    protected function deleteAlumniPhoto(?string $photoPath): void
    {
        if (!$photoPath) {
            return;
        }
        
        // Extract the path from the full URL if it's a URL
        if (preg_match('/^https?:\/\//i', $photoPath)) {
            $parsedPath = parse_url($photoPath, PHP_URL_PATH) ?: '';
            $photoPath = ltrim($parsedPath, '/');
        }
        
        // Delete from S3
        if (Storage::disk('s3')->exists($photoPath)) {
            Storage::disk('s3')->delete($photoPath);
        }
    }

    /**
     * Generate a clean, mobile-app-friendly temporary password
     * Meets all mobile app password requirements:
     * - Minimum 6 characters
     * - At least 1 uppercase letter
     * - At least 1 number
     * - At least 1 special character
     * 
     * Uses only unambiguous characters for readability
     */
    protected function generateTemporaryPassword($length = 10): string
    {
        // Character sets (avoiding ambiguous characters: 0, O, o, 1, I, l)
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghijkmnopqrstuvwxyz';
        $numbers = '23456789';
        $special = '!@#$%^&*?';
        
        // Ensure we have at least one from each required set
        $password = [
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $special[random_int(0, strlen($special) - 1)],
        ];
        
        // All characters combined for remaining positions
        $all = $uppercase . $lowercase . $numbers . $special;
        $remainingLength = max(0, $length - 4);
        
        for ($i = 0; $i < $remainingLength; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }
        
        // Shuffle to randomize order
        shuffle($password);
        
        return implode('', $password);
    }

    /**
     * Create a new Supabase Auth user
     */
    protected function createSupabaseAuthUser($email, $password, $firstName = '', $lastName = '')
    {
        try {
            $supabaseKey = config('services.supabase.service_key');
            $supabaseUrl = config('services.supabase.url');
            
            if (!$supabaseKey || !$supabaseUrl) {
                \Log::error('Supabase credentials not configured');
                return false;
            }
            
            $response = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->post("{$supabaseUrl}/auth/v1/admin/users", [
                'email' => $email,
                'password' => $password,
                'email_confirm' => true,
                'user_metadata' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]
            ]);
            
            if ($response->failed()) {
                $errorData = $response->json();
                $errorMsg = $errorData['msg'] ?? $errorData['error_description'] ?? json_encode($errorData);
                
                // If user already exists, try to update their password instead
                if (strpos(strtolower($errorMsg), 'already exists') !== false || 
                    strpos(strtolower($errorMsg), 'duplicate') !== false ||
                    isset($errorData['error_code']) && $errorData['error_code'] === 'email_exists') {
                    \Log::info('User already exists in Supabase, attempting to update password: ' . $email);
                    return $this->updateSupabaseAuthPassword($email, $password);
                }
                
                \Log::error('Failed to create Supabase user: ' . $response->body());
                return false;
            }
            
            \Log::info('Supabase auth user created for: ' . $email);
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error creating Supabase auth user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update Supabase Auth user password
     */
    protected function updateSupabaseAuthPassword($email, $newPassword)
    {
        try {
            $supabaseKey = config('services.supabase.service_key');
            $supabaseUrl = config('services.supabase.url');
            
            if (!$supabaseKey || !$supabaseUrl) {
                \Log::error('Supabase credentials not configured');
                return false;
            }

            // FIRST: Try to get the user by email from Supabase Auth
            $userResponse = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->get("{$supabaseUrl}/auth/v1/admin/users", [
                'email' => $email
            ]);
            
            if ($userResponse->failed()) {
                \Log::error('Failed to fetch Supabase user: ' . $userResponse->body());
                return false;
            }
            
            $responseData = $userResponse->json();
            
            // 🔥 FIXED: The users array might be under 'users' key or directly in response
            $users = isset($responseData['users']) ? $responseData['users'] : $responseData;
            
            // If it's not an array or empty, try to find the user differently
            if (empty($users) || !is_array($users)) {
                \Log::warning('No users found in response for email: ' . $email);
                \Log::debug('Response data: ' . json_encode($responseData));
                
                // Try to create the user since they might not exist in Supabase Auth
                return $this->createSupabaseAuthUser($email, $newPassword);
            }
            
            // Find the user with matching email (case-insensitive)
            $foundUser = null;
            foreach ($users as $user) {
                if (isset($user['email']) && strtolower($user['email']) === strtolower($email)) {
                    $foundUser = $user;
                    break;
                }
            }
            
            if (!$foundUser || empty($foundUser['id'])) {
                \Log::warning('Supabase user not found for email: ' . $email . ' - attempting to create');
                return $this->createSupabaseAuthUser($email, $newPassword);
            }
            
            $userId = $foundUser['id'];
            
            // Update the user's password in Supabase Auth
            $updateResponse = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->put("{$supabaseUrl}/auth/v1/admin/users/{$userId}", [
                'password' => $newPassword
            ]);
            
            if ($updateResponse->failed()) {
                \Log::error('Failed to update Supabase password: ' . $updateResponse->body());
                return false;
            }
            
            \Log::info('Supabase auth password updated for: ' . $email);
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error updating Supabase auth password: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword()
    {
        return view('admin_forgot_password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'admin_email' => ['required', 'email'],
        ]);

        $admin = Admin::matchingEmail($request->admin_email)->first();

        if ($admin) {
            // Generate a raw token for the email
            $rawToken = Str::random(64);
            
            // Store the HASHED version in the database
            $admin->update([
                'reset_token' => hash('sha256', $rawToken),
                'reset_token_expires_at' => now()->addHour(),
            ]);

            try {
                $service = new BrevoMailService();
                $htmlContent = view('emails.admin_password_reset', [
                    'admin' => $admin,
                    'token' => $rawToken, // Send the RAW token to the user
                ])->render();
                
                $service->sendEmail(
                    $admin->admin_email,
                    'Password Reset Request - LumiNUs',
                    $htmlContent
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send password reset email: ' . $e->getMessage());
            }
        }

        // ALWAYS return the same message
        return back()->with('status', 'If that email is registered, a password reset link has been sent.');
    }

    public function showResetForm(Request $request)
    {
        $rawToken = $request->query('token');
        $email = $request->query('email');
        
        if (!$rawToken || !$email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Invalid password reset link.');
        }

        $hashedToken = hash('sha256', $rawToken);

        $admin = Admin::matchingEmail($email)
            ->where('reset_token', $hashedToken)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$admin) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'This password reset link is invalid or has expired.');
        }

        // ✅ Store the token in the session
        $request->session()->put('reset_token', $rawToken);
        $request->session()->put('reset_email', $email);

        // ✅ Show the reset form directly (no redirect)
        return view('admin_reset_password', [
            'token' => $rawToken,
            'email' => $email,
        ]);
    }

    public function showResetFormClean(Request $request)
    {
        $rawToken = $request->session()->get('reset_token');
        $email = $request->session()->get('reset_email');

        if (!$rawToken || !$email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Invalid password reset link.');
        }

        return view('admin_reset_password', [
            'token' => $rawToken,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Hash the incoming token
        $hashedToken = hash('sha256', $request->token);

        $admin = Admin::matchingEmail($request->email)
            ->where('reset_token', $hashedToken)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$admin) {
            return back()->with('error', 'This password reset link is invalid or has expired.');
        }

        $admin->update([
            'admin_password_hash' => Hash::make($request->password),
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        \DB::table('sessions')->where('user_id', $admin->id)->delete();

        return redirect()->route('admin.login')
            ->with('status', 'Your password has been reset successfully. Please login with your new password.');

    }

    /**
     * Update the authenticated admin's password.
     */
    public function changePassword(Request $request)
    {
        $admin = $this->getAuthenticatedAdmin($request);

        if (!$admin) {
            abort(403);
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Verify current password
        $storedPassword = (string) ($admin->admin_password_hash ?? '');
        $passwordInfo = password_get_info($storedPassword);
        $isHashedPassword = ($passwordInfo['algoName'] ?? 'unknown') !== 'unknown';
        
        $passwordMatches = $isHashedPassword 
            ? Hash::check($request->current_password, $storedPassword)
            : hash_equals($storedPassword, $request->current_password);

        if (!$passwordMatches) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password you entered is incorrect.',
            ]);
        }

        // Update password
        $admin->update([
            'admin_password_hash' => Hash::make($request->password),
        ]);

        $request->session()->regenerate();
        \DB::table('sessions')
            ->where('user_id', $admin->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();


        return redirect()
            ->route('admin.settings', ['section' => 'security'])
            ->with('status', 'Your password has been changed successfully.');
    }

    /**
     * Reset an admin's password and send notification email.
     */
    public function resetAdminPassword(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $this->requireCoordinator($request);

        if ($currentAdmin->id == $admin->id) {
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('status', 'To change your own password, use the Security tab.');
        }

        $temporaryPassword = $this->generateTemporaryPassword(10); // ✅ Custom generator
        $admin->update([
            'admin_password_hash' => Hash::make($temporaryPassword),
        ]);

        // Send email notification
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_password_reset_notify', [
                'admin' => $admin,
                'temporaryPassword' => $temporaryPassword,
                'resetBy' => trim($currentAdmin->admin_first_name . ' ' . $currentAdmin->admin_last_name)
            ])->render();
            
            $service->sendEmail(
                $admin->admin_email,
                'Your LumiNUs Admin Password Has Been Reset',
                $htmlContent
            );
            
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('status', 'Password for ' . $admin->admin_first_name . ' ' . $admin->admin_last_name . ' has been reset and emailed.')
                ->with('temporary_password', $temporaryPassword);
                
        } catch (\Throwable $e) {
            \Log::error('Failed to send password reset email to ' . $admin->admin_email . ': ' . $e->getMessage());
            
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('status', 'Password reset, but email could not be sent.')
                ->with('temporary_password', $temporaryPassword);
        }
    }

    public function toggleRestrictAdmin(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $this->requireCoordinator($request);

        // Prevent self-restriction
        if ($currentAdmin->id == $admin->id) {
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('error', 'You cannot restrict your own account.');
        }

        // Check if account_status exists and has valid value
        $currentStatus = $admin->account_status ?? 1; // Default to active if null
        
        // Toggle the status
        $newStatus = $currentStatus == 1 ? 0 : 1;
        
        // Update the admin status
        $admin->update(['account_status' => $newStatus]);

        $isRestricted = $newStatus == 0;
        $action = $isRestricted ? 'restricted' : 'unrestricted';

        // ✅ FORCE LOGOUT: If restricting and the admin is currently logged in
        if ($isRestricted) {
            $this->forceLogoutAdmin($admin->id);
        }

        // Log the action for audit
        \Log::info('Admin account ' . $action, [
            'admin_id' => $admin->id,
            'admin_email' => $admin->admin_email,
            'performed_by' => $currentAdmin->id,
            'new_status' => $newStatus,
            'ip' => $request->ip(),
            'forced_logout' => $isRestricted
        ]);

        // Send email notification
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_account_restricted', [
                'admin' => $admin,
                'isRestricted' => $isRestricted,
                'updatedBy' => trim($currentAdmin->admin_first_name . ' ' . $currentAdmin->admin_last_name)
            ])->render();
            
            $subject = $isRestricted 
                ? 'Your LumiNUs Admin Account Has Been Restricted' 
                : 'Your LumiNUs Admin Account Has Been Restored';
            
            $service->sendEmail($admin->admin_email, $subject, $htmlContent);
            
            $message = $isRestricted 
                ? 'Account for ' . $admin->admin_first_name . ' ' . $admin->admin_last_name . ' has been restricted and they have been logged out.'
                : 'Account for ' . $admin->admin_first_name . ' ' . $admin->admin_last_name . ' has been unrestricted.';
            
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('success', $message);
                
        } catch (\Throwable $e) {
            \Log::error('Failed to send restriction email to ' . $admin->admin_email . ': ' . $e->getMessage());
            
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('warning', 'Account for ' . $admin->admin_first_name . ' ' . $admin->admin_last_name . ' has been ' . $action . ', but email could not be sent.');
        }
    }

    /**
     * Delete an admin account and send notification email.
     */
    public function deleteAdmin(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $this->requireCoordinator($request);

        if ($currentAdmin->id == $admin->id) {
            return redirect()
                ->route('admin.settings', ['section' => 'roles'])
                ->with('status', 'You cannot delete your own account.');
        }

        $adminName = trim($admin->admin_first_name . ' ' . $admin->admin_last_name);
        $adminEmail = $admin->admin_email;
        $deletedBy = trim($currentAdmin->admin_first_name . ' ' . $currentAdmin->admin_last_name);

        // Delete photo if exists
        $this->deleteAdminPhoto($admin->photo);

        // Send email BEFORE deleting (so we still have the data)
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.admin_account_deleted', [
                'adminName' => $adminName,
                'adminEmail' => $adminEmail,
                'deletedBy' => $deletedBy
            ])->render();
            
            $service->sendEmail(
                $adminEmail,
                'Your LumiNUs Admin Account Has Been Removed',
                $htmlContent
            );
        } catch (\Throwable $e) {
            \Log::error('Failed to send deletion email to ' . $adminEmail . ': ' . $e->getMessage());
        }

        // Now delete the admin
        $admin->delete();

        return redirect()
            ->route('admin.settings', ['section' => 'roles'])
            ->with('status', 'Admin account for ' . $adminName . ' has been deleted and notified.');
    }

    protected function forceLogoutAdmin($adminId)
    {
        try {
            \Log::info('=== FORCE LOGOUT STARTED ===', [
                'admin_id' => $adminId,
                'session_driver' => config('session.driver'),
                'timestamp' => now()
            ]);
            
            if (config('session.driver') !== 'database') {
                \Log::error('Session driver is NOT database! Current: ' . config('session.driver'));
                return;
            }
            
            // Count sessions before
            $before = \DB::table('sessions')->count();
            \Log::info("Sessions before deletion: {$before}");
            
            // Delete by admin_id in payload
            $deleted = \DB::table('sessions')
                ->where('user_id', $adminId)
                ->delete();
            
            // Count sessions after
            $after = \DB::table('sessions')->count();
            
            \Log::info('=== FORCE LOGOUT COMPLETED ===', [
                'admin_id' => $adminId,
                'sessions_deleted' => $deleted,
                'sessions_before' => $before,
                'sessions_after' => $after
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Force logout ERROR: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
    }

    protected function forceLogoutAlumni($alumniId)
    {
        try {
            if (config('session.driver') !== 'database') {
                \Log::info('Session driver is not database, skipping force logout for alumni: ' . $alumniId);
                return;
            }

            // Use the user_id column for reliable matching
            $deleted = \DB::table('sessions')
                ->where('user_id', $alumniId)
                ->delete();

            \Log::info('Force logout alumni', [
                'alumni_id' => $alumniId,
                'sessions_deleted' => $deleted
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to force logout alumni ' . $alumniId . ': ' . $e->getMessage());
        }
    }

    /**
     * Show admin permissions for editing
     */
    public function showAdminPermissions(Request $request, $id)
    {
        $admin = Admin::with('permissions')->findOrFail($id);
        $currentAdmin = $this->requireCoordinator($request);

        // In showAdminPermissions()
        if ($currentAdmin->id == $admin->id) {
            return response()->json(['error' => 'You cannot modify your own permissions.'], 400);
        }
                
        $allModules = AdminPermission::getAvailableModules();
        $existingPermissions = $admin->permissions()->pluck('can_view', 'module')->toArray();
        
        $permissionsData = [];
        foreach ($allModules as $moduleKey => $moduleData) {
            if (array_key_exists($moduleKey, $existingPermissions)) {
                $canView = (bool) $existingPermissions[$moduleKey];
            } else {
                $defaults = AdminPermission::getDefaultPermissionsForRole($admin->admin_role);
                $canView = $defaults[$moduleKey] ?? false;
            }
            
            $permissionsData[$moduleKey] = [
                'name' => $moduleData['name'],
                'icon' => $moduleData['icon'],
                'description' => $moduleData['description'],
                'can_view' => $canView,
                'is_default' => !array_key_exists($moduleKey, $existingPermissions),
            ];
        }
        
        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
                'role' => $admin->admin_role,
            ],
            'permissions' => $permissionsData,
        ]);
    }

    /**
     * Update admin permissions
     */
    public function updateAdminPermissions(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $currentAdmin = $this->requireCoordinator($request);

        // In updateAdminPermissions()
        if ($currentAdmin->id == $admin->id) {
            return response()->json(['error' => 'You cannot modify your own permissions.'], 400);
        }
        
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.module' => 'required|string',
            'permissions.*.can_view' => 'required|boolean',
            'permissions.*.can_manage' => 'required|boolean',
        ]);
        
        $permissionsToSync = [];
        foreach ($validated['permissions'] as $perm) {
            $permissionsToSync[$perm['module']] = $perm['can_view'];
        }
        
        $admin->syncPermissions($permissionsToSync);
        
        \Log::info('Admin permissions updated', [
            'admin_id' => $admin->id,
            'admin_name' => trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
            'updated_by' => $currentAdmin->id,
            'permissions' => $validated['permissions'],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Permissions updated for ' . trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')),
        ]);
    }

    /**
     * Reset alumni password and trigger force change on next login
     */
    public function resetAlumniPassword(Request $request, $id)
    {
        $alumnus = Alumni::findOrFail($id);
        $currentAdmin = $this->getAuthenticatedAdmin($request);

        // Generate a clean, mobile-friendly temporary password
        $temporaryPassword = $this->generateTemporaryPassword(10);

        // 🔥 ADD THIS DEBUG LOG
        \Log::info('=== GENERATED PASSWORD ===', [
            'email' => $alumnus->email,
            'length' => strlen($temporaryPassword),
            'has_uppercase' => preg_match('/[A-Z]/', $temporaryPassword) ? 'YES' : 'NO',
            'has_lowercase' => preg_match('/[a-z]/', $temporaryPassword) ? 'YES' : 'NO',
            'has_number' => preg_match('/[0-9]/', $temporaryPassword) ? 'YES' : 'NO',
            'has_special' => preg_match('/[!@#$%^&*?]/', $temporaryPassword) ? 'YES' : 'NO',
        ]);

        // Update password in your database
        $alumnus->update([
            'password_hash' => Hash::make($temporaryPassword),
            'needs_password_change' => true,
        ]);

        // Update Supabase Auth
        $authUpdated = $this->updateSupabaseAuthPassword(
            $alumnus->email,
            $temporaryPassword
        );

        // 🔥 ADD THIS DEBUG LOG
        \Log::info('=== SUPABASE UPDATE RESULT ===', [
            'email' => $alumnus->email,
            'auth_updated' => $authUpdated ? 'SUCCESS' : 'FAILED',
        ]);

        // ✅ ADD THIS DEBUG LOG
        \Log::info('=== PASSWORD RESET DEBUG ===', [
            'alumni_id' => $alumnus->id,
            'alumni_email' => $alumnus->email,
            'auth_updated' => $authUpdated,
            'auth_updated_success' => $authUpdated ? 'YES' : 'NO',
            'timestamp' => now()->toDateTimeString()
        ]);

        if (!$authUpdated) {
            \Log::error('Failed to update Supabase auth password for: ' . $alumnus->email);
        }

        // Send email notification
        try {
            $service = new BrevoMailService();
            $htmlContent = view('emails.alumni_password_reset_notify', [
                'alumnus' => $alumnus,
                'temporaryPassword' => $temporaryPassword,
                'resetBy' => trim(
                    ($currentAdmin->admin_first_name ?? '') . ' ' . 
                    ($currentAdmin->admin_last_name ?? '')
                )
            ])->render();

            $service->sendEmail(
                $alumnus->email,
                'Your LumiNUs Password Has Been Reset',
                $htmlContent
            );

            \Log::info('Alumni password reset', [
                'alumni_id' => $alumnus->id,
                'alumni_email' => $alumnus->email,
                'reset_by' => $currentAdmin->id ?? null,
                'ip' => $request->ip(),
                'auth_synced' => $authUpdated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. ' . $alumnus->first_name . ' has been emailed their new temporary password.'
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Failed to send password reset email to ' . $alumnus->email . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Password reset but email could not be sent. Please try again.'
            ], 500);
        }
    }

public function toggleRestrictAlumni(Request $request, $id)
{
    $alumnus = Alumni::findOrFail($id);
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    $request->validate([
        'restrict' => 'required|boolean',
        'restriction_reason' => 'required_if:restrict,true|nullable|string|max:255',
        'restriction_comment' => 'nullable|string|max:1000',
    ]);

    $isRestricting = $request->input('restrict') == 1;
    $newStatus = $isRestricting ? 0 : 1;

    $updateData = [
        'account_status' => $newStatus,
    ];

    if ($isRestricting) {
        $updateData['restriction_reason'] = $request->input('restriction_reason');
        $updateData['restriction_comment'] = $request->input('restriction_comment', '');
        $updateData['restricted_by'] = $currentAdmin->id;
        $updateData['restricted_at'] = now();
    } else {
        // Clear restriction data when unrestricting
        $updateData['restriction_reason'] = null;
        $updateData['restriction_comment'] = null;
        $updateData['restricted_by'] = null;
        $updateData['restricted_at'] = null;
    }

    $alumnus->update($updateData);

    // Force logout if restricting
    if ($isRestricting) {
        $this->forceLogoutAlumni($alumnus->id);
    }

    // Send email notification
    try {
        $service = new BrevoMailService();
        $htmlContent = view('emails.alumni_account_restricted', [
            'alumnus' => $alumnus,
            'isRestricted' => $isRestricting,
            'updatedBy' => trim(
                ($currentAdmin->admin_first_name ?? '') . ' ' . 
                ($currentAdmin->admin_last_name ?? '')
            ),
            'restrictionReason' => $isRestricting ? $request->input('restriction_reason') : null,
            'restrictionComment' => $isRestricting ? $request->input('restriction_comment') : null,
        ])->render();

        $subject = $isRestricting 
            ? 'Your LumiNUs Account Has Been Restricted' 
            : 'Your LumiNUs Account Has Been Restored';

        $service->sendEmail($alumnus->email, $subject, $htmlContent);

        \Log::info('Alumni account ' . ($isRestricting ? 'restricted' : 'unrestricted'), [
            'alumni_id' => $alumnus->id,
            'alumni_email' => $alumnus->email,
            'performed_by' => $currentAdmin->id ?? null,
            'new_status' => $newStatus,
            'reason' => $request->input('restriction_reason'),
            'comment' => $request->input('restriction_comment'),
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => $isRestricting 
                ? $alumnus->first_name . '\'s account has been restricted and they have been logged out.'
                : $alumnus->first_name . '\'s account has been unrestricted.',
            'data' => [
                'account_status' => $newStatus,
                'restriction_reason' => $updateData['restriction_reason'] ?? null,
                'restriction_comment' => $updateData['restriction_comment'] ?? null,
            ]
        ], 200);

    } catch (\Throwable $e) {
        \Log::error('Failed to send restriction email to ' . $alumnus->email . ': ' . $e->getMessage());

        return response()->json([
            'success' => true,
            'message' => $isRestricting 
                ? 'Account restricted, but email could not be sent.'
                : 'Account unrestricted, but email could not be sent.',
            'warning' => true
        ], 200);
    }
}

/**
 * Archive an alumni account (soft delete)
 */
public function archiveAlumni(Request $request, $id)
{
    $alumnus = Alumni::findOrFail($id);
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    // Check if already archived - use trashed() instead
    if ($alumnus->trashed()) {
        return response()->json([
            'success' => false,
            'message' => 'This account is already archived.'
        ], 400);
    }

    // Soft delete the account
    $alumnus->delete();

    // Force logout if they're logged in
    $this->forceLogoutAlumni($alumnus->id);

    \Log::info('Alumni account archived', [
        'alumni_id' => $alumnus->id,
        'alumni_email' => $alumnus->email,
        'performed_by' => $currentAdmin->id ?? null,
        'ip' => $request->ip()
    ]);

    // Send email notification
    try {
        $service = new BrevoMailService();
        $htmlContent = view('emails.alumni_account_archived', [
            'alumnus' => $alumnus,
            'archivedBy' => trim(
                ($currentAdmin->admin_first_name ?? '') . ' ' . 
                ($currentAdmin->admin_last_name ?? '')
            )
        ])->render();

        $service->sendEmail(
            $alumnus->email,
            'Your LumiNUs Account Has Been Archived',
            $htmlContent
        );

    } catch (\Throwable $e) {
        \Log::error('Failed to send archive email to ' . $alumnus->email . ': ' . $e->getMessage());
    }

    return response()->json([
        'success' => true,
        'message' => $alumnus->first_name . '\'s account has been archived successfully.'
    ], 200);
}

/**
 * Restore an archived alumni account
 */
public function restoreAlumni(Request $request, $id)
{
    $alumnus = Alumni::withTrashed()->findOrFail($id);
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    // Check if not archived - use trashed() instead
    if (!$alumnus->trashed()) {
        return response()->json([
            'success' => false,
            'message' => 'This account is not archived.'
        ], 400);
    }

    // Restore the account
    $alumnus->restore();

    \Log::info('Alumni account restored from archive', [
        'alumni_id' => $alumnus->id,
        'alumni_email' => $alumnus->email,
        'performed_by' => $currentAdmin->id ?? null,
        'ip' => $request->ip()
    ]);

    return response()->json([
        'success' => true,
        'message' => $alumnus->first_name . '\'s account has been restored successfully.'
    ], 200);
}

/**
 * Permanently delete an alumni account (hard delete)
 */
public function permanentlyDeleteAlumni(Request $request, $id)
{
    $alumnus = Alumni::withTrashed()->findOrFail($id);
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    try {
        // Delete all related records first
        
        // 1. Delete addresses
        $alumnus->addresses()->delete();
        
        // 2. Delete employments
        $alumnus->employments()->delete();
        
        // 3. Delete skills
        $alumnus->skills()->delete();
        
        // 4. Delete posts and their related data
        foreach ($alumnus->posts as $post) {
            // Delete post images
            $post->images()->delete();
            // Delete post reactions
            $post->reactions()->delete();
            // Delete post comments (they will be handled below, but let's be safe)
            $post->comments()->delete();
            // Delete post reports
            $post->reports()->delete();
            // Delete the post
            $post->delete();
        }
        
        // 5. Delete comments (any remaining comments not tied to posts)
        $alumnus->comments()->delete();
        
        // 6. Delete reactions (any remaining reactions)
        $alumnus->reactions()->delete();
        
        // 7. Delete reposts
        $alumnus->reposts()->delete();
        
        // 8. Delete event registrations
        $alumnus->eventRegistrations()->delete();
        
        // 9. Delete tracer responses and their answers
        foreach ($alumnus->tracerResponses as $response) {
            // Delete tracer answers
            $response->answers()->delete();
            // Delete the response
            $response->delete();
        }
        
        // 10. Delete messages (sent and received)
        $alumnus->messagesSent()->delete();
        $alumnus->messagesReceived()->delete();
        
        // 11. Delete group chat memberships
        $alumnus->groupChatMembers()->delete();
        
        // 12. Delete followers/following relationships
        $alumnus->followers()->delete();
        $alumnus->following()->delete();
        
        // 13. Delete dismissed notifications
        $alumnus->dismissedNotifications()->delete();
        
        // 14. Delete favorite chats
        $alumnus->favoriteChats()->delete();
        
        // 15. Delete DM settings
        $alumnus->dmSettings()->delete();

        // 17. Delete photo from S3
        if ($alumnus->card_photo) {
            $this->deleteAlumniPhoto($alumnus->card_photo);
        }
        if ($alumnus->alumni_photo) {
            $this->deleteAlumniPhoto($alumnus->alumni_photo);
        }
        $alumnus->programs()->delete();   

        // Finally, permanently delete the alumni
        $alumnus->forceDelete();

        \Log::info('Alumni account and all related data permanently deleted', [
            'alumni_id' => $alumnus->id,
            'alumni_email' => $alumnus->email,
            'performed_by' => $currentAdmin->id ?? null,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alumni account and all associated data have been permanently deleted.'
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Failed to permanently delete alumni ' . $id . ': ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while deleting the account: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Display archived alumni accounts
 */
public function archivedAlumni(Request $request)
{
    $archivedAlumni = Alumni::onlyTrashed()
        ->latest('deleted_at')
        ->paginate(20);

    $totalArchived = Alumni::onlyTrashed()->count();
    $totalRestricted = Alumni::whereNull('deleted_at')->where('account_status', 0)->count();
    
    // 👇 ADD THIS - Fetch restricted alumni for the restricted tab
    $restrictedAlumni = Alumni::whereNull('deleted_at')
        ->where('account_status', 0)
        ->latest('restricted_at')
        ->paginate(20);

    return view('directory.archived', compact(
        'archivedAlumni',
        'totalArchived',
        'totalRestricted',
        'restrictedAlumni'  // 👈 ADD THIS
    ));
}

/**
 * Display restricted alumni accounts
 */
public function restrictedAlumni(Request $request)
{
    return redirect('/admin/directory/archived#tab-restricted');
}

/**
 * Get restriction reasons for API
 */
public function getRestrictionReasons()
{
    return response()->json([
        'success' => true,
        'reasons' => Alumni::getRestrictionReasons()
    ]);
}

/**
 * Preview bulk data (receives parsed JSON from frontend)
 */
public function previewBulkData(Request $request)
{
    $request->validate([
        'headers' => 'required|array',
        'rows' => 'required|array',
    ]);
    
    $headers = $request->input('headers');
    $dataRows = $request->input('rows');
    
    \Log::info('Preview data received', [
        'headers' => $headers,
        'row_count' => count($dataRows)
    ]);
    
    $validationResults = $this->validateBulkRows($dataRows);
    
    return response()->json([
        'success' => true,
        'headers' => $headers,
        'rows' => $dataRows,
        'total' => count($dataRows),
        'validation' => $validationResults,
    ]);
}

protected function validateBulkRows($rows)
{
    $results = [];
    
    // Get all student IDs for duplicate check
    $studentIds = [];
    $emails = [];
    
    foreach ($rows as $index => $row) {
        $errors = [];
        $valid = true;
        
        // Get the actual column names from the row
        $keys = array_keys($row);
        \Log::info("Row {$index} keys:", ['keys' => $keys]);
        
        // Helper to find value by key (case-insensitive and with flexible matching)
        $findValue = function($searchKeys, $row) {
            foreach ($row as $key => $value) {
                $keyLower = strtolower(trim($key));
                foreach ((array)$searchKeys as $searchKey) {
                    $searchKeyLower = strtolower(trim($searchKey));
                    // Exact match OR contains match
                    if ($keyLower === $searchKeyLower || 
                        strpos($keyLower, $searchKeyLower) !== false ||
                        strpos($searchKeyLower, $keyLower) !== false) {
                        return trim($value);
                    }
                }
            }
            return '';
        };
        
        // Get values with flexible key matching
        $studentId = $findValue(['Student ID', 'StudentID', 'student_id', 'ID Number', 'id'], $row);
        $firstName = $findValue(['First Name', 'FirstName', 'first_name', 'First', 'Given Name'], $row);
        $lastName = $findValue(['Last Name', 'LastName', 'last_name', 'Last', 'Surname', 'Family Name'], $row);
        $email = $findValue(['Email', 'E-mail', 'email_address', 'Personal Email', 'Official Email'], $row);
        $program = $findValue(['Program', 'program', 'Strand', 'Department', 'Course'], $row);
        $middleName = $findValue(['Middle Name', 'MiddleName', 'middle_name', 'Middle'], $row);
        $phone = $findValue(['Phone', 'phone_number', 'Mobile', 'Contact', 'Mobile Number', 'Phone Number'], $row);
        $gradYear = $findValue(['Graduation Year', 'year_graduated', 'Year Graduated', 'Graduation', 'Grad Year'], $row);
        $dob = $findValue(['Date of Birth', 'date_of_birth', 'Birth Date', 'DOB', 'Birthdate'], $row);
        $sex = $findValue(['Sex', 'gender', 'Gender'], $row);
        
        // Log what we found for debugging
        \Log::info("Row {$index} extracted values:", [
            'studentId' => $studentId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'program' => $program
        ]);
        
        // Check required fields
        if (empty($studentId)) {
            $errors[] = 'Student ID is required';
            $valid = false;
        }
        if (empty($firstName)) {
            $errors[] = 'First Name is required';
            $valid = false;
        }
        if (empty($lastName)) {
            $errors[] = 'Last Name is required';
            $valid = false;
        }
        if (empty($email)) {
            $errors[] = 'Email is required';
            $valid = false;
        }
        if (empty($program)) {
            $errors[] = 'Program is required';
            $valid = false;
        }
        
        // Validate email format
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
            $valid = false;
        }
        
        // Check for duplicate student IDs in the import
        if (!empty($studentId)) {
            $studentIds[] = $studentId;
        }
        if (!empty($email)) {
            $emails[] = $email;
        }
        
        // Store the row data with normalized keys for later use
        $results[$index] = [
            'valid' => $valid,
            'errors' => $errors,
            'data' => [
                'student_id_number' => $studentId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'program' => $program,
                'middle_name' => $middleName,
                'phone_number' => $phone,
                'year_graduated' => $gradYear,
                'date_of_birth' => $dob,
                'sex' => $sex,
            ]
        ];
    }
    
    // Check for duplicates within the import
    $duplicateStudentIds = array_filter(array_count_values($studentIds), function($count) {
        return $count > 1;
    });
    
    $duplicateEmails = array_filter(array_count_values($emails), function($count) {
        return $count > 1;
    });
    
    foreach ($results as $index => &$result) {
        $studentId = $result['data']['student_id_number'] ?? '';
        $email = $result['data']['email'] ?? '';
        
        if (!empty($studentId) && isset($duplicateStudentIds[$studentId]) && $duplicateStudentIds[$studentId] > 1) {
            $result['errors'][] = 'Duplicate Student ID in import file';
            $result['valid'] = false;
        }
        
        if (!empty($email) && isset($duplicateEmails[$email]) && $duplicateEmails[$email] > 1) {
            $result['errors'][] = 'Duplicate Email in import file';
            $result['valid'] = false;
        }
    }
    
    return $results;
}

/**
 * Process bulk import with validated data
 */
public function processBulkImport(Request $request)
{
    // ⚠️ With FormData, 'data' and 'mapping' arrive as JSON strings
    $dataInput = $request->input('data');
    $mappingInput = $request->input('mapping');

    $importData = is_string($dataInput) ? json_decode($dataInput, true) : $dataInput;
    $mapping    = is_string($mappingInput) ? json_decode($mappingInput, true) : $mappingInput;

    if (!is_array($importData) || !is_array($mapping)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid import payload.',
        ], 400);
    }

    // Alumni type
    $finalAlumniType = $request->input('default_alumni_type', 'college');
    if (!in_array($finalAlumniType, ['shs', 'college'], true)) {
        $finalAlumniType = 'college';
    }

    // Strip empty mapping entries
    $mapping = array_filter($mapping, fn($v) => !empty($v));

    if (empty($mapping)) {
        return response()->json([
            'success' => false,
            'message' => 'No valid column mapping provided.',
        ], 400);
    }

    $requiredFields = ['student_id_number', 'first_name', 'last_name', 'email', 'program'];
    $missingFields = array_filter($requiredFields, fn($f) => empty($mapping[$f]));
    if (!empty($missingFields)) {
        return response()->json([
            'success' => false,
            'message' => 'Missing required field mapping: ' . implode(', ', $missingFields),
        ], 400);
    }

    $results = [
        'successful' => 0,
        'failed' => 0,
        'errors' => [],
        'duplicates' => [],
        'warnings' => [],
        'photos_matched' => 0,
        'photos_overridden' => 0,
        'photos_unmatched' => [],
    ];

    // ========================================
    // BUILD PHOTO LOOKUP MAP
    // ========================================
    $photoMap = $this->buildPhotoMap($request);

    // ========================================
    // PROCESS ROWS
    // ========================================
    foreach ($importData as $index => $row) {
        try {
            // Map fields
            $record = [];
            foreach ($mapping as $dbField => $csvColumn) {
                if (!empty($csvColumn) && isset($row[$csvColumn])) {
                    $record[$dbField] = trim((string) $row[$csvColumn]);
                }
            }

            // Validate required fields
            if (
                empty($record['first_name']) ||
                empty($record['last_name']) ||
                empty($record['student_id_number']) ||
                empty($record['email']) ||
                empty($record['program'])
            ) {
                $results['errors'][] = "Missing required fields in row " . ($index + 1);
                $results['failed']++;
                continue;
            }

            // Parse dates
            $yearGraduated = !empty($record['year_graduated'])
                ? $this->parseDate($record['year_graduated'])
                : null;
            $dateOfBirth = !empty($record['date_of_birth'])
                ? $this->parseDate($record['date_of_birth'])
                : null;

            // ========================================
            // CHECK IF ALUMNI ALREADY EXISTS
            // (by student_id_number OR email)
            // ========================================
            $idHash = hash('sha256', trim($record['student_id_number']));

            $existing = Alumni::withTrashed()
                ->where('student_id_hash', $idHash)
                ->orWhere('email_hash', hash('sha256', strtolower(trim($record['email']))))
                ->first();

            $isNewAccount = false;

            if ($existing) {
                // 🔄 EXISTING ACCOUNT — we still want to update the photo (override)
                $alumnus = $existing;

                // Optionally un-delete if archived
                if ($alumnus->trashed()) {
                    $alumnus->restore();
                    $results['warnings'][] = "Restored archived account for {$alumnus->email}";
                }
            } else {
                // ✨ NEW ACCOUNT
                $temporaryPassword = $this->generateTemporaryPassword(10);

                $alumnus = Alumni::create([
                    'first_name' => $record['first_name'],
                    'middle_name' => $record['middle_name'] ?? null,
                    'last_name' => $record['last_name'],
                    'student_id_number' => $record['student_id_number'],
                    'student_id_hash' => $idHash, 
                    'email' => $record['email'],
                    'phone_number' => $record['phone_number'] ?? null,
                    'program' => $record['program'] ?? null,
                    'date_of_birth' => $dateOfBirth,
                    'sex' => $record['sex'] ?? null,
                    'year_graduated' => $yearGraduated,
                    'password_hash' => Hash::make($temporaryPassword),
                    'verification_status' => 'verified',
                    'needs_password_change' => true,
                    'account_status' => 1,
                    'alumni_type' => $finalAlumniType,
                ]);

                AlumniProgram::create([
                    'alumni_id'         => $alumnus->id,
                    'program_type'      => $finalAlumniType,
                    'program_name'      => $record['program'] ?? null,
                    'student_id_number' => $record['student_id_number'],
                    'student_id_hash'   => $idHash,
                    'year_graduated'    => $yearGraduated,
                    'enrollment_status' => 'graduated',
                    'is_primary'        => true,
                ]);

                $isNewAccount = true;

                // Create Supabase auth user
                $authCreated = $this->createSupabaseAuthUser(
                    $alumnus->email,
                    $temporaryPassword,
                    $alumnus->first_name,
                    $alumnus->last_name
                );

                if (!$authCreated) {
                    $results['warnings'][] = "Supabase user creation failed for {$alumnus->email}";
                }

                // Send welcome email
                try {
                    $service = new BrevoMailService();
                    $htmlContent = view('emails.welcome-alumni', [
                        'alumnus' => $alumnus,
                        'temporaryPassword' => $temporaryPassword,
                    ])->render();

                    $service->sendEmail($alumnus->email, 'Welcome to LumiNUs', $htmlContent);
                } catch (\Exception $e) {
                    \Log::error('Failed to send welcome email to ' . $alumnus->email . ': ' . $e->getMessage());
                    $results['warnings'][] = "Email could not be sent to {$alumnus->email}";
                }

                $results['successful']++;
            }

                        // ========================================
            // MATCH + UPLOAD CARD PHOTO (unless admin skipped it)
            // ========================================
            if (empty($row['_skip_photo'])) {
                $photoKey = $this->buildPhotoKey(
                    $alumnus->last_name,
                    $alumnus->first_name,
                    $alumnus->middle_name
                );

                if ($photoKey && isset($photoMap[$photoKey])) {
                    $photoInfo = $photoMap[$photoKey];
                    $hadExistingPhoto = !empty($alumnus->card_photo);

                    $newUrl = $this->uploadCardPhotoToSupabase($alumnus, $photoInfo['path'], $photoInfo['ext']);

                    if ($newUrl) {
                        // 🧹 Delete the OLD photo from Supabase before saving the new one
                        if ($hadExistingPhoto) {
                            $this->deleteAlumniPhoto($alumnus->card_photo);
                            $results['photos_overridden']++;
                        }

                        $alumnus->update(['card_photo' => $newUrl]);
                        $results['photos_matched']++;

                        // Mark as used
                        unset($photoMap[$photoKey]);
                    } else {
                        $results['warnings'][] = "Photo upload failed for {$alumnus->student_id_number}";
                    }
                }
            }   // ← closes outer if (empty($row['_skip_photo']))
        } catch (\Exception $e) {
            $results['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
            $results['failed']++;
            \Log::error('Bulk import error at row ' . ($index + 1) . ': ' . $e->getMessage());
        }
    }

    // ========================================
    // REPORT UNMATCHED PHOTOS
    // ========================================
    if (!empty($photoMap)) {
        $results['photos_unmatched'] = array_keys($photoMap);
        $results['warnings'][] = count($photoMap) . ' photo(s) had no matching alumni.';
    }

    return response()->json([
        'success' => true,
        'results' => $results,
        'message' => "Import complete! {$results['successful']} successful, {$results['failed']} failed.",
    ]);
}

/**
 * Build a lookup map of photo key => ['path' => local, 'ext' => 'jpg'].
 * Handles ZIP files or multiple image uploads.
 */
protected function buildPhotoMap(Request $request): array
{
    $map = [];
    $tmpDir = storage_path('app/tmp_photo_' . uniqid());
    @mkdir($tmpDir, 0755, true);

    // -------- Handle ZIP --------
    if ($request->hasFile('photos')) {
        $files = $request->file('photos');
        if (!is_array($files)) $files = [$files];

        foreach ($files as $file) {
            if (!$file->isValid()) continue;

            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());

            // Case 1: It's a ZIP — extract
            if ($ext === 'zip') {
                $this->extractZipPhotos($file->getRealPath(), $tmpDir, $map);
                continue;
            }

            // Case 2: It's an image — save directly
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $key = $this->buildPhotoKeyFromFilename($originalName);
                if (!$key) {
                    \Log::warning('Skipping photo with invalid name: ' . $originalName);
                    continue;
                }

                $localPath = $tmpDir . '/' . uniqid() . '.' . $ext;
                $file->move($tmpDir, basename($localPath));

                $map[$key] = ['path' => $localPath, 'ext' => $ext];
            }
        }
    }

    return $map;
}

/**
 * Extract images from a ZIP into $tmpDir and populate $map.
 */
protected function extractZipPhotos(string $zipPath, string $tmpDir, array &$map): void
{
    $zip = new \ZipArchive();
    if ($zip->open($zipPath) !== true) {
        \Log::error('Failed to open ZIP: ' . $zipPath);
        return;
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        $name = $stat['name'];

        // Skip directories and macOS junk
        if (substr($name, -1) === '/' || str_contains($name, '__MACOSX')) continue;

        $baseName = basename($name);
        $ext = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;

        $key = $this->buildPhotoKeyFromFilename($baseName);
        if (!$key) {
            \Log::warning('Skipping ZIP entry with invalid name: ' . $baseName);
            continue;
        }

        $contents = $zip->getFromIndex($i);
        if ($contents === false) continue;

        $localPath = $tmpDir . '/' . uniqid() . '.' . $ext;
        file_put_contents($localPath, $contents);

        $map[$key] = ['path' => $localPath, 'ext' => $ext];
    }

    $zip->close();
}

/**
 * Build a normalized key from a filename: SURNAME|FIRSTNAME|MIDDLEINITIAL
 * Example: "CLAUS_JOHANNES EMMANUEL_A.jpg" -> "CLAUS|JOHANNES EMMANUEL|A"
 * Returns null if invalid.
 */
protected function buildPhotoKeyFromFilename(string $filename): ?string
{
    // Strip extension
    $base = preg_replace('/\.[^.]+$/', '', $filename);
    $base = strtoupper(trim($base));

    // Split on underscore
    $parts = array_values(array_filter(array_map('trim', explode('_', $base))));

    if (count($parts) < 3) return null;

    $lastName = $parts[0];
    $middleInitial = $parts[count($parts) - 1];
    $firstName = implode(' ', array_slice($parts, 1, -1));

    if (strlen($middleInitial) !== 1 || !preg_match('/^[A-Z]$/', $middleInitial)) {
        return null;
    }

    return $this->buildPhotoKey($lastName, $firstName, $middleInitial);
}

/**
 * Build a normalized key from alumni fields.
 */
protected function buildPhotoKey(?string $lastName, ?string $firstName, ?string $middleName): ?string
{
    $lastName = strtoupper(trim((string) $lastName));
    $firstName = strtoupper(trim((string) $firstName));
    $middleName = trim((string) $middleName);

    if (!$lastName || !$firstName || !$middleName) return null;

    $middleInitial = strtoupper($middleName[0]);
    return "{$lastName}|{$firstName}|{$middleInitial}";
}

/**
 * Upload a card photo to Supabase and return its public URL.
 */
protected function uploadCardPhotoToSupabase(Alumni $alumnus, string $localPath, string $ext): ?string
{
    try {
        // Store as {student_id}.{ext} to keep it deterministic
        $storagePath = 'card_photo/' . preg_replace('/[^A-Za-z0-9\-]/', '', $alumnus->student_id_number) . '.' . $ext;

        $stream = fopen($localPath, 'r');
        \Storage::disk('s3')->put($storagePath, $stream, 'public');
        if (is_resource($stream)) fclose($stream);

        @unlink($localPath);

        $baseUrl = rtrim((string) config('filesystems.disks.s3.url'), '/');
        return $baseUrl . '/' . ltrim($storagePath, '/');
    } catch (\Throwable $e) {
        \Log::error("Card photo upload failed for {$alumnus->student_id_number}: " . $e->getMessage());
        return null;
    }
}


protected function parseDate($dateString)
{
    if (empty($dateString)) {
        return null;
    }
    
    $dateString = trim($dateString);
    
    // Try common formats - put datetime formats FIRST
    $formats = [
        'Y-m-d H:i:s',    // 2004-01-22 00:00:00 (your format)
        'Y-m-d H:i',      // 2004-01-22 00:00
        'Y-m-d',          // 2004-01-22
        'm/d/Y',          // 01/22/2004
        'd/m/Y',          // 22/01/2004
        'm-d-Y',          // 01-22-2004
        'd-m-Y',          // 22-01-2004
        'Y.m.d',          // 2004.01.22
        'm.d.Y',          // 01.22.2004
        'd.m.Y',          // 22.01.2004
    ];
    
    foreach ($formats as $format) {
        $date = \DateTime::createFromFormat($format, $dateString);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
    }
    
    // Try to parse just the year
    if (preg_match('/^\d{4}$/', $dateString)) {
        return $dateString . '-01-01';
    }
    
    // Try to parse with strtotime as last resort
    $timestamp = strtotime($dateString);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    // Return null if no format matches
    return null;
}

/**
 * Get the full Supabase storage URL for a given path
 */
protected function getSupabaseStorageUrl($path)
{
    if (empty($path)) {
        return null;
    }
    
    // If it's already a full URL, return as is
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    
    // Remove leading slashes for consistency
    $path = ltrim($path, '/');
    
    // Get Supabase URL from config
    $supabaseUrl = config('filesystems.disks.s3.url', '');
    
    if (empty($supabaseUrl)) {
        // Fallback: construct from env
        $supabaseUrl = rtrim(config('services.supabase.url', ''), '/') . '/storage/v1/object/public/luminus_assets/';
    } else {
        // Use the S3 URL (which should point to your Supabase bucket)
        $supabaseUrl = rtrim($supabaseUrl, '/') . '/';
    }
    
    return $supabaseUrl . $path;
}

public function getPostInteractions(Request $request, $postId)
{
    try {
        $post = \App\Models\Post::with([
            'reactions.alumni',
            'comments.alumni',
            'reposts.alumni'
        ])->findOrFail($postId);
        
        $type = $request->input('type', 'likes');
        $data = [];
        $total = 0;
        
        // Helper function to get profile photo URL
        $getProfilePhotoUrl = function($alumnus) {
            if (!$alumnus) return null;
            
            $photoPath = trim((string) ($alumnus->alumni_photo ?: $alumnus->card_photo));
            if (empty($photoPath)) return null;
            
            if (preg_match('/^https?:\/\//i', $photoPath)) {
                return $photoPath;
            } elseif (str_starts_with($photoPath, '/storage/')) {
                return $photoPath;
            } elseif (str_starts_with($photoPath, 'storage/')) {
                return '/' . $photoPath;
            } elseif (str_starts_with($photoPath, '/')) {
                return $photoPath;
            } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                return rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($photoPath, '/');
            } else {
                return asset('storage/' . ltrim($photoPath, '/'));
            }
        };
        
        switch ($type) {
            case 'likes':
                $data = $post->reactions
                    ->sortByDesc('created_at')
                    ->take(100)
                    ->map(function($reaction) use ($getProfilePhotoUrl) {
                        return [
                            'id' => $reaction->alumni->id ?? null,
                            'first_name' => $reaction->alumni->first_name ?? 'Unknown',
                            'last_name' => $reaction->alumni->last_name ?? '',
                            'profile_photo' => $getProfilePhotoUrl($reaction->alumni),
                            'created_at' => $reaction->created_at ? $reaction->created_at->toISOString() : null,
                        ];
                    })
                    ->values()
                    ->toArray();
                $total = $post->reactions->count();
                break;
                
            case 'comments':
                $data = $post->comments
                    ->sortByDesc('created_at')
                    ->take(100) 
                    ->map(function($comment) use ($getProfilePhotoUrl) {
                        return [
                            'id' => $comment->alumni->id ?? null,
                            'first_name' => $comment->alumni->first_name ?? 'Unknown',
                            'last_name' => $comment->alumni->last_name ?? '',
                            'profile_photo' => $getProfilePhotoUrl($comment->alumni),
                            'comment' => $comment->comment,
                            'created_at' => $comment->created_at ? $comment->created_at->toISOString() : null,
                        ];
                    })
                    ->values()
                    ->toArray();
                $total = $post->comments->count();
                break;
                
            case 'reposts':
                $data = $post->reposts
                    ->sortByDesc('created_at')
                    ->take(100) 
                    ->map(function($repost) use ($getProfilePhotoUrl) {
                        return [
                            'id' => $repost->alumni->id ?? null,
                            'first_name' => $repost->alumni->first_name ?? 'Unknown',
                            'last_name' => $repost->alumni->last_name ?? '',
                            'profile_photo' => $getProfilePhotoUrl($repost->alumni),
                            'caption' => $repost->caption,
                            'created_at' => $repost->created_at ? $repost->created_at->toISOString() : null,
                        ];
                    })
                    ->values()
                    ->toArray();
                $total = $post->reposts->count();
                break;
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid interaction type. Allowed: likes, comments, reposts.'
                ], 400);
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'type' => $type,
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Post not found.'
        ], 404);
    } catch (\Exception $e) {
        \Log::error('Error fetching post interactions: ' . $e->getMessage(), [
            'post_id' => $postId,
            'type' => $request->input('type', 'likes'),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to load interactions. Please try again.'
        ], 500);
    }
}

/**
 * Get full post data with images and comments for moderation modal
 */
public function getFullPost($id)
{
    try {
        $post = \App\Models\Post::with([
            'alumni',
            'images',
            'comments.alumni',
            'reactions'
        ])->findOrFail($id);
        
        // Format images with full URLs
        $post->images->each(function($image) {
            $imagePath = ltrim($image->image_path, '/');
            $supabaseUrl = config('filesystems.disks.s3.url', '');
            if (empty($supabaseUrl)) {
                $supabaseUrl = rtrim(config('services.supabase.url', ''), '/') . '/storage/v1/object/public/luminus_assets/';
            } else {
                $supabaseUrl = rtrim($supabaseUrl, '/') . '/';
            }
            $image->full_url = $supabaseUrl . $imagePath;
        });
        
        return response()->json([
            'success' => true,
            'post' => $post
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Post not found'
        ], 404);
    }
}

protected function requireCoordinator(Request $request): Admin
{
    $currentAdmin = $this->getAuthenticatedAdmin($request);

    if (!$currentAdmin || $currentAdmin->admin_role !== 'Coordinator') {
        if ($request->expectsJson() || $request->ajax()) {
            abort(response()->json([
                'error' => 'Unauthorized. Only Coordinators can perform this action.'
            ], 403));
        }
        abort(403, 'Only Coordinators can perform this action.');
    }

    return $currentAdmin;
}

/**
 * Resend the LumiNUs invitation email to an alumni who hasn't accessed their account.
 * Generates a new temporary password, updates Supabase, and sends the welcome email again.
 */
public function resendInvite(Request $request, $id)
{
    $alumnus = Alumni::findOrFail($id);

    // 🔒 Optional: prevent resending if they've already accessed their account
    if ($alumnus->last_login_at !== null) {
        return response()->json([
            'success' => false,
            'message' => $alumnus->first_name . ' has already accessed their account. Use Reset Password instead.'
        ], 400);
    }

    // Generate a new temporary password
    $temporaryPassword = $this->generateTemporaryPassword(10);

    // Update the local DB
    $alumnus->update([
        'password_hash'         => Hash::make($temporaryPassword),
        'needs_password_change' => true,
        'last_invite_sent_at'   => now(),
        'invite_count'          => ($alumnus->invite_count ?? 0) + 1,
    ]);

    // Update Supabase Auth credentials
    $authUpdated = $this->updateSupabaseAuthPassword(
        $alumnus->email,
        $temporaryPassword
    );

    if (!$authUpdated) {
        \Log::warning('Resend invite: Supabase password update failed', [
            'alumni_id' => $alumnus->id,
            'email'     => $alumnus->email,
        ]);
        // We continue anyway — the welcome email still goes out.
    }

    // Send the SAME welcome email used at account creation
    try {
        $service = new BrevoMailService();
        $htmlContent = view('emails.welcome-alumni', [
            'alumnus'           => $alumnus,
            'temporaryPassword' => $temporaryPassword,
        ])->render();

        $service->sendEmail(
            $alumnus->email,
            'Welcome to LumiNUs', // same subject as the original invite
            $htmlContent
        );

        \Log::info('Invite resent to alumni', [
            'alumni_id'    => $alumnus->id,
            'email'        => $alumnus->email,
            'invite_count' => $alumnus->invite_count,
            'auth_synced'  => $authUpdated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation resent to ' . $alumnus->email . '.',
        ]);

    } catch (\Throwable $e) {
        \Log::error('Failed to resend invitation email', [
            'alumni_id' => $alumnus->id,
            'email'     => $alumnus->email,
            'error'     => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Invite could not be sent. Please try again.'
        ], 500);
    }
}

/**
 * Upload card photos to EXISTING alumni only.
 * No new accounts, no data changes — just photo matching & upload.
 */
public function uploadPhotosOnly(Request $request)
{
    $photoMap = $this->buildPhotoMap($request);

    if (empty($photoMap)) {
        return response()->json([
            'success' => false,
            'message' => 'No valid photos found. Check filenames follow: SURNAME_FIRST NAME_MI.jpg',
        ], 400);
    }

    $results = [
        'matched' => 0,
        'overridden' => 0,
        'unmatched' => [],
        'errors' => [],
    ];

    // Cache all alumni (including trashed so we can restore photos even on archived accounts)
    $allAlumni = Alumni::withTrashed()
        ->select('id', 'first_name', 'middle_name', 'last_name', 'student_id_number', 'card_photo')
        ->get();

    // Build lookup: photoKey => alumni
    $alumniLookup = [];
    foreach ($allAlumni as $alumnus) {
        $key = $this->buildPhotoKey(
            $alumnus->last_name,
            $alumnus->first_name,
            $alumnus->middle_name
        );
        if ($key && !isset($alumniLookup[$key])) {
            $alumniLookup[$key] = $alumnus;
        }
    }

    foreach ($photoMap as $key => $photoInfo) {
        if (!isset($alumniLookup[$key])) {
            $results['unmatched'][] = basename($photoInfo['path']);
            @unlink($photoInfo['path']);
            continue;
        }

        $alumnus = $alumniLookup[$key];
        $hadExistingPhoto = !empty($alumnus->card_photo);

        $newUrl = $this->uploadCardPhotoToSupabase($alumnus, $photoInfo['path'], $photoInfo['ext']);

        if ($newUrl) {
            if ($hadExistingPhoto) {
                $this->deleteAlumniPhoto($alumnus->card_photo);
                $results['overridden']++;
            }
            $alumnus->update(['card_photo' => $newUrl]);
            $results['matched']++;
        } else {
            $results['errors'][] = "Upload failed for {$alumnus->student_id_number}";
        }
    }

    $messages = ["✅ {$results['matched']} photo(s) matched & uploaded."];
    if ($results['overridden'] > 0) {
        $messages[] = "♻️ {$results['overridden']} overrode existing photos.";
    }
    if (count($results['unmatched']) > 0) {
        $messages[] = "⚠️ " . count($results['unmatched']) . " unmatched: "
            . implode(', ', array_slice($results['unmatched'], 0, 5))
            . (count($results['unmatched']) > 5 ? '…' : '');
    }

    return response()->json([
        'success' => true,
        'results' => $results,
        'message' => implode(' ', $messages),
    ]);
}

    /**
     * Return per-alumni access status for the Access Tracker UI.
     *
     * Response shape:
     *   { success: true, alumni: [ { id, name, email, photo, has_accessed, last_login_at, last_active_at }, ... ] }
     */
    public function getAccessStatus(Request $request)
    {
        $alumni = Alumni::active()
            ->select('id', 'first_name', 'last_name', 'email', 'alumni_photo', 'card_photo', 'last_login_at', 'last_active_at')
            ->get()
            ->map(function ($a) {
                // Resolve a usable photo URL (same logic as the directory view)
                $photoPath = trim((string) ($a->alumni_photo ?: $a->card_photo));

                if ($photoPath === '') {
                    $photo = '/assets/FINAL-NULIPA.jpg';
                } elseif (preg_match('/^https?:\/\//i', $photoPath)) {
                    $photo = $photoPath;
                } elseif (str_starts_with($photoPath, '/storage/')) {
                    $photo = $photoPath;
                } elseif (str_starts_with($photoPath, 'storage/')) {
                    $photo = '/' . $photoPath;
                } elseif (str_starts_with($photoPath, '/')) {
                    $photo = $photoPath;
                } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                    $photo = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($photoPath, '/');
                } else {
                    $photo = asset('storage/' . ltrim($photoPath, '/'));
                }

                return [
                    'id'             => $a->id,
                    'name'           => trim(($a->first_name ?? '') . ' ' . ($a->last_name ?? '')),
                    'email'          => $a->email,
                    'photo'          => $photo,
                    'has_accessed'   => !is_null($a->last_login_at),
                    'last_login_at'  => $a->last_login_at,
                    'last_active_at' => $a->last_active_at,
                ];
            });

        return response()->json([
            'success' => true,
            'alumni'  => $alumni,
        ]);
    }

/**
 * Export alumni data — supports both CSV and PDF formats.
 */
public function exportAlumni(Request $request)
{
    $format = strtolower($request->query('format', 'csv'));

    if ($format === 'pdf') {
        return $this->exportAlumniPdf($request);
    }

    return $this->exportAlumniCsv($request);
}

/**
 * CSV Export — lightweight and opens in Excel/Sheets.
 */
protected function exportAlumniCsv(Request $request)
{
    return (new AlumniExport('all'))->toResponse($request);
}

protected function exportAlumniPdf(Request $request)
{
    // For statistics, use aggregated queries instead of loading all records
    $totalAlumni = Alumni::count();
    
    $byProgram = Alumni::select('program')
        ->selectRaw('COUNT(*) as count')
        ->whereNotNull('program')
        ->where('program', '!=', '')
        ->groupBy('program')
        ->orderByDesc('count')
        ->pluck('count', 'program');
    
    $byType = [
        'College' => Alumni::where('alumni_type', 'college')->count(),
        'SHS'     => Alumni::where('alumni_type', 'shs')->count(),
        'Unknown' => Alumni::whereNotIn('alumni_type', ['college', 'shs'])->count(),
    ];

    $byYear = Alumni::selectRaw("EXTRACT(YEAR FROM year_graduated) as year")
        ->selectRaw('COUNT(*) as count')
        ->whereNotNull('year_graduated')
        ->groupBy('year')
        ->orderByDesc('year')
        ->pluck('count', 'year');

    // For the view, stream the data using cursor()
    return response()->streamDownload(function () use ($byProgram, $byType, $byYear, $totalAlumni) {
        $alumni = Alumni::query()->orderBy('last_name')->orderBy('first_name')->cursor();
        
        echo view('exports.alumni-print', [
            'alumni'      => $alumni, // cursor, not collection
            'totalAlumni' => $totalAlumni,
            'byProgram'   => $byProgram,
            'byType'      => $byType,
            'byYear'      => $byYear,
            'generatedAt' => now(),
            'generatedBy' => session('admin_name', 'System Administrator'),
            'stats' => [
                'withEmail'  => Alumni::whereNotNull('email_hash')->where('email_hash', '!=', '')->count(),
                'active'     => Alumni::where('account_status', 1)->count(),
                'restricted' => Alumni::where('account_status', 0)->count(),
            ],
        ])->render();
    }, 'alumni-export.html');
}

/**
 * Recompute alumnis_base.alumni_type from the alumni's program rows.
 *
 * Rules (mirrors the tracer-form access model):
 *   - If a 'college' program row exists with enrollment_status = 'graduated'
 *         → alumni_type = 'college'   (full College alumnus; owes BOTH tracers)
 *   - Otherwise (no college row, or college row is enrolled/dropped)
 *         → alumni_type = 'shs'       (still SHS alumnus; owes SHS tracer only)
 *
 * If the alumnus never had an SHS row (direct college enrollee),
 * the first rule still applies because college-graduated wins.
 */
protected function recomputeAlumniType(Alumni $alumnus): void
{
    $hasGraduatedCollege = $alumnus->programs()
        ->where('program_type', 'college')
        ->where('enrollment_status', 'graduated')
        ->exists();

    $desiredType = $hasGraduatedCollege ? 'college' : 'shs';

    // Only write if it actually changed — keeps the audit log clean.
    if ($alumnus->alumni_type !== $desiredType) {
        $previous = $alumnus->alumni_type;

        $alumnus->update([
            'alumni_type' => $desiredType,
            // Only stamp promoted_to_college_at the first time we flip to college
            'promoted_to_college_at' => $desiredType === 'college'
                ? ($alumnus->promoted_to_college_at ?? now())
                : $alumnus->promoted_to_college_at,
        ]);

        \Log::info('alumni_type recomputed', [
            'alumni_id' => $alumnus->id,
            'from'      => $previous,
            'to'        => $desiredType,
            'reason'    => $hasGraduatedCollege
                ? 'college program graduated'
                : 'no graduated college program',
        ]);
    }
}

}

// This is the AdminController.php file