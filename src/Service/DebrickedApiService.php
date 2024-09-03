<?php

namespace App\Service;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DebrickedApiService
{
    private HttpClientInterface $httpClient;
    private string $jwtToken;
    private string $username;
    private string $password;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->username = $params->get('env(DEBRICKED_USERNAME)');
        $this->password = $params->get('env(DEBRICKED_PASSWORD)');
    }

    public function authenticate(): void
    {
        $response = $this->httpClient->request('POST', 'https://debricked.com/api/login_check', [
            'json' => [
                'username' => $this->username,
                'password' => $this->password,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Authentication with Debricked API failed.');
        }

        $data = $response->toArray();
        $this->jwtToken = $data['token'];
    }

    // Rest of the methods remain the same
}
