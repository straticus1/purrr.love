<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VRInteraction extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'session_id',
        'cat_id',
        'interaction_type',
        'target_type',
        'target_id',
        'position',
        'rotation',
        'force',
        'duration',
        'result_data',
        'created_at',
    ];
    
    protected $casts = [
        'position' => 'array',
        'rotation' => 'array',
        'force' => 'float',
        'duration' => 'float',
        'result_data' => 'array',
        'created_at' => 'datetime',
    ];
    
    public function session(): BelongsTo
    {
        return $this->belongsTo(VRSession::class, 'session_id');
    }
    
    public function cat(): BelongsTo
    {
        return $this->belongsTo(Cat::class);
    }
    
    public function getPosition3D(): array
    {
        return [
            'x' => $this->position['x'] ?? 0,
            'y' => $this->position['y'] ?? 0,
            'z' => $this->position['z'] ?? 0
        ];
    }
    
    public function getRotation3D(): array
    {
        return [
            'x' => $this->rotation['x'] ?? 0,
            'y' => $this->rotation['y'] ?? 0,
            'z' => $this->rotation['z'] ?? 0
        ];
    }
    
    public function calculateImpactForce(): float
    {
        return $this->force * $this->duration;
    }
    
    public function isPhysicsInteraction(): bool
    {
        return $this->interaction_type === 'physics';
    }
    
    public function isSocialInteraction(): bool
    {
        return $this->interaction_type === 'social';
    }
    
    public function isTrainingInteraction(): bool
    {
        return $this->interaction_type === 'training';
    }
}
