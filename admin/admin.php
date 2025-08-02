<?php
// DB connection settings
require_once '../db_connection.php';
// Auth settings
require_once '../auth_functions.php';

session_start();

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../intro.html");
  exit();
}

// Default to users tab
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
$valid_tabs = ['users', 'backgrounds', 'stats'];

// Validate tab
if (!in_array($current_tab, $valid_tabs)) {
  $current_tab = 'users';
}

// Form submission handling for user management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $current_tab === 'users') {
  if (isset($_POST['edit_user']) && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
        die("CSRF token validation failed");
    }
  if (isset($_POST['edit_user'])) {
    // Handle user edit
    $edit_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    $sql = "UPDATE Users SET username = ?, email = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $username, $email, $role, $edit_id);
    
    if (!$stmt->execute()) {
      die("Error updating user: " . $stmt->error);
    }
  } 
  elseif (isset($_POST['reset_password'])) {
    // Handle password reset
    $reset_id = $_POST['user_id'];
    $new_password = password_hash('temporary123', PASSWORD_DEFAULT);
    
    $sql = "UPDATE Users SET password_hash = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_password, $reset_id);
    
    if (!$stmt->execute()) {
      die("Error resetting password: " . $stmt->error);
    }
  }
}

// Get all users (simplified query without game stats)
if ($current_tab === 'users') {
  $sql = "SELECT 
            user_id, 
            username, 
            email, 
            role, 
            registration_date, 
            last_login
          FROM Users
          ORDER BY registration_date DESC";
  
  $result = $conn->query($sql);
  if (!$result) {
    die("Error fetching users: " . $conn->error);
  }
  $users = $result->fetch_all(MYSQLI_ASSOC);
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fifteen Puzzle</title>
    <link rel="stylesheet" href="../css/admin.css">
  </head>
  <body>
    <header>
      <h1>Admin Dashboard</h1>
      <div class="user-info">
        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 
        (<a href="../logout.php">Logout</a>)
      </div>
    </header>

    <?php include 'admin_nav.php'; ?>

    <main>
      <?php 
      // Include the appropriate content based on current tab
      switch ($current_tab) {
        case 'users':
          include 'admin_users.php';
          break;
        case 'stats':
          include 'admin_stats.php';
          break;
        case 'backgrounds':
          include 'admin_backgrounds.php';
          break;
        default:
          include 'admin_users.php';
      }
      ?>
    </main>

    <!-- Edit User Modal (only needed for users tab) -->
    <?php if ($current_tab === 'users'): ?>
    <div id="editModal" class="modal">
      <div class="modal-content">
        <h2>Edit User</h2>
        <form method="POST" action="admin.php?tab=users">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <input type="hidden" name="user_id" id="modalUserId">
            
          <div class="form-group">
            <label for="modalUsername">Username</label>
            <input type="text" id="modalUsername" name="username" required>
          </div>
          
          <div class="form-group">
            <label for="modalEmail">Email</label>
            <input type="email" id="modalEmail" name="email" required>
          </div>
          
          <div class="form-group">
            <label for="modalRole">Role</label>
            <select id="modalRole" name="role" required>
              <option value="player">Player</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          
          <div class="form-actions">
            <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
            <button type="submit" class="btn btn-save" name="edit_user">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <script src="admin.js" defer></script>
    <?php $conn->close(); ?>
  </body>
</html>