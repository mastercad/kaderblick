import React, { useEffect, useState } from 'react';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import AddIcon from '@mui/icons-material/Add';
import Grid from '@mui/material/Grid';
import { DashboardWidget } from '../components/DashboardWidget';
import { UpcomingEventsWidget } from '../widgets/UpcomingEventsWidget';
import { NewsWidget } from '../widgets/NewsWidget';
import { MessagesWidget } from '../widgets/MessagesWidget';
import { CalendarWidget } from '../widgets/CalendarWidget';
import { ReportWidget } from '../widgets/ReportWidget';
import { useAuth } from '../context/AuthContext';
import { fetchDashboardWidgets, WidgetData } from '../services/dashboardWidgets';
import { WidgetSettingsModal } from '../modals/WidgetSettingsModal';
import { AddWidgetModal } from '../modals/AddWidgetModal';
import { SelectReportModal } from '../modals/SelectReportModal';
import { updateWidgetWidth } from '../services/updateWidgetWidth';
import { createWidget } from '../services/createWidget';
import { fetchAvailableReports, ReportDefinition } from '../services/reports';
import { reorderWidgets } from '../services/reorderWidgets';
import { DashboardDndKitWrapper } from '../dnd/DashboardDndKitWrapper';
import { DynamicConfirmationModal } from '../modals/DynamicConfirmationModal';
import { deleteWidget } from '../services/deleteWidget';
import { refreshWidget } from '../services/refreshWidget';
import { WidgetRefreshProvider, useWidgetRefresh } from '../context/WidgetRefreshContext';

export default function Dashboard() {
  return (
    <WidgetRefreshProvider>
      <DashboardContent />
    </WidgetRefreshProvider>
  );
}

