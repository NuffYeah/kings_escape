import React, { useState } from "react";
import GameBoard from "./GameBoard";
import "./style.css";

export default function App() {
  const [gameStarted, setGameStarted] = useState(false);
  const [positions, setPositions] = useState({
    startX: 0,
    startY: 0,
    exitX: 7,
    exitY: 7,
  });

  const handleStart = () => {
    if (
      positions.startX === positions.exitX &&
      positions.startY === positions.exitY
    ) {
      alert("Start and exit positions cannot be the same!");
      return;
    }
    setGameStarted(true);
  };

  const handleReset = () => {
    setGameStarted(false);
    setPositions({ startX: 0, startY: 0, exitX: 7, exitY: 7 });
  };

  return (
    <div className="container">
      <header>
        <h1>🏰 The King's Escape</h1>
        <p>Help the King escape from enemy invasion!</p>
      </header>

      {!gameStarted ? (
        <div className="game-setup">
          <h2>Start New Game</h2>
          <div className="setup-form">
            <div className="form-group">
              <label>King's Starting Position:</label>
              <select
                value={positions.startX}
                onChange={(e) =>
                  setPositions({ ...positions, startX: parseInt(e.target.value) })
                }
              >
                {[...Array(8)].map((_, i) => (
                  <option key={i} value={i}>
                    {String.fromCharCode(65 + i)}
                  </option>
                ))}
              </select>
              <select
                value={positions.startY}
                onChange={(e) =>
                  setPositions({ ...positions, startY: parseInt(e.target.value) })
                }
              >
                {[...Array(8)].map((_, i) => (
                  <option key={i} value={i}>
                    {i + 1}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Exit Position:</label>
              <select
                value={positions.exitX}
                onChange={(e) =>
                  setPositions({ ...positions, exitX: parseInt(e.target.value) })
                }
              >
                {[...Array(8)].map((_, i) => (
                  <option key={i} value={i}>
                    {String.fromCharCode(65 + i)}
                  </option>
                ))}
              </select>
              <select
                value={positions.exitY}
                onChange={(e) =>
                  setPositions({ ...positions, exitY: parseInt(e.target.value) })
                }
              >
                {[...Array(8)].map((_, i) => (
                  <option key={i} value={i}>
                    {i + 1}
                  </option>
                ))}
              </select>
            </div>

            <button onClick={handleStart} className="btn btn-primary">
              Start Game
            </button>
          </div>
        </div>
      ) : (
        <GameBoard positions={positions} onReset={handleReset} />
      )}
    </div>
  );
}
