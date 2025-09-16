import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import DragIndicatorIcon from '@mui/icons-material/DragIndicator';
import IconButton from '@mui/material/IconButton';
import { useTheme } from '@mui/material/styles';
import useMediaQuery from '@mui/material/useMediaQuery';

export function SortableWidget({ id, children }: { id: string; children: (dragProps: any, isDragging: boolean, dragHandle?: React.ReactNode) => React.ReactNode }) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id });
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  
  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : 1,
    zIndex: isDragging ? 1000 : 'auto',
    boxShadow: isDragging ? '0 4px 16px rgba(0,0,0,0.18)' : undefined,
    background: isDragging ? 'rgba(0,0,0,0.02)' : undefined,
  };
  
  // Mobile-optimized drag handle
  const dragHandle = (
    <IconButton
      {...listeners} 
      {...attributes} 
      size={isMobile ? "small" : "medium"}
      sx={{ 
        cursor: isDragging ? 'grabbing' : 'grab',
        color: 'text.secondary',
        '&:hover': {
          color: 'primary.main',
          backgroundColor: 'action.hover'
        },
        touchAction: 'none', // Prevent scrolling when dragging
        minWidth: { xs: 32, sm: 40 },
        height: { xs: 32, sm: 40 },
        padding: { xs: 0.5, sm: 1 }
      }}
      title="Widget verschieben"
    >
      <DragIndicatorIcon fontSize={isMobile ? "small" : "medium"} />
    </IconButton>
  );
  
  return children({ ref: setNodeRef, style }, isDragging, dragHandle);
}
