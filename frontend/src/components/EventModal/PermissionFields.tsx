import React from 'react';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import Autocomplete from '@mui/material/Autocomplete';
import TextField from '@mui/material/TextField';
import { EventData, SelectOption, User } from '../../types/event';

interface PermissionFieldsProps {
  formData: EventData;
  teams: SelectOption[];
  users: User[];
  handleChange: (field: string, value: any) => void;
}

/**
 * Permission/visibility fields for events:
 * - Permission type (public/club/team/user)
 * - Specific team/club/user selection based on type
 */
const PermissionFieldsComponent: React.FC<PermissionFieldsProps> = ({
  formData,
  teams,
  users,
  handleChange,
}) => {
  return (
    <>
      <FormControl fullWidth margin="normal">
        <InputLabel id="permission-type-label">Sichtbarkeit</InputLabel>
        <Select
          labelId="permission-type-label"
          value={formData.permissionType || 'public'}
          label="Sichtbarkeit"
          onChange={e => handleChange('permissionType', e.target.value as string)}
        >
          <MenuItem value="public">Öffentlich</MenuItem>
          <MenuItem value="club">Spezifische Clubs</MenuItem>
          <MenuItem value="team">Spezifische Teams</MenuItem>
          <MenuItem value="user">Spezifische Nutzer</MenuItem>
        </Select>
      </FormControl>
      
      {formData.permissionType === 'team' && (
        <Autocomplete
          multiple
          options={teams}
          getOptionLabel={(option) => option.label}
          value={teams.filter(t => formData.permissionTeams?.includes(t.value))}
          onChange={(_, newValue) => handleChange('permissionTeams', newValue.map(t => t.value))}
          renderInput={(params) => (
            <TextField
              {...params}
              label="Teams auswählen"
              margin="normal"
            />
          )}
        />
      )}
      
      {formData.permissionType === 'club' && (
        <Autocomplete
          multiple
          options={teams.map(t => ({ value: t.value, label: `Club ${t.label}` }))}
          getOptionLabel={(option) => option.label}
          value={teams
            .filter(t => formData.permissionClubs?.includes(t.value))
            .map(t => ({ value: t.value, label: `Club ${t.label}` }))}
          onChange={(_, newValue) => handleChange('permissionClubs', newValue.map(c => c.value))}
          renderInput={(params) => (
            <TextField
              {...params}
              label="Clubs auswählen"
              margin="normal"
            />
          )}
        />
      )}
      
      {formData.permissionType === 'user' && (
        <Autocomplete
          multiple
          options={users}
          getOptionLabel={(option) =>
            option.fullName || `${option.firstName} ${option.lastName}` || String(option.id)
          }
          value={users.filter(u => formData.permissionUsers?.includes(String(u.id)))}
          onChange={(_, newValue) => handleChange('permissionUsers', newValue.map(u => String(u.id)))}
          renderInput={(params) => (
            <TextField
              {...params}
              label="Nutzer auswählen"
              margin="normal"
            />
          )}
        />
      )}
    </>
  );
};

export const PermissionFields = React.memo(PermissionFieldsComponent);
