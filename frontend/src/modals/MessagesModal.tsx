import React, { useState, useEffect } from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Divider from '@mui/material/Divider';
import IconButton from '@mui/material/IconButton';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Paper from '@mui/material/Paper';
import Chip from '@mui/material/Chip';
import Alert from '@mui/material/Alert';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import Autocomplete from '@mui/material/Autocomplete';
// Material UI Icons als Alternative zu FontAwesome
import MailIcon from '@mui/icons-material/Mail';
import MailOutlineIcon from '@mui/icons-material/MailOutline';
import EditIcon from '@mui/icons-material/Edit';
import InboxIcon from '@mui/icons-material/Inbox';
import SendIcon from '@mui/icons-material/Send';
import GroupIcon from '@mui/icons-material/Group';
import AddIcon from '@mui/icons-material/Add';
import ReplyIcon from '@mui/icons-material/Reply';
import CloseIcon from '@mui/icons-material/Close';
import { apiJson } from '../utils/api';

interface Message {
  id: string;
  subject: string;
  sender: string;
  sentAt: string;
  isRead: boolean;
  content?: string;
  recipients?: Array<{ id: string; name: string }>;
}

interface User {
  id: string;
  fullName: string;
  email: string;
}

interface MessageGroup {
  id: string;
  name: string;
  memberCount: number;
}

interface MessagesModalProps {
  open: boolean;
  onClose: () => void;
}

