<?php
// DB connection settings
require_once 'db_connection.php';

// Drop existing tables (in reverse dependency order)
$conn->query("DROP TABLE IF EXISTS GameStats");
$conn->query("DROP TABLE IF EXISTS user_preferences");
$conn->query("DROP TABLE IF EXISTS Background_Images");
$conn->query("DROP TABLE IF EXISTS Users");

// Create Users table
$sql = "CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('player', 'admin') DEFAULT 'player' NOT NULL,
    registration_date DATETIME NOT NULL,
    last_login DATETIME
)";
$conn->query($sql) or die("Users table error: " . $conn->error);

// Create Background_Images table
$sql = "CREATE TABLE Background_Images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    image_name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    uploaded_by_user_id INT DEFAULT NULL,
    FOREIGN KEY (uploaded_by_user_id) REFERENCES Users(user_id) ON DELETE SET NULL
)";
$conn->query($sql) or die("Background_Images table error: " . $conn->error);

// Create user_preferences table
$sql = "CREATE TABLE user_preferences (
    preference_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    default_puzzle_size VARCHAR(10) DEFAULT '4x4',
    preferred_background_image_id INT,
    sound_enabled BOOLEAN DEFAULT TRUE,
    animations_enabled BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (preferred_background_image_id) REFERENCES Background_Images(image_id) ON DELETE SET NULL
)";
$conn->query($sql) or die("user_preferences table error: " . $conn->error);

// Create GameStats table
$sql = "CREATE TABLE GameStats (
    stat_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    puzzle_size VARCHAR(10) NOT NULL,
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'medium' NOT NULL,
    time_taken_seconds INT NOT NULL DEFAULT 0,
    moves_count INT NOT NULL DEFAULT 0,
    background_image_id INT DEFAULT NULL,
    win_status BOOLEAN NOT NULL DEFAULT FALSE,
    game_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (background_image_id) REFERENCES Background_Images(image_id) ON DELETE SET NULL,
    INDEX (user_id),
    INDEX (game_date)
)";
$conn->query($sql) or die("GameStats table error: " . $conn->error);

// Insert dummy user
$sql = "INSERT INTO Users (username, password_hash, email, role, registration_date)
        VALUES ('testuser', 'testhash', 'test@example.com', 'player', NOW())";
$conn->query($sql) or die("Insert user error: " . $conn->error);

// Insert dummy admin
$admin_password = password_hash('admin', PASSWORD_DEFAULT);
$sql = "INSERT INTO Users (username, password_hash, email, role, registration_date)
        VALUES ('admin', '$admin_password', 'admin@gmail.com', 'admin', NOW())";
$conn->query($sql) or die("Insert admin user error: " . $conn->error);

// Insert dummy background image
$sql = "INSERT INTO Background_Images (image_name, image_url, is_active, uploaded_by_user_id)
        VALUES ('Test Background', 'images/test.jpg', TRUE, 1)";
$conn->query($sql) or die("Insert background image error: " . $conn->error);

echo "✅ Database schema and dummy data created successfully.";

$conn->close();
?>
