import React from 'react';
import { DndContext, closestCenter, PointerSensor, TouchSensor, useSensor, useSensors, DragOverlay } from '@dnd-kit/core';
import { arrayMove, SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { SortableWidget } from './SortableWidget';
import { Box, useTheme, Grid } from '@mui/material';
import useMediaQuery from '@mui/material/useMediaQuery';
import type { WidgetData } from '../services/dashboardWidgets';

interface DashboardDndKitWrapperProps {
  widgets: WidgetData[];
  onReorder: (widgets: WidgetData[]) => void;
  children: (widget: WidgetData, idx: number, dragProps: any, isDragging: boolean, dragHandle?: React.ReactNode) => React.ReactNode;
}

export const DashboardDndKitWrapper: React.FC<DashboardDndKitWrapperProps> = ({ widgets, onReorder, children }) => {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  
  // Different sensor configuration for mobile and desktop
  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: isMobile ? 20 : 15,
        tolerance: isMobile ? 8 : 5,
        delay: isMobile ? 200 : 150,
      },
    }),
    useSensor(TouchSensor, {
      activationConstraint: {
        delay: 200,
        tolerance: 8,
      },
    })
  );
  
  const [activeId, setActiveId] = React.useState<string | null>(null);

  const handleDragStart = (event: any) => {
    setActiveId(event.active.id);
  };

  const handleDragEnd = (event: any) => {
    setActiveId(null);
    const { active, over } = event;
    
    if (over && active.id !== over.id) {
      const oldIndex = widgets.findIndex(w => w.id === active.id);
      const newIndex = widgets.findIndex(w => w.id === over.id);
      
      if (oldIndex !== -1 && newIndex !== -1) {
        const newOrder = arrayMove(widgets, oldIndex, newIndex);
        onReorder(newOrder);
      }
    }
  };

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCenter}
      onDragStart={handleDragStart}
      onDragEnd={handleDragEnd}
    >
      <SortableContext items={widgets.map(w => w.id)} strategy={verticalListSortingStrategy}>
        {(() => {
          const gapPx = 16; // entspricht gap: 2 (theme.spacing(2) = 16px)
          const rows: WidgetData[][] = [];
          let i = 0;
          while (i < widgets.length) {
            // Ermittle die gewünschte Anzahl Widgets pro Zeile anhand der Breite des ersten Widgets
            const width = typeof widgets[i].width === 'number' ? widgets[i].width : 6;
            const percent = (width / 12) * 100;
            const n = Math.floor(100 / percent);
            rows.push(widgets.slice(i, i + n));
            i += n;
          }
          return (
            <>
              {rows.map((row, rowIdx) => {
                const n = row.length;
                const totalGapPx = (n - 1) * gapPx;
                const boxWidth = `calc((100% - ${totalGapPx}px) / ${n})`;
                return (
                  <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: `${gapPx}px`, mb: `${gapPx}px`, width: '100%' }} key={rowIdx}>
                    {row.map((widget, idx) => {
                      const isDraggingThis = activeId === widget.id;
                      const width = typeof widget.width === 'number' ? widget.width : 6;
                      const percent = (width / 12) * 100;
                      // Responsive: auf kleinen Bildschirmen immer 100% Breite
                      const boxWidth = { xs: '100%', sm: `calc(${percent}% - ${(n > 1 ? ((n-1)*gapPx)/n : 0)}px)` };
                      const sx: any = {
                        width: boxWidth,
                        minWidth: 120,
                        opacity: isDraggingThis ? 0.3 : 1,
                        boxSizing: 'border-box',
                        transition: 'width 0.2s'
                      };
                      if (isDraggingThis) sx.minHeight = 200;
                      if (isDraggingThis) {
                        return (
                          <Box key={widget.id} sx={{ border: '2px dashed #ccc', borderRadius: 1, height: '100%', ...sx }} />
                        );
                      }
                      return (
                        <Box key={widget.id} sx={sx}>
                          <SortableWidget id={widget.id}>
                            {(dragProps, isDragging, dragHandle) => children(widget, idx, dragProps, isDragging, dragHandle)}
                          </SortableWidget>
                        </Box>
                      );
                    })}
                  </Box>
                );
              })}
            </>
          );
        })()}
      </SortableContext>
      <DragOverlay>
        {activeId ? (() => {
          const idx = widgets.findIndex(w => w.id === activeId);
          const widget = widgets[idx];
          if (!widget) return null;
          // Zeige das echte Widget als Drag-Ghost
          return (
            <Box sx={{ 
              width: '300px',
              minHeight: '200px',
              transform: 'rotate(5deg)', // Leichter visueller Hinweis
              boxShadow: '0 8px 25px rgba(0,0,0,0.15)'
            }}>
              {children(widget, idx, {}, true)}
            </Box>
          );
        })() : null}
      </DragOverlay>
    </DndContext>
  );
};
