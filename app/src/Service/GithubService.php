<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubService
{
    private HttpClientInterface $httpClient;
    private string $githubToken;
    private string $repoOwner;
    private string $repoName;

    public function __construct(
        HttpClientInterface $httpClient,
        string $githubToken,
        string $githubRepoOwner,
        string $githubRepoName
    ) {
        $this->httpClient = $httpClient;
        $this->githubToken = $githubToken;
        $this->repoOwner = $githubRepoOwner;
        $this->repoName = $githubRepoName;
    }

    /**
     * @param array<int, string> $labels
     *
     * @return array<string, mixed> 
     */
    public function createIssue(string $title, string $body, array $labels = []): array
    {
        $response = $this->httpClient->request('POST', "https://api.github.com/repos/{$this->repoOwner}/{$this->repoName}/issues", [
            'headers' => [
                'Authorization' => "token {$this->githubToken}",
                'Accept' => 'application/vnd.github.v3+json',
            ],
            'json' => [
                'title' => $title,
                'body' => $body,
                'labels' => $labels,
            ],
        ]);

        return $response->toArray();
    }

    /** @return array<string, mixed> */
    public function retrieveIssues(string $state = 'open'): array
    {
        $response = $this->httpClient->request('GET', "https://api.github.com/repos/{$this->repoOwner}/{$this->repoName}/issues", [
            'headers' => [
                'Authorization' => "token {$this->githubToken}",
                'Accept' => 'application/vnd.github.v3+json',
            ],
            'query' => [
                'state' => $state,
            ],
        ]);

        return $response->toArray();
    }
}
