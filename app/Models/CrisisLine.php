<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrisisLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'country',
        'country_code',
        'phone_number',
        'service_name',
        'hours',
        'cost_type',
        'description',
        'whatsapp_or_chat_url',
        'is_featured',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'order_index' => 'integer',
        ];
    }
}
