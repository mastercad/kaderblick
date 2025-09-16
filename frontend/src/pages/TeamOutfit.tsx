import React, { useEffect, useState } from "react";
import { apiJson } from '../utils/api';
import {
  Box,
  Typography,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Alert,
  CircularProgress,
  Divider,
  useTheme,
  useMediaQuery,
  Stack,
  Card,
  CardContent,
  Avatar
} from '@mui/material';
import CheckroomIcon from '@mui/icons-material/Checkroom';
import TShirtIcon from '@mui/icons-material/Checkroom';
import DirectionsRunIcon from '@mui/icons-material/DirectionsRun';

interface Player {
  id: number;
  name: string;
  shorts_size: string | null;
  shirt_size: string | null;
  shoe_size: string | null;
}

interface Team {
  team_id: number;
  team_name: string;
  players: Player[];
}

interface SizeSummary {
  [size: string]: number;
}

const aggregateSizes = (teams: Team[], key: keyof Player): SizeSummary => {
  const summary: SizeSummary = {};
  teams.forEach((team) => {
    team.players.forEach((player) => {
      const size = player[key];
      if (size) {
        summary[size] = (summary[size] || 0) + 1;
      }
    });
  });
  return summary;
};

const TeamOutfit: React.FC = () => {
  const [teams, setTeams] = useState<Team[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const shortsSummary = aggregateSizes(teams, "shorts_size");
  const shirtSummary = aggregateSizes(teams, "shirt_size");
  const shoeSummary = aggregateSizes(teams, "shoe_size");

  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));

  useEffect(() => {
    apiJson("/api/teams/outfit-overview")
      .then(setTeams)
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return (
    <Box display="flex" justifyContent="center" p={4}>
      <CircularProgress />
    </Box>
  );
  if (error) return (
    <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>
  );

  return (
    <Box sx={{ width: '100%', px: { xs: 2, md: 4 }, py: { xs: 2, md: 4 } }}>
      <Typography variant="h4" component="h1" mb={3}>Outfit-Übersicht für Trainer</Typography>
      {teams.map((team) => {
        // Team-spezifische Zusammenfassungen
        const shortsSummary = team.players.reduce((acc: SizeSummary, p) => {
          if (p.shorts_size) acc[p.shorts_size] = (acc[p.shorts_size] || 0) + 1;
          return acc;
        }, {} as SizeSummary);
        const shirtSummary = team.players.reduce((acc: SizeSummary, p) => {
          if (p.shirt_size) acc[p.shirt_size] = (acc[p.shirt_size] || 0) + 1;
          return acc;
        }, {} as SizeSummary);
        const shoeSummary = team.players.reduce((acc: SizeSummary, p) => {
          if (p.shoe_size) acc[p.shoe_size] = (acc[p.shoe_size] || 0) + 1;
          return acc;
        }, {} as SizeSummary);
        return (
          <Paper key={team.team_id} sx={{ mb: 4, p: 2 }} elevation={2}>
            <Typography variant="h6" component="h2" mb={2}>{team.team_name}</Typography>
            <TableContainer>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Name</TableCell>
                    <TableCell>Hosen-Größe</TableCell>
                    <TableCell>T-Shirt-Größe</TableCell>
                    <TableCell>Schuhgröße</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {team.players.map((player) => (
                    <TableRow key={player.id}>
                      <TableCell>{player.name}</TableCell>
                      <TableCell>{player.shorts_size || "-"}</TableCell>
                      <TableCell>{player.shirt_size || "-"}</TableCell>
                      <TableCell>{player.shoe_size || "-"}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
            <Divider sx={{ my: 2 }} />
            <Stack direction={isMobile ? 'column' : 'row'} spacing={2} useFlexGap flexWrap="wrap">
              <Card variant="outlined" sx={{ minWidth: 180, flex: 1, bgcolor: 'background.paper', boxShadow: 0 }}>
                <CardContent sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                  <Avatar sx={{ bgcolor: theme.palette.primary.light }}>
                    <CheckroomIcon />
                  </Avatar>
                  <Box>
                    <Typography variant="subtitle1" mb={0.5}>Hosen-Größen</Typography>
                    <Box component="ul" sx={{ m: 0, pl: 2 }}>
                      {Object.entries(shortsSummary).map(([size, count]) => (
                        <li key={size}><Typography variant="body2">{size}: {count}</Typography></li>
                      ))}
                    </Box>
                  </Box>
                </CardContent>
              </Card>
              <Card variant="outlined" sx={{ minWidth: 180, flex: 1, bgcolor: 'background.paper', boxShadow: 0 }}>
                <CardContent sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                  <Avatar sx={{ bgcolor: theme.palette.secondary.light }}>
                    <TShirtIcon />
                  </Avatar>
                  <Box>
                    <Typography variant="subtitle1" mb={0.5}>T-Shirt-Größen</Typography>
                    <Box component="ul" sx={{ m: 0, pl: 2 }}>
                      {Object.entries(shirtSummary).map(([size, count]) => (
                        <li key={size}><Typography variant="body2">{size}: {count}</Typography></li>
                      ))}
                    </Box>
                  </Box>
                </CardContent>
              </Card>
              <Card variant="outlined" sx={{ minWidth: 180, flex: 1, bgcolor: 'background.paper', boxShadow: 0 }}>
                <CardContent sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                  <Avatar sx={{ bgcolor: theme.palette.info.light }}>
                    <DirectionsRunIcon />
                  </Avatar>
                  <Box>
                    <Typography variant="subtitle1" mb={0.5}>Schuhgrößen</Typography>
                    <Box component="ul" sx={{ m: 0, pl: 2 }}>
                      {Object.entries(shoeSummary).map(([size, count]) => (
                        <li key={size}><Typography variant="body2">{size}: {count}</Typography></li>
                      ))}
                    </Box>
                  </Box>
                </CardContent>
              </Card>
            </Stack>
          </Paper>
        );
      })}
    </Box>
  );
};

export default TeamOutfit;
