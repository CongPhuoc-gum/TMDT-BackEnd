<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'admin_id';
    public $incrementing = false;

    protected $fillable = [
        'admin_id',
        'role',
        'permissions',
        'last_login',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'last_login' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }
}