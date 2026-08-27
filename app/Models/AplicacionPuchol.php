<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AplicacionPuchol extends Model
{
    use HasFactory;

    protected $table = 'aplicaciones_puchol';

    protected $fillable = [
        'user_id',
        'fecha',
        'fisio',
        'cogn',
        'motora',
        'emoc',
        'alteradas',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fisio' => 'integer',
            'cogn' => 'integer',
            'motora' => 'integer',
            'emoc' => 'integer',
            'alteradas' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
