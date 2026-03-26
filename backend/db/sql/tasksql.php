<?php

include_once(__DIR__ . '/../connect.php');
include_once(__DIR__ . '/../../classes/taskclass.php');

function InsertTask(Task $t): int
{
    $conn = connectDB();

    $stmt = $conn->prepare("INSERT INTO task(category, description, complete) VALUES (?,?,?)");
    if (!$stmt) {
        throw new RuntimeException("Prepare fehlgeschlagen: " . $conn->error);
    }

    $cate  = $t->getCategory();
    $desc  = $t->getDescription();
    $compl = $t->getComplete();

    $stmt->bind_param("ssi", $cate, $desc, $compl);
    $stmt->execute();

    $lastInserted = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    return $lastInserted;
}

function SelectTaskById(int $id): array
{
    $tasks = [];

    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM task WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Prepare fehlgeschlagen: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $task = new Task();
        $task->setId((int)$row['id']);
        $task->setCategory((string)$row['category']);
        $task->setDescription((string)$row['description']);
        $task->setComplete((int)$row['complete']);
        $tasks[] = $task;
    }

    $stmt->close();
    $conn->close();
    return $tasks;
}

function SelectTasks(): array
{
    $tasks = [];

    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM task ORDER BY category ASC");
    if (!$stmt) {
        throw new RuntimeException("Prepare fehlgeschlagen: " . $conn->error);
    }

    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $task = new Task();
        $task->setId((int)$row['id']);
        $task->setCategory((string)$row['category']);
        $task->setDescription((string)$row['description']);
        $task->setComplete((int)$row['complete']);
        $tasks[] = $task;
    }

    $stmt->close();
    $conn->close();
    return $tasks;
}

function DeleteTask(int $id): bool
{
    $existing = SelectTaskById($id);
    if (empty($existing)) {
        return false;
    }

    $conn = connectDB();
    $stmt = $conn->prepare("DELETE FROM task WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Prepare fehlgeschlagen: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    return true;
}

function UpdateTask(Task $t): bool
{
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE task SET category = ?, description = ?, complete = ? WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Prepare fehlgeschlagen: " . $conn->error);
    }

    $cate  = $t->getCategory();
    $desc  = $t->getDescription();
    $compl = $t->getComplete();
    $id    = $t->getId();

    $stmt->bind_param("ssii", $cate, $desc, $compl, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}

function UpdateTaskComplete(int $id, int $complete): bool
{
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE task SET complete = ? WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Prepare fehlgeschlagen: " . $conn->error);
    }

    $stmt->bind_param("ii", $complete, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}
