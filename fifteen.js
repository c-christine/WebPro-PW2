window.onload = function initPuzzle() {
  const board = document.getElementById('puzzle-board');
  const sizeSelect = document.getElementById('size');
  const backgroundSelect = document.getElementById('background');
  const shuffleBtn = document.getElementById('shuffle');
  const solveBtn = document.getElementById('solve');
  const timerDisplay = document.getElementById('timer');
  const movesDisplay = document.getElementById('moves');
  const musicToggle = document.getElementById('music');
  const music = document.getElementById('bg-music');
  const gameInfo = document.getElementById('game-info');
  const logoutBtn = document.getElementById('logout-btn');
  const backgroundId = backgroundSelect.selectedIndex + 1;

  if (!board || !sizeSelect || !backgroundSelect || !shuffleBtn || !solveBtn || !timerDisplay || !movesDisplay || !musicToggle || !music || !gameInfo) {
    console.error('One or more required DOM elements are missing.');
    return;
  }

  const undoBtn = document.createElement('button');
  undoBtn.textContent = '↩️ Undo';
  undoBtn.id = 'undo';
  gameInfo.appendChild(undoBtn);

  let tiles = [];
  let emptyIndex = 0;
  let puzzleSize = 4;
  let timerInterval = null;
  let seconds = 0;
  let moves = 0;
  let moveHistory = [];
  let isSolving = false;
  let isShuffling = false;
  let gameStarted = false;
  let canMove = true; // input lock

  function updateTimer() {
    if (!isSolving && !isShuffling && gameStarted) {
      seconds++;
      const mins = String(Math.floor(seconds / 60)).padStart(2, '0');
      const secs = String(seconds % 60).padStart(2, '0');
      timerDisplay.textContent = `⏱️ Time: ${mins}:${secs}`;
    }
  }

  function resetGame() {
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }
    seconds = 0;
    moves = 0;
    moveHistory = [];
    movesDisplay.textContent = '📦 Moves: 0';
    timerDisplay.textContent = '⏱️ Time: 00:00';
    gameStarted = false;
    canMove = true;
  }

  function startGame() {
    if (!gameStarted) {
      gameStarted = true;
      if (timerInterval) clearInterval(timerInterval);
      timerInterval = setInterval(updateTimer, 1000);
    }
  }

  function generateTiles(size) {
    if (!size || size < 2) return;

    puzzleSize = size;
    const total = size * size;
    board.innerHTML = '';
    board.className = `puzzle-${size}x${size}`;
    board.style.gridTemplateColumns = `repeat(${size}, 100px)`;
    board.style.gridTemplateRows = `repeat(${size}, 100px)`;
    tiles = [];

    const backgroundImage = backgroundSelect.value;
    const backgroundPath = `images/${backgroundImage}`;

    for (let i = 0; i < total - 1; i++) {
      const tile = document.createElement('div');
      tile.className = 'tile';
      tile.textContent = i + 1;
      tile.dataset.value = i + 1;
      tile.style.backgroundImage = `url("${backgroundPath}")`;
      tile.style.backgroundSize = `${100 * size}px ${100 * size}px`;
      tile.style.backgroundPosition = `-${(i % size) * 100}px -${Math.floor(i / size) * 100}px`;
      board.appendChild(tile);
      tiles.push(tile);
    }

    const empty = document.createElement('div');
    empty.className = 'tile empty';
    empty.dataset.value = '0';
    empty.textContent = '';
    board.appendChild(empty);
    tiles.push(empty);
    emptyIndex = total - 1;

    updateTilePositions();
    setTileListeners();
    highlightTiles();
  }

  function updateTilePositions() {
    tiles.forEach((tile, index) => {
      const row = Math.floor(index / puzzleSize) + 1;
      const col = (index % puzzleSize) + 1;
      tile.style.gridRowStart = row;
      tile.style.gridColumnStart = col;
    });
  }

  function setTileListeners() {
    tiles.forEach(tile => {
      tile.onclick = null;
    });

    // Use event delegation through the board
    board.onclick = (e) => {
      if (!canMove || isSolving || isShuffling) return;
        
      const tile = e.target.closest('.tile:not(.empty)');
      if (!tile) return;
        
      const index = tiles.indexOf(tile);
      if (index === -1 || !tile.classList.contains('movablepiece')) return;
        
      startGame();
      canMove = false;
      swapTiles(index);
        
      setTimeout(() => {
        canMove = true;
      }, 150);
    };
  }

  function isMovable(index) {
    if (index === emptyIndex) return false;

    const row = Math.floor(index / puzzleSize);
    const col = index % puzzleSize;
    const erow = Math.floor(emptyIndex / puzzleSize);
    const ecol = emptyIndex % puzzleSize;
    return (
      (row === erow && Math.abs(col - ecol) === 1) ||
      (col === ecol && Math.abs(row - erow) === 1)
    );
  }

  function highlightTiles() {
    tiles.forEach(tile => tile.classList.remove('movablepiece'));

    const movableTiles = [];
    for (let i = 0; i < tiles.length; i++) {
      if (i !== emptyIndex && isMovable(i)) {
          movableTiles.push(i);
      }
    }
    
    // Highlight them
    movableTiles.forEach(i => tiles[i].classList.add('movablepiece'));
  }

  function swapTiles(index, record = true) {
    if (!isMovable(index)) return;

    if (record) {
      moveHistory.push(emptyIndex);
      if (moveHistory.length > 100) moveHistory.shift();
    }

    // Swap positions
    [tiles[emptyIndex], tiles[index]] = [tiles[index], tiles[emptyIndex]];
    emptyIndex = index;

    // Update DOM positions
    tiles.forEach((tile, i) => {
      const row = Math.floor(i / puzzleSize) + 1;
      const col = (i % puzzleSize) + 1;
      tile.style.gridRowStart = row;
      tile.style.gridColumnStart = col;
    });

    if (record && !isShuffling) {
      moves++;
      movesDisplay.textContent = `📦 Moves: ${moves}`;
    }

    highlightTiles();
    if (!isShuffling) checkWin();
}

  function undoMove() {
    if (moveHistory.length > 0 && !isSolving && !isShuffling && canMove) {
      canMove = false;
      const lastMove = moveHistory.pop();
      swapTiles(lastMove, false);
      moves = Math.max(0, moves - 1);
      movesDisplay.textContent = `📦 Moves: ${moves}`;
      setTimeout(() => {
        canMove = true;
      }, 150);
    }
  }

  async function shuffleTiles() {
    if (isSolving || isShuffling) return;

    isShuffling = true;
    resetGame();

    const shuffleMoves = puzzleSize * 50;
    let lastMove = null;

    const originalCheckWin = checkWin;
    checkWin = () => {}; // disable win check while shuffling

    for (let i = 0; i < shuffleMoves; i++) {
      const neighbors = tiles
        .map((_, i) => i)
        .filter(i => isMovable(i) && i !== lastMove);

      if (neighbors.length === 0) break; // Defensive break

      const rand = neighbors[Math.floor(Math.random() * neighbors.length)];
      swapTiles(rand);
      lastMove = emptyIndex;

      await new Promise(resolve => setTimeout(resolve, 5));
    }

    checkWin = originalCheckWin;
    moveHistory = [];
    moves = 0;
    movesDisplay.textContent = '📦 Moves: 0';

    setTileListeners();
    highlightTiles();

    isShuffling = false;

    if (!timerInterval) {
      timerInterval = setInterval(updateTimer, 1000);
    }
  }

  function checkWin() {
    for (let i = 0; i < tiles.length - 1; i++) {
      if (tiles[i].textContent !== (i + 1).toString()) {
        return;
      }
    }
    if (tiles[tiles.length - 1].textContent === '') {
      if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
      }
      setTimeout(() => {
        alert(`🎉 You won in ${moves} moves and ${seconds} seconds!`);
        saveHighScore(moves, seconds);
      }, 200);
    }
  }

  function saveHighScore(movesCount, timeSeconds) {
    const highScores = JSON.parse(localStorage.getItem('puzzleHighScores')) || [];
    highScores.push({
      moves: movesCount,
      time: timeSeconds,
      size: puzzleSize,
      date: new Date().toLocaleString(),
    });
    localStorage.setItem('puzzleHighScores', JSON.stringify(highScores));
  }

  function showHighScores() {
    const highScores = JSON.parse(localStorage.getItem('puzzleHighScores')) || [];
    if (highScores.length > 0) {
      const scoresList = highScores
        .sort((a, b) => a.moves - b.moves || a.time - b.time)
        .slice(0, 5)
        .map(score =>
          `Size: ${score.size}x${score.size} - Moves: ${score.moves} - Time: ${Math.floor(score.time / 60)}m${score.time % 60}s - ${score.date}`
        )
        .join('\n');
      alert(`🏆 High Scores:\n${scoresList}`);
    } else {
      alert('No high scores yet!');
    }
  }

  document.addEventListener('keydown', (e) => {
    if (isSolving || isShuffling || !canMove) return;

    let moveIndex = -1;
    const row = Math.floor(emptyIndex / puzzleSize);
    const col = emptyIndex % puzzleSize;

    switch (e.key) {
      case 'ArrowUp':
        if (row > 0) moveIndex = emptyIndex - puzzleSize;
        break;
      case 'ArrowDown':
        if (row < puzzleSize - 1) moveIndex = emptyIndex + puzzleSize;
        break;
      case 'ArrowLeft':
        if (col > 0) moveIndex = emptyIndex - 1;
        break;
      case 'ArrowRight':
        if (col < puzzleSize - 1) moveIndex = emptyIndex + 1;
        break;
      default:
        return;
    }

    if (moveIndex !== -1 && isMovable(moveIndex)) {
      startGame();
      canMove = false;
      swapTiles(moveIndex);
      setTimeout(() => {
        canMove = true;
      }, 150);
    }
  });

  async function solvePuzzle() {
    if (isSolving || isShuffling) return;
    isSolving = true;

    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }

    // Reset puzzle to solved state
    generateTiles(puzzleSize);

    moves = 0;
    seconds = 0;
    movesDisplay.textContent = `📦 Moves: 0`;
    timerDisplay.textContent = `⏱️ Time: 00:00`;
    gameStarted = false;

    isSolving = false;
    highlightTiles();
  }

  const highScoresBtn = document.createElement('button');
  highScoresBtn.textContent = '🏆 High Scores';
  highScoresBtn.onclick = showHighScores;
  gameInfo.appendChild(highScoresBtn);

  [undoBtn, highScoresBtn].forEach(btn => {
    btn.style.marginLeft = '1rem';
    btn.style.padding = '0.5rem 1rem';
    btn.style.border = 'none';
    btn.style.borderRadius = '10px';
    btn.style.backgroundColor = '#6c5ce7';
    btn.style.color = 'white';
    btn.style.fontWeight = 'bold';
    btn.style.cursor = 'pointer';
    btn.style.transition = 'background 0.2s ease';
    btn.onmouseenter = () => btn.style.backgroundColor = '#341f97';
    btn.onmouseleave = () => btn.style.backgroundColor = '#6c5ce7';
  });

  shuffleBtn.onclick = shuffleTiles;
  solveBtn.onclick = solvePuzzle;
  undoBtn.onclick = undoMove;

  backgroundSelect.onchange = function () {
    if (!isSolving && !isShuffling) generateTiles(puzzleSize);
  };

  sizeSelect.onchange = function () {
    if (!isSolving && !isShuffling) {
      const selectedSize = parseInt(sizeSelect.value.charAt(0), 10);
      generateTiles(selectedSize);
      resetGame();
    }
  };

  musicToggle.onchange = function () {
    if (musicToggle.checked) {
      music.play().catch(() => {});
    } else {
      music.pause();
      music.currentTime = 0;
    }
  };

  music.pause(); // Pauses music
  musicToggle.checked = false; // Makes sure music checkbox is unchecked
  
  generateTiles(puzzleSize);
  resetGame();

  // Logout button
  if (logoutBtn) {
    logoutBtn.onclick = function() {
      window.location.href = 'logout.php';
    };
  }
};