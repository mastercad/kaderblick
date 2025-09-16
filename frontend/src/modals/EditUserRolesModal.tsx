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
  const [roles, setRoles] = useState<string[]>(user?.roles || []);

  React.useEffect(() => {
    setRoles(user?.roles || []);
  }, [user]);

  const handleToggle = (role: string) => {
    setRoles((prev) =>
      prev.includes(role) ? prev.filter((r) => r !== role) : [...prev, role]
    );
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
