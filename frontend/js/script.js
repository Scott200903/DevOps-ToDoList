const API_BASE = 'http://devopstodo/DevOps-ToDoList-develop/DevOps-ToDoList-develop/backend/api';
// const API_BASE = 'http://devopstodo/backend/api';

// Globale Variable zum Speichern der Task-ID für Bearbeitung
let editingTaskId = null;

// Benachrichtigungsfunktion
function showNotification(message, type = 'error', duration = 4000) {
    const container = document.getElementById('notification-container');
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    let icon = '⚠️';
    if (type === 'error') {
        icon = '❌';
    } else if (type === 'success') {
        icon = '✅';
    } else if (type === 'warning') {
        icon = '⚠️';
    }
    
    notification.innerHTML = `
        <span class="notification-icon">${icon}</span>
        <span class="notification-message">${message}</span>
        <button class="notification-close">&times;</button>
    `;
    
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.classList.add('exit');
        setTimeout(() => notification.remove(), 300);
    });
    
    container.appendChild(notification);
    
    // Automatisch entfernen nach duration
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('exit');
            setTimeout(() => notification.remove(), 300);
        }
    }, duration);
}

// Confirmation Dialog
function showConfirmation(message, callback) {
    const container = document.getElementById('notification-container');
    
    const confirmation = document.createElement('div');
    confirmation.className = 'modal show';
    confirmation.innerHTML = `
        <div class="modal-content" style="margin-top: 100px; max-width: 350px;">
            <h2>Bestätigung</h2>
            <div id="confirmation-message" style="margin: 15px 0; color: #333; font-size: 15px;"></div>
            <div class="modal-buttons">
                <button id="confirm-yes" style="background-color: #e74c3c; color: white;">Ja, löschen</button>
                <button id="confirm-no" style="background-color: #95a5a6; color: white;">Abbrechen</button>
            </div>
        </div>
    `;
    
    // Nachricht mit HTML-Support einfügen
    const messageDiv = confirmation.querySelector('#confirmation-message');
    messageDiv.innerHTML = message;
    
    document.body.appendChild(confirmation);
    
    const yesBtn = confirmation.querySelector('#confirm-yes');
    const noBtn = confirmation.querySelector('#confirm-no');
    
    yesBtn.addEventListener('click', () => {
        confirmation.remove();
        callback(true);
    });
    
    noBtn.addEventListener('click', () => {
        confirmation.remove();
        callback(false);
    });
    
    // Modal schließen bei Klick außerhalb
    confirmation.addEventListener('click', function(e) {
        if (e.target === confirmation) {
            confirmation.remove();
            callback(false);
        }
    });
}

// Beim Laden der Seite initialisieren
document.addEventListener('DOMContentLoaded', function() {
    loadTasks();
    setupEventListeners();
    setupScottEffect();
});

// Event Listener für alle Eingaben und Buttons
function setupEventListeners() {
    const categoryInput = document.getElementById('category-input');
    const descriptionInput = document.getElementById('description-input');
    const addButton = document.getElementById('add-button');
    
    // Validierung bei Eingabe
    [categoryInput, descriptionInput].forEach(input => {
        input.addEventListener('input', validateAddForm);
    });
    
    // Enter-Taste zum Hinzufügen
    descriptionInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !addButton.disabled) {
            addTask();
        }
    });
    
    // Plus-Button
    addButton.addEventListener('click', addTask);
    
    // Modal-Events
    setupModalEvents();
}

// Validierung des Hinzufügen-Formulars
function validateAddForm() {
    const categoryInput = document.getElementById('category-input');
    const descriptionInput = document.getElementById('description-input');
    const addButton = document.getElementById('add-button');
    
    const isValid = categoryInput.value.trim() !== '' && descriptionInput.value.trim() !== '';
    addButton.disabled = !isValid;
}

