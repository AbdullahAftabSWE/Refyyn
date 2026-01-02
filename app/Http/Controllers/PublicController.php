<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Changelog;
use App\Models\Comment;
use App\Models\Feedback;
use App\Models\Status;
use App\Models\Upvote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function feedback()
    {
        $boards = Board::withCount('feedback')
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'color']);

        $statuses = Status::orderBy('id')
            ->get(['id', 'name', 'color']);

        $feedbacks = Feedback::with(['status:id,name,color', 'board:id,name,color', 'author:id,name,email,avatar'])
            ->withCount(['upvotes', 'comments'])
            ->latest()
            ->get();

        $upvotedIds = [];
        if (Auth::check()) {
            $upvotedIds = Upvote::where('user_id', Auth::id())
                ->whereIn('feedback_id', $feedbacks->pluck('id'))
                ->pluck('feedback_id')
                ->toArray();
        }

        return Inertia::render('Member/Feedback', [
            'boards' => $boards,
            'statuses' => $statuses,
            'feedbacks' => $feedbacks,
            'upvotedIds' => $upvotedIds,
        ]);
    }

    public function roadmap()
    {
        $statuses = Status::where('show_on_roadmap', true)
            ->orderBy('id')
            ->get(['id', 'name', 'color']);

        $feedbacks = Feedback::whereHas('status', function ($query) {
                $query->where('show_on_roadmap', true);
            })
            ->with(['status:id,name,color'])
            ->withCount(['upvotes', 'comments'])
            ->latest()
            ->get();

        $feedbacksByStatus = $feedbacks->groupBy('status_id');

        return Inertia::render('Member/Roadmap', [
            'statuses' => $statuses,
            'feedbacksByStatus' => $feedbacksByStatus,
        ]);
    }

    public function changelog()
    {
        $changelogs = Changelog::with('author:id,name,avatar')
            ->latest()
            ->get();

        return Inertia::render('Member/Changelog', [
            'changelogs' => $changelogs,
        ]);
    }

    public function showChangelog(Changelog $changelog)
    {
        $changelog->load('author:id,name,avatar');

        return Inertia::render('Member/ChangelogShow', [
            'changelog' => $changelog,
        ]);
    }

    public function showFeedback($slug)
    {
        $feedback = Feedback::where('slug', $slug)->firstOrFail();

        $feedback->load([
            'status:id,name,color',
            'board:id,name,color',
            'author:id,name,email,avatar',
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with([
                        'user:id,name,avatar',
                        'replies' => function ($q) {
                            $q->with('user:id,name,avatar')->oldest();
                        }
                    ])
                    ->oldest();
            }
        ]);

        $upvoteCount = $feedback->upvotes()->count();
        $upvoters = $feedback->upvotes()
            ->with('user:id,name,avatar')
            ->limit(10)
            ->get()
            ->pluck('user');

        return Inertia::render('Member/FeedbackShow', [
            'feedback' => $feedback,
            'upvoteCount' => $upvoteCount,
            'upvoters' => $upvoters,
            'hasUpvoted' => Auth::check() ? Upvote::where('feedback_id', $feedback->id)->where('user_id', Auth::id())->exists() : false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'board_id' => 'required|exists:boards,id',
        ]);

        $statusId = Status::first()?->id;

        $feedback = Feedback::create([
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'board_id' => $validated['board_id'],
            'status_id' => $statusId,
        ]);

        return redirect()->route('feedback.show', ['slug' => $feedback->slug]);
    }

    public function toggleUpvote(Feedback $feedback)
    {
        $userId = Auth::id();

        $existingUpvote = Upvote::where('feedback_id', $feedback->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingUpvote) {
            $existingUpvote->delete();
            $upvoted = false;
        } else {
            Upvote::create([
                'feedback_id' => $feedback->id,
                'user_id' => $userId,
            ]);
            $upvoted = true;
        }

        $newCount = $feedback->upvotes()->count();

        return response()->json([
            'success' => true,
            'upvoted' => $upvoted,
            'upvoteCount' => $newCount,
        ]);
    }

    public function storeComment(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        Comment::create([
            'feedback_id' => $feedback->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'internal' => false,
        ]);

        return back();
    }
}
