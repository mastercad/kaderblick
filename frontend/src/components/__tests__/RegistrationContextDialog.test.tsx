import React from 'react';
import { render, screen, waitFor, act, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import RegistrationContextDialog from '../../modals/RegistrationContextDialog';

// ────── MUI Mock ──────
jest.mock('@mui/material', () => {
  const actual = jest.requireActual('@mui/material');
  return {
    ...actual,
    Dialog: (props: any) => props.open ? <div data-testid="Dialog">{props.children}</div> : null,
    DialogTitle: (props: any) => <h2 data-testid="DialogTitle">{props.children}</h2>,
    DialogContent: (props: any) => <div data-testid="DialogContent">{props.children}</div>,
    DialogActions: (props: any) => <div data-testid="DialogActions">{props.children}</div>,
    Button: (props: any) => (
      <button
        data-testid={props['data-testid'] || undefined}
        onClick={props.onClick}
        disabled={props.disabled}
      >
        {props.children}
      </button>
    ),
    Box: (props: any) => <div {...props}>{props.children}</div>,
    Typography: (props: any) => <span>{props.children}</span>,
    CircularProgress: () => <div data-testid="CircularProgress" />,
    Alert: (props: any) => <div data-testid="Alert" role="alert">{props.children}</div>,
    Stepper: (props: any) => <div data-testid="Stepper">{props.children}</div>,
    Step: (props: any) => <div data-testid="Step">{props.children}</div>,
    StepLabel: (props: any) => <span data-testid="StepLabel">{props.children}</span>,
    ToggleButtonGroup: (props: any) => (
      <div data-testid="ToggleButtonGroup" data-value={props.value}>
        {React.Children.map(props.children, (child: any) =>
          child ? React.cloneElement(child, { onChange: props.onChange }) : child
        )}
      </div>
    ),
    ToggleButton: (props: any) => (
      <button
        data-testid={`toggle-${props.value}`}
        onClick={(e) => props.onChange?.(e, props.value)}
        data-selected={props.value === props['data-parent-value'] ? 'true' : undefined}
      >
        {props.children}
      </button>
    ),
    Autocomplete: (props: any) => (
      <div data-testid="Autocomplete">
        {props.renderInput({ inputProps: {}, InputLabelProps: {}, inputValue: '' })}
        {props.options?.map((o: any) => (
          <div
            key={o.id}
            data-testid={`autocomplete-option-${o.id}`}
            onClick={() => props.onChange?.(null, o)}
          >
            {props.renderOption
              ? props.renderOption({}, o)
              : props.getOptionLabel(o)}
          </div>
        ))}
      </div>
    ),
    TextField: (props: any) => (
      <input
        data-testid={props['data-testid'] || 'TextField'}
        placeholder={props.placeholder ?? props.label}
        value={props.value ?? ''}
        onChange={(e) => props.onChange?.(e)}
        {...(props.inputProps || {})}
      />
    ),
    Stack: (props: any) => <div {...props}>{props.children}</div>,
    Paper: (props: any) => (
      <div
        data-testid="Paper"
        onClick={props.onClick}
        style={{ cursor: props.onClick ? 'pointer' : undefined }}
      >
        {props.children}
      </div>
    ),
    Chip: (props: any) => <span data-testid="Chip">{props.label}</span>,
  };
});

jest.mock('@mui/icons-material/Person', () => () => <span>PersonIcon</span>);
jest.mock('@mui/icons-material/SportsSoccer', () => () => <span>SportsSoccerIcon</span>);
jest.mock('@mui/icons-material/CheckCircleOutline', () => () => <span data-testid="CheckCircleOutlineIcon">✓</span>);

// ────── API Mock ──────
const mockApiJson = jest.fn();
jest.mock('../../utils/api', () => ({
  apiJson: (...args: any[]) => mockApiJson(...args),
}));

// ────── Fixtures ──────
const mockContextData = {
  players: [
    { id: 1, fullName: 'Max Mustermann', teams: ['U17', 'U15'] },
    { id: 2, fullName: 'Anna Schmidt' },
  ],
  coaches: [
    { id: 10, fullName: 'Hans Trainer', teams: ['1. Mannschaft'] },
  ],
  relationTypes: [
    { id: 1, identifier: 'parent', name: 'Elternteil', category: 'player' },
    { id: 2, identifier: 'self_player', name: 'Spieler selbst', category: 'player' },
    { id: 3, identifier: 'self_coach', name: 'Trainer selbst', category: 'coach' },
  ],
};

beforeAll(() => {
  jest.spyOn(console, 'error').mockImplementation(() => {});
});

afterAll(() => {
  (console.error as jest.Mock).mockRestore();
});

beforeEach(() => {
  mockApiJson.mockReset();
  mockApiJson.mockImplementation((url: string) => {
    if (url.includes('/api/registration-request/context')) {
      return Promise.resolve(mockContextData);
    }
    if (url.includes('/api/registration-request') && !url.includes('context') && !url.includes('mine')) {
      return Promise.resolve({ message: 'Antrag eingereicht' });
    }
    return Promise.resolve({});
  });
});

// ────── Tests ──────

describe('RegistrationContextDialog', () => {
  describe('Visibility', () => {
    it('renders nothing when open=false', () => {
      render(<RegistrationContextDialog open={false} onClose={jest.fn()} />);
      expect(screen.queryByTestId('Dialog')).not.toBeInTheDocument();
    });

    it('renders dialog when open=true', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });
      expect(screen.getByTestId('Dialog')).toBeInTheDocument();
    });

    it('shows title when open', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });
      expect(screen.getByTestId('DialogTitle')).toHaveTextContent('Meine Vereinszugehörigkeit angeben');
    });
  });

  describe('Loading / Context fetch', () => {
    it('shows loading spinner while fetching context', async () => {
      let resolveContext: (v: any) => void;
      mockApiJson.mockReturnValue(new Promise((res) => { resolveContext = res; }));

      render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);

      expect(screen.getByTestId('CircularProgress')).toBeInTheDocument();

      await act(async () => resolveContext!(mockContextData));
    });

    it('calls context API on open', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => {
        expect(mockApiJson).toHaveBeenCalledWith(
          expect.stringContaining('/api/registration-request/context')
        );
      });
    });

    it('shows stepper after context loaded', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => {
        expect(screen.getByTestId('Stepper')).toBeInTheDocument();
      });
    });

    it('shows 4 step labels', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => {
        const stepLabels = screen.getAllByTestId('StepLabel');
        expect(stepLabels).toHaveLength(4);
      });
    });

    it('shows error alert when context API fails', async () => {
      mockApiJson.mockRejectedValue(new Error('Network error'));

      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => {
        // Error is rendered inside the stepper block; check for the Alert message
        const alert = screen.getByTestId('Alert');
        expect(alert).toBeInTheDocument();
        expect(alert).toHaveTextContent('Daten konnten nicht geladen werden.');
      });
    });
  });

  describe('Überspringen button (skip)', () => {
    it('renders Überspringen button', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => screen.getByTestId('Stepper'));

      expect(screen.getByText('Überspringen')).toBeInTheDocument();
    });

    it('calls onClose when Überspringen is clicked', async () => {
      const onClose = jest.fn();

      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={onClose} />);
      });

      await waitFor(() => screen.getByTestId('Stepper'));

      fireEvent.click(screen.getByText('Überspringen'));
      expect(onClose).toHaveBeenCalledTimes(1);
    });
  });

  describe('Step 0 – Player/Coach selection', () => {
    it('shows ToggleButtonGroup with player and coach options', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => screen.getByTestId('Stepper'));

      expect(screen.getByTestId('toggle-player')).toBeInTheDocument();
      expect(screen.getByTestId('toggle-coach')).toBeInTheDocument();
    });

    it('Weiter button is disabled until a type is selected', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => screen.getByTestId('Stepper'));

      const weiterBtn = screen.getByText('Weiter');
      expect(weiterBtn).toBeDisabled();
    });

    it('Weiter button becomes enabled after selecting Player', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => screen.getByTestId('Stepper'));

      await act(async () => {
        fireEvent.click(screen.getByTestId('toggle-player'));
      });

      const weiterBtn = screen.getByText('Weiter');
      expect(weiterBtn).not.toBeDisabled();
    });

    it('Weiter button becomes enabled after selecting Coach', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });

      await waitFor(() => screen.getByTestId('Stepper'));

      await act(async () => {
        fireEvent.click(screen.getByTestId('toggle-coach'));
      });

      const weiterBtn = screen.getByText('Weiter');
      expect(weiterBtn).not.toBeDisabled();
    });
  });

  describe('Step 1 – Entity selection', () => {
    const advanceToStep1 = async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });
      await waitFor(() => screen.getByTestId('Stepper'));

      await act(async () => { fireEvent.click(screen.getByTestId('toggle-player')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
    };

    it('shows Autocomplete for entity selection', async () => {
      await advanceToStep1();
      expect(screen.getByTestId('Autocomplete')).toBeInTheDocument();
    });

    it('shows player options in Autocomplete', async () => {
      await advanceToStep1();
      expect(screen.getByText('Max Mustermann')).toBeInTheDocument();
      expect(screen.getByText('Anna Schmidt')).toBeInTheDocument();
    });

    it('Weiter is disabled until a player is selected', async () => {
      await advanceToStep1();
      expect(screen.getByText('Weiter')).toBeDisabled();
    });

    it('Weiter is enabled after selecting a player', async () => {
      await advanceToStep1();

      await act(async () => {
        fireEvent.click(screen.getByTestId('autocomplete-option-1'));
      });

      expect(screen.getByText('Weiter')).not.toBeDisabled();
    });

    it('shows Zurück button on step 1', async () => {
      await advanceToStep1();
      expect(screen.getByText('Zurück')).toBeInTheDocument();
    });
  });

  describe('Step 2 – Relation type selection', () => {
    const advanceToStep2 = async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });
      await waitFor(() => screen.getByTestId('Stepper'));

      await act(async () => { fireEvent.click(screen.getByTestId('toggle-player')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
      await act(async () => { fireEvent.click(screen.getByTestId('autocomplete-option-1')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
    };

    it('shows player relation types as clickable Papers', async () => {
      await advanceToStep2();
      // Should show Elternteil and Spieler selbst (player category)
      expect(screen.getByText('Elternteil')).toBeInTheDocument();
      expect(screen.getByText('Spieler selbst')).toBeInTheDocument();
    });

    it('does not show coach relation types on player step', async () => {
      await advanceToStep2();
      expect(screen.queryByText('Trainer selbst')).not.toBeInTheDocument();
    });

    it('Weiter is disabled until a relation type is selected', async () => {
      await advanceToStep2();
      expect(screen.getByText('Weiter')).toBeDisabled();
    });

    it('Weiter is enabled after selecting a relation type', async () => {
      await advanceToStep2();

      await act(async () => {
        fireEvent.click(screen.getByText('Elternteil'));
      });

      expect(screen.getByText('Weiter')).not.toBeDisabled();
    });
  });

  describe('Step 3 – Summary and submit', () => {
    const advanceToStep3 = async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });
      await waitFor(() => screen.getByTestId('Stepper'));

      await act(async () => { fireEvent.click(screen.getByTestId('toggle-player')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
      await act(async () => { fireEvent.click(screen.getByTestId('autocomplete-option-1')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
      await act(async () => { fireEvent.click(screen.getByText('Elternteil')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
    };

    it('shows "Antrag stellen" submit button on step 3', async () => {
      await advanceToStep3();
      expect(screen.getByText('Antrag stellen')).toBeInTheDocument();
    });

    it('shows summary with selected entity name', async () => {
      await advanceToStep3();
      expect(screen.getByText('Max Mustermann')).toBeInTheDocument();
    });

    it('shows summary with selected relation type', async () => {
      await advanceToStep3();
      expect(screen.getByText('Elternteil')).toBeInTheDocument();
    });

    it('submits request with correct payload', async () => {
      await advanceToStep3();

      await act(async () => {
        fireEvent.click(screen.getByText('Antrag stellen'));
      });

      await waitFor(() => {
        expect(mockApiJson).toHaveBeenCalledWith(
          expect.stringContaining('/api/registration-request'),
          expect.objectContaining({
            method: 'POST',
            body: expect.objectContaining({
              entityType: 'player',
              entityId: 1,
              relationTypeId: 1,
            }),
          })
        );
      });
    });

    it('shows success state after successful submission', async () => {
      await advanceToStep3();

      await act(async () => {
        fireEvent.click(screen.getByText('Antrag stellen'));
      });

      await waitFor(() => {
        expect(screen.getByText('Antrag eingereicht!')).toBeInTheDocument();
      });
    });

    it('shows success Schließen button after submission', async () => {
      await advanceToStep3();

      await act(async () => {
        fireEvent.click(screen.getByText('Antrag stellen'));
      });

      await waitFor(() => {
        expect(screen.getByText('Schließen')).toBeInTheDocument();
      });
    });

    it('shows error alert when submission fails', async () => {
      mockApiJson.mockImplementation((url: string) => {
        if (url.includes('context')) return Promise.resolve(mockContextData);
        return Promise.reject(new Error('Network error'));
      });

      await advanceToStep3();

      await act(async () => {
        fireEvent.click(screen.getByText('Antrag stellen'));
      });

      await waitFor(() => {
        // Step 3 always shows an info Alert + an error Alert = two Alerts
        const alerts = screen.getAllByTestId('Alert');
        const hasError = alerts.some(a =>
          a.textContent?.includes('Network error')
        );
        expect(hasError).toBe(true);
      });
    });

    it('shows error alert when API returns error field', async () => {
      mockApiJson.mockImplementation((url: string) => {
        if (url.includes('context')) return Promise.resolve(mockContextData);
        return Promise.resolve({ error: 'Du hast bereits einen offenen Antrag.' });
      });

      await advanceToStep3();

      await act(async () => {
        fireEvent.click(screen.getByText('Antrag stellen'));
      });

      await waitFor(() => {
        const alerts = screen.getAllByTestId('Alert');
        const errorAlert = alerts.find(a =>
          a.textContent?.includes('Du hast bereits einen offenen Antrag.')
        );
        expect(errorAlert).toBeDefined();
        expect(errorAlert).toHaveTextContent('Du hast bereits einen offenen Antrag.');
      });
    });

    it('Schließen calls onClose after successful submission', async () => {
      const onClose = jest.fn();

      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={onClose} />);
      });
      await waitFor(() => screen.getByTestId('Stepper'));

      await act(async () => { fireEvent.click(screen.getByTestId('toggle-player')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
      await act(async () => { fireEvent.click(screen.getByTestId('autocomplete-option-1')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
      await act(async () => { fireEvent.click(screen.getByText('Elternteil')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });

      await act(async () => {
        fireEvent.click(screen.getByText('Antrag stellen'));
      });

      await waitFor(() => screen.getByText('Schließen'));

      fireEvent.click(screen.getByText('Schließen'));
      expect(onClose).toHaveBeenCalledTimes(1);
    });
  });

  // ────── Team-Anzeige im Autocomplete ──────
  describe('Team-Anzeige im Autocomplete', () => {
    const navigateToPersonStep = async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });
      await waitFor(() => screen.getByTestId('Stepper'));
      await act(async () => { fireEvent.click(screen.getByTestId('toggle-player')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
      await waitFor(() => screen.getByTestId('Autocomplete'));
    };

    it('zeigt Team-Namen in den renderOption-Inhalten für Spieler mit Teams', async () => {
      await navigateToPersonStep();

      const option1 = screen.getByTestId('autocomplete-option-1');
      expect(option1).toHaveTextContent('Max Mustermann');
      expect(option1).toHaveTextContent('U17');
      expect(option1).toHaveTextContent('U15');
    });

    it('zeigt keine Team-Namen für Spieler ohne Teams', async () => {
      await navigateToPersonStep();

      const option2 = screen.getByTestId('autocomplete-option-2');
      expect(option2).toHaveTextContent('Anna Schmidt');
      // No team text — only the name present
      expect(option2.textContent).toBe('Anna Schmidt');
    });

    it('zeigt Team-Namen in renderOption für Coach-Typ', async () => {
      await act(async () => {
        render(<RegistrationContextDialog open={true} onClose={jest.fn()} />);
      });
      await waitFor(() => screen.getByTestId('Stepper'));
      await act(async () => { fireEvent.click(screen.getByTestId('toggle-coach')); });
      await act(async () => { fireEvent.click(screen.getByText('Weiter')); });
      await waitFor(() => screen.getByTestId('Autocomplete'));

      const coachOption = screen.getByTestId('autocomplete-option-10');
      expect(coachOption).toHaveTextContent('Hans Trainer');
      expect(coachOption).toHaveTextContent('1. Mannschaft');
    });
  });
});
