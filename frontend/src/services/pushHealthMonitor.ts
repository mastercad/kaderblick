/**
 * Push Health Monitor
 *
 * Proaktives Monitoring des Push-Notification-Systems.
 * Erkennt Probleme BEVOR der Benutzer merkt, dass er keine 
 * Benachrichtigungen erhält.
 *
 * Prüft:
 *  - Browser-Unterstützung (Notification API, Service Worker, PushManager)
 *  - Berechtigung (granted / denied / default)
 *  - Service Worker Status (installiert, aktiviert, hat Push-Handler)
 *  - Push-Subscription beim PushManager aktiv
 *  - Backend-Subscription registriert & aktiv
 *  - Backend-Delivery-Statistiken (Fehlerrate)
 */

import { apiJson } from '../utils/api';

export type PushHealthStatus =
  | 'healthy'       // Alles OK
  | 'degraded'      // Teilweise defekt (z.B. hohe Fehlerrate)  
  | 'broken'        // Komplett kaputt (keine Subscription, kein SW, etc.)
  | 'not_supported' // Browser unterstützt kein Push
  | 'permission_denied' // Benutzer hat Push-Berechtigung verweigert
  | 'not_subscribed'    // Technisch möglich, aber noch nicht abonniert
  | 'checking';     // Prüfung läuft

export interface PushHealthIssue {
  code: string;
  severity: 'error' | 'warning' | 'info';
  message: string;
  action?: string; // Was der User tun kann
}

export interface PushHealthReport {
  status: PushHealthStatus;
  issues: PushHealthIssue[];
  details: {
    browserSupport: boolean;
    permission: NotificationPermission | 'unsupported';
    serviceWorkerActive: boolean;
    pushSubscriptionActive: boolean;
    backendSubscriptionCount: number;
    backendStatus: string | null;
    lastSentAt: string | null;
    deliveryStats: { total: number; sent: number; unsent: number; failRate: number } | null;
  };
  checkedAt: Date;
}

type HealthChangeCallback = (report: PushHealthReport) => void;

class PushHealthMonitor {
  private lastReport: PushHealthReport | null = null;
  private checkInterval: ReturnType<typeof setInterval> | null = null;
  private listeners: HealthChangeCallback[] = [];

  /**
   * Führt eine vollständige Gesundheitsprüfung durch.
   */
  async check(): Promise<PushHealthReport> {
    const issues: PushHealthIssue[] = [];

    // 1. Browser-Unterstützung
    const browserSupport =
      'Notification' in window &&
      'serviceWorker' in navigator &&
      'PushManager' in window;

    if (!browserSupport) {
      const report = this.buildReport('not_supported', [{
        code: 'browser_not_supported',
        severity: 'error',
        message: 'Dieser Browser unterstützt keine Push-Benachrichtigungen.',
        action: 'Verwende Chrome, Firefox, Edge oder Safari für Push-Benachrichtigungen.',
      }], {
        browserSupport: false,
        permission: 'unsupported',
        serviceWorkerActive: false,
        pushSubscriptionActive: false,
        backendSubscriptionCount: 0,
        backendStatus: null,
        lastSentAt: null,
        deliveryStats: null,
      });
      this.setReport(report);
      return report;
    }

    // 2. Berechtigung
    const permission = Notification.permission;
    if (permission === 'denied') {
      issues.push({
        code: 'permission_denied',
        severity: 'error',
        message: 'Push-Benachrichtigungen wurden im Browser blockiert.',
        action: 'Klicke auf das Schloss-Symbol in der Adressleiste und erlaube Benachrichtigungen.',
      });
    } else if (permission === 'default') {
      issues.push({
        code: 'permission_not_asked',
        severity: 'warning',
        message: 'Push-Berechtigung wurde noch nicht erteilt.',
        action: 'Klicke hier, um Push-Benachrichtigungen zu aktivieren.',
      });
    }

    // 3. Service Worker
    let serviceWorkerActive = false;
    let pushSubscriptionActive = false;

    try {
      const registration = await navigator.serviceWorker.ready;
      serviceWorkerActive = registration.active !== null;

      if (!serviceWorkerActive) {
        issues.push({
          code: 'sw_not_active',
          severity: 'error',
          message: 'Service Worker ist nicht aktiv.',
          action: 'Seite neu laden. Falls das Problem bestehen bleibt, Cache leeren.',
        });
      }

      // 4. Push-Subscription prüfen
      const subscription = await registration.pushManager.getSubscription();
      pushSubscriptionActive = subscription !== null;

      if (!pushSubscriptionActive && permission === 'granted') {
        issues.push({
          code: 'no_push_subscription',
          severity: 'error',
          message: 'Keine aktive Push-Subscription vorhanden.',
          action: 'Push-Benachrichtigungen werden bei nächster Gelegenheit automatisch neu abonniert.',
        });
      }
    } catch (err) {
      issues.push({
        code: 'sw_error',
        severity: 'error',
        message: 'Fehler beim Prüfen des Service Workers.',
      });
    }

    // 5. Backend-Status prüfen
    let backendSubscriptionCount = 0;
    let backendStatus: string | null = null;
    let lastSentAt: string | null = null;
    let deliveryStats: { total: number; sent: number; unsent: number; failRate: number } | null = null;

    try {
      const health = await apiJson('/api/push/health');
      backendStatus = health.status;
      backendSubscriptionCount = health.subscriptionCount ?? 0;
      lastSentAt = health.lastSentAt ?? null;
      deliveryStats = health.recentStats ?? null;

      if (health.issues && Array.isArray(health.issues)) {
        for (const issue of health.issues) {
          switch (issue) {
            case 'vapid_not_configured':
              issues.push({
                code: 'vapid_not_configured',
                severity: 'error',
                message: 'Server-seitige Push-Konfiguration fehlt (VAPID).',
                action: 'Bitte Administrator kontaktieren.',
              });
              break;
            case 'no_subscriptions':
              if (!issues.find(i => i.code === 'no_push_subscription')) {
                issues.push({
                  code: 'backend_no_subscriptions',
                  severity: 'error',
                  message: 'Keine Push-Subscriptions auf dem Server registriert.',
                });
              }
              break;
            case 'all_deliveries_failed':
              issues.push({
                code: 'all_deliveries_failed',
                severity: 'error',
                message: 'Alle Push-Benachrichtigungen der letzten 7 Tage sind fehlgeschlagen.',
                action: 'Push-Subscription erneuern. Seite neu laden und Push erlauben.',
              });
              break;
            case 'high_failure_rate':
              issues.push({
                code: 'high_failure_rate',
                severity: 'warning',
                message: `Hohe Fehlerrate bei Push-Zustellung (${deliveryStats?.failRate ?? '?'}%).`,
              });
              break;
            case 'many_unsent_stuck':
              issues.push({
                code: 'many_unsent_stuck',
                severity: 'warning',
                message: 'Es gibt mehrere fehlgeschlagene Benachrichtigungen.',
              });
              break;
          }
        }
      }
    } catch {
      // Backend nicht erreichbar - kein critischer Fehler, SW Push kann trotzdem funken
      issues.push({
        code: 'backend_unreachable',
        severity: 'warning',
        message: 'Push-Status konnte nicht vom Server abgerufen werden.',
      });
    }

    // Status bestimmen
    let status: PushHealthStatus;
    const hasErrors = issues.some(i => i.severity === 'error');
    const hasWarnings = issues.some(i => i.severity === 'warning');

    if (permission === 'denied') {
      status = 'permission_denied';
    } else if (!pushSubscriptionActive && permission === 'default') {
      status = 'not_subscribed';
    } else if (hasErrors) {
      status = 'broken';
    } else if (hasWarnings) {
      status = 'degraded';
    } else {
      status = 'healthy';
    }

    const report = this.buildReport(status, issues, {
      browserSupport,
      permission,
      serviceWorkerActive,
      pushSubscriptionActive,
      backendSubscriptionCount,
      backendStatus,
      lastSentAt,
      deliveryStats,
    });

    this.setReport(report);
    return report;
  }

