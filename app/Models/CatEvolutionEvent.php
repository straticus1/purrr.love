<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatEvolutionEvent extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'cat_id',
        'event_type',
        'event_data',
        'experience_gain',
        'adaptations',
        'mutations',
        'new_stage',
        'created_at',
    ];
    
    protected $casts = [
        'event_data' => 'array',
        'experience_gain' => 'integer',
        'adaptations' => 'array',
        'mutations' => 'array',
        'new_stage' => 'integer',
        'created_at' => 'datetime',
    ];
    
    public function cat(): BelongsTo
    {
        return $this->belongsTo(Cat::class);
    }
}
