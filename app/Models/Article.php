<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'author_name',
        'author_role',
        'category',
        'read_time',
        'color_tag',
        'excerpt',
        'content',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'date',
        ];
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->published_at ? Carbon::parse($this->published_at)->translatedFormat('d M, Y') : '';
    }
}
