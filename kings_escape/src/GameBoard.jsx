/* eslint-disable react-hooks/exhaustive-deps */
import React, { useState, useEffect } from "react";

const boardSize = 8;

// Error Boundary Component
class GameBoardErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null, errorInfo: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    this.setState({
      error: error,
      errorInfo: errorInfo
    });
    console.error('GameBoard Error Boundary caught an error:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="error-boundary">
          <div className="error-message">
            <h2>Something went wrong with the game board</h2>
            <p>The game encountered an unexpected error. Please try refreshing the page or starting a new game.</p>
            <button 
              onClick={() => this.setState({ hasError: false, error: null, errorInfo: null })}
              className="btn btn-primary"
            >
              Try Again
            </button>
            <button 
              onClick={() => window.location.reload()}
              className="btn btn-secondary"
            >
              Refresh Page
            </button>
            {process.env.NODE_ENV === 'development' && (
              <details className="error-details">
                <summary>Error Details (Development)</summary>
                <pre>{this.state.error ? this.state.error.toString() : 'No error details available'}</pre>
                <pre>{this.state.errorInfo?.componentStack || 'No component stack available'}</pre>
              </details>
            )}
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}

function GameBoard({ positions, onReset }) {
  const [gameState, setGameState] = useState(null);
  const [messages, setMessages] = useState([]);

  const addMessage = (text, type = "info") => {
    setMessages((prev) => [...prev, { text, type }]);
  };

  const loadGameState = async (gameId) => {
    try {
      const response = await fetch(`http://localhost:80/kings_escape/api/get_game_state.php?game_id=${gameId}`);
      const data = await response.json();
      setGameState({ ...data, gameId });
    } catch (err) {
      console.log(err);
      addMessage("Failed to load game state.", "error");
    }
  };

  const startGame = async () => {
    try {
      const response = await fetch("http://localhost:80/kings_escape/api/start_game.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(positions),
      });
      const result = await response.json();
      if (result.success) {
        loadGameState(result.game_id);
        addMessage("Game started! Help the King escape!", "success");
      } else {
        addMessage(result.message || "Failed to start game", "error");
      }
    } catch (err) { 
      console.log(err);
      addMessage("Server error starting game.", "error");
    }
  };

  const handleMove = async (x, y) => {
    if (!gameState || gameState.game_status !== "active") return;

    const isValid = gameState.possible_moves.some(
      (m) => m.x === x && m.y === y
    );
    if (!isValid) {
      addMessage("Invalid move!", "error");
      return;
    }

    try {
      const response = await fetch("http://localhost:80/kings_escape/api/make_move.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ game_id: gameState.gameId, x, y }),
      });
      const result = await response.json();
      if (result.success) {
        await loadGameState(gameState.gameId);
        addMessage(`King moved to ${String.fromCharCode(65 + x)}${y + 1}`, "info");
      }
    } catch (err) {
      console.log(err);
      addMessage("Failed to make move.", "error");
    }
  };

  // Load initial game
  useEffect(() => {
    startGame();
  }, []);

  const renderSquare = (x, y) => {
    let className = (x + y) % 2 === 0 ? "chess-square light" : "chess-square dark";
    let content = "";

    if (!gameState) return <div key={`${x},${y}`} className={className}></div>;

    if (gameState.exit_position?.x === x && gameState.exit_position?.y === y) {
      className += " exit-position";
      content = <span className="exit-marker"></span>;
    }

    if (gameState.king_position?.position_x === x && gameState.king_position?.position_y === y) {
      className += " king-position";
      content = <span className="piece-king"></span>;
    }

    gameState.occupied_positions.forEach((p) => {
      if (p.position_x === x && p.position_y === y && p.is_enemy) {
        className += " enemy-piece";
        content = <span className={`enemypiece-${p.piece_type.toLowerCase()}`}></span>;
      }
    });

    if (gameState.possible_moves.some((m) => m.x === x && m.y === y)) {
      className += " possible-move";
      content = <span style={{ color: "#006400" }}>●</span>;
    }

    return (
      <div
        key={`${x},${y}`}
        className={className}
        onClick={() => handleMove(x, y)}
      >
        {content}
      </div>
    );
  };

  return (
    <div className="game-area">
      <div className="game-info">
        <div className="info-panel">
          <span>Move: {gameState?.current_move || 0}</span>
          <span>Status: {gameState?.game_status || "Active"}</span>
          <button onClick={onReset} className="btn btn-danger">
            New Game
          </button>
        </div>
      </div>

      <div className="chessboard">
        {[...Array(boardSize)].map((_, row) =>
          [...Array(boardSize)].map((_, col) => renderSquare(col, row))
        )}
      </div>

      <div className="game-messages">
        {messages.map((m, i) => (
          <div key={i} className={`message ${m.type}`}>
            {m.text}
          </div>
        ))}
      </div>
    </div>
  );
}

// Export GameBoard wrapped with Error Boundary
export default function GameBoardWithErrorBoundary(props) {
  return (
    <GameBoardErrorBoundary>
      <GameBoard {...props} />
    </GameBoardErrorBoundary>
  );
}






