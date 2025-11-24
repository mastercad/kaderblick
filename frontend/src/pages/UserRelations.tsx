import React, { useEffect, useState } from 'react';
import { useToast } from '../context/ToastContext';
import {
  Box,
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Button,
  IconButton,
  Stack,
  Chip,
  CircularProgress
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { apiJson } from '../utils/api';
import UserRelationEditModal from '../modals/UserRelationEditModal';
import UserRelationDeleteModal from '../modals/UserRelationDeleteModal';
import EditUserRolesModal from '../modals/EditUserRolesModal';
import DeleteUserModal from '../modals/DeleteUserModal';
import ResendVerificationModal from '../modals/ResendVerificationModal';

const UserRelations: React.FC = () => {
  const [users, setUsers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [editModal, setEditModal] = useState<{ open: boolean; user?: any } | null>(null);
  const [deleteModal, setDeleteModal] = useState<{ open: boolean; user?: any; relation?: any } | null>(null);
  const [rolesModal, setRolesModal] = useState<{ open: boolean; user?: any } | null>(null);
  const [deleteUserModal, setDeleteUserModal] = useState<{ open: boolean; user?: any } | null>(null);
  const [resendVerificationModal, setResendVerificationModal] = useState<{ open: boolean; user?: any } | null>(null);

  const toast = useToast();

  const handleDeleteUser = async (user: any) => {
    try {
      const res = await apiJson(`/admin/users/${user.id}`, {
        method: 'DELETE',
      });
      if (res && res.success) {
        // User aus der Liste entfernen
        setUsers((prev) => prev.filter(u => u.id !== user.id));
        setDeleteUserModal(null);
        toast.showToast(res.message || 'Benutzer wurde erfolgreich gelöscht', 'success');
      } else {
        toast.showToast(res?.message || 'Fehler beim Löschen des Benutzers', 'error');
      }
    } catch (e: any) {
      toast.showToast(e?.message || 'Fehler beim Löschen des Benutzers', 'error');
    }
  };

  const handleToggleStatus = async (user: any) => {
    try {
      const res = await apiJson(`/admin/users/${user.id}/toggle-status`);
      if (res && res.success) {
        setUsers((prev) => prev.map(u => u.id === user.id ? { ...u, isEnabled: !u.isEnabled } : u));
        toast.showToast(res.message || 'Status geändert', 'success');
      } else {
        toast.showToast(res?.message || 'Fehler beim Ändern des Status', 'error');
      }
    } catch (e: any) {
      toast.showToast(e?.message || 'Fehler beim Ändern des Status', 'error');
    }
  };

  const handleResendVerification = async (user: any) => {
    try {
      const res = await apiJson(`/api/resend-verification/${user.id}`, {
        method: 'POST',
      });
      if (res && res.success) {
        setResendVerificationModal(null);
        toast.showToast(res.message || 'Verifizierungslink wurde erfolgreich gesendet', 'success');
      } else {
        toast.showToast(res?.message || 'Fehler beim Senden des Verifizierungslinks', 'error');
      }
    } catch (e: any) {
      toast.showToast(e?.message || 'Fehler beim Senden des Verifizierungslinks', 'error');
    }
  };

  useEffect(() => {
    setLoading(true);
    apiJson('/admin/users')
      .then((data) => {
        setUsers(data.users || data);
        setLoading(false);
      })
      .catch((e) => {
        setError(e.message || 'Fehler beim Laden der Benutzer');
        setLoading(false);
      });
  }, []);

  if (loading) {
    return <Box sx={{ display: 'flex', justifyContent: 'center', py: 6 }}><CircularProgress /></Box>;
  }
  if (error) {
    return <Box sx={{ color: 'error.main', p: 4 }}>{error}</Box>;
  }

  return (
    <Box sx={{ p: { xs: 1, sm: 3 } }}>
      <Typography variant="h4" gutterBottom>Benutzer-Zuordnungen</Typography>
      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>Name</TableCell>
              <TableCell>E-Mail</TableCell>
              <TableCell>Status</TableCell>
              <TableCell>Zuordnungen</TableCell>
              <TableCell>Aktionen</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {users.map((user) => (
              <TableRow key={user.id}>
                <TableCell>{user.fullName}</TableCell>
                <TableCell>{user.email}</TableCell>
                <TableCell>
                  {user.isVerified ? <Chip label="Verifiziert" color="success" size="small" sx={{ mr: 1, cursor: 'pointer' }} onClick={() => setResendVerificationModal({ open: true, user })} /> : <Chip label="Nicht verifiziert" color="warning" size="small" sx={{ mr: 1, cursor: 'pointer' }} onClick={() => setResendVerificationModal({ open: true, user })} />}
                  {user.isEnabled ? <Chip label="Aktiv" color="success" size="small" /> : <Chip label="Deaktiviert" color="error" size="small" />}
                </TableCell>
                <TableCell>
                  {user.userRelations && user.userRelations.length > 0 ? (
                    <ul style={{ margin: 0, paddingLeft: 16 }}>
                      {user.userRelations.map((relation: any, idx: number) => (
                        <li key={idx}>
                          {relation.relationType?.name} von {relation.entity}

                          {/*
                          {relation.permissions && relation.permissions.length > 0 && (
                            <>
                              {' '}
                              <Stack direction="row" spacing={0.5} component="span" display="inline-flex">
                                {relation.permissions.map((perm: string) => (
                                  <Chip key={perm} label={perm} size="small" variant="outlined" />
                                ))}
                              </Stack>
                            </>
                          )}
                          */}
                          {/*
                          <IconButton size="small" onClick={() => setEditModal({ open: true, user })}>
                            <EditIcon fontSize="small" />
                          </IconButton>
                          <IconButton size="small" color="error" onClick={() => setDeleteModal({ open: true, user, relation })}>
                            <DeleteIcon fontSize="small" />
                          </IconButton>
                          */}
                        </li>
                      ))}
                    </ul>
                  ) : (
                    <span style={{ color: '#888' }}>Keine</span>
                  )}
                </TableCell>
                <TableCell>
                  <Stack direction="row" spacing={1}>
                    <Button
                      variant="contained"
                      size="small"
                      onClick={() => setEditModal({ open: true, user })}
                    >
                      Zuordnen
                    </Button>
                    <Button
                      variant={user.isEnabled ? 'outlined' : 'contained'}
                      color={user.isEnabled ? 'error' : 'success'}
                      size="small"
                      onClick={() => handleToggleStatus(user)}
                    >
                      {user.isEnabled ? 'Deaktivieren' : 'Aktivieren'}
                    </Button>
                    <Button
                      variant="outlined"
                      size="small"
                      onClick={() => setRolesModal({ open: true, user })}
                    >
                      Rollen bearbeiten
                    </Button>
                    <Button
                      variant="outlined"
                      color="error"
                      size="small"
                      onClick={() => setDeleteUserModal({ open: true, user })}
                      startIcon={<DeleteIcon />}
                    >
                      Löschen
                    </Button>
                    {/* Edit User Roles Modal */}
                    <EditUserRolesModal
                      open={!!rolesModal?.open}
                      onClose={() => setRolesModal(null)}
                      user={rolesModal?.user}
                      onSave={async (roles) => {
                        if (!rolesModal?.user) return;
                        try {
                          const res = await apiJson(`/admin/users/${rolesModal.user.id}/roles`, {
                            method: 'POST',
                            body: { roles },
                            headers: { 'Content-Type': 'application/json' },
                          });
                          if (res && res.success) {
                            // Nach dem Speichern: User-Liste neu laden
                            setLoading(true);
                            const data = await apiJson('/admin/users');
                            setUsers(data.users || data);
                            setLoading(false);
                            setRolesModal(null);
                            toast.showToast(res.message || 'Rollen gespeichert', 'success');
                          } else {
                            toast.showToast(res?.message || res?.error || 'Fehler beim Speichern der Rollen', 'error');
                          }
                        } catch (e: any) {
                          toast.showToast(e?.message || 'Fehler beim Speichern der Rollen', 'error');
                        }
                      }}
                    />
                  </Stack>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>

      {/* Edit Modal */}
      <UserRelationEditModal
        open={!!editModal?.open}
        onClose={() => setEditModal(null)}
        user={editModal?.user}
      />

      {/* Delete Modal */}
      <UserRelationDeleteModal
        open={!!deleteModal?.open}
        onClose={() => setDeleteModal(null)}
        onConfirm={() => { setDeleteModal(null); }}
        userRelation={deleteModal?.relation}
      />

      {/* Delete User Modal */}
      <DeleteUserModal
        open={!!deleteUserModal?.open}
        onClose={() => setDeleteUserModal(null)}
        onConfirm={() => {
          if (deleteUserModal?.user) {
            handleDeleteUser(deleteUserModal.user);
          }
        }}
        user={deleteUserModal?.user}
      />

      {/* Resend Verification Modal */}
      <ResendVerificationModal
        open={!!resendVerificationModal?.open}
        onClose={() => setResendVerificationModal(null)}
        onConfirm={() => {
          if (resendVerificationModal?.user) {
            handleResendVerification(resendVerificationModal.user);
          }
        }}
        userName={resendVerificationModal?.user?.fullName}
      />
    </Box>
  );
};

export default UserRelations;
