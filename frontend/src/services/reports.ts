import { apiJson } from '../utils/api';
import type { ReportBuilderData } from '../types/reports';
import type { Report } from '../modals/ReportBuilder/types';

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
    const putResponse = await apiJson(`/api/report/definition/${report.id}`, {
      method: 'PUT',
      body: JSON.stringify({
        name: report.name,
        description: report.description,
        config: report.config,
        isTemplate: report.isTemplate ?? false,
      }),
    });
    return { ...report, id: putResponse?.id ?? report.id };
  } else {
    const response = await apiJson('/api/report/definition', {
      method: 'POST',
      body: JSON.stringify({
        name: report.name,
        description: report.description,
        config: report.config,
        isTemplate: report.isTemplate ?? false,
      }),
    });
    return { ...report, id: response.id };
  }
}

export async function fetchReportById(id: number): Promise<Report> {
  return apiJson(`/api/report/definition/${id}`);
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
  
  if (Array.isArray(report.config.groupBy)) {
    (report.config.groupBy as unknown as string[]).forEach((field, index) => {
      formData.append(`config[groupBy][${index}]`, field);
    });
  } else if (report.config.groupBy) {
    formData.append('config[groupBy]', report.config.groupBy);
  }

  Object.entries(report.config.filters ?? {}).forEach(([key, value]) => {
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
