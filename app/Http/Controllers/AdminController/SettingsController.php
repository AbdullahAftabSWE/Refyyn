<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Feedback;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SettingsController extends Controller
{
    private function getSidebarStats($userId)
    {
        $totalCount = Feedback::count();
        $unreadCount = Feedback::whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('feedback_reads')
                    ->whereColumn('feedback_reads.feedback_id', 'feedback.id')
                    ->where('feedback_reads.user_id', $userId);
            })
            ->count();

        return [
            'totalCount' => $totalCount,
            'unreadCount' => $unreadCount,
        ];
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $boards = Board::withCount('feedback')
            ->orderBy('id')
            ->get();

        $statuses = Status::withCount('feedback')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/Settings', [
            'boards' => $boards,
            'statuses' => $statuses,
            'sidebarStats' => $this->getSidebarStats($userId),
            'currentRoute' => 'admin.settings',
        ]);
    }

    public function storeBoard(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
        ]);

        Board::create([
            'name' => $validated['name'],
            'color' => $validated['color'],
        ]);

        return back();
    }

    public function updateBoard(Request $request, Board $board)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
        ]);

        $board->update($validated);

        return back();
    }

    public function destroyBoard(Request $request, Board $board)
    {
        if ($board->feedback()->count() > 0) {
            return back()->withErrors(['board' => 'Cannot delete board with existing feedback.']);
        }

        $board->delete();

        return back();
    }

    public function storeStatus(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'show_on_roadmap' => 'boolean',
        ]);

        Status::create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'show_on_roadmap' => $validated['show_on_roadmap'] ?? true,
        ]);

        return back();
    }

    public function updateStatus(Request $request, Status $status)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'show_on_roadmap' => 'boolean',
        ]);

        $status->update($validated);

        return back();
    }

    public function destroyStatus(Request $request, Status $status)
    {
        if ($status->feedback()->count() > 0) {
            return back()->withErrors(['status' => 'Cannot delete status with existing feedback.']);
        }

        $status->delete();

        return back();
    }
}
