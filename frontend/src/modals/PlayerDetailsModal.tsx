import React, { useEffect, useState } from 'react';
import {
    Dialog, DialogContent, DialogActions, Button, Box, Typography, CircularProgress, IconButton, Avatar, Chip, Divider, Stack, Tooltip
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import SportsSoccerIcon from '@mui/icons-material/SportsSoccer';
import { apiJson } from '../utils/api';
import { Player } from '../types/player';
import PlayerDeleteConfirmationModal from './PlayerDeleteConfirmationModal';
import PlayerEditModal from './PlayerEditModal';

interface PlayerDetailsResponse {
    player: Player;
    permissions: {
        canEdit: boolean;
        canView: boolean;
        canDelete: boolean;
    };
}

interface PlayerDetailsModalProps {
  open: boolean;
  playerId: number | null;
  onClose: () => void;
  loadPlayeres: () => void;
}


const PlayerDetailsModal: React.FC<PlayerDetailsModalProps> = ({ open, playerId, onClose, loadPlayeres }) => {
    const [player, setPlayer] = useState<Player | null>(null);
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [deletePlayer, setDeletePlayer] = useState<Player | null>(null);
    const [playerEditModalOpen, setPlayerEditModalOpen] = useState(false);

    useEffect(() => {
        if (open) {
            apiJson<PlayerDetailsResponse>(`/api/playeres/${playerId}`)
                .then(data => {
                    setPlayer(data.player);
                })
                .catch(() => setPlayer(null));
        }
    }, [open, playerId]);

    // Hilfsfunktionen für die Anzeige
    const getTeams = () => player?.teamAssignments?.map(a => a.team) || [];
    const getLicenses = () => player?.licenseAssignments?.map(a => a.license) || [];
    const getNationalities = () => player?.nationalityAssignments?.map(a => a.nationality) || [];

    return (
        <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
            {player && player.permissions?.canView ? (
                <>
                    <Box display="flex" alignItems="center" justifyContent="space-between" px={3} pt={3} pb={1}>
                        <Box display="flex" alignItems="center">
                            {player.profilePicturePath ? (
                                <Avatar src={player.profilePicturePath} alt={`${player.firstName} ${player.lastName}`} sx={{ width: 48, height: 48, mr: 2, bgcolor: 'white', border: '1px solid #eee' }} />
                            ) : (
                                <Avatar sx={{ width: 48, height: 48, mr: 2, bgcolor: 'grey.100', color: 'text.secondary' }}>
                                    <SportsSoccerIcon />
                                </Avatar>
                            )}
                            <Box>
                                <Typography variant="h5">{player.firstName} {player.lastName}</Typography>
                                <Typography variant="body2" color="text.secondary">{player.email}</Typography>
                                <Typography variant="body2" color="text.secondary">Geburtsdatum: {player.birthDate}</Typography>
                            </Box>
                        </Box>
                        <IconButton aria-label="close" onClick={onClose} size="small" sx={{ ml: 2 }}>
                            <CloseIcon />
                        </IconButton>
                    </Box>
                    <DialogContent>
                        <Stack spacing={3}>
                            {/* Teams */}
                            <Box>
                                <Typography variant="subtitle1" fontWeight={600} gutterBottom>Teams</Typography>
                                <Stack direction="row" spacing={1} flexWrap="wrap">
                                    {getTeams().length > 0 ? getTeams().map(team => (
                                        <Tooltip key={team.id} title={`Altersklasse: ${team.ageGroup?.name}, Liga: ${team.league?.name}`} arrow>
                                            <Chip label={team.name} color="primary" variant="outlined" sx={{ mb: 1 }} />
                                        </Tooltip>
                                    )) : <Typography variant="body2" color="text.secondary">Keine Teams zugewiesen</Typography>}
                                </Stack>
                            </Box>
                            {/* Lizenzen */}
                            <Box>
                                <Typography variant="subtitle1" fontWeight={600} gutterBottom>Lizenzen</Typography>
                                <Stack direction="row" spacing={1} flexWrap="wrap">
                                    {getLicenses().length > 0 ? getLicenses().map((license, idx) => (
                                        <Chip key={license.name + idx} label={license.name} color="success" variant="outlined" sx={{ mb: 1 }} />
                                    )) : <Typography variant="body2" color="text.secondary">Keine Lizenzen</Typography>}
                                </Stack>
                            </Box>
                            {/* Nationalitäten */}
                            <Box>
                                <Typography variant="subtitle1" fontWeight={600} gutterBottom>Nationalitäten</Typography>
                                <Stack direction="row" spacing={1} flexWrap="wrap">
                                    {getNationalities().length > 0 ? getNationalities().map((nat, idx) => (
                                        <Chip key={nat.id + idx} label={nat.name} color="info" variant="outlined" sx={{ mb: 1 }} />
                                    )) : <Typography variant="body2" color="text.secondary">Keine Nationalitäten</Typography>}
                                </Stack>
                            </Box>
                        </Stack>
                    </DialogContent>
                    <DialogActions sx={{ justifyContent: 'flex-end', px: 3, pb: 2 }}>
                        {player.permissions?.canEdit && (
                            <Button variant="contained" color="warning" startIcon={<EditIcon />} size="small" onClick={() => setPlayerEditModalOpen(true)} sx={{ ml: 1 }}>
                                Bearbeiten
                            </Button>
                        )}
                        {player.permissions?.canDelete && (
                            <Button variant="contained" color="error" startIcon={<DeleteIcon />} onClick={() => { setDeletePlayer(player); setDeleteModalOpen(true); }} sx={{ ml: 1 }}>
                                Löschen
                            </Button>
                        )}
                    </DialogActions>
                </>
            ) : (
                <Box display="flex" alignItems="center" justifyContent="center" minHeight={200}>
                    <CircularProgress />
                </Box>
            )}
            <PlayerDeleteConfirmationModal
                open={deleteModalOpen}
                playerName={deletePlayer?.firstName + ' ' + deletePlayer?.lastName}
                onClose={() => setDeleteModalOpen(false)}
                onConfirm={async () => {
                    if (!deletePlayer) return;
                    try {
                        await apiJson(`/playeres/${deletePlayer.id}/delete`, { method: 'DELETE' });
                    } catch (e) {
                        // Fehlerbehandlung ggf. Toast
                    } finally {
                        setDeleteModalOpen(false);
                        setPlayer(null);
                        setDeletePlayer(null);
                        onClose();
                    }
                }}
            />
            <PlayerEditModal
                openPlayerEditModal={playerEditModalOpen}
                playerId={playerId}
                onPlayerEditModalClose={() => setPlayerEditModalOpen(false)}
                onPlayerSaved={() => {
                    setPlayerEditModalOpen(false);
                    loadPlayeres();
                }}
            />
        </Dialog>
    );
};

export default PlayerDetailsModal;
