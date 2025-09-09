<?php

namespace App\Models\Traits;

use PurrrLove\Core\GeneticsSystem;
use PurrrLove\Core\EvolutionSystem;

trait HasGeneticsAndEvolution
{
    protected static ?GeneticsSystem $geneticsSystem = null;
    protected static ?EvolutionSystem $evolutionSystem = null;
    
    /**
     * Get the genetics system instance
     */
    protected static function getGeneticsSystem(): GeneticsSystem
    {
        if (static::$geneticsSystem === null) {
            static::$geneticsSystem = app(GeneticsSystem::class);
        }
        return static::$geneticsSystem;
    }
    
    /**
     * Get the evolution system instance
     */
    protected static function getEvolutionSystem(): EvolutionSystem
    {
        if (static::$evolutionSystem === null) {
            static::$evolutionSystem = app(EvolutionSystem::class);
        }
        return static::$evolutionSystem;
    }
    
    /**
     * Initialize genetics profile for a cat
     */
    public function initializeGenetics(array $parentIds = [])
    {
        return static::getGeneticsSystem()->createGeneticProfile(
            $this->id,
            $parentIds
        );
    }
    
    /**
     * Get genetic profile
     */
    public function getGeneticProfile()
    {
        return $this->geneticProfile;
    }
    
    /**
     * Get evolution data
     */
    public function getEvolutionData()
    {
        return $this->evolutionData;
    }
    
    /**
     * Process evolution event
     */
    public function evolve(string $eventType, array $eventData = [])
    {
        return static::getEvolutionSystem()->processEvolutionEvent(
            $this->id,
            $eventType,
            $eventData
        );
    }
    
    /**
     * Get current evolution stage
     */
    public function getEvolutionStage(): int
    {
        return $this->evolutionData?->evolution_stage ?? 1;
    }
    
    /**
     * Check if cat can evolve to next stage
     */
    public function canEvolve(): bool
    {
        $currentStage = $this->getEvolutionStage();
        $currentExp = $this->evolutionData?->experience_points ?? 0;
        
        $nextStageThreshold = config('evolution.stages.' . ($currentStage + 1) . '.exp_threshold', PHP_INT_MAX);
        
        return $currentExp >= $nextStageThreshold;
    }
    
    /**
     * Get genetic markers
     */
    public function getGeneticMarkers(): array
    {
        return $this->geneticProfile?->genetic_markers ?? [];
    }
    
    /**
     * Get trait data
     */
    public function getTraitData(): array
    {
        return $this->geneticProfile?->trait_data ?? [];
    }
    
    /**
     * Get cat's generation number
     */
    public function getGeneration(): int
    {
        return $this->geneticProfile?->generation ?? 1;
    }
    
    /**
     * Get cat's lineage path
     */
    public function getLineage(): array
    {
        return $this->geneticProfile?->lineage_path ?? [];
    }
    
    /**
     * Get all mutations
     */
    public function getMutations(): array
    {
        return $this->evolutionData?->mutations ?? [];
    }
    
    /**
     * Get all adaptations
     */
    public function getAdaptations(): array
    {
        return $this->evolutionData?->adaptations ?? [];
    }
    
