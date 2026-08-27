<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AplicacionWho5 extends Model
{
    use HasFactory;

    protected $table = 'aplicaciones_who5';

    protected $fillable = [
        'user_id',
        'fecha',
        'i1',
        'i2',
        'i3',
        'i4',
        'i5',
        'crudo',
        'escala',
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
            'crudo' => 'integer',
            'escala' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
