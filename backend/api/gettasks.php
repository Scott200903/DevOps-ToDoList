<?php

require_once("../db/connect.php");
require_once("../db/sql/tasksql.php");

header("Content-Type: application/json");

// Daten extrahieren
$tasks = [];

try{
    $tasks = SelectTasks();

    // Wandelt jedes Task-Objekt in ein Array um
    $tasksArray = array_map(function($task) {
        return [
            'id' => $task->getId(),          // Annahme: Es gibt Getter-Methoden
            'category' => $task->getCategory(),
            'description' => $task->getDescription(),
            'complete' => $task->getComplete(),
        ];
    }, $tasks);

    // Serialisiere das Array zu JSON
    echo json_encode([
        "tasks" => $tasksArray,
        "success" => "Daten erfolgreich zurückgeliefert"
    ]);
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(["error" => "Fehler beim Speichern: " . $e->getMessage()]);
}

