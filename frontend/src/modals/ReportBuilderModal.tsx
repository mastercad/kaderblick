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
} from '@mui/material';
import { DragDropContext, Droppable, Draggable, DropResult } from '@hello-pangea/dnd';
import { ReportWidget } from '../widgets/ReportWidget';
import { WidgetRefreshProvider } from '../context/WidgetRefreshContext';
import { apiJson } from '../utils/api';
import { useAuth } from '../context/AuthContext';
import BaseModal from './BaseModal';

interface FieldOption {
  key: string;
  label: string;
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
    filters?: {
      team?: string;
      player?: string;
      eventType?: string;
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
  teams: Array<{ id: number; name: string }>;
  players: Array<{ id: number; fullName: string; firstName: string; lastName: string }>;
  eventTypes: Array<{ id: number; name: string }>;
  availableDates: string[];
  minDate: string;
  maxDate: string;
}

export const ReportBuilderModal: React.FC<ReportBuilderModalProps> = ({
  open,
  onClose,
  onSave,
  report,
}) => {
  const { isSuperAdmin } = useAuth();
  const [currentReport, setCurrentReport] = useState<Report>({
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

  const [availableFields, setAvailableFields] = useState<FieldOption[]>([]);
  const [previewData, setPreviewData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [builderData, setBuilderData] = useState<BuilderData | null>(null);

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
      setPreviewData(data);
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
      const newFilters = { ...prev.config.filters };
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

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      maxWidth="lg"
      title={report ? 'Report bearbeiten' : 'Neuer Report'}
      actions={
        <>
          <Button onClick={onClose} variant="outlined" color="secondary">
            Abbrechen
          </Button>
          <Button
            onClick={handleSave}
            variant="contained"
            color="primary"
            disabled={!currentReport.name || !currentReport.config.xField || !currentReport.config.yField}
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
                                sx={{ cursor: 'grab' }}
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
                                sx={{ cursor: 'grab' }}
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
                              label={field.label}
                              variant="outlined"
                              size="small"
                              sx={{ cursor: 'grab' }}
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
                  <MenuItem value="pie">Kreisdiagramm</MenuItem>
                  <MenuItem value="doughnut">Donut-Diagramm</MenuItem>
                </Select>
              </FormControl>

              {/* Gruppierung */}
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
              <Typography variant="h6" gutterBottom>
                Vorschau
              </Typography>
              
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
                  <WidgetRefreshProvider>
                    <ReportWidget 
                      config={previewData}
                    />
                  </WidgetRefreshProvider>
                ) : (
                  <Typography color="text.secondary">Preview wird geladen...</Typography>
                )}
              </Box>
            </Box>
          </Box>
        </DragDropContext>
    </BaseModal>
  );
};
