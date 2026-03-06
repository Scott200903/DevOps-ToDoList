// const API_BASE = 'http://devopstodo/DevOps-ToDoList-develop/DevOps-ToDoList-develop/backend/api';
const API_BASE = 'http://devopstodo/backend/api';

// Globale Variable zum Speichern der Task-ID für Bearbeitung
let editingTaskId = null;

// Beim Laden der Seite initialisieren
document.addEventListener('DOMContentLoaded', function() {
    loadTasks();
    setupEventListeners();
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
            }
        })
        .catch(error => {
            console.error('Fehler beim Abrufen der Aufgaben:', error);
        });
}

// Tasks anzeigen
function displayTasks(tasks) {
    const taskListElement = document.getElementById('task-list');
    
    if (!tasks || tasks.length === 0) {
        taskListElement.innerHTML = '<p style="text-align: center; color: #999;">Keine Aufgaben vorhanden</p>';
        return;
    }
    
    taskListElement.innerHTML = '';

    tasks.forEach(task => {
        const taskElement = document.createElement('div');
        taskElement.className = `task ${task.complete ? 'complete' : ''}`;
        taskElement.id = `task-${task.id}`;

        // Category
        const categoryElement = document.createElement('span');
        categoryElement.className = 'task-category';
        categoryElement.textContent = task.category;

        // Description
        const descriptionElement = document.createElement('span');
        descriptionElement.className = 'task-description';
        descriptionElement.textContent = task.description;

        // Status (clickable zum ändern)
        const statusElement = document.createElement('span');
        statusElement.className = `task-status ${task.complete ? 'complete' : 'incomplete'}`;
        statusElement.textContent = task.complete ? 'Erledigt' : 'Offen';
        statusElement.addEventListener('click', () => toggleTaskStatus(task.id, task.complete));

        // Actions Container
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'task-actions';

        // Edit Button
        const editButton = document.createElement('button');
        editButton.className = 'edit-button';
        editButton.innerHTML = '<i class="fas fa-pencil"></i>';
        editButton.title = 'Bearbeiten';
        editButton.addEventListener('click', () => openEditModal(task));

        // Delete Button
        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-button';
        deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
        deleteButton.title = 'Löschen';
        deleteButton.addEventListener('click', () => deleteTask(task.id));

        actionsDiv.appendChild(editButton);
        actionsDiv.appendChild(deleteButton);

        taskElement.appendChild(categoryElement);
        taskElement.appendChild(descriptionElement);
        taskElement.appendChild(statusElement);
        taskElement.appendChild(actionsDiv);

        taskListElement.appendChild(taskElement);
    });
}

// API CALL 2: addTask - Neue Task hinzufügen
function addTask() {
    const categoryInput = document.getElementById('category-input');
    const descriptionInput = document.getElementById('description-input');
    
    const category = categoryInput.value.trim();
    const description = descriptionInput.value.trim();
    
    if (!category || !description) {
        alert('Bitte füllen Sie beide Felder aus');
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
        } else {
            alert('Fehler beim Hinzufügen der Aufgabe: ' + (data.error || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler beim Hinzufügen der Aufgabe:', error);
        alert('Ein Fehler ist aufgetreten');
    });
}

// API CALL 3: deleteTask - Task löschen
function deleteTask(taskId) {
    if (!confirm('Möchten Sie diese Aufgabe wirklich löschen?')) {
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
        } else {
            alert('Fehler beim Löschen: ' + (data.error || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler beim Löschen der Aufgabe:', error);
        alert('Ein Fehler ist aufgetreten');
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
        } else {
            alert('Fehler beim Aktualisieren: ' + (data.error || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler beim Aktualisieren des Status:', error);
        alert('Ein Fehler ist aufgetreten');
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
        alert('Bitte füllen Sie beide Felder aus');
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
        } else {
            alert('Fehler beim Aktualisieren: ' + (data.error || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler beim Aktualisieren der Aufgabe:', error);
        alert('Ein Fehler ist aufgetreten');
    });
}
