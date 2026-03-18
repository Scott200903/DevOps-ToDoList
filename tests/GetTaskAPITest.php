<?php
use PHPUnit\Framework\TestCase;

class TaskApiTest extends TestCase
{
    private $apiUrl = "http://devopstodo/backend/api/gettasks.php"; // Anpassen!

    public function testGetTaskByIdSuccess()
    {
        // Mock-Daten für den Test
        $testId = 1; // ID eines existierenden Tasks in der Test-DB

        // API-Aufruf simulieren
        $response = $this->callApi($testId);

        // Überprüfe den HTTP-Statuscode
        $this->assertEquals(200, $response['httpCode']);

        // Überprüfe die JSON-Struktur
        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('task', $data);
        $this->assertArrayHasKey('success', $data);

        // Überprüfe die Task-Daten
        $task = $data['task'][0];
        $this->assertEquals($testId, $task['id']);
        $this->assertArrayHasKey('category', $task);
        $this->assertArrayHasKey('description', $task);
        $this->assertArrayHasKey('complete', $task);
    }

    public function testGetTaskByIdNotFound()
    {
        // Nicht-existierende Task-ID
        $testId = 9999;

        // API-Aufruf simulieren
        $response = $this->callApi($testId);

        // Überprüfe den HTTP-Statuscode
        $this->assertEquals(200, $response['httpCode']); // Oder 404, je nach Implementierung

        // Überprüfe die JSON-Struktur
        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('task', $data);
        $this->assertEmpty($data['task']); // Leeres Array, wenn kein Task gefunden
    }

    public function testGetTaskByIdInvalidInput()
    {
        // Ungültige Eingabe (keine ID)
        $response = $this->callApi(null, []);

        // Überprüfe den HTTP-Statuscode
        $this->assertEquals(400, $response['httpCode']);

        // Überprüfe die Fehlermeldung
        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Keine Daten empfangen.", $data['error']);
    }

    private function callApi($id, $input = null)
    {
        if ($input === null) {
            $input = ['id' => $id];
        }

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'httpCode' => $httpCode,
            'body' => $response
        ];
    }
}
