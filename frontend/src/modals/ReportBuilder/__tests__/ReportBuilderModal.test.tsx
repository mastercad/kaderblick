/**
 * Tests für ReportBuilderModal (index.tsx)
 *
 * Geprüft wird die Template-Info-Banner-Logik:
 * – Normaler User bearbeitet ein Template → Banner wird angezeigt
 * – Admin bearbeitet ein Template → kein Banner (Admin kann in-place bearbeiten)
 * – Kein Template → kein Banner
 * – Neuer Report (report=null) → kein Banner
 *
 * Außerdem wird grundlegendes Rendering geprüft:
 * – Titel im Bearbeiten-Modus vs. Neu-Modus
 */

import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import { ReportBuilderModal } from '../index';

// ── Minimal state factory ─────────────────────────────────────────────────────

const BASE_CONFIG = {
  diagramType: 'bar',
  xField: 'player',
  yField: 'goals',
  filters: {},
  groupBy: [],
  metrics: [],
  showLegend: true,
  showLabels: false,
};

function makeState(overrides: Record<string, any> = {}) {
  return {
    currentReport: { name: 'Test', description: '', config: BASE_CONFIG, isTemplate: false, ...overrides.currentReport },
    isAdmin: false,
    isMobile: false,
    fullScreen: false,
    canSave: true,
    helpOpen: false,
    setHelpOpen: jest.fn(),
    handleSave: jest.fn(),
    activeStep: 0,
    setActiveStep: jest.fn(),
    expandedSection: 'basics' as const,
    setExpandedSection: jest.fn(),
    availableFields: [],
    builderData: null,
    previewData: null,
    isLoading: false,
    showAdvancedMeta: false,
    setShowAdvancedMeta: jest.fn(),
    previewDrawerOpen: false,
    setPreviewDrawerOpen: jest.fn(),
    isSuperAdmin: false,
    handleConfigChange: jest.fn(),
    handleFilterChange: jest.fn(),
    getFieldLabel: jest.fn((k: string) => k),
    hasPreview: false,
    activeFilterCount: 0,
    diag: 'bar',
    maApplicable: false,
    computePreviewWarnings: jest.fn(() => ({})),
    setCurrentReport: jest.fn(),
    ...overrides,
  };
}

// ── Mocks ─────────────────────────────────────────────────────────────────────

const mockUseReportBuilder = jest.fn();
jest.mock('../useReportBuilder', () => ({
  useReportBuilder: (...args: any[]) => mockUseReportBuilder(...args),
}));

// BaseModal lives at modals/BaseModal — from __tests__/ that is ../../BaseModal
jest.mock('../../BaseModal', () => ({
  __esModule: true,
  default: ({ open, title, children }: any) =>
    open ? (
      <div data-testid="Dialog">
        <div data-testid="DialogTitle">{title}</div>
        <div data-testid="DialogContent">{children}</div>
      </div>
    ) : null,
}));

jest.mock('../DesktopLayout', () => ({
  DesktopLayout: () => <div data-testid="DesktopLayout" />,
}));

jest.mock('../MobileWizard', () => ({
  MobileWizard: () => <div data-testid="MobileWizard" />,
}));

jest.mock('../HelpDialog', () => ({
  HelpDialog: () => null,
}));

jest.mock('@mui/material/Alert', () => ({
  __esModule: true,
  default: ({ children, severity }: any) => (
    <div data-testid="template-banner" data-severity={severity} role="alert">
      {children}
    </div>
  ),
}));

jest.mock('@mui/material/Button', () => (props: any) => (
  <button onClick={props.onClick} disabled={props.disabled}>{props.children}</button>
));

beforeAll(() => {
  jest.spyOn(console, 'error').mockImplementation(() => {});
});
afterAll(() => {
  (console.error as jest.Mock).mockRestore();
});

// ── Helper ────────────────────────────────────────────────────────────────────

const NOOP = jest.fn();
const BASE_REPORT = { id: 1, name: 'Template X', description: '', config: BASE_CONFIG, isTemplate: true };

function renderModal(stateOverrides: Record<string, any> = {}, reportProp: any = BASE_REPORT) {
  mockUseReportBuilder.mockReturnValue(makeState(stateOverrides));
  return render(
    <ReportBuilderModal
      open={true}
      onClose={NOOP}
      onSave={NOOP}
      report={reportProp}
    />,
  );
}

// ── Tests: Template-Banner ────────────────────────────────────────────────────

describe('ReportBuilderModal — Template-Banner', () => {
  beforeEach(() => jest.clearAllMocks());

  it('zeigt den Info-Banner wenn ein Template von einem normalen User bearbeitet wird', () => {
    renderModal({
      currentReport: { ...BASE_REPORT, isTemplate: true },
      isAdmin: false,
    });

    const banner = screen.getByTestId('template-banner');
    expect(banner).toBeInTheDocument();
    expect(banner).toHaveAttribute('data-severity', 'info');
    expect(banner).toHaveTextContent('Vorlage');
    expect(banner).toHaveTextContent('persönliche Kopie');
  });

  it('zeigt keinen Banner wenn der User Admin ist (kann in-place bearbeiten)', () => {
    renderModal({
      currentReport: { ...BASE_REPORT, isTemplate: true },
      isAdmin: true,
    });

    expect(screen.queryByTestId('template-banner')).not.toBeInTheDocument();
  });

  it('zeigt keinen Banner wenn der Report kein Template ist', () => {
    renderModal({
      currentReport: { ...BASE_REPORT, isTemplate: false },
      isAdmin: false,
    });

    expect(screen.queryByTestId('template-banner')).not.toBeInTheDocument();
  });

  it('zeigt keinen Banner beim Erstellen eines neuen Reports (report=null)', () => {
    renderModal(
      { currentReport: { name: '', description: '', config: BASE_CONFIG, isTemplate: false }, isAdmin: false },
      null,
    );

    expect(screen.queryByTestId('template-banner')).not.toBeInTheDocument();
  });

  it('zeigt keinen Banner wenn isTemplate undefined ist', () => {
    renderModal({
      currentReport: { name: 'X', description: '', config: BASE_CONFIG },
      isAdmin: false,
    });

    expect(screen.queryByTestId('template-banner')).not.toBeInTheDocument();
  });
});

// ── Tests: Modal-Titel ────────────────────────────────────────────────────────

describe('ReportBuilderModal — Titel', () => {
  beforeEach(() => jest.clearAllMocks());

  it('zeigt "Report bearbeiten" wenn ein vorhandener Report übergeben wird', () => {
    renderModal({}, BASE_REPORT);
    expect(screen.getByTestId('DialogTitle')).toHaveTextContent('Report bearbeiten');
  });

  it('zeigt "Neuer Report" wenn kein Report übergeben wird', () => {
    renderModal({}, null);
    expect(screen.getByTestId('DialogTitle')).toHaveTextContent('Neuer Report');
  });
});
