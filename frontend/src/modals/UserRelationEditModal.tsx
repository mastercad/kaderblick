import React, { useEffect, useState } from 'react';
import { useToast } from '../context/ToastContext';
import {
	Dialog, DialogTitle, DialogContent, DialogActions, Button, Box, Typography, Card, CardHeader, CardContent, IconButton, TextField, MenuItem, Checkbox, ListItemText
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import { apiJson } from '../utils/api';

// Typdefinitionen
export type RelationType = {
	id: number;
	identifier: string;
	name: string;
	category: 'player' | 'coach';
};
export type Player = { id: number; fullName: string };
export type Coach = { id: number; fullName: string };
export type Relation = {
	id?: string;
	relationType: RelationType | null;
	entity: Player | Coach | null;
	permissions: string[];
};
export type UserRelationEditModalProps = {
	open: boolean;
	onClose: () => void;
	user: { id: number; fullName: string };
};

const UserRelationEditModal: React.FC<UserRelationEditModalProps> = ({ open, onClose, user }) => {
    // Sofortiger Schutz: Wenn user nicht gesetzt, gar nichts rendern und keine Logik ausführen
    if (!user || typeof user.id === 'undefined') {
        return null;
    }

    const [playerRelations, setPlayerRelations] = useState<Relation[]>([]);
    const [coachRelations, setCoachRelations] = useState<Relation[]>([]);
    const [relationTypes, setRelationTypes] = useState<RelationType[]>([]);
    const [players, setPlayers] = useState<Player[]>([]);
    const [coaches, setCoaches] = useState<Coach[]>([]);
    const [allPermissions, setAllPermissions] = useState<string[]>([]);
    const [loading, setLoading] = useState(false);

    const { showToast } = useToast();

    useEffect(() => {
        if (!open) return;
        setLoading(true);
        apiJson(`/admin/users/${user.id}/assign`).then((data) => {
            // relationTypes ist im Backend nach Kategorie gruppiert, wir brauchen ein flaches Array
            const flatRelationTypes = Object.values(data.relationTypes || {}).flat() as RelationType[];
            setRelationTypes(flatRelationTypes);
            setPlayers(data.players || []);
            setCoaches(data.coaches || []);
            setAllPermissions((data.permissions || []).map((p: any) => p.name || p));
            // currentAssignments: { players: [...], coaches: [...] }
            setPlayerRelations((data.currentAssignments?.players || []).map((a: any) => ({
                relationType: a.relationType,
                entity: a.entity,
                permissions: a.permissions || []
            })));
            setCoachRelations((data.currentAssignments?.coaches || []).map((a: any) => ({
                relationType: a.relationType,
                entity: a.entity,
                permissions: a.permissions || []
            })));
        }).finally(() => setLoading(false));
    }, [open, user.id]);

	const handleAdd = (category: 'player' | 'coach') => {
        let rel: Relation;
        if (category === 'player') {
            const firstType = relationTypes.find(rt => rt.category === 'player') || null;
            rel = { relationType: firstType, entity: null, permissions: [] };
            setPlayerRelations(prev => [...prev, rel]);
        } else {
            const firstType = relationTypes.find(rt => rt.category === 'coach') || null;
            rel = { relationType: firstType, entity: null, permissions: [] };
            setCoachRelations(prev => [...prev, rel]);
        }
	};
	const handleRemove = (category: 'player' | 'coach', idx: number) => {
		if (category === 'player') setPlayerRelations(prev => prev.filter((_, i) => i !== idx));
		else setCoachRelations(prev => prev.filter((_, i) => i !== idx));
	};
	const handleChange = (category: 'player' | 'coach', idx: number, field: keyof Relation, value: any) => {
		const setter = category === 'player' ? setPlayerRelations : setCoachRelations;
		const rels = category === 'player' ? [...playerRelations] : [...coachRelations];
		rels[idx] = { ...rels[idx], [field]: value };
		if (field === 'relationType') rels[idx].entity = null;
		setter(rels);
	};

    const handleSave = async () => {
        // Speichern-Logik (API-Aufruf)
        const allRelations = [
            ...playerRelations.map(r => ({ ...r, player: r.entity, coach: null })),
            ...coachRelations.map(r => ({ ...r, coach: r.entity, player: null })),
        ];
        try {
            const res = await apiJson(`/admin/users/${user.id}/assign`, {
                method: 'POST',
                body: { relations: allRelations },
                headers: { 'Content-Type': 'application/json' },
            });
            if (res && res.status === 'success') {
                showToast(res.message || 'Zuordnungen erfolgreich gespeichert.', 'success');
            } else {
                showToast(res?.message || 'Fehler beim Speichern der Zuordnungen.', 'error');
            }
        } catch (e: any) {
            showToast(e?.message || 'Fehler beim Speichern der Zuordnungen.', 'error');
        }
        onClose();
    };

    if (!user || !user.id) {
        return null;
    }

	return (
		<Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
			<DialogTitle>Benutzer zuordnen: {user.fullName}</DialogTitle>
			<DialogContent>
				{loading ? <Typography>Lade...</Typography> : (
					<>
						{/* Spielerzuordnungen */}
						<Card sx={{ mb: 3 }}>
							<CardHeader title="Spielerzuordnungen" action={<Button onClick={() => handleAdd('player')} startIcon={<AddIcon />}>Hinzufügen</Button>} />
							<CardContent>
                                {Array.isArray(playerRelations) && playerRelations.filter(rel => rel && rel.relationType && typeof rel.relationType.id !== 'undefined').map((rel, idx) => (
                                    <Box key={idx} display="flex" gap={2} alignItems="center" mb={1}>
                                        <TextField
                                            select
                                            label="Beziehungstyp"
                                            value={rel.relationType.id}
                                            onChange={e => handleChange('player', idx, 'relationType', relationTypes.find(rt => rt.id === +e.target.value) || null)}
                                            sx={{ minWidth: 180 }}
                                            size="small"
                                        >
                                            {relationTypes.filter(rt => rt.category === 'player').map(rt => (
                                                <MenuItem key={rt.id} value={rt.id}>{rt.name}</MenuItem>
                                            ))}
                                        </TextField>
                                        <TextField
                                            select
                                            label="Spieler"
                                            value={rel.entity && rel.entity.id ? rel.entity.id : ''}
                                            onChange={e => handleChange('player', idx, 'entity', players.find(p => p.id === +e.target.value) || null)}
                                            sx={{ minWidth: 180 }}
                                            size="small"
                                            disabled={!rel.relationType}
                                        >
                                            {players.map(p => (
                                                <MenuItem key={p.id} value={p.id}>{p.fullName}</MenuItem>
                                            ))}
                                        </TextField>
                                        <TextField
                                            select
                                            label="Berechtigungen"
                                            SelectProps={{ multiple: true, renderValue: (selected: any) => (selected as string[]).join(', ') }}
                                            value={rel.permissions}
                                            onChange={e => handleChange('player', idx, 'permissions', e.target.value)}
                                            sx={{ minWidth: 200 }}
                                            size="small"
                                        >
                                            {allPermissions.map(perm => (
                                                <MenuItem key={perm} value={perm}>
                                                    <Checkbox checked={rel.permissions.includes(perm)} />
                                                    <ListItemText primary={perm} />
                                                </MenuItem>
                                            ))}
                                        </TextField>
                                        <IconButton color="error" onClick={() => handleRemove('player', idx)}><DeleteIcon /></IconButton>
                                    </Box>
                                ))}
							</CardContent>
						</Card>
						{/* Trainerzuordnungen */}
						<Card sx={{ mb: 3 }}>
							<CardHeader title="Trainerzuordnungen" action={<Button onClick={() => handleAdd('coach')} startIcon={<AddIcon />}>Hinzufügen</Button>} />
							<CardContent>
                                {Array.isArray(coachRelations) && coachRelations.filter(rel => rel && rel.relationType && typeof rel.relationType.id !== 'undefined').map((rel, idx) => (
                                    <Box key={idx} display="flex" gap={2} alignItems="center" mb={1}>
                                        <TextField
                                            select
                                            label="Beziehungstyp"
                                            value={rel.relationType.id}
                                            onChange={e => handleChange('coach', idx, 'relationType', relationTypes.find(rt => rt.id === +e.target.value) || null)}
                                            sx={{ minWidth: 180 }}
                                            size="small"
                                        >
                                            {relationTypes.filter(rt => rt.category === 'coach').map(rt => (
                                                <MenuItem key={rt.id} value={rt.id}>{rt.name}</MenuItem>
                                            ))}
                                        </TextField>
                                        <TextField
                                            select
                                            label="Trainer"
                                            value={rel.entity && rel.entity.id ? rel.entity.id : ''}
                                            onChange={e => handleChange('coach', idx, 'entity', coaches.find(c => c.id === +e.target.value) || null)}
                                            sx={{ minWidth: 180 }}
                                            size="small"
                                            disabled={!rel.relationType}
                                        >
                                            {coaches.map(c => (
                                                <MenuItem key={c.id} value={c.id}>{c.fullName}</MenuItem>
                                            ))}
                                        </TextField>
                                        <TextField
                                            select
                                            label="Berechtigungen"
                                            SelectProps={{ multiple: true, renderValue: (selected: any) => (selected as string[]).join(', ') }}
                                            value={rel.permissions}
                                            onChange={e => handleChange('coach', idx, 'permissions', e.target.value)}
                                            sx={{ minWidth: 200 }}
                                            size="small"
                                        >
                                            {allPermissions.map(perm => (
                                                <MenuItem key={perm} value={perm}>
                                                    <Checkbox checked={rel.permissions.includes(perm)} />
                                                    <ListItemText primary={perm} />
                                                </MenuItem>
                                            ))}
                                        </TextField>
                                        <IconButton color="error" onClick={() => handleRemove('coach', idx)}><DeleteIcon /></IconButton>
                                    </Box>
                                ))}
							</CardContent>
						</Card>
					</>
				)}
			</DialogContent>
			<DialogActions>
				<Button onClick={onClose}>Abbrechen</Button>
				<Button variant="contained" onClick={handleSave} disabled={loading}>Speichern</Button>
			</DialogActions>
		</Dialog>
	);
};

export default UserRelationEditModal;
