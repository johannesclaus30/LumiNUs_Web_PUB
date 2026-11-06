<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Event;
use App\Models\TracerResponse;
use App\Models\TracerForm;
use App\Models\Announcement;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostReport;
use App\Models\CommentReport;
use App\Models\EventRegistration;
use App\Models\Address;
use App\Models\Mentorship; 
use App\Models\TracerPhase;   
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with all analytics.
     */
    public function index()
    {
        // =========================================
        // 1. MODERATION DATA (Priority-based)
        // =========================================
        
        $reportedPosts = DB::table('post_reports')
        ->select(
            'post_reports.post_id as id',
            DB::raw('COUNT(post_reports.id) as report_count'),
            DB::raw('STRING_AGG(DISTINCT post_reports.reason, \', \') as report_reasons'),
            DB::raw('MAX(post_reports.created_at) as last_reported_at'),
            'posts.caption',
            'posts.alumni_id',
            'posts.created_at as post_created_at',
            'posts.moderation_status',
            'posts.is_hidden',
            'alumnis_base.first_name',         
            'alumnis_base.last_name',       
            'alumnis_base.account_status',     
        )
        ->leftJoin('posts', 'post_reports.post_id', '=', 'posts.id')
        ->leftJoin('alumnis_base', 'posts.alumni_id', '=', 'alumnis_base.id')  
        ->whereNotNull('post_reports.post_id')
        ->groupBy(
            'post_reports.post_id',
            'posts.caption',
            'posts.alumni_id',
            'posts.created_at',
            'posts.moderation_status',
            'posts.is_hidden',
            'alumnis_base.first_name',     
            'alumnis_base.last_name',          
            'alumnis_base.account_status',      
        )
        ->havingRaw('COUNT(post_reports.id) > 0')
        ->orderByRaw('COUNT(post_reports.id) DESC, MAX(post_reports.created_at) DESC')
        ->get()
        ->map(function($item) {
            return (object) [
                'id' => $item->id,
                'caption' => $item->caption ?? 'Post deleted or not found',
                'alumni_id' => $item->alumni_id,
                'account_status' => (int) ($item->account_status ?? 1),  // 👈 ADD THIS
                'alumni' => $item->alumni_id ? (object) [
                    'first_name' => $item->first_name ?? 'Unknown',
                    'last_name' => $item->last_name ?? '',
                    'account_status' => (int) ($item->account_status ?? 1),  // 👈 And here
                ] : null,
                'created_at' => $item->post_created_at ? Carbon::parse($item->post_created_at) : null,
                'moderation_status' => $item->moderation_status ?? 'unknown',
                'is_hidden' => (bool) ($item->is_hidden ?? false),
                'report_count' => (int)$item->report_count,
                'report_reasons' => $item->report_reasons,
                'last_reported_at' => $item->last_reported_at,
            ];
        });

        $reportedComments = DB::table('comment_reports')
        ->select(
            'comment_reports.comment_id as id',
            DB::raw('COUNT(comment_reports.id) as report_count'),
            DB::raw('STRING_AGG(DISTINCT comment_reports.reason, \', \') as report_reasons'),
            DB::raw('MAX(comment_reports.created_at) as last_reported_at'),
            'comments.comment',
            'comments.alumni_id',
            'comments.created_at as comment_created_at',
            'alumnis_base.first_name',          // 👈 CHANGED
            'alumnis_base.last_name',           // 👈 CHANGED
            'alumnis_base.account_status',      // 👈 CHANGED
            'posts.caption as post_caption'
        )
        ->leftJoin('comments', 'comment_reports.comment_id', '=', 'comments.id')
        ->leftJoin('alumnis_base', 'comments.alumni_id', '=', 'alumnis_base.id') // 👈 CHANGED
        ->leftJoin('posts', 'comments.post_id', '=', 'posts.id')
        ->whereNotNull('comment_reports.comment_id')
        ->groupBy(
            'comment_reports.comment_id',
            'comments.comment',
            'comments.alumni_id',
            'comments.created_at',
            'alumnis_base.first_name',          // 👈 CHANGED
            'alumnis_base.last_name',           // 👈 CHANGED
            'alumnis_base.account_status',      // 👈 CHANGED
            'posts.caption'
        )
        ->havingRaw('COUNT(comment_reports.id) > 0')
        ->orderByRaw('COUNT(comment_reports.id) DESC, MAX(comment_reports.created_at) DESC')
        ->get()
        ->map(function($item) {
            return (object) [
                'id' => $item->id,
                'comment' => $item->comment ?? 'Comment deleted or not found',
                'alumni_id' => $item->alumni_id,
                'account_status' => (int) ($item->account_status ?? 1),  // 👈 ADD
                'alumni' => $item->alumni_id ? (object) [
                    'first_name' => $item->first_name ?? 'Unknown',
                    'last_name' => $item->last_name ?? '',
                    'account_status' => (int) ($item->account_status ?? 1),  // 👈 And here
                ] : null,
                'created_at' => $item->comment_created_at ? Carbon::parse($item->comment_created_at) : null,
                'post_caption' => $item->post_caption,
                'report_count' => (int)$item->report_count,
                'report_reasons' => $item->report_reasons,
                'last_reported_at' => $item->last_reported_at,
            ];
        });

        // Total pending reports count
        $totalReports = PostReport::count() + CommentReport::count();

        // =========================================
        // FREQUENT VIOLATORS
        // =========================================

        $frequentViolators = DB::table('alumnis_base')
            ->select(
                'alumnis_base.id',
                'alumnis_base.first_name',
                'alumnis_base.last_name',
                'alumnis_base.email',
                'alumnis_base.alumni_photo',
                'alumnis_base.account_status',
                'alumnis_base.created_at',
                DB::raw('COUNT(DISTINCT post_reports.id) as post_reports'),
                DB::raw('COUNT(DISTINCT comment_reports.id) as comment_reports'),
                DB::raw('(COUNT(DISTINCT post_reports.id) + COUNT(DISTINCT comment_reports.id)) as total_reports'),
                DB::raw('STRING_AGG(DISTINCT post_reports.reason, \', \') as report_reasons')
            )
            ->leftJoin('posts', 'alumnis_base.id', '=', 'posts.alumni_id')
            ->leftJoin('post_reports', 'posts.id', '=', 'post_reports.post_id')
            ->leftJoin('comments', 'alumnis_base.id', '=', 'comments.alumni_id')
            ->leftJoin('comment_reports', 'comments.id', '=', 'comment_reports.comment_id')
            ->where(function($query) {
                $query->whereNotNull('post_reports.id')
                    ->orWhereNotNull('comment_reports.id');
            })
            ->groupBy(
                'alumnis_base.id',
                'alumnis_base.first_name',
                'alumnis_base.last_name',
                'alumnis_base.email',
                'alumnis_base.alumni_photo',
                'alumnis_base.account_status',
                'alumnis_base.created_at'
            )
            ->havingRaw('(COUNT(DISTINCT post_reports.id) + COUNT(DISTINCT comment_reports.id)) > 0')
            ->orderByRaw('(COUNT(DISTINCT post_reports.id) + COUNT(DISTINCT comment_reports.id)) DESC')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;
                return $item;
            });

        // =========================================
        // 2. USER ANALYTICS
        // =========================================

        $activity = $this->getUserActivityMetrics();
        $verifiedAlumniCount = Alumni::where('verification_status', 'verified')->count();
        $pendingVerificationCount = Alumni::where('verification_status', 'pending')->count();

        // User growth (last 12 months)
        $userGrowth = Alumni::select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'count' => (int)$item->count
            ]);

        // Alumni by program
        $alumniByProgram = Alumni::selectRaw('program, COUNT(*) as count')
            ->where('verification_status', 'verified')
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->groupBy('program')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Alumni by year graduated
        $alumniByYear = Alumni::selectRaw('EXTRACT(YEAR FROM year_graduated) as year, COUNT(*) as count')
            ->where('verification_status', 'verified')
            ->whereNotNull('year_graduated')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->limit(15)
            ->get()
            ->map(fn($item) => [
                'year' => (int)$item->year,
                'count' => (int)$item->count
            ]);

        // Alumni by type (SHS vs College)
        $alumniByType = Alumni::selectRaw('alumni_type, COUNT(*) as count')
            ->where('verification_status', 'verified')
            ->whereNotNull('alumni_type')
            ->groupBy('alumni_type')
            ->get();

        // Alumni by region (from addresses)
        $alumniByRegion = DB::table('addresses_base')
            ->selectRaw('region, COUNT(DISTINCT alumni_id) as count')
            ->groupBy('region')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
                    
        

        // =========================================
        // 3. TRACER ANALYTICS
        // =========================================

        $totalTracerResponses = TracerResponse::count();
        $completedTracerResponses = TracerResponse::where('status', 'completed')->count();
        $inProgressTracerResponses = TracerResponse::where('status', 'in_progress')->count();

        $alumniWithTracer = TracerResponse::where('status', 'completed')
            ->distinct('alumni_id')
            ->count('alumni_id');
        $tracerCompletionRate = $verifiedAlumniCount > 0 
            ? round(($alumniWithTracer / $verifiedAlumniCount) * 100, 1) 
            : 0;

        $tracerByForm = TracerForm::select(
                'tracer_forms.form_title',
                DB::raw('COUNT(tracer_responses.id) as response_count')
            )
            ->leftJoin('tracer_responses', 'tracer_forms.id', '=', 'tracer_responses.form_id')
            ->where('tracer_forms.status', 1)
            ->groupBy('tracer_forms.id', 'tracer_forms.form_title')
            ->orderBy('response_count', 'desc')
            ->get();

        $tracerOverTime = TracerResponse::select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'count' => (int)$item->count
            ]);

        // =========================================
        // 4. EVENT ANALYTICS
        // =========================================

        $totalEvents = Event::count();
        $activeEvents = Event::where('status', 1)
            ->where('end_date', '>=', now()->toDateString())
            ->count();
        $upcomingEvents = Event::where('status', 1)
            ->where('start_date', '>=', now()->toDateString())
            ->count();
        $totalRegistrations = EventRegistration::count();

        $topEvents = Event::select(
                'events.title',
                DB::raw('COUNT(event_registrations.id) as registration_count')
            )
            ->leftJoin('event_registrations', 'events.id', '=', 'event_registrations.event_id')
            ->where('events.status', 1)
            ->groupBy('events.id', 'events.title')
            ->orderBy('registration_count', 'desc')
            ->limit(5)
            ->get();

        $registrationsOverTime = EventRegistration::select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'count' => (int)$item->count
            ]);

        // =========================================
        // 5. RECENT ACTIVITIES
        // =========================================

        $recentAnnouncements = Announcement::where('status', 1)
            ->latest('created_at')
            ->take(3)
            ->get();

        $recentTracerForms = TracerForm::where('status', 1)
            ->latest('created_at')
            ->take(5)
            ->get();

        // =========================================
        // 6. ALUMNI LOCATIONS FOR MAP
        // =========================================

        $alumniLocations = DB::table('alumnis_base')
            ->select(
                'alumnis_base.id',
                'alumnis_base.first_name',
                'alumnis_base.last_name',
                'alumnis_base.alumni_photo',
                'addresses_base.latitude',
                'addresses_base.longitude',
                'addresses_base.address_type',
                'addresses_base.region',
                'addresses_base.province',
                'addresses_base.municipality',
                'addresses_base.barangay'
            )
            ->join('addresses_base', 'alumnis_base.id', '=', 'addresses_base.alumni_id')
            ->whereNotNull('addresses_base.latitude')
            ->whereNotNull('addresses_base.longitude')
            ->where('alumnis_base.verification_status', 'verified')
            ->where('alumnis_base.account_status', 1)
            ->orderBy('alumnis_base.id')
            ->orderBy('addresses_base.id')
            ->get()
            // 1. Group by alumni ID to get their primary address
            ->groupBy('id')
            // 2. Map and decrypt manually
            ->map(function($group) {
                $first = $group->first();
                
                // Manually decrypt using Laravel's Crypt helper
                $lat = null;
                $lng = null;
                try {
                    $lat = \Illuminate\Support\Facades\Crypt::decryptString($first->latitude);
                    $lng = \Illuminate\Support\Facades\Crypt::decryptString($first->longitude);
                } catch (\Exception $e) {
                    // If they are somehow already plaintext, use them as-is
                    $lat = $first->latitude;
                    $lng = $first->longitude;
                }

                $lat = (float) $lat;
                $lng = (float) $lng;

                // 3. Reject invalid/empty coordinates
                if ($lat === 0.0 || $lng === 0.0 || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                    return null;
                }

                return (object) [
                    'id' => (int)$first->id,
                    'first_name' => $first->first_name,
                    'last_name' => $first->last_name,
                    'alumni_photo' => $first->alumni_photo,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'address_type' => $first->address_type,
                    'region' => $first->region,
                    'province' => $first->province,
                    'municipality' => $first->municipality,
                    'barangay' => $first->barangay,
                ];
            })
            // 4. Remove nulls (invalid or empty coordinates)
            ->filter()
            ->values();

        // =========================================
        // 7. MENTORSHIP DATA
        // =========================================

        $mentorships = Mentorship::with('alumni')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($mentorship) {
                // Just pass through the raw date without parsing
                $mentorship->formatted_proposed_date = $mentorship->proposed_date;
                return $mentorship;
            });

        $totalMentorships = Mentorship::count();
        $pendingMentorships = Mentorship::where('status', 'pending')->count();
        $activeMentorships = Mentorship::where('status', 'active')->count();
        $completedMentorships = Mentorship::where('status', 'completed')->count();
        $declinedMentorships = Mentorship::where('status', 'declined')->count();

        // =========================================
        // 8. CHART DATA
        // =========================================

        $chartData = [
            'user_growth' => [
                'labels' => $userGrowth->pluck('month')->values()->toArray(),
                'data' => $userGrowth->pluck('count')->values()->toArray(),
            ],
            'alumni_by_program' => [
                'labels' => $alumniByProgram->pluck('program')->filter()->values()->toArray(),
                'data' => $alumniByProgram->pluck('count')->filter()->values()->toArray(),
            ],
            'alumni_by_year' => [
                'labels' => $alumniByYear->pluck('year')->filter()->values()->toArray(),
                'data' => $alumniByYear->pluck('count')->filter()->values()->toArray(),
            ],
            'tracer_over_time' => [
                'labels' => $tracerOverTime->pluck('month')->values()->toArray(),
                'data' => $tracerOverTime->pluck('count')->values()->toArray(),
            ],
            'tracer_by_form' => [
                'labels' => $tracerByForm->pluck('form_title')->filter()->values()->toArray(),
                'data' => $tracerByForm->pluck('response_count')->filter()->values()->toArray(),
            ],
            'registrations_over_time' => [
                'labels' => $registrationsOverTime->pluck('month')->values()->toArray(),
                'data' => $registrationsOverTime->pluck('count')->values()->toArray(),
            ],
            'top_events' => [
                'labels' => $topEvents->pluck('title')->filter()->values()->toArray(),
                'data' => $topEvents->pluck('registration_count')->filter()->values()->toArray(),
            ],
            'alumni_by_type' => [
                'labels' => $alumniByType->pluck('alumni_type')->filter()->values()->toArray(),
                'data' => $alumniByType->pluck('count')->filter()->values()->toArray(),
            ],
            'alumni_by_region' => [
                'labels' => $alumniByRegion->pluck('region')->filter()->values()->toArray(),
                'data' => $alumniByRegion->pluck('count')->filter()->values()->toArray(),
            ],
            // ✅ ADD MENTORSHIP STATUS CHART DATA
            'mentorship_status' => [
                'labels' => ['Pending', 'Active', 'Completed', 'Declined'],
                'data' => [
                    $pendingMentorships,
                    $activeMentorships,
                    $completedMentorships,
                    $declinedMentorships
                ]
            ],
            'activity_overview' => [
                'labels' => ['Posts', 'Comments', 'Reactions', 'Reposts', 'DMs', 'Group Msgs', 'Event Reg.'],
                'data'   => [
                    $activity['postsCount'],
                    $activity['commentsCount'],
                    $activity['reactionsCount'],
                    $activity['repostsCount'],
                    $activity['messagesCount'],
                    $activity['groupMessagesCount'],
                    $activity['registrationsCount'],
                ],
            ],
            'activity_breakdown' => [
                'labels' => ['Posted', 'Commented', 'Reacted', 'Messaged', 'Registered'],
                'data'   => [
                    $activity['usersWhoPosted'],
                    $activity['usersWhoCommented'],
                    $activity['usersWhoReacted'],
                    $activity['usersWhoMessaged'],
                    $activity['usersWhoRegistered'],
                ],
            ],
            'retention' => [
                'labels' => collect($activity['months'])->pluck('label')->toArray(),
                'data'   => collect($activity['months'])->pluck('count')->toArray(),
            ],
            'programs_activity' => [
                'labels' => $activity['programsActivity']->pluck('program')->toArray(),
                'data'   => $activity['programsActivity']->pluck('activity_per_user')->toArray(),
            ],
        ];

        // =========================================
        // 9. RETURN VIEW WITH ALL DATA
        // =========================================
        $tracerAnalytics = $this->getTracerPhaseAnalytics();
        $eventAnalytics    = $this->getEventAnalytics();

        return view('admin_dashboard', compact(
            'reportedPosts',
            'reportedComments',
            'totalReports',
            'verifiedAlumniCount',
            'pendingVerificationCount',
            'alumniByType',
            'alumniByRegion',
            'userGrowth',
            'totalTracerResponses',
            'completedTracerResponses',
            'inProgressTracerResponses',
            'tracerCompletionRate',
            'tracerByForm',
            'tracerOverTime',
            'totalEvents',
            'activeEvents',
            'upcomingEvents',
            'totalRegistrations',
            'topEvents',
            'registrationsOverTime',
            'recentAnnouncements',
            'recentTracerForms',
            'chartData',
            'alumniByProgram',
            'alumniByYear',
            'frequentViolators',
            'alumniLocations',
            'mentorships',
            'totalMentorships',
            'pendingMentorships',
            'activeMentorships',
            'completedMentorships',
            'declinedMentorships',
            'activity',
            'tracerAnalytics',
            'eventAnalytics' 
        ));
    }


    // ============================================
    // MODERATION ACTION METHODS
    // ============================================

    /**
     * Moderate a post (approve, hide, or delete)
     */
    public function moderatePost(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:posts,id',
            'action' => 'required|in:approve,hide,unhide,delete'
        ]);

        $post = Post::find($request->id);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        try {
            switch ($request->action) {
                case 'approve':
                    $post->moderation_status = 'approved';
                    $post->is_hidden = false;
                    PostReport::where('post_id', $post->id)->delete();
                    break;

                case 'hide':
                    $post->is_hidden = true;
                    // Keep moderation_status as-is or set to 'pending'
                    if ($post->moderation_status === 'approved') {
                        $post->moderation_status = 'pending';
                    }
                    break;

                case 'unhide':
                    $post->is_hidden = false;
                    $post->moderation_status = 'approved';
                    PostReport::where('post_id', $post->id)->delete();
                    break;

                case 'delete':
                    $post->delete();
                    PostReport::where('post_id', $request->id)->delete();
                    return response()->json([
                        'success' => true,
                        'message' => 'Post deleted successfully'
                    ]);
            }

            $post->save();

            return response()->json([
                'success' => true,
                'message' => 'Post moderated successfully',
                'is_hidden' => (bool) $post->is_hidden,
                'moderation_status' => $post->moderation_status,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Moderate a comment (approve or delete)
     */
    public function moderateComment(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:comments,id',
            'action' => 'required|in:approve,delete'
        ]);

        $comment = Comment::find($request->id);

        if (!$comment) {
            return response()->json(['success' => false, 'message' => 'Comment not found'], 404);
        }

        try {
            switch ($request->action) {
                case 'approve':
                    $comment->moderation_status = 'approved';
                    CommentReport::where('comment_id', $comment->id)->delete();
                    break;

                case 'delete':
                    $comment->delete();
                    CommentReport::where('comment_id', $request->id)->delete();
                    return response()->json([
                        'success' => true,
                        'message' => 'Comment deleted successfully'
                    ]);
            }

            $comment->save();

            return response()->json([
                'success' => true,
                'message' => 'Comment moderated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restrict/Suspend an alumni user
     */
    public function restrictUser(Request $request)
    {
        $request->validate([
            'alumni_id' => 'required|exists:alumnis,id'
        ]);

        $alumni = Alumni::find($request->alumni_id);

        if (!$alumni) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        try {
            $alumni->account_status = $alumni->account_status == 1 ? 0 : 1;
            $alumni->save();

            PostReport::where('reporter_id', $alumni->id)->delete();
            CommentReport::where('reporter_id', $alumni->id)->delete();

            $status = $alumni->account_status == 1 ? 'restored' : 'restricted';

            return response()->json([
                'success' => true,
                'message' => "User {$status} successfully",
                'status' => $alumni->account_status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a single post (for admin review) - Redirects to the dedicated view page
     */
    public function viewPost($id)
    {
        // Redirect to the dedicated view post page
        return redirect()->route('admin.posts.view', $id);
    }

    /**
     * View a single comment (for admin review)
     */
    public function viewComment($id)
    {
        $comment = Comment::with(['alumni', 'post'])
            ->find($id);

        if (!$comment) {
            abort(404, 'Comment not found');
        }

        $reports = CommentReport::with('reporter')
            ->where('comment_id', $id)
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'comment' => $comment,
                'reports' => $reports,
                'report_count' => $reports->count()
            ]);
        }

        return view('admin.comments.view', compact('comment', 'reports'));
    }

    /**
     * Get all reports for a specific post (AJAX)
     */
    public function getPostReports($id)
    {
        $reports = PostReport::with('reporter')
            ->where('post_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reports' => $reports,
            'count' => $reports->count()
        ]);
    }

    /**
     * Get all reports for a specific comment (AJAX)
     */
    public function getCommentReports($id)
    {
        $reports = CommentReport::with('reporter')
            ->where('comment_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reports' => $reports,
            'count' => $reports->count()
        ]);
    }

    /**
     * Bulk moderate multiple posts
     */
    public function bulkModeratePosts(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id',
            'action' => 'required|in:approve,hide,delete'
        ]);

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($request->post_ids as $postId) {
            try {
                $post = Post::find($postId);
                if (!$post) continue;

                switch ($request->action) {
                    case 'approve':
                        $post->moderation_status = 'approved';
                        $post->is_hidden = false;
                        PostReport::where('post_id', $postId)->delete();
                        break;
                    case 'hide':
                        $post->is_hidden = true;
                        $post->moderation_status = 'pending';
                        break;
                    case 'delete':
                        $post->delete();
                        PostReport::where('post_id', $postId)->delete();
                        break;
                }

                if ($request->action !== 'delete') {
                    $post->save();
                }
                $results['success']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Post ID {$postId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => "Processed {$results['success']} posts successfully, {$results['failed']} failed"
        ]);
    }

    /**
     * Bulk moderate multiple comments
     */
    public function bulkModerateComments(Request $request)
    {
        $request->validate([
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id',
            'action' => 'required|in:approve,delete'
        ]);

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($request->comment_ids as $commentId) {
            try {
                $comment = Comment::find($commentId);
                if (!$comment) continue;

                switch ($request->action) {
                    case 'approve':
                        $comment->moderation_status = 'approved';
                        CommentReport::where('comment_id', $commentId)->delete();
                        break;
                    case 'delete':
                        $comment->delete();
                        CommentReport::where('comment_id', $commentId)->delete();
                        break;
                }

                if ($request->action !== 'delete') {
                    $comment->save();
                }
                $results['success']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Comment ID {$commentId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => "Processed {$results['success']} comments successfully, {$results['failed']} failed"
        ]);
    }

    public function fetchCoordinates($barangay = null, $municipality = null, $province = null, $region = null, $zipCode = null)
    {
        $baseUrl = 'https://nominatim.openstreetmap.org/search';

        // Build progressive search queries from most specific to broad
        $attempts = [
            // 1. Street / Barangay + Municipality + Province + Region
            implode(', ', array_filter([$barangay, $municipality, $province, $region, $zipCode])),

            // 2. Municipality/City + Province/State
            implode(', ', array_filter([$municipality, $province])),

            // 3. Municipality/City + Region/Country
            implode(', ', array_filter([$municipality, $region])),

            // 4. Province/State or Region alone
            implode(', ', array_filter([$province, $region]))
        ];

        // Remove duplicates and empty strings
        $attempts = array_unique(array_filter($attempts));

        foreach ($attempts as $query) {
            $cleanQuery = trim($query);
            if (empty($cleanQuery)) continue;

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'LumiNUs-Alumni-Portal/1.0 (contact@luminus.edu.ph)'
                ])->timeout(5)->get($baseUrl, [
                    'q' => $cleanQuery,
                    'format' => 'json',
                    'limit' => 1,
                    // 'countrycodes' parameter removed to allow global searches
                ]);

                $data = $response->json();

                if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                    return [
                        'latitude' => (float)$data[0]['lat'],
                        'longitude' => (float)$data[0]['lon']
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning("Geocoding failed for query '{$cleanQuery}': " . $e->getMessage());
            }
        }

        return [
            'latitude' => 0,
            'longitude' => 0
        ];
    }

    /**
     * Display a single post for moderation preview
     */
    public function viewPostPage($id)
    {
        $post = \App\Models\Post::with([
            'alumni',
            'images',
            'comments.alumni',
            'reactions'
        ])->findOrFail($id);
        
        // Get report count and reasons (if any)
        $post->report_count = $post->reports()->count();
        $post->report_reasons = $post->reports()->pluck('reason')->implode(', ');
        $post->reported_at = $post->reports()->first()?->created_at;
        
        return view('view-post', compact('post'));
    }

    // ========== MENTORSHIP METHODS ==========
    
    public function getMentorship($id)
    {
        $mentorship = Mentorship::with('alumni')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $mentorship
        ]);
    }

    public function updateMentorshipStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,active,completed,declined'
        ]);

        $mentorship = Mentorship::findOrFail($id);
        $mentorship->status = $request->status;
        $mentorship->save();

        return response()->json([
            'success' => true,
            'message' => 'Mentorship status updated successfully'
        ]);
    }

    public function deleteMentorship($id)
    {
        $mentorship = Mentorship::findOrFail($id);
        $mentorship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mentorship application deleted successfully'
        ]);
    }

    public function refreshMentorships()
    {
        $mentorships = Mentorship::with('alumni')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mentorships
        ]);
    }

    public function viewMentorship($id)
    {
        // Try to find by UUID first, if that fails try by alumni_id
        try {
            $mentorship = Mentorship::with('alumni')->findOrFail($id);
        } catch (\Exception $e) {
            // If not found by UUID, try by alumni_id
            $mentorship = Mentorship::with('alumni')->where('alumni_id', $id)->firstOrFail();
        }
        
        return view('dashboard.mentorship_view', compact('mentorship'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'alumni_id' => 'required|exists:alumnis,id',
            'message' => 'required|string|min:1'
        ]);
        
        try {
            $alumni = Alumni::find($request->alumni_id);
            
            // Here you can implement your message sending logic
            // For example, save to a messages table, send email, etc.
            
            // Example: Save to a messages table (you'll need to create this)
            // Message::create([
            //     'alumni_id' => $request->alumni_id,
            //     'admin_id' => auth()->id(),
            //     'message' => $request->message,
            //     'sent_at' => now()
            // ]);
            
            // For now, we'll just log it and return success
            \Log::info('Message sent to alumni ' . $request->alumni_id . ': ' . $request->message);
            
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ], 500);
        }
    }

    private function safeFormatDate($dateString, $format = 'M d, Y')
    {
        if (empty($dateString)) {
            return null;
        }

        // If it doesn't look like a date, return as-is
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $dateString) && 
            !preg_match('/^\d{2}\/\d{2}\/\d{4}/', $dateString) &&
            !preg_match('/^\d{2}-\d{2}-\d{4}/', $dateString) &&
            !preg_match('/^\d{4}\/\d{2}\/\d{2}/', $dateString)) {
            return $dateString;
        }

        try {
            return Carbon::parse($dateString)->format($format);
        } catch (\Throwable $e) {
            return $dateString;
        }
    }

    // ============================================
