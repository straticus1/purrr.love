<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VRSocialInteraction extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'session_id',
        'initiator_cat_id',
        'target_cat_id',
        'interaction_type',
        'start_time',
        'end_time',
        'interaction_data',
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'interaction_data' => 'array',
    ];
    
    public function session(): BelongsTo
    {
        return $this->belongsTo(VRSession::class, 'session_id');
    }
    
    public function initiatorCat(): BelongsTo
    {
        return $this->belongsTo(Cat::class, 'initiator_cat_id');
    }
    
    public function targetCat(): BelongsTo
    {
        return $this->belongsTo(Cat::class, 'target_cat_id');
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
        
        return $this->end_time->timestamp - $this->start_time->timestamp;
    }
}
