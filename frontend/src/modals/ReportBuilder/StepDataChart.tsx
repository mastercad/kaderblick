import React from 'react';
import {
  Box,
  TextField,
  FormControl,
  FormControlLabel,
  InputLabel,
  Select,
  MenuItem,
  Typography,
  Paper,
  Chip,
  ListSubheader,
  Switch,
} from '@mui/material';
import Autocomplete from '@mui/material/Autocomplete';
import type { ReportBuilderState, FieldOption } from './types';
import { DIAGRAM_TYPES } from './types';

interface StepDataChartProps {
  state: ReportBuilderState;
}

/** Classify fields into dimensions (grouping) vs metrics (counting) for structured dropdowns */
function splitFields(fields: FieldOption[]) {
  const dimensions = fields.filter(f => !f.isMetricCandidate);
  const metrics = fields.filter(f => f.isMetricCandidate);
  return { dimensions, metrics };
}

export const StepDataChart: React.FC<StepDataChartProps> = ({ state }) => {
  const {
    currentReport,
    availableFields,
    builderData,
    isMobile,
    diag,
    handleConfigChange,
  } = state;

  const { dimensions, metrics } = splitFields(availableFields);

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2.5 }}>

      {/* X-Axis — typically a dimension (Spieler, Team, Monat...) */}
      <FormControl fullWidth>
        <InputLabel>X-Achse (Gruppierung) *</InputLabel>
        <Select
          value={currentReport.config.xField}
          onChange={(e) => handleConfigChange('xField', e.target.value)}
          label="X-Achse (Gruppierung) *"
        >
          <MenuItem value="">
            <em>— Feld wählen —</em>
          </MenuItem>
          {dimensions.length > 0 && <ListSubheader>Gruppierung</ListSubheader>}
          {dimensions
            .filter(f => f.key !== currentReport.config.yField)
            .map(f => (
              <MenuItem key={f.key} value={f.key}>{f.label}</MenuItem>
          ))}
          {metrics.length > 0 && <ListSubheader>Metriken</ListSubheader>}
          {metrics
            .filter(f => f.key !== currentReport.config.yField)
            .map(f => (
              <MenuItem key={f.key} value={f.key}>{f.label}</MenuItem>
          ))}
        </Select>
      </FormControl>

      {/* Y-Axis — typically a metric (Tore, Assists, Karten...) */}
      <FormControl fullWidth>
        <InputLabel>Y-Achse (Wert) *</InputLabel>
        <Select
          value={currentReport.config.yField}
          onChange={(e) => handleConfigChange('yField', e.target.value)}
          label="Y-Achse (Wert) *"
        >
          <MenuItem value="">
            <em>— Feld wählen —</em>
          </MenuItem>
          {metrics.length > 0 && <ListSubheader>Metriken</ListSubheader>}
          {metrics
            .filter(f => f.key !== currentReport.config.xField)
            .map(f => (
              <MenuItem key={f.key} value={f.key}>{f.label}</MenuItem>
          ))}
          {dimensions.length > 0 && <ListSubheader>Gruppierung</ListSubheader>}
          {dimensions
            .filter(f => f.key !== currentReport.config.xField)
            .map(f => (
              <MenuItem key={f.key} value={f.key}>{f.label}</MenuItem>
          ))}
        </Select>
      </FormControl>

      {/* Chart Type — card grid on mobile, select on desktop */}
      <Box>
        <Typography variant="subtitle2" sx={{ mb: 1 }}>Chart-Typ</Typography>
        {isMobile ? (
          <Box
            sx={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fill, minmax(130px, 1fr))',
              gap: 1,
            }}
          >
            {DIAGRAM_TYPES.map(dt => (
              <Paper
                key={dt.value}
                variant={currentReport.config.diagramType === dt.value ? 'elevation' : 'outlined'}
                onClick={() => handleConfigChange('diagramType', dt.value)}
                sx={{
                  p: 1.5,
                  cursor: 'pointer',
                  textAlign: 'center',
                  bgcolor: currentReport.config.diagramType === dt.value ? 'primary.main' : 'background.paper',
                  color: currentReport.config.diagramType === dt.value ? 'primary.contrastText' : 'text.primary',
                  border: currentReport.config.diagramType === dt.value ? 2 : 1,
                  borderColor: currentReport.config.diagramType === dt.value ? 'primary.main' : 'divider',
                  transition: 'all 0.15s',
                  '&:active': { transform: 'scale(0.97)' },
                }}
              >
                <Typography variant="body2" fontWeight={currentReport.config.diagramType === dt.value ? 600 : 400}>
                  {dt.label}
                </Typography>
              </Paper>
            ))}
          </Box>
        ) : (
          <FormControl fullWidth>
            <InputLabel>Chart Typ</InputLabel>
            <Select
              value={currentReport.config.diagramType}
              onChange={(e) => handleConfigChange('diagramType', e.target.value)}
              label="Chart Typ"
            >
              {DIAGRAM_TYPES.map(dt => (
                <MenuItem key={dt.value} value={dt.value}>{dt.label}</MenuItem>
              ))}
            </Select>
          </FormControl>
        )}
      </Box>

      {/* Gruppierung */}
      <FormControl fullWidth>
        <InputLabel>Gruppierung (optional)</InputLabel>
        <Select
          value={Array.isArray(currentReport.config.groupBy) ? (currentReport.config.groupBy[0] || '') : (currentReport.config.groupBy || '')}
          onChange={(e) => handleConfigChange('groupBy', e.target.value)}
          label="Gruppierung (optional)"
        >
          <MenuItem value="">Keine Gruppierung</MenuItem>
          {dimensions.map((field) => (
            <MenuItem key={field.key} value={field.key}>{field.label}</MenuItem>
          ))}
        </Select>
      </FormControl>

      {/* Facet-By selector for faceted charts */}
      {diag === 'faceted' && (
      <>
        <FormControl fullWidth>
          <InputLabel>Facette (Panel-Aufteilung) *</InputLabel>
          <Select
            value={currentReport.config.facetBy || ''}
            onChange={(e) => handleConfigChange('facetBy', e.target.value)}
            label="Facette (Panel-Aufteilung) *"
          >
            <MenuItem value="">
              <em>— Feld wählen —</em>
            </MenuItem>
            {dimensions
              .filter(f => f.key !== currentReport.config.xField && f.key !== currentReport.config.groupBy)
              .map(f => (
                <MenuItem key={f.key} value={f.key}>{f.label}</MenuItem>
            ))}
          </Select>
          <Typography variant="caption" color="text.secondary" sx={{ mt: 0.5 }}>
            Pro Wert dieses Feldes wird ein eigenes Panel/Diagramm erstellt.
          </Typography>
        </FormControl>

        {/* Sub-chart type for faceted panels */}
        <FormControl fullWidth>
          <InputLabel>Panel-Diagrammtyp</InputLabel>
          <Select
            value={currentReport.config.facetSubType || 'bar'}
            onChange={(e) => handleConfigChange('facetSubType', e.target.value)}
            label="Panel-Diagrammtyp"
          >
            <MenuItem value="bar">Balken</MenuItem>
            <MenuItem value="radar">Radar</MenuItem>
            <MenuItem value="area">Fläche (Area)</MenuItem>
            <MenuItem value="line">Linie</MenuItem>
          </Select>
          <Typography variant="caption" color="text.secondary" sx={{ mt: 0.5 }}>
            Bestimmt den Chart-Typ innerhalb jedes Panels.
          </Typography>
        </FormControl>

        {/* Transpose toggle: swap axes ↔ datasets */}
        <FormControlLabel
          control={
            <Switch
              checked={currentReport.config.facetTranspose ?? (currentReport.config.facetSubType === 'radar')}
              onChange={(e) => handleConfigChange('facetTranspose', e.target.checked)}
            />
          }
          label="Achsen tauschen (Spieler ↔ Ereignistypen)"
        />
        <Typography variant="caption" color="text.secondary" sx={{ mt: -0.5 }}>
          An: Spieler als Overlay-Layers, Ereignistypen als Achsen. Aus: umgekehrt.
        </Typography>

        {/* Layout selector for faceted panels */}
        <FormControl fullWidth>
          <InputLabel>Darstellung / Layout</InputLabel>
          <Select
            value={currentReport.config.facetLayout || 'grid'}
            onChange={(e) => handleConfigChange('facetLayout', e.target.value)}
            label="Darstellung / Layout"
          >
            <MenuItem value="grid">Raster (Panels nebeneinander)</MenuItem>
            <MenuItem value="vertical">Untereinander (volle Breite)</MenuItem>
            <MenuItem value="interactive">Interaktiv (Umschalten per Klick)</MenuItem>
          </Select>
          <Typography variant="caption" color="text.secondary" sx={{ mt: 0.5 }}>
            Raster: kompakter Überblick. Untereinander: größere Panels. Interaktiv: ein Panel mit Umschalter.
          </Typography>
        </FormControl>
      </>
      )}

      {/* Metrics selector for Radar charts */}
      {(diag === 'radar' || diag === 'radaroverlay') && (
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
      )}
    </Box>
  );
};
