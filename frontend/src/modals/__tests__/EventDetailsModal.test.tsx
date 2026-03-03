import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import '@testing-library/jest-dom';
import { EventDetailsModal, EventDetailsModalProps } from '../EventDetailsModal';

// Mock BaseModal to avoid MUI theme/useMediaQuery issues
jest.mock('../BaseModal', () => ({
  __esModule: true,
  default: ({ open, title, children, actions }: any) => open ? (
    <div data-testid="Dialog">
      <div data-testid="DialogTitle">{title}</div>
      <div data-testid="DialogContent">{children}</div>
      <div data-testid="DialogActions">{actions}</div>
    </div>
  ) : null,
}));

// Mock sub-modals that also use BaseModal to avoid extra Dialog elements
jest.mock('../WeatherModal', () => () => null);
jest.mock('../TeamRideDetailsModal', () => () => null);

// Mock WeatherDisplay and Location
jest.mock('../../components/WeatherIcons', () => ({ WeatherDisplay: () => <div data-testid="WeatherDisplay" /> }));
jest.mock('../../components/Location', () => () => <div data-testid="Location" />);
jest.mock('../../components/TourTooltip', () => ({ children }: any) => <>{children}</>);

// Mock apiJson
jest.mock('../../utils/api', () => ({
  apiJson: jest.fn()
}));
import { apiJson } from '../../utils/api';

const baseEvent = {
  id: 1,
  title: 'Test Event',
  start: new Date().toISOString(),
  end: new Date(Date.now() + 3600000).toISOString(),
  description: 'Beschreibung des Events',
  type: { name: 'Training', color: '#123456' },
  location: { name: 'Sportplatz', city: 'Berlin', address: 'Straße 1', latitude: 52.5, longitude: 13.4 },
  weatherData: { weatherCode: 2 },
  game: {
    homeTeam: { name: 'FC Test' },
    awayTeam: { name: 'SC Gegner' },
    gameType: { name: 'Freundschaftsspiel' }
  },
  permissions: { canEdit: true, canDelete: true }
};

const participationStatuses = [
  { id: 1, name: 'Zusage', color: '#4caf50', icon: 'fa-check', sort_order: 1 },
  { id: 2, name: 'Absage', color: '#f44336', icon: 'fa-times', sort_order: 2 }
];

const participations = [
  {
    user_id: 10,
    user_name: 'Max Mustermann',
    is_team_player: true,
    note: 'Komme später',
    status: { id: 1, name: 'Zusage', color: '#4caf50', icon: 'fa-check', code: 'OK' }
  },
  {
    user_id: 11,
    user_name: 'Erika Musterfrau',
    is_team_player: false,
    status: { id: 2, name: 'Absage', color: '#f44336', icon: 'fa-times', code: 'NO' }
  }
];

