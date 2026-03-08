import React, { useEffect, useState } from 'react';
import {
  Box,
  Typography,
  Chip,
  Stack,
  Tooltip,
  IconButton,
  CircularProgress,
} from '@mui/material';
import ManageAccountsIcon from '@mui/icons-material/ManageAccounts';
import LinkIcon             from '@mui/icons-material/Link';
import PowerSettingsNewIcon from '@mui/icons-material/PowerSettingsNew';
import AdminPanelSettingsIcon from '@mui/icons-material/AdminPanelSettings';
import MarkEmailUnreadIcon  from '@mui/icons-material/MarkEmailUnread';
import DeleteIcon           from '@mui/icons-material/Delete';
import { apiJson } from '../../utils/api';
import { AdminEmptyState, AdminTable, AdminTableColumn } from '../../components/AdminPageLayout';
import UserRelationEditModal   from '../../modals/UserRelationEditModal';
import UserRelationDeleteModal from '../../modals/UserRelationDeleteModal';
import EditUserRolesModal      from '../../modals/EditUserRolesModal';
import DeleteUserModal         from '../../modals/DeleteUserModal';
import ResendVerificationModal from '../../modals/ResendVerificationModal';
import { useToast }            from '../../context/ToastContext';
import { UserRow }             from './types';
import { VerifiedChip }        from './UserStatusChips';

