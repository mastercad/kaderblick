import React, { useEffect, useRef, useState } from 'react';
import { useToast } from '../../context/ToastContext';
import { apiJson } from '../../utils/api';
import type { Formation, FormationData, Player, PlayerData, Team } from './types';
import { findFreePosition, getRelativePosition } from './helpers';
import { FOOTBALL_TEMPLATES } from './templates';
import type { FormationTemplate } from './templates';

type DragSource = 'field' | 'bench';

export interface FormationEditorState {
  // data
  formation: Formation | null;
  players: PlayerData[];
  benchPlayers: PlayerData[];
  availablePlayers: Player[];
  teams: Team[];
  // form fields
  name: string;
  notes: string;
  selectedTeam: number | '';
  // ui state
  loading: boolean;
  error: string | null;
  searchQuery: string;
  showTemplatePicker: boolean;
  draggedPlayerId: number | null;
  pitchRef: React.RefObject<HTMLDivElement | null>;
  // setters
  setName: (v: string) => void;
  setNotes: (v: string) => void;
  setSelectedTeam: (v: number | '') => void;
  setSearchQuery: (v: string) => void;
  setShowTemplatePicker: (v: boolean) => void;
  // actions
  applyTemplate: (t: FormationTemplate) => void;
  addPlayerToFormation: (p: Player, target: DragSource) => void;
  addGenericPlayer: () => void;
  removePlayer: (id: number) => void;
  removeBenchPlayer: (id: number) => void;
  sendToBench: (id: number) => void;
  sendToField: (id: number) => void;
  handlePitchMouseMove: (e: React.MouseEvent) => void;
  handlePitchMouseUp: () => void;
  handlePitchTouchMove: (e: React.TouchEvent) => void;
  handlePitchTouchEnd: () => void;
  startDragFromField: (id: number, e: React.MouseEvent | React.TouchEvent) => void;
  startDragFromBench: (id: number, e: React.MouseEvent | React.TouchEvent) => void;
  handleSave: () => Promise<void>;
}

