<?php

use PHPUnit\Framework\TestCase;

/**
 * API-Integrationstests für die ToDo-Listen API.
 *
 * Voraussetzung: PHP-Server läuft auf localhost:8000 und zeigt auf ./backend/api/
 * Starten mit: php -S localhost:8000 -t ./backend/api/
 */
class GetTaskAPITest extends TestCase
{
    private string $baseUrl = "";

    protected function setUp(): void
    {
        $port = getenv('API_PORT') ?: "8080";
        $this->baseUrl = "http://localhost:{$port}/backend/api";
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /**
     * Sendet einen HTTP-Request an die API.
     *
     * @param string $endpoint  z. B. "/gettask.php"
     * @param string $method    GET, POST, PUT, DELETE
     * @param array|null $body  Wird als JSON gesendet (null = kein Body)
     * @return array{httpCode: int, body: string}
     */
    private function request(string $endpoint, string $method = 'POST', ?array $body = null): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return ['httpCode' => $httpCode, 'body' => $response ?: ''];
    }

    // -----------------------------------------------------------------------
    // GET /gettasks.php  – Alle Tasks abrufen
    // -----------------------------------------------------------------------

    public function testGetAllTasksReturnsArray(): void
    {
        $response = $this->request('/gettasks.php', 'GET');

        $this->assertEquals(200, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('tasks', $data);
        $this->assertArrayHasKey('success', $data);
        $this->assertIsArray($data['tasks']);
    }

    public function testGetAllTasksContainExpectedFields(): void
    {
        $response = $this->request('/gettasks.php', 'GET');
        $data = json_decode($response['body'], true);

        // Die Test-DB enthält mindestens einen Task
        $this->assertNotEmpty($data['tasks']);

        $task = $data['tasks'][0];
        $this->assertArrayHasKey('id', $task);
        $this->assertArrayHasKey('category', $task);
        $this->assertArrayHasKey('description', $task);
        $this->assertArrayHasKey('complete', $task);
    }

    // -----------------------------------------------------------------------
    // POST /gettask.php  – Einzelnen Task abrufen
    // -----------------------------------------------------------------------

    public function testGetTaskByIdSuccess(): void
    {
        // Erste vorhandene ID dynamisch ermitteln
        $all = $this->request('/gettasks.php', 'GET');
        $allData = json_decode($all['body'], true);
        $this->assertNotEmpty($allData['tasks'], 'Datenbank muss mindestens einen Task enthalten');
        $existingId = $allData['tasks'][0]['id'];

        $response = $this->request('/gettask.php', 'POST', ['id' => $existingId]);

        $this->assertEquals(200, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('task', $data);
        $this->assertArrayHasKey('success', $data);
        $this->assertNotEmpty($data['task']);

        $task = $data['task'][0];
        $this->assertEquals($existingId, $task['id']);
        $this->assertArrayHasKey('category', $task);
        $this->assertArrayHasKey('description', $task);
        $this->assertArrayHasKey('complete', $task);
    }

    public function testGetTaskByIdNotFound(): void
    {
        $response = $this->request('/gettask.php', 'POST', ['id' => 9999]);

        $this->assertEquals(200, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('task', $data);
        $this->assertEmpty($data['task']);
    }

    public function testGetTaskByIdInvalidInput(): void
    {
        // Leerer Body → 400
        $response = $this->request('/gettask.php', 'POST', []);

        $this->assertEquals(400, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Keine Daten empfangen.", $data['error']);
    }

    // -----------------------------------------------------------------------
    // POST /addtask.php  – Task erstellen
    // -----------------------------------------------------------------------

    public function testAddTaskSuccess(): void
    {
        $response = $this->request('/addtask.php', 'POST', [
            'category'    => 'Test',
            'description' => 'Automatisch erstellter Task',
            'complete'    => 0,
        ]);

        $this->assertEquals(200, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('success', $data);
    }

    public function testAddTaskMissingBody(): void
    {
        $response = $this->request('/addtask.php', 'POST', []);

        $this->assertEquals(400, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Keine Daten empfangen.", $data['error']);
    }

    // -----------------------------------------------------------------------
    // POST /updatetask.php  – Task vollständig aktualisieren
    // -----------------------------------------------------------------------

    public function testUpdateTaskSuccess(): void
    {
        $all = $this->request('/gettasks.php', 'GET');
        $existingId = json_decode($all['body'], true)['tasks'][0]['id'];

        $response = $this->request('/updatetask.php', 'POST', [
            'id'          => $existingId,
            'category'    => 'Freizeit',
            'description' => 'Spazieren gehen (aktualisiert)',
            'complete'    => 1,
        ]);

        $this->assertEquals(200, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('success', $data);
    }

    public function testUpdateTaskWithIdZeroFails(): void
    {
        $response = $this->request('/updatetask.php', 'POST', [
            'id'          => 0,
            'category'    => 'Test',
            'description' => 'Sollte fehlschlagen',
            'complete'    => 0,
        ]);

        $this->assertEquals(400, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('failure', $data);
    }

    public function testUpdateTaskMissingBody(): void
    {
        $response = $this->request('/updatetask.php', 'POST', []);

        $this->assertEquals(400, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $data);
    }

    // -----------------------------------------------------------------------
    // POST /updatetaskcomplete.php  – Nur Status aktualisieren
    // -----------------------------------------------------------------------

    public function testUpdateTaskCompleteSuccess(): void
    {
        $all = $this->request('/gettasks.php', 'GET');
        $existingId = json_decode($all['body'], true)['tasks'][0]['id'];

        $response = $this->request('/updatetaskcomplete.php', 'POST', [
            'id'       => $existingId,
            'complete' => 0,
        ]);

        $this->assertEquals(200, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('success', $data);
    }

    public function testUpdateTaskCompleteWithIdZeroFails(): void
    {
        $response = $this->request('/updatetaskcomplete.php', 'POST', [
            'id'       => 0,
            'complete' => 1,
        ]);

        $this->assertEquals(400, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('failure', $data);
    }

    public function testUpdateTaskCompleteMissingBody(): void
    {
        $response = $this->request('/updatetaskcomplete.php', 'POST', []);

        $this->assertEquals(400, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $data);
    }

    // -----------------------------------------------------------------------
    // POST /deletetask.php  – Task löschen
    // -----------------------------------------------------------------------

    public function testDeleteTaskSuccess(): void
    {
        // Zuerst einen neuen Task anlegen, dann löschen
        $add = $this->request('/addtask.php', 'POST', [
            'category'    => 'ZumLöschen',
            'description' => 'Wird gleich gelöscht',
            'complete'    => 0,
        ]);
        $this->assertEquals(200, $add['httpCode'], 'Voraussetzung: Task anlegen muss klappen');

        // ID des neu erstellten Tasks ermitteln
        $allTasks = $this->request('/gettasks.php', 'GET');
        $data     = json_decode($allTasks['body'], true);
        $tasks    = $data['tasks'];

        // Letzten Task mit der Kategorie "ZumLöschen" finden
        $newTask = null;
        foreach (array_reverse($tasks) as $t) {
            if ($t['category'] === 'ZumLöschen') {
                $newTask = $t;
                break;
            }
        }
        $this->assertNotNull($newTask, 'Neu erstellter Task muss in der Liste sein');

        $delete = $this->request('/deletetask.php', 'POST', ['id' => $newTask['id']]);

        $this->assertEquals(200, $delete['httpCode']);

        $deleteData = json_decode($delete['body'], true);
        $this->assertArrayHasKey('success', $deleteData);
    }

    public function testDeleteTaskNotFound(): void
    {
        $response = $this->request('/deletetask.php', 'POST', ['id' => 9999]);

        $this->assertEquals(404, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Task nicht gefunden.", $data['error']);
    }

    public function testDeleteTaskMissingBody(): void
    {
        $response = $this->request('/deletetask.php', 'POST', []);

        $this->assertEquals(400, $response['httpCode']);

        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Keine Daten empfangen.", $data['error']);
    }
}
