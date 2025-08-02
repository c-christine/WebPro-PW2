
<?php
// DB connection settings
require_once 'db_connection.php';

// Get POST data safely
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$default_puzzle_size = isset($_POST['default_puzzle_size']) ? $conn->real_escape_string($_POST['default_puzzle_size']) : '4x4';
$preferred_background_image_id = isset($_POST['preferred_background_image_id']) ? intval($_POST['preferred_background_image_id']) : NULL;
$sound_enabled = isset($_POST['sound_enabled']) ? ($_POST['sound_enabled'] === '1' || strtolower($_POST['sound_enabled']) === 'true' ? 1 : 0) : 1;
$animations_enabled = isset($_POST['animations_enabled']) ? ($_POST['animations_enabled'] === '1' || strtolower($_POST['animations_enabled']) === 'true' ? 1 : 0) : 1;

// Check user_id
if ($user_id <= 0) {
    die("Invalid user ID.");
}

// Check if preferences exist for this user
$sqlCheck = "SELECT preference_id FROM user_preferences WHERE user_id = $user_id";
$result = $conn->query($sqlCheck);

if ($result && $result->num_rows > 0) {
    // Update existing preferences
    $sqlUpdate = "UPDATE user_preferences SET
        default_puzzle_size = '$default_puzzle_size',
        preferred_background_image_id = " . ($preferred_background_image_id !== NULL ? $preferred_background_image_id : "NULL") . ",
        sound_enabled = $sound_enabled,
        animations_enabled = $animations_enabled
        WHERE user_id = $user_id";

    if ($conn->query($sqlUpdate) === TRUE) {
        echo "Preferences updated successfully.";
    } else {
        echo "Error updating preferences: " . $conn->error;
    }
} else {
    // Insert new preferences
    $sqlInsert = "INSERT INTO user_preferences 
        (user_id, default_puzzle_size, preferred_background_image_id, sound_enabled, animations_enabled)
        VALUES
        ($user_id, '$default_puzzle_size', " . ($preferred_background_image_id !== NULL ? $preferred_background_image_id : "NULL") . ", $sound_enabled, $animations_enabled)";

    if ($conn->query($sqlInsert) === TRUE) {
        echo "Preferences saved successfully.";
    } else {
        echo "Error saving preferences: " . $conn->error;
    }
}

$conn->close();
?>
