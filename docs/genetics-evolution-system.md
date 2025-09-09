# Genetics & Evolution System

The genetics and evolution system handles cat traits, inheritance, adaptations, and evolution in the Purrr.love virtual pet game.

## Overview

The system consists of three main components:
1. Genetics System
2. Evolution System
3. Metaverse Integration

### 1. Genetics System

The genetics system handles:
- Genetic traits and inheritance
- Gene sequences and alleles
- Trait inheritance patterns
- Mutation mechanics

#### Trait Categories
- Physical traits (size, color, pattern, features)
- Personality traits (temperament, intelligence, social)
- Ability traits (agility, strength, special)

#### Inheritance
Each trait has:
- Genetic markers
- Alleles
- Expression values
- Mutation rates

### 2. Evolution System

The evolution system manages:
- Experience points
- Evolution stages
- Adaptations
- Mutations

#### Evolution Stages
1. Basic (0 XP)
2. Evolved (1,000 XP)
3. Advanced (5,000 XP)
4. Superior (15,000 XP)
5. Ultimate (50,000 XP)

#### Experience Types
- Physical activity (1.2x)
- Mental challenge (1.5x)
- Social interaction (1.3x)
- Combat (1.8x)
- Exploration (1.4x)
- Training (2.0x)

### 3. Metaverse Integration

Virtual environment support for:
- Physics interactions
- Social interactions
- Training sessions
- Environment effects

## Database Structure

### Core Tables
1. `trait_inheritance_patterns`
2. `cat_genetic_profiles`
3. `cat_evolution_data`
4. `cat_evolution_events`

### Metaverse Tables
1. `virtual_environments`
2. `vr_sessions`
3. `vr_interactions`
4. `vr_social_interactions`
5. `physics_configurations`

## Usage

### Initializing Genetics

```php
// For a new cat
$cat->initializeGenetics();

// For breeding
$cat->initializeGenetics([$parent1Id, $parent2Id]);
```

### Processing Evolution

```php
// Basic evolution event
$cat->evolve('physical_activity', [
    'base_experience' => 20,
    'difficulty' => 0.5,
    'success_rate' => 0.8
]);

// Complex event with conditions
$cat->evolve('training', [
    'base_experience' => 50,
    'difficulty' => 0.7,
    'success_rate' => 0.9,
    'conditions' => ['first_time', 'challenging']
]);
```

### Working with Traits

```php
// Get trait value
$agility = $cat->calculateTraitValue('agility');

// Get category value
$physical = $cat->calculateCategoryValue('physical');

// Get excellent traits
$bestTraits = $cat->getExcellentTraits(0.8);
```

### Virtual Environment

```php
// Create environment
$environment = VirtualEnvironment::create([
    'name' => 'Training Ground',
    'type' => 'training',
    'difficulty' => 2,
    'physics_config' => PhysicsConfiguration::where('name', 'training')->first()->config
]);

// Start VR session
$session = $cat->startVRSession($environment->id);

// Process interaction
$session->processInteraction([
    'type' => 'physics',
    'position' => ['x' => 1, 'y' => 0, 'z' => 1],
    'force' => 10,
    'duration' => 0.5
]);
```

## Configuration

### Genetics Config
```php
'genetics' => [
    'min_breeding_diversity' => 0.2,
    'max_generation_gap' => 2,
    'min_breeding_stage' => 2
]
```

### Evolution Config
```php
'evolution' => [
    'stages' => [
        1 => ['name' => 'Basic', 'exp_threshold' => 0],
        2 => ['name' => 'Evolved', 'exp_threshold' => 1000],
        // ...
    ]
]
```

## Models

### Core Models
- `CatGeneticProfile`
- `CatEvolutionData`
- `CatEvolutionEvent`
- `TraitInheritancePattern`

### VR Models
- `VirtualEnvironment`
- `VRSession`
- `VRInteraction`
- `VRSocialInteraction`
- `PhysicsConfiguration`
