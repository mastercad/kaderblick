import React, { useState } from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, FormGroup, FormControlLabel, Checkbox, Box } from '@mui/material';

const ALL_ROLES = [
  'ROLE_GUEST',
  'ROLE_USER',
  'ROLE_SUPPORTER',
  'ROLE_CLUB',
  'ROLE_ADMIN',
  'ROLE_SUPERADMIN',
];

interface EditUserRolesModalProps {
  open: boolean;
  onClose: () => void;
  user: any;
  onSave: (roles: string[]) => void;
}

const EditUserRolesModal: React.FC<EditUserRolesModalProps> = ({ open, onClose, user, onSave }) => {
  // Normalize roles to always be an array
  const normalizeRoles = (roles: any): string[] => {
    if (Array.isArray(roles)) return roles;
    if (roles && typeof roles === 'object') return Object.values(roles);
    return [];
  };
  const [roles, setRoles] = useState<string[]>(normalizeRoles(user?.roles));

  React.useEffect(() => {
    setRoles(normalizeRoles(user?.roles));
  }, [user]);

  const handleToggle = (role: string) => {
    setRoles((prev) => {
      // prev should always be an array, but double check
      const arr = Array.isArray(prev) ? prev : normalizeRoles(prev);
      return arr.includes(role) ? arr.filter((r) => r !== role) : [...arr, role];
    });
  };

  const handleSave = () => {
    onSave(roles);
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="xs" fullWidth>
      <DialogTitle>Rollen bearbeiten für {user?.fullName || user?.email}</DialogTitle>
      <DialogContent>
        <Box sx={{ mt: 1 }}>
          <FormGroup>
            {ALL_ROLES.map((role) => (
              <FormControlLabel
                key={role}
                control={
                  <Checkbox
                    checked={roles.includes(role)}
                    onChange={() => handleToggle(role)}
                  />
                }
                label={role.replace('ROLE_', '')}
              />
            ))}
          </FormGroup>
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Abbrechen</Button>
        <Button onClick={handleSave} variant="contained">Speichern</Button>
      </DialogActions>
    </Dialog>
  );
};

export default EditUserRolesModal;
