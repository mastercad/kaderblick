import { apiJson } from '../utils/api';

export interface WidgetData {
  id: string;
  type: string;
  title: string;
  width: number;
  position: number;
  config?: any;
  reportId?: number;
  name?: string;
  description?: string;
  enabled: boolean;
  default: boolean;
}

export async function fetchDashboardWidgets(): Promise<WidgetData[]> {
  const res = await apiJson<{ widgets: WidgetData[] }>('/');
  return res.widgets;
}