// USER ACTIVITY ANALYTICS
// ============================================

/**
 * Compute user activity metrics for the dashboard.
 */
private function getUserActivityMetrics(): array
{
    $now          = Carbon::now();
    $sevenDaysAgo = $now->copy()->subDays(7);
    $thirtyDaysAgo = $now->copy()->subDays(30);
    $sixMonthsAgo = $now->copy()->subMonths(6);

    // ---------- A. Simple counters ----------
    $activeLast7Days = Alumni::where('last_login_at', '>=', $sevenDaysAgo)->count();
    $onlineNow       = Alumni::where('is_online', true)->count();
    $totalVerified   = Alumni::where('verification_status', 'verified')->count();

    // Dormant: verified users with no login in 30 days (or never)
    $dormantCount = Alumni::where('verification_status', 'verified')
        ->where(function ($q) use ($thirtyDaysAgo) {
            $q->whereNull('last_login_at')
              ->orWhere('last_login_at', '<', $thirtyDaysAgo);
        })
        ->count();

    // Average posts per verified user (all-time)
    $totalPosts = Post::whereNull('deleted_at')->count();
    $avgPostsPerUser = $totalVerified > 0
        ? round($totalPosts / $totalVerified, 2)
        : 0;

    // In getUserActivityMetrics() method - around line 1145 (the counters section)

    // ---------- B. Activity Overview (last 30 days) ----------
    $postsCount = Post::where('created_at', '>=', $thirtyDaysAgo)
        ->whereNull('deleted_at')
        ->count();

    $commentsCount = Comment::where('created_at', '>=', $thirtyDaysAgo)->count();

    $reactionsCount = DB::table('reactions')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->count();

    $repostsCount = DB::table('reposts')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->count();

    $messagesCount = DB::table('messages')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->count();

    $groupMessagesCount = DB::table('group_messages')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->count();

    $registrationsCount = DB::table('event_registrations')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->count();

    // ---------- C. Activity breakdown (unique users, last 30d) ----------
    $usersWhoPosted = Post::where('created_at', '>=', $thirtyDaysAgo)
        ->whereNull('deleted_at')
        ->distinct('alumni_id')
        ->count('alumni_id');

    $usersWhoCommented = Comment::where('created_at', '>=', $thirtyDaysAgo)
        ->distinct('alumni_id')
        ->count('alumni_id');

    $usersWhoReacted = DB::table('reactions')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->distinct('alumni_id')
        ->count('alumni_id');

    $usersWhoMessaged = DB::table('messages')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->where('sender_type', 'alumni')
        ->distinct('sender_id')
        ->count('sender_id');

    $usersWhoRegistered = DB::table('event_registrations')
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->distinct('alumni_id')
        ->count('alumni_id');
        
    // ---------- D. Top Contributors (RELIABLE VERSION) ----------
    $topContributors = Alumni::query()
        ->where('verification_status', 'verified')
        ->where('account_status', 1)
        ->select(['id', 'first_name', 'last_name', 'alumni_photo', 'program'])
        ->selectRaw("
            (SELECT COUNT(*) FROM posts p     WHERE p.alumni_id  = alumnis_base.id AND p.created_at  >= ? AND p.deleted_at IS NULL) +
            (SELECT COUNT(*) FROM comments c  WHERE c.alumni_id  = alumnis_base.id AND c.created_at  >= ?) +
            (SELECT COUNT(*) FROM reactions r WHERE r.alumni_id  = alumnis_base.id AND r.created_at  >= ?) +
            (SELECT COUNT(*) FROM reposts rp  WHERE rp.alumni_id = alumnis_base.id AND rp.created_at >= ?)
            AS activity_score
        ", [$thirtyDaysAgo, $thirtyDaysAgo, $thirtyDaysAgo, $thirtyDaysAgo])
        ->selectRaw("(SELECT COUNT(*) FROM posts p     WHERE p.alumni_id  = alumnis_base.id AND p.created_at  >= ? AND p.deleted_at IS NULL) AS post_count",   [$thirtyDaysAgo])
        ->selectRaw("(SELECT COUNT(*) FROM comments c  WHERE c.alumni_id  = alumnis_base.id AND c.created_at  >= ?) AS comment_count",  [$thirtyDaysAgo])
        ->selectRaw("(SELECT COUNT(*) FROM reactions r WHERE r.alumni_id  = alumnis_base.id AND r.created_at  >= ?) AS reaction_count", [$thirtyDaysAgo])
        ->orderByRaw('activity_score DESC')
        ->limit(5)
        ->get();

    // ---------- E. Dormant Users (top 10) ----------
    $dormantUsers = Alumni::where('verification_status', 'verified')
        ->where('account_status', 1)
        ->where(function ($q) use ($thirtyDaysAgo) {
            $q->whereNull('last_login_at')
              ->orWhere('last_login_at', '<', $thirtyDaysAgo);
        })
        ->orderByRaw('last_login_at NULLS FIRST')
        ->limit(10)
        ->get(['id', 'first_name', 'last_name', 'alumni_photo', 'email', 'last_login_at']);

    // ---------- F. Retention (6 months) ----------
    // For each month, count distinct users who did ANY activity
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $start = $now->copy()->subMonths($i)->startOfMonth();
        $end   = $now->copy()->subMonths($i)->endOfMonth();

        $ids = collect()
            ->merge(Post::whereBetween('created_at', [$start, $end])->pluck('alumni_id'))
            ->merge(Comment::whereBetween('created_at', [$start, $end])->pluck('alumni_id'))
            ->merge(DB::table('reactions')->whereBetween('created_at', [$start, $end])->pluck('alumni_id'))
            ->merge(DB::table('reposts')->whereBetween('created_at', [$start, $end])->pluck('alumni_id'))
            ->merge(DB::table('messages')->whereBetween('created_at', [$start, $end])->where('sender_type', 'alumni')->pluck('sender_id'))
            ->unique()
            ->filter();

        $months[] = [
            'label' => $start->format('M Y'),
            'count' => $ids->count(),
        ];
    }

    // ---------- G. Most Active Programs ----------
    $programsActivity = DB::table('alumnis_base')
        ->select(
            'program',
            DB::raw('COUNT(DISTINCT alumnis_base.id) as user_count'),
            DB::raw('(
                (SELECT COUNT(*) FROM posts p
                    WHERE p.alumni_id IN (
                        SELECT id FROM alumnis_base a2 WHERE a2.program = alumnis_base.program
                    )) +
                (SELECT COUNT(*) FROM comments c
                    WHERE c.alumni_id IN (
                        SELECT id FROM alumnis_base a3 WHERE a3.program = alumnis_base.program
                    ))
            ) as activity_total')
        )
        ->where('verification_status', 'verified')
        ->whereNotNull('program')
        ->where('program', '!=', '')
        ->groupBy('program')
        ->orderByDesc('activity_total')
        ->limit(8)
        ->get()
        ->map(function ($row) {
            $row->activity_per_user = $row->user_count > 0
                ? round($row->activity_total / $row->user_count, 2)
                : 0;
            return $row;
        });

    // ---------- H. Connection density ----------
    $usersWithFollowers = DB::table('followers')
        ->where('status', 1)
        ->distinct('followed_alumni_id')
        ->count('followed_alumni_id');

    $usersFollowing = DB::table('followers')
        ->where('status', 1)
        ->distinct('follower_alumni_id')
        ->count('follower_alumni_id');

    return compact(
        'activeLast7Days',
        'onlineNow',
        'dormantCount',
        'avgPostsPerUser',
        'postsCount',
        'commentsCount',
        'reactionsCount',
        'repostsCount',
        'messagesCount',
        'groupMessagesCount',
        'registrationsCount',
        'usersWhoPosted',
        'usersWhoCommented',
        'usersWhoReacted',
        'usersWhoMessaged',
        'usersWhoRegistered',
        'topContributors',
        'dormantUsers',
        'months',
        'programsActivity',
        'usersWithFollowers',
        'usersFollowing'
    );
}

 /**
     * Get comprehensive tracer phase analytics for the admin dashboard.
     */
    public function getTracerPhaseAnalytics()
    {
        // =========================================
        // 1. ACTIVE FORM (only 1 active)
        // =========================================
        $activeForm = TracerForm::where('status', 2)->first();
        
        if (!$activeForm) {
            return [
                'hasActiveForm' => false,
                'phases' => [],
                'summary' => [],
                'chartData' => [],
            ];
        }

        $formId = $activeForm->id;

        // =========================================
        // 2. PHASE-LEVEL ANALYTICS
        // =========================================
        $phases = TracerPhase::where('form_id', $formId)
            ->where('is_archived', false)
            ->orderBy('order_priority')
            ->get();

        $phaseAnalytics = $phases->map(function ($phase) use ($formId) {
            // Count alumni eligible for this phase based on target_alumni_type
            $eligibleQuery = Alumni::where('verification_status', 'verified')
                ->where('account_status', 1);

            if ($phase->target_alumni_type === 'shs') {
                $eligibleQuery->where('alumni_type', 'shs');
            } elseif ($phase->target_alumni_type === 'college') {
                $eligibleQuery->where('alumni_type', 'college');
            }
            // 'all' = no filter

            $eligibleCount = $eligibleQuery->count();

            // Count responses for this phase
            // Since responses are tied to form_id, we need to determine
            // which phase a response belongs to.
            // 
            // OPTION A: If you have a phase_id in tracer_responses (not in schema)
            // OPTION B: Use the questions answered to infer the phase
            // OPTION C: Use target_alumni_type matching on the responder's profile
            //
            // We'll use OPTION C for now — a response is "in" a phase if the
            // responder's alumni_type matches the phase's target_alumni_type
            // (or phase targets 'all').
            
            $responseQuery = TracerResponse::where('form_id', $formId);
            
            if ($phase->target_alumni_type === 'shs') {
                $responseQuery->whereHas('alumni', function ($q) {
                    $q->where('alumni_type', 'shs');
                });
            } elseif ($phase->target_alumni_type === 'college') {
                $responseQuery->whereHas('alumni', function ($q) {
                    $q->where('alumni_type', 'college');
                });
            }
            // 'all' = all responses count

            $totalResponses = (clone $responseQuery)->count();
            $completedResponses = (clone $responseQuery)
                ->where('status', 'completed')
                ->count();
            $inProgressResponses = (clone $responseQuery)
                ->where('status', 'in_progress')
                ->count();

            // Completion rate = completed / eligible
            $completionRate = $eligibleCount > 0
                ? round(($completedResponses / $eligibleCount) * 100, 1)
                : 0;

            // Response rate = (completed + in_progress) / eligible
            $responseRate = $eligibleCount > 0
                ? round((($completedResponses + $inProgressResponses) / $eligibleCount) * 100, 1)
                : 0;

            // Drop-off = in_progress / (completed + in_progress)
            $dropOffRate = ($completedResponses + $inProgressResponses) > 0
                ? round(($inProgressResponses / ($completedResponses + $inProgressResponses)) * 100, 1)
                : 0;

            return (object) [
                'id' => $phase->id,
                'title' => $phase->title,
                'subtitle' => $phase->subtitle,
                'icon' => $phase->icon ?? 'fa-user',
                'color' => $phase->color ?? '#3b82f6',
                'target_alumni_type' => $phase->target_alumni_type,
                'order_priority' => $phase->order_priority,
                'eligible_count' => $eligibleCount,
                'total_responses' => $totalResponses,
                'completed_responses' => $completedResponses,
                'in_progress_responses' => $inProgressResponses,
                'completion_rate' => $completionRate,
                'response_rate' => $responseRate,
                'drop_off_rate' => $dropOffRate,
            ];
        });

        // =========================================
        // 3. OVERALL SUMMARY
        // =========================================
        $totalEligible = Alumni::where('verification_status', 'verified')
            ->where('account_status', 1)
            ->count();

        $totalCompleted = TracerResponse::where('form_id', $formId)
            ->where('status', 'completed')
            ->count();

        $totalInProgress = TracerResponse::where('form_id', $formId)
            ->where('status', 'in_progress')
            ->count();

        $uniqueResponders = TracerResponse::where('form_id', $formId)
            ->distinct('alumni_id')
            ->count('alumni_id');

        $overallCompletionRate = $totalEligible > 0
            ? round(($totalCompleted / $totalEligible) * 100, 1)
            : 0;

        $overallResponseRate = $totalEligible > 0
            ? round((($totalCompleted + $totalInProgress) / $totalEligible) * 100, 1)
            : 0;

        $summary = (object) [
            'total_eligible' => $totalEligible,
            'total_completed' => $totalCompleted,
            'total_in_progress' => $totalInProgress,
            'unique_responders' => $uniqueResponders,
            'overall_completion_rate' => $overallCompletionRate,
            'overall_response_rate' => $overallResponseRate,
            'total_phases' => $phases->count(),
            'active_phases' => $phases->where('is_archived', false)->count(),
        ];

        // =========================================
        // 4. TARGET AUDIENCE BREAKDOWN
        // =========================================
        $audienceBreakdown = [
            'all' => (object) [
                'label' => 'General / All',
                'eligible' => Alumni::where('verification_status', 'verified')
                    ->where('account_status', 1)
                    ->count(),
                'completed' => TracerResponse::where('form_id', $formId)
                    ->where('status', 'completed')
                    ->count(),
            ],
            'shs' => (object) [
                'label' => 'SHS',
                'eligible' => Alumni::where('verification_status', 'verified')
                    ->where('account_status', 1)
                    ->where('alumni_type', 'shs')
                    ->count(),
                'completed' => TracerResponse::where('form_id', $formId)
                    ->where('status', 'completed')
                    ->whereHas('alumni', fn($q) => $q->where('alumni_type', 'shs'))
                    ->count(),
            ],
            'college' => (object) [
                'label' => 'College',
                'eligible' => Alumni::where('verification_status', 'verified')
                    ->where('account_status', 1)
                    ->where('alumni_type', 'college')
                    ->count(),
                'completed' => TracerResponse::where('form_id', $formId)
                    ->where('status', 'completed')
                    ->whereHas('alumni', fn($q) => $q->where('alumni_type', 'college'))
                    ->count(),
            ],
        ];

        // Calculate rates for each
        foreach ($audienceBreakdown as $key => &$aud) {
            $aud->completion_rate = $aud->eligible > 0
                ? round(($aud->completed / $aud->eligible) * 100, 1)
                : 0;
        }

        // =========================================
        // 5. RESPONSES OVER TIME (Last 6 months) - Overall
        // =========================================
        $responsesOverTime = TracerResponse::where('form_id', $formId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'count' => (int) $item->count,
            ]);

        // =========================================
        // 6. RESPONSES OVER TIME PER PHASE (Last 6 months)
        // =========================================
        $phaseTrends = [];
        foreach ($phases as $phase) {
            $trend = TracerResponse::where('form_id', $formId)
                ->where('created_at', '>=', Carbon::now()->subMonths(6));

            if ($phase->target_alumni_type === 'shs') {
                $trend->whereHas('alumni', fn($q) => $q->where('alumni_type', 'shs'));
            } elseif ($phase->target_alumni_type === 'college') {
                $trend->whereHas('alumni', fn($q) => $q->where('alumni_type', 'college'));
            }

            $trendData = $trend
                ->select(
                    DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                    DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get()
                ->map(fn($item) => [
                    'month' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                    'count' => (int) $item->count,
                ]);

            $phaseTrends[] = [
                'phase_title' => $phase->title,
                'phase_color' => $phase->color ?? '#3b82f6',
                'data' => $trendData,
            ];
        }

        // =========================================
        // 7. COMPLETION FUNNEL (Started → Completed)
        // =========================================
        $funnelData = [
            'eligible' => $totalEligible,
            'started' => $totalInProgress + $totalCompleted,
            'completed' => $totalCompleted,
        ];

        // =========================================
        // 8. PHASE COMPARISON (for grouped bar chart)
        // =========================================
        $phaseComparison = $phaseAnalytics->map(fn($p) => [
            'phase' => $p->title,
            'eligible' => $p->eligible_count,
            'completed' => $p->completed_responses,
            'in_progress' => $p->in_progress_responses,
            'completion_rate' => $p->completion_rate,
        ])->values();

        // =========================================
        // 9. TOP & BOTTOM PERFORMING PHASES
        // =========================================
        $sortedByCompletion = $phaseAnalytics->sortByDesc('completion_rate')->values();
        $topPhases = $sortedByCompletion->take(3);
        $needsAttention = $sortedByCompletion->reverse()->take(3);

        // =========================================
        // 10. RECENT SUBMISSIONS (last 10)
        // =========================================
        $recentSubmissions = TracerResponse::with('alumni')
            ->where('form_id', $formId)
            ->where('status', 'completed')
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($r) => (object) [
                'id' => $r->id,
                'alumni_name' => $r->alumni
                    ? $r->alumni->first_name . ' ' . $r->alumni->last_name
                    : 'Unknown',
                'alumni_photo' => $r->alumni
                ? $this->resolveAlumniPhotoUrl($r->alumni->alumni_photo ?: $r->alumni->card_photo)
                : null,
                'alumni_type' => $r->alumni->alumni_type ?? 'N/A',
                'program' => $r->alumni->program ?? 'N/A',
                'submitted_at' => $r->submitted_at,
                'time_ago' => $r->submitted_at
                    ? Carbon::parse($r->submitted_at)->diffForHumans()
                    : 'N/A',
            ]);

        // =========================================
        // 11. CHART DATA AGGREGATION
        // =========================================
        $chartData = [
            'responses_over_time' => [
                'labels' => $responsesOverTime->pluck('month')->values()->toArray(),
                'data' => $responsesOverTime->pluck('count')->values()->toArray(),
            ],
            'phase_trends' => $phaseTrends,
            'phase_comparison' => [
                'labels' => $phaseComparison->pluck('phase')->toArray(),
                'eligible' => $phaseComparison->pluck('eligible')->toArray(),
                'completed' => $phaseComparison->pluck('completed')->toArray(),
                'in_progress' => $phaseComparison->pluck('in_progress')->toArray(),
                'completion_rate' => $phaseComparison->pluck('completion_rate')->toArray(),
            ],
            'funnel' => [
                'labels' => ['Eligible', 'Started', 'Completed'],
                'data' => [
                    $funnelData['eligible'],
                    $funnelData['started'],
                    $funnelData['completed'],
                ],
            ],
            'audience_breakdown' => [
                'labels' => ['General / All', 'SHS', 'College'],
                'eligible' => [
                    $audienceBreakdown['all']->eligible,
                    $audienceBreakdown['shs']->eligible,
                    $audienceBreakdown['college']->eligible,
                ],
                'completed' => [
                    $audienceBreakdown['all']->completed,
                    $audienceBreakdown['shs']->completed,
                    $audienceBreakdown['college']->completed,
                ],
                'completion_rate' => [
                    $audienceBreakdown['all']->completion_rate,
                    $audienceBreakdown['shs']->completion_rate,
                    $audienceBreakdown['college']->completion_rate,
                ],
            ],
            'phase_completion_rates' => [
                'labels' => $phaseAnalytics->pluck('title')->values()->toArray(),
                'data' => $phaseAnalytics->pluck('completion_rate')->values()->toArray(),
                'colors' => $phaseAnalytics->pluck('color')->values()->toArray(),
            ],
            'drop_off_rates' => [
                'labels' => $phaseAnalytics->pluck('title')->values()->toArray(),
                'data' => $phaseAnalytics->pluck('drop_off_rate')->values()->toArray(),
            ],
        ];

        $collegeEmployment = $this->getCollegeEmploymentAnalytics();
        $shsCollegeChoice   = $this->getShsCollegeChoiceAnalytics();

        return [
            'hasActiveForm' => true,
            'activeForm' => $activeForm,
            'phases' => $phaseAnalytics,
            'summary' => $summary,
            'audienceBreakdown' => $audienceBreakdown,
            'phaseTrends' => $phaseTrends,
            'phaseComparison' => $phaseComparison,
            'topPhases' => $topPhases,
            'needsAttention' => $needsAttention,
            'recentSubmissions' => $recentSubmissions,
            'chartData' => $chartData,
            'collegeEmployment' => $collegeEmployment,   // 👈 NEW
            'shsCollegeChoice' => $shsCollegeChoice,     // 👈 NEW

        ];
    }

    /**
 * Resolve an alumni photo path into a usable URL.
 * Matches the pattern used in AdminController::getAccessStatus().
 */
