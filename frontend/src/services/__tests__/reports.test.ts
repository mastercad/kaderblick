/**
 * Tests für reports.ts
 *
 * saveReport:
 *   – isTemplate wird korrekt in den Request-Body eingebettet (POST + PUT)
 *   – Bei Kopier-Antwort (PUT → neue ID) wird die neue ID übernommen
 *
 * fetchAvailableReports:  GET /api/report/available
 * fetchReportDefinitions: GET /api/report/definitions
 * fetchReportById:        GET /api/report/definition/{id}
 * deleteReport:           DELETE /api/report/definition/{id}
 */

import { saveReport, fetchAvailableReports, fetchReportDefinitions, fetchReportById, deleteReport } from '../reports';

const mockApiJson = jest.fn();
jest.mock('../../utils/api', () => ({
  apiJson: (...args: any[]) => mockApiJson(...args),
}));

const BASE_CONFIG = {
  diagramType: 'bar',
  xField: 'player',
  yField: 'goals',
  filters: {},
  groupBy: [],
  metrics: [],
  showLegend: true,
  showLabels: false,
} as const;

beforeEach(() => {
  jest.clearAllMocks();
});

// =============================================================================
//  POST — neuer Report
// =============================================================================

describe('saveReport – POST (neuer Report)', () => {
  it('sendet isTemplate: true wenn report.isTemplate === true', async () => {
    mockApiJson.mockResolvedValue({ id: 42 });

    await saveReport({ name: 'Template Report', description: '', config: BASE_CONFIG, isTemplate: true });

    expect(mockApiJson).toHaveBeenCalledTimes(1);
    const [, options] = mockApiJson.mock.calls[0];
    const body = JSON.parse(options.body);
    expect(body.isTemplate).toBe(true);
  });

  it('sendet isTemplate: false wenn report.isTemplate === false', async () => {
    mockApiJson.mockResolvedValue({ id: 43 });

    await saveReport({ name: 'Own Report', description: '', config: BASE_CONFIG, isTemplate: false });

    const body = JSON.parse(mockApiJson.mock.calls[0][1].body);
    expect(body.isTemplate).toBe(false);
  });

  it('sendet isTemplate: false wenn report.isTemplate undefined', async () => {
    mockApiJson.mockResolvedValue({ id: 44 });

    await saveReport({ name: 'No Flag', description: '', config: BASE_CONFIG });

    const body = JSON.parse(mockApiJson.mock.calls[0][1].body);
    expect(body.isTemplate).toBe(false);
  });

  it('benutzt POST /api/report/definition wenn keine id vorhanden', async () => {
    mockApiJson.mockResolvedValue({ id: 45 });

    await saveReport({ name: 'New', description: '', config: BASE_CONFIG });

    const [url, options] = mockApiJson.mock.calls[0];
    expect(url).toBe('/api/report/definition');
    expect(options.method).toBe('POST');
  });

  it('gibt den vom Server zurückgegebenen id-Wert zurück', async () => {
    mockApiJson.mockResolvedValue({ id: 99 });

    const result = await saveReport({ name: 'New', description: '', config: BASE_CONFIG });

    expect(result.id).toBe(99);
  });
});

// =============================================================================
//  PUT — bestehenden Report aktualisieren
// =============================================================================

describe('saveReport – PUT (bestehender Report)', () => {
  it('sendet isTemplate: true im Body wenn report.isTemplate === true', async () => {
    mockApiJson.mockResolvedValue({ status: 'success' });

    await saveReport({ id: 10, name: 'Template', description: '', config: BASE_CONFIG, isTemplate: true });

    const [url, options] = mockApiJson.mock.calls[0];
    expect(url).toBe('/api/report/definition/10');
    expect(options.method).toBe('PUT');
    const body = JSON.parse(options.body);
    expect(body.isTemplate).toBe(true);
  });

  it('sendet isTemplate: false im Body wenn report.isTemplate === false', async () => {
    mockApiJson.mockResolvedValue({ status: 'success' });

    await saveReport({ id: 11, name: 'Own', description: '', config: BASE_CONFIG, isTemplate: false });

    const body = JSON.parse(mockApiJson.mock.calls[0][1].body);
    expect(body.isTemplate).toBe(false);
  });

  it('übernimmt die neue ID aus der Server-Antwort (Kopier-Fall)', async () => {
    // Server gibt im Kopier-Fall eine neue ID zurück
    mockApiJson.mockResolvedValue({ status: 'success', id: 77 });

    const result = await saveReport({ id: 10, name: 'Template', description: '', config: BASE_CONFIG, isTemplate: true });

    expect(result.id).toBe(77);
  });

  it('behält die ursprüngliche ID wenn der Server keine neue ID sendet (in-place)', async () => {
    // Server gibt bei in-place-Edit kein id-Feld zurück
    mockApiJson.mockResolvedValue({ status: 'success' });

    const result = await saveReport({ id: 10, name: 'Template', description: '', config: BASE_CONFIG, isTemplate: true });

    expect(result.id).toBe(10);
  });
});

