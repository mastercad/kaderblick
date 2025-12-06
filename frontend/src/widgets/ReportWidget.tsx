import React, { useEffect, useState } from 'react';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import CircularProgress from '@mui/material/CircularProgress';
import { Bar, Line, Pie, Doughnut, Radar, PolarArea, Bubble, Scatter } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, RadialLinearScale, Tooltip, Legend, Title, ChartOptions, Filler } from 'chart.js';
import { apiJson } from '../utils/api';
import { useWidgetRefresh } from '../context/WidgetRefreshContext';

// Chart.js Komponenten registrieren (nur einmal pro App nötig, aber hier zur Sicherheit)
ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, RadialLinearScale, Tooltip, Legend, Title, Filler);

// Boxplot drawing plugin: expects datasets where each dataset.data is an array of numbers
const boxplotPlugin = {
  id: 'boxplotPlugin',
  afterDatasetsDraw: (chart: any) => {
    if ((chart.data && chart.options && chart.options._drawBoxplots !== true) && chart.config.type !== undefined) {
      // not enabled globally
      return;
    }
    const ctx = chart.ctx;
    const yScale = chart.scales['y'];
    const xScale = chart.scales['x'];
    chart.data.datasets.forEach((ds: any, dsIndex: number) => {
      // ds.data expected to be an array of arrays or numbers -- we treat as per-index values
      if (!Array.isArray(ds.data) || ds._boxplot !== true) return;
      // for each label index, compute quartiles from ds.data[index] if it's an array
      ds.data.forEach((entry: any, idx: number) => {
        if (!Array.isArray(entry)) return;
        const arr = entry.map((v: any) => Number(v)).filter((v: number) => !isNaN(v)).sort((a: number, b: number) => a - b);
        if (arr.length === 0) return;
        const q = (arr: number[], p: number) => {
          const pos = (arr.length - 1) * p;
          const base = Math.floor(pos);
          const rest = pos - base;
          if (arr[base + 1] !== undefined) return arr[base] + rest * (arr[base + 1] - arr[base]);
          return arr[base];
        };
        const min = arr[0];
        const q1 = q(arr, 0.25);
        const median = q(arr, 0.5);
        const q3 = q(arr, 0.75);
        const max = arr[arr.length - 1];

        // compute x coordinate for this category index
        const x = xScale.getPixelForTick(idx);
        // box width
        const boxWidth = Math.min(40, (xScale.getPixelForTick(Math.min(chart.data.labels.length -1, idx + 1)) - xScale.getPixelForTick(idx)) * 0.6 || 20);
        // y positions
        const yMin = yScale.getPixelForValue(min);
        const yQ1 = yScale.getPixelForValue(q1);
        const yMed = yScale.getPixelForValue(median);
        const yQ3 = yScale.getPixelForValue(q3);
        const yMax = yScale.getPixelForValue(max);

        ctx.save();
        ctx.fillStyle = ds.backgroundColor || 'rgba(100,100,200,0.6)';
        ctx.strokeStyle = ds.borderColor || 'rgba(50,50,120,0.9)';
        ctx.lineWidth = ds.borderWidth || 1.5;

        // whiskers
        ctx.beginPath();
        ctx.moveTo(x, yMin);
        ctx.lineTo(x, yQ1);
        ctx.moveTo(x, yQ3);
        ctx.lineTo(x, yMax);
        ctx.stroke();

        // box
        ctx.beginPath();
        ctx.rect(x - boxWidth / 2, yQ3, boxWidth, Math.max(1, yQ1 - yQ3));
        ctx.fill();
        ctx.stroke();

        // median
        ctx.beginPath();
        ctx.moveTo(x - boxWidth / 2, yMed);
        ctx.lineTo(x + boxWidth / 2, yMed);
        ctx.stroke();

        ctx.restore();
      });
    });
  }
};

ChartJS.register(boxplotPlugin as any);

// Heatmap overlay plugin: draws an offscreen canvas (with smoothed blobs) into the chart area before datasets
const heatmapOverlayPlugin = {
  id: 'heatmapOverlay',
  beforeDatasetsDraw: (chart: any) => {
    const opts = chart.options?.plugins?.heatmapOverlay;
    if (!opts || !opts.canvas) return;
    const canvas = opts.canvas as HTMLCanvasElement;
    const chartArea = chart.chartArea;
    if (!chartArea) return;
    chart.ctx.save();
    chart.ctx.globalCompositeOperation = 'source-over';
    chart.ctx.drawImage(canvas, chartArea.left, chartArea.top, chartArea.right - chartArea.left, chartArea.bottom - chartArea.top);
    chart.ctx.restore();
  }
};

