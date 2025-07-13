<?php

namespace App\Services;

class GeminiService
{
    private $config;
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../frontend/config/gemini.php';
        $this->apiKey = $this->config['api_key'];
        $this->baseUrl = $this->config['base_url'];
    }

    /**
     * Génère un résumé d'un article
     * 
     * @param string $content Le contenu de l'article
     * @return string Le résumé généré
     */
    public function generateSummary(string $content): string
    {
        $prompt = "En tant qu'assistant de rédaction, résume le texte suivant en un paragraphe concis et facile à comprendre. Voici le texte : \n\n" . $content;
        
        return $this->callApi($prompt);
    }

    /**
     * Génère des questions liées à un article
     * 
     * @param string $content Le contenu de l'article
     * @return array Les questions générées
     */
    public function generateRelatedQuestions(string $content): array
    {
        $prompt = "En tant qu'assistant de rédaction, génère 3 questions pertinentes et intéressantes qu'un lecteur pourrait se poser après avoir lu cet article. Ne fournis que les questions, sous forme de liste à puces (utilisant -). Voici l'article : \n\n" . $content;
        
        $response = $this->callApi($prompt);
        
        // Parse les questions depuis la réponse
        $questions = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '- ') === 0) {
                $questions[] = substr($line, 2);
            } elseif (strpos($line, '• ') === 0) {
                $questions[] = substr($line, 2);
            }
        }
        
        return array_slice($questions, 0, 3); // Retourne max 3 questions
    }

    /**
     * Effectue un appel à l'API Gemini
     * 
     * @param string $prompt Le prompt à envoyer
     * @return string La réponse générée
     */
    private function callApi(string $prompt): string
    {
        if (empty($this->apiKey)) {
            return "Configuration de l'API Gemini manquante. Veuillez configurer votre clé API.";
        }

        $url = $this->baseUrl . $this->config['model'] . ':generateContent?key=' . $this->apiKey;
        
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => $this->config['max_tokens'],
                'temperature' => $this->config['temperature']
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return "Erreur lors de l'appel à l'API Gemini (HTTP $httpCode)";
        }

        $result = json_decode($response, true);

        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }

        return "Erreur lors du traitement de la réponse de l'API";
    }

    /**
     * Vérifie si l'API est configurée
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
} 