<?php
namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FALClient {
    private $client;
    private $apiKey;
    private $baseUrl = 'https://fal.run/fal-ai/fast-sdxl';

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->client = new Client();
    }

    public function generateImage($sceneDescription, $style) {
        try {
            $prompt = $this->buildImagePrompt($sceneDescription, $style);
            
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Key {$this->apiKey}"
                ],
                'json' => [
                    'prompt' => $prompt,
                    'negative_prompt' => 'blurry, distorted, low quality, oversaturated, ugly, text, watermark',
                    'sync_mode' => true,
                    'image_size' => 'landscape_16_9'
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            
            if (!isset($result['images'][0]['url'])) {
                throw new \Exception('Invalid response from image generation API');
            }

            // Download the generated image
            return $result['images'][0]['url'];

        } catch (GuzzleException $e) {
            //error_log("FAL.ai API error: " . $e->getMessage());
            //throw new \Exception('Error generating image: ' . $e->getMessage());
        }
    }

    private function buildImagePrompt($scene, $style) {
        return trim($scene) . ", $style, high quality";
    }
}