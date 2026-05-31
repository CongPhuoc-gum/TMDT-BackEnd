<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $incrementing = false;

    protected $fillable = [
        'staff_id',
        'position',
        'department',
        'salary',
        'hire_date',
        'specializations',
        'availability',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'salary' => 'decimal:2',
            'availability' => 'boolean',
            'hire_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id', 'user_id');
    }
}