<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatGeneticProfile extends Model
{
    protected $fillable = [
        'cat_id',
        'genetic_markers',
        'trait_data',
        'mutation_history',
        'generation',
        'lineage_path',
    ];
    
    protected $casts = [
        'genetic_markers' => 'array',
        'trait_data' => 'array',
        'mutation_history' => 'array',
        'generation' => 'integer',
        'lineage_path' => 'array',
    ];
    
    public function cat(): BelongsTo
    {
        return $this->belongsTo(Cat::class);
    }
}
