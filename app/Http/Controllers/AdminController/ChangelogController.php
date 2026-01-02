<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Changelog;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChangelogController extends Controller
{
    private function getSidebarData($userId)
    {
        $totalFeedbackCount = Feedback::count();
        $unreadCount = Feedback::whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('feedback_reads')
                    ->whereColumn('feedback_reads.feedback_id', 'feedback.id')
                    ->where('feedback_reads.user_id', $userId);
            })
            ->count();

        $boards = Board::withCount('feedback')->get();

        return [
            'boards' => $boards,
            'sidebarStats' => [
                'totalCount' => $totalFeedbackCount,
                'unreadCount' => $unreadCount,
            ],
        ];
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $changelogs = Changelog::with('author:id,name,avatar')
            ->latest()
            ->get();

        $sidebarData = $this->getSidebarData($userId);

        return Inertia::render('Admin/Changelog', [
            'changelogs' => $changelogs,
            'boards' => $sidebarData['boards'],
            'sidebarStats' => $sidebarData['sidebarStats'],
            'currentRoute' => 'admin.changelog',
        ]);
    }

    public function create(Request $request)
    {
        $userId = Auth::id();
        $sidebarData = $this->getSidebarData($userId);

        return Inertia::render('Admin/ChangelogCreate', [
            'boards' => $sidebarData['boards'],
            'sidebarStats' => $sidebarData['sidebarStats'],
            'currentRoute' => 'admin.changelog',
        ]);
    }

    public function show(Request $request, Changelog $changelog)
    {
        $userId = Auth::id();

        $changelog->load('author:id,name,avatar');

        $sidebarData = $this->getSidebarData($userId);

        return Inertia::render('Admin/ChangelogShow', [
            'changelog' => $changelog,
            'boards' => $sidebarData['boards'],
            'sidebarStats' => $sidebarData['sidebarStats'],
            'currentRoute' => 'admin.changelog',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $cleanDescription = clean($validated['description']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('changelogs', 'public');
        }

        $changelog = Changelog::create([
            'author_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $cleanDescription,
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.changelog.show', ['changelog' => $changelog->slug]);
    }

    public function edit(Request $request, Changelog $changelog)
    {
        $userId = Auth::id();
        $sidebarData = $this->getSidebarData($userId);

        return Inertia::render('Admin/ChangelogEdit', [
            'changelog' => $changelog,
            'boards' => $sidebarData['boards'],
            'sidebarStats' => $sidebarData['sidebarStats'],
            'currentRoute' => 'admin.changelog',
        ]);
    }

    public function update(Request $request, Changelog $changelog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        $cleanDescription = clean($validated['description']);

        if ($request->boolean('remove_image') && $changelog->image) {
            Storage::disk('public')->delete($changelog->image);
            $changelog->image = null;
        }

        if ($request->hasFile('image')) {
            if ($changelog->image) {
                Storage::disk('public')->delete($changelog->image);
            }
            $changelog->image = $request->file('image')->store('changelogs', 'public');
        }

        $changelog->title = $validated['title'];
        $changelog->description = $cleanDescription;
        $changelog->save();

        return redirect()->route('admin.changelog.show', ['changelog' => $changelog->slug]);
    }

    public function destroy(Request $request, Changelog $changelog)
    {
        if ($changelog->image) {
            Storage::disk('public')->delete($changelog->image);
        }

        $changelog->delete();

        return redirect()->route('admin.changelog');
    }
}
