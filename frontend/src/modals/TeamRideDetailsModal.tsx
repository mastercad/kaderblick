import React, { useState, useEffect } from 'react';
import Alert from '@mui/material/Alert';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import CircularProgress from '@mui/material/CircularProgress';
import Stack from '@mui/material/Stack';
import AddTeamRideModal from './AddTeamRideModal';
import { apiJson } from '../utils/api';
import BaseModal from './BaseModal';

interface TeamRide {
  id: number;
  driver: string;
  seats: number;
  availableSeats: number;
  passengers: Array<{ id: number; name: string }>;
  note?: string;
}

interface TeamRideDetailsModalProps {
  open: boolean;
  onClose: () => void;
  eventId: number | null;
}

const TeamRideDetailsModal: React.FC<TeamRideDetailsModalProps> = ({ open, onClose, eventId }) => {
  const [rides, setRides] = useState<TeamRide[]>([]);
  const [loading, setLoading] = useState(false);
  const [bookingRideId, setBookingRideId] = useState<number | null>(null);
  const [addTeamRideModalOpen, setAddTeamRideModalOpen] = useState(false);
  const [bookingError, setBookingError] = useState<string | null>(null);

  useEffect(() => {
    if (!eventId || !open) return;
    setLoading(true);
    apiJson(`/api/teamrides/event/${eventId}`)
      .then(data => setRides(data.rides || []))
      .catch(() => setRides([]))
      .finally(() => setLoading(false));
  }, [eventId, open]);

  const handleBookSeat = (rideId: number) => {
    setBookingError(null);
    setBookingRideId(rideId);
    apiJson(`/api/teamrides/book/${rideId}`, { method: 'POST' })
      .then(response => {
        if (response.error) {
          setBookingError(response.error);
          return;
        }
        // Refresh rides
        return apiJson(`/api/teamrides/event/${eventId}`)
          .then(data => setRides(data.rides || []));
      })
      .catch(() => setBookingError('Unbekannter Fehler beim Buchen.'))
      .finally(() => setBookingRideId(null));
  };

  return (
    <>
      <BaseModal
        open={open}
        onClose={onClose}
        title="Mitfahrgelegenheiten"
        maxWidth="sm"
        actions={
          <>
            <Button onClick={onClose} color="primary" variant="contained">
              Schließen
            </Button>
            <Button onClick={() => setAddTeamRideModalOpen(true)} color="secondary" variant="outlined">
              Mitfahrgelegenheit anbieten
            </Button>
          </>
        }
      >
        {bookingError && (
          <Alert severity="error" sx={{ mb: 2 }}>{bookingError}</Alert>
        )}
        {loading ? (
          <Box display="flex" justifyContent="center" my={2}><CircularProgress /></Box>
        ) : (
          <>
            {rides.length === 0 ? (
              <Typography variant="body2" color="text.secondary">Keine Mitfahrgelegenheiten gefunden.</Typography>
            ) : (
              <Stack spacing={2}>
                {rides.map(ride => (
                  <Box key={ride.id} border={1} borderRadius={2} p={2}>
                    <Typography variant="subtitle2">Fahrer: {ride.driver}</Typography>
                    <Typography variant="body2">Plätze: {ride.seats} | Frei: {ride.availableSeats}</Typography>
                    {ride.note && <Typography variant="body2">Notiz: {ride.note}</Typography>}
                    <Typography variant="body2">Mitfahrer:</Typography>
                    <Stack direction="row" spacing={1}>
                      {ride.passengers.map(p => (
                        <Box key={p.id} px={1}>{p.name}</Box>
                      ))}
                    </Stack>
                    <Button
                      variant="contained"
                      color="primary"
                      disabled={ride.availableSeats === 0 || bookingRideId === ride.id}
                      onClick={() => handleBookSeat(ride.id)}
                      sx={{ mt: 1 }}
                    >
                      {bookingRideId === ride.id ? 'Buchen...' : 'Platz buchen'}
                    </Button>
                  </Box>
                ))}
              </Stack>
            )}
          </>
        )}
      </BaseModal>

      <AddTeamRideModal open={addTeamRideModalOpen} onClose={() => setAddTeamRideModalOpen(false)} eventId={eventId} onAdded={() => {
        if (eventId) {
          setLoading(true);
          apiJson(`/api/teamrides/event/${eventId}`)
            .then(data => setRides(data.rides || []))
            .catch(() => setRides([]))
            .finally(() => setLoading(false));
        }
      }} />
    </>
  );
};

export default TeamRideDetailsModal;
