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
        <Grid container spacing={{ xs: 1, sm: 2 }} wrap="wrap" sx={{ width: '100%' }}>
          {widgets.map((widget, idx) => {
            const isDraggingThis = activeId === widget.id;
            const width = widget.width || 6;
            if (isDraggingThis) {
              // Zeige nur einen leeren Platzhalter für das gezogene Element
              return (
                <Grid item key={widget.id} xs={12} sm={width} md={width} lg={width} xl={width} style={{ opacity: 0.3, minHeight: 200 }}>
                  <Box sx={{ border: '2px dashed #ccc', borderRadius: 1, height: '100%' }} />
                </Grid>
              );
            }
            return (
              <Grid item key={widget.id} xs={12} sm={width} md={width} lg={width} xl={width}>
                <SortableWidget id={widget.id}>
                  {(dragProps, isDragging, dragHandle) => children(widget, idx, dragProps, isDragging, dragHandle)}
                </SortableWidget>
              </Grid>
            );
          })}
        </Grid>
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