ChartJS.register(heatmapOverlayPlugin as any);

// Heatmap grid (classic) plugin: draws colored rectangles per grid cell
const heatmapGridPlugin = {
  id: 'heatmapGrid',
  beforeDatasetsDraw: (chart: any) => {
    const opts = chart.options?.plugins?.heatmapGrid;
    if (!opts || !opts.gridPoints) return;
    const gridPoints = opts.gridPoints as Array<{ r: number; c: number; v: number; intensity: number }>;
    const labels = opts.labels || [];
    const dsets = opts.dsets || [];
    const chartArea = chart.chartArea;
    if (!chartArea) return;
    const ctx = chart.ctx;
    const cellW = (chartArea.right - chartArea.left) / Math.max(1, labels.length);
    const cellH = (chartArea.bottom - chartArea.top) / Math.max(1, dsets.length);
    ctx.save();
    // local safe color mapper in case heatColor is not available in this scope
    const mapColor = (intensity: number) => {
      const t = Math.max(0, Math.min(1, intensity));
      // try to use module-level heatColor if present
      try {
        // @ts-ignore
        if (typeof heatColor === 'function') return heatColor(t);
      } catch (e) {}
      const alpha = t * 0.6;
      const r = Math.round(255 * t);
      const g = Math.round(140 * (1 - t));
      return `rgba(${r},${g},0,${alpha})`;
    };
    gridPoints.forEach(p => {
      const x = chartArea.left + p.c * cellW;
      const y = chartArea.top + p.r * cellH;
      // color by intensity
      ctx.fillStyle = mapColor(p.intensity);
      ctx.fillRect(x, y, cellW, cellH);
    });
    ctx.restore();
  }
};

ChartJS.register(heatmapGridPlugin as any);

