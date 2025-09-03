
        <!-- Special Cats Section -->
        <div class="section">
            <h2>🌟 Special Cats</h2>
            <p>Unlock legendary cats with unique abilities and stories!</p>
            
            <div class="special-cats-grid">
                <?php
                require_once 'includes/special_cats.php';
                $specialCatsProgress = getSpecialCatUnlockProgress($userId);
                
                foreach ($specialCatsProgress as $catId => $progress):
                    $specialCat = getSpecialCat($catId);
                    $rarityClass = 'rarity-' . $progress['rarity'];
                ?>
                <div class="special-cat-card <?php echo $rarityClass; ?>">
                    <div class="cat-header">
                        <h3><?php echo $progress['name']; ?></h3>
                        <span class="rarity-badge <?php echo $rarityClass; ?>">
                            <?php echo ucfirst($progress['rarity']); ?>
                        </span>
                    </div>
                    
                    <div class="cat-description">
                        <?php echo $specialCat['description']; ?>
                    </div>
                    
                    <div class="unlock-status">
                        <?php if ($progress['unlocked']): ?>
                            <div class="unlocked">
                                <span class="status-icon">✅</span>
                                <span class="status-text">Unlocked!</span>
                            </div>
                        <?php else: ?>
                            <div class="locked">
                                <span class="status-icon">🔒</span>
                                <span class="status-text">Locked</span>
                            </div>
                            
                            <div class="unlock-conditions">
                                <h4>Unlock Requirements:</h4>
                                <p><?php echo $progress['conditions']['description']; ?></p>
                                
                                <?php if (isset($progress['progress']['percentage'])): ?>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $progress['progress']['percentage']; ?>%"></div>
                                </div>
                                <div class="progress-text">
                                    <?php echo $progress['progress']['current']; ?> / <?php echo $progress['progress']['required']; ?>
                                    (<?php echo round($progress['progress']['percentage']); ?>%)
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($progress['unlocked']): ?>
                    <div class="special-abilities">
                        <h4>Special Abilities:</h4>
                        <ul>
                            <?php foreach ($specialCat['special_abilities'] as $ability => $description): ?>
                            <li><?php echo $description; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

