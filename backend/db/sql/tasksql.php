<?php

include_once(__DIR__ . '/../connect.php');
include_once(__DIR__ . '/../../classes/taskclass.php');

function InsertTask(Task $t)
{
    if (!isset($t)) {
        return 0;
    }

    $conn = connectDB();

    $stmt = $conn->prepare("INSERT INTO task(category, description, complete) VALUES (?,?,?)");

    $cate = $t->getCategory();
    $desc = $t->getDescription();
    $compl = $t->getComplete();

    $stmt->bind_param("ssi", $cate, $desc, $compl);

    $stmt->execute();

    $lastinserted = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    return $lastinserted;
}
function SelectTaskById($id)
{
    $tasks = [];

    if (!isset($id)) {
        return 0;
    }

    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM task WHERE id = ?;");

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($rows = $result->fetch_assoc()) {
            $task = new Task();

            $task->setId($rows['id']);
            $task->setCategory($rows['category']);
            $task->setDescription($rows['description']);
            $task->setComplete($rows['complete']);

            $tasks[] = $task;
        }
    }

    $conn->close();
    return $tasks;
}

function SelectTasks()
{
    $tasks = [];

    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM task;");

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($rows = $result->fetch_assoc()) {
            $task = new Task();

            $task->setId($rows['id']);
            $task->setCategory($rows['category']);
            $task->setDescription($rows['description']);
            $task->setComplete($rows['complete']);

            $tasks[] = $task;
        }
    }

    $conn->close();
    return $tasks;
}

function DeleteTask($id)
{
    $conn = connectDB();

    $task = SelectTaskById($id);

    if (isset($task)) {
        $stmt = $conn->prepare("DELETE FROM task WHERE id = ?;");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $stmt->close();
    }

    $conn->close();
    return 1;
}

function UpdateTask(Task $t)
{
    if (!$t || !$t->getId()) {
        return false;
    }

    $conn = connectDB();
    $stmt = $conn->prepare("
        UPDATE task
        SET category = ?, description = ?, complete = ?
        WHERE id = ?
    ");

    $cate = $t->getCategory();
    $desc = $t->getDescription();
    $compl = $t->getComplete();
    $id = $t->getId();

    $stmt->bind_param("ssii", $cate, $desc, $compl, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}

/**
 * Toggelt den Complete-Status eines Tasks.
 * @param int $id ID des Tasks.
 * @param int $complete Neuer Complete-Status (0 oder 1).
 * @return bool True bei Erfolg, False bei Fehler.
 */
function UpdateTaskComplete($id, $complete)
{
    if (!isset($id) || !in_array($complete, [0, 1])) {
        return false;
    }

    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE task SET complete = ? WHERE id = ?");
    $stmt->bind_param("ii", $complete, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}
