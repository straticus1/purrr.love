<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraitInheritancePattern extends Model
{
    protected $fillable = [
        'trait_name',
        'gene_markers',
        'mutation_rates',
        'description',
    ];
    
    protected $casts = [
        'gene_markers' => 'array',
        'mutation_rates' => 'array',
    ];
}
