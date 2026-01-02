<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @method static create(array $array)
 * @method static where(string $string, $id)
 */
class Board extends Model
{
    protected $table = 'boards';

    protected $fillable = [
        'color',
        'name',
        'slug',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (Board $board) {
            if (empty($board->slug)) {
                $slug = Str::slug($board->name);

                $count = static::where('slug', 'like', "{$slug}%")->count();

                $board->slug = $count ? "{$slug}-{$count}" : $slug;
            }
        });
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
