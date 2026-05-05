document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const cells = document.querySelectorAll('.cell');
    const statusText = document.getElementById('current-turn-text');
    const scoreX = document.getElementById('score-x');
    const scoreO = document.getElementById('score-o');
    const scoreDraws = document.getElementById('score-draws');
    const restartBtn = document.getElementById('restart-btn');
    const resetScoresBtn = document.getElementById('reset-scores-btn');
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const playerXInput = document.getElementById('player-x-name');
    const playerOInput = document.getElementById('player-o-name');
    const modal = document.getElementById('result-modal');
    const resultTitle = document.getElementById('result-title');
    const resultMessage = document.getElementById('result-message');
    const modalClose = document.getElementById('modal-close');

    // State from PHP
    let gameState = window.initialGameState;

    // Initialize
    updateUI(gameState);
    loadDarkMode();

    // Event Listeners
    cells.forEach(cell => {
        cell.addEventListener('click', () => {
            const index = cell.getAttribute('data-index');
            if (gameState.status === 'playing' && gameState.board[index] === '') {
                makeMove(index);
            }
        });
    });

    restartBtn.addEventListener('click', () => {
        sendAction('restart');
    });

    resetScoresBtn.addEventListener('click', () => {
        sendAction('reset_scores');
    });

    modalClose.addEventListener('click', () => {
        modal.classList.remove('show');
        sendAction('restart');
    });

    darkModeToggle.addEventListener('change', () => {
        if (darkModeToggle.checked) {
            document.body.classList.add('dark-mode');
            document.body.classList.remove('light-mode');
            localStorage.setItem('theme', 'dark');
        } else {
            document.body.classList.add('light-mode');
            document.body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
        }
    });

    [playerXInput, playerOInput].forEach(input => {
        input.addEventListener('change', () => {
            updateNames();
        });
    });

    // Functions
    async function makeMove(index) {
        // Optimistic UI update for move animation
        const cell = cells[index];
        cell.classList.add('occupied');
        const content = cell.querySelector('.cell-content');
        content.textContent = gameState.turn;
        content.className = 'cell-content ' + (gameState.turn === 'X' ? 'symbol-x' : 'symbol-o');
        
        const formData = new FormData();
        formData.append('action', 'move');
        formData.append('index', index);

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const newState = await response.json();
            gameState = newState;
            updateUI(newState);
        } catch (error) {
            console.error('Error making move:', error);
        }
    }

    async function sendAction(action) {
        const formData = new FormData();
        formData.append('action', action);

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const newState = await response.json();
            gameState = newState;
            updateUI(newState);
        } catch (error) {
            console.error('Error sending action:', error);
        }
    }

    async function updateNames() {
        const formData = new FormData();
        formData.append('action', 'update_names');
        formData.append('playerX', playerXInput.value || 'Player 1');
        formData.append('playerO', playerOInput.value || 'Player 2');

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const newState = await response.json();
            gameState = newState;
            updateUI(newState);
        } catch (error) {
            console.error('Error updating names:', error);
        }
    }

    function updateUI(state) {
        // Update board
        state.board.forEach((val, i) => {
            const cell = cells[i];
            const content = cell.querySelector('.cell-content');
            
            // Highlight occupied cells
            if (val !== '') {
                cell.classList.add('occupied');
                if (content.textContent === '') { // Only animate if it's a new value
                    content.textContent = val;
                    content.className = 'cell-content ' + (val === 'X' ? 'symbol-x' : 'symbol-o');
                }
            } else {
                cell.classList.remove('occupied');
                cell.classList.remove('winner');
                content.textContent = '';
                content.className = 'cell-content';
            }
        });

        // Highlight winning combo
        if (state.win_combo) {
            state.win_combo.forEach(index => {
                cells[index].classList.add('winner');
            });
        }

        // Update scores
        scoreX.textContent = state.scores.X;
        scoreO.textContent = state.scores.O;
        scoreDraws.textContent = state.scores.Draws;

        // Update status text
        if (state.status === 'playing') {
            statusText.textContent = `${state.players[state.turn]}'s Turn (${state.turn})`;
        } else if (state.status === 'win') {
            statusText.textContent = `${state.players[state.winner]} Wins!`;
            showResultModal(state);
        } else if (state.status === 'draw') {
            statusText.textContent = "It's a Draw!";
            showResultModal(state);
        }

        // Update name inputs if they changed from other source
        playerXInput.value = state.players.X;
        playerOInput.value = state.players.O;
    }

    function showResultModal(state) {
        if (state.status === 'win') {
            resultTitle.textContent = "Winner!";
            resultTitle.style.color = state.winner === 'X' ? 'var(--cupids-arrow)' : 'var(--thistle-down)';
            resultMessage.textContent = `Congratulations, ${state.players[state.winner]}!`;
        } else {
            resultTitle.textContent = "Draw!";
            resultTitle.style.color = 'var(--puddle-jumper)';
            resultMessage.textContent = "Good game, both players!";
        }
        
        setTimeout(() => {
            modal.classList.add('show');
        }, 600); // Small delay to see the winning move/highlight
    }

    function loadDarkMode() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            darkModeToggle.checked = true;
            document.body.classList.add('dark-mode');
            document.body.classList.remove('light-mode');
        } else {
            darkModeToggle.checked = false;
            document.body.classList.add('light-mode');
            document.body.classList.remove('dark-mode');
        }
    }
});
