import React, { useEffect, useState } from 'react';
import {
    Dialog, DialogContent, DialogActions, Button, Box, Typography, CircularProgress, IconButton, Divider, Stack
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { apiJson } from '../utils/api';
import { Team } from '../types/team';
import TeamDeleteConfirmationModal from './TeamDeleteConfirmationModal';
import TeamEditModal from './TeamEditModal';

interface TeamDetailsModalProps {
  teamDetailOpen: boolean;
  teamId: number | null;
  onClose: () => void;
  loadTeams: () => void;
}

const TeamDetailsModal: React.FC<TeamDetailsModalProps> = ({ teamDetailOpen, teamId, onClose, loadTeams }) => {
    const [team, setTeam] = useState<Team | null>(null);
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [deleteTeam, setDeleteTeam] = useState<Team | null>(null);
    const [teamEditModalOpen, setTeamEditModalOpen] = useState(false);

    // Initialdaten laden
    useEffect(() => {
        if (teamDetailOpen) {
            apiJson<{ team: Team }>(`/api/teams/${teamId}`)
            .then(data => {
                setTeam(data.team);
            })
            .catch(() => setTeam(null));
        }
    }, [teamDetailOpen]);

    return (
        <Dialog open={teamDetailOpen} onClose={onClose} maxWidth="sm" fullWidth>
            {team && team.permissions?.canView ? (
                <>
                    <Box display="flex" alignItems="center" justifyContent="space-between" px={3} pt={3} pb={1}>
                        <Box>
                            <Typography variant="h5" component="span">{team.name}</Typography>
                            <Typography variant="subtitle2" component="span" sx={{ ml: 2, color: 'text.secondary' }}>({team.englishName})</Typography>
                        </Box>
                        <IconButton aria-label="close" onClick={onClose} size="small" sx={{ ml: 2 }}>
                            <CloseIcon />
                        </IconButton>
                    </Box>
                    <DialogContent>
                        <Box mb={2}>
                            <Typography variant="body2" color="text.secondary" sx={{ mb: 0.5 }}>Beschreibung</Typography>
                            <Typography variant="body1" sx={{ mb: 2 }}>{team.description}</Typography>
                            <Divider sx={{ mb: 2 }} />
                            <Box display="grid" gridTemplateColumns="1fr 1fr" gap={2}>
                                <Box>
                                    <Typography variant="body2" color="text.secondary">Code</Typography>
                                    <Typography variant="body1">{team.code}</Typography>
                                </Box>
                                <Box>
                                    <Typography variant="body2" color="text.secondary">Stichtag</Typography>
                                    <Typography variant="body1">{team.referenceDate}</Typography>
                                </Box>
                                <Box>
                                    <Typography variant="body2" color="text.secondary">Mindestalter</Typography>
                                    <Typography variant="body1">{team.minAge} Jahre</Typography>
                                </Box>
                                <Box>
                                    <Typography variant="body2" color="text.secondary">Höchstalter</Typography>
                                    <Typography variant="body1">{team.maxAge} Jahre</Typography>
                                </Box>
                            </Box>
                        </Box>
                    </DialogContent>
                    <DialogActions sx={{ justifyContent: 'flex-end', px: 3, pb: 2 }}>
                        {team.permissions?.canEdit && (
                            <Button variant="contained" color="warning" startIcon={<EditIcon />}
                                size="small"
                                onClick={() => {
                                    setTeamEditModalOpen(true);
                                }}
                                sx={{ ml: 1 }}
                                aria-label="Team bearbeiten"
                            >
                                Bearbeiten
                            </Button>
                        )}
                        {team.permissions.canDelete && (
                            <Button variant="contained" color="error" startIcon={<DeleteIcon />}
                                onClick={() => {
                                    setDeleteTeam(team);
                                    setDeleteModalOpen(true);
                                }}
                                sx={{ ml: 1 }}
                                aria-label="Team löschen"
                            >
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
            <TeamDeleteConfirmationModal
                open={deleteModalOpen}
                teamName={deleteTeam?.name}
                onClose={() => setDeleteModalOpen(false)}
                onConfirm={async () => {
                    if (!deleteTeam) return;
                    try {
                        await apiJson(`/api/teams/${deleteTeam.id}`, { method: 'DELETE' });
                    } catch (e) {
                        // Fehlerbehandlung ggf. Toast
                    } finally {
                        setDeleteModalOpen(false);
                        setTeam(null);
                        setDeleteTeam(null);
                        loadTeams();
                        onClose();
                    }
                }}
            />
            <TeamEditModal
                openTeamEditModal={teamEditModalOpen}
                teamId={teamId}
                onTeamEditModalClose={() => setTeamEditModalOpen(false)}
                onTeamSaved={() => {
                    setTeamEditModalOpen(false);
                    loadTeams();
                }}
            />
        </Dialog>
    );
};

export default TeamDetailsModal;
