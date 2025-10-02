import React, { useState, useEffect } from 'react';
import {
  Box,
  Button,
  Stepper,
  Step,
  StepLabel,
  Typography,
  TextField,
  Stack,
  Alert,
  MenuItem
} from '@mui/material';
import { apiJson } from '../utils/api';
import BaseModal from './BaseModal';

  type QuestionType = 'single_choice' | 'multiple_choice' | 'text' | 'scale_1_5' | 'scale_1_10';

  interface SurveyOption {
    id: number;
    optionText: string;
  }

  interface SurveyQuestion {
    id: string;
    questionText: string;
    type: QuestionType;
    options: number[];
  }

  interface SurveyFormData {
    title: string;
    description: string;
    dueDate: string;
    teamIds: number[];
    clubIds: number[];
    platform: boolean;
    questions: SurveyQuestion[];
  }

  const steps = ['Allgemein', 'Fragen', 'Zusammenfassung'];

  interface SurveyCreateWizardProps {
    open: boolean;
    onClose: () => void;
    onSurveyCreated?: () => void;
    editSurvey?: any | null; // TODO: Replace 'any' with proper Survey type if available
  }

  const SurveyCreateWizard: React.FC<SurveyCreateWizardProps> = ({ open, onClose, onSurveyCreated, editSurvey }) => {
    const [activeStep, setActiveStep] = useState(0);
    const [form, setForm] = useState<SurveyFormData>(() => {
      if (editSurvey) {
        return {
          title: editSurvey.title || '',
          description: editSurvey.description || '',
          dueDate: editSurvey.dueDate || '',
          teamIds: editSurvey.teamIds || [],
          clubIds: editSurvey.clubIds || [],
          platform: editSurvey.platform || false,
          questions: Array.isArray(editSurvey.questions)
            ? editSurvey.questions.map((q: any) => ({
                id: String(q.id),
                questionText: q.questionText || '',
                type: q.type,
                options: Array.isArray(q.options)
                  ? q.options.map((o: any) => typeof o === 'object' && o.id !== undefined ? o.id : o)
                  : [],
              }))
            : [],
        };
      }
      return {
        title: '',
        description: '',
        dueDate: '',
        teamIds: [],
        clubIds: [],
        platform: false,
        questions: []
      };
    });

    // Synchronisiere das Formular, wenn editSurvey sich ändert (z.B. beim Öffnen des Wizards)
    React.useEffect(() => {
      if (editSurvey) {
        setForm({
          title: editSurvey.title || '',
          description: editSurvey.description || '',
          dueDate: editSurvey.dueDate || '',
          teamIds: editSurvey.teamIds || [],
          clubIds: editSurvey.clubIds || [],
          platform: editSurvey.platform || false,
          questions: Array.isArray(editSurvey.questions)
            ? editSurvey.questions.map((q: any) => ({
                id: String(q.id),
                questionText: q.questionText || '',
                type: q.type,
                options: Array.isArray(q.options)
                  ? q.options.map((o: any) => typeof o === 'object' && o.id !== undefined ? o.id : o)
                  : [],
              }))
            : [],
        });
      } else {
        setForm({
          title: '',
          description: '',
          dueDate: '',
          teamIds: [],
          clubIds: [],
          platform: false,
          questions: []
        });
      }
    }, [editSurvey]);
  // Teams & Clubs
  const [availableTeams, setAvailableTeams] = useState<{id:number, name:string}[]>([]);
  const [availableClubs, setAvailableClubs] = useState<{id:number, name:string}[]>([]);
  const [teamsLoadError, setTeamsLoadError] = useState<string|null>(null);
  const [clubsLoadError, setClubsLoadError] = useState<string|null>(null);
  useEffect(() => {
    apiJson<{teams: {id:number, name:string}[]}>('/api/teams/list')
      .then(data => setAvailableTeams(data.teams))
      .catch(e => setTeamsLoadError('Fehler beim Laden der Teams: ' + (e?.message || 'Unknown error')));
    apiJson<{clubs: {id:number, name:string}[]}>('/api/clubs/list')
      .then((data) => {
        if (data && typeof data === 'object') {
        // Die eigentlichen Einträge stehen unter numerischen Keys
        const clubList = Object.keys(data)
          .filter(key => /^\d+$/.test(key))
          .map(key => data[key]);
        setAvailableClubs(clubList);
      } else {
        setAvailableClubs([]);
      }
    });
  }, []);
  const [error, setError] = useState<string | null>(null);
  // Zeigt Validierungsfehler erst nach Interaktion (Weiter-Klick) an
  const [touched, setTouched] = useState<{ [step: number]: boolean }>({});
  const [editQuestionId, setEditQuestionId] = useState<string | null>(null);
  const [questionDraft, setQuestionDraft] = useState<{ questionText: string; type: QuestionType; options: number[] }>({ questionText: '', type: 'single_choice', options: [] });
  const [availableOptions, setAvailableOptions] = useState<SurveyOption[]>([]);
  const [optionsLoadError, setOptionsLoadError] = useState<string | null>(null);

    useEffect(() => {
      apiJson<SurveyOption[]>('/api/survey-options')
        .then(setAvailableOptions)
        .catch((e) => {
          setAvailableOptions([]);
          setOptionsLoadError('Fehler beim Laden der Antwortoptionen: ' + (e?.message || 'Unknown error'));
          // eslint-disable-next-line no-console
          console.error('SurveyOptions Load Error:', e);
        });
    }, []);


    // Validierung pro Schritt
    const validateGeneralStep = () => {
      if (!form.title.trim()) return 'Bitte gib einen Titel ein.';
      if (
        (form.teamIds.length > 0 && (form.clubIds.length > 0 || form.platform)) ||
        (form.clubIds.length > 0 && (form.teamIds.length > 0 || form.platform)) ||
        (form.platform && (form.teamIds.length > 0 || form.clubIds.length > 0))
      ) {
        return 'Bitte wähle entweder Teams, Vereine oder Plattform – nicht mehrere gleichzeitig.';
      }
      if (form.teamIds.length === 0 && form.clubIds.length === 0 && !form.platform) {
        return 'Bitte wähle Teams, Vereine oder Plattform aus.';
      }
      return null;
    };

    const validateQuestionsStep = () => {
      if (form.questions.length === 0) return 'Bitte füge mindestens eine Frage hinzu.';
      for (const q of form.questions) {
        if (!q.questionText.trim()) return 'Jede Frage benötigt einen Fragetext.';
        // Nur Auswahlfragen (single_choice, multiple_choice) benötigen Optionen
        if ((q.type === 'single_choice' || q.type === 'multiple_choice') && (!q.options || q.options.length === 0)) {
          return 'Jede Auswahlfrage benötigt mindestens eine Option.';
        }
      }
      return null;
    };

    const validateSummaryStep = () => {
      // Final check, alles zusammen
      return validateGeneralStep() || validateQuestionsStep();
    };

    const stepValidation = [validateGeneralStep, validateQuestionsStep, validateSummaryStep];
    const currentStepError = stepValidation[activeStep]();

    const handleNext = () => {
      // Markiere aktuellen Schritt als "touched" beim ersten Weiter-Klick
      if (!touched[activeStep]) setTouched(t => ({ ...t, [activeStep]: true }));
      if (!currentStepError) setActiveStep((prev) => prev + 1);
    };
    const handleBack = () => setActiveStep((prev) => prev - 1);

    // Schritt 1: Allgemeine Angaben
    const renderGeneralStep = () => (
      <Stack spacing={2}>
        <TextField
          label="Titel der Umfrage"
          value={form.title}
          onChange={e => setForm(f => ({ ...f, title: e.target.value }))}
          fullWidth
          required
        />
        <TextField
          label="Beschreibung"
          value={form.description}
          onChange={e => setForm(f => ({ ...f, description: e.target.value }))}
          fullWidth
          multiline
          minRows={2}
        />
        <TextField
          label="Fälligkeitsdatum"
          type="date"
          value={form.dueDate}
          onChange={e => setForm(f => ({ ...f, dueDate: e.target.value }))}
          InputLabelProps={{ shrink: true }}
          fullWidth
        />
        <TextField
          select
          label="Teams zuordnen"
          value={form.teamIds}
          onChange={e => {
            const value = typeof e.target.value === 'string' ? e.target.value.split(',').map(Number) : e.target.value;
            setForm(f => ({ ...f, teamIds: value, clubIds: [], platform: false }));
          }}
          SelectProps={{ multiple: true }}
          fullWidth
          helperText="Wähle die Teams, für die die Umfrage gilt"
          disabled={form.clubIds.length > 0 || form.platform}
        >
          {Array.isArray(availableTeams) && availableTeams.length > 0
            ? availableTeams.map(team => (
                <MenuItem key={team.id} value={team.id}>{team.name}</MenuItem>
              ))
            : <MenuItem disabled value="">Keine Teams verfügbar</MenuItem>
          }
        </TextField>
        <TextField
          select
          label="Vereine zuordnen"
          value={form.clubIds}
          onChange={e => {
            const value = typeof e.target.value === 'string' ? e.target.value.split(',').map(Number) : e.target.value;
            setForm(f => ({ ...f, clubIds: value, teamIds: [], platform: false }));
          }}
          SelectProps={{ multiple: true }}
          fullWidth
          helperText="Wähle die Vereine, für die die Umfrage gilt"
          disabled={form.teamIds.length > 0 || form.platform}
        >
          {Array.isArray(availableClubs) && availableClubs.length > 0
            ? availableClubs.map(club => (
                <MenuItem key={club.id} value={club.id}>{club.name}</MenuItem>
              ))
            : <MenuItem disabled value="">Keine Vereine verfügbar</MenuItem>
          }
        </TextField>
        <Box display="flex" alignItems="center" gap={2}>
          <label>
            <input
              type="checkbox"
              checked={form.platform}
              onChange={e => {
                const checked = e.target.checked;
                setForm(f => ({ ...f, platform: checked, teamIds: [], clubIds: [] }));
              }}
              disabled={form.teamIds.length > 0 || form.clubIds.length > 0}
            />
            &nbsp;Umfrage für gesamte Plattform
          </label>
        </Box>
        {teamsLoadError && <Alert severity="error">{teamsLoadError}</Alert>}
        {clubsLoadError && <Alert severity="error">{clubsLoadError}</Alert>}
      </Stack>
    );

    // Schritt 2: Fragen verwalten
    const handleAddOrUpdateQuestion = () => {
      if (!questionDraft.questionText.trim()) {
        setError('Bitte gib einen Fragetext ein.');
        return;
      }
      if ((questionDraft.type === 'single_choice' || questionDraft.type === 'multiple_choice') && questionDraft.options.length === 0) {
        setError('Bitte wähle mindestens eine Antwortoption.');
        return;
      }
      setError(null);
      if (editQuestionId) {
        setForm(f => ({
          ...f,
          questions: f.questions.map(q =>
            q.id === editQuestionId ? { ...q, ...questionDraft } : q
          ),
        }));
        setEditQuestionId(null);
      } else {
        setForm(f => ({
          ...f,
          questions: [
            ...f.questions,
            { id: Math.random().toString(36).slice(2), ...questionDraft },
          ],
        }));
      }
      setQuestionDraft({ questionText: '', type: 'single_choice', options: [] });
    };

    const handleEditQuestion = (id: string) => {
      const q = form.questions.find(q => q.id === id);
      if (q) {
        setEditQuestionId(id);
        setQuestionDraft({ questionText: q.questionText, type: q.type, options: q.options });
      }
    };

    const handleDeleteQuestion = (id: string) => {
      setForm(f => ({ ...f, questions: f.questions.filter(q => q.id !== id) }));
      if (editQuestionId === id) {
        setEditQuestionId(null);
        setQuestionDraft({ questionText: '', type: 'single_choice', options: [] });
      }
    };
        
    // Fragetypen dynamisch laden (API liefert jetzt { id, name, typeKey })
    interface SurveyOptionType {
      id: number;
      name: string;
      typeKey: string;
    }

  // Skalen-Typen lokal ergänzen
  const scaleTypes: SurveyOptionType[] = [
  ];

  const [availableTypes, setAvailableTypes] = useState<SurveyOptionType[]>([]);
  const [typesLoadError, setTypesLoadError] = useState<string | null>(null);
    useEffect(() => {
      apiJson<SurveyOptionType[]>('/api/survey-option-types')
        .then(types => setAvailableTypes([...types, ...scaleTypes]))
        .catch((e) => {
          setAvailableTypes([...scaleTypes]);
          setTypesLoadError('Fehler beim Laden der Fragetypen: ' + (e?.message || 'Unknown error'));
          // eslint-disable-next-line no-console
          console.error('SurveyOptionTypes Load Error:', e);
        });
    }, []);

    const renderQuestionsStep = () => (
      <Box>
        <Stack spacing={2} mb={2}>
          <TextField
            label="Fragetext"
            value={questionDraft.questionText}
            onChange={e => setQuestionDraft(d => ({ ...d, questionText: e.target.value }))}
            fullWidth
          />
          <TextField
            select
            label="Fragetyp"
            value={questionDraft.type}
            onChange={e => {
              const type = e.target.value as QuestionType;
              // Skalen-Typen: Optionen automatisch setzen
              if (type === 'scale_1_5') {
                setQuestionDraft(d => ({ ...d, type, options: [1,2,3,4,5] }));
              } else if (type === 'scale_1_10') {
                setQuestionDraft(d => ({ ...d, type, options: [1,2,3,4,5,6,7,8,9,10] }));
              } else {
                setQuestionDraft(d => ({ ...d, type, options: [] }));
              }
            }}
            fullWidth
          >
            {availableTypes.map(type => (
              <MenuItem key={type.typeKey} value={type.typeKey}>{type.name}</MenuItem>
            ))}
          </TextField>
          {(questionDraft.type === 'single_choice' || questionDraft.type === 'multiple_choice') && (
            <TextField
              select
              label="Antwortoptionen (Mehrfachauswahl möglich)"
              value={questionDraft.options}
              onChange={e => {
                const value = typeof e.target.value === 'string'
                  ? e.target.value.split(',').map(Number)
                  : (e.target.value as number[]);
                setQuestionDraft(d => ({ ...d, options: value }));
              }}
              fullWidth
              SelectProps={{ multiple: true }}
              helperText="Wähle die erlaubten Antwortoptionen für diese Frage"
            >
              {availableOptions.map(opt => (
                <MenuItem key={opt.id} value={opt.id}>{opt.optionText}</MenuItem>
              ))}
            </TextField>
          )}
          {(questionDraft.type === 'scale_1_5' || questionDraft.type === 'scale_1_10') && (
            <Box>
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
                Optionen: {questionDraft.options.join(', ')}
              </Typography>
            </Box>
          )}
          <Box display="flex" gap={2}>
            <Button variant="contained" onClick={handleAddOrUpdateQuestion}>
              {editQuestionId ? 'Frage aktualisieren' : 'Frage hinzufügen'}
            </Button>
            {editQuestionId && (
              <Button onClick={() => { setEditQuestionId(null); setQuestionDraft({ questionText: '', type: 'single_choice', options: [] }); }}>
                Abbrechen
              </Button>
            )}
          </Box>
        </Stack>
        <Stack spacing={1}>
          {form.questions.length === 0 && <Typography color="text.secondary">Noch keine Fragen hinzugefügt.</Typography>}
          {form.questions.map((q, idx) => (
            <Box key={q.id} sx={{ p: 2, border: '1px solid #eee', borderRadius: 1, mb: 1, display: 'flex', alignItems: 'center', gap: 2 }}>
              <Typography sx={{ flex: 1 }}>{idx + 1}. {q.questionText} <span style={{ color: '#888', fontSize: 13 }}>({getTypeName(q.type)})</span></Typography>
              {(q.type === 'single_choice' || q.type === 'multiple_choice') && q.options.length > 0 && (
                <Typography variant="caption" color="text.secondary" sx={{ ml: 2 }}>
                  Optionen: {q.options.map(id => availableOptions.find(o => o.id === id)?.optionText).filter(Boolean).join(', ')}
                </Typography>
              )}
              <Button size="small" onClick={() => handleEditQuestion(q.id)}>Bearbeiten</Button>
              <Button size="small" color="error" onClick={() => handleDeleteQuestion(q.id)}>Löschen</Button>
            </Box>
          ))}
        </Stack>
      </Box>
    );

    // Schrittweises Rendering

    // Step 4: Absenden (Platzhalter)

    // Absenden-Logik für Zusammenfassung
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitSuccess, setSubmitSuccess] = useState(false);
    const [submitError, setSubmitError] = useState<string | null>(null);

    const handleSubmit = async () => {
      setIsSubmitting(true);
      setSubmitError(null);
      setSubmitSuccess(false);
      try {
        if (editSurvey && editSurvey.id) {
          await apiJson(`/api/surveys/${editSurvey.id}`, {
            method: 'PUT',
            body: {
              title: form.title,
              description: form.description,
              dueDate: form.dueDate ? new Date(form.dueDate).toISOString() : null,
              teamIds: form.teamIds,
              clubIds: form.clubIds,
              platform: form.platform,
              questions: form.questions,
            },
          });
        } else {
          await apiJson('/api/surveys', {
            method: 'POST',
            body: {
              title: form.title,
              description: form.description,
              dueDate: form.dueDate ? new Date(form.dueDate).toISOString() : null,
              teamIds: form.teamIds,
              clubIds: form.clubIds,
              platform: form.platform,
              questions: form.questions,
            },
          });
        }
        setSubmitSuccess(true);
        if (onSurveyCreated) {
          onSurveyCreated();
        }
        setTimeout(() => {
          onClose();
        }, 1500);
      } catch (e: any) {
        let msg = 'Unbekannter Fehler beim Speichern';
        if (e && typeof e === 'object') {
          if (e.message) msg = e.message;
          if (e.status) msg += ` (HTTP ${e.status})`;
        }
        setSubmitError(msg);
        // eslint-disable-next-line no-console
        console.error('Survey Submit Error:', e);
      } finally {
        setIsSubmitting(false);
      }
    };

    const getTypeName = (type: string) => {
        if (type === 'single_choice') return 'Einzelauswahl';
        if (type === 'multiple_choice') return 'Mehrfachauswahl';
        if (type === 'text') return 'Freitext';
        if (type === 'scale_1_5') return 'Skala 1–5';
        if (type === 'scale_1_10') return 'Skala 1–10';
        return type;
    };
    
    // Zusammenfassung mit Absenden-Button
    const renderSummaryStep = () => (
      <Box>
        <Typography variant="h6" mb={2}>Zusammenfassung</Typography>
        <Typography variant="subtitle1">Titel:</Typography>
        <Typography mb={1}>{form.title || <span style={{color:'#888'}}>Kein Titel</span>}</Typography>
        <Typography variant="subtitle1">Beschreibung:</Typography>
        <Typography mb={2}>{form.description || <span style={{color:'#888'}}>Keine Beschreibung</span>}</Typography>
        <Typography variant="subtitle1">Fragen:</Typography>
        <Stack spacing={2} mt={1} mb={2}>
          {form.questions.length === 0 && <Typography color="text.secondary">Noch keine Fragen hinzugefügt.</Typography>}
          {form.questions.map((q, idx) => (
            <Box key={q.id} sx={{ p: 2, border: '1px solid #eee', borderRadius: 1 }}>
              <Typography><b>{idx + 1}. {q.questionText}</b> <span style={{ color: '#888', fontSize: 13 }}>({getTypeName(q.type)})</span></Typography>
              {(q.type === 'single_choice' || q.type === 'multiple_choice') && q.options.length > 0 && (
                <Typography variant="caption" color="text.secondary">
                  Optionen: {q.options.map(id => availableOptions.find(o => o.id === id)?.optionText).filter(Boolean).join(', ')}
                </Typography>
              )}
            </Box>
          ))}
        </Stack>
        {submitSuccess && <Alert severity="success" sx={{ mb: 2 }}>Umfrage erfolgreich erstellt!</Alert>}
        {submitError && <Alert severity="error" sx={{ mb: 2 }}>{submitError}</Alert>}
      </Box>
    );

    const renderStepContent = () => {
      switch (activeStep) {
        case 0:
          return renderGeneralStep();
        case 1:
          return renderQuestionsStep();
        case 2:
          return renderSummaryStep();
        default:
          return null;
      }
    };

    return (
      <BaseModal
        open={open}
        onClose={onClose}
        title={editSurvey ? 'Umfrage bearbeiten' : 'Neue Umfrage erstellen'}
        maxWidth="md"
        actions={
          <>
            <Button disabled={activeStep === 0} onClick={handleBack}>
              Zurück
            </Button>
            {activeStep < steps.length - 1 && (
              <Button variant="contained" onClick={handleNext}>
                Weiter
              </Button>
            )}
            {activeStep === steps.length - 1 && (
              <Button
                variant="contained"
                color="primary"
                onClick={e => {
                  e.preventDefault();
                  if (!currentStepError) handleSubmit();
                }}
                disabled={isSubmitting || submitSuccess || !!currentStepError}
              >
                {isSubmitting ? 'Speichere...' : 'Fertigstellen'}
              </Button>
            )}
          </>
        }
      >
        <Stepper activeStep={activeStep} sx={{ mb: 3 }}>
          {steps.map(label => (
            <Step key={label}>
              <StepLabel>{label}</StepLabel>
            </Step>
          ))}
        </Stepper>
        {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
        {(touched[activeStep] && currentStepError) && <Alert severity="error" sx={{ mb: 2 }}>{currentStepError}</Alert>}
        {optionsLoadError && <Alert severity="error" sx={{ mb: 2 }}>{optionsLoadError}</Alert>}
        {typesLoadError && <Alert severity="error" sx={{ mb: 2 }}>{typesLoadError}</Alert>}
        {renderStepContent()}
      </BaseModal>
    );
  };

  export default SurveyCreateWizard;
