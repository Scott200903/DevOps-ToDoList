# DevOps-ToDoList

A lightweight **ToDo-List application** built with **HTML, CSS, JavaScript, and PHP**.
Supports **CRUD operations** (Create, Read, Update, Delete) for efficient task management.

## Features
- **Create:** Add new tasks with a title and optional description.
- **Read:** Display all tasks in a clear, organized list.
- **Update:** Mark tasks as *completed* or *pending*, and edit task details.
- **Delete:** Remove tasks that are no longer needed.
- **Simple UI:** Intuitive interface for quick task management.


# DevOps-ToDoList

Eine einfache **ToDo-Liste**, entwickelt mit **HTML, CSS, JavaScript und PHP**.
Unterstützt **CRUD-Operationen** (Erstellen, Lesen, Aktualisieren, Löschen) für effizientes Aufgabenmanagement.

## Funktionen
- **Erstellen:** Neue Aufgaben mit Titel und optionaler Beschreibung hinzufügen.
- **Anzeigen:** Alle Aufgaben in einer übersichtlichen Liste darstellen.
- **Aktualisieren:** Aufgaben als *erledigt* oder *ausstehend* markieren und Details bearbeiten.
- **Löschen:** Aufgaben entfernen, die nicht mehr benötigt werden.
- **Einfache Bedienung:** Intuitive Oberfläche für schnelles Aufgabenmanagement.

## Endpunkte

### POST `/addtask`
**Beschreibung**:
Fügt eine neue Aufgabe anhand der übergebenen `category`, `description` und `complete`.

**Authentifizierung**:
Keine (auth: none)

**Request-Format**:
`POST devopstodo/backend/api/addtask`

**Request-Body (JSON)**:
```json
{
    "category": "Freizeit",
    "description": "Elden Ring spielen",
    "complete": 0
}
```

### GET `/gettask`
**Beschreibung**:
Listet eine Aufgabe der anhand der übergebenen `id` auf.

**Authentifizierung**:
Keine (auth: none)

**Request-Format**:
`GET devopstodo/backend/api/gettask`

**Request-Body (JSON)**:
```json
{
    "id": 1
}
```

### GET `/gettasks`
**Beschreibung**:
Listet alle Aufgabe auf.

**Authentifizierung**:
Keine (auth: none)

**Request-Format**:
`GET devopstodo/backend/api/gettasks`

**Request-Body (JSON)**:
```json
KEIN BODY BENÖTIGT
```

### PUT `/updatetask`
**Beschreibung**:
Aktualisiert eine Aufgabe anhand der übergebenen `id`, `category`, `description` und `complete`.

**Authentifizierung**:
Keine (auth: none)

**Request-Format**:
`PUT devopstodo/backend/api/updatetask`

**Request-Body (JSON)**:
```json
{
    "id": 1,
    "category": "Freizeit",
    "description": "Elden Ring spielen",
    "complete": 0
}
```

### PUT `/updatetaskcomplete`
**Beschreibung**:
Aktualisiert den Status einer Aufgabe anhand der übergebenen `id` und `complete`.

**Authentifizierung**:
Keine (auth: none)

**Request-Format**:
`PUT devopstodo/backend/api/updatetaskcomplete`

**Request-Body (JSON)**:
```json
{
    "id": 1,
    "complete": 0
}
```

### DELETE `/deletetask`

**Beschreibung**:
Löscht eine bestehende Aufgabe anhand der übergebenen `id`.

**Authentifizierung**:
Keine (auth: none)

**Request-Format**:
`DELETE devopstodo/backend/api/deletetask`

**Request-Body (JSON)**:
```json
{
  "id": 2
}