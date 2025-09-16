import React, { useEffect, useState } from 'react';
import { Box, Tabs, Tab, Badge, Typography, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper, Button, Dialog, DialogTitle, DialogContent, DialogActions, TextField, Chip, CircularProgress, IconButton, Tooltip } from '@mui/material';
import AttachFileIcon from '@mui/icons-material/AttachFile';
import { apiJson } from '../utils/api';

interface FeedbackItem {
  id: number;
  createdAt: string;
  userName: string;
  userId: number;
  type: string;
  message: string;
  isRead: boolean;
  isResolved: boolean;
  adminNote?: string;
  screenshotPath?: string;
}

interface FeedbackStats {
  [type: string]: number;
}

const FeedbackAdmin: React.FC = () => {
  const [tab, setTab] = useState(0);
  const [unresolved, setUnresolved] = useState<FeedbackItem[]>([]);
  const [read, setRead] = useState<FeedbackItem[]>([]);
  const [resolved, setResolved] = useState<FeedbackItem[]>([]);
  const [stats, setStats] = useState<FeedbackStats>({});
  const [loading, setLoading] = useState(true);
  const [resolveOpen, setResolveOpen] = useState<number|null>(null);
  const [resolveNote, setResolveNote] = useState('');
  const [resolving, setResolving] = useState(false);
  const [screenshotModal, setScreenshotModal] = useState<{ open: boolean, path?: string }>({ open: false });

  const fetchData = async () => {
    setLoading(true);
    const data = await apiJson('/admin/feedback', { method: 'GET' });
    setUnresolved(data.unresolved || []);
    setRead(data.read || []);
    setResolved(data.resolved || []);
    setStats(data.statistics || {});
    setLoading(false);
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleMarkRead = async (id: number) => {
    await apiJson(`/admin/feedback/${id}/mark-read`, { method: 'POST' });
    fetchData();
  };

  const handleResolve = async (id: number) => {
    setResolving(true);
    await apiJson(`/admin/feedback/${id}/resolve`, {
      method: 'POST',
      body: { adminNote: resolveNote },
    });
    setResolving(false);
    setResolveOpen(null);
    setResolveNote('');
    fetchData();
  };

  const renderStatus = (item: FeedbackItem) => {
    if (item.isResolved) return <Chip label="Erledigt" color="success" size="small" />;
    if (item.isRead) return <Chip label="Gelesen" color="warning" size="small" />;
    return <Chip label="Neu" color="error" size="small" />;
  };

  const renderType = (type: string) => {
    if (type === 'bug') return <Chip label="Bug" color="error" size="small" />;
    if (type === 'feature') return <Chip label="Feature" color="primary" size="small" />;
    return <Chip label={type} color="info" size="small" />;
  };

  const renderTable = (items: FeedbackItem[], showActions: boolean) => (
    <TableContainer component={Paper} sx={{ mb: 2 }}>
      <Table size="small">
        <TableHead>
          <TableRow>
            <TableCell>Datum</TableCell>
            <TableCell>Benutzer</TableCell>
            <TableCell>Typ</TableCell>
            <TableCell>Nachricht</TableCell>
            <TableCell>Status</TableCell>
            <TableCell>Aktionen</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {items.map(item => (
            <TableRow key={item.id}>
              <TableCell>{new Date(item.createdAt).toLocaleString('de-DE')}</TableCell>
              <TableCell>{item.userName}</TableCell>
              <TableCell>{renderType(item.type)}</TableCell>
              <TableCell>
                <span style={{ whiteSpace: 'pre-line' }}>{item.message}</span>
                {item.screenshotPath && (
                  <Tooltip title="Anhang anzeigen">
                    <IconButton size="small" sx={{ ml: 1 }} onClick={() => setScreenshotModal({ open: true, path: item.screenshotPath })}>
                      <AttachFileIcon fontSize="small" />
                    </IconButton>
                  </Tooltip>
                )}
              </TableCell>
              <TableCell>{renderStatus(item)}</TableCell>
              <TableCell>
                {!item.isRead && (
                  <Button size="small" color="info" variant="outlined" onClick={() => handleMarkRead(item.id)}>
                    Als gelesen markieren
                  </Button>
                )}
                {!item.isResolved && (
                  <Button size="small" color="success" variant="contained" sx={{ ml: 1 }} onClick={() => { setResolveOpen(item.id); setResolveNote(''); }}>
                    Erledigen
                  </Button>
                )}
                <Dialog open={resolveOpen === item.id} onClose={() => setResolveOpen(null)}>
                  <DialogTitle>Feedback erledigen</DialogTitle>
                  <DialogContent>
                    <Typography variant="subtitle2" gutterBottom>Feedback:</Typography>
                    <Typography variant="body2" color="text.secondary" gutterBottom>{item.message}</Typography>
                    {item.screenshotPath && (
                      <Box sx={{ my: 2, textAlign: 'center' }}>
                        <img src={item.screenshotPath} alt="Screenshot" style={{ maxWidth: 320, maxHeight: 240, borderRadius: 8, boxShadow: '0 2px 8px #0002' }} />
                        <Typography variant="caption" color="text.secondary">Screenshot</Typography>
                      </Box>
                    )}
                    <TextField
                      label="Notiz/Kommentar"
                      multiline
                      minRows={2}
                      fullWidth
                      value={resolveNote}
                      onChange={e => setResolveNote(e.target.value)}
                      sx={{ mt: 2 }}
                    />
                  </DialogContent>
                  <DialogActions>
                    <Button onClick={() => setResolveOpen(null)} color="secondary">Abbrechen</Button>
                    <Button onClick={() => handleResolve(item.id)} color="success" variant="contained" disabled={resolving}>
                      Als erledigt markieren
                    </Button>
                  </DialogActions>
                </Dialog>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      {items.length === 0 && (
        <Box p={2}>
          <Typography color="text.secondary">Keine Einträge vorhanden.</Typography>
        </Box>
      )}
    </TableContainer>
  );

  if (loading) return <Box display="flex" justifyContent="center" alignItems="center" minHeight={300}><CircularProgress /></Box>;

  return (
    <>
      <Box sx={{ p: { xs: 1, md: 3 } }}>
        <Typography variant="h4" gutterBottom>Feedback-Übersicht</Typography>
        <Tabs value={tab} onChange={(_, v) => setTab(v)} sx={{ mb: 2 }}>
          <Tab label={<span>Neues Feedback {unresolved.length > 0 && <Badge color="error" badgeContent={unresolved.length} sx={{ ml: 1 }} />}</span>} />
          <Tab label={<span>In Bearbeitung {read.length > 0 && <Badge color="warning" badgeContent={read.length} sx={{ ml: 1 }} />}</span>} />
          <Tab label={<span>Erledigt {resolved.length > 0 && <Badge color="success" badgeContent={resolved.length} sx={{ ml: 1 }} />}</span>} />
        </Tabs>
        <Box hidden={tab !== 0}>{renderTable(unresolved, true)}</Box>
        <Box hidden={tab !== 1}>{renderTable(read, true)}</Box>
        <Box hidden={tab !== 2}>{renderTable(resolved, false)}</Box>
      </Box>
      <Dialog open={screenshotModal.open} onClose={() => setScreenshotModal({ open: false })} maxWidth="md">
        <DialogTitle>Anhang</DialogTitle>
        <DialogContent>
          {screenshotModal.path && (
            <Box sx={{ textAlign: 'center' }}>
              <img src={screenshotModal.path} alt="Screenshot" style={{ maxWidth: '100%', maxHeight: 500, borderRadius: 8, boxShadow: '0 2px 8px #0002' }} />
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setScreenshotModal({ open: false })}>Schließen</Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default FeedbackAdmin;
