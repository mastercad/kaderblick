import { apiJson } from '../utils/api';
import { User } from '../types/user';

export interface WidgetData {
  id: string;
  type: string;
  title: string;
  width: number;
  position: number;
  config?: any;
  reportId?: number;
}

export async function fetchDashboardWidgets(): Promise<WidgetData[]> {
  const res = await apiJson<{ widgets: WidgetData[] }>('/');
  return res.widgets;
}