// API CALL 1: getTasks - Alle Tasks laden
function loadTasks() {
    fetch(`${API_BASE}/gettasks.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayTasks(data.tasks);
            } else {
                console.error('Fehler beim Laden der Aufgaben:', data);
                showNotification('Fehler beim Laden der Aufgaben', 'error');
            }
        })
        .catch(error => {
            console.error('Fehler beim Abrufen der Aufgaben:', error);
            showNotification('Fehler beim Abrufen der Aufgaben. Bitte versuchen Sie es später erneut.', 'error');
        });
}

// Tasks anzeigen
function displayTasks(tasks) {
    const taskListElement = document.getElementById('task-list');
    
    if (!tasks || tasks.length === 0) {
        taskListElement.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #999; padding: 20px;">Keine Aufgaben vorhanden</td></tr>';
        return;
    }
    
    taskListElement.innerHTML = '';

    tasks.forEach(task => {
        const taskRow = document.createElement('tr');
        taskRow.className = `task ${task.complete ? 'complete' : ''}`;
        taskRow.id = `task-${task.id}`;

        // Category Cell
        const categoryCell = document.createElement('td');
        categoryCell.className = 'task-category';
        categoryCell.textContent = task.category;

        // Description Cell
        const descriptionCell = document.createElement('td');
        descriptionCell.className = 'task-description';
        descriptionCell.textContent = task.description;

        // Status Cell (clickable zum ändern)
        const statusCell = document.createElement('td');
        statusCell.className = 'task-status-cell';
        const statusElement = document.createElement('span');
        statusElement.className = `task-status ${task.complete ? 'complete' : 'incomplete'}`;
        statusElement.textContent = task.complete ? 'Erledigt' : 'Offen';
        statusElement.addEventListener('click', () => toggleTaskStatus(task.id, task.complete));
        statusCell.appendChild(statusElement);

        // Actions Cell
        const actionsCell = document.createElement('td');
        actionsCell.className = 'task-actions-cell';
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'task-actions';

        // Edit Button
        const editButton = document.createElement('button');
        editButton.className = 'edit-button';
        editButton.innerHTML = '<i class="fas fa-pencil"></i>';
        editButton.title = 'Bearbeiten';
        if (task.complete) {
            // disable editing for completed tasks
            editButton.disabled = true;
            editButton.classList.add('disabled');
        } else {
            editButton.addEventListener('click', () => openEditModal(task));
        }

        // Delete Button
        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-button';
        deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
        deleteButton.title = 'Löschen';
        deleteButton.addEventListener('click', () => deleteTask(task.id, task.category, task.description));

        actionsDiv.appendChild(editButton);
        actionsDiv.appendChild(deleteButton);
        actionsCell.appendChild(actionsDiv);

        taskRow.appendChild(categoryCell);
        taskRow.appendChild(descriptionCell);
        taskRow.appendChild(statusCell);
        taskRow.appendChild(actionsCell);

        taskListElement.appendChild(taskRow);
    });
}

// API CALL 2: addTask - Neue Task hinzufügen
function addTask() {
    const categoryInput = document.getElementById('category-input');
    const descriptionInput = document.getElementById('description-input');
    
    const category = categoryInput.value.trim();
    const description = descriptionInput.value.trim();
    
    if (!category || !description) {
        showNotification('Bitte füllen Sie beide Felder aus', 'warning');
        return;
    }
    
    const taskData = {
        category: category,
        description: description,
        complete: 0
    };
    
    fetch(`${API_BASE}/addtask.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(taskData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerkantwort war nicht ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Eingabefelder leeren
            categoryInput.value = '';
            descriptionInput.value = '';
            validateAddForm();
            
            // Tasks neu laden
            loadTasks();
            showNotification('Aufgabe erfolgreich hinzugefügt', 'success');
        } else {
            showNotification('Fehler beim Hinzufügen der Aufgabe: ' + (data.error || 'Unbekannter Fehler'), 'error');
        }
    })
    .catch(error => {
        console.error('Fehler beim Hinzufügen der Aufgabe:', error);
        showNotification('Fehler beim Hinzufügen der Aufgabe', 'error');
    });
}

