<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';

    protected $fillable = [
        'package',
        'question_text',
        'choices',
        'extra_fields',
        'problem_label',
        'countermeasure_label',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'choices' => 'array',
        'extra_fields' => 'boolean',
        'is_active' => 'boolean',
    ];

    // scopes
    public function scopeForPackage($q, string $package)
    {
        return $q->where('package', $package);
    }

    public function scopeActiveOrdered($q)
    {
        return $q->where('is_active', true)->orderBy('display_order');
    }

    public static function getNextOrder(string $package): int
    {
        $last = self::where('package', $package)->max('display_order');

        return $last ? $last + 1 : 1;
    }

    protected static function booted()
    {

        // Saat CREATE
        static::creating(function ($question) {
            $max = self::where('package', $question->package)->max('display_order') ?? 0;
            $question->display_order = $max + 1;
        });

        // Saat UPDATE → hanya jika package berubah
        static::updating(function ($question) {
            if ($question->isDirty('package')) {
                $max = self::where('package', $question->package)->max('display_order') ?? 0;
                $question->display_order = $max + 1;
            }
        });
    }
}
