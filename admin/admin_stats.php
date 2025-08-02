<?php
// DB connection settings
require_once '../db_connection.php';

session_start();

// Checks if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}

// Get aggregate game stats
$aggregateStats = getAggregateStats($conn);
$topPlayers = getTopPlayers($conn);

// Get individual player stats
$playerStats = [];
if (isset($_GET['player_id'])) {
  $playerStats = getPlayerStats($conn, (int)$_GET['player_id']);
}

function getAggregateStats($conn) {
  $stats = [];
  
  // Total games played
  $result = $conn->query("SELECT COUNT(*) as total_games FROM GameStats");
  $stats['total_games'] = $result->fetch_assoc()['total_games'];
  
  // Average time per puzzle size and difficulty
  $result = $conn->query("
    SELECT puzzle_size, difficulty_level,
            AVG(time_taken_seconds) as avg_time,
            COUNT(*) as games_played
    FROM GameStats
    GROUP BY puzzle_size, difficulty_level
    ORDER BY games_played DESC
  ");
  $stats['puzzle_stats'] = $result->fetch_all(MYSQLI_ASSOC);
  
  // Most popular background
  $result = $conn->query("
    SELECT b.image_name, COUNT(*) as usage_count
    FROM GameStats g
    LEFT JOIN Background_Images b ON g.background_image_id = b.image_id
    GROUP BY g.background_image_id
    ORDER BY usage_count DESC
    LIMIT 1
  ");
  $stats['popular_background'] = $result->fetch_assoc();
  
  return $stats;
}

function getTopPlayers($conn) {
  return $conn->query("
    SELECT u.user_id, u.username, 
            COUNT(*) as games_played,
            AVG(g.time_taken_seconds) as avg_time,
            MIN(g.time_taken_seconds) as best_time
    FROM GameStats g
    JOIN Users u ON g.user_id = u.user_id
    WHERE g.win_status = 1
    GROUP BY g.user_id
    ORDER BY games_played DESC, avg_time ASC
    LIMIT 10
  ")->fetch_all(MYSQLI_ASSOC);
}

function getPlayerStats($conn, $player_id) {
  $stats = [];
  
  // Use prepared statement for security
  $stmt = $conn->prepare("
    SELECT username, email, registration_date, last_login
    FROM Users
    WHERE user_id = ?
  ");
  $stmt->bind_param("i", $player_id);
  $stmt->execute();
  $stats['info'] = $stmt->get_result()->fetch_assoc();
  
  // Game history with prepared statement
  $stmt = $conn->prepare("
    SELECT g.*, b.image_name as background_name
    FROM GameStats g
    LEFT JOIN Background_Images b ON g.background_image_id = b.image_id
    WHERE g.user_id = ?
    ORDER BY g.game_date DESC
  ");
  $stmt->bind_param("i", $player_id);
  $stmt->execute();
  $stats['games'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  
  return $stats;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Statistics</title>
    <link rel="stylesheet" href="admin_styles.css">
  </head>
  <body>
    <div class="admin-container">
      <h1>Game Statistics</h1>
      
      <!-- Aggregate Statistics Section -->
      <section class="stats-section">
        <h2>Aggregate Statistics</h2>
        
        <div class="stats-grid">
          <div class="stat-card">
            <h3>Total Games Played</h3>
            <div class="stat-value"><?= number_format($aggregateStats['total_games']) ?></div>
          </div>
            
          <div class="stat-card">
            <h3>Most Popular Puzzle</h3>
            <div class="stat-value">
              <?php 
              $mostPopular = array_reduce($aggregateStats['puzzle_stats'], function($a, $b) {
                  return $a['games_played'] > $b['games_played'] ? $a : $b; 
              });
              echo htmlspecialchars($mostPopular['puzzle_size']);
              ?>
            </div>
            <div class="stat-detail">
              (<?= number_format($mostPopular['games_played']) ?> games)
            </div>
          </div>
            
          <div class="stat-card">
            <h3>Most Popular Background</h3>
            <div class="stat-value">
              <?= htmlspecialchars($aggregateStats['popular_background']['image_name'] ?? 'N/A') ?>
            </div>
            <div class="stat-detail">
              (<?= number_format($aggregateStats['popular_background']['usage_count'] ?? 0) ?> uses)
            </div>
          </div>
        </div>
        </table>
      </section>
        
      <!-- Leaderboard Section -->
      <section class="stats-section">
        <h2>Top Players</h2>
        <table class="stats-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Player</th>
              <th>Games Won</th>
              <th>Avg. Time</th>
              <th>Best Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topPlayers as $index => $player): ?>
            <tr>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($player['username']) ?></td>
              <td><?= number_format($player['games_played']) ?></td>
              <td><?= gmdate("i:s", $player['avg_time']) ?></td>
              <td><?= gmdate("i:s", $player['best_time']) ?></td>
              <td>
                <a href="admin_stats.php?player_id=<?= $player['user_id'] ?>" class="btn-view">
                  View Details
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
        
      <!-- Individual Player Stats (shown when player_id is set) -->
      <?php if (!empty($playerStats)): ?>
      <section class="stats-section">
        <h2>Player Statistics: <?= htmlspecialchars($playerStats['info']['username']) ?></h2>
        
        <div class="player-info">
          <div><strong>Email:</strong> <?= htmlspecialchars($playerStats['info']['email']) ?></div>
          <div><strong>Registered:</strong> <?= date('M j, Y', strtotime($playerStats['info']['registration_date'])) ?></div>
          <div><strong>Last Login:</strong> <?= $playerStats['info']['last_login'] ? date('M j, Y H:i', strtotime($playerStats['info']['last_login'])) : 'Never' ?></div>
        </div>
        
        <h3>Game History</h3>
        <table class="stats-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Puzzle Size</th>
              <th>Difficulty</th>
              <th>Background</th>
              <th>Time</th>
              <th>Moves</th>
              <th>Result</th>
            </tr>
        </thead>
        <tbody>
          <?php foreach ($playerStats['games'] as $game): ?>
          <tr>
            <td><?= date('M j, Y', strtotime($game['game_date'])) ?></td>
            <td><?= htmlspecialchars($game['puzzle_size']) ?></td>
            <td><?= ucfirst(htmlspecialchars($game['difficulty_level'])) ?></td>
            <td><?= htmlspecialchars($game['background_name'] ?? 'Default') ?></td>
            <td><?= gmdate("i:s", $game['time_taken_seconds']) ?></td>
            <td><?= number_format($game['moves_count']) ?></td>
            <td><?= $game['win_status'] ? '🏆 Won' : '❌ Lost' ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
          
        <a href="admin_stats.php" class="btn-back">← Back to Statistics</a>
      </section>
      <?php endif; ?>
    </div>
  </body>
</html>