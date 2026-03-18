<?php

use PHPUnit\Framework\TestCase;

class GetTaskAPITest extends TestCase
{
    private $apiUrl = "http://localhost:8000/gettask.php"; // URL für den lokalen Webserver

    public function getAPIURL(): string
    {
        return $this->apiUrl;
    }

    public function testGetTaskByIdSuccess()
    {
        $testId = 13; // ID eines existierenden Tasks in der Test-DB

        echo "API-URL: " . $this->apiUrl . "\n";
        $response = $this->callApi($testId);

        // Debug-Ausgaben
        echo "HTTP-Statuscode: " . $response['httpCode'] . "\n";
        echo "Antwort: " . $response['body'] . "\n";
        if (!empty($response['error'])) {
            echo "Fehler: " . $response['error'] . "\n";
        }
        if (!empty($response['debug'])) {
            echo "Debug-Log:\n" . $response['debug'] . "\n";
        }

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
        $testId = 9999; // Nicht-existierende Task-ID

        echo "API-URL: " . $this->apiUrl . "\n";
        $response = $this->callApi($testId);

        // Debug-Ausgaben
        echo "HTTP-Statuscode: " . $response['httpCode'] . "\n";
        echo "Antwort: " . $response['body'] . "\n";

        // Überprüfe den HTTP-Statuscode
        $this->assertEquals(200, $response['httpCode']);

        // Überprüfe die JSON-Struktur
        $data = json_decode($response['body'], true);
        $this->assertArrayHasKey('task', $data);
        $this->assertEmpty($data['task']);
    }

    public function testGetTaskByIdInvalidInput()
    {
        echo "API-URL: " . $this->apiUrl . "\n";
        $response = $this->callApi(null, []);

        // Debug-Ausgaben
        echo "HTTP-Statuscode: " . $response['httpCode'] . "\n";
        echo "Antwort: " . $response['body'] . "\n";

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

        // Debug-Ausgaben aktivieren
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true); // POST-Anfrage
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        // Debug-Ausgaben
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        if ($error) {
            echo "cURL-Fehler: " . $error . "\n";
        }

        if ($response === false) {
            echo "cURL-Antwort: false\n";
            echo "Debug-Log:\n" . $verboseLog . "\n";
        }

        curl_close($ch);

        return [
            'httpCode' => $httpCode,
            'body' => $response,
            'error' => $error,
            'debug' => $verboseLog
        ];
    }
}
