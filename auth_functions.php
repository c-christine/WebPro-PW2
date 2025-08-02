<?php
function authenticateUser($username, $password, $conn) {
  $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM Users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password_hash'])) {
      return $user; // Success
    }
  }
  return false; // Failure
}

function handleSuccessfulLogin($userData) {
  session_start();
  $_SESSION['user_id'] = $userData['user_id'];
  $_SESSION['username'] = $userData['username'];
  $_SESSION['role'] = $userData['role'];

  // Last login time
  global $conn;
  $updateSql = "UPDATE Users SET last_login = NOW() WHERE user_id = ?";
  $stmt = $conn->prepare($updateSql);
  $stmt->bind_param("i", $userData['user_id']);
  $stmt->execute();

  if ($userData['role'] === 'admin') {
    header("Location: admin/admin.php");
  } else {
    header("Location: game.php");
  }
  exit();
}
?>