protected function resolveAlumniPhotoUrl(?string $photoPath): string
{
    $photoPath = trim((string) $photoPath);

    // No photo → default NU LIPA avatar
    if ($photoPath === '') {
        return '/assets/FINAL-NULIPA.jpg';
    }

    // Already a full URL
    if (preg_match('/^https?:\/\//i', $photoPath)) {
        return $photoPath;
    }

    // /storage/... → use as-is
    if (str_starts_with($photoPath, '/storage/')) {
        return $photoPath;
    }

    // storage/... → prefix with /
    if (str_starts_with($photoPath, 'storage/')) {
        return '/' . $photoPath;
    }

    // Leading slash → use as-is
    if (str_starts_with($photoPath, '/')) {
        return $photoPath;
    }

    // Supabase / S3 URL exists → use it
    $s3Url = trim((string) config('filesystems.disks.s3.url'));
    if ($s3Url !== '') {
        return rtrim($s3Url, '/') . '/' . ltrim($photoPath, '/');
    }

    // Local fallback
    return asset('storage/' . ltrim($photoPath, '/'));
}

/**
 * Get comprehensive event analytics data for the dashboard.
 */
public function getEventAnalytics()
{
    // ============================================
    // 1. CORE KPIs
    // ============================================
    $totalEvents = \App\Models\Event::count();
    $activeEvents = \App\Models\Event::where('status', 1)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->count();
    $upcomingEvents = \App\Models\Event::where('status', 1)
        ->where('start_date', '>', now())
        ->count();
    $completedEvents = \App\Models\Event::where('end_date', '<', now())->count();
    $cancelledEvents = \App\Models\Event::where('status', 0)->count();

    $totalRegistrations = \App\Models\EventRegistration::count();

    // ============================================
    // 2. AVG REGISTRATIONS PER EVENT
    // ============================================
    $avgRegistrationsPerEvent = $totalEvents > 0
        ? round($totalRegistrations / $totalEvents, 1)
        : 0;

    // ============================================
    // 3. OVERALL FILL RATE (registrations vs total capacity)
    // ============================================
    $totalCapacity = \App\Models\Event::sum('max_capacity');
    $fillRate = $totalCapacity > 0
        ? round(($totalRegistrations / $totalCapacity) * 100, 1)
        : 0;

    // ============================================
    // 4. REGISTRATIONS OVER TIME (last 12 months)
    // ============================================
    $registrationsOverTime = \App\Models\EventRegistration::selectRaw("
            TO_CHAR(created_at, 'YYYY-MM') as month,
            COUNT(*) as count
        ")
        ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->pluck('count', 'month')
        ->toArray();

    // Fill in missing months with 0
    $regLabels = [];
    $regData = [];
    for ($i = 11; $i >= 0; $i--) {
        $month = now()->subMonths($i)->format('Y-m');
        $regLabels[] = now()->subMonths($i)->format('M Y');
        $regData[] = $registrationsOverTime[$month] ?? 0;
    }

    // ============================================
    // 5. TOP PERFORMING EVENTS (by registration count)
    // ============================================
    $topEvents = \App\Models\Event::withCount('registrations')
        ->orderByDesc('registrations_count')
        ->take(8)
        ->get()
        ->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => \Str::limit($event->title, 30),
                'full_title' => $event->title,
                'registrations' => $event->registrations_count,
                'capacity' => $event->max_capacity,
                'fill_rate' => $event->max_capacity > 0
                    ? round(($event->registrations_count / $event->max_capacity) * 100, 1)
                    : 0,
                'start_date' => $event->start_date,
                'status' => $event->status,
            ];
        });

    // ============================================
    // 6. EVENT TYPE BREAKDOWN
    // ============================================
    $eventTypeBreakdown = \App\Models\Event::selectRaw('event_type, COUNT(*) as count')
        ->whereNotNull('event_type')
        ->groupBy('event_type')
        ->get()
        ->pluck('count', 'event_type')
        ->toArray();

    // ============================================
    // 7. EVENT STATUS DISTRIBUTION
    // ============================================
    $statusDistribution = [
        'Active' => $activeEvents,
        'Upcoming' => $upcomingEvents,
        'Completed' => $completedEvents,
        'Cancelled' => $cancelledEvents,
    ];

    // ============================================
    // 8. MONTHLY EVENT CREATION (last 12 months)
    // ============================================
    $monthlyEvents = \App\Models\Event::selectRaw("
            TO_CHAR(created_at, 'YYYY-MM') as month,
            COUNT(*) as count
        ")
        ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->pluck('count', 'month')
        ->toArray();

    $eventLabels = [];
    $eventData = [];
    for ($i = 11; $i >= 0; $i--) {
        $month = now()->subMonths($i)->format('Y-m');
        $eventLabels[] = now()->subMonths($i)->format('M Y');
        $eventData[] = $monthlyEvents[$month] ?? 0;
    }

    // ============================================
    // 9. TOP PROGRAMS ATTENDING EVENTS
    // ============================================
    $topPrograms = \App\Models\EventRegistration::join('alumnis_base', 'event_registrations.alumni_id', '=', 'alumnis_base.id')
        ->selectRaw('alumnis_base.program, COUNT(*) as count')
        ->whereNotNull('alumnis_base.program')
        ->groupBy('alumnis_base.program')
        ->orderByDesc('count')
        ->take(8)
        ->get()
        ->pluck('count', 'program')
        ->toArray();

    // ============================================
    // 10. CAPACITY & FILL RATE ANALYSIS
    // ============================================
    $capacityAnalysis = \App\Models\Event::withCount('registrations')
        ->where('status', 1)
        ->where('end_date', '>=', now())
        ->orderBy('start_date')
        ->take(10)
        ->get()
        ->map(function ($event) {
            return [
                'title' => \Str::limit($event->title, 25),
                'capacity' => $event->max_capacity,
                'registered' => $event->registrations_count,
                'fill_rate' => $event->max_capacity > 0
                    ? round(($event->registrations_count / $event->max_capacity) * 100, 1)
                    : 0,
            ];
        });

    // ============================================
    // 11. RECENT REGISTRATION ACTIVITY
    // ============================================
    $recentRegistrations = \App\Models\EventRegistration::with(['event', 'alumni'])
        ->latest('created_at')
        ->take(10)
        ->get()
        ->map(function ($reg) {
            // Resolve the alumni's profile photo (falls back to card_photo, then default)
            $photoPath = null;
            if ($reg->alumni) {
                $photoPath = $reg->alumni->alumni_photo ?: $reg->alumni->card_photo;
            }

            return [
                'alumni_id'     => $reg->alumni_id,
                'alumni_name'   => $reg->alumni
                    ? trim($reg->alumni->first_name . ' ' . $reg->alumni->last_name)
                    : 'Unknown Alumni',
                'alumni_photo'  => $this->resolveAlumniPhotoUrl($photoPath),   // 👈 NEW
                'event_title'   => $reg->event ? $reg->event->title : 'Unknown Event',
                'event_id'      => $reg->event_id,
                'registered_at' => $reg->created_at,
                'time_ago'      => $reg->created_at->diffForHumans(),
            ];
        });

    // ============================================
    // 12. GROWTH INDICATORS (compare vs last month)
    // ============================================
    $thisMonth = \App\Models\EventRegistration::whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
    $lastMonth = \App\Models\EventRegistration::whereMonth('created_at', now()->subMonth()->month)
        ->whereYear('created_at', now()->subMonth()->year)
        ->count();

    $registrationGrowth = $lastMonth > 0
        ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
        : ($thisMonth > 0 ? 100 : 0);

    // ============================================
    // 13. FULL EVENT PERFORMANCE TABLE
    // ============================================
    $eventPerformance = \App\Models\Event::withCount('registrations')
        ->latest('start_date')
        ->take(50)
        ->get()
        ->map(function ($event) {
            $fillRate = $event->max_capacity > 0
                ? round(($event->registrations_count / $event->max_capacity) * 100, 1)
                : 0;

            // Determine status
            $status = 'Upcoming';
            if ($event->status == 0) {
                $status = 'Cancelled';
            } elseif ($event->end_date < now()) {
                $status = 'Completed';
            } elseif ($event->start_date <= now() && $event->end_date >= now()) {
                $status = 'Active';
            }

            return [
                'id' => $event->id,
                'title' => $event->title,
                'event_type' => $event->event_type ?? 'N/A',
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'capacity' => $event->max_capacity,
                'registered' => $event->registrations_count,
                'fill_rate' => $fillRate,
                'status' => $status,
            ];
        });

    // ============================================
    // ASSEMBLE EVERYTHING
    // ============================================
    return [
        'summary' => [
            'total_events' => $totalEvents,
            'active_events' => $activeEvents,
            'upcoming_events' => $upcomingEvents,
            'completed_events' => $completedEvents,
            'cancelled_events' => $cancelledEvents,
            'total_registrations' => $totalRegistrations,
            'avg_registrations_per_event' => $avgRegistrationsPerEvent,
            'overall_fill_rate' => $fillRate,
            'registration_growth' => $registrationGrowth,
            'this_month_registrations' => $thisMonth,
        ],
        'charts' => [
            'registrations_over_time' => [
                'labels' => $regLabels,
                'data' => $regData,
            ],
            'top_events' => [
                'labels' => $topEvents->pluck('title')->toArray(),
                'full_titles' => $topEvents->pluck('full_title')->toArray(),
                'data' => $topEvents->pluck('registrations')->toArray(),
                'fill_rates' => $topEvents->pluck('fill_rate')->toArray(),
            ],
            'event_type_breakdown' => [
                'labels' => array_keys($eventTypeBreakdown),
                'data' => array_values($eventTypeBreakdown),
            ],
            'status_distribution' => [
                'labels' => array_keys($statusDistribution),
                'data' => array_values($statusDistribution),
            ],
            'monthly_events' => [
                'labels' => $eventLabels,
                'data' => $eventData,
            ],
            'top_programs' => [
                'labels' => array_keys($topPrograms),
                'data' => array_values($topPrograms),
            ],
            'capacity_analysis' => $capacityAnalysis->toArray(),
        ],
        'recent_registrations' => $recentRegistrations,
        'event_performance' => $eventPerformance,
    ];
}

