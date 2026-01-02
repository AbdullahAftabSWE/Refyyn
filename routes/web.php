<?php

use App\Http\Controllers\AdminController\ChangelogController;
use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\FeedbackController;
use App\Http\Controllers\AdminController\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// Public Routes (Feedback Board)
Route::get('/', fn() => redirect('/feedback'));

Route::get('/feedback', [PublicController::class, 'feedback'])->name('feedback');
Route::get('/feedback/{slug}', [PublicController::class, 'showFeedback'])->name('feedback.show');
Route::post('/feedback', [PublicController::class, 'store'])->middleware('auth')->name('feedback.store');
Route::post('/feedback/{feedback}/upvote', [PublicController::class, 'toggleUpvote'])->middleware('auth')->name('feedback.upvote');
Route::post('/feedback/{feedback}/comment', [PublicController::class, 'storeComment'])->middleware('auth')->name('feedback.comment.store');

// Public Roadmap & Changelog
Route::get('/roadmap', [PublicController::class, 'roadmap'])->name('roadmap');
Route::get('/changelog', [PublicController::class, 'changelog'])->name('changelog');
Route::get('/changelog/{changelog:slug}', [PublicController::class, 'showChangelog'])->name('changelog.show');

// Admin Dashboard (Protected)
Route::prefix('/admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/unread', [DashboardController::class, 'index'])->name('admin.unread');
    Route::get('/board/{slug}', [DashboardController::class, 'boardFilter'])->name('admin.board');

    // Feedback management
    Route::get('/feedback/{slug}', [FeedbackController::class, 'show'])->name('admin.feedback.show');
    Route::patch('/feedback/{feedback}', [FeedbackController::class, 'update'])->name('admin.feedback.update');
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('admin.feedback.destroy');
    Route::post('/feedback/{feedback}/read', [FeedbackController::class, 'markAsRead'])->name('admin.feedback.read');
    Route::post('/feedback/{feedback}/upvote', [FeedbackController::class, 'toggleUpvote'])->name('admin.feedback.upvote');
    Route::post('/feedback/{feedback}/comment', [FeedbackController::class, 'storeComment'])->name('admin.feedback.comment.store');
    Route::patch('/feedback/{feedback}/comment/{comment}/pin', [FeedbackController::class, 'toggleCommentPin'])->name('admin.feedback.comment.pin');
    Route::delete('/feedback/{feedback}/comment/{comment}', [FeedbackController::class, 'destroyComment'])->name('admin.feedback.comment.destroy');

    // Roadmap
    Route::get('/roadmap', [DashboardController::class, 'showRoadmap'])->name('admin.roadmap');
    Route::patch('/roadmap/status/{status}', [DashboardController::class, 'updateRoadmapVisibility'])->name('admin.roadmap.status.update');

    // Changelog
    Route::get('/changelog', [ChangelogController::class, 'index'])->name('admin.changelog');
    Route::get('/changelog/create', [ChangelogController::class, 'create'])->name('admin.changelog.create');
    Route::post('/changelog', [ChangelogController::class, 'store'])->name('admin.changelog.store');
    Route::get('/changelog/{changelog:slug}', [ChangelogController::class, 'show'])->name('admin.changelog.show');
    Route::get('/changelog/{changelog:slug}/edit', [ChangelogController::class, 'edit'])->name('admin.changelog.edit');
    Route::patch('/changelog/{changelog}', [ChangelogController::class, 'update'])->name('admin.changelog.update');
    Route::delete('/changelog/{changelog}', [ChangelogController::class, 'destroy'])->name('admin.changelog.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');
    Route::post('/settings/boards', [SettingsController::class, 'storeBoard'])->name('admin.settings.boards.store');
    Route::patch('/settings/boards/{board}', [SettingsController::class, 'updateBoard'])->name('admin.settings.boards.update');
    Route::delete('/settings/boards/{board}', [SettingsController::class, 'destroyBoard'])->name('admin.settings.boards.destroy');
    Route::post('/settings/statuses', [SettingsController::class, 'storeStatus'])->name('admin.settings.statuses.store');
    Route::patch('/settings/statuses/{status}', [SettingsController::class, 'updateStatus'])->name('admin.settings.statuses.update');
    Route::delete('/settings/statuses/{status}', [SettingsController::class, 'destroyStatus'])->name('admin.settings.statuses.destroy');
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
