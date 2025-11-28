import React from 'react';
import { Box, Typography, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper, CircularProgress, Alert } from '@mui/material';
import { apiJson } from '../../utils/api';

const AdminTitleXpOverview: React.FC = () => {
  const [loading, setLoading] = React.useState(true);
  const [error, setError] = React.useState<string | null>(null);
  const [titles, setTitles] = React.useState<any[]>([]);
  const [users, setUsers] = React.useState<any[]>([]);

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
                  <TableCell>Anzahl Nutzer</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {titles.map((t, idx) => (
                  <TableRow key={idx}>
                    <TableCell>{t.titleCategory}</TableCell>
                    <TableCell>{t.titleScope}</TableCell>
                    <TableCell>{t.titleRank}</TableCell>
                    <TableCell>{t.userCount}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>

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
