<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AplicacionMdi extends Model
{
    use HasFactory;

    protected $table = 'aplicaciones_mdi';

    protected $fillable = [
        'user_id',
        'fecha',
        'i1',
        'i2',
        'i3',
        'i4',
        'i5',
        'i6',
        'i7',
        'i8a',
        'i8b',
        'i9',
        'i10a',
        'i10b',
        'total',
        'nivel',
        'origen',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'i1' => 'integer',
            'i2' => 'integer',
            'i3' => 'integer',
            'i4' => 'integer',
            'i5' => 'integer',
            'i6' => 'integer',
            'i7' => 'integer',
            'i8a' => 'integer',
            'i8b' => 'integer',
            'i9' => 'integer',
            'i10a' => 'integer',
            'i10b' => 'integer',
            'total' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