    /**
     * Check if cat has specific mutation
     */
    public function hasMutation(string $mutationType): bool
    {
        $mutations = $this->getMutations();
        
        foreach ($mutations as $mutation) {
            if ($mutation['type'] === $mutationType) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if cat has specific adaptation
     */
    public function hasAdaptation(string $adaptationType): bool
    {
        $adaptations = $this->getAdaptations();
        
        foreach ($adaptations as $adaptation) {
            if ($adaptation['type'] === $adaptationType) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get experience points
     */
    public function getExperiencePoints(): int
    {
        return $this->evolutionData?->experience_points ?? 0;
    }
    
    /**
     * Calculate trait value
     */
    public function calculateTraitValue(string $traitName): float
    {
        $traitData = $this->getTraitData();
        
        // If trait exists in trait data, return it
        if (isset($traitData[$traitName])) {
            return $traitData[$traitName];
        }
        
        // Otherwise, need to calculate from genetic markers
        $markers = $this->getGeneticMarkers();
        $category = $this->determineTraitCategory($traitName);
        
        if (!isset($markers[$category])) {
            return 0.5; // Default middle value
        }
        
        return static::getGeneticsSystem()->calculateTraitValue(
            $markers[$category][$traitName . '_genes'] ?? []
        );
    }
    
    /**
     * Calculate overall trait category value
     */
    public function calculateCategoryValue(string $category): float
    {
        $traitData = $this->getTraitData();
        
        if (!isset($traitData[$category])) {
            return 0.5; // Default middle value
        }
        
        $total = 0;
        $count = 0;
        
        foreach ($traitData[$category] as $value) {
            $total += $value;
            $count++;
        }
        
        return $count > 0 ? $total / $count : 0.5;
    }
    
    /**
     * Get trait categories this cat excels in
     */
    public function getExcellentTraits(float $threshold = 0.8): array
    {
        $excellent = [];
        $traitData = $this->getTraitData();
        
        foreach ($traitData as $category => $traits) {
            foreach ($traits as $trait => $value) {
                if ($value >= $threshold) {
                    $excellent[] = [
                        'category' => $category,
                        'trait' => $trait,
                        'value' => $value
                    ];
                }
            }
        }
        
        return $excellent;
    }
    
    /**
     * Get mutation history
     */
    public function getMutationHistory(): array
    {
        return $this->geneticProfile?->mutation_history ?? [];
    }
    
    /**
     * Calculate genetic similarity with another cat
     */
    public function calculateGeneticSimilarity(self $otherCat): float
    {
        $thisMarkers = $this->getGeneticMarkers();
        $otherMarkers = $otherCat->getGeneticMarkers();
        
        if (empty($thisMarkers) || empty($otherMarkers)) {
            return 0.0;
        }
        
        $totalSimilarity = 0;
        $totalComparisons = 0;
        
        foreach ($thisMarkers as $category => $traits) {
            if (!isset($otherMarkers[$category])) continue;
            
            foreach ($traits as $traitName => $geneData) {
                if (!isset($otherMarkers[$category][$traitName])) continue;
                
                $similarity = static::getGeneticsSystem()->calculateGeneticSimilarity(
                    $geneData,
                    $otherMarkers[$category][$traitName]
                );
                
                $totalSimilarity += $similarity;
                $totalComparisons++;
            }
        }
        
        return $totalComparisons > 0 ? $totalSimilarity / $totalComparisons : 0.0;
    }
    
    /**
     * Check if cats are compatible for breeding
     */
    public function isBreedingCompatible(self $otherCat): bool
    {
        // Check if cats are too closely related
        $minGeneticDiversity = config('genetics.min_breeding_diversity', 0.2);
        $similarity = $this->calculateGeneticSimilarity($otherCat);
        
        if ($similarity > (1 - $minGeneticDiversity)) {
            return false;
        }
        
        // Check generation gap
        $maxGenGap = config('genetics.max_generation_gap', 2);
        $genGap = abs($this->getGeneration() - $otherCat->getGeneration());
        
        if ($genGap > $maxGenGap) {
            return false;
        }
        
        // Check evolution stage compatibility
        $minStage = config('genetics.min_breeding_stage', 2);
        if ($this->getEvolutionStage() < $minStage || 
            $otherCat->getEvolutionStage() < $minStage) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Determine trait category
     */
    protected function determineTraitCategory(string $traitName): string
    {
        $categories = [
            'physical' => ['size', 'color', 'pattern', 'features'],
            'personality' => ['temperament', 'intelligence', 'social'],
            'abilities' => ['agility', 'strength', 'special']
        ];
        
        foreach ($categories as $category => $traits) {
            if (in_array($traitName, $traits)) {
                return $category;
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Database relationships
     */
    public function geneticProfile()
    {
        return $this->hasOne(CatGeneticProfile::class, 'cat_id');
    }
    
    public function evolutionData()
    {
        return $this->hasOne(CatEvolutionData::class, 'cat_id');
    }
    
    public function evolutionEvents()
    {
        return $this->hasMany(CatEvolutionEvent::class, 'cat_id');
    }
}