private function getCollegeEmploymentAnalytics(): array
{
    // Total verified college alumni (no account_status filter)
    $totalCollegeAlumni = Alumni::where('alumni_type', 'college')
        ->where('verification_status', 'verified')
        ->count();

    // Get ALL employment records
    $allEmployment = DB::table('alumni_employments')
        ->join('alumnis_base', 'alumni_employments.alumni_id', '=', 'alumnis_base.id')
        ->where('alumnis_base.alumni_type', 'college')
        ->where('alumnis_base.verification_status', 'verified')
        ->select(
            'alumni_employments.id',
            'alumni_employments.alumni_id',
            'alumni_employments.job_title',
            'alumni_employments.company',
            'alumni_employments.location',
            'alumni_employments.start_date',
            'alumni_employments.end_date',
            'alumni_employments.is_current',
            'alumni_employments.created_at',
            'alumnis_base.program as alumni_program',   
            'alumnis_base.first_name',                
            'alumnis_base.last_name'                   
        )
        ->orderBy('alumni_employments.start_date', 'desc')
        ->get();

    // Per alumni, pick the most recent record (prefer is_current)
    $currentEmployment = $allEmployment
        ->groupBy('alumni_id')
        ->map(function ($group) {
            return $group->firstWhere('is_current', true) ?? $group->first();
        })
        ->values();

    // Distinct alumni with ANY employment record
    $employedAlumniIds = $allEmployment->pluck('alumni_id')->unique();
    $employedCount = $employedAlumniIds->count();

    // Distinct alumni with a CURRENT employment record
    $currentlyEmployedIds = $allEmployment->where('is_current', true)->pluck('alumni_id')->unique();
    $currentlyEmployedCount = $currentlyEmployedIds->count();

    $unemployedCount = max(0, $totalCollegeAlumni - $employedCount);

    $employmentRate = $totalCollegeAlumni > 0
        ? round(($employedCount / $totalCollegeAlumni) * 100, 1)
        : 0;

    $topEmployers = $currentEmployment
        ->groupBy(fn($r) => $r->company ?: 'Unspecified')
        ->map(fn($group, $company) => (object) [
            'company' => $company,
            'count' => $group->count(),
        ])
        ->sortByDesc('count')
        ->take(8)
        ->values();

    $topJobTitles = $currentEmployment
        ->groupBy(fn($r) => $r->job_title ?: 'Unspecified')
        ->map(fn($group, $title) => (object) [
            'job_title' => $title,
            'count' => $group->count(),
        ])
        ->sortByDesc('count')
        ->take(8)
        ->values();

    $topLocations = $currentEmployment
        ->groupBy(fn($r) => $r->location ?: 'Unspecified')
        ->map(fn($group, $loc) => (object) [
            'location' => $loc,
            'count' => $group->count(),
        ])
        ->sortByDesc('count')
        ->take(8)
        ->values();

    // Employment by program
    $employmentByProgram = DB::table('alumnis_base')
        ->where('alumnis_base.alumni_type', 'college')
        ->where('alumnis_base.verification_status', 'verified')
        ->whereNotNull('alumnis_base.program')
        ->where('alumnis_base.program', '!=', '')
        ->select(
            'alumnis_base.program',
            DB::raw('COUNT(DISTINCT alumnis_base.id) as total_alumni'),
            DB::raw('COUNT(DISTINCT CASE
                WHEN EXISTS (
                    SELECT 1 FROM alumni_employments ae WHERE ae.alumni_id = alumnis_base.id
                ) THEN alumnis_base.id END) as employed_alumni')
        )
        ->groupBy('alumnis_base.program')
        ->get()
        ->map(function ($row) {
            $row->employment_rate = $row->total_alumni > 0
                ? round(($row->employed_alumni / $row->total_alumni) * 100, 1)
                : 0;
            return $row;
        })
        ->sortByDesc('employment_rate')
        ->values();

    // Recently added employment records
    $recentlyAdded = DB::table('alumni_employments')
        ->join('alumnis_base', 'alumni_employments.alumni_id', '=', 'alumnis_base.id')
        ->where('alumnis_base.alumni_type', 'college')
        ->where('alumnis_base.verification_status', 'verified')
        ->orderBy('alumni_employments.created_at', 'desc')
        ->limit(5)
        ->select(
            'alumnis_base.first_name',
            'alumnis_base.last_name',
            'alumnis_base.alumni_photo',
            'alumni_employments.company',
            'alumni_employments.job_title',
            'alumni_employments.created_at'
        )
        ->get();

    return [
        'has_data' => $totalCollegeAlumni > 0,
        'total_college_alumni' => $totalCollegeAlumni,
        'total_with_employment_records' => $employedCount,
        'currently_employed' => $currentlyEmployedCount,
        'unemployed_count' => $unemployedCount,
        'employment_rate' => $employmentRate,
        'status_breakdown' => [
            'Currently Employed' => $currentlyEmployedCount,
            'Has Employment History' => max(0, $employedCount - $currentlyEmployedCount),
            'No Employment Records' => $unemployedCount,
        ],
        'top_employers' => $topEmployers,
        'top_job_titles' => $topJobTitles,
        'top_locations' => $topLocations,
        'employment_by_program' => $employmentByProgram,
        'recently_added' => $recentlyAdded,
    ];
}

