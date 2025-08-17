/**
 * Dashboard Modal Registration
 * Professional modal templates for dashboard functionality
 */

// Dashboard-specific modal templates
const dashboardModalTemplates = {
  addWidgetModal: `
    <div class="modal fade" id="addWidgetModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Widget hinzufügen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="list-group">
              <button type="button" class="list-group-item list-group-item-action btn-add-widget" data-widget-type="calendar">
                <i class="fas fa-calendar"></i> Kalender
              </button>
              <button type="button" class="list-group-item list-group-item-action btn-add-widget" data-widget-type="stats">
                <i class="fas fa-chart-bar"></i> Statistiken
              </button>
              <button type="button" class="list-group-item list-group-item-action btn-add-widget" data-widget-type="messages">
                <i class="fas fa-envelope"></i> Nachrichten
              </button>
              <button type="button" class="list-group-item list-group-item-action btn-add-widget" data-widget-type="news">
                <i class="fas fa-newspaper"></i> News
              </button>
              <button type="button" class="list-group-item list-group-item-action btn-add-widget" data-widget-type="upcoming_events">
                <i class="fas fa-newspaper"></i> Anstehende Veranstaltungen
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `
};

// Register dashboard modals when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Wait for Modal Manager and ensure we're on dashboard page
  setTimeout(() => {
    if (window.ModalManager && document.getElementById('widgetGrid')) {
      // Register dashboard-specific modals
      Object.keys(dashboardModalTemplates).forEach(modalId => {
        window.ModalManager.registerModal(modalId, dashboardModalTemplates[modalId]);
      });
      
      console.log('Dashboard modals registered successfully');
      
      // Initialize dashboard modal functionality
      initializeDashboardModalFunctionality();
    }
  }, 150);
});

/**
 * Initialize dashboard-specific modal functionality
 */
function initializeDashboardModalFunctionality() {
  // Widget add button handlers
  const addWidgetButtons = document.querySelectorAll('.btn-add-widget');
  addWidgetButtons.forEach(button => {
    button.addEventListener('click', function() {
      const widgetType = this.dataset.widgetType;
      createWidget(widgetType);
      window.ModalManager.hideModal('addWidgetModal');
    });
  });
}

async function createWidget(type) {
    await fetch('{{ path("app_dashboard_widget_create") }}', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type })
    }).then((response) => {
        if (response.status >= 400 && response.status < 600) {
            throw new Error("Bad response from server - " + response.status + " : " + response.statusText);
        }
        return response;
    }).then((returnedResponse) => returnedResponse.json()
    ).then((data) => {
        addWidget(data.widget.id);
    }).catch((error) => {
        console.log(error)
    });
}

/**
 * Add a new widget to the dashboard
 */
async function addWidget(widgetType) {
  try {
    const response = await fetch('/api/dashboard/widgets', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ type: widgetType })
    });

    if (response.ok) {
      const widget = await response.json();
      
      // Create widget element
      const widgetElement = createWidgetElement(widget);
      
      // Add to grid
      const grid = document.getElementById('widgetGrid');
      grid.appendChild(widgetElement);
      
      // Load widget content
      await loadWidgetContent(widgetElement);
      
      // Update positions
      updateWidgetPositions();
      
    } else {
      alert('Fehler beim Hinzufügen des Widgets');
    }
  } catch (error) {
    console.error('Add widget error:', error);
    alert('Fehler beim Hinzufügen des Widgets');
  }
}

/**
 * Create a new widget element
 */
function createWidgetElement(widget) {
  const widgetHtml = `
    <div class="col-lg-6 col-xl-4 widget-item" data-widget-id="${widget.id}" data-widget-type="${widget.type}">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="card-title mb-0">${getWidgetTitle(widget.type)}</h6>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="fas fa-cog"></i>
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item btn-widget-settings" href="#" data-widget-id="${widget.id}">Einstellungen</a></li>
              <li><a class="dropdown-item btn-widget-remove" href="#" data-widget-id="${widget.id}">Entfernen</a></li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <div class="widget-content" id="widget-content-${widget.id}">
            <div class="text-center">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Laden...</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
  
  const wrapper = document.createElement('div');
  wrapper.innerHTML = widgetHtml.trim();
  const element = wrapper.firstChild;
  
  // Add event listeners
  addWidgetEventListeners(element);
  
  return element;
}

/**
 * Get widget title by type
 */
function getWidgetTitle(type) {
  const titles = {
    calendar: 'Kalender',
    stats: 'Statistiken',
    messages: 'Nachrichten',
    news: 'News',
    upcoming_events: 'Anstehende Veranstaltungen'
  };
  
  return titles[type] || 'Widget';
}

/**
 * Add event listeners to widget
 */
function addWidgetEventListeners(widgetElement) {
  // Settings button
  const settingsBtn = widgetElement.querySelector('.btn-widget-settings');
  if (settingsBtn) {
    settingsBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const widgetId = this.dataset.widgetId;
      showWidgetSettings(widgetId);
    });
  }
  
  // Remove button
  const removeBtn = widgetElement.querySelector('.btn-widget-remove');
  if (removeBtn) {
    removeBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const widgetId = this.dataset.widgetId;
      removeWidget(widgetId);
    });
  }
}

/**
 * Show widget settings (could be extended with specific modals)
 */
function showWidgetSettings(widgetId) {
  // For now, just a simple prompt - could be extended with specific modals
  alert('Widget-Einstellungen für Widget ' + widgetId + ' - Feature wird bald implementiert');
}

/**
 * Remove a widget
 */
async function removeWidget(widgetId) {
  if (confirm('Soll dieses Widget wirklich entfernt werden?')) {
    try {
      const response = await fetch(`/api/dashboard/widgets/${widgetId}`, {
        method: 'DELETE'
      });

      if (response.ok) {
        const widgetElement = document.querySelector(`[data-widget-id="${widgetId}"]`);
        if (widgetElement) {
          widgetElement.remove();
          updateWidgetPositions();
        }
      } else {
        alert('Fehler beim Entfernen des Widgets');
      }
    } catch (error) {
      console.error('Remove widget error:', error);
      alert('Fehler beim Entfernen des Widgets');
    }
  }
}

/**
 * Load widget content
 */
async function loadWidgetContent(widgetElement) {
  const widgetId = widgetElement.dataset.widgetId;
  const widgetType = widgetElement.dataset.widgetType;
  const contentContainer = widgetElement.querySelector('.widget-content');
  
  try {
    const response = await fetch(`/api/dashboard/widgets/${widgetId}/content`);
    
    if (response.ok) {
      const content = await response.text();
      contentContainer.innerHTML = content;
    } else {
      contentContainer.innerHTML = '<p class="text-muted">Fehler beim Laden des Inhalts</p>';
    }
  } catch (error) {
    console.error('Load widget content error:', error);
    contentContainer.innerHTML = '<p class="text-muted">Fehler beim Laden des Inhalts</p>';
  }
}

/**
 * Update widget positions
 */
function updateWidgetPositions() {
  const widgets = document.querySelectorAll('.widget-item');
  const positions = Array.from(widgets).map((widget, index) => ({
    id: widget.dataset.widgetId,
    position: index
  }));
  
  // Send to backend
  fetch('/api/dashboard/widgets/positions', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ positions })
  }).catch(error => {
    console.error('Update positions error:', error);
  });
}

// Make functions globally available
window.addWidget = addWidget;
window.removeWidget = removeWidget;
window.loadWidgetContent = loadWidgetContent;
window.updateWidgetPositions = updateWidgetPositions;
