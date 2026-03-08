import React, { useEffect, useState } from 'react';
import {
  Box,
  Typography,
  Chip,
  Stack,
  Tooltip,
  IconButton,
  CircularProgress,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
} from '@mui/material';
import CheckIcon   from '@mui/icons-material/Check';
import CloseIcon   from '@mui/icons-material/Close';
import HowToRegIcon from '@mui/icons-material/HowToReg';
import { apiJson } from '../../utils/api';
import { AdminEmptyState, AdminTable, AdminTableColumn } from '../../components/AdminPageLayout';
import { useToast } from '../../context/ToastContext';
import { RegistrationRequestRow, RequestCounts } from './types';
import { StatusChip } from './UserStatusChips';

interface Props {
  onCountsChange: (counts: RequestCounts) => void;
}

const RequestsTab: React.FC<Props> = ({ onCountsChange }) => {
  const [requests, setRequests]             = useState<RegistrationRequestRow[]>([]);
  const [counts, setCounts]                 = useState<RequestCounts>({ pending: 0, approved: 0, rejected: 0 });
  const [requestsLoading, setRequestsLoading] = useState(true);
  const [rejectDialog, setRejectDialog]     = useState<{ open: boolean; request?: RegistrationRequestRow } | null>(null);
  const [rejectReason, setRejectReason]     = useState('');

  const toast = useToast();

  // ── Data loader ──

  const loadRequests = () => {
    setRequestsLoading(true);
    apiJson('/admin/registration-requests?status=all')
      .then((data: any) => {
        const newCounts = data.counts ?? { pending: 0, approved: 0, rejected: 0 };
        setRequests(data.requests || []);
        setCounts(newCounts);
        onCountsChange(newCounts);
      })
      .catch(() => toast.showToast('Fehler beim Laden der Registrierungsanfragen', 'error'))
      .finally(() => setRequestsLoading(false));
  };

  useEffect(() => { loadRequests(); }, []);

  // ── Handlers ──

  const handleApproveRequest = async (req: RegistrationRequestRow) => {
    try {
      const res = await apiJson(`/admin/registration-requests/${req.id}/approve`, { method: 'POST' });
      if (res?.success) {
        toast.showToast('Antrag genehmigt und Benutzerverknüpfung erstellt', 'success');
        loadRequests();
      } else {
        toast.showToast(res?.error || 'Fehler beim Genehmigen', 'error');
      }
    } catch {
      toast.showToast('Fehler beim Genehmigen', 'error');
    }
  };

  const handleRejectRequest = async () => {
    if (!rejectDialog?.request) return;
    try {
      const res = await apiJson(`/admin/registration-requests/${rejectDialog.request.id}/reject`, {
        method: 'POST',
        body: { reason: rejectReason.trim() || null },
      });
      if (res?.success) {
        toast.showToast('Antrag abgelehnt', 'success');
        setRejectDialog(null);
        setRejectReason('');
        loadRequests();
      } else {
        toast.showToast(res?.error || 'Fehler beim Ablehnen', 'error');
      }
    } catch {
      toast.showToast('Fehler beim Ablehnen', 'error');
    }
  };

  // ── Column definitions ──

  const columns: AdminTableColumn<RegistrationRequestRow>[] = [
    {
      header: 'Datum',
      width: 120,
      render: r => <Typography variant="body2" sx={{ whiteSpace: 'nowrap' }}>{r.createdAt}</Typography>,
    },
    {
      header: 'Benutzer',
      width: '22%',
      render: r => (
        <>
          <Typography variant="body2">{r.user.fullName}</Typography>
          <Typography variant="caption" color="text.secondary">{r.user.email}</Typography>
        </>
      ),
    },
    {
      header: 'Bezugsperson',
      width: '18%',
      render: r => (
        <>
          <Typography variant="body2">{r.entityName ?? '–'}</Typography>
          <Typography variant="caption" color="text.secondary">
            {r.entityType === 'player' ? 'Spieler' : r.entityType === 'coach' ? 'Trainer' : ''}
          </Typography>
        </>
      ),
    },
    {
      header: 'Beziehung',
      width: '14%',
      render: r => <Typography variant="body2">{r.relationType?.name ?? '–'}</Typography>,
    },
    {
      header: 'Anmerkung',
      render: r => (
        <Typography variant="body2" sx={{ maxWidth: 180, whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>
          {r.note ?? <span style={{ color: '#bbb' }}>–</span>}
        </Typography>
      ),
    },
    {
      header: 'Status',
      width: 130,
      align: 'center',
      render: r => <StatusChip status={r.status} />,
    },
  ];

  // ── Render ──

  return (
    <>
      <Box>
        <Stack direction="row" spacing={1} sx={{ mb: 2, mt: 1 }}>
          <Chip label={`Ausstehend: ${counts.pending}`}  color="warning" size="small" />
          <Chip label={`Genehmigt: ${counts.approved}`}  color="success" size="small" />
          <Chip label={`Abgelehnt: ${counts.rejected}`}  color="error"   size="small" variant="outlined" />
        </Stack>

        {requestsLoading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', py: 6 }}>
            <CircularProgress />
          </Box>
        ) : requests.length === 0 ? (
          <AdminEmptyState
            icon={<HowToRegIcon />}
            title="Keine Registrierungsanfragen vorhanden"
            description="Neue Anfragen erscheinen hier, sobald sich Nutzer mit einem Vereinsbezug registrieren."
          />
        ) : (
          <AdminTable<RegistrationRequestRow>
            columns={columns}
            data={requests}
            getKey={r => r.id}
            pagination
            renderActions={r =>
              r.status === 'pending' ? (
                <Stack direction="row" spacing={0.5} justifyContent="flex-end">
                  <Tooltip title="Genehmigen">
                    <IconButton size="small" color="success" onClick={() => handleApproveRequest(r)}>
                      <CheckIcon fontSize="small" />
                    </IconButton>
                  </Tooltip>
                  <Tooltip title="Ablehnen">
                    <IconButton
                      size="small"
                      color="error"
                      onClick={() => { setRejectDialog({ open: true, request: r }); setRejectReason(''); }}
                    >
                      <CloseIcon fontSize="small" />
                    </IconButton>
                  </Tooltip>
                </Stack>
              ) : (
                r.processedAt ? (
                  <Typography variant="caption" color="text.secondary" sx={{ whiteSpace: 'nowrap' }}>
                    {r.processedAt}
                    {r.processedBy && <><br />{r.processedBy.name}</>}
                  </Typography>
                ) : null
              )
            }
          />
        )}
      </Box>

      {/* ── Reject reason dialog ── */}
      <Dialog open={!!rejectDialog?.open} onClose={() => setRejectDialog(null)} maxWidth="xs" fullWidth>
        <DialogTitle>Antrag ablehnen</DialogTitle>
        <DialogContent>
          <Typography variant="body2" gutterBottom>
            Antrag von <strong>{rejectDialog?.request?.user?.fullName}</strong> für{' '}
            <strong>{rejectDialog?.request?.entityName}</strong>.
          </Typography>
          <TextField
            label="Grund (optional)"
            multiline
            rows={3}
            fullWidth
            value={rejectReason}
            onChange={(e) => setRejectReason(e.target.value)}
            size="small"
            sx={{ mt: 1 }}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setRejectDialog(null)}>Abbrechen</Button>
          <Button variant="contained" color="error" onClick={handleRejectRequest}>Ablehnen</Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default RequestsTab;