export function useFormationEditor(
  open: boolean,
  formationId: number | null,
  onClose: () => void,
  onSaved?: (f: Formation) => void,
): FormationEditorState {
  const { showToast } = useToast();

  const [formation, setFormation] = useState<Formation | null>(null);
  const [players, setPlayers] = useState<PlayerData[]>([]);
  const [benchPlayers, setBenchPlayers] = useState<PlayerData[]>([]);
  const [availablePlayers, setAvailablePlayers] = useState<Player[]>([]);
  const [teams, setTeams] = useState<Team[]>([]);
  const [name, setName] = useState('');
  const [notes, setNotes] = useState('');
  const [selectedTeam, setSelectedTeam] = useState<number | ''>('');
  const [nextPlayerNumber, setNextPlayerNumber] = useState(1);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [showTemplatePicker, setShowTemplatePicker] = useState(false);
  const [draggedPlayerId, setDraggedPlayerId] = useState<number | null>(null);
  const [draggedFrom, setDraggedFrom] = useState<DragSource | null>(null);

  const pitchRef = useRef<HTMLDivElement>(null);

  // ── Show template picker for new formations ──────────────────────────────────
  useEffect(() => {
    setShowTemplatePicker(open && !formationId);
  }, [open, formationId]);

  // ── Load teams (only active coach assignments) ────────────────────────────────
  useEffect(() => {
    if (!open) return;
    apiJson<{ teams: Team[] }>('/formation/coach-teams')
      .then(data => {
        const loaded = Array.isArray(data.teams) ? data.teams : [];
        setTeams(loaded);
        if (loaded.length === 0) {
          setError('Du bist aktuell keinem Team als Trainer zugeordnet. Bitte wende dich an einen Administrator.');
        } else {
          setSelectedTeam(loaded[0].id);
        }
      })
      .catch(() => {
        setTeams([]);
        setError('Teams konnten nicht geladen werden.');
      });
  }, [open]);

  // ── Load formation when editing ──────────────────────────────────────────────
  useEffect(() => {
    if (!open) return;
    if (formationId) {
      setLoading(true);
      apiJson<any>(`/formation/${formationId}/edit`)
        .then(data => {
          const f = data.formation;
          setFormation(f);
          setName(f.name);
          const fieldPlayers: PlayerData[] = Array.isArray(f.formationData?.players)
            ? f.formationData.players.map((p: any) => ({ ...p, id: p.id ?? Date.now() + Math.random() }))
            : [];
          const bench: PlayerData[] = Array.isArray(f.formationData?.bench)
            ? f.formationData.bench.map((p: any) => ({ ...p, id: p.id ?? Date.now() + Math.random() }))
            : [];
          setPlayers(fieldPlayers);
          setBenchPlayers(bench);
          setNotes(f.formationData?.notes ?? '');
          const allNums = [...fieldPlayers, ...bench].map(p => (typeof p.number === 'number' ? p.number : 0));
          setNextPlayerNumber(allNums.length > 0 ? Math.max(...allNums) + 1 : 1);
          if (Array.isArray(data.availablePlayers?.players)) {
            setAvailablePlayers(data.availablePlayers.players.map((e: any) => ({
              id: e.player.id, name: e.player.name, shirtNumber: e.shirtNumber,
            })));
          }
        })
        .catch(err => setError(err?.message ?? 'Fehler beim Laden'))
        .finally(() => setLoading(false));
    } else {
      // Reset for new formation
      setFormation(null);
      setName('');
      setNotes('');
      setPlayers([]);
      setBenchPlayers([]);
      setNextPlayerNumber(1);
      setAvailablePlayers([]);
      setSearchQuery('');
      setError(null);
    }
  }, [open, formationId]);

  // ── Load squad when team changes ─────────────────────────────────────────────
  useEffect(() => {
    if (!open || !selectedTeam) {
      if (!selectedTeam) setAvailablePlayers([]);
      return;
    }
    apiJson<any>(`/formation/team/${selectedTeam}/players`)
      .then(data => {
        if (Array.isArray(data.players)) {
          const mapped = data.players
            .filter((e: any) => e?.id)
            .map((e: any) => ({ id: e.id, name: e.name, shirtNumber: e.shirtNumber }));
          setAvailablePlayers(mapped);
          setError(mapped.length === 0 ? 'Keine Spieler für dieses Team gefunden.' : null);
        } else {
          setAvailablePlayers([]);
        }
      })
      .catch(() => setAvailablePlayers([]));
  }, [open, selectedTeam]);

  // ─── Template ────────────────────────────────────────────────────────────────

  const applyTemplate = (template: FormationTemplate) => {
    const templatePlayers: PlayerData[] = template.players.map((tp, idx) => ({
      id: Date.now() + idx,
      x: tp.x, y: tp.y,
      number: idx + 1,
      name: tp.position,
      position: tp.position,
      playerId: null,
      isRealPlayer: false,
    }));
    setPlayers(templatePlayers);
    setBenchPlayers([]);
    setNextPlayerNumber(templatePlayers.length + 1);
    setShowTemplatePicker(false);
  };

  // ─── Drag & Drop ─────────────────────────────────────────────────────────────

  const startDragFromField = (id: number, e: React.MouseEvent | React.TouchEvent) => {
    setDraggedPlayerId(id);
    setDraggedFrom('field');
    e.stopPropagation();
    // prevent native scroll on touch
    if ('touches' in e) e.preventDefault?.();
  };

  const startDragFromBench = (id: number, e: React.MouseEvent | React.TouchEvent) => {
    setDraggedPlayerId(id);
    setDraggedFrom('bench');
    e.stopPropagation();
  };

  const applyDragMove = (clientX: number, clientY: number) => {
    if (draggedPlayerId === null || !pitchRef.current) return;
    const pos = getRelativePosition(clientX, clientY, pitchRef.current);
    if (draggedFrom === 'field') {
      setPlayers(prev => prev.map(p => p.id === draggedPlayerId ? { ...p, ...pos } : p));
    } else if (draggedFrom === 'bench') {
      const benched = benchPlayers.find(p => p.id === draggedPlayerId);
      if (benched) {
        setBenchPlayers(prev => prev.filter(p => p.id !== draggedPlayerId));
        setPlayers(prev => [...prev, { ...benched, ...pos }]);
        setDraggedFrom('field');
      }
    }
  };

  const handlePitchMouseMove = (e: React.MouseEvent) => applyDragMove(e.clientX, e.clientY);

  const handlePitchTouchMove = (e: React.TouchEvent) => {
    e.preventDefault();
    applyDragMove(e.touches[0].clientX, e.touches[0].clientY);
  };

  const stopDrag = () => { setDraggedPlayerId(null); setDraggedFrom(null); };
  const handlePitchMouseUp = stopDrag;
  const handlePitchTouchEnd = stopDrag;

  // ─── Player management ───────────────────────────────────────────────────────

  const addPlayerToFormation = (player: Player, target: DragSource) => {
    const alreadyIn = players.some(p => p.playerId === player.id) || benchPlayers.some(p => p.playerId === player.id);
    if (alreadyIn) return;
    const newPlayer: PlayerData = {
      id: Date.now(), x: 0, y: 0,
      number: player.shirtNumber ?? nextPlayerNumber,
      name: player.name,
      playerId: player.id,
      isRealPlayer: true,
    };
    if (target === 'field') {
      const pos = findFreePosition(players);
      setPlayers(prev => [...prev, { ...newPlayer, ...pos }]);
    } else {
      setBenchPlayers(prev => [...prev, newPlayer]);
    }
    setNextPlayerNumber(n => n + 1);
  };

  const addGenericPlayer = () => {
    const pos = findFreePosition(players);
    setPlayers(prev => [...prev, {
      id: Date.now(), ...pos,
      number: nextPlayerNumber,
      name: `Spieler ${nextPlayerNumber}`,
      playerId: null, isRealPlayer: false,
    }]);
    setNextPlayerNumber(n => n + 1);
  };

  const removePlayer = (id: number) => setPlayers(prev => prev.filter(p => p.id !== id));
  const removeBenchPlayer = (id: number) => setBenchPlayers(prev => prev.filter(p => p.id !== id));

  const sendToBench = (id: number) => {
    const p = players.find(pl => pl.id === id);
    if (!p) return;
    setPlayers(prev => prev.filter(pl => pl.id !== id));
    setBenchPlayers(prev => [...prev, p]);
  };

  const sendToField = (id: number) => {
    const p = benchPlayers.find(pl => pl.id === id);
    if (!p) return;
    setBenchPlayers(prev => prev.filter(pl => pl.id !== id));
    setPlayers(prev => [...prev, { ...p, ...findFreePosition(players) }]);
  };

  // ─── Save ────────────────────────────────────────────────────────────────────

  const handleSave = async () => {
    setLoading(true);
    setError(null);
    try {
      const formationData: FormationData = {
        ...(formation?.formationData ?? {}),
        players,
        bench: benchPlayers,
        notes,
      };
      const url = formationId ? `/formation/${formationId}/edit` : '/formation/new';
      const response = await apiJson(url, { method: 'POST', body: { name, team: selectedTeam, formationData } });
      if (response?.error) { setError(response.error); return; }
      showToast('Formation erfolgreich gespeichert!', 'success');
      const saved: Formation = response?.formation ?? {
        id: response?.id ?? Date.now(),
        name,
        formationType: {
          name: formation?.formationType?.name ?? 'fußball',
          cssClass: formation?.formationType?.cssClass ?? '',
          backgroundPath: formation?.formationType?.backgroundPath ?? 'fussballfeld_haelfte.jpg',
        },
        formationData,
      };
      onSaved?.(saved);
      onClose();
    } catch (err: any) {
      setError(err?.message ?? 'Fehler beim Speichern');
    } finally {
      setLoading(false);
    }
  };

  return {
    formation, players, benchPlayers, availablePlayers, teams,
    name, notes, selectedTeam,
    loading, error, searchQuery, showTemplatePicker, draggedPlayerId,
    pitchRef,
    setName, setNotes, setSelectedTeam: setSelectedTeam as (v: number | '') => void,
    setSearchQuery, setShowTemplatePicker,
    applyTemplate,
    addPlayerToFormation, addGenericPlayer,
    removePlayer, removeBenchPlayer, sendToBench, sendToField,
    handlePitchMouseMove, handlePitchMouseUp,
    handlePitchTouchMove, handlePitchTouchEnd,
    startDragFromField, startDragFromBench,
    handleSave,
  };
}
