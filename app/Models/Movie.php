<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'show_start_date',
        'show_end_date',
        'show_times',
        'booking_window_days',
        'cover_image_path',
        'booking_charge',
        'is_published',
        'is_upcoming',
        'published_at',
        'book_now',
        'category_id',
        'language_id',
    ];
        public function language()
        {
            return $this->belongsTo(Language::class);
        }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected $casts = [
        'show_start_date' => 'date',
        'show_end_date' => 'date',
        'show_times' => 'array',
        'booking_window_days' => 'integer',
        'booking_charge' => 'decimal:2',
        'is_published' => 'boolean',
        'is_upcoming' => 'boolean',
        'published_at' => 'datetime',
        'book_now' => 'boolean',
    ];

    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->cover_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->cover_image_path);
    }
}
