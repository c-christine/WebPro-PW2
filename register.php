<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];

  // Validate
  $errors = [];
  
  if ($password !== $confirm) {
    $errors[] = 'password_mismatch';
  }
  
  if (strlen($password) < 8) {
    $errors[] = 'password_length';
  }
  
  // Check if user exists
  $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  
  if ($stmt->get_result()->num_rows > 0) {
    $errors[] = 'user_exists';
  }

  if (!empty($errors)) {
    header("Location: intro.html?errors=".implode(',', $errors));
    exit();
  }

  // Hash password and insert
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $registration_date = date('Y-m-d H:i:s'); // Current timestamp
  $stmt = $conn->prepare("INSERT INTO Users (
                        username,
                        email,
                        password_hash,
                        registration_date,
                        last_login) 
                        VALUES (?, ?, ?, NOW(), NOW())");
  $stmt->bind_param("ssss", $username, $email, $hash, $registration_date);
  
  if ($stmt->execute()) {
    header("Location: intro.html?success=1");
  } else {
    header("Location: intro.html?error=registration_failed");
  }
  exit();
}