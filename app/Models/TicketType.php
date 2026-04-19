<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'adult_price',
        'has_child',
        'child_price',
    ];

    protected $casts = [
        'adult_price' => 'decimal:2',
        'has_child' => 'boolean',
        'child_price' => 'decimal:2',
    ];
}
