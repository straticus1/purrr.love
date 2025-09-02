<?php
/**
 * Purrr.love - Enhanced Cat Breeding System
 * Developed and Designed by Ryan Coleman. <coleman.ryan@gmail.com>
 * Enhanced with feline-specific genetics, personality inheritance, and breed-specific traits
 */

require_once 'includes/functions.php';
require_once 'includes/security.php';
require_once 'includes/cat_behavior.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$user_cats = getUserCats($user_id);

include 'includes/header.php';
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">🧬 Advanced Cat Breeding & Genetics</h1>
    <div id="breeding-alert-container"></div>
    
    <!-- Breeding Method Selection -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>🔬 Breeding Enhancement Options</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="breeding-method" data-method="natural">
                                <div class="method-icon">🌿</div>
                                <h6>Natural Breeding</h6>
                                <p>Traditional breeding with genetic inheritance</p>
                                <div class="price">Free</div>
                                <div class="success-rate">Success Rate: 60%</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="breeding-method" data-method="ai_assisted">
                                <div class="method-icon">🤖</div>
                                <h6>AI-Assisted</h6>
                                <p>Enhanced success rates and trait prediction</p>
                                <div class="price">$2.50</div>
                                <div class="success-rate">Success Rate: 80%</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="breeding-method" data-method="genetic_enhancement">
                                <div class="method-icon">⚡</div>
                                <h6>Genetic Enhancement</h6>
                                <p>Maximum success with rare trait chances</p>
                                <div class="price">$5.00</div>
                                <div class="success-rate">Success Rate: 95%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>🐱 Select Parents</h5>
                </div>
                <div class="card-body">
                    <form id="breeding-form">
                        <?php echo getCSRFTokenField(); ?>
                        <input type="hidden" id="breeding-method" name="breeding_method" value="natural">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mother-select">Select Mother 🤱</label>
                                    <select class="form-control" id="mother-select" name="mother_id">
                                        <option value="">-- Select a Cat --</option>
                                        <?php foreach ($user_cats as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['id']); ?>" 
                                                    data-species="<?php echo htmlspecialchars($cat['species'] ?? 'cat'); ?>"
                                                    data-personality="<?php echo htmlspecialchars($cat['personality_type']); ?>"
                                                    data-breed="<?php echo htmlspecialchars($cat['breed'] ?? 'mixed'); ?>"
                                                    data-level="<?php echo htmlspecialchars($cat['level']); ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?> 
                                                (<?php echo getCatPersonalityName($cat['personality_type']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="mother-preview" class="cat-preview"></div>
                                <div id="mother-traits" class="traits-display"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="father-select">Select Father 👨</label>
                                    <select class="form-control" id="father-select" name="father_id">
                                        <option value="">-- Select a Cat --</option>
                                        <?php foreach ($user_cats as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['id']); ?>"
                                                    data-species="<?php echo htmlspecialchars($cat['species'] ?? 'cat'); ?>"
                                                    data-personality="<?php echo htmlspecialchars($cat['personality_type']); ?>"
                                                    data-breed="<?php echo htmlspecialchars($cat['breed'] ?? 'mixed'); ?>"
                                                    data-level="<?php echo htmlspecialchars($cat['level']); ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?> 
                                                (<?php echo getCatPersonalityName($cat['personality_type']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="father-preview" class="cat-preview"></div>
                                <div id="father-traits" class="traits-display"></div>
                            </div>
                        </div>
                        
                        <!-- Compatibility Analysis -->
                        <div id="compatibility-analysis" class="mt-4" style="display: none;">
                            <h6>🔍 Breeding Compatibility Analysis</h6>
                            <div class="compatibility-grid">
                                <div class="compatibility-item">
                                    <span class="label">Personality Match:</span>
                                    <span class="value" id="personality-match">-</span>
                                </div>
                                <div class="compatibility-item">
                                    <span class="label">Breed Compatibility:</span>
                                    <span class="value" id="breed-compatibility">-</span>
                                </div>
                                <div class="compatibility-item">
                                    <span class="label">Level Difference:</span>
                                    <span class="value" id="level-difference">-</span>
                                </div>
                                <div class="compatibility-item">
                                    <span class="label">Overall Success Rate:</span>
                                    <span class="value" id="overall-success-rate">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Predicted Offspring Traits -->
                        <div id="offspring-prediction" class="mt-4" style="display: none;">
                            <h6>🔮 Predicted Offspring Traits</h6>
                            <div class="prediction-grid">
                                <div class="prediction-item">
                                    <span class="label">Personality:</span>
                                    <span class="value" id="predicted-personality">-</span>
                                </div>
                                <div class="prediction-item">
                                    <span class="label">Breed:</span>
                                    <span class="value" id="predicted-breed">-</span>
                                </div>
                                <div class="prediction-item">
                                    <span class="label">Rare Trait Chance:</span>
                                    <span class="value" id="rare-trait-chance">-</span>
                                </div>
                                <div class="prediction-item">
                                    <span class="label">Genetic Mutation:</span>
                                    <span class="value" id="genetic-mutation">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="start-breeding-btn" disabled>
                                🧬 Start Breeding Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Breeding History -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6>📚 Breeding History</h6>
                </div>
                <div class="card-body">
                    <div id="breeding-history">
                        <div class="loader"></div>
                    </div>
                </div>
            </div>
            
            <!-- Breeding Tips -->
            <div class="card">
                <div class="card-header">
                    <h6>💡 Breeding Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="breeding-tips">
                        <li>🐱 Cats with similar personalities have higher compatibility</li>
                        <li>🧬 Purebred cats have higher chances of passing on breed traits</li>
                        <li>⚡ Higher level cats produce stronger offspring</li>
                        <li>🎯 AI-assisted breeding increases success rates</li>
                        <li>💎 Genetic enhancement unlocks rare traits</li>
                        <li>🌙 Some traits are more common during specific seasons</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Breeding Results Modal -->
<div class="modal fade" id="breedingResultsModal" tabindex="-1" aria-labelledby="breedingResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="breedingResultsModalLabel">Breeding Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="breedingResultsBody">
                <!-- Breeding results will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="view-offspring-btn">View Offspring</button>
            </div>
        </div>
    </div>
</div>

<!-- Offspring Details Modal -->
<div class="modal fade" id="offspringDetailsModal" tabindex="-1" aria-labelledby="offspringDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offspringDetailsModalLabel">New Kitten Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="offspringDetailsBody">
                <!-- Offspring details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="adopt-kitten-btn">Adopt Kitten</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/cat-breeding.js"></script>

<style>
.breeding-method {
    text-align: center;
    padding: 20px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 15px;
}

.breeding-method:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.breeding-method.selected {
    border-color: #28a745;
    background-color: #f8fff9;
}

.method-icon {
    font-size: 2em;
    margin-bottom: 10px;
}

.price {
    font-size: 1.2em;
    font-weight: bold;
    color: #28a745;
    margin-top: 10px;
}

.success-rate {
    font-size: 0.9em;
    color: #6c757d;
    margin-top: 5px;
}

.cat-preview {
    margin-top: 15px;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background-color: #f8f9fa;
    min-height: 120px;
}

.traits-display {
    margin-top: 10px;
    font-size: 0.9em;
}

.trait-item {
    display: inline-block;
    margin: 2px 5px 2px 0;
    padding: 2px 8px;
    background-color: #e9ecef;
    border-radius: 12px;
    font-size: 0.8em;
}

.compatibility-grid, .prediction-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-top: 15px;
}

.compatibility-item, .prediction-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 6px;
}

.label {
    font-weight: 600;
    color: #495057;
}

.value {
    font-weight: bold;
    color: #007bff;
}

.breeding-tips {
    list-style: none;
    padding: 0;
}

.breeding-tips li {
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9em;
}

.breeding-tips li:last-child {
    border-bottom: none;
}

.loader {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}
</style>