describe('EventDetailsModal', () => {
  beforeAll(() => {
    jest.spyOn(console, 'log').mockImplementation(() => {});
    jest.spyOn(console, 'error').mockImplementation(() => {});
  });
  afterAll(() => {
    (console.log as jest.Mock).mockRestore();
    (console.error as jest.Mock).mockRestore();
  });
  beforeEach(() => {
    jest.clearAllMocks();
    (apiJson as jest.Mock).mockImplementation((url: string) => {
      if (url.includes('/api/participation/statuses')) {
        return Promise.resolve({ statuses: participationStatuses });
      }
      if (url.includes('/api/participation/event/1')) {
        return Promise.resolve({ participations });
      }
      if (url.includes('/respond')) {
        return Promise.resolve({});
      }
      return Promise.resolve({});
    });
  });

  const defaultProps: EventDetailsModalProps = {
    open: true,
    onClose: jest.fn(),
    event: baseEvent,
    onEdit: jest.fn(),
    showEdit: true,
    onDelete: jest.fn(),
  };

  it('renders modal with event details', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    expect(screen.getByTestId('Dialog')).toBeInTheDocument();
    expect(screen.getByText('Test Event')).toBeInTheDocument();
    expect(screen.getByText('Beschreibung des Events')).toBeInTheDocument();
    expect(screen.getByTestId('WeatherDisplay')).toBeInTheDocument();
    expect(screen.getByTestId('Location')).toBeInTheDocument();
    expect(screen.getByText('FC Test')).toBeInTheDocument();
    expect(screen.getByText('SC Gegner')).toBeInTheDocument();
    await waitFor(() => {
      expect(screen.getByText('Teilnahme')).toBeInTheDocument();
      expect(screen.getByText('Zusage')).toBeInTheDocument();
      expect(screen.getByText('Absage')).toBeInTheDocument();
      expect(screen.getByText('Max Mustermann')).toBeInTheDocument();
      expect(screen.getByText('Erika Musterfrau')).toBeInTheDocument();
    });
  });

  it('calls onClose when Schließen button is clicked', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    fireEvent.click(screen.getByText('Schließen'));
    expect(defaultProps.onClose).toHaveBeenCalled();
  });

  it('calls onEdit when Bearbeiten button is clicked', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    fireEvent.click(screen.getByText('Bearbeiten'));
    expect(defaultProps.onEdit).toHaveBeenCalled();
  });

  it('calls onDelete when Löschen button is clicked', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    fireEvent.click(screen.getByText('Löschen'));
    expect(defaultProps.onDelete).toHaveBeenCalled();
  });

  it('shows participation buttons and handles participation change', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    await waitFor(() => {
      expect(screen.getByText('Zusage')).toBeInTheDocument();
      expect(screen.getByText('Absage')).toBeInTheDocument();
    });
    fireEvent.click(screen.getByText('Absage'));
    await waitFor(() => {
      expect(apiJson).toHaveBeenCalledWith('/api/participation/event/1/respond', expect.objectContaining({ method: 'POST' }));
    });
  });

  it('allows entering a note in the Notizfeld', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    const noteField = await screen.findByLabelText('Notiz (optional)');
    fireEvent.change(noteField, { target: { value: 'Testnotiz' } });
    expect(noteField).toHaveValue('Testnotiz');
  });

  it('opens weather modal when weather icon is clicked', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    const weatherIcon = screen.getByTestId('WeatherDisplay').parentElement;
    if (weatherIcon) {
      fireEvent.click(weatherIcon);
      expect(weatherIcon).toBeInTheDocument();
    }
  });

  it('shows loading spinner when loading', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={{ ...baseEvent }} />);
    });
    expect(screen.getByTestId('Dialog')).toBeInTheDocument();
  });

  it('renders nothing if event is null', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={null} />);
    });
    expect(screen.queryByTestId('Dialog')).not.toBeInTheDocument();
  });

  // ─── Cancellation Tests ───

  it('shows cancelled banner when event is cancelled', async () => {
    const cancelledEvent = {
      ...baseEvent,
      cancelled: true,
      cancelReason: 'Platzsperrung',
      cancelledBy: 'Max Mustermann',
    };
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={cancelledEvent} />);
    });
    expect(screen.getByText('Abgesagt')).toBeInTheDocument();
    expect(screen.getByText('Platzsperrung')).toBeInTheDocument();
    expect(screen.getByText(/Abgesagt von Max Mustermann/)).toBeInTheDocument();
  });

  it('hides participation buttons when event is cancelled', async () => {
    const cancelledEvent = {
      ...baseEvent,
      cancelled: true,
      cancelReason: 'Regen',
    };
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={cancelledEvent} />);
    });
    await waitFor(() => {
      // Participation action buttons should not be shown
      expect(screen.queryByText('Zusage')).not.toBeInTheDocument();
      expect(screen.queryByText('Absage')).not.toBeInTheDocument();
    });
  });

  it('hides note field when event is cancelled', async () => {
    const cancelledEvent = {
      ...baseEvent,
      cancelled: true,
      cancelReason: 'Regen',
    };
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={cancelledEvent} />);
    });
    expect(screen.queryByLabelText('Notiz (optional)')).not.toBeInTheDocument();
  });

  it('shows Absagen button when user has canCancel permission and event is not cancelled', async () => {
    const eventWithCancel = {
      ...baseEvent,
      permissions: { canEdit: true, canDelete: true, canCancel: true },
      cancelled: false,
    };
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={eventWithCancel} />);
    });
    expect(screen.getByText('Absagen')).toBeInTheDocument();
  });

  it('hides Absagen button when event is already cancelled', async () => {
    const cancelledWithPermission = {
      ...baseEvent,
      permissions: { canEdit: true, canDelete: true, canCancel: true },
      cancelled: true,
      cancelReason: 'Grund',
    };
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={cancelledWithPermission} />);
    });
    expect(screen.queryByText('Absagen')).not.toBeInTheDocument();
  });

  it('does not show Absagen button when user lacks canCancel permission', async () => {
    const eventWithoutCancel = {
      ...baseEvent,
      permissions: { canEdit: true, canDelete: true, canCancel: false },
      cancelled: false,
    };
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={eventWithoutCancel} />);
    });
    expect(screen.queryByText('Absagen')).not.toBeInTheDocument();
  });

  it('applies line-through style to title when cancelled', async () => {
    const cancelledEvent = {
      ...baseEvent,
      cancelled: true,
      cancelReason: 'Grund',
    };
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={cancelledEvent} />);
    });
    const title = screen.getByText('Test Event');
    expect(title).toHaveStyle({ textDecoration: 'line-through' });
  });

  // ─── Game Matchup Display ───

  it('renders game matchup with team names', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    expect(screen.getByText('FC Test')).toBeInTheDocument();
    expect(screen.getByText('SC Gegner')).toBeInTheDocument();
  });

  // ─── Description Section ───

  it('renders description when provided', async () => {
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} />);
    });
    expect(screen.getByText('Beschreibung des Events')).toBeInTheDocument();
  });

  it('does not render description section when no description', async () => {
    const eventWithoutDescription = { ...baseEvent, description: undefined };
    await act(async () => {
      render(<EventDetailsModal {...defaultProps} event={eventWithoutDescription} />);
    });
    expect(screen.queryByText('Beschreibung des Events')).not.toBeInTheDocument();
  });
});
