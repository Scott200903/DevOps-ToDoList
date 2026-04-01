<?php
function connectDB(): mysqli
{
    $servername = getenv('DB_HOST') ?: "localhost";
    $username   = getenv('DB_USER') ?: "root";
    $password   = getenv('DB_PASSWORD') ?: "geheim_1";
    $dbname     = getenv('DB_NAME') ?: "todolist";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new RuntimeException("Datenbankverbindung fehlgeschlagen: " . $conn->connect_error);
    }

    return $conn;
}
