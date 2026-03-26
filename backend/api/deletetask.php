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
$id_task = isset($input["id"]) ? $input["id"] : 0;

try {
    $found = DeleteTask($id_task);
    if ($found) {
        echo json_encode(["success" => "Task erfolgreich gelöscht."]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Task nicht gefunden."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Fehler beim Löschen: " . $e->getMessage()]);
}

