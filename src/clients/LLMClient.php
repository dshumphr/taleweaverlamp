<?php
namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class LLMClient {
    private $client;
    private $apiKey;
    private $baseUrl = 'https://api.anthropic.com/v1/messages';
    private $model = 'claude-3-5-sonnet-20240620';
    private $sysPrompt = "You are tasked with writing a children's book about for very young children. The book should be engaging, educational, and visually appealing.

    Age Group
    - The book is aimed at children less than 1 year old.
    
    Style
    - Keep the language simple, using short words.
    - Use engaging image descriptions to capture the attention of young children.
    
    Structure
    - The book should be exactly 10 pages long.
    - Each page should contain one sentence with simple rhyming or metering.
    
    Image Prompts:
    - For each page, create a brief image prompt that can be used with a text-to-image model like Stable Diffusion.
    - The prompt should describe the fish and its environment in simple, clear terms.
    - Include colors, basic shapes, and any key elements mentioned in the text.
    
    6. Output Format:
    Present your book in the following format:
    
    <book>
    <title>[Title]</title>
    <page>
    <text>[Insert rhyming sentence here]</text>
    <image_prompt>[Insert image prompt here]</image_prompt>
    </page>
    
    <page>
    <text>[Insert rhyming sentence here]</text>
    <image_prompt>[Insert image prompt here]</image_prompt>
    </page>
    
    [Continue this pattern for all 10 pages]
    </book>
    
    Remember to maintain a consistent rhyme scheme or rhythm, keep the content appropriate for young children, and ensure each page has both a sentence and an image prompt. Your goal is to create a delightful, simple book that introduces young children to reading and the world around them.";

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->client = new Client();
    }

    public function generate($storyIdea, $detailed = false) {
        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01'
                ],
                'json' => [
                    'model' => $this->model,
                    'system' => $this->sysPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $storyIdea,
                        ]
                    ],
                    'max_tokens' => 4096
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            return $this->parseResponse($result['content'][0]['text']);

        } catch (GuzzleException $e) {
            throw new \Exception('Error generating story: ' . $e->getMessage());
        }
    }

    private function parseResponse($text) {
        $book = [];
        preg_match('/<book>(.*?)<\/book>/s', $text, $matches);
        
        if (isset($matches[1])) {
            preg_match('/<title>(.*?)<\/title>/s', $matches[1], $titleMatch);
            $book['title'] = trim($titleMatch[1] ?? '');

            preg_match_all('/<page>(.*?)<\/page>/s', $matches[1], $pages);
            
            foreach ($pages[1] as $page) {
                preg_match('/<text>(.*?)<\/text>/s', $page, $textMatch);
                preg_match('/<image_prompt>(.*?)<\/image_prompt>/s', $page, $promptMatch);
                
                $book['pages'][] = [
                    'text' => trim($textMatch[1] ?? ''),
                    'image_prompt' => trim($promptMatch[1] ?? '')
                ];
            }
        }
        
        return $book;
    }
}