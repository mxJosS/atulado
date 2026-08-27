<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AplicacionAsq extends Model
{
    use HasFactory;

    protected $table = 'aplicaciones_asq';

    protected $fillable = [
        'user_id',
        'fecha',
        'p1',
        'p2',
        'p3',
        'p4',
        'p5',
        'metodo',
        'fecha_intento',
        'resultado',
        'nivel',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