  /**
   * Startet periodische Prüfung (Standard: alle 30 Minuten).
   */
  startMonitoring(intervalMs: number = 30 * 60 * 1000): void {
    if (this.checkInterval) return;

    // Erste Prüfung nach 10 Sekunden (App muss erst geladen sein)
    setTimeout(() => {
      this.check().catch(console.error);
    }, 10_000);

    this.checkInterval = setInterval(() => {
      this.check().catch(console.error);
    }, intervalMs);
  }

  stopMonitoring(): void {
    if (this.checkInterval) {
      clearInterval(this.checkInterval);
      this.checkInterval = null;
    }
  }

  /**
   * Listener für Status-Änderungen registrieren.
   */
  onStatusChange(callback: HealthChangeCallback): () => void {
    this.listeners.push(callback);
    // Sofort aktuelle Info liefern, falls vorhanden
    if (this.lastReport) {
      callback(this.lastReport);
    }
    return () => {
      this.listeners = this.listeners.filter(l => l !== callback);
    };
  }

  getLastReport(): PushHealthReport | null {
    return this.lastReport;
  }

  /**
   * Push-Benachrichtigungen aktivieren (Permission anfragen + Subscribe).
   */
  async enablePush(): Promise<boolean> {
    try {
      if (!('Notification' in window) || !('PushManager' in window)) return false;

      const permission = await Notification.requestPermission();
      if (permission !== 'granted') return false;

      const registration = await navigator.serviceWorker.ready;

      // VAPID Key vom Server holen
      const vapidResponse = await apiJson('/api/push/vapid-key');
      const vapidKey = vapidResponse.key;

      // Subscribe
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(vapidKey),
      });

      // An Backend senden
      await apiJson('/api/push/subscribe', {
        method: 'POST',
        body: { subscription: subscription.toJSON() },
      });

      // Sofort neu prüfen
      await this.check();
      return true;
    } catch (err) {
      console.error('Failed to enable push:', err);
      return false;
    }
  }

  /**
   * Test-Push senden, um die gesamte Pipeline zu verifizieren.
   */
  async sendTestPush(): Promise<{ success: boolean; message: string }> {
    try {
      const result = await apiJson('/api/push/test', { method: 'POST' });
      return { success: result.success, message: result.message };
    } catch (err: any) {
      return {
        success: false,
        message: err.message || 'Test-Push konnte nicht gesendet werden.',
      };
    }
  }

  private buildReport(
    status: PushHealthStatus,
    issues: PushHealthIssue[],
    details: PushHealthReport['details']
  ): PushHealthReport {
    return { status, issues, details, checkedAt: new Date() };
  }

  private setReport(report: PushHealthReport): void {
    const statusChanged = this.lastReport?.status !== report.status;
    this.lastReport = report;

    if (statusChanged || report.issues.length > 0) {
      this.listeners.forEach(cb => cb(report));
    }
  }

  private urlBase64ToUint8Array(base64String: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length) as Uint8Array<ArrayBuffer>;
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }
}

export const pushHealthMonitor = new PushHealthMonitor();
