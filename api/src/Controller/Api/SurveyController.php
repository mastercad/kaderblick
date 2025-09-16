<?php

namespace App\Controller\Api;

use App\Entity\Club;
use App\Entity\Survey;
use App\Entity\SurveyOption;
use App\Entity\SurveyQuestion;
use App\Entity\SurveyResponse;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\SurveyOptionTypeRepository;
use App\Repository\SurveyRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/surveys', name: 'api_surveys_')]
class SurveyController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Survey $survey): JsonResponse
    {
        $this->em->remove($survey);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(SurveyRepository $surveyRepository): JsonResponse
    {
        $surveys = $surveyRepository->findAll();
        $data = array_map(fn ($survey) => [
            'id' => $survey->getId(),
            'title' => $survey->getTitle(),
            'description' => $survey->getDescription(),
        ], $surveys);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Survey $survey): JsonResponse
    {
        $questions = $survey->getQuestions()->map(fn ($q) => [
            'id' => $q->getId(),
            'questionText' => $q->getQuestionText(),
            'type' => $q->getType()?->getTypeKey(),
            'options' => $q->getOptions()->map(fn ($o) => [
                'id' => $o->getId(),
                'optionText' => $o->getOptionText(),
            ])->toArray(),
        ])->toArray();
        $data = [
            'id' => $survey->getId(),
            'title' => $survey->getTitle(),
            'description' => $survey->getDescription(),
            'dueDate' => $survey->getDueDate() ? $survey->getDueDate()->format('Y-m-d') : null,
            'teamIds' => $survey->getTeams()->map(fn ($t) => $t->getId())->toArray(),
            'clubIds' => $survey->getClubs()->map(fn ($c) => $c->getId())->toArray(),
            'platform' => $survey->isPlatform(),
            'questions' => $questions,
        ];

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, SurveyOptionTypeRepository $typeRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['title'], $data['questions'])) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        $survey = new Survey();
        $survey->setTitle($data['title']);
        $survey->setDescription($data['description'] ?? null);
        if (!empty($data['dueDate'])) {
            $survey->setDueDate(new DateTime($data['dueDate']));
        }
        if (!empty($data['teamIds']) && is_array($data['teamIds'])) {
            foreach ($data['teamIds'] as $teamId) {
                $team = $this->em->getRepository(Team::class)->find($teamId);
                if ($team) {
                    $survey->addTeam($team);
                }
            }
        }
        if (!empty($data['clubIds']) && is_array($data['clubIds'])) {
            foreach ($data['clubIds'] as $clubId) {
                $club = $this->em->getRepository(Club::class)->find($clubId);
                if ($club) {
                    $survey->addClub($club);
                }
            }
        }
        if (!empty($data['platform'])) {
            $survey->setPlatform((bool) $data['platform']);
        }

        foreach ($data['questions'] as $q) {
            if (!isset($q['questionText'], $q['type'])) {
                continue;
            }
            $question = new SurveyQuestion();
            $question->setSurvey($survey);
            $question->setQuestionText($q['questionText']);
            $type = $typeRepo->findOneBy(['typeKey' => $q['type']]);
            if (!$type) {
                return $this->json(['error' => 'Unknown question type: ' . $q['type']], 400);
            }
            $question->setType($type);

            // Optionen zuordnen (immer für Auswahlfragen, egal ob single_choice oder multiple_choice)
            if (in_array($q['type'], ['single_choice', 'multiple_choice'], true) && isset($q['options']) && is_array($q['options']) && count($q['options']) > 0) {
                foreach ($q['options'] as $optionId) {
                    $option = $this->em->getRepository(SurveyOption::class)->find($optionId);
                    if ($option) {
                        $question->addOption($option);
                    }
                }
            }

            $survey->getQuestions()->add($question);
        }

        $this->em->persist($survey);
        $this->em->flush();

        return $this->json(['id' => $survey->getId()], 201);
    }

    #[Route('/{id}/submit', name: 'answer', methods: ['POST'])]
    public function answer(Survey $survey, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $surveyResponse = $this->em->getRepository(SurveyResponse::class)->findOneBy(['survey' => $survey, 'userId' => $user->getId()]);

        if ($surveyResponse) {
            return $this->json(['error' => 'You have already submitted this survey.'], 400);
        }

        $surveyResponse = new SurveyResponse();
        $surveyResponse->setAnswers($data['answers']);
        $surveyResponse->setSurvey($survey);
        $surveyResponse->setCreatedAt(new DateTimeImmutable());
        $surveyResponse->setUserId($user->getId());

        $this->em->persist($surveyResponse);
        $this->em->flush();

        return $this->json(['id' => $survey->getId()], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Survey $survey, SurveyOptionTypeRepository $typeRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['title'], $data['questions'])) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        $survey->setTitle($data['title']);
        $survey->setDescription($data['description'] ?? null);
        $survey->setDueDate(!empty($data['dueDate']) ? new DateTime($data['dueDate']) : null);
        $survey->getTeams()->clear();
        if (!empty($data['teamIds']) && is_array($data['teamIds'])) {
            foreach ($data['teamIds'] as $teamId) {
                $team = $this->em->getRepository(Team::class)->find($teamId);
                if ($team) {
                    $survey->addTeam($team);
                }
            }
        }
        $survey->getClubs()->clear();
        if (!empty($data['clubIds']) && is_array($data['clubIds'])) {
            foreach ($data['clubIds'] as $clubId) {
                $club = $this->em->getRepository(Club::class)->find($clubId);
                if ($club) {
                    $survey->addClub($club);
                }
            }
        }
        $survey->setPlatform((bool) ($data['platform'] ?? false));

        foreach ($survey->getQuestions() as $q) {
            $this->em->remove($q);
        }
        $survey->getQuestions()->clear();
        foreach ($data['questions'] as $q) {
            if (!isset($q['questionText'], $q['type'])) {
                continue;
            }
            $question = new SurveyQuestion();
            $question->setSurvey($survey);
            $question->setQuestionText($q['questionText']);
            $type = $typeRepo->findOneBy(['typeKey' => $q['type']]);
            if (!$type) {
                return $this->json(['error' => 'Unknown question type: ' . $q['type']], 400);
            }
            $question->setType($type);
            if (in_array($q['type'], ['single_choice', 'multiple_choice'], true) && isset($q['options']) && is_array($q['options']) && count($q['options']) > 0) {
                foreach ($q['options'] as $optionId) {
                    $option = $this->em->getRepository(SurveyOption::class)->find($optionId);
                    if ($option) {
                        $question->addOption($option);
                    }
                }
            }
            $survey->getQuestions()->add($question);
        }

        $this->em->persist($survey);
        $this->em->flush();

        return $this->json(['id' => $survey->getId()]);
    }

    #[Route('/{id}/results', name: 'results', methods: ['GET'])]
    public function results(Survey $survey): JsonResponse
    {
        $results = [];
        $questions = $this->prepareQuestions($survey);
        $answers = $this->collectAnswers($questions, $survey->getId());

        foreach ($questions as $question) {
            $qData = [
                'id' => $question->getId(),
                'questionText' => $question->getQuestionText(),
                'type' => $question->getType()?->getTypeKey(),
                'options' => [],
                'answers' => $answers[$question->getId()] ?? [],
            ];
            if (in_array($qData['type'], ['single_choice', 'multiple_choice', 'scale_1_5', 'scale_1_10'])) {
                foreach ($question->getOptions() as $option) {
                    $qData['options'][] = [
                        'id' => $option->getId(),
                        'optionText' => $option->getOptionText(),
                        'count' => $answers[$question->getId()][$option->getId()] ?? 0,
                    ];
                }
            }
            // Für Freitext: Leeres Array (oder später: Liste der Antworten)
            $results[] = $qData;
        }

        return $this->json([
            'answers_total' => $answers['answers_total'],
            'answers' => $answers,
            'surveyId' => $survey->getId(),
            'title' => $survey->getTitle(),
            'results' => $results,
        ]);
    }

    /**
     * @return array<int, SurveyQuestion>
     */
    private function prepareQuestions(Survey $survey): array
    {
        $sortedQuestions = [];
        foreach ($survey->getQuestions() as $question) {
            $sortedQuestions[$question->getId()] = $question;
        }

        return $sortedQuestions;
    }

    /**
     * @param array<int, SurveyQuestion> $sortedQuestions
     *
     * @return array<mixed>
     */
    private function collectAnswers(array $sortedQuestions, int $surveyId)
    {
        $answers = [];
        $rawAnswers = $this->em->getRepository(SurveyResponse::class)->findBy(['survey' => $surveyId]);
        foreach ($rawAnswers as $response) {
            $answer = $response->getAnswers();
            foreach ($answer as $questionId => $ans) {
                $questionTypeKey = $sortedQuestions[$questionId]->getType()?->getTypeKey();
                if (in_array($questionTypeKey, ['scale_1_5', 'scale_1_10'])) {
                    if (!isset($answers[$questionId])) {
                        $answers[$questionId] = 0;
                    }
                    $answers[$questionId] += $ans;
                    continue;
                }

                if (!isset($answers[$questionId])) {
                    $answers[$questionId] = [];
                }
                if (is_array($ans)) {
                    foreach ($ans as $a) {
                        $answers[$questionId][$a] = isset($answers[$questionId][$a]) ? $answers[$questionId][$a] + 1 : 1;
                    }
                    continue;
                }
                if (is_numeric($ans)) {
                    $answers[$questionId][$ans] = isset($answers[$questionId][$ans]) ? $answers[$questionId][$ans] + 1 : 1;
                    continue;
                }

                $answers[$questionId][] = $ans;
            }
        }
        $answers['answers_total'] = count($rawAnswers);

        return $answers;
    }
}
