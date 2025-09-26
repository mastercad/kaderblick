import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import '@testing-library/jest-dom';
import FeedbackModal from '../FeedbackModal';

jest.mock('@mui/material/Snackbar', () => ({
  __esModule: true,
  default: ({ open, message, anchorOrigin, autoHideDuration, onClose, ...props }: any) => (
    open ? (
      <div
        data-testid="Snackbar"
        data-anchororigin={JSON.stringify(anchorOrigin)}
        data-autohideduration={autoHideDuration}
        {...props}
      >
        {message}
      </div>
    ) : null
  )
}));
jest.mock('@mui/material/Alert', () => ({
  __esModule: true,
  default: ({ children, ...props }: any) => (
    <div data-testid="Alert" role="alert" {...props}>{children}</div>
  ),
}));

const filterProps = (props: any) => {
  const { children } = props;
  return { children };
};

jest.mock('@mui/material/Dialog', () => (props: any) => <div data-testid="Dialog">{filterProps(props).children}</div>);
jest.mock('@mui/material/DialogTitle', () => (props: any) => <div data-testid="DialogTitle">{filterProps(props).children}</div>);
jest.mock('@mui/material/DialogContent', () => (props: any) => <div data-testid="DialogContent">{filterProps(props).children}</div>);
jest.mock('@mui/material/DialogActions', () => (props: any) => <div data-testid="DialogActions">{filterProps(props).children}</div>);
jest.mock('@mui/material/Button', () => (props: any) => <button {...props}>{props.children}</button>);
jest.mock('@mui/material/TextField', () => (props: any) => <input {...props} data-testid={props.label} />);
jest.mock('@mui/material/Select', () => (props: any) => <select {...props}>{props.children}</select>);
jest.mock('@mui/material/MenuItem', () => (props: any) => <option {...props}>{props.children}</option>);
jest.mock('@mui/material/InputLabel', () => (props: any) => <label {...props}>{props.children}</label>);
jest.mock('@mui/material/FormControl', () => (props: any) => <div>{props.children}</div>);
jest.mock('@mui/material/FormControlLabel', () => (props: any) => (
  <label>
    {props.control}
    <span>{props.label}</span>
  </label>
));
jest.mock('@mui/material/Checkbox', () => (props: any) => <input type="checkbox" {...props} />);
jest.mock('@mui/material/Box', () => (props: any) => <div>{props.children}</div>);
jest.mock('@mui/material/Alert', () => (props: any) => (
  <div data-testid="Alert" role="alert">{props.children}</div>
));
jest.mock('@mui/material/CircularProgress', () => () => <span data-testid="CircularProgress" />);
jest.mock('@mui/material/styles', () => ({
  ...jest.requireActual('@mui/material/styles'),
  useTheme: () => ({ palette: { background: { paper: '#fff' } } })
}));

// html2canvas mocken
jest.mock('html2canvas', () => () => Promise.resolve({ toDataURL: () => 'data:image/png;base64,screenshot' }));

// apiRequest mocken
jest.mock('../../utils/api', () => ({
  apiRequest: jest.fn(() => Promise.resolve({ ok: true, json: async () => ({}) }))
}));
import { apiRequest } from '../../utils/api';

beforeAll(() => {
  jest.spyOn(console, 'log').mockImplementation(() => {});
  jest.spyOn(console, 'error').mockImplementation(() => {});
});
afterAll(() => {
  (console.log as jest.Mock).mockRestore();
  (console.error as jest.Mock).mockRestore();
});

describe('FeedbackModal', () => {
  const onClose = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renders modal and fields', async () => {
    await act(async () => {
      render(<FeedbackModal open={true} onClose={onClose} />);
    });
    expect(screen.getByTestId('Dialog')).toBeInTheDocument();
    expect(screen.getByTestId('DialogTitle')).toHaveTextContent('Feedback');
    expect(screen.getByTestId('Ihre Nachricht')).toBeInTheDocument();
    expect(screen.getByText('Screenshot anhängen')).toBeInTheDocument();
    expect(screen.getByText('Abbrechen')).toBeInTheDocument();
    expect(screen.getByText('Senden')).toBeInTheDocument();
  });

  it('calls onClose when Abbrechen button is clicked', async () => {
    await act(async () => {
      render(<FeedbackModal open={true} onClose={onClose} />);
    });
    fireEvent.click(screen.getByText('Abbrechen'));
    expect(onClose).toHaveBeenCalled();
  });

  it('disables Senden button when message is empty', async () => {
    await act(async () => {
      render(<FeedbackModal open={true} onClose={onClose} />);
    });
    expect(screen.getByText('Senden')).toBeDisabled();
  });

  it('enables Senden button when message is filled', async () => {
    await act(async () => {
      render(<FeedbackModal open={true} onClose={onClose} />);
    });
    fireEvent.change(screen.getByTestId('Ihre Nachricht'), { target: { value: 'Testnachricht' } });
    expect(screen.getByText('Senden')).not.toBeDisabled();
  });

  it('shows screenshot preview when checkbox is checked', async () => {
    await act(async () => {
      render(<FeedbackModal open={true} onClose={onClose} />);
    });
    fireEvent.click(screen.getByLabelText('Screenshot anhängen'));
    await waitFor(() => {
      expect(screen.getByAltText('Screenshot Vorschau')).toBeInTheDocument();
    });
  });

  it('submits feedback and shows success', async () => {
    jest.useFakeTimers();
    await act(async () => {
      render(<FeedbackModal open={true} onClose={onClose} />);
    });
    fireEvent.change(screen.getByTestId('Ihre Nachricht'), { target: { value: 'Testnachricht' } });
    fireEvent.click(screen.getByText('Senden'));
    
    await act(async () => {
      await Promise.resolve();
    });

    await waitFor(() => {
      const snackbars = screen.getAllByTestId('Snackbar');
      expect(snackbars.some(sb => sb.textContent?.includes('Vielen Dank für Ihr Feedback!'))).toBe(true);
    });
    expect(apiRequest).toHaveBeenCalledWith('/api/feedback/create', expect.objectContaining({ method: 'POST' }));
    // Timer vordrehen, damit Modal geschlossen wird
    act(() => {
      jest.runAllTimers();
    });
    jest.useRealTimers();
  });

  it('resets fields when modal is closed', async () => {
    const { rerender } = render(<FeedbackModal open={true} onClose={onClose} />);
    fireEvent.change(screen.getByTestId('Ihre Nachricht'), { target: { value: 'Testnachricht' } });
    rerender(<FeedbackModal open={false} onClose={onClose} />);
    rerender(<FeedbackModal open={true} onClose={onClose} />);
    expect(screen.getByTestId('Ihre Nachricht')).toHaveValue('');
  });
});