function DashboardContent() {
  const { refreshWidget: triggerRefresh, isRefreshing } = useWidgetRefresh();
  const { user } = useAuth();
  const [widgets, setWidgets] = useState<WidgetData[]>([]);
  const [loading, setLoading] = useState(true);
  const [addModalOpen, setAddModalOpen] = useState(false);
  const [reportModalOpen, setReportModalOpen] = useState(false);
  const [reports, setReports] = useState<ReportDefinition[]>([]);
  const [selectedReportIds, setSelectedReportIds] = useState<number[]>([]);
  const [reportsLoading, setReportsLoading] = useState(false);

  // Settings modal state
  const [settingsOpen, setSettingsOpen] = useState(false);
  const [settingsWidgetId, setSettingsWidgetId] = useState<string | null>(null);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteWidgetId, setDeleteWidgetId] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    fetchDashboardWidgets()
      .then(widgets => {
        console.log('Dashboard Widgets:', widgets);
        setWidgets(widgets);
      })
      .catch(() => setWidgets([]))
      .finally(() => setLoading(false));
  }, []);

  // Handler für Toolbar-Buttons (Refresh, Delete, Settings)
  const handleRefresh = async (id: string) => {
    try {
      // Trigger das Refresh im Context (zeigt Loading-State)
      triggerRefresh(id);
      // Der tatsächliche API-Call wird durch die Widget-Komponenten gehandelt
      // basierend auf dem getRefreshTrigger aus dem Context
    } catch (error) {
      console.error('Error refreshing widget:', error);
    }
  };
  const handleDelete = (id: string) => {
    setDeleteWidgetId(id);
    setDeleteModalOpen(true);
  };

  const handleDeleteConfirm = async () => {
    if (!deleteWidgetId) return;
    try {
      await deleteWidget(deleteWidgetId);
      setWidgets(widgets => widgets.filter(w => w.id !== deleteWidgetId));
    } catch (e) {
      // Optional: Fehlerbehandlung, z.B. Snackbar
      // alert('Fehler beim Löschen des Widgets');
    }
    setDeleteModalOpen(false);
    setDeleteWidgetId(null);
  };

  const handleDeleteCancel = () => {
    setDeleteModalOpen(false);
    setDeleteWidgetId(null);
  };
  const handleSettings = (id: string) => {
    setSettingsWidgetId(id);
    setSettingsOpen(true);
  };

  const handleSettingsClose = () => {
    setSettingsOpen(false);
    setSettingsWidgetId(null);
  };

  const handleSettingsSave = async (newWidth: number | string) => {
    if (!settingsWidgetId) return;
    const widget = widgets.find(w => w.id === settingsWidgetId);
    if (!widget) return;
    await updateWidgetWidth({
      id: widget.id,
      width: newWidth,
      position: widget.position,
      config: widget.config,
      enabled: true
    });
    setLoading(true);
    fetchDashboardWidgets()
      .then(setWidgets)
      .catch(() => setWidgets([]))
      .finally(() => setLoading(false));
    handleSettingsClose();
  };

  return (
  <Box sx={{ width: '100%', height: '100%', minWidth: 320, p: { xs: 1, md: 3 } }}>
      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 3 }}>
        <Typography variant="h4" component="h1">
          Dashboard{user?.firstName ? ` – ${user.firstName}` : ''}
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => setAddModalOpen(true)}
        >
          Widget hinzufügen
        </Button>
      {/* AddWidgetModal */}
      <AddWidgetModal
        open={addModalOpen}
        onClose={() => setAddModalOpen(false)}
        onAdd={async (widgetType) => {
          setAddModalOpen(false);
          await createWidget({ type: widgetType });
          setLoading(true);
          fetchDashboardWidgets()
            .then(setWidgets)
            .catch(() => setWidgets([]))
            .finally(() => setLoading(false));
        }}
        onReportWidgetFlow={async () => {
          setAddModalOpen(false);
          setReportsLoading(true);
          setReportModalOpen(true);
          setSelectedReportIds([]);
          try {
            const data = await fetchAvailableReports();
            setReports(data);
          } finally {
            setReportsLoading(false);
          }
        }}
      />

      {/* SelectReportModal */}
      <SelectReportModal
        open={reportModalOpen}
        onClose={() => setReportModalOpen(false)}
        onAdd={async () => {
          setReportModalOpen(false);
          for (const reportId of selectedReportIds) {
            await createWidget({ type: 'report', reportId });
          }
          setLoading(true);
          fetchDashboardWidgets()
            .then(setWidgets)
            .catch(() => setWidgets([]))
            .finally(() => setLoading(false));
        }}
        loading={reportsLoading}
      >
        <div style={{ maxHeight: 400, overflowY: 'auto' }}>
          {reports.map(report => (
            <div key={report.id} style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
              <input
                type="checkbox"
                id={`report${report.id}`}
                checked={selectedReportIds.includes(report.id)}
                onChange={e => {
                  setSelectedReportIds(ids =>
                    e.target.checked
                      ? [...ids, report.id]
                      : ids.filter(id => id !== report.id)
                  );
                }}
              />
              <label htmlFor={`report${report.id}`} style={{ flex: 1, cursor: 'pointer' }}>
                {report.name} {report.isTemplate ? <span style={{ color: '#1976d2', fontSize: 12, marginLeft: 8 }}>[Template]</span> : null}
              </label>
            </div>
          ))}
        </div>
      </SelectReportModal>
      </Box>

      <DashboardDndKitWrapper
        widgets={[...widgets].sort((a, b) => a.position - b.position)}
        onReorder={async (newOrder) => {
          // Update position field for all widgets
          const reordered = newOrder.map((w, idx) => ({ ...w, position: idx }));
          setWidgets(reordered);
          await reorderWidgets(reordered);
        }}
      >
        {(widget, idx, dragProps, isDragging, dragHandle) => (
          <DashboardWidget
            id={widget.id}
            type={widget.type}
            title={widget.type === 'upcoming_events' ? 'Anstehende Termine' :
              widget.type === 'news' ? 'Neuigkeiten' :
              widget.type === 'messages' ? 'Nachrichten' :
              widget.type === 'calendar' ? 'Kalender' :
              widget.type === 'report' ? widget.name ?? 'Report' :
              widget.type}
            loading={isRefreshing(widget.id)}
            onRefresh={() => handleRefresh(widget.id)}
            onDelete={() => handleDelete(widget.id)}
            onSettings={() => handleSettings(widget.id)}
            dragHandle={dragHandle}
            {...dragProps}
          >
            {widget.type === 'upcoming_events' && <UpcomingEventsWidget widgetId={widget.id} config={widget.config} />}
            {widget.type === 'news' && <NewsWidget widgetId={widget.id} config={widget.config} />}
            {widget.type === 'messages' && <MessagesWidget widgetId={widget.id} config={widget.config} />}
            {widget.type === 'calendar' && <CalendarWidget widgetId={widget.id} config={widget.config} />}
            {widget.type === 'report' && <ReportWidget config={widget.config} reportId={widget.reportId} widgetId={widget.id} />}
            {![
              'upcoming_events',
              'news',
              'messages',
              'calendar',
              'report'
            ].includes(widget.type) && (
              <Box sx={{ color: 'text.secondary', fontSize: 16, textAlign: 'center' }}>
                (Unbekannter Widget-Typ: <b>{widget.type}</b>)
              </Box>
            )}
          </DashboardWidget>
        )}
      </DashboardDndKitWrapper>

      <DynamicConfirmationModal
        open={deleteModalOpen}
        onClose={handleDeleteCancel}
        onConfirm={handleDeleteConfirm}
        title="Widget löschen?"
        message={`Soll das Widget${(() => {
          const w = widgets.find(w => w.id === deleteWidgetId);
          if (!w) return '';
          return ` "${w.type === 'upcoming_events' ? 'Anstehende Termine' :
            w.type === 'news' ? 'Neuigkeiten' :
            w.type === 'messages' ? 'Nachrichten' :
            w.type === 'calendar' ? 'Kalender' :
            w.type === 'report' ? 'Report' : w.type}"`;
        })()} wirklich entfernt werden?`}
        confirmText="Löschen"
        confirmColor="error"
      />

      <WidgetSettingsModal
        open={settingsOpen}
        currentWidth={(() => {
          if (!settingsWidgetId) return 6;
          const w = widgets.find(w => w.id === settingsWidgetId);
          return w?.width ?? 6;
        })()}
        onClose={handleSettingsClose}
        onSave={handleSettingsSave}
      />
    </Box>
  );
}
