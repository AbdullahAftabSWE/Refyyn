<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static create(array $array)
 */
class Status extends Model
{
    protected $table = 'statuses';

    protected $fillable = [
        'name',
        'color',
        'show_on_roadmap',
    ];

    protected $casts = [
        'show_on_roadmap' => 'boolean',
    ];

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