/**
 * SHS Alumni College Choices — sourced from `alumni_programs` + `promoted_to_college_at`.
 */
private function getShsCollegeChoiceAnalytics(): array
{
    // Base: all verified SHS alumni (no account_status filter)
    $totalShsAlumni = Alumni::where('alumni_type', 'shs')
        ->where('verification_status', 'verified')
        ->count();

    // SHS alumni who were promoted to college
    $promotedToCollege = Alumni::where('alumni_type', 'shs')
        ->where('verification_status', 'verified')
        ->whereNotNull('promoted_to_college_at')
        ->count();

    $withCollegeProgram = DB::table('alumnis_base')
        ->where('alumnis_base.alumni_type', 'shs')
        ->where('alumnis_base.verification_status', 'verified')
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('alumni_programs')
                ->whereColumn('alumni_programs.alumni_id', 'alumnis_base.id')
                ->where('alumni_programs.program_type', 'college');
        })
        ->count();

    $promotedViaPrograms = DB::table('alumnis_base')
        ->where('alumnis_base.alumni_type', 'college')
        ->where('alumnis_base.verification_status', 'verified')
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('alumni_programs')
                ->whereColumn('alumni_programs.alumni_id', 'alumnis_base.id')
                ->where('alumni_programs.program_type', 'shs');
        })
        ->count();

    $continuedCount = max($promotedToCollege, $withCollegeProgram, $promotedViaPrograms);

    // Total pool = current SHS alumni + anyone who was ever SHS
    $totalEverShs = $totalShsAlumni + $promotedViaPrograms;

    $continuationRate = $totalEverShs > 0
        ? round(($continuedCount / $totalEverShs) * 100, 1)
        : 0;

    $nuLipaCount = $continuedCount;
    $otherSchoolCount = 0;

    $nuLipaRate = $continuedCount > 0 ? 100.0 : 0;

    // Top college programs chosen by former SHS alumni
    $topCollegePrograms = DB::table('alumni_programs')
        ->join('alumnis_base', 'alumni_programs.alumni_id', '=', 'alumnis_base.id')
        ->where('alumnis_base.verification_status', 'verified')
        ->where('alumni_programs.program_type', 'college')
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('alumni_programs as ap_shs')
                ->whereColumn('ap_shs.alumni_id', 'alumnis_base.id')
                ->where('ap_shs.program_type', 'shs');
        })
        ->whereNotNull('alumni_programs.program_name')
        ->where('alumni_programs.program_name', '!=', '')
        ->select(
            'alumni_programs.program_name',
            DB::raw('COUNT(DISTINCT alumni_programs.alumni_id) as count')
        )
        ->groupBy('alumni_programs.program_name')
        ->orderByDesc('count')
        ->limit(10)
        ->get();

    // Enrollment status
    $enrollmentStatus = DB::table('alumni_programs')
        ->join('alumnis_base', 'alumni_programs.alumni_id', '=', 'alumnis_base.id')
        ->where('alumnis_base.verification_status', 'verified')
        ->where('alumni_programs.program_type', 'college')
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('alumni_programs as ap_shs')
                ->whereColumn('ap_shs.alumni_id', 'alumnis_base.id')
                ->where('ap_shs.program_type', 'shs');
        })
        ->whereNotNull('alumni_programs.enrollment_status')
        ->select(
            'alumni_programs.enrollment_status',
            DB::raw('COUNT(DISTINCT alumni_programs.alumni_id) as count')
        )
        ->groupBy('alumni_programs.enrollment_status')
        ->get();

    // Promotion timeline
    $promotionTimeline = Alumni::whereNotNull('promoted_to_college_at')
        ->where('verification_status', 'verified')
        ->select(
            DB::raw("EXTRACT(YEAR FROM promoted_to_college_at) as year"),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('year')
        ->orderBy('year')
        ->get()
        ->map(fn($row) => (object) [
            'year' => (int) $row->year,
            'count' => (int) $row->count,
        ]);

    // Recently promoted
    $recentlyPromoted = Alumni::whereNotNull('promoted_to_college_at')
        ->where('verification_status', 'verified')
        ->orderBy('promoted_to_college_at', 'desc')
        ->limit(5)
        ->get(['id', 'first_name', 'last_name', 'alumni_photo', 'program', 'promoted_to_college_at'])
        ->map(function ($a) {
            $a->alumni_photo = $this->resolveAlumniPhotoUrl($a->alumni_photo);
            return $a;
        });

    // Did NOT continue
    $didNotContinue = Alumni::where('alumni_type', 'shs')
        ->where('verification_status', 'verified')
        ->whereNull('promoted_to_college_at')
        ->whereDoesntHave('programs', function ($q) {
            $q->where('program_type', 'college');
        })
        ->count();

    return [
        'has_data' => $totalEverShs > 0,
        'total_shs_alumni' => $totalEverShs,
        'continued_count' => $continuedCount,
        'did_not_continue_count' => $didNotContinue,
        'continuation_rate' => $continuationRate,
        'nu_lipa_count' => $nuLipaCount,
        'other_school_count' => $otherSchoolCount,
        'nu_lipa_rate' => $nuLipaRate,
        'top_college_programs' => $topCollegePrograms,
        'enrollment_status' => $enrollmentStatus,
        'promotion_timeline' => $promotionTimeline,
        'recently_promoted' => $recentlyPromoted,
    ];
}

/**
 * Helper: find a question whose text matches any of the given keywords
 */
private function findQuestionByKeywords($questions, array $keywords)
{
    foreach ($questions as $q) {
        $text = strtolower($q->question_text ?? '');
        foreach ($keywords as $kw) {
            if (str_contains($text, strtolower($kw))) {
                return $q;
            }
        }
    }
    return null;
}

}