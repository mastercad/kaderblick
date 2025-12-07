import React, { useState, useEffect } from 'react';
import {
  Button,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Typography,
  Box,
  Paper,
  Chip,
  Divider,
  Slider,
  Checkbox,
  FormControlLabel,
  OutlinedInput,
  IconButton,
  Tooltip,
  Popover,
  Alert,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  useTheme,
  useMediaQuery,
} from '@mui/material';
import Autocomplete from '@mui/material/Autocomplete';
import InfoOutlinedIcon from '@mui/icons-material/InfoOutlined';
import { DragDropContext, Droppable, Draggable, DropResult } from '@hello-pangea/dnd';
import { ReportWidget } from '../widgets/ReportWidget';
import { WidgetRefreshProvider } from '../context/WidgetRefreshContext';
import { apiJson } from '../utils/api';
import { useAuth } from '../context/AuthContext';
import BaseModal from './BaseModal';

interface FieldOption {
  key: string;
  label: string;
  source?: string;
  dataType?: string;
  isMetricCandidate?: boolean;
}

export interface Report {
  id?: number;
  name: string;
  description: string;
  isTemplate?: boolean;
  config: {
    diagramType: string;
    xField: string;
    yField: string;
    groupBy?: string;
    metrics?: string[];
      movingAverage?: { enabled: boolean; window: number; method?: 'mean' | 'median' };
      heatmapStyle?: string;
      heatmapSpatial?: boolean;
      use_db_aggregates?: boolean;
    filters?: {
      team?: string;
      player?: string;
      eventType?: string;
        surfaceType?: string;
        precipitation?: string; // 'yes' | 'no'
      dateFrom?: string;
      dateTo?: string;
    };
    showLegend: boolean;
    showLabels: boolean;
  };
}

interface ReportBuilderModalProps {
  open: boolean;
  onClose: () => void;
  onSave: (report: Report) => Promise<void>;
  report?: Report | null;
}

interface BuilderData {
  fields: FieldOption[];
  advancedFields?: FieldOption[];
  presets?: Array<any>;
  teams: Array<{ id: number; name: string }>;
  players: Array<{ id: number; fullName: string; firstName: string; lastName: string }>;
  eventTypes: Array<{ id: number; name: string }>;
  metrics?: Array<{ key: string; label: string }>;
  surfaceTypes?: Array<{ id: number; name: string }>;
  availableDates: string[];
  minDate: string;
  maxDate: string;
  // no surfaceTypes by default
}

