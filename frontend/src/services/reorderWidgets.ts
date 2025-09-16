import { apiJson } from '../utils/api';
import { WidgetData } from './dashboardWidgets';

export async function reorderWidgets(widgets: WidgetData[]): Promise<void> {
  await apiJson('/app/dashboard/widgets/positions', {
    method: 'PUT',
    body: {
      positions: widgets.map((w, idx) => ({ id: w.id, position: idx })),
    },
  });
}
