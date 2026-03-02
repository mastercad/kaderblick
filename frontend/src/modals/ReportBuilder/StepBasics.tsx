import React from 'react';
import {
  Box,
  TextField,
  Typography,
  Paper,
  Chip,
  Checkbox,
  FormControlLabel,
} from '@mui/material';
import type { ReportBuilderState } from './types';

interface StepBasicsProps {
  state: ReportBuilderState;
}

export const StepBasics: React.FC<StepBasicsProps> = ({ state }) => {
  const { currentReport, setCurrentReport, builderData, isSuperAdmin, activeStep } = state;

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
      <TextField
        fullWidth
        label="Report Name *"
        value={currentReport.name}
        onChange={(e) => setCurrentReport(prev => ({ ...prev, name: e.target.value }))}
        placeholder="z.B. Torschüsse pro Spieltag"
        helperText={!currentReport.name ? 'Pflichtfeld' : undefined}
        error={!currentReport.name && activeStep > 0}
      />

      <TextField
        fullWidth
        label="Beschreibung"
        value={currentReport.description}
        onChange={(e) => setCurrentReport(prev => ({ ...prev, description: e.target.value }))}
        multiline
        rows={2}
        placeholder="Kurze Beschreibung des Reports (optional)"
      />

      {/* Presets */}
      {builderData?.presets && builderData.presets.length > 0 && (
        <Paper variant="outlined" sx={{ p: 2 }}>
          <Typography variant="subtitle2" sx={{ mb: 1.5 }}>
            Schnellstart — Vorlage wählen
          </Typography>
          <Box display="flex" gap={1} flexWrap="wrap">
            {builderData.presets.map((p: any) => (
              <Chip
                key={p.key}
                label={p.label}
                onClick={() => setCurrentReport(prev => ({ ...prev, config: { ...prev.config, ...p.config } }))}
                variant="outlined"
                clickable
                sx={{ fontSize: '0.85rem' }}
              />
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
              onChange={(e) => setCurrentReport(prev => ({ ...prev, isTemplate: e.target.checked }))}
            />
          }
          label="Als Template verfügbar machen"
        />
      )}
    </Box>
  );
};
