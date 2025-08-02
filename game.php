<?php
session_start();
require_once 'auth_functions.php';

// Go back to intro.html if user is not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: intro.html");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Fifteen Puzzle</title>
    <link rel="stylesheet" href="css/game.css" />
    <script src="fifteen.js" defer></script>
    <script src="record_game.js" defer></script>
  </head>
  <body>
    <header>
      <h1>Fifteen Puzzle</h1>
    </header>

    <section id="controls" aria-label="Game Controls">
      <h2>Controls</h2>

      <div class="selector">
        <label for="size">Puzzle Size:</label>
        <select name="size" id="size" aria-describedby="sizeHelp">
          <option value="3x3">3x3</option>
          <option value="4x4" selected>4x4</option>
          <option value="5x5">5x5</option>
        </select>
      </div>

      <div class="selector">
        <label for="background">Background Image:</label>
        <select name="background" id="background" aria-describedby="backgroundHelp">
          <option value="animal.png">Animal</option>
          <option value="forest.png">Forest</option>
          <option value="city.jpg">City</option>
          <option value="monalisa.jpg">Mona Lisa</option>
          <option value="beach.png">Beach</option>
        </select>
      </div>

      <div class="selector">
        <label for="music">🎵 Music:</label>
        <input type="checkbox" id="music" aria-checked="false" />
      </div>
    </section>

    <section id="game-info" aria-live="polite" aria-atomic="true">
      <h2>Game Info</h2>
      <div id="timer" role="timer" aria-live="polite">⏱️ Time: 00:00</div>
      <div id="moves">📦 Moves: 0</div>
      <button id="shuffle" type="button" aria-label="Shuffle puzzle">🔀 Shuffle</button>
      <button id="solve" type="button" aria-label="Solve puzzle">💡 Solve</button>
    </section>

    <form id="preferences-form" action="save_preferences.php" method="POST" aria-label="Save preferences">
      <input type="hidden" name="user_id" value="1" />
      <input type="hidden" name="default_puzzle_size" id="hidden-size" value="4x4" />
      <input type="hidden" name="preferred_background_image_id" id="hidden-bg" value="1" />
      <input type="hidden" name="sound_enabled" id="hidden-sound" value="1" />
      <input type="hidden" name="animations_enabled" id="hidden-animations" value="1" />
      <button type="submit">💾 Save Preferences</button>
    </form>

    <form action="logout.php" method="post" style="display: inline;">
      <button type="submit" class="logout-button">🚪 Logout</button>
    </form>

    <main id="game-container">
      <div id="puzzle-board" class="puzzle-4x4" role="grid" aria-label="Puzzle board">
        <!-- Tiles: numbered 1-15 and 1 blank tile -->
        <div class="tile" role="gridcell" aria-label="Tile 1" style="background-position: 0px 0px;">1</div>
        <div class="tile" role="gridcell" aria-label="Tile 2" style="background-position: -100px 0px;">2</div>
        <div class="tile" role="gridcell" aria-label="Tile 3" style="background-position: -200px 0px;">3</div>
        <div class="tile" role="gridcell" aria-label="Tile 4" style="background-position: -300px 0px;">4</div>
        <div class="tile" role="gridcell" aria-label="Tile 5" style="background-position: 0px -100px;">5</div>
        <div class="tile" role="gridcell" aria-label="Tile 6" style="background-position: -100px -100px;">6</div>
        <div class="tile" role="gridcell" aria-label="Tile 7" style="background-position: -200px -100px;">7</div>
        <div class="tile" role="gridcell" aria-label="Tile 8" style="background-position: -300px -100px;">8</div>
        <div class="tile" role="gridcell" aria-label="Tile 9" style="background-position: 0px -200px;">9</div>
        <div class="tile" role="gridcell" aria-label="Tile 10" style="background-position: -100px -200px;">10</div>
        <div class="tile" role="gridcell" aria-label="Tile 11" style="background-position: -200px -200px;">11</div>
        <div class="tile" role="gridcell" aria-label="Tile 12" style="background-position: -300px -200px;">12</div>
        <div class="tile" role="gridcell" aria-label="Tile 13" style="background-position: 0px -300px;">13</div>
        <div class="tile" role="gridcell" aria-label="Tile 14" style="background-position: -100px -300px;">14</div>
        <div class="tile" role="gridcell" aria-label="Tile 15" style="background-position: -200px -300px;">15</div>
        <div class="tile empty" role="gridcell" aria-label="Empty tile" aria-disabled="true"></div>
      </div>
    </main>

    <audio id="bg-music" loop>
      <source src="music.mp3" type="audio/mpeg">
      <!-- Fallback text -->
      Your browser does not support the audio element.
    </audio>
  </body>
</html>

