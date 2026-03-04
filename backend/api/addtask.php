<?php

require_once("../db/connect.php");
require_once("../classes/taskclass.php");

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
$task_category = isset($input["category"]) ? $input["category"] : "Keine Kategorie";
$task_description = isset($input["description"]) ? $input["description"] : "Keine Bezeichnung";
$task_complete = isset($input["complete"]) ? $input["complete"] : 0;

$task->setCategory($task_category);
$task->setDescription($task_description);
$task->setComplete($task_complete);

try {
    $id = InsertTask($task);
    echo json_encode(["success" => "Task erfolgreich gespeichert."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Fehler beim Speichern: " . $e->getMessage()]);
}

