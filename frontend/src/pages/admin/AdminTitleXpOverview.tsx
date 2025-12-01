import React from 'react';
import { Box, Typography, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper, CircularProgress, Alert, Dialog, DialogTitle, DialogContent, IconButton, List, ListItem, ListItemText } from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import { apiJson } from '../../utils/api';

const AdminTitleXpOverview: React.FC = () => {
  const [loading, setLoading] = React.useState(true);
  const [error, setError] = React.useState<string | null>(null);
  const [titles, setTitles] = React.useState<any[]>([]);
  const [users, setUsers] = React.useState<any[]>([]);
  const [modalOpen, setModalOpen] = React.useState(false);
  const [modalTitle, setModalTitle] = React.useState<any | null>(null);
  const [modalUsers, setModalUsers] = React.useState<any[]>([]);

  React.useEffect(() => {
    setLoading(true);
    apiJson('/api/admin/title-xp-overview')
      .then((data) => {
        setTitles(data.titles || []);
        setUsers(data.users || []);
        setError(null);
      })
      .catch(() => setError('Fehler beim Laden der Übersicht'))
      .finally(() => setLoading(false));
  }, []);


  // Zeige Spieler aus title.players im Modal an
  const handleUserCountClick = (title: any) => {
    setModalTitle(title);
    setModalUsers(title.players || []);
    setModalOpen(true);
  };

  const handleCloseModal = () => {
    setModalOpen(false);
    setModalTitle(null);
    setModalUsers([]);
  };

  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h4" gutterBottom>Titel & XP Übersicht</Typography>
      {loading ? (
        <Box sx={{ display: 'flex', justifyContent: 'center', my: 4 }}><CircularProgress /></Box>
      ) : error ? (
        <Alert severity="error">{error}</Alert>
      ) : (
        <>
          <Typography variant="h6" sx={{ mt: 2, mb: 1 }}>Vergebene Titel</Typography>
          <TableContainer component={Paper} sx={{ mb: 4 }}>
            <Table size="small">
              <TableHead>
                <TableRow>
                  <TableCell>Kategorie</TableCell>
                  <TableCell>Scope</TableCell>
                  <TableCell>Rang</TableCell>
                  <TableCell>Name</TableCell>
                  <TableCell>Anzahl Nutzer</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {[...titles]
                  .sort((a, b) => {
                    // Scope-Sortierung: platform < league < team
                    type Scope = 'platform' | 'league' | 'team';
                    type Rank = 'gold' | 'silver' | 'bronze';
                    const scopeOrder: Record<Scope, number> = { platform: 0, league: 1, team: 2 };
                    const rankOrder: Record<Rank, number> = { gold: 0, silver: 1, bronze: 2 };
                    const scopeA = scopeOrder[(a.titleScope as Scope)] ?? 99;
                    const scopeB = scopeOrder[(b.titleScope as Scope)] ?? 99;
                    if (scopeA !== scopeB) return scopeA - scopeB;
                    // Innerhalb Scope nach Rank
                    const rankA = rankOrder[(a.titleRank as Rank)] ?? 99;
                    const rankB = rankOrder[(b.titleRank as Rank)] ?? 99;
                    if (rankA !== rankB) return rankA - rankB;
                    // Innerhalb league/team nach Name
                    if (a.titleScope === 'league') {
                      return (a.leagueName || '').localeCompare(b.leagueName || '');
                    }
                    if (a.titleScope === 'team') {
                      return (a.teamName || '').localeCompare(b.teamName || '');
                    }
                    return 0;
                  })
                  .map((t, idx) => {
                    let name = '-';
                    if (t.titleScope === 'team') {
                      name = t.teamName || '-';
                    } else if (t.titleScope === 'league') {
                      name = t.leagueName || '-';
                    } else if (t.titleScope === 'platform') {
                      name = 'Plattform';
                    }
                    return (
                      <TableRow key={idx}>
                        <TableCell>{t.titleCategory}</TableCell>
                        <TableCell>{t.titleScope}</TableCell>
                        <TableCell>{t.titleRank}</TableCell>
                        <TableCell>{name}</TableCell>
                        <TableCell>
                          <span
                            style={{ color: '#1976d2', cursor: 'pointer', textDecoration: 'underline' }}
                            onClick={() => handleUserCountClick(t)}
                            title="Nutzer anzeigen"
                          >
                            {t.userCount}
                          </span>
                        </TableCell>
                      </TableRow>
                    );
                  })}
              </TableBody>
            </Table>
          </TableContainer>

          <Dialog open={modalOpen} onClose={handleCloseModal} maxWidth="xs" fullWidth>
            <DialogTitle>
              Nutzer mit Titel
              <IconButton
                aria-label="close"
                onClick={handleCloseModal}
                sx={{ position: 'absolute', right: 8, top: 8 }}
                size="large"
              >
                <CloseIcon />
              </IconButton>
            </DialogTitle>
            <DialogContent>
              {modalUsers.length === 0 ? (
                <Typography variant="body2">Keine Nutzer mit diesem Titel gefunden.</Typography>
              ) : (
                <List>
                  {modalUsers.map((u) => (
                    <ListItem key={u.id}>
                      <ListItemText
                        primary={`${u.firstName || ''} ${u.lastName || ''}`.trim() || u.email}
                        secondary={u.email}
                      />
                    </ListItem>
                  ))}
                </List>
              )}
            </DialogContent>
          </Dialog>

          <Typography variant="h6" sx={{ mt: 2, mb: 1 }}>Benutzer – Level & XP</Typography>
          <TableContainer component={Paper}>
            <Table size="small">
              <TableHead>
                <TableRow>
                  <TableCell>Name</TableCell>
                  <TableCell>E-Mail</TableCell>
                  <TableCell>Titel</TableCell>
                  <TableCell>Level</TableCell>
                  <TableCell>XP</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {users.map((u) => (
                  <TableRow key={u.id}>
                    <TableCell>{u.firstName} {u.lastName}</TableCell>
                    <TableCell>{u.email}</TableCell>
                    <TableCell>{u.title || '-'}</TableCell>
                    <TableCell>{u.level ?? '-'}</TableCell>
                    <TableCell>{u.xp}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        </>
      )}
    </Box>
  );
};

export default AdminTitleXpOverview;