// =============================================================================
//  fetchAvailableReports
// =============================================================================

describe('fetchAvailableReports', () => {
  it('ruft GET /api/report/available auf', async () => {
    mockApiJson.mockResolvedValue([]);

    await fetchAvailableReports();

    expect(mockApiJson).toHaveBeenCalledTimes(1);
    expect(mockApiJson).toHaveBeenCalledWith('/api/report/available');
  });

  it('gibt das Array der verfügbaren Reports zurück', async () => {
    const mockData = [
      { id: 1, name: 'Template A', isTemplate: true },
      { id: 2, name: 'Eigener Report', isTemplate: false },
    ];
    mockApiJson.mockResolvedValue(mockData);

    const result = await fetchAvailableReports();

    expect(result).toEqual(mockData);
  });

  it('leitet Fehler weiter wenn der API-Aufruf fehlschlägt', async () => {
    mockApiJson.mockRejectedValue(new Error('Network error'));

    await expect(fetchAvailableReports()).rejects.toThrow('Network error');
  });
});

// =============================================================================
//  fetchReportDefinitions
// =============================================================================

describe('fetchReportDefinitions', () => {
  it('ruft GET /api/report/definitions auf', async () => {
    mockApiJson.mockResolvedValue({ templates: [], userReports: [] });

    await fetchReportDefinitions();

    expect(mockApiJson).toHaveBeenCalledWith('/api/report/definitions');
  });

  it('gibt { templates, userReports } zurück', async () => {
    const mockData = {
      templates: [{ id: 1, name: 'T1', isTemplate: true }],
      userReports: [{ id: 2, name: 'U1', isTemplate: false }],
    };
    mockApiJson.mockResolvedValue(mockData);

    const result = await fetchReportDefinitions();

    expect(result.templates).toHaveLength(1);
    expect(result.userReports).toHaveLength(1);
    expect(result.templates[0].id).toBe(1);
    expect(result.userReports[0].id).toBe(2);
  });
});

// =============================================================================
//  fetchReportById
// =============================================================================

describe('fetchReportById', () => {
  it('ruft GET /api/report/definition/{id} auf', async () => {
    mockApiJson.mockResolvedValue({ id: 42, name: 'Test', config: BASE_CONFIG, isTemplate: false });

    await fetchReportById(42);

    expect(mockApiJson).toHaveBeenCalledWith('/api/report/definition/42');
  });

  it('gibt den vollständigen Report zurück', async () => {
    const mockReport = { id: 42, name: 'Test', description: 'Beschreibung', config: BASE_CONFIG, isTemplate: true };
    mockApiJson.mockResolvedValue(mockReport);

    const result = await fetchReportById(42);

    expect(result).toEqual(mockReport);
  });

  it('leitet Fehler weiter wenn der API-Aufruf fehlschlägt (z. B. 404/403)', async () => {
    mockApiJson.mockRejectedValue(new Error('Not found'));

    await expect(fetchReportById(9999)).rejects.toThrow('Not found');
  });
});

// =============================================================================
//  deleteReport
// =============================================================================

describe('deleteReport', () => {
  it('ruft DELETE /api/report/definition/{id} auf', async () => {
    mockApiJson.mockResolvedValue(undefined);

    await deleteReport(42);

    expect(mockApiJson).toHaveBeenCalledTimes(1);
    expect(mockApiJson).toHaveBeenCalledWith('/api/report/definition/42', { method: 'DELETE' });
  });

  it('gibt nichts zurück (void)', async () => {
    mockApiJson.mockResolvedValue(undefined);

    const result = await deleteReport(42);

    expect(result).toBeUndefined();
  });

  it('leitet Fehler weiter wenn der API-Aufruf fehlschlägt (z. B. 403)', async () => {
    mockApiJson.mockRejectedValue(new Error('Forbidden'));

    await expect(deleteReport(42)).rejects.toThrow('Forbidden');
  });
});
