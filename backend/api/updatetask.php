<?php

require_once("../db/connect.php");
require_once("../db/sql/tasksql.php");

header("Content-Type: application/json");

// Daten aus dem Request lesen
$input = json_decode(file_get_contents("php://input"), true);

// Überprüfen, ob Daten vorhanden sind
if (empty($input)) {
    http_response_code(400);
    echo json_encode(["error" => "Keine Daten empfangen."]);
    exit;
}

// Daten extrahieren
$task = new Task();
$task_id = isset($input["id"]) ? $input["id"] : 0;
$task_category = isset($input["category"]) ? $input["category"] : "Keine Kategorie";
$task_description = isset($input["description"]) ? $input["description"] : "Keine Bezeichnung";
$task_complete = isset($input["complete"]) ? $input["complete"] : 0;

$task->setId($task_id);
$task->setCategory($task_category);
$task->setDescription($task_description);
$task->setComplete($task_complete);

try {
    if($task->getId() != 0){
        UpdateTask($task);
        echo json_encode(["success" => "Task updaten erfolgreich."]);
    }
    else{
        echo json_encode(["failure" => "Task updaten fehlgeschlagen."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Fehler beim Speichern: " . $e->getMessage()]);
}

