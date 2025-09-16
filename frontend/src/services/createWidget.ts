import { apiJson } from '../utils/api';
import { WidgetData } from './dashboardWidgets';

export async function createWidget({ type, reportId }: { type: string; reportId?: number }): Promise<WidgetData> {
  const res = await apiJson('/widget', {
    method: 'PUT',
    body: {
      type,
      ...(reportId ? { reportId } : {})
    }
  });
  return res.widget;
}
