<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @method static create(array $array)
 * @method static where(string $string, $id)
 */
class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'description',
        'board_id',
        'status_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Feedback $feedback) {
            $baseSlug = Str::slug($feedback->title);
            $slug = $baseSlug;
            $count = 1;

            while (static::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$count}";
                $count++;
            }

            $feedback->slug = $slug;
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function upvotes(): HasMany
    {
        return $this->hasMany(Upvote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(FeedbackRead::class);
    }

    public function isReadBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->reads()->where('user_id', $user->id)->exists();
    }
}
