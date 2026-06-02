<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OccasionReminder extends Model
{
    protected $table = 'occasion_reminder';
    protected $primaryKey = 'reminder_id';
    public $timestamps = false;
    protected $fillable = ['customer_id', 'occasion_name', 'occurrence_date', 'reminder_days_before', 'is_recurring', 'message'];

    protected function casts(): array
    {
        return [
            'occurrence_date'      => 'date',
            'reminder_days_before' => 'integer',
            'is_recurring'         => 'boolean'
        ];
    }
}
