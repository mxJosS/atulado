<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TopicArea extends Model
{
    use HasFactory;

    protected $table = 'topic_areas';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'topic_area_id');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->where('status', 'published')->whereNotNull('published_at');
    }
}
