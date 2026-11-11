**This project was developed as part of the curriculum at Boston City Campus. This is for portfolio demonstration purposes only**

- This is King's escape!
- Guide the king piece to safety before it can be captured by the enemies.
- The king only gets one move per turn, and the monsters are endless.

King's escape is built using a 3-Tier (Frontend, Backend, and Database) architecture. It is a web-based chess puzzle game where the player navigates a King across an 8×8 board to reach an exit point while avoiding invading enemy pieces.

**Tech Stack**
- Frontend : React (Vite)
- Backend: PHP
- Database: MySQL

**Architecture**

**Frontend**:  React (VITE) and CSS. 

Key Components:
- App.jsx: Controls the initial setup screen, allowing the player to pick starting coordinates for the King and the Exit square.
- GameBoard.jsx: Renders the interactive 8×8 chessboard grid, shows pieces (King, enemies, exit point), highlights available moves, and handles user clicks.
- Error Handling: Uses GameBoardErrorBoundary to catch UI crashes.
- Role: Captures user moves, renders real-time game status, and communicates with the backend via fetch() JSON requests.

**Backend**: PHP (Using RESTful JSON Endpoints).

Key Components:

API Layer (api/): 
- start_game.php: Initializes a new game session with start and exit coordinates. 
- make_move.php: Processes the King's move and updates the state.
- get_game_state.php: Returns the current board layout, move count, piece positions, and valid next moves.
- add_enemy.php: Spawns enemy pieces during gameplay.

Core Game Engine (Game.php): 
- Enforces rules (valid 1-step moves in 8 directions, board boundaries).
- Calculates collision detection and occupied squares.
- Evaluates win conditions (King reaches exit) and loss conditions (King trapped or captured).

**Database**:  MySQL connected via PHP PDO.

Key Components:
- Database.php: Manages the database connection using the Singleton pattern to reuse a single PDO instance with prepared statements.
- Schema (database_setup.sql):
- game_sessions: Tracks metadata for each game (unique game ID, starting position, exit position, current move count, status: active, won, or lost).
- chessboard_positions: Stores piece coordinates (King and enemies) mapped to each move number, maintaining history.
