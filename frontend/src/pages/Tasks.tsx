import React, { useEffect, useState } from 'react';
import { Button, Stack, Typography, Box, Paper, IconButton, List, ListItem, ListItemText, ListItemSecondaryAction } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { apiJson } from '../utils/api';
import TaskEditModal from '../modals/TaskEditModal';

interface User {
  id: number;
  firstName: string;
  lastName: string;
}

interface Task {
  id: number;
  title: string;
  description?: string;
  isRecurring: boolean;
  recurrenceRule?: string;
  assignments: Assignment[];
}

interface Assignment {
  id: number;
  user: User;
  assignedDate: string;
  status: string;
}

const Tasks: React.FC = () => {
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [editTask, setEditTask] = useState<Task | null>(null);
  const [showModal, setShowModal] = useState(false);

  const fetchTasks = async () => {
    setLoading(true);
    try {
      const data = await apiJson<{ tasks: Task[] }>('/api/tasks');
      setTasks(data.tasks);
    } catch (e) {
      // Fehlerbehandlung
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchTasks();
  }, []);

  const handleEdit = (task: Task) => {
    setEditTask(task);
    setShowModal(true);
  };

  const handleDelete = (task: Task) => {
    setEditTask(task);
    setShowModal(true);
  };

  const handleAdd = () => {
    setEditTask(null);
    setShowModal(true);
  };

  const handleModalClose = (changed: boolean) => {
    setShowModal(false);
    setEditTask(null);
    if (changed) fetchTasks();
  };

  return (
    <Box p={2}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
      <Typography variant="h4" gutterBottom>Aufgaben verwalten</Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { handleAdd() }}>
          Neue Aufgabe erstellen
        </Button>
      </Stack>
      <Paper>
        <List>
          {tasks.map(task => (
            <ListItem key={task.id} divider>
              <ListItemText
                primary={task.title}
                secondary={task.description}
              />
              <ListItemSecondaryAction>
                <IconButton edge="end" onClick={() => handleEdit(task)}><EditIcon /></IconButton>
                <IconButton edge="end" onClick={() => handleDelete(task)}><DeleteIcon /></IconButton>
              </ListItemSecondaryAction>
            </ListItem>
          ))}
        </List>
      </Paper>
      {showModal && (
        <TaskEditModal
          open={showModal}
          onClose={handleModalClose}
          task={editTask}
        />
      )}
    </Box>
  );
};

export default Tasks;
