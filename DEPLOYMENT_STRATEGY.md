# Zero-Downtime Deployment Strategy für Kaderblick

## Aktueller Zustand
Der Deploy-Workflow verwendet einen Staging → Production Ansatz:
1. Staging-Container werden gebaut und gestartet
2. Migrations und Tests laufen auf Staging
3. Images werden getaggt
4. **Production wird gestoppt** ⚠️ (Downtime!)
5. Neuer Symlink wird gesetzt
6. Production wird gestartet

**Problem:** Zwischen Schritt 4 und 6 gibt es ca. 5-15 Sekunden Downtime.

---

## Lösung 1: Nginx Reverse Proxy (Empfohlen) ✅

### Vorteile
- ✅ Automatische Maintenance Page bei Ausfall
- ✅ Health Checks für Backend
- ✅ Load Balancing möglich
- ✅ SSL-Termination zentral
- ✅ Caching Layer
- ✅ Rate Limiting möglich

### Setup

```bash
# Starte mit Nginx Proxy
docker compose -f docker-compose.yml -f docker-compose.proxy.yml up -d
```

Der Nginx Proxy zeigt automatisch die Maintenance Page, wenn Backend nicht verfügbar ist.

**Deployment-Ablauf:**
1. Staging läuft parallel zu Production
2. Tests auf Staging
3. Production wird gestoppt → Nginx zeigt Maintenance
4. Neue Production startet → Nginx routet automatisch durch
5. **Downtime nur während Container-Start (5-10 Sekunden)**

---

## Lösung 2: Echter Zero-Downtime mit Rolling Update

### Änderung im Workflow

Statt:
```yaml
docker compose -p production down
docker compose -p production up -d
```

Besser:
```yaml
# Neue Container mit anderen Namen starten
docker compose -p production-new up -d --no-deps api frontend

# Health Check warten
timeout 30 sh -c 'until docker exec production-new-api-1 curl -f http://localhost/api/health; do sleep 1; done'

# Alte Container stoppen
docker compose -p production down --remove-orphans

# Umbenennen
docker rename production-new-api-1 production-api-1
docker rename production-new-frontend-1 production-frontend-1
```

**Problem:** Erfordert Container-Umbenennung und ist komplexer.

---

## Lösung 3: Health-Check-basiertes Deployment

```yaml
- name: Deploy with health checks
  script: |
    # Start new containers
    docker compose -p production up -d --force-recreate --no-deps api frontend
    
    # Wait for health checks
    until docker inspect --format='{{.State.Health.Status}}' production-api-1 | grep -q healthy; do
      echo "Waiting for API..."
      sleep 2
    done
    
    until docker inspect --format='{{.State.Health.Status}}' production-frontend-1 | grep -q healthy; do
      echo "Waiting for Frontend..."
      sleep 2
    done
    
    echo "Deployment successful!"
```

---

## Empfehlung

**Für dein Setup: Option 1 (Nginx Proxy)**

### Warum?
1. Minimale Änderung am Workflow nötig
2. Automatische Fehlerbehandlung
3. Zukünftige Skalierung möglich
4. Professionelle Maintenance Page
5. Monitoring durch Health Checks

### Migration Steps

1. **Nginx Setup testen (lokal):**
   ```bash
   cd /media/Austausch/Projekte/fussballverein/webapp
   docker compose -f docker-compose.proxy.yml up -d nginx
   ```

2. **Workflow anpassen:**
   - `docker-compose.proxy.yml` auf Server kopieren
   - Im Deployment `docker compose -f docker-compose.yml -f docker-compose.proxy.yml` nutzen
   - Nginx läuft permanent, nur api/frontend werden neu deployed

3. **Port-Mapping anpassen:**
   - Nginx lauscht auf Port 80/443
   - API/Frontend nur intern erreichbar
   - Plesk Proxy auf Nginx zeigen lassen

---

## Aktuelle Downtime-Messung

Mit deinem aktuellen Setup:
- `docker compose down`: ~1-2s
- Symlink ändern: <0.1s  
- `docker compose up -d`: ~5-15s (abhängig von Container-Start)
- **Total: 6-17 Sekunden Downtime**

Mit Nginx Proxy:
- **5-10 Sekunden** (nur Container-Startzeit)
- Maintenance Page während dieser Zeit
- Nahtloser Übergang

---

## Alternative: Ist Downtime wirklich ein Problem?

Für einen Fußballverein mit vermutlich niedrigem Traffic:
- Deployment um 3:00 Uhr morgens
- 10 Sekunden Downtime sind kaum bemerkbar
- Blue-Green-Deployment (Staging) fängt Fehler ab

**→ Aktuelles Setup ist für deinen Use Case wahrscheinlich ausreichend!**

Nginx Proxy würde aber:
- Professionalität erhöhen
- Transparenz schaffen (Nutzer sehen Wartungsseite)
- Zukünftige Features ermöglichen (SSL, Caching, CDN)
