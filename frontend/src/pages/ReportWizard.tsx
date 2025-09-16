import React, { useState } from 'react';
import { Bar, Line, Pie } from 'react-chartjs-2';

// Beispielhafte Report-Typen und Parameter
const REPORT_TYPES = [
  { value: 'bar', label: 'Balkendiagramm' },
  { value: 'line', label: 'Liniendiagramm' },
  { value: 'pie', label: 'Kuchendiagramm' },
];

const DEFAULT_PARAMS = {
  bar: { labels: ['A', 'B', 'C'], data: [12, 19, 3] },
  line: { labels: ['A', 'B', 'C'], data: [5, 10, 7] },
  pie: { labels: ['A', 'B', 'C'], data: [2, 7, 11] },
};

const ReportWizard = () => {
  const [step, setStep] = useState(1);
  const [type, setType] = useState('bar');
  const [params, setParams] = useState(DEFAULT_PARAMS['bar']);
  const [saving, setSaving] = useState(false);
  const [success, setSuccess] = useState(false);

  // Schritt 1: Typ wählen
  const StepType = () => (
    <div>
      <h2>1. Report-Typ wählen</h2>
      <select value={type} onChange={e => {
        setType(e.target.value);
        setParams(DEFAULT_PARAMS[e.target.value]);
      }}>
        {REPORT_TYPES.map(rt => (
          <option key={rt.value} value={rt.value}>{rt.label}</option>
        ))}
      </select>
      <button onClick={() => setStep(2)}>Weiter</button>
    </div>
  );

  // Schritt 2: Parameter setzen
  const StepParams = () => (
    <div>
      <h2>2. Parameter setzen</h2>
      <label>Labels (Komma-getrennt):
        <input
          value={params.labels.join(',')}
          onChange={e => setParams({ ...params, labels: e.target.value.split(',') })}
        />
      </label>
      <br />
      <label>Daten (Komma-getrennt):
        <input
          value={params.data.join(',')}
          onChange={e => setParams({ ...params, data: e.target.value.split(',').map(Number) })}
        />
      </label>
      <br />
      <button onClick={() => setStep(1)}>Zurück</button>
      <button onClick={() => setStep(3)}>Vorschau</button>
    </div>
  );

  // Schritt 3: Vorschau
  const StepPreview = () => {
    const chartData = {
      labels: params.labels,
      datasets: [{
        label: 'Beispiel-Daten',
        data: params.data,
        backgroundColor: ['#36a2eb', '#ff6384', '#ffce56'],
      }],
    };
    return (
      <div>
        <h2>3. Vorschau</h2>
        {type === 'bar' && <Bar data={chartData} />}
        {type === 'line' && <Line data={chartData} />}
        {type === 'pie' && <Pie data={chartData} />}
        <br />
        <button onClick={() => setStep(2)}>Zurück</button>
        <button onClick={() => setStep(4)}>Speichern</button>
      </div>
    );
  };

  // Schritt 4: Speichern
  const StepSave = () => (
    <div>
      <h2>4. Speichern</h2>
      {saving ? <p>Speichern...</p> : success ? <p>Report gespeichert!</p> : (
        <>
          <button onClick={() => setStep(3)}>Zurück</button>
          <button onClick={handleSave}>Jetzt speichern</button>
        </>
      )}
    </div>
  );

  function handleSave() {
    setSaving(true);
    // Hier würdest du einen API-Call machen
    setTimeout(() => {
      setSaving(false);
      setSuccess(true);
    }, 1200);
  }

  return (
    <div style={{ width: '100%', padding: '2rem', border: '1px solid #ccc', borderRadius: 8, margin: '0 auto', maxWidth: '100%' }}>
      <h1>Report Wizard</h1>
      {step === 1 && <StepType />}
      {step === 2 && <StepParams />}
      {step === 3 && <StepPreview />}
      {step === 4 && <StepSave />}
    </div>
  );
};

export default ReportWizard;
