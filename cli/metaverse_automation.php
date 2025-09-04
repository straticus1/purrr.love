#!/usr/bin/env php
<?php
/**
 * 🤖 Purrr.love Metaverse Automation CLI
 * Automated activity boosting and engagement management
 */

// Define secure access for includes
define('SECURE_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/metaverse_ai_activities.php';
require_once __DIR__ . '/../includes/metaverse_gamification.php';
require_once __DIR__ . '/../includes/metaverse_world_dynamics.php';
require_once __DIR__ . '/../includes/metaverse_analytics_automation.php';

/**
 * Main automation runner
 */
class MetaverseAutomationRunner {
    private $logFile;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../logs/metaverse_automation.log';
        
        // Ensure logs directory exists
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }
    
    /**
     * Run automation function
     */
    public function run($functionName) {
        $this->log("Starting automation function: $functionName");
        
        try {
            switch ($functionName) {
                case 'monitorAndBoostMetaverseEngagement':
                    $this->monitorAndBoostEngagement();
                    break;
                    
                case 'spawnAICatsInLowActivityWorlds':
                    $this->spawnAICats();
                    break;
                    
                case 'updateMetaverseWorldWeather':
                    $this->updateWeather();
                    break;
                    
                case 'updateMetaverseSeasonalContent':
                    $this->updateSeasonalContent();
                    break;
                    
                case 'generateDailyMetaverseQuests':
                    $this->generateDailyQuests();
                    break;
                    
                case 'manageMetaverseSpecialAreas':
                    $this->manageSpecialAreas();
                    break;
                    
                case 'balanceMetaverseWorldPopulation':
                    $this->balanceWorldPopulation();
                    break;
                    
                case 'processMetaverseAnalytics':
                    $this->processAnalytics();
                    break;
                    
                case 'triggerAutonomousMetaverseEvents':
                    $this->triggerAutonomousEvents();
                    break;
                    
                case 'checkAndStartMetaverseTournaments':
                    $this->checkTournaments();
                    break;
                    
                case 'sendMetaverseReEngagementNotifications':
                    $this->sendReEngagementNotifications();
                    break;
                    
                default:
                    throw new Exception("Unknown automation function: $functionName");
            }
            
            $this->log("Successfully completed: $functionName");
            
        } catch (Exception $e) {
            $this->log("Error in $functionName: " . $e->getMessage(), 'ERROR');
            exit(1);
        }
    }
    
    /**
     * Monitor and boost engagement
     */
    private function monitorAndBoostEngagement() {
        $metrics = calculateMetaverseEngagement();
        $this->log("Engagement score: " . $metrics['overall_score']);
        
        if ($metrics['overall_score'] < 0.4) {
            $this->log("Low engagement detected, triggering boosts");
            runAutomatedMetaverseBoosts();
        }
        
        // Log metrics
        $this->log("Active users: " . $metrics['player_metrics']['active_users']);
        $this->log("Avg session duration: " . $metrics['player_metrics']['avg_session_duration'] . " minutes");
    }
    
    /**
     * Spawn AI cats in quiet worlds
     */
    private function spawnAICats() {
        $result = spawnAICatsInLowActivityWorlds();
        $this->log("AI cat spawning completed");
    }
    
    /**
     * Update world weather systems
     */
    private function updateWeather() {
        updateMetaverseWorldWeather();
        $this->log("Weather systems updated for all active worlds");
    }
    
    /**
     * Update seasonal content
     */
    private function updateSeasonalContent() {
        updateMetaverseSeasonalContent();
        $season = $this->getCurrentSeason();
        $this->log("Seasonal content updated for season: $season");
    }
    
    /**
     * Generate daily quests for all users
     */
    private function generateDailyQuests() {
        // Get all active users
        $pdo = get_db();
        $stmt = $pdo->prepare("
            SELECT DISTINCT user_id 
            FROM metaverse_sessions 
            WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $questCount = 0;
        foreach ($users as $userId) {
            generateUserDailyQuests($userId);
            $questCount++;
        }
        
        $this->log("Generated daily quests for $questCount users");
    }
    
    /**
     * Manage special limited-time areas
     */
    private function manageSpecialAreas() {
        manageMetaverseSpecialAreas();
        $this->log("Special area management completed");
    }
    
    /**
     * Balance world population
     */
    private function balanceWorldPopulation() {
        balanceMetaverseWorldPopulation();
        $this->log("World population balancing completed");
    }
    
    /**
     * Process analytics data
     */
    private function processAnalytics() {
        $metrics = calculateMetaverseEngagement('last_hour');
        $heatMap = generateMetaverseActivityHeatMap();
        
        $this->log("Analytics processing completed");
        $this->log("Processed engagement score: " . $metrics['overall_score']);
    }
    
    /**
     * Trigger autonomous events
     */
    private function triggerAutonomousEvents() {
        triggerAutonomousMetaverseEvents();
        $this->log("Autonomous events triggered");
    }
    
    /**
     * Check and start tournaments
     */
    private function checkTournaments() {
        checkAndStartMetaverseTournaments();
        $this->log("Tournament check completed");
    }
    
    /**
     * Send re-engagement notifications
     */
    private function sendReEngagementNotifications() {
        sendMetaverseReEngagementNotifications();
        $this->log("Re-engagement notifications sent");
    }
    
    /**
     * Get current season
     */
    private function getCurrentSeason() {
        $month = (int)date('n');
        if ($month >= 3 && $month <= 5) return 'spring';
        if ($month >= 6 && $month <= 8) return 'summer';
        if ($month >= 9 && $month <= 11) return 'autumn';
        return 'winter';
    }
    
    /**
     * Log function
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        if (php_sapi_name() === 'cli') {
            echo $logEntry;
        }
    }
}

/**
 * CLI Interface
 */
function showUsage() {
    echo "Purrr.love Metaverse Automation CLI\n\n";
    echo "Usage: php metaverse_automation.php <function_name>\n\n";
    echo "Available functions:\n";
    echo "  monitorAndBoostMetaverseEngagement    - Monitor and boost engagement\n";
    echo "  spawnAICatsInLowActivityWorlds       - Spawn AI NPCs in quiet worlds\n";
    echo "  updateMetaverseWorldWeather          - Update world weather systems\n";
    echo "  updateMetaverseSeasonalContent       - Update seasonal content\n";
    echo "  generateDailyMetaverseQuests         - Generate daily quests\n";
    echo "  manageMetaverseSpecialAreas          - Manage special areas\n";
    echo "  balanceMetaverseWorldPopulation      - Balance world population\n";
    echo "  processMetaverseAnalytics            - Process analytics data\n";
    echo "  triggerAutonomousMetaverseEvents     - Trigger autonomous events\n";
    echo "  checkAndStartMetaverseTournaments    - Check and start tournaments\n";
    echo "  sendMetaverseReEngagementNotifications - Send re-engagement notifications\n";
    echo "\nOther commands:\n";
    echo "  status                               - Show current engagement status\n";
    echo "  setup-cron                          - Generate crontab entries\n";
    echo "  test                                - Run test suite\n\n";
}

/**
 * Show current status
 */
function showStatus() {
    echo "🌐 Metaverse Engagement Status\n";
    echo "================================\n\n";
    
    $metrics = calculateMetaverseEngagement();
    
    echo "📊 Overall Engagement Score: " . ($metrics['overall_score'] * 100) . "%\n";
    echo "👥 Active Users: " . $metrics['player_metrics']['active_users'] . "\n";
    echo "⏱️  Avg Session Duration: " . $metrics['player_metrics']['avg_session_duration'] . " minutes\n";
    echo "🎮 Total Interactions: " . $metrics['interaction_metrics']['total_interactions'] . "\n";
    echo "🤝 Social Engagement: " . ($metrics['social_metrics']['social_engagement_ratio'] * 100) . "%\n";
    echo "🏠 World Utilization: " . ($metrics['world_metrics']['capacity_utilization'] * 100) . "%\n\n";
    
    if ($metrics['overall_score'] < 0.4) {
        echo "⚠️  WARNING: Low engagement detected. Consider running engagement boosts.\n";
    } elseif ($metrics['overall_score'] > 0.8) {
        echo "🎉 EXCELLENT: High engagement! Metaverse is thriving.\n";
    } else {
        echo "✅ GOOD: Engagement levels are healthy.\n";
    }
    
    echo "\nRun 'php metaverse_automation.php monitorAndBoostMetaverseEngagement' to boost if needed.\n\n";
}

/**
 * Setup cron jobs
 */
function setupCron() {
    echo "🕐 Metaverse Automation Cron Setup\n";
    echo "===================================\n\n";
    echo "Add these entries to your crontab (run 'crontab -e'):\n\n";
    
    $cronJobs = getMetaverseCronJobs();
    
    foreach ($cronJobs as $job) {
        echo $job . "\n";
    }
    
    echo "\nAlternatively, run this command to add them automatically:\n";
    echo "(crontab -l 2>/dev/null; echo '# Purrr.love Metaverse Automation'; ";
    
    foreach ($cronJobs as $job) {
        echo "echo '$job'; ";
    }
    
    echo ") | crontab -\n\n";
}

/**
 * Run test suite
 */
function runTests() {
    echo "🧪 Running Metaverse Automation Tests\n";
    echo "=====================================\n\n";
    
    $tests = [
        'Database Connection' => function() {
            $pdo = get_db();
            return $pdo instanceof PDO;
        },
        'AI Activities System' => function() {
            return class_exists('MetaverseAIActivities');
        },
        'Gamification System' => function() {
            return class_exists('MetaverseGamification');
        },
        'World Dynamics System' => function() {
            return class_exists('MetaverseWorldDynamics');
        },
        'Analytics System' => function() {
            return class_exists('MetaverseEngagementAnalytics');
        }
    ];
    
    $passed = 0;
    $total = count($tests);
    
    foreach ($tests as $testName => $testFunction) {
        try {
            $result = $testFunction();
            if ($result) {
                echo "✅ $testName: PASSED\n";
                $passed++;
            } else {
                echo "❌ $testName: FAILED\n";
            }
        } catch (Exception $e) {
            echo "❌ $testName: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📈 Test Results: $passed/$total tests passed\n\n";
    
    if ($passed === $total) {
        echo "🎉 All systems operational! Ready for automation.\n";
    } else {
        echo "⚠️  Some systems need attention before full automation.\n";
    }
}

/**
 * Main CLI handler
 */
if ($argc < 2) {
    showUsage();
    exit(1);
}

$command = $argv[1];
$runner = new MetaverseAutomationRunner();

switch ($command) {
    case 'status':
        showStatus();
        break;
        
    case 'setup-cron':
        setupCron();
        break;
        
    case 'test':
        runTests();
        break;
        
    case 'help':
    case '--help':
    case '-h':
        showUsage();
        break;
        
    default:
        // Check if it's a valid automation function
        $validFunctions = [
            'monitorAndBoostMetaverseEngagement',
            'spawnAICatsInLowActivityWorlds',
            'updateMetaverseWorldWeather',
            'updateMetaverseSeasonalContent',
            'generateDailyMetaverseQuests',
            'manageMetaverseSpecialAreas',
            'balanceMetaverseWorldPopulation',
            'processMetaverseAnalytics',
            'triggerAutonomousMetaverseEvents',
            'checkAndStartMetaverseTournaments',
            'sendMetaverseReEngagementNotifications'
        ];
        
        if (in_array($command, $validFunctions)) {
            $runner->run($command);
        } else {
            echo "Error: Unknown command '$command'\n\n";
            showUsage();
            exit(1);
        }
}

exit(0);
?>