export const MessagesModal: React.FC<MessagesModalProps> = ({ open, onClose }) => {
  const [messages, setMessages] = useState<Message[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [groups, setGroups] = useState<MessageGroup[]>([]);
  const [selectedMessage, setSelectedMessage] = useState<Message | null>(null);
  const [showComposeModal, setShowComposeModal] = useState(false);
  const [showMessageDetail, setShowMessageDetail] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  // Compose Form State
  const [composeForm, setComposeForm] = useState({
    recipients: [] as User[],
    group: '',
    subject: '',
    content: ''
  });

  // Load data when modal opens
  useEffect(() => {
    if (open) {
      loadMessages();
      loadUsers();
      loadGroups();
    }
  }, [open]);

  const loadMessages = async () => {
    try {
      setLoading(true);
      const response = await apiJson('/api/messages');
      setMessages(response.messages || []);
    } catch (err) {
      setError('Fehler beim Laden der Nachrichten');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const loadUsers = async () => {
    try {
      const response = await apiJson('/api/users');
      setUsers(response.users || []);
    } catch (err) {
      console.error('Fehler beim Laden der Benutzer:', err);
    }
  };

  const loadGroups = async () => {
    try {
      const response = await apiJson('/api/message-groups');
      setGroups(response.groups || []);
    } catch (err) {
      console.error('Fehler beim Laden der Gruppen:', err);
    }
  };

  const handleMessageClick = async (messageId: string) => {
    try {
      const response = await apiJson(`/api/messages/${messageId}`);
      setSelectedMessage(response);
      setShowMessageDetail(true);
      
      // Mark as read and reload messages
      await loadMessages();
    } catch (err) {
      setError('Fehler beim Laden der Nachricht');
      console.error(err);
    }
  };

  const handleSendMessage = async () => {
    try {
      setLoading(true);
      setError(null);

      const payload = {
        recipients: composeForm.recipients.map(r => r.id),
        group_id: composeForm.group || null,
        subject: composeForm.subject,
        content: composeForm.content
      };

      await apiJson('/api/messages', {
        method: 'POST',
        body: payload
      });

      // Reset form and close modal
      setComposeForm({
        recipients: [],
        group: '',
        subject: '',
        content: ''
      });
      setShowComposeModal(false);
      
      // Reload messages
      await loadMessages();
    } catch (err) {
      setError('Fehler beim Senden der Nachricht');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleReply = () => {
    if (selectedMessage) {
      setComposeForm({
        recipients: [{ id: '', fullName: selectedMessage.sender, email: '' }],
        group: '',
        subject: `Re: ${selectedMessage.subject}`,
        content: `\n\n--- Ursprüngliche Nachricht ---\nVon: ${selectedMessage.sender}\nBetreff: ${selectedMessage.subject}\n\n${selectedMessage.content || ''}`
      });
      setShowMessageDetail(false);
      setShowComposeModal(true);
    }
  };

  const unreadCount = messages.filter(m => !m.isRead).length;

  return (
    <>
      {/* Main Messages Modal */}
      <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
        <DialogTitle>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Typography variant="h6">Nachrichten</Typography>
            <Button
              variant="contained"
              startIcon={<EditIcon />}
              onClick={() => setShowComposeModal(true)}
            >
              Neue Nachricht
            </Button>
          </Box>
        </DialogTitle>
        
        <DialogContent>
          <Box display="flex" gap={2} height="500px">
            {/* Left Sidebar */}
            <Box width="250px" bgcolor="grey.50" p={2} borderRadius={1}>
              <Box mb={2}>
                <Chip
                  icon={<InboxIcon />}
                  label={`Posteingang (${unreadCount})`}
                  color="primary"
                  variant="filled"
                />
              </Box>
              
              <Divider sx={{ my: 2 }} />
              
              <Typography variant="subtitle2" gutterBottom>
                Nachrichtengruppen
              </Typography>
              
              {groups.map(group => (
                <Chip
                  key={group.id}
                  icon={<GroupIcon />}
                  label={`${group.name} (${group.memberCount})`}
                  variant="outlined"
                  size="small"
                  sx={{ mb: 1, display: 'flex', justifyContent: 'flex-start' }}
                />
              ))}
            </Box>

            {/* Messages List */}
            <Box flex={1}>
              {error && (
                <Alert severity="error" sx={{ mb: 2 }}>
                  {error}
                </Alert>
              )}
              
              <TableContainer component={Paper}>
                <Table size="small">
                  <TableHead>
                    <TableRow>
                      <TableCell width="30px"></TableCell>
                      <TableCell>Betreff</TableCell>
                      <TableCell>Von</TableCell>
                      <TableCell>Datum</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {messages.map((message) => (
                      <TableRow
                        key={message.id}
                        hover
                        onClick={() => handleMessageClick(message.id)}
                        sx={{ cursor: 'pointer' }}
                      >
                        <TableCell>
                          {message.isRead ? <MailOutlineIcon color="disabled" /> : <MailIcon color="primary" />}
                        </TableCell>
                        <TableCell>
                          <Typography
                            variant="body2"
                            fontWeight={message.isRead ? 'normal' : 'bold'}
                          >
                            {message.subject}
                          </Typography>
                        </TableCell>
                        <TableCell>{message.sender}</TableCell>
                        <TableCell>
                          {new Date(message.sentAt).toLocaleString()}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            </Box>
          </Box>
        </DialogContent>
        
        <DialogActions>
          <Button onClick={onClose}>Schließen</Button>
        </DialogActions>
      </Dialog>

      {/* Compose Message Modal */}
      <Dialog open={showComposeModal} onClose={() => setShowComposeModal(false)} maxWidth="md" fullWidth>
        <DialogTitle>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            Neue Nachricht
            <IconButton onClick={() => setShowComposeModal(false)}>
              <CloseIcon />
            </IconButton>
          </Box>
        </DialogTitle>
        
        <DialogContent>
          <Box display="flex" flexDirection="column" gap={3} pt={1}>
            <Autocomplete
              multiple
              options={users}
              getOptionLabel={(option) => `${option.fullName} (${option.email})`}
              value={composeForm.recipients}
              onChange={(_, newValue) =>
                setComposeForm(prev => ({ ...prev, recipients: newValue }))
              }
              renderInput={(params) => (
                <TextField {...params} label="Empfänger" variant="outlined" />
              )}
            />

            <FormControl>
              <InputLabel>Oder Gruppe</InputLabel>
              <Select
                value={composeForm.group}
                onChange={(e) => setComposeForm(prev => ({ ...prev, group: e.target.value }))}
                label="Oder Gruppe"
              >
                <MenuItem value="">Keine Gruppe</MenuItem>
                {groups.map(group => (
                  <MenuItem key={group.id} value={group.id}>
                    {group.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>

            <TextField
              label="Betreff"
              variant="outlined"
              fullWidth
              value={composeForm.subject}
              onChange={(e) => setComposeForm(prev => ({ ...prev, subject: e.target.value }))}
              required
            />

            <TextField
              label="Nachricht"
              variant="outlined"
              fullWidth
              multiline
              rows={6}
              value={composeForm.content}
              onChange={(e) => setComposeForm(prev => ({ ...prev, content: e.target.value }))}
              required
            />

            {error && (
              <Alert severity="error">{error}</Alert>
            )}
          </Box>
        </DialogContent>
        
        <DialogActions>
          <Button onClick={() => setShowComposeModal(false)}>Abbrechen</Button>
          <Button 
            variant="contained" 
            onClick={handleSendMessage}
            disabled={loading || !composeForm.subject || !composeForm.content}
          >
            {loading ? 'Senden...' : 'Senden'}
          </Button>
        </DialogActions>
      </Dialog>

      {/* Message Detail Modal */}
      <Dialog open={showMessageDetail} onClose={() => setShowMessageDetail(false)} maxWidth="md" fullWidth>
        <DialogTitle>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            {selectedMessage?.subject}
            <IconButton onClick={() => setShowMessageDetail(false)}>
              <CloseIcon />
            </IconButton>
          </Box>
        </DialogTitle>
        
        <DialogContent>
          {selectedMessage && (
            <Box>
              <Box display="flex" justifyContent="space-between" mb={2}>
                <Typography variant="body2">
                  <strong>Von:</strong> {selectedMessage.sender}
                </Typography>
                <Typography variant="body2">
                  <strong>Datum:</strong> {new Date(selectedMessage.sentAt).toLocaleString()}
                </Typography>
              </Box>
              
              {selectedMessage.recipients && (
                <Typography variant="body2" mb={2}>
                  <strong>An:</strong> {selectedMessage.recipients.map(r => r.name).join(', ')}
                </Typography>
              )}
              
              <Divider sx={{ my: 2 }} />
              
              <Typography variant="body1" component="div" sx={{ whiteSpace: 'pre-wrap' }}>
                {selectedMessage.content}
              </Typography>
            </Box>
          )}
        </DialogContent>
        
        <DialogActions>
          <Button onClick={() => setShowMessageDetail(false)}>Schließen</Button>
          <Button 
            variant="contained" 
            startIcon={<ReplyIcon />}
            onClick={handleReply}
          >
            Antworten
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default MessagesModal;
