<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quote extends Model
{
    protected $fillable = ['text', 'author', 'unique_hash', 'source_key'];

    public function favoredByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorite_quotes');
    }
}
