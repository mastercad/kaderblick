/**
 * Tests für reports.ts — saveReport
 *
 * Geprüft wird, dass isTemplate korrekt in den Request-Body eingebettet
 * wird, egal ob ein neuer Report erstellt (POST) oder ein bestehender
 * aktualisiert (PUT) wird. Außerdem wird sichergestellt, dass bei einer
 * Kopier-Antwort des Servers (PUT → neue ID) die zurückgegebene ID
 * des Reports korrekt übernommen wird.
 */

import { saveReport } from '../reports';

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
