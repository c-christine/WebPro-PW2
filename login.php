<?php
session_start();
require_once 'auth_functions.php';
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  
  // Authenticate user
  $user = authenticateUser($username, $password, $conn);
  
  if ($user) {
    // Successful login
    handleSuccessfulLogin($user);
  } else {
    // Failed login
    header("Location: intro.html?error=1");
    exit();
  }
} else {
  // Not a POST request
  header("Location: intro.html");
  exit();
}