<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneticsEvolutionSeeder extends Seeder
{
    /**
     * Run the genetics and evolution seeders.
     */
    public function run()
    {
        $this->seedTraitInheritancePatterns();
        $this->seedPhysicsConfigurations();
    }

    /**
     * Seed the trait inheritance patterns.
     */
    private function seedTraitInheritancePatterns()
    {
        $patterns = [
            // Physical traits
            [
                'trait_name' => 'size',
                'gene_markers' => json_encode([
                    'body_mass',
                    'height',
                    'muscle_density',
                    'skeletal_structure'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.01,
                    'environmental' => 0.02,
                    'adaptive' => 0.015
                ]),
                'description' => 'Controls the physical size and build of the cat'
            ],
            [
                'trait_name' => 'color',
                'gene_markers' => json_encode([
                    'base_color',
                    'pattern_intensity',
                    'melanin_production',
                    'color_variants'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.015,
                    'environmental' => 0.01,
                    'adaptive' => 0.02
                ]),
                'description' => 'Determines the coat color and patterns'
            ],
            [
                'trait_name' => 'pattern',
                'gene_markers' => json_encode([
                    'pattern_type',
                    'marking_distribution',
                    'pattern_clarity',
                    'pattern_complexity'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.02,
                    'environmental' => 0.015,
                    'adaptive' => 0.025
                ]),
                'description' => 'Controls coat patterns and markings'
            ],
            [
                'trait_name' => 'features',
                'gene_markers' => json_encode([
                    'ear_shape',
                    'tail_length',
                    'face_structure',
                    'paw_size'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.01,
                    'environmental' => 0.02,
                    'adaptive' => 0.015
                ]),
                'description' => 'Determines distinctive physical features'
            ],

            // Personality traits
            [
                'trait_name' => 'temperament',
                'gene_markers' => json_encode([
                    'aggression_tendency',
                    'sociability',
                    'curiosity',
                    'independence'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.02,
                    'environmental' => 0.03,
                    'adaptive' => 0.025
                ]),
                'description' => 'Influences basic personality and behavior patterns'
            ],
            [
                'trait_name' => 'intelligence',
                'gene_markers' => json_encode([
                    'problem_solving',
                    'memory_capacity',
                    'learning_speed',
                    'adaptability'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.015,
                    'environmental' => 0.025,
                    'adaptive' => 0.03
                ]),
                'description' => 'Affects learning ability and problem-solving skills'
            ],
            [
                'trait_name' => 'social',
                'gene_markers' => json_encode([
                    'empathy',
                    'communication',
                    'leadership',
                    'cooperation'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.02,
                    'environmental' => 0.03,
                    'adaptive' => 0.025
                ]),
                'description' => 'Controls social interaction capabilities'
            ],

            // Ability traits
            [
                'trait_name' => 'agility',
                'gene_markers' => json_encode([
                    'balance',
                    'reflexes',
                    'flexibility',
                    'coordination'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.02,
                    'environmental' => 0.025,
                    'adaptive' => 0.03
                ]),
                'description' => 'Determines physical agility and movement capabilities'
            ],
            [
                'trait_name' => 'strength',
                'gene_markers' => json_encode([
                    'muscle_power',
                    'endurance',
                    'recovery_rate',
                    'stamina'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.015,
                    'environmental' => 0.02,
                    'adaptive' => 0.025
                ]),
                'description' => 'Influences physical strength and endurance'
            ],
            [
                'trait_name' => 'special',
                'gene_markers' => json_encode([
                    'unique_ability',
                    'adaptation_rate',
                    'energy_control',
                    'potential'
                ]),
                'mutation_rates' => json_encode([
                    'base' => 0.03,
                    'environmental' => 0.04,
                    'adaptive' => 0.05
                ]),
                'description' => 'Controls development of special abilities'
            ],
        ];

        foreach ($patterns as $pattern) {
            DB::table('trait_inheritance_patterns')->insertOrIgnore([
                'trait_name' => $pattern['trait_name'],
                'gene_markers' => $pattern['gene_markers'],
                'mutation_rates' => $pattern['mutation_rates'],
                'description' => $pattern['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Seed the physics configurations.
     */
    private function seedPhysicsConfigurations()
    {
        $configs = [
            [
                'name' => 'default',
                'config' => json_encode([
                    'gravity' => ['x' => 0, 'y' => -9.81, 'z' => 0],
                    'air_resistance' => 0.1,
                    'friction' => 0.5,
                    'restitution' => 0.3,
                    'collision_tolerance' => 0.01,
                    'simulation_rate' => 60,
                    'substeps' => 2,
                    'force_limits' => [
                        'linear' => 1000,
                        'angular' => 100
                    ],
                    'velocity_limits' => [
                        'linear' => 50,
                        'angular' => 10
                    ]
                ]),
                'description' => 'Default physics configuration for standard environments'
            ],
            [
                'name' => 'training',
                'config' => json_encode([
                    'gravity' => ['x' => 0, 'y' => -6.0, 'z' => 0],
                    'air_resistance' => 0.2,
                    'friction' => 0.4,
                    'restitution' => 0.4,
                    'collision_tolerance' => 0.02,
                    'simulation_rate' => 60,
                    'substeps' => 3,
                    'force_limits' => [
                        'linear' => 800,
                        'angular' => 80
                    ],
                    'velocity_limits' => [
                        'linear' => 40,
                        'angular' => 8
                    ]
                ]),
                'description' => 'Modified physics for training environments with reduced gravity'
            ],
            [
                'name' => 'competition',
                'config' => json_encode([
                    'gravity' => ['x' => 0, 'y' => -9.81, 'z' => 0],
                    'air_resistance' => 0.08,
                    'friction' => 0.6,
                    'restitution' => 0.25,
                    'collision_tolerance' => 0.008,
                    'simulation_rate' => 120,
                    'substeps' => 4,
                    'force_limits' => [
                        'linear' => 1200,
                        'angular' => 120
                    ],
                    'velocity_limits' => [
                        'linear' => 60,
                        'angular' => 12
                    ]
                ]),
                'description' => 'High-precision physics for competition environments'
            ],
            [
                'name' => 'playground',
                'config' => json_encode([
                    'gravity' => ['x' => 0, 'y' => -7.5, 'z' => 0],
                    'air_resistance' => 0.15,
                    'friction' => 0.45,
                    'restitution' => 0.5,
                    'collision_tolerance' => 0.015,
                    'simulation_rate' => 60,
                    'substeps' => 2,
                    'force_limits' => [
                        'linear' => 900,
                        'angular' => 90
                    ],
                    'velocity_limits' => [
                        'linear' => 45,
                        'angular' => 9
                    ]
                ]),
                'description' => 'Fun-oriented physics for playground environments'
            ],
        ];

        foreach ($configs as $config) {
            DB::table('physics_configurations')->insertOrIgnore([
                'name' => $config['name'],
                'config' => $config['config'],
                'description' => $config['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
