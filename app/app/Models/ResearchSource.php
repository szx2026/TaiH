<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchSource extends Model
{
    protected $fillable = [
        'product_project_id',
        'platform',
        'custom_source_name',
        'url',
        'evidence_note',
        'captured_at',
        'created_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProductProject::class, 'product_project_id');
    }
}
