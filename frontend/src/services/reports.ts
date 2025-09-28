import { apiJson } from '../utils/api';
import type { Report, ReportBuilderData } from '../types/reports';

export interface ReportDefinition {
  id: number;
  name: string;
  isTemplate: boolean;
  description?: string;
}

export async function fetchAvailableReports(): Promise<ReportDefinition[]> {
  return apiJson('/api/report/available');
}

export async function fetchReportDefinitions(): Promise<{ templates: any[], userReports: any[] }> {
  return apiJson('/api/report/definitions');
}

export async function saveReport(report: Report): Promise<Report> {
  if (report.id) {
    await apiJson(`/api/report/definition/${report.id}`, {
      method: 'PUT',
      body: JSON.stringify({
        name: report.name,
        description: report.description,
        config: report.config,
      }),
    });
    return report;
  } else {
    const response = await apiJson('/api/report/definition', {
      method: 'POST',
      body: JSON.stringify({
        name: report.name,
        description: report.description,
        config: report.config,
      }),
    });
    return { ...report, id: response.id };
  }
}

export async function deleteReport(id: number): Promise<void> {
  await apiJson(`/api/report/definition/${id}`, {
    method: 'DELETE',
  });
}

export async function fetchReportData(reportId: number): Promise<any> {
  return apiJson(`/api/report/widget/${reportId}/data`);
}

export async function fetchReportPreview(config: any): Promise<any> {
  return apiJson('/api/report/preview', {
    method: 'POST',
    body: config,
  });
}

export async function fetchReportBuilderData(): Promise<ReportBuilderData> {
  return apiJson('/api/report/builder-data');
}

export async function previewReport(report: Report): Promise<{ preview?: string; error?: string }> {
  // Verwendet den bestehenden /reports/preview Endpunkt (für Web-Interface)
  const formData = new FormData();
  formData.append('name', report.name);
  formData.append('description', report.description || '');
  formData.append('config[diagramType]', report.config.diagramType);
  formData.append('config[xField]', report.config.xField);
  formData.append('config[yField]', report.config.yField);
  
  report.config.groupBy.forEach((field, index) => {
    formData.append(`config[groupBy][${index}]`, field);
  });
  
  Object.entries(report.config.filters).forEach(([key, value]) => {
    if (value) {
      formData.append(`config[filters][${key}]`, value.toString());
    }
  });

  try {
    const response = await apiJson('/reports/preview', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    });
    
    const data = await response.json();
    return data;
  } catch (error) {
    return { error: 'Fehler beim Laden der Vorschau' };
  }
}
