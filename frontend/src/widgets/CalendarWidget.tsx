import React, { useEffect, useState } from 'react';
import { EventDetailsModal } from '../modals/EventDetailsModal';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import CircularProgress from '@mui/material/CircularProgress';
import { Calendar as BigCalendar, momentLocalizer, Views } from 'react-big-calendar';
import moment from 'moment';
import 'moment/locale/de';
import { apiJson } from '../utils/api';
import { useWidgetRefresh } from '../context/WidgetRefreshContext';

import 'react-big-calendar/lib/css/react-big-calendar.css';

moment.locale('de', { week: { dow: 1 } });
const localizer = momentLocalizer(moment);

type CalendarEvent = {
  id: number;
  title: string;
  start: string;
  end: string;
  description?: string;
  type?: { name?: string; color?: string };
  location?: { name?: string };
};

export const CalendarWidget: React.FC<{ config?: any; widgetId?: string }> = ({ config, widgetId }) => {
  const { getRefreshTrigger } = useWidgetRefresh();
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [view, setView] = useState(Views.MONTH);
  const [date, setDate] = useState(new Date());
  const [selectedEvent, setSelectedEvent] = useState<CalendarEvent | null>(null);

  const refreshTrigger = widgetId ? getRefreshTrigger(widgetId) : 0;

  useEffect(() => {
    // Lade Events für den sichtbaren Zeitraum
    const start = moment(date).startOf(view === 'month' ? 'month' : (view as any)).toISOString();
    const end = moment(date).endOf(view === 'month' ? 'month' : (view as any)).toISOString();
    setLoading(true);
    setError(null);
    apiJson(`/api/calendar/events?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`)
      .then((data) => {
        setEvents(data.map((ev: any) => ({
          ...ev,
          start: new Date(ev.start),
          end: new Date(ev.end)
        })));
        setLoading(false);
      })
      .catch((e) => {
        setError(e.message);
        setLoading(false);
      });
  }, [date, view, refreshTrigger]);

  const eventStyleGetter = (event: CalendarEvent) => {
    let backgroundColor = event.type?.color || '#1976d2';
    return {
      style: {
        backgroundColor,
        borderRadius: '4px',
        opacity: 0.85,
        color: 'white',
        border: '0px',
        display: 'block',
      }
    };
  };

  if (loading) return <Box sx={{ display: 'flex', justifyContent: 'center', py: 3 }}><CircularProgress size={28} /></Box>;
  if (error) return <Typography color="error">{error}</Typography>;

  return (
    <>
      <Box sx={{ width: '100%' }}>
        <BigCalendar
          localizer={localizer}
          events={events}
          startAccessor="start"
          endAccessor="end"
          titleAccessor="title"
          view={view}
          onView={setView}
          date={date}
          onNavigate={setDate}
          eventPropGetter={eventStyleGetter}
          messages={{
            allDay: 'Ganztägig',
            previous: 'Zurück',
            next: 'Weiter',
            today: 'Heute',
            month: 'Monat',
            week: 'Woche',
            day: 'Tag',
            agenda: 'Agenda',
            date: 'Datum',
            time: 'Zeit',
            event: 'Ereignis',
            noEventsInRange: 'Keine Termine in diesem Zeitraum.',
            showMore: (total: number) => `+ ${total} weitere`
          }}
          culture="de"
          style={{ height: 400 }}
          views={['month', 'week', 'day', 'agenda']}
          step={30}
          showMultiDayTimes
          defaultDate={new Date()}
          onSelectEvent={ev => setSelectedEvent(ev)}
        />
      </Box>
      <EventDetailsModal open={!!selectedEvent} onClose={() => setSelectedEvent(null)} event={selectedEvent} />
    </>
  );
};
