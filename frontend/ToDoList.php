<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevOps ToDo-Liste</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div id="notification-container" class="notification-container"></div>
    
    <div class="container">
        <h1>DevOps ToDo-Li<span id="trigger-s">s</span>te</h1>
        
        <!-- Eingabebereich für neue Tasks -->
        <div class="add-task-container">
            <input type="text" id="category-input" placeholder="Kategorie eingeben..." />
            <input type="text" id="description-input" placeholder="Aufgabe eingeben..." />
            <button id="add-button" disabled>
                <i class="fas fa-plus"></i>
            </button>
        </div>

        <!-- Task-Liste Tabelle -->
        <table id="task-table" class="task-table">
            <thead>
                <tr class="task-header">
                    <th class="header-category">Kategorie</th>
                    <th class="header-description">Aufgabe</th>
                    <th class="header-status">Status</th>
                    <th class="header-actions">Aktionen</th>
                </tr>
            </thead>
            <tbody id="task-list">
                <!-- Tasks werden hier dynamisch eingefügt -->
            </tbody>
        </table>

        <!-- Modal für Bearbeitung -->
        <div id="edit-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Aufgabe bearbeiten</h2>
                <input type="text" id="edit-category" placeholder="Kategorie" />
                <input type="text" id="edit-description" placeholder="Aufgabe" />
                <div class="modal-buttons">
                    <button id="save-button">Speichern</button>
                    <button id="cancel-button">Abbrechen</button>
                </div>
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
