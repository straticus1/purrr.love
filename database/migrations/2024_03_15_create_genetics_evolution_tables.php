<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneticsEvolutionTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create trait inheritance patterns table
        Schema::create('trait_inheritance_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('trait_name')->unique();
            $table->jsonb('gene_markers');
            $table->jsonb('mutation_rates');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create cat genetic profiles table
        Schema::create('cat_genetic_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_id')->constrained()->onDelete('cascade');
            $table->jsonb('genetic_markers');
            $table->jsonb('trait_data');
            $table->jsonb('mutation_history')->default('[]');
            $table->integer('generation');
            $table->jsonb('lineage_path');
            $table->timestamps();

            $table->index('cat_id');
            $table->index('generation');
        });

        // Create cat evolution data table
        Schema::create('cat_evolution_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_id')->constrained()->onDelete('cascade');
            $table->bigInteger('experience_points')->default(0);
            $table->integer('evolution_stage')->default(1);
            $table->jsonb('adaptations')->default('[]');
            $table->jsonb('mutations')->default('[]');
            $table->timestamps();

            $table->index('cat_id');
            $table->index('evolution_stage');
        });

        // Create cat evolution events table
        Schema::create('cat_evolution_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_id')->constrained()->onDelete('cascade');
            $table->string('event_type');
            $table->jsonb('event_data');
            $table->integer('experience_gain');
            $table->jsonb('adaptations');
            $table->jsonb('mutations');
            $table->integer('new_stage');
            $table->timestamp('created_at');

            $table->index('cat_id');
            $table->index('event_type');
            $table->index('created_at');
        });

        // Create virtual environments table
        Schema::create('virtual_environments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->integer('difficulty')->default(1);
            $table->jsonb('physics_config');
            $table->boolean('weather_enabled')->default(true);
            $table->boolean('time_cycle_enabled')->default(true);
            $table->integer('capacity')->default(10);
            $table->foreignId('creator_id')->constrained('users');
            $table->boolean('is_template')->default(false);
            $table->foreignId('parent_template_id')->nullable()->constrained('virtual_environments');
            $table->string('status')->default('active');
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('creator_id');
        });

        // Create VR sessions table
        Schema::create('vr_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environment_id')->constrained('virtual_environments');
            $table->foreignId('cat_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('session_type')->default('standard');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('interaction_count')->default(0);
            $table->jsonb('performance_metrics')->default('{}');
            $table->jsonb('session_data')->default('{}');

            $table->index('environment_id');
            $table->index('cat_id');
            $table->index('start_time');
            $table->index(['environment_id', 'end_time']);
        });

        // Create VR interactions table
        Schema::create('vr_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('vr_sessions');
            $table->foreignId('cat_id')->constrained();
            $table->string('interaction_type');
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->jsonb('position');
            $table->jsonb('rotation')->nullable();
            $table->float('force')->default(0);
            $table->float('duration')->default(0);
            $table->jsonb('result_data')->default('{}');
            $table->timestamp('created_at');

            $table->index('session_id');
            $table->index('cat_id');
            $table->index('interaction_type');
            $table->index('created_at');
        });

        // Create VR social interactions table
        Schema::create('vr_social_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('vr_sessions');
            $table->foreignId('initiator_cat_id')->constrained('cats');
            $table->foreignId('target_cat_id')->constrained('cats');
            $table->string('interaction_type');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->jsonb('interaction_data');

            $table->index('session_id');
            $table->index('initiator_cat_id');
            $table->index('target_cat_id');
            $table->index('interaction_type');
        });

        // Create physics configurations table
        Schema::create('physics_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->jsonb('config');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('vr_social_interactions');
        Schema::dropIfExists('vr_interactions');
        Schema::dropIfExists('vr_sessions');
        Schema::dropIfExists('virtual_environments');
        Schema::dropIfExists('cat_evolution_events');
        Schema::dropIfExists('cat_evolution_data');
        Schema::dropIfExists('cat_genetic_profiles');
        Schema::dropIfExists('trait_inheritance_patterns');
        Schema::dropIfExists('physics_configurations');
    }
}