const UsersTab: React.FC = () => {
  const [users, setUsers]             = useState<UserRow[]>([]);
  const [usersLoading, setUsersLoading] = useState(true);
  const [usersError, setUsersError]   = useState<string | null>(null);

  const [editModal, setEditModal]                     = useState<{ open: boolean; user?: UserRow } | null>(null);
  const [deleteModal, setDeleteModal]                 = useState<{ open: boolean; user?: UserRow; relation?: any } | null>(null);
  const [rolesModal, setRolesModal]                   = useState<{ open: boolean; user?: UserRow } | null>(null);
  const [deleteUserModal, setDeleteUserModal]         = useState<{ open: boolean; user?: UserRow } | null>(null);
  const [resendVerificationModal, setResendVerificationModal] = useState<{ open: boolean; user?: UserRow } | null>(null);

  const toast = useToast();

  // ── Data loader ──

  const loadUsers = () => {
    setUsersLoading(true);
    apiJson('/admin/users')
      .then((data: any) => { setUsers(data.users || data); })
      .catch((e: any)   => { setUsersError(e.message || 'Fehler beim Laden der Benutzer'); })
      .finally(() => setUsersLoading(false));
  };

  useEffect(() => { loadUsers(); }, []);

  // ── Handlers ──

  const handleDeleteUser = async (user: UserRow) => {
    try {
      const res = await apiJson(`/admin/users/${user.id}`, { method: 'DELETE' });
      if (res?.success) {
        setUsers(prev => prev.filter(u => u.id !== user.id));
        setDeleteUserModal(null);
        toast.showToast(res.message || 'Benutzer wurde erfolgreich gelöscht', 'success');
      } else {
        toast.showToast(res?.message || 'Fehler beim Löschen des Benutzers', 'error');
      }
    } catch (e: any) {
      toast.showToast(e?.message || 'Fehler beim Löschen des Benutzers', 'error');
    }
  };

  const handleToggleStatus = async (user: UserRow) => {
    try {
      const res = await apiJson(`/admin/users/${user.id}/toggle-status`);
      if (res?.success) {
        setUsers(prev => prev.map(u => u.id === user.id ? { ...u, isEnabled: !u.isEnabled } : u));
        toast.showToast(res.message || 'Status geändert', 'success');
      } else {
        toast.showToast(res?.message || 'Fehler beim Ändern des Status', 'error');
      }
    } catch (e: any) {
      toast.showToast(e?.message || 'Fehler beim Ändern des Status', 'error');
    }
  };

  const handleResendVerification = async (user: UserRow) => {
    try {
      const res = await apiJson(`/api/resend-verification/${user.id}`, { method: 'POST' });
      if (res?.success) {
        setResendVerificationModal(null);
        toast.showToast(res.message || 'Verifizierungslink wurde erfolgreich gesendet', 'success');
      } else {
        toast.showToast(res?.message || 'Fehler beim Senden des Verifizierungslinks', 'error');
      }
    } catch (e: any) {
      toast.showToast(e?.message || 'Fehler beim Senden des Verifizierungslinks', 'error');
    }
  };

  // ── Column definitions ──

  const columns: AdminTableColumn<UserRow>[] = [
    {
      header: 'Name',
      width: '18%',
      render: u => <Typography variant="body2" fontWeight={500}>{u.fullName}</Typography>,
    },
    {
      header: 'E-Mail',
      width: '22%',
      render: u => <Typography variant="body2" color="text.secondary">{u.email}</Typography>,
    },
    {
      header: 'Status',
      width: '18%',
      render: u => (
        <Stack direction="row" spacing={0.5} flexWrap="wrap">
          <VerifiedChip user={u} onClick={() => setResendVerificationModal({ open: true, user: u })} />
          {u.isEnabled
            ? <Chip label="Aktiv"       color="success" size="small" />
            : <Chip label="Deaktiviert" color="error"   size="small" />}
        </Stack>
      ),
    },
    {
      header: 'Zuordnungen',
      render: u =>
        u.userRelations?.length > 0 ? (
          <Stack spacing={0.25}>
            {u.userRelations.map((r, i) => (
              <Typography key={i} variant="caption" color="text.secondary">
                {r.relationType?.name} von {r.entity}
              </Typography>
            ))}
          </Stack>
        ) : (
          <Typography variant="caption" color="text.disabled">Keine</Typography>
        ),
    },
  ];

  // ── Render ──

  return (
    <>
      {usersLoading ? (
        <Box sx={{ display: 'flex', justifyContent: 'center', py: 6 }}>
          <CircularProgress />
        </Box>
      ) : usersError ? (
        <Typography color="error" sx={{ p: 2 }}>{usersError}</Typography>
      ) : users.length === 0 ? (
        <AdminEmptyState icon={<ManageAccountsIcon />} title="Keine Benutzer vorhanden" />
      ) : (
        <AdminTable<UserRow>
          columns={columns}
          data={users}
          getKey={u => u.id}
          pagination
          renderActions={u => (
            <Stack direction="row" spacing={0.5} justifyContent="flex-end">
              <Tooltip title="Zuordnung bearbeiten">
                <IconButton size="small" color="primary" onClick={() => setEditModal({ open: true, user: u })}>
                  <LinkIcon fontSize="small" />
                </IconButton>
              </Tooltip>
              <Tooltip title="Rollen bearbeiten">
                <IconButton size="small" color="info" onClick={() => setRolesModal({ open: true, user: u })}>
                  <AdminPanelSettingsIcon fontSize="small" />
                </IconButton>
              </Tooltip>
              <Tooltip title={u.isEnabled ? 'Deaktivieren' : 'Aktivieren'}>
                <IconButton
                  size="small"
                  color={u.isEnabled ? 'warning' : 'success'}
                  onClick={() => handleToggleStatus(u)}
                >
                  <PowerSettingsNewIcon fontSize="small" />
                </IconButton>
              </Tooltip>
              <Tooltip title="Verifikationsmail erneut senden">
                <IconButton size="small" onClick={() => setResendVerificationModal({ open: true, user: u })}>
                  <MarkEmailUnreadIcon fontSize="small" />
                </IconButton>
              </Tooltip>
              <Tooltip title="Benutzer löschen">
                <IconButton size="small" color="error" onClick={() => setDeleteUserModal({ open: true, user: u })}>
                  <DeleteIcon fontSize="small" />
                </IconButton>
              </Tooltip>
            </Stack>
          )}
        />
      )}

      {/* ── Modals ── */}

      <UserRelationEditModal
        open={!!editModal?.open}
        onClose={() => setEditModal(null)}
        user={editModal?.user ?? { id: 0, fullName: '' }}
      />

      <UserRelationDeleteModal
        open={!!deleteModal?.open}
        onClose={() => setDeleteModal(null)}
        onConfirm={() => setDeleteModal(null)}
        userRelation={deleteModal?.relation}
      />

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
            if (res?.success) {
              loadUsers();
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

      <DeleteUserModal
        open={!!deleteUserModal?.open}
        onClose={() => setDeleteUserModal(null)}
        onConfirm={() => { if (deleteUserModal?.user) handleDeleteUser(deleteUserModal.user); }}
        user={deleteUserModal?.user}
      />

      <ResendVerificationModal
        open={!!resendVerificationModal?.open}
        onClose={() => setResendVerificationModal(null)}
        onConfirm={() => { if (resendVerificationModal?.user) handleResendVerification(resendVerificationModal.user); }}
        userName={resendVerificationModal?.user?.fullName}
      />
    </>
  );
};

export default UsersTab;
