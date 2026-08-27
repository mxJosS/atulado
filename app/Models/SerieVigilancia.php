<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerieVigilancia extends Model
{
    use HasFactory;

    protected $table = 'series_vigilancia';

    protected $fillable = [
        'user_id',
        'base30',
        'movil7',
        'dias_silencio',
        'ultima_senal',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'base30' => 'float',
            'movil7' => 'float',
            'dias_silencio' => 'integer',
            'fecha' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