// API CALL 3: deleteTask - Task löschen
function deleteTask(taskId, category, description) {
    const message = `Möchten Sie diese Aufgabe wirklich löschen?\n\n<strong>${category}</strong>\n${description}`;
    showConfirmation(message, function(confirmed) {
        if (!confirmed) {
            return;
        }
        
        const deleteData = {
            id: taskId
        };
        
        fetch(`${API_BASE}/deletetask.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(deleteData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                loadTasks();
                showNotification('Aufgabe erfolgreich gelöscht', 'success');
            } else {
                showNotification('Fehler beim Löschen: ' + (data.error || 'Unbekannter Fehler'), 'error');
            }
        })
        .catch(error => {
            console.error('Fehler beim Löschen der Aufgabe:', error);
            showNotification('Fehler beim Löschen der Aufgabe', 'error');
        });
    });
}

// API CALL 4: updateTaskComplete - Status ändern
function toggleTaskStatus(taskId, currentComplete) {
    const newComplete = currentComplete ? 0 : 1;
    
    const updateData = {
        id: taskId,
        complete: newComplete
    };
    
    fetch(`${API_BASE}/updatetaskcomplete.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(updateData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerkantwort war nicht ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            loadTasks();
            showNotification('Status aktualisiert', 'success');
        } else {
            showNotification('Fehler beim Aktualisieren: ' + (data.error || 'Unbekannter Fehler'), 'error');
        }
    })
    .catch(error => {
        console.error('Fehler beim Aktualisieren des Status:', error);
        showNotification('Fehler beim Aktualisieren des Status', 'error');
    });
}

// MODAL FUNKTIONEN für Bearbeitung
function setupModalEvents() {
    const modal = document.getElementById('edit-modal');
    const closeBtn = document.querySelector('.close');
    const saveBtn = document.getElementById('save-button');
    const cancelBtn = document.getElementById('cancel-button');
    const editCategoryInput = document.getElementById('edit-category');
    const editDescriptionInput = document.getElementById('edit-description');
    
    closeBtn.addEventListener('click', closeEditModal);
    cancelBtn.addEventListener('click', closeEditModal);
    saveBtn.addEventListener('click', saveEditedTask);
    
    // Enter-Taste zum Speichern im Modal
    editCategoryInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            saveEditedTask();
        }
    });
    
    editDescriptionInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            saveEditedTask();
        }
    });
    
    // Modal schließen bei Klick außerhalb
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeEditModal();
        }
    });
}

function openEditModal(task) {
    editingTaskId = task.id;
    document.getElementById('edit-category').value = task.category;
    document.getElementById('edit-description').value = task.description;
    
    const modal = document.getElementById('edit-modal');
    modal.classList.add('show');
    
    // Focus auf erstes Eingabefeld
    document.getElementById('edit-category').focus();
}

function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    modal.classList.remove('show');
    editingTaskId = null;
}

// API CALL 5: updateTask - Task bearbeiten
function saveEditedTask() {
    const category = document.getElementById('edit-category').value.trim();
    const description = document.getElementById('edit-description').value.trim();
    
    if (!category || !description) {
        showNotification('Bitte füllen Sie beide Felder aus', 'warning');
        return;
    }
    
    const updateData = {
        id: editingTaskId,
        category: category,
        description: description,
        complete: 0 // Wird beim Update nicht geändert
    };
    
    fetch(`${API_BASE}/updatetask.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(updateData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerkantwort war nicht ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeEditModal();
            loadTasks();
            showNotification('Aufgabe erfolgreich aktualisiert', 'success');
        } else {
            showNotification('Fehler beim Aktualisieren: ' + (data.error || 'Unbekannter Fehler'), 'error');
        }
    })
    .catch(error => {
        console.error('Fehler beim Aktualisieren der Aufgabe:', error);
        showNotification('Fehler beim Aktualisieren der Aufgabe', 'error');
    });
}

// Scott easter egg functionality
let scottTimer = null;
let scottElem = null;
let scottInterval = null;

function setupScottEffect() {
    const trigger = document.getElementById('trigger-s');
    if (!trigger) return;
    trigger.addEventListener('mouseenter', startScottTimer);
    trigger.addEventListener('mouseleave', cancelScott);
}

function startScottTimer() {
    scottTimer = setTimeout(() => {
        showScott();
    }, 5000);
}

function cancelScott() {
    if (scottTimer) {
        clearTimeout(scottTimer);
        scottTimer = null;
    }
    removeScott();
}

function showScott() {
    if (scottElem) return;
    scottElem = document.createElement('div');
    scottElem.className = 'scott';
    scottElem.textContent = 'Scottyboiii';
    document.body.appendChild(scottElem);
    // move Scottyboiii once every half second for slower travel
    scottInterval = setInterval(moveScott, 500);
}

function removeScott() {
    if (scottInterval) {
        clearInterval(scottInterval);
        scottInterval = null;
    }
    if (scottElem) {
        scottElem.remove();
        scottElem = null;
    }
}

function moveScott() {
    if (!scottElem) return;
    const w = window.innerWidth - scottElem.offsetWidth;
    const h = window.innerHeight - scottElem.offsetHeight;
    const x = Math.random() * w;
    const y = Math.random() * h;
    scottElem.style.left = x + 'px';
    scottElem.style.top = y + 'px';
}
