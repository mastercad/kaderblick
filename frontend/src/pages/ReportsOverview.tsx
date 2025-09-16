import React, { useState, useEffect } from 'react';
import {
  Box,
  Button,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
  Paper,
  IconButton,
  Alert,
  CircularProgress,
} from '@mui/material';
import { Edit as EditIcon, Delete as DeleteIcon, Add as AddIcon } from '@mui/icons-material';
import { ReportBuilderModal, type Report } from '../modals/ReportBuilderModal';
import { DynamicConfirmationModal } from '../modals/DynamicConfirmationModal';
import { fetchReportDefinitions, deleteReport } from '../services/reports';
import { apiJson } from '../utils/api';

const ReportsOverview = () => {
  const [reports, setReports] = useState<Report[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string>('');
  const [builderModalOpen, setBuilderModalOpen] = useState(false);
  const [editingReport, setEditingReport] = useState<Report | null>(null);
  const [confirmationModalOpen, setConfirmationModalOpen] = useState(false);
  const [reportToDelete, setReportToDelete] = useState<number | null>(null);

  useEffect(() => {
    loadReports();
  }, []);

  const loadReports = async () => {
    try {
      setLoading(true);
      setError('');
      const reportDefinitions = await fetchReportDefinitions();
      
      const allReports = [
        ...reportDefinitions.templates.map((rd: any) => ({
          id: rd.id,
          name: rd.name,
          description: rd.description || '',
          isTemplate: true,
          config: rd.config || {
            diagramType: 'bar',
            xField: 'player',
            yField: 'goals',
            groupBy: [],
            filters: {},
          },
        })),
        ...reportDefinitions.userReports.map((rd: any) => ({
          id: rd.id,
          name: rd.name,
          description: rd.description || '',
          isTemplate: false,
          config: rd.config || {
            diagramType: 'bar',
            xField: 'player',
            yField: 'goals',
            groupBy: [],
            filters: {},
          },
        }))
      ];
      
      setReports(allReports);
    } catch (err) {
      setError('Fehler beim Laden der Reports');
      console.error('Failed to load reports:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateNew = () => {
    setEditingReport(null);
    setBuilderModalOpen(true);
  };

  const handleEditReport = (report: Report) => {
    setEditingReport(report);
    setBuilderModalOpen(true);
  };

  const handleSaveReport = async (reportData: Report) => {
    try {
      if (reportData.id) {
        await apiJson(`/api/report/definition/${reportData.id}`, {
          method: 'PUT',
          body: reportData
        });
      } else {
        await apiJson('/api/report/definition', {
          method: 'POST',
          body: reportData
        });
      }
      
      setBuilderModalOpen(false);
      setEditingReport(null);
      await loadReports();
    } catch (error) {
      console.error('Error saving report:', error);
    }
  };

  const handleDeleteReport = (reportId: number) => {
    setReportToDelete(reportId);
    setConfirmationModalOpen(true);
  };

  const handleConfirmDelete = async () => {
    if (reportToDelete) {
      try {
        await deleteReport(reportToDelete);
        setReports(prev => prev.filter(r => r.id !== reportToDelete));
        setConfirmationModalOpen(false);
        setReportToDelete(null);
      } catch (err) {
        setError('Fehler beim Löschen des Reports');
        console.error('Failed to delete report:', err);
        setConfirmationModalOpen(false);
        setReportToDelete(null);
      }
    }
  };

  const handleCancelDelete = () => {
    setConfirmationModalOpen(false);
    setReportToDelete(null);
  };

  const getReportTypeLabel = (type: string) => {
    switch (type) {
      case 'bar': return 'Balken';
      case 'line': return 'Linie';
      case 'pie': return 'Kreis';
      default: return type;
    }
  };

  return (
    <Box sx={{ width: '100%', px: { xs: 2, md: 4 }, py: { xs: 2, md: 4 } }}>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4" component="h1">Reports Übersicht</Typography>
        <Button
          variant="contained"
          color="primary"
          startIcon={<AddIcon />}
          onClick={handleCreateNew}
        >
          Neuer Report
        </Button>
      </Box>

      {error && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {error}
        </Alert>
      )}

      {loading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : (
        <TableContainer component={Paper}>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Name</TableCell>
                <TableCell>Beschreibung</TableCell>
                <TableCell>Diagrammtyp</TableCell>
                <TableCell>Vorlage</TableCell>
                <TableCell>Aktionen</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {reports.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} align="center">
                    <Typography variant="body2" color="textSecondary">
                      Keine Reports vorhanden. Erstellen Sie Ihren ersten Report!
                    </Typography>
                  </TableCell>
                </TableRow>
              ) : (
                reports.map((report) => (
                  <TableRow key={report.id}>
                    <TableCell>
                      <Typography variant="body2" fontWeight="medium">
                        {report.name}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2" color="textSecondary">
                        {report.description || '-'}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      {getReportTypeLabel(report.config.diagramType)}
                    </TableCell>
                    <TableCell>
                      {report.isTemplate ? (
                        <Typography variant="caption" color="primary">
                          Vorlage
                        </Typography>
                      ) : (
                        '-'
                      )}
                    </TableCell>
                    <TableCell>
                      <Box display="flex" gap={1}>
                        <IconButton
                          size="small"
                          onClick={() => handleEditReport(report)}
                          color="primary"
                        >
                          <EditIcon fontSize="small" />
                        </IconButton>
                        <IconButton
                          size="small"
                          onClick={() => handleDeleteReport(report.id!)}
                          color="error"
                        >
                          <DeleteIcon fontSize="small" />
                        </IconButton>
                      </Box>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>
      )}

      <ReportBuilderModal
        open={builderModalOpen}
        onClose={() => {
          setBuilderModalOpen(false);
          setEditingReport(null);
        }}
        onSave={handleSaveReport}
        report={editingReport || undefined}
      />

      <DynamicConfirmationModal
        open={confirmationModalOpen}
        onClose={handleCancelDelete}
        onConfirm={handleConfirmDelete}
        title="Report löschen"
        message="Sind Sie sicher, dass Sie diesen Report löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden."
        confirmText="Löschen"
        cancelText="Abbrechen"
        confirmColor="error"
      />
    </Box>
  );
};

export default ReportsOverview;
