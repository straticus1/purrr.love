<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicsConfiguration extends Model
{
    protected $fillable = [
        'name',
        'config',
        'description',
    ];
    
    protected $casts = [
        'config' => 'array',
    ];
    
    public function getGravity(): array
    {
        return $this->config['gravity'] ?? ['x' => 0, 'y' => -9.81, 'z' => 0];
    }
    
    public function getAirResistance(): float
    {
        return $this->config['air_resistance'] ?? 0.1;
    }
    
    public function getFriction(): float
    {
        return $this->config['friction'] ?? 0.5;
    }
    
    public function getRestitution(): float
    {
        return $this->config['restitution'] ?? 0.3;
    }
    
    public function getCollisionTolerance(): float
    {
        return $this->config['collision_tolerance'] ?? 0.01;
    }
    
    public function getSimulationRate(): int
    {
        return $this->config['simulation_rate'] ?? 60;
    }
    
    public function getSubsteps(): int
    {
        return $this->config['substeps'] ?? 2;
    }
    
    public function getForceLimits(): array
    {
        return $this->config['force_limits'] ?? [
            'linear' => 1000,
            'angular' => 100
        ];
    }
    
    public function getVelocityLimits(): array
    {
        return $this->config['velocity_limits'] ?? [
            'linear' => 50,
            'angular' => 10
        ];
    }
}
