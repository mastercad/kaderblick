import React from 'react';
import {
  Box,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Typography,
  Paper,
  Slider,
  Checkbox,
  FormControlLabel,
} from '@mui/material';
import type { ReportBuilderState } from './types';

interface StepFiltersProps {
  state: ReportBuilderState;
}

export const StepFilters: React.FC<StepFiltersProps> = ({ state }) => {
  const {
    currentReport,
    builderData,
    handleFilterChange,
  } = state;

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
      {!builderData ? (
        <Typography color="text.secondary">Lade Filterdaten...</Typography>
      ) : (
        <>
          {/* Date range */}
          {builderData.availableDates?.length > 0 && (
            <Paper variant="outlined" sx={{ p: 2 }}>
              <Typography variant="subtitle2" gutterBottom>Zeitraum</Typography>
              <Box display="flex" gap={2} mb={1} flexWrap="wrap">
                <FormControlLabel
                  control={
                    <Checkbox
                      checked={currentReport.config.filters?.dateFrom != null}
                      onChange={(e) => handleFilterChange('dateFrom', e.target.checked ? builderData.minDate : null)}
                      size="small"
                    />
                  }
                  label="Von"
                />
                <FormControlLabel
                  control={
                    <Checkbox
                      checked={currentReport.config.filters?.dateTo != null}
                      onChange={(e) => handleFilterChange('dateTo', e.target.checked ? builderData.maxDate : null)}
                      size="small"
                    />
                  }
                  label="Bis"
                />
              </Box>

              {(currentReport.config.filters?.dateFrom != null || currentReport.config.filters?.dateTo != null) && (
                <Box sx={{ px: 1, mt: 1 }}>
                  <Slider
                    value={[
                      currentReport.config.filters?.dateFrom != null
                        ? builderData.availableDates.indexOf(currentReport.config.filters.dateFrom)
                        : 0,
                      currentReport.config.filters?.dateTo != null
                        ? builderData.availableDates.indexOf(currentReport.config.filters.dateTo)
                        : builderData.availableDates.length - 1,
                    ]}
                    onChange={(_, newValue) => {
                      const [startIndex, endIndex] = newValue as number[];
                      if (currentReport.config.filters?.dateFrom != null) {
                        handleFilterChange('dateFrom', builderData.availableDates[startIndex]);
                      }
                      if (currentReport.config.filters?.dateTo != null) {
                        handleFilterChange('dateTo', builderData.availableDates[endIndex]);
                      }
                    }}
                    min={0}
                    max={builderData.availableDates.length - 1}
                    valueLabelDisplay="auto"
                    valueLabelFormat={(value) => builderData.availableDates[value]}
                    marks={[
                      { value: 0, label: builderData.minDate },
                      { value: builderData.availableDates.length - 1, label: builderData.maxDate },
                    ]}
                  />
                </Box>
              )}
            </Paper>
          )}

          {/* Team */}
          <FormControl fullWidth>
            <InputLabel>Team</InputLabel>
            <Select
              value={currentReport.config.filters?.team || ''}
              onChange={(e) => handleFilterChange('team', e.target.value)}
              label="Team"
            >
              <MenuItem value="">Alle Teams</MenuItem>
              {builderData.teams.map((team) => (
                <MenuItem key={team.id} value={team.id.toString()}>{team.name}</MenuItem>
              ))}
            </Select>
          </FormControl>

          {/* Spieler */}
          <FormControl fullWidth>
            <InputLabel>Spieler</InputLabel>
            <Select
              value={currentReport.config.filters?.player || ''}
              onChange={(e) => handleFilterChange('player', e.target.value)}
              label="Spieler"
            >
              <MenuItem value="">Alle Spieler</MenuItem>
              {builderData.players.map((player) => (
                <MenuItem key={player.id} value={player.id.toString()}>{player.fullName}</MenuItem>
              ))}
            </Select>
          </FormControl>

          {/* Spieltyp */}
          {(builderData.gameTypes?.length ?? 0) > 0 && (
            <FormControl fullWidth>
              <InputLabel>Spieltyp</InputLabel>
              <Select
                value={currentReport.config.filters?.gameType || ''}
                onChange={(e) => handleFilterChange('gameType', e.target.value)}
                label="Spieltyp"
              >
                <MenuItem value="">Alle Spieltypen</MenuItem>
                {builderData.gameTypes!.map((gt) => (
                  <MenuItem key={gt.id} value={gt.id.toString()}>{gt.name}</MenuItem>
                ))}
              </Select>
            </FormControl>
          )}

          {/* Ereignistyp */}
          <FormControl fullWidth>
            <InputLabel>Ereignistyp</InputLabel>
            <Select
              value={currentReport.config.filters?.eventType || ''}
              onChange={(e) => handleFilterChange('eventType', e.target.value)}
              label="Ereignistyp"
            >
              <MenuItem value="">Alle Ereignistypen</MenuItem>
              {builderData.eventTypes.map((eventType) => (
                <MenuItem key={eventType.id} value={eventType.id.toString()}>{eventType.name}</MenuItem>
              ))}
            </Select>
          </FormControl>

          {/* Platztyp */}
          <FormControl fullWidth>
            <InputLabel>Platztyp</InputLabel>
            <Select
              value={currentReport.config.filters?.surfaceType || ''}
              onChange={(e) => handleFilterChange('surfaceType', e.target.value)}
              label="Platztyp"
            >
              <MenuItem value="">Alle Platztypen</MenuItem>
              {builderData.surfaceTypes?.map((st) => (
                <MenuItem key={st.id} value={st.id.toString()}>{st.name}</MenuItem>
              ))}
            </Select>
          </FormControl>

          {/* Niederschlag */}
          <FormControl fullWidth>
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
        </>
      )}
    </Box>
  );
};
