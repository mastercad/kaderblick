import React, { useEffect, useState, useCallback, useRef } from 'react';
import GroupsIcon from '@mui/icons-material/Groups';
import { apiJson } from '../utils/api';
import { AdminPageLayout, AdminEmptyState, AdminTable, AdminActions, AdminSnackbar, AdminTableColumn } from '../components/AdminPageLayout';
import TeamDetailsModal from '../modals/TeamDetailsModal';
import TeamDeleteConfirmationModal from '../modals/TeamDeleteConfirmationModal';
import TeamEditModal from '../modals/TeamEditModal';
import { Team } from '../types/team';

interface PaginatedTeamsResponse {
  teams: Team[];
  total: number;
  page: number;
  limit: number;
}

const Teams = () => {
  const [teams, setTeams] = useState<Team[]>([]);
  const [totalCount, setTotalCount] = useState(0);
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(25);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [search, setSearch] = useState('');
  const [debouncedSearch, setDebouncedSearch] = useState('');
  const [teamId, setTeamId] = useState<number | null>(null);
  const [teamDetailsModalOpen, setTeamDetailsModalOpen] = useState(false);
  const [teamEditModalOpen, setTeamEditModalOpen] = useState(false);
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [deleteTeam, setDeleteTeam] = useState<Team | null>(null);
  const [snackbar, setSnackbar] = useState<AdminSnackbar>({ open: false, message: '', severity: 'success' });
  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const handleSearchChange = useCallback((value: string) => {
    setSearch(value);
    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      setDebouncedSearch(value);
      setPage(0);
    }, 350);
  }, []);

  const loadTeams = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const params = new URLSearchParams({
        page: String(page + 1),
        limit: String(rowsPerPage),
      });
      if (debouncedSearch) params.set('search', debouncedSearch);

      const res = await apiJson<PaginatedTeamsResponse>(`/api/teams?${params}`);
      setTeams(res?.teams || []);
      setTotalCount(res?.total || 0);
    } catch {
      setError('Fehler beim Laden der Teams.');
    } finally {
      setLoading(false);
    }
  }, [page, rowsPerPage, debouncedSearch]);

  useEffect(() => { loadTeams(); }, [loadTeams]);

  const handleDelete = async (id: number) => {
    try {
      await apiJson(`/api/teams/${id}`, { method: 'DELETE' });
      setDeleteModalOpen(false);
      setSnackbar({ open: true, message: 'Team gelöscht', severity: 'success' });
      loadTeams();
    } catch {
      setSnackbar({ open: true, message: 'Fehler beim Löschen des Teams.', severity: 'error' });
    }
  };

  const columns: AdminTableColumn<Team>[] = [
    { header: 'Name', render: t => t.name || '' },
    { header: 'Altersgruppe', render: t => t.ageGroup?.name || '' },
    { header: 'Liga', render: t => t.league?.name || '' },
  ];

  return (
    <AdminPageLayout
      icon={<GroupsIcon />}
      title="Teams"
      itemCount={totalCount}
      loading={loading}
      error={error}
      createLabel="Neues Team"
      onCreate={() => { setTeamId(null); setTeamEditModalOpen(true); }}
      search={search}
      onSearchChange={handleSearchChange}
      searchPlaceholder="Team suchen..."
      snackbar={snackbar}
      onSnackbarClose={() => setSnackbar(s => ({ ...s, open: false }))}
    >
      {teams.length === 0 && !loading ? (
        <AdminEmptyState icon={<GroupsIcon />} title="Keine Teams vorhanden" createLabel="Neues Team" onCreate={() => { setTeamId(null); setTeamEditModalOpen(true); }} />
      ) : (
        <AdminTable columns={columns} data={teams} getKey={t => t.id}
          serverPagination={{
            page,
            rowsPerPage,
            totalCount,
            onPageChange: setPage,
            onRowsPerPageChange: setRowsPerPage,
          }}
          onRowClick={t => { setTeamId(t.id); setTeamDetailsModalOpen(true); }}
          renderActions={t => (
            <AdminActions
              onDetails={() => { setTeamId(t.id); setTeamDetailsModalOpen(true); }}
              onEdit={t.permissions?.canEdit ? () => { setTeamId(t.id); setTeamEditModalOpen(true); } : undefined}
              onDelete={t.permissions?.canDelete ? () => { setDeleteTeam(t); setDeleteModalOpen(true); } : undefined}
            />
          )}
        />
      )}

      <TeamDeleteConfirmationModal open={deleteModalOpen} teamName={deleteTeam?.name} onClose={() => setDeleteModalOpen(false)} onConfirm={async () => handleDelete(deleteTeam!.id)} />
      <TeamDetailsModal teamDetailOpen={teamDetailsModalOpen} loadTeams={() => loadTeams()} teamId={teamId} onClose={() => setTeamDetailsModalOpen(false)} />
      <TeamEditModal openTeamEditModal={teamEditModalOpen} teamId={teamId} onTeamEditModalClose={() => setTeamEditModalOpen(false)} onTeamSaved={() => { setTeamEditModalOpen(false); loadTeams(); }} />
    </AdminPageLayout>
  );
};

export default Teams;
