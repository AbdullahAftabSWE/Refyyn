<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Comment;
use App\Models\Feedback;
use App\Models\FeedbackRead;
use App\Models\Status;
use App\Models\Upvote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function show($slug)
    {
        $feedback = Feedback::where('slug', $slug)->firstOrFail();
        $userId = Auth::id();

        $feedback->load([
            'author',
            'board',
            'status',
            'upvotes.user',
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['user', 'replies.user'])
                    ->orderByDesc('pinned')
                    ->orderBy('created_at', 'desc');
            }
        ]);

        $upvoters = $feedback->upvotes()
            ->join('users', 'upvotes.user_id', '=', 'users.id')
            ->select('users.*')
            ->get();

        $statuses = Status::get(['id', 'name', 'color']);

        $boards = Board::withCount('feedback')
            ->orderBy('id')
            ->get();

        $hasUpvoted = Upvote::where('feedback_id', $feedback->id)
            ->where('user_id', $userId)
            ->exists();

        $totalFeedbackCount = Feedback::count();
        $unreadCount = Feedback::whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('feedback_reads')
                    ->whereColumn('feedback_reads.feedback_id', 'feedback.id')
                    ->where('feedback_reads.user_id', $userId);
            })
            ->count();

        return inertia('Admin/RequestDetail', [
            'feedback' => $feedback,
            'upvoteCount' => $feedback->upvotes->count(),
            'upvoters' => $upvoters,
            'statuses' => $statuses,
            'boards' => $boards,
            'hasUpvoted' => $hasUpvoted,
            'sidebarStats' => [
                'totalCount' => $totalFeedbackCount,
                'unreadCount' => $unreadCount,
            ],
        ]);
    }

    public function update(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'status_id' => 'sometimes|exists:statuses,id',
            'board_id' => 'sometimes|exists:boards,id',
        ]);

        $feedback->update($validated);

        return back()->with('success', 'Feedback updated successfully');
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->comments()->delete();
        $feedback->upvotes()->delete();
        $feedback->delete();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Feedback deleted successfully');
    }

    public function markAsRead(Feedback $feedback)
    {
        $userId = Auth::id();

        FeedbackRead::firstOrCreate(
            [
                'feedback_id' => $feedback->id,
                'user_id' => $userId,
            ],
            [
                'read_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
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

        $comment = Comment::create([
            'feedback_id' => $feedback->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'internal' => true,
        ]);

        $comment->load('user');

        return back();
    }

    public function destroyComment(Feedback $feedback, Comment $comment)
    {
        if ($comment->feedback_id !== $feedback->id) {
            return response()->json(['error' => 'Invalid comment'], 400);
        }

        $comment->replies()->delete();
        $comment->delete();

        return back();
    }

    public function toggleCommentPin(Feedback $feedback, Comment $comment)
    {
        if ($comment->feedback_id !== $feedback->id) {
            return response()->json(['error' => 'Invalid comment'], 400);
        }

        $comment->update([
            'pinned' => !$comment->pinned
        ]);

        return response()->json([
            'success' => true,
            'pinned' => $comment->pinned,
        ]);
    }
}
