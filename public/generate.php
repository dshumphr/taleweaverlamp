<?php
// generate.php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Clients\LLMClient;
use App\Clients\FALClient;
session_start();

if (!isset($_SESSION['pending_story'])) {
    header('Location: index.php');
    exit;
}

try {

    $data = $_SESSION['pending_story'];
    unset($_SESSION['pending_story']); // Clean up
    $style = $data['style'];

    $llmClient = new LLMClient(getenv('ANTHROPIC_API_KEY'));
    $falClient = new FALClient(getenv('FAL_KEY'));
    
    // Generate story
    $storyResult = $llmClient->generate($data['idea'], false);
    
    // Generate illustrations
    $processes = [];
    $illustrations = [];
    $autoloadPath = realpath(__DIR__ . '/../vendor/autoload.php');
    $clientPath = realpath(__DIR__ . '/../src/Clients/FALClient.php');
    foreach ($storyResult['pages'] as $index => $page) {
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];
        $escapedPrompt = addslashes($page['image_prompt']);
        $escapedStyle = addslashes($style);
        
        // Create a temporary PHP script for this generation
        $falKey = getenv('FAL_KEY');
        $tmpScript = tempnam(sys_get_temp_dir(), 'img_gen_');
        file_put_contents($tmpScript, <<<PHP
        <?php
        require_once '{$autoloadPath}';
        \$client = new \App\Clients\FALClient('{$falKey}');
        echo \$client->generateImage('{$escapedPrompt}', '{$escapedStyle}');
        PHP);
        
        $processes[$index] = [
            'process' => proc_open("php $tmpScript", $descriptorspec, $pipes),
            'pipes' => $pipes,
            'script' => $tmpScript
        ];
    }
    foreach ($processes as $index => $proc) {
        $illustrations[$index] = stream_get_contents($proc['pipes'][1]);
        fclose($proc['pipes'][1]);
        proc_close($proc['process']);
        unlink($proc['script']);
    }
    
    // Store result
    $_SESSION['story'] = [
        'title' => $storyResult['title'],
        'pages' => $storyResult['pages'],
        'illustrations' => $illustrations
    ];
    
    header('Location: index.php?result');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}