<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualEnvironment extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'difficulty',
        'physics_config',
        'weather_enabled',
        'time_cycle_enabled',
        'capacity',
        'creator_id',
        'is_template',
        'parent_template_id',
        'status',
        'metadata',
    ];
    
    protected $casts = [
        'physics_config' => 'array',
        'weather_enabled' => 'boolean',
        'time_cycle_enabled' => 'boolean',
        'difficulty' => 'integer',
        'capacity' => 'integer',
        'is_template' => 'boolean',
        'metadata' => 'array',
    ];
    
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    
    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_template_id');
    }
    
    public function childEnvironments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_template_id');
    }
    
    public function sessions(): HasMany
    {
        return $this->hasMany(VRSession::class, 'environment_id');
    }
    
    public function getActiveSessionsCount(): int
    {
        return $this->sessions()
            ->whereNull('end_time')
            ->count();
    }
    
    public function hasCapacity(): bool
    {
        return $this->getActiveSessionsCount() < $this->capacity;
    }
    
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    public function isTemplate(): bool
    {
        return $this->is_template;
    }
    
    public function hasWeather(): bool
    {
        return $this->weather_enabled;
    }
    
    public function hasTimeCycle(): bool
    {
        return $this->time_cycle_enabled;
    }
}
