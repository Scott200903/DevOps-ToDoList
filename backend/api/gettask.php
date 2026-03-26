<?php

require_once("../db/connect.php");
require_once("../db/sql/tasksql.php");

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

// Überprüfen, ob Daten vorhanden sind
if (empty($input)) {
    http_response_code(400);
    echo json_encode(["error" => "Keine Daten empfangen."]);
    exit;
}

// Daten extrahieren
$id_task = isset($input["id"]) ? $input["id"] : 0;

try{
    $task = SelectTaskById($id_task);

    // Wandelt jedes Task-Objekt in ein Array um
    $tasksArray = array_map(function($task) {
        return [
            'id' => $task->getId(),          // Annahme: Es gibt Getter-Methoden
            'category' => $task->getCategory(),
            'description' => $task->getDescription(),
            'complete' => $task->getComplete(),
        ];
    }, $task);

    // Serialisiere das Array zu JSON
    echo json_encode([
        "task" => $tasksArray,
        "success" => "Daten erfolgreich zurückgeliefert"
    ]);
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(["error" => "Fehler beim Abrufen: " . $e->getMessage()]);
}

