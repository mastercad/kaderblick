<?php

declare(strict_types=1);

namespace Tests\Feature\Controller;

use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\ApiWebTestCase;

/**
 * Feature-Tests für den SurveyController (/api/surveys).
 *
 * Fixtures werden über bootstrap.php geladen (--group=master --group=test).
 * Rollen: user6 = ROLE_USER, user16 = ROLE_ADMIN, user21 = ROLE_SUPERADMIN
 */
final class SurveyControllerTest extends ApiWebTestCase
{
    // ========== LIST ==========

    public function testListSurveysAsAuthenticatedUser(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $client->request('GET', '/api/surveys');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
    }

    // ========== CREATE ==========

    public function testCreateSurveyAsAdminSucceeds(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Test-Umfrage',
            'description' => 'Beschreibung der Testumfrage',
            'platform' => true,
            'questions' => [
                [
                    'questionText' => 'Wie findest du das?',
                    'type' => 'text',
                ],
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('id', $data);
    }

    public function testCreateSurveyWithChoiceQuestionsAndOptions(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Hole verfügbare System-Optionen
        $client->request('GET', '/api/survey-options');
        $options = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($options, 'Es sollten System-Optionen aus den Fixtures existieren.');

        $optionIds = array_slice(array_column($options, 'id'), 0, 3);

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Auswahl-Umfrage',
            'description' => 'Test mit Auswahloptionen',
            'platform' => true,
            'questions' => [
                [
                    'questionText' => 'Stimmst du zu?',
                    'type' => 'single_choice',
                    'options' => $optionIds,
                ],
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('id', $data);

        // Survey laden und Optionen prüfen
        $client->request('GET', '/api/surveys/' . $data['id']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $survey = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(1, $survey['questions']);
        self::assertCount(count($optionIds), $survey['questions'][0]['options']);
    }

    public function testCreateSurveyWithInlineNewOption(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Umfrage mit neuer Option',
            'platform' => true,
            'questions' => [
                [
                    'questionText' => 'Welcher Tag?',
                    'type' => 'single_choice',
                    'options' => ['Montag', 'Dienstag', 'Mittwoch'],
                ],
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($client->getResponse()->getContent(), true);

        // Survey laden und prüfen, ob die inline-erstellten Optionen vorhanden sind
        $client->request('GET', '/api/surveys/' . $data['id']);
        $survey = json_decode($client->getResponse()->getContent(), true);

        $optionTexts = array_map(fn ($o) => $o['optionText'], $survey['questions'][0]['options']);
        self::assertContains('Montag', $optionTexts);
        self::assertContains('Dienstag', $optionTexts);
        self::assertContains('Mittwoch', $optionTexts);
    }

    public function testCreateSurveyAsRegularUserFails(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Nicht erlaubt',
            'platform' => true,
            'questions' => [
                ['questionText' => 'Frage?', 'type' => 'text'],
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateSurveyWithInvalidPayloadFails(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'description' => 'Kein Titel, keine Fragen',
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCreateSurveyWithUnknownTypeFails(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Umfrage mit unbekanntem Typ',
            'platform' => true,
            'questions' => [
                ['questionText' => 'Frage?', 'type' => 'nonexistent_type'],
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    // ========== SHOW ==========

    public function testShowCreatedSurvey(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Erstelle eine Umfrage
        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Show-Test',
            'platform' => true,
            'questions' => [
                ['questionText' => 'Frage 1', 'type' => 'text'],
                ['questionText' => 'Frage 2', 'type' => 'scale_1_5'],
            ],
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);

        // Lese die Umfrage
        $client->request('GET', '/api/surveys/' . $data['id']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $survey = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals('Show-Test', $survey['title']);
        self::assertCount(2, $survey['questions']);
        self::assertEquals('text', $survey['questions'][0]['type']);
        self::assertEquals('scale_1_5', $survey['questions'][1]['type']);
    }

    // ========== UPDATE ==========

    public function testUpdateSurveyAsAdmin(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Erstelle Umfrage
        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Vor Update',
            'platform' => true,
            'questions' => [
                ['questionText' => 'Alte Frage', 'type' => 'text'],
            ],
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);

        // Update
        $client->request('PUT', '/api/surveys/' . $data['id'], [], [], [], json_encode([
            'title' => 'Nach Update',
            'platform' => true,
            'questions' => [
                ['questionText' => 'Neue Frage 1', 'type' => 'text'],
                ['questionText' => 'Neue Frage 2', 'type' => 'scale_1_10'],
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Kontrolliere Änderungen
        $client->request('GET', '/api/surveys/' . $data['id']);
        $survey = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals('Nach Update', $survey['title']);
        self::assertCount(2, $survey['questions']);
    }

    public function testUpdateSurveyAsRegularUserFails(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Erstelle als Admin
        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Admin-Umfrage',
            'platform' => true,
            'questions' => [['questionText' => 'Frage', 'type' => 'text']],
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);

        // User versucht zu bearbeiten
        $this->authenticateUser($client, 'user6@example.com');
        $client->request('PUT', '/api/surveys/' . $data['id'], [], [], [], json_encode([
            'title' => 'Geändert',
            'questions' => [['questionText' => 'Neue Frage', 'type' => 'text']],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    // ========== SUBMIT ==========

    public function testSubmitSurveyResponse(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Erstelle Umfrage mit Text-Frage
        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Antwort-Test',
            'platform' => true,
            'questions' => [
                ['questionText' => 'Was denkst du?', 'type' => 'text'],
            ],
        ]));
        $surveyData = json_decode($client->getResponse()->getContent(), true);

        // Survey laden, um Question-IDs zu bekommen
        $client->request('GET', '/api/surveys/' . $surveyData['id']);
        $survey = json_decode($client->getResponse()->getContent(), true);
        $questionId = $survey['questions'][0]['id'];

        // Antwort als normaler User abgeben
        $this->authenticateUser($client, 'user6@example.com');
        $client->request('POST', '/api/surveys/' . $surveyData['id'] . '/submit', [], [], [], json_encode([
            'answers' => [
                $questionId => 'Das ist meine Antwort',
            ],
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testSubmitSurveyResponseTwiceFails(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Doppelt-Test',
            'platform' => true,
            'questions' => [['questionText' => 'Frage?', 'type' => 'text']],
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);

        // Survey laden -> Question ID
        $client->request('GET', '/api/surveys/' . $data['id']);
        $survey = json_decode($client->getResponse()->getContent(), true);
        $qId = $survey['questions'][0]['id'];

        // Erste Antwort
        $this->authenticateUser($client, 'user7@example.com');
        $client->request('POST', '/api/surveys/' . $data['id'] . '/submit', [], [], [], json_encode([
            'answers' => [$qId => 'Erster Versuch'],
        ]));
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Zweite Antwort vom gleichen User
        $client->request('POST', '/api/surveys/' . $data['id'] . '/submit', [], [], [], json_encode([
            'answers' => [$qId => 'Zweiter Versuch'],
        ]));
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    // ========== RESULTS ==========

    public function testResultsAsAdmin(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Umfrage erstellen
        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Ergebnis-Test',
            'platform' => true,
            'questions' => [['questionText' => 'Frage?', 'type' => 'text']],
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);

        // Ergebnisse abrufen
        $client->request('GET', '/api/surveys/' . $data['id'] . '/results');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $results = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('answers_total', $results);
        self::assertArrayHasKey('results', $results);
    }

    // ========== DELETE ==========

    public function testDeleteSurveyAsAdmin(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Lösch-Test',
            'platform' => true,
            'questions' => [['questionText' => 'Frage?', 'type' => 'text']],
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);

        $client->request('DELETE', '/api/surveys/' . $data['id']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $resp = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($resp['success']);
    }

    public function testDeleteSurveyAsRegularUserFails(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/api/surveys', [], [], [], json_encode([
            'title' => 'Nicht löschbar',
            'platform' => true,
            'questions' => [['questionText' => 'Frage?', 'type' => 'text']],
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->authenticateUser($client, 'user6@example.com');
        $client->request('DELETE', '/api/surveys/' . $data['id']);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
