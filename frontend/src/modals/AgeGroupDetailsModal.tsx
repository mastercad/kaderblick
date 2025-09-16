import React, { useEffect, useState } from 'react';
import {
    Dialog, DialogContent, DialogActions, Button, Box, Typography, CircularProgress, IconButton, Divider, Stack
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { apiJson } from '../utils/api';
import { AgeGroup } from '../types/ageGroup';
import AgeGroupDeleteConfirmationModal from './AgeGroupDeleteConfirmationModal';
import AgeGroupEditModal from './AgeGroupEditModal';

interface AgeGroupDetailsModalProps {
  ageGroupDetailOpen: boolean;
  ageGroupId: number | null;
  onClose: () => void;
  loadAgeGroups: () => void;
}

const AgeGroupDetailsModal: React.FC<AgeGroupDetailsModalProps> = ({ ageGroupDetailOpen, ageGroupId, onClose, loadAgeGroups }) => {
    const [ageGroup, setAgeGroup] = useState<AgeGroup | null>(null);
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [deleteAgeGroup, setDeleteAgeGroup] = useState<AgeGroup | null>(null);
    const [ageGroupEditModalOpen, setAgeGroupEditModalOpen] = useState(false);

    // Initialdaten laden
    useEffect(() => {
        if (ageGroupDetailOpen) {
            apiJson<{ ageGroup: AgeGroup }>(`/api/age-groups/${ageGroupId}`)
            .then(data => {
                setAgeGroup(data.ageGroup);
            })
            .catch(() => setAgeGroup(null));
        }
    }, [ageGroupDetailOpen]);

    return (
        <Dialog open={ageGroupDetailOpen} onClose={onClose} maxWidth="sm" fullWidth>
            {ageGroup && ageGroup.permissions?.canView ? (
                <>
                    <Box display="flex" alignItems="center" justifyContent="space-between" px={3} pt={3} pb={1}>
                        <Box>
                            <Typography variant="h5" component="span">{ageGroup.name}</Typography>
                            <Typography variant="subtitle2" component="span" sx={{ ml: 2, color: 'text.secondary' }}>({ageGroup.englishName})</Typography>
                        </Box>
                        <IconButton aria-label="close" onClick={onClose} size="small" sx={{ ml: 2 }}>
                            <CloseIcon />
                        </IconButton>
                    </Box>
                    <DialogContent>
                        <Box mb={2}>
                            <Typography variant="body2" color="text.secondary" sx={{ mb: 0.5 }}>Beschreibung</Typography>
                            <Typography variant="body1" sx={{ mb: 2 }}>{ageGroup.description}</Typography>
                            <Divider sx={{ mb: 2 }} />
                            <Box display="grid" gridTemplateColumns="1fr 1fr" gap={2}>
                                <Box>
                                    <Typography variant="body2" color="text.secondary">Code</Typography>
                                    <Typography variant="body1">{ageGroup.code}</Typography>
                                </Box>
                                <Box>
                                    <Typography variant="body2" color="text.secondary">Stichtag</Typography>
                                    <Typography variant="body1">{ageGroup.referenceDate}</Typography>
                                </Box>
                                <Box>
                                    <Typography variant="body2" color="text.secondary">Mindestalter</Typography>
                                    <Typography variant="body1">{ageGroup.minAge} Jahre</Typography>
                                </Box>
                                <Box>
                                    <Typography variant="body2" color="text.secondary">Höchstalter</Typography>
                                    <Typography variant="body1">{ageGroup.maxAge} Jahre</Typography>
                                </Box>
                            </Box>
                        </Box>
                    </DialogContent>
                    <DialogActions sx={{ justifyContent: 'flex-end', px: 3, pb: 2 }}>
                        {ageGroup.permissions?.canEdit && (
                            <Button variant="contained" color="warning" startIcon={<EditIcon />}
                                size="small"
                                onClick={() => {
                                    setAgeGroupEditModalOpen(true);
                                }}
                                sx={{ ml: 1 }}
                                aria-label="Altersgruppe bearbeiten"
                            >
                                Bearbeiten
                            </Button>
                        )}
                        {ageGroup.permissions.canDelete && (
                            <Button variant="contained" color="error" startIcon={<DeleteIcon />}
                                onClick={() => {
                                    setDeleteAgeGroup(ageGroup);
                                    setDeleteModalOpen(true);
                                }}
                                sx={{ ml: 1 }}
                                aria-label="Altersgruppe löschen"
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
            <AgeGroupDeleteConfirmationModal
                open={deleteModalOpen}
                ageGroupName={deleteAgeGroup?.name}
                onClose={() => setDeleteModalOpen(false)}
                onConfirm={async () => {
                    if (!deleteAgeGroup) return;
                    try {
                        await apiJson(`/api/age-groups/${deleteAgeGroup.id}`, { method: 'DELETE' });
                    } catch (e) {
                        // Fehlerbehandlung ggf. Toast
                    } finally {
                        setDeleteModalOpen(false);
                        setAgeGroup(null);
                        setDeleteAgeGroup(null);
                        loadAgeGroups();
                        onClose();
                    }
                }}
            />
            <AgeGroupEditModal
                openAgeGroupEditModal={ageGroupEditModalOpen}
                ageGroupId={ageGroupId}
                onAgeGroupEditModalClose={() => setAgeGroupEditModalOpen(false)}
                onAgeGroupSaved={() => {
                    setAgeGroupEditModalOpen(false);
                    loadAgeGroups();
                }}
            />
        </Dialog>
    );
};

export default AgeGroupDetailsModal;
