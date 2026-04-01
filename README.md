# DevOps-ToDoList

Eine einfache **ToDo-Listen-Anwendung** entwickelt mit **HTML, CSS, JavaScript und PHP**.
Unterstützt **CRUD-Operationen** (Erstellen, Lesen, Aktualisieren, Löschen) für effizientes Aufgabenmanagement.

---

## Inhaltsverzeichnis

1. [Funktionen](#funktionen)
2. [Projektstruktur](#projektstruktur)
3. [Docker-Setup](#docker-setup)
4. [Datenbank](#datenbank)
5. [API-Endpunkte](#api-endpunkte)
6. [Testing](#testing)
7. [CI/CD Pipeline](#cicd-pipeline)

---

## Funktionen

- **Erstellen:** Neue Aufgaben mit Kategorie und Beschreibung hinzufügen.
- **Anzeigen:** Alle Aufgaben in einer übersichtlichen Liste darstellen.
- **Aktualisieren:** Aufgaben als *erledigt* oder *ausstehend* markieren und Details bearbeiten.
- **Löschen:** Aufgaben entfernen, die nicht mehr benötigt werden.
- **Einfache Bedienung:** Intuitive Oberfläche für schnelles Aufgabenmanagement.

---

## Projektstruktur

```
DevOps-ToDoList/
├── backend/
│   └── api/              # PHP-API-Endpunkte
├── database/
│   └── Initial-DB.sql    # Initiales Datenbankschema mit Testdaten
├── docker/
│   └── php/
│       └── Dockerfile    # PHP 8.4 + Apache Image
├── frontend/
│   └── ToDoList.php      # Frontend-Oberfläche
├── tests/
│   └── GetTaskAPITest.php # PHPUnit Integrationstests
├── docker-compose.yml
├── composer.json
└── index.php
```

---

## Docker-Setup

Das Projekt läuft vollständig in Docker. Es werden drei Container gestartet:

| Container               | Beschreibung                  | Port            |
|-------------------------|-------------------------------|-----------------|
| `devops-todo-php`       | PHP 8.4 + Apache (API & Frontend) | `8080`      |
| `devops-todo-mysql`     | MySQL 8.0 Datenbank           | `3307`          |
| `devops-todo-phpmyadmin`| phpMyAdmin Verwaltungsoberfläche | `8081`       |

### Starten

```bash
docker compose up --build
```

Anschließend ist die Anwendung unter [http://localhost:8080](http://localhost:8080) erreichbar.
phpMyAdmin ist unter [http://localhost:8081](http://localhost:8081) erreichbar.

### Dockerfile (`docker/php/Dockerfile`)

- Basis-Image: `php:8.4-apache`
- Installierte PHP-Erweiterungen: `mysqli`, `pdo`, `pdo_mysql`
- Apache `mod_rewrite` aktiviert
- Composer integriert

### Umgebungsvariablen

| Variable      | Wert        | Beschreibung              |
|---------------|-------------|---------------------------|
| `DB_HOST`     | `mysql`     | Hostname des Datenbankcontainers |
| `DB_USER`     | `root`      | Datenbankbenutzer         |
| `DB_PASSWORD` | `geheim_1`  | Datenbankpasswort         |
| `DB_NAME`     | `todolist`  | Name der Datenbank        |

---

## Datenbank

Das Schema wird beim ersten Start des MySQL-Containers automatisch aus `database/Initial-DB.sql` importiert.

### Tabellenstruktur `task`

| Spalte        | Typ           | Beschreibung              |
|---------------|---------------|---------------------------|
| `id`          | INT (PK, AI)  | Eindeutige ID             |
| `category`    | VARCHAR(80)   | Kategorie der Aufgabe     |
| `description` | VARCHAR(100)  | Beschreibung der Aufgabe  |
| `complete`    | BIT(1)        | Status: 0 = offen, 1 = erledigt |

### Testdaten

Die Datenbank enthält beim Start drei Beispielaufgaben:

| ID | Kategorie | Beschreibung      | Erledigt |
|----|-----------|-------------------|----------|
| 1  | Freizeit  | Spazieren gehen   | Ja       |
| 3  | Essen     | Einkaufen laufen  | Ja       |
| 4  | Freizeit  | Elden Ring        | Nein     |

---

## API-Endpunkte

Basis-URL: `http://localhost:8080/backend/api`

---

### POST `/addtask.php`

Fügt eine neue Aufgabe hinzu.

**Request-Body (JSON):**
```json
{
    "category": "Freizeit",
    "description": "Elden Ring spielen",
    "complete": 0
}
```

**Antwort (200):**
```json
{ "success": true }
```

---

### GET `/gettasks.php`

Gibt alle Aufgaben zurück. Kein Request-Body erforderlich.

**Antwort (200):**
```json
{
    "success": true,
    "tasks": [
        { "id": 1, "category": "Freizeit", "description": "Spazieren gehen", "complete": 1 }
    ]
}
```

---

### POST `/gettask.php`

Gibt eine einzelne Aufgabe anhand der ID zurück.

**Request-Body (JSON):**
```json
{ "id": 1 }
```

**Antwort (200):**
```json
{
    "success": true,
    "task": [
        { "id": 1, "category": "Freizeit", "description": "Spazieren gehen", "complete": 1 }
    ]
}
```

---

### PUT `/updatetask.php`

Aktualisiert alle Felder einer Aufgabe.

**Request-Body (JSON):**
```json
{
    "id": 1,
    "category": "Freizeit",
    "description": "Elden Ring spielen",
    "complete": 0
}
```

**Antwort (200):**
```json
{ "success": true }
```

---

### PUT `/updatetaskcomplete.php`

Aktualisiert nur den Erledigungsstatus einer Aufgabe.

**Request-Body (JSON):**
```json
{
    "id": 1,
    "complete": 1
}
```

**Antwort (200):**
```json
{ "success": true }
```

---

### DELETE `/deletetask.php`

Löscht eine Aufgabe anhand der ID.

**Request-Body (JSON):**
```json
{ "id": 2 }
```

**Antwort (200):**
```json
{ "success": true }
```

**Antwort (404 – nicht gefunden):**
```json
{ "error": "Task nicht gefunden." }
```

---

## Testing

Die Tests sind als PHPUnit **Integrationstests** implementiert und testen die API direkt über HTTP.

### Voraussetzung

Der PHP-Entwicklungsserver muss laufen:

```bash
php -S localhost:8000 -t ./backend/api/
```

### Tests ausführen

```bash
composer test
# oder direkt:
./vendor/bin/phpunit tests
```

### Testabdeckung

Die Testdatei `tests/GetTaskAPITest.php` deckt alle Endpunkte ab:

| Endpunkt               | Testfälle                                              |
|------------------------|--------------------------------------------------------|
| `GET /gettasks.php`    | Erfolgreiche Antwort, Felder vorhanden                 |
| `POST /gettask.php`    | Task per ID abrufen, nicht gefunden (9999), ungültige Eingabe |
| `POST /addtask.php`    | Task erstellen, fehlender Body                         |
| `POST /updatetask.php` | Task aktualisieren, ID=0 schlägt fehl, fehlender Body  |
| `POST /updatetaskcomplete.php` | Status setzen, ID=0 schlägt fehl, fehlender Body |
| `POST /deletetask.php` | Task löschen, nicht gefunden (9999), fehlender Body    |

---

## CI/CD Pipeline

Die GitHub Actions Pipeline (`.github/workflows/test.yml`) wird bei jedem Push oder Pull Request auf den `develop`-Branch ausgeführt.

### Ablauf

```
1. Repository auschecken
2. PHP 8.4 einrichten (mit mysqli, curl, json, pdo_mysql)
3. Composer-Abhängigkeiten installieren
4. MySQL-Testdatenbank aufsetzen (Initial-DB.sql importieren)
5. Umgebungsvariablen setzen (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, API_PORT)
6. PHP-Entwicklungsserver starten (localhost:8000)
7. 3 Sekunden warten bis API bereit ist
8. PHPUnit-Tests ausführen
```

### Services

Die Pipeline startet einen **MySQL 5.7**-Service-Container, der während der Tests als Datenbank dient.

### Trigger

| Ereignis       | Branch    |
|----------------|-----------|
| `push`         | `develop` |
| `pull_request` | `develop` |
