import React, { useState } from 'react';
import Games from './Games';
import GameDetails from './GameDetails';
import GameEvents from './GameEvents';

type GameView = 'overview' | 'details' | 'events';

interface GameState {
  view: GameView;
  gameId?: number;
}

export default function GamesContainer() {
  const [gameState, setGameState] = useState<GameState>({ view: 'overview' });

  const handleGameSelect = (gameId: number) => {
    setGameState({ view: 'details', gameId });
  };

  const handleBackToOverview = () => {
    setGameState({ view: 'overview' });
  };

  const handleShowEvents = (gameId: number) => {
    setGameState({ view: 'events', gameId });
  };

  // Check URL for direct access to events
  React.useEffect(() => {
    const path = window.location.pathname;
    const match = path.match(/\/game\/(\d+)\/events/);
    if (match) {
      const gameId = parseInt(match[1], 10);
      setGameState({ view: 'events', gameId });
    }
  }, []);

  switch (gameState.view) {
    case 'details':
      return (
        <GameDetails
          gameId={gameState.gameId!}
          onBack={handleBackToOverview}
        />
      );
    
    case 'events':
      return (
        <GameEvents
          gameId={gameState.gameId!}
        />
      );
    
    default:
      return (
        <Games
          onGameSelect={handleGameSelect}
        />
      );
  }
}
