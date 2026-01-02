<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Changelog extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'description',
        'image',
    ];

    protected static function booted(): void
    {
        static::creating(function (Changelog $changelog) {
            if (empty($changelog->slug)) {
                $slug = Str::slug($changelog->title);

                $count = static::where('slug', 'like', "{$slug}%")->count();

                $changelog->slug = $count ? "{$slug}-{$count}" : $slug;
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
