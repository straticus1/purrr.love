<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VRSession extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'environment_id',
        'cat_id',
        'user_id',
        'session_type',
        'start_time',
        'end_time',
        'duration',
        'interaction_count',
        'performance_metrics',
        'session_data',
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
        'interaction_count' => 'integer',
        'performance_metrics' => 'array',
        'session_data' => 'array',
    ];
    
    public function environment(): BelongsTo
    {
        return $this->belongsTo(VirtualEnvironment::class, 'environment_id');
    }
    
    public function cat(): BelongsTo
    {
        return $this->belongsTo(Cat::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function interactions(): HasMany
    {
        return $this->hasMany(VRInteraction::class, 'session_id');
    }
    
    public function socialInteractions(): HasMany
    {
        return $this->hasMany(VRSocialInteraction::class, 'session_id');
    }
    
    public function isActive(): bool
    {
        return !$this->end_time;
    }
    
    public function getDuration(): int
    {
        if (!$this->end_time) {
            return time() - $this->start_time->timestamp;
        }
        return $this->duration;
    }
    
    public function getInteractionRate(): float
    {
        $duration = $this->getDuration();
        if ($duration <= 0) return 0;
        
        return $this->interaction_count / ($duration / 60); // per minute
    }
}
