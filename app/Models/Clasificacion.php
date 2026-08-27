<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clasificacion extends Model
{
    use HasFactory;

    protected $table = 'clasificaciones';

    protected $fillable = [
        'user_id',
        'fecha',
        'nivel', // 'VERDE', 'AMARILLO', 'NARANJA', 'ROJO', 'ROJO_AGUDO'
        'origen',
        'banderas',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'banderas' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
