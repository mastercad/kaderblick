import React, { useState } from "react";

type Step = { target: string; content: string };
type TourTooltipProps = {
  steps: Step[];
  buttonLabel?: string;
};

const TourTooltip: React.FC<TourTooltipProps> = ({ steps, buttonLabel = "?" /*"Hilfe & Rundgang" */ }) => {
  const [stepIdx, setStepIdx] = useState(-1); // -1 = Tour aus
  const startTour = () => setStepIdx(0);
  const closeTour = () => setStepIdx(-1);
  const nextStep = () => setStepIdx((idx) => Math.min(idx + 1, steps.length - 1));
  const prevStep = () => setStepIdx((idx) => Math.max(idx - 1, 0));

  let tooltip = null;
  let backdrop = null;
  if (stepIdx >= 0 && steps[stepIdx]) {
    // Vor jedem Schritt: Alle Highlights entfernen
      steps.forEach((step, idx) => {
        const el = document.getElementById(step.target);
        if (el) {
          el.classList.remove("tour-highlight");
        }
      });
    const step = steps[stepIdx];
    const el = document.getElementById(step.target);
    const rect = el ? el.getBoundingClientRect() : { top: 100, left: 100, width: 0, height: 40 };
    // Backdrop
    backdrop = (
      <div
        style={{
          position: "fixed",
          top: 0,
          left: 0,
          width: "100vw",
          height: "100vh",
          background: "rgba(0,0,0,0.5)",
          zIndex: 9998,
        }}
      />
    );
    if (el) {
        el.classList.add("tour-highlight");
    }

    // Tooltip
    const style: React.CSSProperties = {
      position: "fixed",
      top: rect.top + window.scrollY - 10,
      left: rect.left + window.scrollX + rect.width + 20,
      zIndex: 10002,
      background: "#f5faff",
      color: "#1a237e",
      border: "2px solid #1976d2",
      borderRadius: 8,
      boxShadow: "0 2px 8px rgba(0,0,0,0.15)",
      padding: "16px 20px",
      minWidth: 220,
      maxWidth: 320,
      fontSize: "1rem",
    };
    tooltip = (
      <div style={style}>
        <div style={{ marginBottom: 12 }}>{step.content}</div>
        <div style={{ display: "flex", gap: 8, justifyContent: "flex-end" }}>
          {stepIdx > 0 && (
            <button onClick={prevStep} style={{ background: "#e3f2fd", color: "#1976d2", border: "none", borderRadius: 4, padding: "6px 12px", cursor: "pointer" }}>Zurück</button>
          )}
          {stepIdx < steps.length - 1 && (
            <button onClick={nextStep} style={{ background: "#1976d2", color: "#fff", border: "none", borderRadius: 4, padding: "6px 12px", cursor: "pointer" }}>Weiter</button>
          )}
          <button onClick={closeTour} style={{ background: "#fffde7", color: "#f57c00", border: "none", borderRadius: 4, padding: "6px 12px", cursor: "pointer" }}>Schließen</button>
        </div>
      </div>
    );
  } else {
    // Spotlight wieder entfernen, falls Tour beendet
      steps.forEach(step => {
        const el = document.getElementById(step.target);
        if (el) {
          el.classList.remove("tour-highlight");
        }
      });
  }

  return (
    <>
      <button
        style={{
          position: "absolute",
          top: 16,
          right: 16,
          background: "#1976d2",
          color: "#fff",
          border: "none",
          width: 45,
          height: 45,
          borderRadius: "50%",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          cursor: "pointer",
          fontWeight: "bold",
          fontSize: "1.5rem",
          boxShadow: "0 2px 8px rgba(0,0,0,0.15)",
        }}
        onClick={startTour}
      >
        {buttonLabel}
      </button>
      {backdrop}
      {tooltip}
    </>
  );
};

export default TourTooltip;