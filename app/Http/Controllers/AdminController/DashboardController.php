<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Feedback;
use App\Models\FeedbackRead;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, $filterOrSlug = null)
    {
        $userId = Auth::id();

        $filter = null;
        $boardId = null;

        if ($filterOrSlug === 'unread') {
            $filter = 'unread';
        } elseif ($filterOrSlug) {
            $board = Board::where('slug', $filterOrSlug)->first();
            if ($board) {
                $boardId = $board->id;
            }
        } else {
            $filter = $request->query('filter');
            $boardId = $request->query('board_id');
        }

        $totalFeedbackCount = Feedback::count();

        $unreadCount = Feedback::whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('feedback_reads')
                    ->whereColumn('feedback_reads.feedback_id', 'feedback.id')
                    ->where('feedback_reads.user_id', $userId);
            })
            ->count();

        $feedbackQuery = Feedback::with([
                'author:id,name,avatar,email',
                'status:id,name,color',
                'board:id,slug,name,color'
            ])
            ->withCount(['upvotes', 'comments'])
            ->addSelect([
                'is_read' => FeedbackRead::selectRaw('1')
                    ->whereColumn('feedback_reads.feedback_id', 'feedback.id')
                    ->where('feedback_reads.user_id', $userId)
                    ->limit(1),
            ]);

        if ($filter === 'unread') {
            $feedbackQuery->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('feedback_reads')
                    ->whereColumn('feedback_reads.feedback_id', 'feedback.id')
                    ->where('feedback_reads.user_id', $userId);
            });
        }

        if ($boardId) {
            $feedbackQuery->where('board_id', $boardId);
        }

        $feedbacks = $feedbackQuery->latest()->get();

        $boards = Board::withCount('feedback')
            ->orderBy('id')
            ->get();

        $statuses = Status::get(['id', 'name', 'color']);

        return Inertia::render('Admin/Dashboard', [
            'feedbacks' => $feedbacks,
            'boards' => $boards,
            'statuses' => $statuses,
            'sidebarStats' => [
                'totalCount' => $totalFeedbackCount,
                'unreadCount' => $unreadCount,
            ],
            'currentFilter' => $filter,
            'currentBoardId' => $boardId ? (int) $boardId : null,
            'currentRoute' => 'admin.dashboard',
        ]);
    }

    public function boardFilter(Request $request, $slug)
    {
        return $this->index($request, $slug);
    }

    public function showRoadmap(Request $request)
    {
        $userId = Auth::id();

        $statuses = Status::orderBy('id')
            ->get(['id', 'name', 'color', 'show_on_roadmap']);

        $feedbacks = Feedback::whereHas('status', function ($query) {
                $query->where('show_on_roadmap', true);
            })
            ->with(['status:id,name,color'])
            ->withCount(['upvotes', 'comments'])
            ->latest()
            ->get();

        $feedbacksByStatus = $feedbacks->groupBy('status_id');

        $totalFeedbackCount = Feedback::count();
        $unreadCount = Feedback::whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('feedback_reads')
                    ->whereColumn('feedback_reads.feedback_id', 'feedback.id')
                    ->where('feedback_reads.user_id', $userId);
            })
            ->count();

        $boards = Board::withCount('feedback')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/Roadmap', [
            'statuses' => $statuses,
            'feedbacksByStatus' => $feedbacksByStatus,
            'boards' => $boards,
            'sidebarStats' => [
                'totalCount' => $totalFeedbackCount,
                'unreadCount' => $unreadCount,
            ],
            'currentRoute' => 'admin.roadmap',
        ]);
    }

    public function updateRoadmapVisibility(Request $request, Status $status)
    {
        $validated = $request->validate([
            'show_on_roadmap' => 'required|boolean',
        ]);

        $status->update($validated);

        return back();
    }
}