export const ReportBuilderModal: React.FC<ReportBuilderModalProps> = ({
  open,
  onClose,
  onSave,
  report,
}) => {
  const { isSuperAdmin } = useAuth();
  const theme = useTheme();
  const fullScreen = useMediaQuery(theme.breakpoints.down('sm'));
  const [currentReport, setCurrentReport] = useState<Report>({
    name: '',
    description: '',
    config: {
      diagramType: 'bar',
      xField: '',
      yField: '',
      use_db_aggregates: false,
      groupBy: undefined,
      metrics: [],
      movingAverage: { enabled: false, window: 3, method: 'mean' },
      filters: {},
      showLegend: true,
      showLabels: false,
    },
  });

  const [availableFields, setAvailableFields] = useState<FieldOption[]>([]);
  const [previewData, setPreviewData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [builderData, setBuilderData] = useState<BuilderData | null>(null);
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [prevGroupBy, setPrevGroupBy] = useState<string | undefined>(undefined);
  const [helpOpen, setHelpOpen] = useState(false);
  const [heatmapAnchorEl, setHeatmapAnchorEl] = useState<HTMLElement | null>(null);
  const [groupAnchorEl, setGroupAnchorEl] = useState<HTMLElement | null>(null);
  const [precipAnchorEl, setPrecipAnchorEl] = useState<HTMLElement | null>(null);
  const [showAdvancedMeta, setShowAdvancedMeta] = useState(false);

  // Initialize currentReport when report prop changes
  useEffect(() => {
    if (report) {
      setCurrentReport(report);
    } else {
      setCurrentReport({
        name: '',
        description: '',
        config: {
          diagramType: 'bar',
          xField: '',
          yField: '',
          filters: {},
          showLegend: true,
          showLabels: false,
        },
      });
    }
  }, [report, open]);

  // Load available fields
  useEffect(() => {
    if (open) {
      loadBuilderData();
    }
  }, [open]);

  // Update preview when config changes
  useEffect(() => {
    if (open && currentReport.config.xField && currentReport.config.yField) {
      loadPreview();
    }
  }, [currentReport.config, open]);

  const loadBuilderData = async () => {
    try {
      const data = await apiJson('/api/report/builder-data');
      setBuilderData(data);
      setAvailableFields(data.fields || []);
    } catch (error) {
      console.error('Error loading builder data:', error);
    }
  };

  // update visible fields when builderData or showAdvanced toggles
  useEffect(() => {
    if (!builderData) return;
    if (showAdvanced) {
      setAvailableFields([...(builderData.fields || []), ...(builderData['advancedFields'] || [])]);
    } else {
      setAvailableFields(builderData.fields || []);
    }
  }, [builderData, showAdvanced]);

  const loadPreview = async () => {
    if (!currentReport.config.xField || !currentReport.config.yField) {
      setPreviewData(null);
      return;
    }

    setIsLoading(true);
    try {
      const data = await apiJson('/api/report/preview', {
        method: 'POST',
        body: {
          config: currentReport.config,
        },
      });
      // Attach current report config so widget can apply client-side transforms (e.g. moving average)
      setPreviewData({ ...data, config: currentReport.config });
    } catch (error) {
      console.error('Error loading preview:', error);
      setPreviewData(null);
    } finally {
      setIsLoading(false);
    }
  };

  const handleConfigChange = (key: keyof Report['config'], value: any) => {
    setCurrentReport(prev => ({
      ...prev,
      config: {
        ...prev.config,
        [key]: value,
      },
    }));
  };

  const handleFilterChange = (filterKey: string, value: any) => {
    setCurrentReport(prev => {
      const newFilters = { ...(prev.config.filters || {}) } as any;
      if (value === null) {
        delete newFilters[filterKey];
      } else {
        newFilters[filterKey] = value;
      }
      return {
        ...prev,
        config: {
          ...prev.config,
          filters: newFilters,
        },
      };
    });
  };

  const handleSave = async () => {
    await onSave(currentReport);
    onClose();
  };

  const getFieldLabel = (fieldKey: string) => {
    const field = availableFields.find(f => f.key === fieldKey);
    return field ? field.label : fieldKey;
  };

  const handleDragEnd = (result: DropResult) => {
    const { source, destination } = result;

    // Check if drop was outside droppable area
    if (!destination) {
      return;
    }

    const draggedId = result.draggableId;
    
    // Extract field key from draggable ID
    let fieldKey = '';
    if (draggedId.startsWith('available-')) {
      fieldKey = draggedId.replace('available-', '');
    } else if (draggedId.startsWith('x-')) {
      fieldKey = draggedId.replace('x-', '');
    } else if (draggedId.startsWith('y-')) {
      fieldKey = draggedId.replace('y-', '');
    }

    const newConfig = { ...currentReport.config };

    // Handle dropping to X-axis
    if (destination.droppableId === 'x-axis') {
      // If X-axis already has a field and we're moving from Y-axis, swap them
      if (newConfig.xField && source.droppableId === 'y-axis') {
        const tempField = newConfig.xField;
        newConfig.xField = fieldKey;
        newConfig.yField = tempField;
      } else {
        newConfig.xField = fieldKey;
        // If moving from Y-axis, clear Y-axis
        if (source.droppableId === 'y-axis') {
          newConfig.yField = '';
        }
      }
    }
    // Handle dropping to Y-axis
    else if (destination.droppableId === 'y-axis') {
      // If Y-axis already has a field and we're moving from X-axis, swap them
      if (newConfig.yField && source.droppableId === 'x-axis') {
        const tempField = newConfig.yField;
        newConfig.yField = fieldKey;
        newConfig.xField = tempField;
      } else {
        newConfig.yField = fieldKey;
        // If moving from X-axis, clear X-axis
        if (source.droppableId === 'x-axis') {
          newConfig.xField = '';
        }
      }
    }

    setCurrentReport(prev => ({
      ...prev,
      config: newConfig,
    }));
  };

  // Preview validation/warnings (computed from previewData + current config)
  const computePreviewWarnings = () => {
    const warnings: { movingAverageWindowTooLarge?: boolean; boxplotFormatInvalid?: boolean; scatterNonNumeric?: boolean } = {};
    try {
      const labelsLen = (previewData && Array.isArray(previewData.labels)) ? previewData.labels.length : 0;
      const maCfg = currentReport.config.movingAverage;
      const diag = (previewData?.diagramType || currentReport.config.diagramType || '').toLowerCase();
      const maApplicable = ['line', 'area', 'stackedarea', 'bar', 'boxplot'].includes(diag);
      if (maApplicable && maCfg && maCfg.enabled && Number.isInteger(maCfg.window)) {
        if (labelsLen > 0 && maCfg.window > labelsLen) {
          warnings.movingAverageWindowTooLarge = true;
        }
      }

      const dsets = previewData?.datasets || [];
      if (diag === 'boxplot') {
        // Expect each dataset.data to be an array where each entry is an array of numbers
        let invalid = false;
        if (!Array.isArray(dsets) || dsets.length === 0) invalid = true;
        for (const ds of dsets) {
          if (!Array.isArray(ds.data)) { invalid = true; break; }
          for (const entry of ds.data) {
            if (!Array.isArray(entry)) { invalid = true; break; }
          }
          if (invalid) break;
        }
        if (invalid) warnings.boxplotFormatInvalid = true;
      }

      if (diag === 'scatter') {
        let nonNumeric = false;
        for (const ds of dsets) {
          if (!Array.isArray(ds.data)) continue;
          for (const v of ds.data) {
            const n = Number(v);
            if (isNaN(n)) { nonNumeric = true; break; }
          }
          if (nonNumeric) break;
        }
        if (nonNumeric) warnings.scatterNonNumeric = true;
      }
    } catch (e) {
      // ignore
    }
    return warnings;
  };

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      maxWidth="lg"
      fullScreen={fullScreen}
      title={report ? 'Report bearbeiten' : 'Neuer Report'}
      actions={
        <>
          <Button onClick={onClose} variant="outlined" color="secondary" size={fullScreen ? 'large' : 'medium'}>
            Abbrechen
          </Button>
          <Button
            onClick={handleSave}
            variant="contained"
            color="primary"
            disabled={!currentReport.name || !currentReport.config.xField || !currentReport.config.yField}
            size={fullScreen ? 'large' : 'medium'}
          >
            Speichern
          </Button>
        </>
      }
    >
      <DragDropContext onDragEnd={handleDragEnd}>
        <Box display="flex" flexDirection={{ xs: 'column', lg: 'row' }} gap={3} height="70vh">
          {/* Configuration Panel */}
          <Box flex={1} overflow="auto" pr={{ lg: 2 }}>
            {/* Basic Information */}
            <TextField
              fullWidth
              label="Report Name"
                value={currentReport.name}
                onChange={(e) => setCurrentReport(prev => ({ ...prev, name: e.target.value }))}
                margin="normal"
              />
              
              <TextField
                fullWidth
                label="Beschreibung"
                value={currentReport.description}
                onChange={(e) => setCurrentReport(prev => ({ ...prev, description: e.target.value }))}
                margin="normal"
                multiline
                rows={2}
              />

              {/* Presets */}
              {builderData?.presets && (
                <Paper variant="outlined" sx={{ p: 1, my: 1 }}>
                  <Typography variant="subtitle2" sx={{ mb: 1 }}>Vorlagen</Typography>
                  <Box display="flex" gap={1} flexWrap="wrap">
                    {builderData.presets.map((p: any) => (
                      <Button
                        key={p.key}
                        size="small"
                        variant="outlined"
                        onClick={() => {
                          setCurrentReport(prev => ({ ...prev, config: { ...prev.config, ...p.config } }));
                        }}
                      >
                        {p.label}
                      </Button>
                    ))}
                  </Box>
                </Paper>
              )}

              {/* Template Checkbox für SuperAdmin */}
              {isSuperAdmin && (
                <FormControlLabel
                  control={
                    <Checkbox
                      checked={currentReport.isTemplate || false}
                      onChange={(e) => setCurrentReport(prev => ({ 
                        ...prev, 
                        isTemplate: e.target.checked 
                      }))}
                    />
                  }
                  label="Als Template verfügbar machen"
                  sx={{ mt: 1, mb: 1 }}
                />
              )}


              {/* Field Assignment */}
              <FormControlLabel
                control={<Checkbox checked={showAdvanced} onChange={(e) => setShowAdvanced(e.target.checked)} />}
                label="Erweiterte Felder anzeigen"
                sx={{ mb: 1 }}
              />
              <Typography variant="subtitle1" gutterBottom>
                Feldkonfiguration
              </Typography>

              {/* X/Y-Achsen */}
              <Box display="flex" gap={2} mb={2}>
                {/* X-Axis */}
                <Box flex={1}>
                  <Typography variant="subtitle2" fontWeight="bold">
                    X-Achse:
                  </Typography>
                  <Droppable droppableId="x-axis">
                    {(provided, snapshot) => (
                      <Paper
                        ref={provided.innerRef}
                        {...provided.droppableProps}
                        sx={{
                          p: 1,
                          minHeight: 40,
                          bgcolor: snapshot.isDraggingOver ? 'action.hover' : 'background.paper',
                          border: 1,
                          borderColor: 'divider',
                          touchAction: 'none',
                          WebkitUserSelect: 'none',
                          userSelect: 'none',
                        }}
                      >
                        {currentReport.config.xField ? (
                          <Draggable draggableId={`x-${currentReport.config.xField}`} index={0}>
                            {(provided) => (
                              <Chip
                                ref={provided.innerRef}
                                {...provided.draggableProps}
                                {...provided.dragHandleProps}
                                label={getFieldLabel(currentReport.config.xField)}
                                onDelete={() => handleConfigChange('xField', '')}
                                variant="outlined"
                                size="small"
                                sx={{ cursor: 'grab', WebkitUserSelect: 'none', userSelect: 'none', touchAction: 'none' }}
                                onDragStart={(e) => e.preventDefault()}
                                onTouchStart={(e) => e.preventDefault()}
                              />
                            )}
                          </Draggable>
                        ) : (
                          <Typography variant="body2" color="text.secondary">
                            Feld hier ablegen
                          </Typography>
                        )}
                        {provided.placeholder}
                      </Paper>
                    )}
                  </Droppable>
                </Box>

                {/* Y-Axis */}
                <Box flex={1}>
                  <Typography variant="subtitle2" fontWeight="bold">
                    Y-Achse:
                  </Typography>
                  <Droppable droppableId="y-axis">
                    {(provided, snapshot) => (
                      <Paper
                        ref={provided.innerRef}
                        {...provided.droppableProps}
                        sx={{
                          p: 1,
                          minHeight: 40,
                          bgcolor: snapshot.isDraggingOver ? 'action.hover' : 'background.paper',
                          border: 1,
                          borderColor: 'divider',
                          touchAction: 'none',
                          WebkitUserSelect: 'none',
                          userSelect: 'none',
                        }}
                      >
                        {currentReport.config.yField ? (
                          <Draggable draggableId={`y-${currentReport.config.yField}`} index={0}>
                            {(provided) => (
                              <Chip
                                ref={provided.innerRef}
                                {...provided.draggableProps}
                                {...provided.dragHandleProps}
                                label={getFieldLabel(currentReport.config.yField)}
                                onDelete={() => handleConfigChange('yField', '')}
                                variant="outlined"
                                size="small"
                                sx={{ cursor: 'grab', WebkitUserSelect: 'none', userSelect: 'none', touchAction: 'none' }}
                                onDragStart={(e) => e.preventDefault()}
                                onTouchStart={(e) => e.preventDefault()}
                              />
                            )}
                          </Draggable>
                        ) : (
                          <Typography variant="body2" color="text.secondary">
                            Feld hier ablegen
                          </Typography>
                        )}
                        {provided.placeholder}
                      </Paper>
                    )}
                  </Droppable>
                </Box>
              </Box>

              {/* Available Fields */}
              <Box mb={2}>
                <Typography variant="subtitle2" fontWeight="bold">
                  Verfügbare Felder:
                </Typography>
                <Droppable droppableId="available-fields">
                  {(provided) => (
                    <Paper
                      ref={provided.innerRef}
                      {...provided.droppableProps}
                      sx={{
                        p: 1,
                        minHeight: 80,
                        border: 1,
                        borderColor: 'divider',
                        display: 'flex',
                        flexWrap: 'wrap',
                        gap: 1,
                        touchAction: 'none',
                        WebkitUserSelect: 'none',
                        userSelect: 'none',
                      }}
                    >
                      {availableFields
                        .filter(field => field.key !== currentReport.config.xField && field.key !== currentReport.config.yField)
                        .map((field, index) => (
                        <Draggable
                          key={field.key}
                          draggableId={`available-${field.key}`}
                          index={index}
                        >
                          {(dragProvided) => (
                            <Chip
                              ref={dragProvided.innerRef}
                              {...dragProvided.draggableProps}
                              {...dragProvided.dragHandleProps}
                              label={
                                <Box display="flex" alignItems="center" gap={1}>
                                  <span>{field.label}</span>
                                  {field.source && (
                                    <Chip label={field.source} size="small" sx={{ fontSize: '0.65rem', ml: 0.5 }} />
                                  )}
                                </Box>
                              }
                              variant="outlined"
                              size="small"
                              sx={{ cursor: 'grab', WebkitUserSelect: 'none', userSelect: 'none', touchAction: 'none' }}
                              onDragStart={(e) => e.preventDefault()}
                              onTouchStart={(e) => e.preventDefault()}
                            />
                          )}
                        </Draggable>
                      ))}
                      {provided.placeholder}
                    </Paper>
                  )}
                </Droppable>
              </Box>

              <Divider sx={{ my: 2 }} />

              {/* Chart Configuration */}
              <FormControl fullWidth margin="normal">
                <InputLabel>Chart Typ</InputLabel>
                <Select
                  value={currentReport.config.diagramType}
                  onChange={(e) => handleConfigChange('diagramType', e.target.value)}
                  label="Chart Typ"
                >
                  <MenuItem value="bar">Balkendiagramm</MenuItem>
                  <MenuItem value="line">Liniendiagramm</MenuItem>
                  <MenuItem value="area">Flächendiagramm</MenuItem>
                  <MenuItem value="stackedarea">Gestapeltes Flächendiagramm</MenuItem>
                  <MenuItem value="scatter">Scatter (Punkte)</MenuItem>
                  <MenuItem value="pitchheatmap">Pitch Heatmap</MenuItem>
                  <MenuItem value="boxplot">Boxplot</MenuItem>
                  <MenuItem value="pie">Kreisdiagramm</MenuItem>
                  <MenuItem value="doughnut">Donut-Diagramm</MenuItem>
                  <MenuItem value="radar">Radar</MenuItem>
                  <MenuItem value="radaroverlay">Radar (Overlay)</MenuItem>
                </Select>
              </FormControl>

              {/* Advanced chart options */}
              <Box sx={{ mt: 1, display: 'flex', gap: 2, alignItems: 'center' }}>
                {/* Always show the MA checkbox, but disable it for diagram types where it doesn't make sense */}
                <Box display="flex" alignItems="center" gap={1}>
                  {(() => {
                    const diag = ((currentReport.config.diagramType || '') as string).toLowerCase();
                    const maApplicable = ['line', 'area', 'stackedarea', 'bar', 'boxplot'].includes(diag);
                    console.log(currentReport.config.diagramType);
                    console.log(maApplicable);
                    return (
                      <FormControlLabel
                        control={
                          <Checkbox
                            checked={currentReport.config.movingAverage?.enabled || false}
                            onChange={(e) => setCurrentReport(prev => ({ ...prev, config: { ...prev.config, movingAverage: { ...(prev.config.movingAverage || { enabled: false, window: 3, method: 'mean' }), enabled: e.target.checked } } }))}
                            disabled={!maApplicable}
                          />
                        }
                        label="Gleitender Durchschnitt"
                      />
                    );
                  })()}
                  <Tooltip enterDelay={300} title="Glättet die Serie über ein wählbares Fenster. Tipp: Wenn das Fenster größer ist als die Anzahl Datenpunkte, verkleinern oder Zeitraum erweitern.">
                    <IconButton size="small" aria-label="Info Gleitender Durchschnitt" sx={{ color: 'text.secondary' }}>
                      <InfoOutlinedIcon fontSize="small" />
                    </IconButton>
                  </Tooltip>
                  {!(['line', 'area', 'stackedarea', 'bar', 'boxplot'].includes(((currentReport.config.diagramType || '') as string).toLowerCase())) && (
                    <Tooltip enterDelay={200} title="Nicht für diesen Diagrammtyp verfügbar">
                      <InfoOutlinedIcon fontSize="small" sx={{ color: 'text.disabled', ml: 0.5 }} />
                    </Tooltip>
                  )}
                </Box>

                {currentReport.config.movingAverage?.enabled && ['line', 'area', 'stackedarea', 'bar', 'boxplot'].includes((currentReport.config.diagramType || '').toLowerCase()) && (
                  <Box sx={{ width: 220, display: 'flex', alignItems: 'center', gap: 1 }}>
                    <Typography variant="body2">Fenster</Typography>
                    <Slider
                      value={currentReport.config.movingAverage?.window || 3}
                      min={1}
                      max={21}
                      step={1}
                      onChange={(_, val) => setCurrentReport(prev => ({ ...prev, config: { ...prev.config, movingAverage: { ...(prev.config.movingAverage || { enabled: true, window: 3 }), window: val as number } } }))}
                      valueLabelDisplay="auto"
                    />
                  </Box>
                )}
                {currentReport.config.movingAverage?.enabled && ['line', 'area', 'stackedarea', 'bar', 'boxplot'].includes((currentReport.config.diagramType || '').toLowerCase()) && (
                  <FormControl size="small" sx={{ minWidth: 140 }}>
                    <InputLabel id="ma-method-label">Zentralwert</InputLabel>
                    <Select
                      labelId="ma-method-label"
                      value={currentReport.config.movingAverage?.method || 'mean'}
                      label="Zentralwert"
                      onChange={(e) => setCurrentReport(prev => ({ ...prev, config: { ...prev.config, movingAverage: { ...(prev.config.movingAverage || { enabled: true, window: 3, method: 'mean' }), method: e.target.value as 'mean' | 'median' } } }))}
                    >
                      <MenuItem value="mean">Mittelwert</MenuItem>
                      <MenuItem value="median">Median</MenuItem>
                    </Select>
                  </FormControl>
                )}

                {isSuperAdmin && (
                  <Box display="flex" alignItems="center" gap={1}>
                    <FormControlLabel
                      control={
                        <Checkbox
                          checked={!!currentReport.config.use_db_aggregates}
                          onChange={(e) => handleConfigChange('use_db_aggregates', e.target.checked)}
                        />
                      }
                      label="DB-Aggregate (opt-in)"
                    />
                    <Tooltip enterDelay={300} title="Aktiviere diese Option, damit die Vorschau einfache Gruppierungen/Counts in der Datenbank statt in PHP berechnet (Admin-Option).">
                      <IconButton size="small" aria-label="Info DB-Aggregate" sx={{ color: 'text.secondary' }}>
                        <InfoOutlinedIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  </Box>
                )}
              </Box>

              {/* Heatmap style selector when pitchheatmap is chosen */}
              {currentReport.config.diagramType === 'pitchheatmap' && (
                <>
                  <Box sx={{ position: 'relative' }}>
                    <FormControl fullWidth margin="normal">
                      <InputLabel>Heatmap-Darstellung</InputLabel>
                      <Select
                        value={(currentReport.config as any).heatmapStyle || 'smoothed'}
                        onChange={(e) => handleConfigChange('heatmapStyle', e.target.value)}
                        label="Heatmap-Darstellung"
                      >
                        <MenuItem value="smoothed">Geglättete Heatmap (Standard)</MenuItem>
                        <MenuItem value="classic">Klassische Heatmap (Grid)</MenuItem>
                        <MenuItem value="both">Beides (Grid + Glättung)</MenuItem>
                      </Select>
                    </FormControl>
                    <Tooltip enterDelay={300} title="Wähle Darstellung: 'Geglättet' für performante Overlay-Heatmap, 'Klassisch' für exakte Zellenwerte (Grid). 'Beides' zeigt beide Ansichten.">
                      <IconButton
                        size="small"
                        aria-label="Info Heatmap-Stil"
                        sx={{ color: 'text.secondary', position: 'absolute', right: 8, top: '50%', transform: 'translateY(-50%)' }}
                        onClick={(e) => setHeatmapAnchorEl(e.currentTarget as HTMLElement)}
                      >
                        <InfoOutlinedIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                    <Popover
                      open={Boolean(heatmapAnchorEl)}
                      anchorEl={heatmapAnchorEl}
                      onClose={() => setHeatmapAnchorEl(null)}
                      anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
                      transformOrigin={{ vertical: 'top', horizontal: 'right' }}
                    >
                      <Box sx={{ p: 2, maxWidth: 320 }}>
                        <Typography variant="subtitle2">Heatmap-Darstellung</Typography>
                        <Typography variant="body2">Wähle Darstellung: 'Geglättet' für performante Overlay-Heatmap, 'Klassisch' für exakte Zellenwerte (Grid). 'Beides' zeigt beide Ansichten.</Typography>
                      </Box>
                    </Popover>
                  </Box>

                  <FormControl fullWidth margin="normal">
                    <Box display="flex" alignItems="center" gap={1}>
                      <FormControlLabel
                        control={
                          <Checkbox
                            checked={!!(currentReport.config as any).heatmapSpatial}
                            onChange={(e) => setCurrentReport(prev => ({ ...prev, config: { ...prev.config, heatmapSpatial: e.target.checked } }))}
                          />
                        }
                        label="Räumliche Heatmap (x/y)"
                      />
                      <Tooltip enterDelay={300} title="Versucht Server-seitige X/Y-Koordinaten (0–100%) für Events zu verwenden. Fehlen Positionen, fällt der Server auf Matrix (Zellen) zurück — die Vorschau zeigt dann einen Hinweis.">
                        <IconButton size="small" aria-label="Info Räumliche Heatmap" sx={{ color: 'text.secondary' }}>
                          <InfoOutlinedIcon fontSize="small" />
                        </IconButton>
                      </Tooltip>
                    </Box>
                  </FormControl>
                </>
              )}

              {/* Radar overlay normalization option */}
              {(currentReport.config.diagramType === 'radar' || currentReport.config.diagramType === 'radaroverlay') && (
                <Box display="flex" alignItems="center" gap={1}>
                  <FormControlLabel
                    control={<Checkbox checked={(currentReport.config as any).radarNormalize === true} onChange={(e) => setCurrentReport(prev => ({ ...prev, config: { ...prev.config, radarNormalize: e.target.checked } }))} />}
                    label="Pro Dataset normalisieren"
                  />
                  <Tooltip enterDelay={300} title="Normiert jedes Dataset auf 0..1, hilfreich wenn Datasets stark unterschiedliche Summen haben — zeigt Form anstelle von absoluten Werten.">
                    <IconButton size="small" aria-label="Info Radar Normalisierung" sx={{ color: 'text.secondary' }}>
                      <InfoOutlinedIcon fontSize="small" />
                    </IconButton>
                  </Tooltip>
                </Box>
              )}

              {/* Metrics selector for Radar charts (searchable) */}
              {currentReport.config.diagramType === 'radar' && (
                <Box sx={{ mt: 2 }}>
                  <Autocomplete
                    multiple
                    options={builderData?.metrics ?? []}
                    getOptionLabel={(opt) => typeof opt === 'string' ? opt : opt.label}
                    value={(builderData?.metrics ?? []).filter((m) => (currentReport.config.metrics || []).includes(m.key))}
                    onChange={(_, newValue) => handleConfigChange('metrics', newValue.map((v: any) => v.key))}
                    renderTags={(value: any[], getTagProps) =>
                      value.map((option: any, index: number) => (
                        <Chip label={option.label} {...getTagProps({ index })} key={option.key} size="small" />
                      ))
                    }
                    renderInput={(params) => (
                      <TextField {...params} label="Metriken" placeholder="Metriken auswählen" />
                    )}
                  />
                </Box>
              )}

              {/* Gruppierung */}
              <Box sx={{ position: 'relative' }}>
                <FormControl fullWidth margin="normal">
                  <InputLabel>Gruppierung (optional)</InputLabel>
                  <Select
                    value={currentReport.config.groupBy || ''}
                    onChange={(e) => handleConfigChange('groupBy', e.target.value)}
                    label="Gruppierung (optional)"
                  >
                    <MenuItem value="">Keine Gruppierung</MenuItem>
                    {availableFields.map((field) => (
                      <MenuItem key={field.key} value={field.key}>
                        {field.label}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
                <Tooltip enterDelay={300} title="Gruppiert die Daten in separate Layer (z. B. nach Spieler oder Team). Nützlich zum Vergleich über Kategorien hinweg.">
                  <IconButton
                    size="small"
                    aria-label="Info Gruppierung"
                    sx={{ color: 'text.secondary', position: 'absolute', right: 8, top: '50%', transform: 'translateY(-50%)' }}
                    onClick={(e) => setGroupAnchorEl(e.currentTarget as HTMLElement)}
                  >
                    <InfoOutlinedIcon fontSize="small" />
                  </IconButton>
                </Tooltip>
                <Popover
                  open={Boolean(groupAnchorEl)}
                  anchorEl={groupAnchorEl}
                  onClose={() => setGroupAnchorEl(null)}
                  anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
                  transformOrigin={{ vertical: 'top', horizontal: 'right' }}
                >
                  <Box sx={{ p: 2, maxWidth: 320 }}>
                    <Typography variant="subtitle2">Gruppierung</Typography>
                    <Typography variant="body2">Gruppiert die Daten in separate Layer (z. B. nach Spieler oder Team). Nützlich zum Vergleich über Kategorien hinweg.</Typography>
                  </Box>
                </Popover>
              </Box>

              {/* no area-specific UI here; 'area' is a chart type rendered as filled line chart */}

              <Divider sx={{ my: 2 }} />

              {/* Datumsbereich Slider */}
              {builderData?.availableDates && builderData?.availableDates.length > 0 && (
                <Box sx={{ mt: 2, px: 2 }}>
                  <Typography variant="subtitle2" gutterBottom>
                    Datumsbereich
                  </Typography>
                  
                  <Box display="flex" gap={1} mb={2}>
                    <FormControlLabel
                      control={
                        <Checkbox
                          checked={currentReport.config.filters?.dateFrom != null}
                          onChange={(e) => {
                            if (e.target.checked) {
                              handleFilterChange('dateFrom', builderData?.minDate);
                            } else {
                              handleFilterChange('dateFrom', null);
                            }
                          }}
                        />
                      }
                      label="Von"
                    />
                    <FormControlLabel
                      control={
                        <Checkbox
                          checked={currentReport.config.filters?.dateTo != null}
                          onChange={(e) => {
                            if (e.target.checked) {
                              handleFilterChange('dateTo', builderData?.maxDate);
                            } else {
                              handleFilterChange('dateTo', null);
                            }
                          }}
                        />
                      }
                      label="Bis"
                    />
                  </Box>

                  {(currentReport.config.filters?.dateFrom != null || currentReport.config.filters?.dateTo != null) && (
                    <Box sx={{ mx: 2, mt: 2, mb: 3 }}>
                      <Slider
                        value={[
                          currentReport.config.filters?.dateFrom != null 
                            ? builderData?.availableDates.indexOf(currentReport.config.filters.dateFrom)
                            : 0,
                          currentReport.config.filters?.dateTo != null 
                            ? builderData?.availableDates.indexOf(currentReport.config.filters.dateTo)
                            : builderData?.availableDates.length - 1
                        ]}
                        onChange={(_, newValue) => {
                          const [startIndex, endIndex] = newValue as number[];
                          if (currentReport.config.filters?.dateFrom != null) {
                            handleFilterChange('dateFrom', builderData?.availableDates[startIndex]);
                          }
                          if (currentReport.config.filters?.dateTo != null) {
                            handleFilterChange('dateTo', builderData?.availableDates[endIndex]);
                          }
                        }}
                        min={0}
                        max={builderData?.availableDates.length - 1}
                        valueLabelDisplay="auto"
                        valueLabelFormat={(value) => builderData?.availableDates[value]}
                        marks={[
                          { value: 0, label: builderData?.minDate },
                          { value: builderData?.availableDates.length - 1, label: builderData?.maxDate }
                        ]}
                      />
                    </Box>
                  )}
                </Box>
              )}

              <Divider sx={{ my: 2 }} />

              {/* Filter */}
              <Typography variant="subtitle1" gutterBottom>
                Filter
              </Typography>

              {builderData && (
                <>
                  <FormControl fullWidth margin="normal">
                    <InputLabel>Team</InputLabel>
                    <Select
                      value={currentReport.config.filters?.team || ''}
                      onChange={(e) => handleFilterChange('team', e.target.value)}
                      label="Team"
                    >
                      <MenuItem value="">Alle Teams</MenuItem>
                      {builderData?.teams.map((team) => (
                        <MenuItem key={team.id} value={team.id.toString()}>
                          {team.name}
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>

                  <FormControl fullWidth margin="normal">
                    <InputLabel>Spieler</InputLabel>
                    <Select
                      value={currentReport.config.filters?.player || ''}
                      onChange={(e) => handleFilterChange('player', e.target.value)}
                      label="Spieler"
                    >
                      <MenuItem value="">Alle Spieler</MenuItem>
                      {builderData?.players.map((player) => (
                        <MenuItem key={player.id} value={player.id.toString()}>
                          {player.fullName}
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>

                  <FormControl fullWidth margin="normal">
                    <InputLabel>Ereignistyp</InputLabel>
                    <Select
                      value={currentReport.config.filters?.eventType || ''}
                      onChange={(e) => handleFilterChange('eventType', e.target.value)}
                      label="Ereignistyp"
                    >
                      <MenuItem value="">Alle Ereignistypen</MenuItem>
                      {builderData?.eventTypes.map((eventType) => (
                        <MenuItem key={eventType.id} value={eventType.id.toString()}>
                          {eventType.name}
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                  <FormControl fullWidth margin="normal">
                    <InputLabel>Platztyp</InputLabel>
                    <Select
                      value={currentReport.config.filters?.surfaceType || ''}
                      onChange={(e) => handleFilterChange('surfaceType', e.target.value)}
                      label="Platztyp"
                    >
                      <MenuItem value="">Alle Platztypen</MenuItem>
                      {builderData?.surfaceTypes?.map((st) => (
                        <MenuItem key={st.id} value={st.id.toString()}>
                          {st.name}
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                    <Box sx={{ position: 'relative' }}>
                      <FormControl fullWidth margin="normal">
                        <InputLabel>Wetter (Niederschlag)</InputLabel>
                        <Select
                          value={currentReport.config.filters?.precipitation || ''}
                          onChange={(e) => handleFilterChange('precipitation', e.target.value)}
                          label="Wetter (Niederschlag)"
                        >
                          <MenuItem value="">Beliebig</MenuItem>
                          <MenuItem value="yes">Mit Niederschlag</MenuItem>
                          <MenuItem value="no">Ohne Niederschlag</MenuItem>
                        </Select>
                      </FormControl>
                      <Tooltip enterDelay={300} title="Filtert Spiele nach vorhandenem Niederschlag (aus Wetterdaten).">
                        <IconButton
                          size="small"
                          aria-label="Info Niederschlag"
                          sx={{ color: 'text.secondary', position: 'absolute', right: 8, top: '50%', transform: 'translateY(-50%)' }}
                          onClick={(e) => setPrecipAnchorEl(e.currentTarget as HTMLElement)}
                        >
                          <InfoOutlinedIcon fontSize="small" />
                        </IconButton>
                      </Tooltip>
                      <Popover
                        open={Boolean(precipAnchorEl)}
                        anchorEl={precipAnchorEl}
                        onClose={() => setPrecipAnchorEl(null)}
                        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
                        transformOrigin={{ vertical: 'top', horizontal: 'right' }}
                      >
                        <Box sx={{ p: 2, maxWidth: 320 }}>
                          <Typography variant="subtitle2">Wetter (Niederschlag)</Typography>
                          <Typography variant="body2">Filtert Spiele nach vorhandenem Niederschlag (aus Wetterdaten).</Typography>
                        </Box>
                      </Popover>
                    </Box>
                    <Box display="flex" alignItems="center" gap={1}>
                      <FormControlLabel
                        control={<Checkbox checked={currentReport.config.groupBy === 'surfaceType'} onChange={(e) => {
                          if (e.target.checked) {
                            setPrevGroupBy(currentReport.config.groupBy);
                            handleConfigChange('groupBy', 'surfaceType');
                          } else {
                            handleConfigChange('groupBy', prevGroupBy);
                          }
                        }} />}
                        label="Vergleich nach Platztyp"
                      />
                      <Tooltip enterDelay={300} title="Setzt die Gruppierung auf 'Platztyp', um Metriken nach Platzoberfläche zu vergleichen.">
                        <IconButton size="small" aria-label="Info Vergleich nach Platztyp" sx={{ color: 'text.secondary' }}>
                          <InfoOutlinedIcon fontSize="small" />
                        </IconButton>
                      </Tooltip>
                    </Box>
                </>
              )}
            </Box>
          
          {/* Preview Panel */}
          <Box 
              flex={1} 
              display="flex" 
              flexDirection="column" 
              minHeight={{ xs: 300, lg: 'auto' }}
            >
              <Box display="flex" alignItems="center" gap={1}>
                <Typography variant="h6" gutterBottom>
                  Vorschau
                </Typography>
                <Tooltip title="Hilfe zur räumlichen Heatmap und Fallbacks">
                  <IconButton size="small" onClick={() => setHelpOpen(true)} aria-label="Hilfe anzeigen">
                    <InfoOutlinedIcon fontSize="small" />
                  </IconButton>
                </Tooltip>
              </Box>
              
              <Box 
                flex={1} 
                display="flex" 
                alignItems="center" 
                justifyContent="center"
                border={1}
                borderColor="divider"
                borderRadius={1}
                bgcolor="background.paper"
                minHeight={300}
              >
                {isLoading ? (
                  <Typography color="text.secondary">Lade Vorschau...</Typography>
                ) : !currentReport.config.xField || !currentReport.config.yField ? (
                  <Box textAlign="center">
                    <Typography variant="body1" color="text.secondary" gutterBottom>
                      Wähle X- und Y-Achse für Vorschau
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Ziehe Felder aus "Verfügbare Felder" in die X- und Y-Achsenbereiche
                    </Typography>
                  </Box>
                ) : previewData ? (
                  <Box sx={{ width: '100%', px: 1 }}>
                    {/* Preview meta warnings */}
                    {previewData.meta && (
                      <Box sx={{ mb: 1 }}>
                        {/* User-facing concise message */}
                        {previewData.meta.userMessage && (
                          <Alert severity={previewData.meta.eventsCount === 0 ? 'warning' : 'info'}>{previewData.meta.userMessage}</Alert>
                        )}

                        {/* Admin / advanced details (hidden by default) */}
                        {isSuperAdmin && (
                          <Box sx={{ mt: 1 }}>
                            <Button size="small" onClick={() => setShowAdvancedMeta(s => !s)}>
                              {showAdvancedMeta ? 'Erweiterte Details verbergen' : 'Erweiterte Details anzeigen'}
                            </Button>
                            {showAdvancedMeta && (
                              <Box sx={{ mt: 1 }}>
                                {previewData.meta.dbAggregate === true && (
                                  <Alert severity="info">Vorschau berechnet Aggregation in der Datenbank (DB-Aggregate).</Alert>
                                )}
                                {Array.isArray(previewData.meta.warnings) && previewData.meta.warnings.map((w: string, idx: number) => (
                                  <Alert key={`warn-${idx}`} severity="warning">{w}</Alert>
                                ))}
                                {Array.isArray(previewData.meta.suggestions) && previewData.meta.suggestions.map((s: string, idx: number) => (
                                  <Alert key={`sug-${idx}`} severity="info">{s}</Alert>
                                ))}
                                {/* Additional computed preview warnings useful for debugging */}
                                {computePreviewWarnings().movingAverageWindowTooLarge && (
                                  <Alert severity="warning">Gleitschnitt-Fenster ist größer als die Anzahl an Datenpunkten — Ergebnis könnte leer aussehen.</Alert>
                                )}
                                {computePreviewWarnings().boxplotFormatInvalid && (
                                  <Alert severity="warning">Boxplot erwartet pro Label Arrays mit numerischen Werten.</Alert>
                                )}
                                {computePreviewWarnings().scatterNonNumeric && (
                                  <Alert severity="warning">Scatter-Diagramm enthält nicht-numerische Werte.</Alert>
                                )}
                              </Box>
                            )}
                          </Box>
                        )}
                      </Box>
                    )}
                    <WidgetRefreshProvider>
                      <ReportWidget 
                        config={previewData}
                      />
                    </WidgetRefreshProvider>
                  </Box>
                ) : (
                  <Typography color="text.secondary">Preview wird geladen...</Typography>
                )}
              </Box>
            </Box>
          </Box>
        </DragDropContext>
        {/* Help dialog explaining spatial heatmap behavior and fallbacks */}
        <Dialog open={helpOpen} onClose={() => setHelpOpen(false)} aria-labelledby="report-help-dialog">
          <DialogTitle id="report-help-dialog">Hilfe: Räumliche Heatmap & Fallbacks</DialogTitle>
          <DialogContent>
            <DialogContentText component="div">
              <Typography paragraph>
                Wenn die Option "Räumliche Heatmap (x/y)" aktiviert ist, versucht der Server für jedes Ereignis Koordinaten
                im Spielfeldmaß (als Prozentwerte 0–100) bereitzustellen. Mit diesen Koordinaten kann die Heatmap genau
                auf dem Spielfeld positioniert und als echte, geglättete Überlagerung dargestellt werden.
              </Typography>
              <Typography paragraph>
                Falls die Datenbank oder die Ereignis-Daten keine Positionswerte enthalten, liefert der Server stattdessen
                eine Matrix-basierte Ausgabe (Zellen/Counts). In diesem Fall wird die Heatmap im Builder als Matrix/Fallback
                gerendert. Die Vorschau zeigt eine Info-Meldung an, wenn dieser Fallback verwendet wird.
              </Typography>
              <Typography paragraph>
                Hinweise zur Aktivierung von Positionsdaten: Füge den Ereignissen X/Y-Werte (z. B. `posX`, `posY` als Prozent)
                hinzu oder importiere Koordinaten beim Datenimport. Alternativ kann ein Migrationsskript die Positionen aus
                externen Quellen ergänzen. Diese Änderungen sind in der Regel administrativ und erfordern DB-/Import-Anpassungen.
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Tipp für Admins: Wenn du Unterstützung beim Hinzufügen von Positionsdaten brauchst, kann ich kurz skizzieren,
                welche Felder und Migrationen sinnvoll wären.
              </Typography>
            </DialogContentText>
          </DialogContent>
          <DialogActions>
            <Button onClick={() => setHelpOpen(false)} autoFocus>Schließen</Button>
          </DialogActions>
        </Dialog>
    </BaseModal>
  );
};
