<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomRequest extends Model
{
    protected $table = 'custom_request';
    protected $primaryKey = 'custom_request_id';
    protected $fillable = ['customer_id', 'staff_id', 'palette_id', 'title', 'description', 'reference_image_url', 'budget', 'occasion', 'delivery_date', 'status', 'design_notes', 'quotation'];

    protected function casts(): array
    {
        return [
            'budget'        => 'decimal:2',
            'quotation'     => 'decimal:2',
            'delivery_date' => 'date',
        ];
    }
}