// Generate an offscreen canvas with radial blobs for smoothing
function generateHeatmapCanvas(points: Array<{ x: number; y: number; intensity: number }>, width = 400, height = 300, radius = 40) {
  const c = document.createElement('canvas');
  c.width = width;
  c.height = height;
  const ctx = c.getContext('2d')!;
  ctx.clearRect(0, 0, width, height);
  ctx.globalCompositeOperation = 'lighter';
  points.forEach(p => {
    const px = (p.x / 100) * width;
    const py = (p.y / 100) * height;
    const grad = ctx.createRadialGradient(px, py, 0, px, py, radius);
    const alpha = Math.max(0, Math.min(1, p.intensity));
    grad.addColorStop(0, `rgba(255,255,255,${alpha})`);
    grad.addColorStop(0.2, `rgba(255,200,0,${alpha * 0.9})`);
    grad.addColorStop(0.5, `rgba(255,80,0,${alpha * 0.6})`);
    grad.addColorStop(1, `rgba(0,0,0,0)`);
    ctx.fillStyle = grad as any;
    ctx.beginPath();
    ctx.arc(px, py, radius, 0, Math.PI * 2);
    ctx.fill();
  });
  // apply a color map by sampling and remapping pixels
  const image = ctx.getImageData(0, 0, width, height);
  for (let i = 0; i < image.data.length; i += 4) {
    const alpha = image.data[i + 3] / 255; // using alpha as intensity
    if (alpha <= 0) continue;
    // map alpha to heatColor
    const color = (function (t: number) {
      const r = Math.round(255 * Math.min(1, Math.max(0, 2 * t)));
      const g = Math.round(255 * Math.min(1, Math.max(0, 2 * (1 - Math.abs(t - 0.5)))));
      const b = Math.round(255 * Math.min(1, Math.max(0, 2 * (1 - t))));
      return [r, g, b];
    })(alpha);
    image.data[i] = color[0];
    image.data[i + 1] = color[1];
    image.data[i + 2] = color[2];
    image.data[i + 3] = Math.round(255 * Math.min(1, alpha));
  }
  ctx.putImageData(image, 0, 0);
  return c;
}

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

  const rgbaColors = [
    'rgba(51,102,204,0.35)', 'rgba(220,57,18,0.35)', 'rgba(255,153,0,0.35)', 'rgba(16,150,24,0.35)',
    'rgba(153,0,153,0.35)', 'rgba(0,153,198,0.35)', 'rgba(221,68,119,0.35)', 'rgba(102,170,0,0.35)',
    'rgba(184,46,46,0.35)', 'rgba(49,99,149,0.35)'
  ];

  // Determine effective diagram type (support preview objects where diagramType may be under config)
  const effectiveType = ((data.diagramType as string) || (data as any).config?.diagramType || '').toLowerCase();

  const chartData = {
    labels: data.labels,
    datasets: data.datasets.map((ds, i) => {
      // Für Pie/PolarArea: backgroundColor als Array, sonst als String
      const isPie = ["pie", "doughnut", "polararea"].includes(effectiveType);
      const isArea = effectiveType === 'area';

      // compute sensible colors
      const computedBackground = ds.backgroundColor || (isPie
        ? data.labels.map((_, idx) => defaultColors[idx % defaultColors.length])
        : (isArea ? rgbaColors[i % rgbaColors.length] : defaultColors[i % defaultColors.length]));
      const computedBorder = ds.borderColor || (isPie
        ? data.labels.map((_, idx) => defaultColors[idx % defaultColors.length])
        : defaultColors[i % defaultColors.length]);

      // For area charts, explicitly enforce filling and a semi-transparent background
      const enforcedProps: any = {};
      if (isArea) {
        enforcedProps.fill = ds.fill === false ? false : true; // allow explicit opt-out
        enforcedProps.tension = ds.tension ?? 0.3;
        // if background is a single hex (no alpha), prefer rgbaColors to ensure visible fill
        if (!ds.backgroundColor) {
          enforcedProps.backgroundColor = computedBackground;
        }
      }

      return {
        ...ds,
        backgroundColor: ds.backgroundColor || computedBackground,
        borderColor: ds.borderColor || computedBorder,
        borderWidth: ds.borderWidth ?? 2,
        ...enforcedProps,
      };
    })
  };

  const type = effectiveType;
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

  // If config requests moving average, compute overlay datasets (non-stacked) from raw datasets
  const applyMovingAverage = (datasets: any[], windowSize: number) => {
    const maDatasets: any[] = [];
    datasets.forEach((ds, idx) => {
      const raw = ds.data as number[];
      if (!Array.isArray(raw)) return;
      const ma: number[] = [];
      for (let i = 0; i < raw.length; i++) {
        let sum = 0;
        let count = 0;
        for (let j = Math.max(0, i - windowSize + 1); j <= i; j++) {
          const v = Number(raw[j]);
          if (!isNaN(v)) {
            sum += v;
            count++;
          }
        }
        ma.push(count > 0 ? sum / count : 0);
      }
      maDatasets.push({
        label: `${ds.label} (MA ${windowSize})`,
        data: ma,
        borderColor: ds.borderColor || defaultColors[idx % defaultColors.length],
        backgroundColor: 'transparent',
        borderWidth: 2,
        borderDash: [6, 4],
        fill: false,
        yAxisID: ds.yAxisID ?? 'y',
        type: 'line',
      });
    });
    return maDatasets;
  };

  // helper: color map for heat intensity (0..1) -> rgba
  const heatColor = (t: number) => {
    const r = Math.round(255 * Math.min(1, Math.max(0, 2 * t)));
    const g = Math.round(255 * Math.min(1, Math.max(0, 2 * (1 - Math.abs(t - 0.5)))));
    const b = Math.round(255 * Math.min(1, Math.max(0, 2 * (1 - t))));
    return `rgba(${r},${g},${b},${0.6})`;
  };

  // Responsive Chart: Container-Box steuert die Größe
  const chartProps = { data: chartData, options };

  // If config requests moving average for simple chart types (line/area/bar),
  // compute MA overlay datasets and append them to the chart data. We skip
  // stackedarea (handled in its branch) and boxplot (handled in its branch).
  try {
    const maCfgGlobal = (data as any).config?.movingAverage;
    const diagGlobal = ((data as any).diagramType || (data as any).config?.diagramType || type || '').toLowerCase();
    if (maCfgGlobal && maCfgGlobal.enabled && Number.isInteger(maCfgGlobal.window) && maCfgGlobal.window > 1) {
      if (['line', 'area', 'bar'].includes(diagGlobal)) {
        const ma = applyMovingAverage(chartData.datasets || [], maCfgGlobal.window);
        if (Array.isArray(ma) && ma.length > 0) {
          chartProps.data = { ...(chartProps.data || {}), datasets: [...(chartProps.data?.datasets || []), ...ma] } as any;
        }
      }
    }
  } catch (e) {
    // don't block rendering on MA computation errors
    // console.debug('MA overlay skipped', e);
  }

  return (
    <Box sx={{ width: '100%', height: { xs: 260, sm: 320, md: 360, lg: 400 }, minHeight: 220 }}>
      {(() => {
        switch (type) {
          case 'bar':
            return <Bar {...chartProps} />;
          case 'line':
            return <Line {...chartProps} />;
          case 'area':
            // Area charts are rendered as filled Line charts
            return <Line {...chartProps} />;
          case 'stackedarea': {
            // stacked area: use Line chart with stacking enabled
            const stackedOptions = {
              ...options,
              scales: {
                x: { stacked: true },
                y: { stacked: true }
              }
            };
            const stackedData = {
              ...chartData,
              datasets: chartData.datasets.map((ds: any) => ({ ...ds, fill: true }))
            };
            // moving average overlay if requested
            let finalDatasets = stackedData.datasets;
            const maCfg = (data as any).config?.movingAverage;
            if (maCfg && maCfg.enabled && Number.isInteger(maCfg.window) && maCfg.window > 1) {
              const ma = applyMovingAverage(stackedData.datasets, maCfg.window);
              finalDatasets = [...stackedData.datasets, ...ma];
            }
            return <Line data={{ ...stackedData, datasets: finalDatasets }} options={stackedOptions} />;
          }
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
          case 'scatter': {
            // Convert numeric-array datasets to scatter points (x=index, y=value)
            const scatterData = {
              labels: data.labels,
              datasets: data.datasets.map((ds: any, i: number) => ({
                label: ds.label,
                data: (ds.data || []).map((v: number, idx: number) => ({ x: idx, y: Number(v) })),
                backgroundColor: ds.backgroundColor || defaultColors[i % defaultColors.length],
                borderColor: ds.borderColor || defaultColors[i % defaultColors.length],
                showLine: false,
                pointRadius: 4,
              }))
            };
            const scatterOptions = {
              ...options,
              scales: {
                x: {
                  type: 'linear' as const,
                  ticks: {
                    callback: function (val: any, index: number) {
                      // Show label text for integer tick values
                      const labels = data.labels || [];
                      if (typeof val === 'number' && Math.round(val) === val) {
                        return labels[val] ?? val;
                      }
                      return '';
                    }
                  }
                }
              }
            };
            return <Scatter data={scatterData as any} options={scatterOptions as any} />;
          }
          case 'pitchheatmap': {
            // Two supported input shapes:
            // 1) array of point objects: [{x,y,intensity[,radius]}, ...]
            // 2) matrix-like numeric arrays returned by backend: datasets[i].data = [v0, v1, ...]
            const firstDs = data.datasets[0];
            // If first dataset contains objects with x/y, render points directly
            if (firstDs && firstDs.data && firstDs.data.length > 0 && typeof firstDs.data[0] === 'object' && ('x' in firstDs.data[0] || 'y' in firstDs.data[0])) {
              const pts = firstDs.data;
              const heatDs = {
                label: firstDs.label || 'Heatmap',
                data: pts.map((p: any) => ({ x: Number(p.x), y: Number(p.y) })),
                backgroundColor: pts.map((p: any) => heatColor(Math.min(1, Math.max(0, (p.intensity ?? 0))))),
                pointRadius: pts.map((p: any) => (p.radius ?? 6)),
              };
              const heatOptions = {
                ...options,
                scales: {
                  x: { min: 0, max: 100 },
                  y: { min: 0, max: 100 }
                }
              };
              return <Scatter data={{ labels: data.labels, datasets: [heatDs] } as any} options={heatOptions as any} />;
            }

            // Otherwise, treat incoming datasets as a matrix: rows = datasets, cols = labels
            const labels = data.labels || [];
            const dsets = data.datasets || [];
            // build grid points: x = colIndex mapped to 0..100, y = rowIndex mapped to 0..100
            const points: any[] = [];
            let maxVal = 0;
            for (let r = 0; r < dsets.length; r++) {
              const row = dsets[r].data || [];
              for (let c = 0; c < labels.length; c++) {
                const v = Number(row[c] ?? 0);
                if (!isNaN(v) && v > maxVal) maxVal = v;
                points.push({ r, c, v });
              }
            }
            const gridPoints = points.map(p => {
              const x = (p.c / Math.max(1, labels.length - 1)) * 100;
              const y = (p.r / Math.max(1, dsets.length - 1)) * 100;
              const intensity = maxVal > 0 ? (p.v / maxVal) : 0;
              return { x, y, intensity, radius: 12 };
            });

            // compute scaled radii and optional kernel overlay
            const baseRadius = 12;
            const heatDs2 = {
              label: 'Heatmap',
              data: gridPoints.map((p: any) => ({ x: p.x, y: p.y })),
              backgroundColor: gridPoints.map((p: any) => heatColor(p.intensity)),
              // scale point size by intensity (min radius 3.. max baseRadius*1.8)
              pointRadius: gridPoints.map((p: any) => Math.max(3, Math.round(baseRadius * (0.4 + 1.4 * p.intensity)))),
            };
            const xStep = labels.length > 1 ? (100 / Math.max(1, labels.length - 1)) : 100;
            const yStep = dsets.length > 1 ? (100 / Math.max(1, dsets.length - 1)) : 100;
            // create kernel-smoothed overlay canvas (small size, will be stretched)
            const overlayCanvas = generateHeatmapCanvas(gridPoints, 400, 300, Math.max(18, Math.round(baseRadius * 1.5)));

            const heatOptions2 = {
              ...options,
              plugins: {
                ...(options.plugins || {}),
                heatmapOverlay: { canvas: overlayCanvas }
              },
              scales: {
                x: {
                  min: 0,
                  max: 100,
                  ticks: {
                    stepSize: xStep,
                    callback: function (val: any) {
                      const labelsArr = labels || [];
                      if (typeof val === 'number') {
                        // compute index from exact tick position
                        const idx = Math.round((val / 100) * (labelsArr.length - 1));
                        return labelsArr[idx] ?? '';
                      }
                      return '';
                    }
                  }
                },
                y: {
                  min: 0,
                  max: 100,
                  ticks: {
                    stepSize: yStep,
                    callback: function (val: any) {
                      const dArr = dsets || [];
                      if (typeof val === 'number') {
                        const idx = Math.round((val / 100) * (dArr.length - 1));
                        return dArr[idx]?.label ?? '';
                      }
                      return '';
                    }
                  }
                }
              }
            };

            // Add tooltip callbacks to show exact cell info
            (heatOptions2.plugins as any).tooltip = {
              enabled: true,
              callbacks: {
                title: (items: any[]) => {
                  if (!items || items.length === 0) return '';
                  const it = items[0];
                  const raw = it.raw || it; // Chart.js raw point
                  const colIdx = Math.round((raw.x / 100) * Math.max(0, (labels.length - 1)));
                  const rowIdx = Math.round((raw.y / 100) * Math.max(0, (dsets.length - 1)));
                  const colLabel = labels[colIdx] ?? '';
                  const rowLabel = dsets[rowIdx]?.label ?? '';
                  return `${rowLabel} — ${colLabel}`;
                },
                label: (context: any) => {
                  const raw = context.raw || context;
                  const colIdx = Math.round((raw.x / 100) * Math.max(0, (labels.length - 1)));
                  const rowIdx = Math.round((raw.y / 100) * Math.max(0, (dsets.length - 1)));
                  const val = dsets[rowIdx]?.data?.[colIdx] ?? 0;
                  return `Wert: ${val}`;
                }
              }
            };

            // If heatmapStyle includes classic/grid, add grid plugin config
            const style = (data as any).config?.heatmapStyle || 'smoothed';
            if (style === 'classic' || style === 'both') {
              (heatOptions2.plugins as any).heatmapGrid = {
                gridPoints: points,
                labels,
                dsets
              };
            }

            return <Scatter data={{ labels, datasets: [heatDs2] } as any} options={heatOptions2 as any} />;
          }
          case 'boxplot': {
            // Expect datasets where each dataset.data is an array per label: e.g. [{label: 'A', data: [[...nums], [...nums]]}]
            // We'll reuse the boxplotPlugin which draws boxes when dataset._boxplot === true
            const bpData = {
              labels: data.labels,
              datasets: data.datasets.map((ds: any, i: number) => ({
                label: ds.label,
                data: ds.data, // array where each entry can be an array of numbers
                backgroundColor: ds.backgroundColor || rgbaColors[i % rgbaColors.length],
                borderColor: ds.borderColor || defaultColors[i % defaultColors.length],
                borderWidth: ds.borderWidth || 1.5,
                _boxplot: true,
              }))
            };
            const bpOptions: ChartOptions = {
              ...options as any,
              _drawBoxplots: true,
              scales: {
                x: { stacked: false },
                y: { stacked: false }
              }
            };
            // If moving-average overlay requested, compute central values per label
            // for each dataset (mean of each numeric-array entry) and append MA line datasets.
            const maCfg = (data as any).config?.movingAverage;
            let finalBpDatasets = bpData.datasets;
            if (maCfg && maCfg.enabled && Number.isInteger(maCfg.window) && maCfg.window > 1) {
              // Build temp numeric datasets: for each bp dataset, compute central values per label
              const tempNumericDs: any[] = [];
              bpData.datasets.forEach((ds: any, idx: number) => {
                const central: number[] = [];
                if (Array.isArray(ds.data)) {
                  for (const entry of ds.data) {
                        if (Array.isArray(entry) && entry.length > 0) {
                          // compute central value (mean or median) depending on config
                          const nums: number[] = [];
                          for (const v of entry) {
                            const n = Number(v);
                            if (!isNaN(n)) nums.push(n);
                          }
                          if (nums.length === 0) {
                            central.push(0);
                          } else if ((maCfg && maCfg.method === 'median')) {
                            nums.sort((a, b) => a - b);
                            const m = nums.length;
                            if (m % 2 === 1) {
                              central.push(nums[(m - 1) / 2]);
                            } else {
                              central.push((nums[m / 2 - 1] + nums[m / 2]) / 2);
                            }
                          } else {
                            const s = nums.reduce((a, b) => a + b, 0);
                            central.push(s / nums.length);
                          }
                    } else if (typeof entry === 'number') {
                      central.push(entry);
                    } else {
                      central.push(0);
                    }
                  }
                }
                tempNumericDs.push({ label: ds.label, data: central, borderColor: ds.borderColor || defaultColors[idx % defaultColors.length] });
              });
              const maDs = applyMovingAverage(tempNumericDs, maCfg.window);
              // Append MA datasets after boxplot datasets; they are line-type datasets
              finalBpDatasets = [...bpData.datasets, ...maDs];
            }

            return <Bar data={{ ...bpData, datasets: finalBpDatasets } as any} options={bpOptions as any} />;
          }
          case 'radaroverlay': {
            // datasets: each dataset.data is numeric array matching labels
            const labels = data.labels || [];
            const rawDsets = data.datasets || [];
            const normalize = (data as any).config?.radarNormalize === true;
            let maxGlobal = 0;
            rawDsets.forEach((ds: any) => {
              (ds.data || []).forEach((v: any) => { const n = Number(v)||0; if (n > maxGlobal) maxGlobal = n; });
            });
            const radarDatasets = rawDsets.map((ds: any, i: number) => {
              const arr = (ds.data || []).map((v: any) => Number(v) || 0);
              let values = arr;
              if (normalize) {
                const localMax = Math.max(1, ...arr);
                values = arr.map((v: number) => (localMax > 0 ? v / localMax : 0));
              }
              return {
                label: ds.label || `Series ${i}`,
                data: values,
                backgroundColor: ds.backgroundColor || rgbaColors[i % rgbaColors.length],
                borderColor: ds.borderColor || defaultColors[i % defaultColors.length],
                borderWidth: 2,
                fill: true,
                tension: 0.2,
              };
            });
            const radarOptions = {
              ...options,
              scales: undefined,
              elements: { line: { borderWidth: 2 } },
              plugins: {
                ...(options.plugins || {}),
              }
            } as any;
            if (normalize) {
              radarOptions.scales = { r: { min: 0, max: 1, ticks: { stepSize: 0.25 } } };
            } else {
              radarOptions.scales = { r: { min: 0, max: Math.max(1, maxGlobal) } };
            }
            return <Radar data={{ labels, datasets: radarDatasets } as any} options={radarOptions as any} />;
          }
          default:
            return <Bar {...chartProps} />;
        }
      })()}
    </Box>
  );
};
