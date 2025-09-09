<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatEvolutionData extends Model
{
    protected $fillable = [
        'cat_id',
        'experience_points',
        'evolution_stage',
        'adaptations',
        'mutations',
    ];
    
    protected $casts = [
        'experience_points' => 'integer',
        'evolution_stage' => 'integer',
        'adaptations' => 'array',
        'mutations' => 'array',
    ];
    
    public function cat(): BelongsTo
    {
        return $this->belongsTo(Cat::class);
    }
}
