import React, { useEffect, useState } from 'react';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import CircularProgress from '@mui/material/CircularProgress';
import { Bar, Line, Pie, Doughnut, Radar, PolarArea, Bubble, Scatter } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, RadialLinearScale, Tooltip, Legend, Title } from 'chart.js';
import { apiJson } from '../utils/api';
import { useWidgetRefresh } from '../context/WidgetRefreshContext';

// Chart.js Komponenten registrieren (nur einmal pro App nötig, aber hier zur Sicherheit)
ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, RadialLinearScale, Tooltip, Legend, Title);

interface ReportData {
  labels: string[];
  datasets: Array<{
    label: string;
    data: number[];
    color?: string;
    [key: string]: any;
  }>;
  diagramType: string;
}

export const ReportWidget: React.FC<{ config?: any; reportId?: number; widgetId?: string }> = ({ config, reportId, widgetId }) => {
  const { getRefreshTrigger } = useWidgetRefresh();
  const [data, setData] = useState<ReportData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const refreshTrigger = widgetId ? getRefreshTrigger(widgetId) : 0;

  useEffect(() => {
    // If config is provided directly, use it (for preview)
    if (config && config.labels && config.datasets) {
      setData(config);
      setLoading(false);
      return;
    }
    
    // Otherwise load from API using reportId
    if (!reportId) return;
    setLoading(true);
    setError(null);
    apiJson(`/api/report/widget/${reportId}/data`)
      .then(setData)
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false));
  }, [reportId, refreshTrigger, config]);

  if (!reportId && !config) {
    return <Typography color="text.secondary">Kein Report ausgewählt.</Typography>;
  }
  if (loading) {
    return <Box sx={{ display: 'flex', justifyContent: 'center', py: 3 }}><CircularProgress size={28} /></Box>;
  }
  if (error) {
    return <Typography color="error">{error}</Typography>;
  }
  if (!data || !data.labels || !data.datasets || !data.datasets.length) {
    return <Typography color="text.secondary">Keine Daten für diesen Report.</Typography>;
  }

  const defaultColors = [
    '#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#0099C6', '#DD4477', '#66AA00', '#B82E2E', '#316395',
    '#994499', '#22AA99', '#AAAA11', '#6633CC', '#E67300', '#8B0707', '#329262', '#5574A6', '#3B3EAC'
  ];

  const chartData = {
    labels: data.labels,
    datasets: data.datasets.map((ds, i) => {
      // Für Pie/PolarArea: backgroundColor als Array, sonst als String
      const isPie = ["pie", "doughnut", "polararea"].includes((data.diagramType || '').toLowerCase());
      return {
        ...ds,
        backgroundColor: ds.backgroundColor || (isPie
          ? data.labels.map((_, idx) => defaultColors[idx % defaultColors.length])
          : defaultColors[i % defaultColors.length]),
        borderColor: ds.borderColor || (isPie
          ? data.labels.map((_, idx) => defaultColors[idx % defaultColors.length])
          : defaultColors[i % defaultColors.length]),
        borderWidth: 2,
      };
    })
  };

  const type = (data.diagramType || '').toLowerCase();

  // Chart.js Optionen (Default interaktive Legende, Responsive, etc.)
  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: "top" as const,
        labels: {
          usePointStyle: true,
          // Größere Klickfläche für Mobile
          padding: 18,
        },
        // Chart.js handled hover/highlight automatisch
      },
      title: { display: false },
      tooltip: { enabled: true },
    },
    hover: {
      mode: "nearest" as const,
      intersect: true,
    },
  };

  // Responsive Chart: Container-Box steuert die Größe
  const chartProps = { data: chartData, options };

  return (
    <Box sx={{ width: '100%', height: { xs: 260, sm: 320, md: 360, lg: 400 }, minHeight: 220 }}>
      {(() => {
        switch (type) {
          case 'bar':
            return <Bar {...chartProps} />;
          case 'line':
            return <Line {...chartProps} />;
          case 'pie':
            return <Pie {...chartProps} />;
          case 'doughnut':
            return <Doughnut {...chartProps} />;
          case 'radar':
            return <Radar {...chartProps} />;
          case 'polararea':
            return <PolarArea {...chartProps} />;
          case 'bubble':
            return <Bubble {...chartProps} />;
          case 'scatter':
            return <Scatter {...chartProps} />;
          default:
            return <Bar {...chartProps} />;
        }
      })()}
    </Box>
  );
};
