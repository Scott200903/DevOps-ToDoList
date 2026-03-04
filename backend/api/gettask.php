<?php

require_once("../db/connect.php");
require_once("../classes/taskclass.php");

header("Content-Type: application/json");

// Daten extrahieren
$tasks = [];

try{
    $tasks = SelectTasks();
    echo json_encode(["tasks" => $tasks, "success" => "Daten erfolgreich zurückgeliefert"]);
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(["error" => "Fehler beim Speichern: " . $e->getMessage()]);
}

