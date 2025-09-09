<?php
/**
 * 🧬 Purrr.love Core Genetics System
 * Advanced genetic and evolution processing for virtual cats
 */

namespace PurrrLove\Core;

use PurrrLove\Database\Connection;
use PurrrLove\Utils\Random;
use PurrrLove\Utils\Logger;

class GeneticsSystem {
    private $db;
    private $config;
    private $logger;

    // Genetic configuration
    private const MUTATION_BASE_RATE = 0.01;
    private const EVOLUTION_THRESHOLD = 0.8;
    private const FITNESS_MAX_SCORE = 10.0;

    // Trait inheritance weights
    private const TRAIT_WEIGHTS = [
        'dominant' => 0.7,
        'recessive' => 0.3,
        'codominant' => 0.5
    ];

    public function __construct() {
        $this->db = Connection::getInstance()->getConnection();
        $this->logger = new Logger('genetics');
        $this->loadConfiguration();
    }

    /**
     * Initialize genetic profile for a new cat
     */
    public function initializeGeneticProfile($catId, $parentData = null) {
        try {
            $profile = [
                'genetic_markers' => $this->generateInitialGeneticMarkers($parentData),
                'trait_data' => $this->generateInitialTraits($parentData),
                'mutation_history' => [],
                'generation' => $parentData ? $parentData['generation'] + 1 : 1,
                'lineage_path' => $this->generateLineagePath($parentData)
            ];

            $stmt = $this->db->prepare("
                INSERT INTO cat_genetic_profiles 
                (cat_id, genetic_markers, trait_data, mutation_history, generation, lineage_path)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $catId,
                json_encode($profile['genetic_markers']),
                json_encode($profile['trait_data']),
                json_encode($profile['mutation_history']),
                $profile['generation'],
                "{" . implode(",", $profile['lineage_path']) . "}"
            ]);

            $this->logger->info("Initialized genetic profile for cat $catId", [
                'generation' => $profile['generation'],
                'traits' => count($profile['trait_data'])
            ]);

            return $profile;

        } catch (\Exception $e) {
            $this->logger->error("Failed to initialize genetic profile", [
                'cat_id' => $catId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process genetic inheritance between parents
     */
    public function processInheritance($parent1Id, $parent2Id) {
        try {
            // Get parent genetic data
            $parent1Data = $this->getGeneticProfile($parent1Id);
            $parent2Data = $this->getGeneticProfile($parent2Id);

            if (!$parent1Data || !$parent2Data) {
                throw new \Exception("Missing parent genetic data");
            }

            // Process trait inheritance
            $inheritedTraits = $this->calculateTraitInheritance($parent1Data, $parent2Data);

            // Calculate mutation probability
            $mutationProb = $this->calculateMutationProbability($parent1Data, $parent2Data);

            // Apply possible mutations
            $mutations = $this->processPossibleMutations($inheritedTraits, $mutationProb);

            // Calculate initial fitness
            $fitness = $this->calculateInitialFitness($inheritedTraits, $mutations);

            return [
                'inherited_traits' => $inheritedTraits,
                'mutations' => $mutations,
                'fitness_score' => $fitness,
                'genetic_markers' => $this->combineGeneticMarkers($parent1Data, $parent2Data)
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to process inheritance", [
                'parent1_id' => $parent1Id,
                'parent2_id' => $parent2Id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate trait inheritance between parents
     */
    private function calculateTraitInheritance($parent1Data, $parent2Data) {
        $inheritedTraits = [];

        // Get trait patterns
        $traitPatterns = $this->getTraitInheritancePatterns();

        foreach ($traitPatterns as $trait) {
            $p1Traits = $parent1Data['trait_data'][$trait['trait_name']] ?? null;
            $p2Traits = $parent2Data['trait_data'][$trait['trait_name']] ?? null;

            if ($p1Traits && $p2Traits) {
                switch ($trait['inheritance_type']) {
                    case 'simple':
                        $inheritedTraits[$trait['trait_name']] = $this->processSimpleInheritance(
                            $p1Traits,
                            $p2Traits,
                            $trait['dominance_factors']
                        );
                        break;

                    case 'complex':
                        $inheritedTraits[$trait['trait_name']] = $this->processComplexInheritance(
                            $p1Traits,
                            $p2Traits,
                            $trait['gene_markers'],
                            $trait['dominance_factors']
                        );
                        break;

                    case 'polygenic':
                        $inheritedTraits[$trait['trait_name']] = $this->processPolygenicInheritance(
                            $p1Traits,
                            $p2Traits,
                            $trait['gene_markers']
                        );
                        break;
                }
            }
        }

        return $inheritedTraits;
    }

    /**
     * Process simple (Mendelian) inheritance
     */
    private function processSimpleInheritance($trait1, $trait2, $dominance) {
        $allele1 = $this->selectRandomAllele($trait1);
        $allele2 = $this->selectRandomAllele($trait2);

        // Process dominance
        if ($dominance[$allele1] > $dominance[$allele2]) {
            return $allele1;
        } elseif ($dominance[$allele2] > $dominance[$allele1]) {
            return $allele2;
        } else {
            // Co-dominance or incomplete dominance
            return $this->processCodeminance($allele1, $allele2);
        }
    }

    /**
     * Process complex inheritance with multiple genes
     */
    private function processComplexInheritance($trait1, $trait2, $markers, $dominance) {
        $result = [];

        foreach ($markers as $marker) {
            $gene1 = $trait1[$marker] ?? null;
            $gene2 = $trait2[$marker] ?? null;

            if ($gene1 && $gene2) {
                // Calculate contribution based on dominance
                $dom1 = $dominance[$gene1] ?? 0.5;
                $dom2 = $dominance[$gene2] ?? 0.5;

                $result[$marker] = $this->calculateGeneExpression($gene1, $gene2, $dom1, $dom2);
            }
        }

        return $result;
    }

    /**
     * Process polygenic inheritance
     */
    private function processPolygenicInheritance($trait1, $trait2, $markers) {
        $values = [];

        foreach ($markers as $marker) {
            $gene1 = $trait1[$marker] ?? 0;
            $gene2 = $trait2[$marker] ?? 0;

            // Calculate additive effect
            $values[] = ($gene1 + $gene2) / 2 + Random::gaussian(0, 0.1);
        }

        return array_sum($values) / count($values);
    }

    /**
     * Calculate mutation probability
     */
    private function calculateMutationProbability($parent1Data, $parent2Data) {
        $baseProbability = self::MUTATION_BASE_RATE;

        // Adjust based on parent diversity
        $diversityFactor = $this->calculateGeneticDiversity($parent1Data, $parent2Data);
        $adjustedProbability = $baseProbability * (1 + $diversityFactor);

        // Adjust based on generation
        $generationFactor = ($parent1Data['generation'] + $parent2Data['generation']) / 2;
        $adjustedProbability *= (1 + log10($generationFactor) * 0.1);

        return min($adjustedProbability, 0.05); // Cap at 5%
    }

    /**
     * Process possible mutations
     */
    private function processPossibleMutations($traits, $mutationProb) {
        $mutations = [];

        foreach ($traits as $traitName => $traitValue) {
            if (Random::float() < $mutationProb) {
                $mutation = $this->generateMutation($traitName, $traitValue);
                if ($mutation) {
                    $mutations[$traitName] = $mutation;
                }
            }
        }

        return $mutations;
    }

    /**
     * Generate a mutation for a trait
     */
    private function generateMutation($traitName, $currentValue) {
        $patterns = $this->getTraitInheritancePatterns();
        $pattern = null;

        foreach ($patterns as $p) {
            if ($p['trait_name'] === $traitName) {
                $pattern = $p;
                break;
            }
        }

        if (!$pattern) {
            return null;
        }

        $mutationRate = $pattern['mutation_rates']['base'];
        $modifier = $pattern['mutation_rates']['modifier'];

        switch ($pattern['inheritance_type']) {
            case 'simple':
                return $this->generateSimpleMutation($currentValue, $pattern['dominance_factors']);

            case 'complex':
                return $this->generateComplexMutation($currentValue, $pattern['gene_markers'], $mutationRate);

            case 'polygenic':
                return $this->generatePolygenicMutation($currentValue, $modifier);

            default:
                return null;
        }
    }

    /**
     * Calculate initial fitness score
     */
    private function calculateInitialFitness($traits, $mutations) {
        $fitnessScore = 1.0;

        // Base fitness from traits
        foreach ($traits as $trait => $value) {
            $fitnessScore *= $this->calculateTraitFitness($trait, $value);
        }

        // Adjust for mutations
        foreach ($mutations as $mutation) {
            $fitnessScore *= $this->calculateMutationImpact($mutation);
        }

        return min($fitnessScore, self::FITNESS_MAX_SCORE);
    }

    /**
     * Helper functions for trait inheritance
     */
    private function getTraitInheritancePatterns() {
        $stmt = $this->db->prepare("SELECT * FROM trait_inheritance_patterns");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function selectRandomAllele($trait) {
        $alleles = is_array($trait) ? $trait : [$trait];
        return $alleles[array_rand($alleles)];
    }

    private function processCodeminance($allele1, $allele2) {
        return [
            'allele1' => $allele1,
            'allele2' => $allele2,
            'expression' => ($allele1 === $allele2) ? 1.0 : 0.5
        ];
    }

    private function calculateGeneExpression($gene1, $gene2, $dom1, $dom2) {
        $totalDom = $dom1 + $dom2;
        if ($totalDom === 0) return null;

        return [
            'gene1' => ['value' => $gene1, 'expression' => $dom1 / $totalDom],
            'gene2' => ['value' => $gene2, 'expression' => $dom2 / $totalDom]
        ];
    }

    private function calculateGeneticDiversity($parent1Data, $parent2Data) {
        $differences = 0;
        $total = 0;

        foreach ($parent1Data['genetic_markers'] as $marker => $value) {
            if (isset($parent2Data['genetic_markers'][$marker])) {
                $total++;
                if ($value !== $parent2Data['genetic_markers'][$marker]) {
                    $differences++;
                }
            }
        }

        return $total > 0 ? $differences / $total : 0;
    }

    private function generateSimpleMutation($currentValue, $dominanceFactors) {
        $possibleValues = array_keys($dominanceFactors);
        unset($possibleValues[array_search($currentValue, $possibleValues)]);
        return $possibleValues[array_rand($possibleValues)];
    }

    private function generateComplexMutation($currentValue, $markers, $rate) {
        $mutation = [];
        foreach ($markers as $marker) {
            if (Random::float() < $rate) {
                $mutation[$marker] = Random::float(0, 1);
            }
        }
        return !empty($mutation) ? $mutation : null;
    }

    private function generatePolygenicMutation($currentValue, $modifier) {
        return $currentValue * (1 + Random::gaussian(0, $modifier));
    }

    private function calculateTraitFitness($trait, $value) {
        // Implementation would depend on specific trait fitness calculations
        return 1.0; // Placeholder
    }

    private function calculateMutationImpact($mutation) {
        // Implementation would depend on specific mutation impact calculations
        return 0.95; // Slight negative impact by default
    }

    /**
     * Load configuration
     */
    private function loadConfiguration() {
        // Load configuration from database or file
        $this->config = [
            'mutation_rates' => [
                'base' => self::MUTATION_BASE_RATE,
                'max' => 0.05
            ],
            'evolution' => [
                'threshold' => self::EVOLUTION_THRESHOLD,
                'max_fitness' => self::FITNESS_MAX_SCORE
            ]
        ];
    }
}
