document.addEventListener('DOMContentLoaded', function() {
    fetchTasks();
});

function fetchTasks() {
    fetch('http://devopstodo/backend/api/gettasks')
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

function displayTasks(tasks) {
    const taskListElement = document.getElementById('task-list');
    taskListElement.innerHTML = '';

    tasks.forEach(task => {
        const taskElement = document.createElement('div');
        taskElement.className = `task ${task.complete ? 'complete' : ''}`;

        const categoryElement = document.createElement('span');
        categoryElement.className = 'task-category';
        categoryElement.textContent = task.category;

        const descriptionElement = document.createElement('span');
        descriptionElement.className = 'task-description';
        descriptionElement.textContent = task.description;

        const statusElement = document.createElement('span');
        statusElement.className = `task-status ${task.complete ? '' : 'incomplete'}`;
        statusElement.textContent = task.complete ? 'Erledigt' : 'Offen';

        taskElement.appendChild(categoryElement);
        taskElement.appendChild(descriptionElement);
        taskElement.appendChild(statusElement);

        taskListElement.appendChild(taskElement);
    });
}
