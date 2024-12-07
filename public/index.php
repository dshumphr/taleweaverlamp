<?php
// index.php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Clients\LLMClient;
use App\Clients\FALClient;
use App\Utils\PDFGenerator;

session_start();

$error = null;
$story = null;

error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('POST data: ' . print_r($_POST, true));
error_log("ANTHROPIC_API_KEY set: " . (!empty(getenv('ANTHROPIC_API_KEY')) ? 'yes' : 'no'));
error_log("FAL_KEY set: " . (!empty(getenv('FAL_KEY')) ? 'yes' : 'no'));

// Get story from session if available
if (isset($_GET['result'])) {
    $story = $_SESSION['story'] ?? null;
    if (!$story) {
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gradient-to-b from-stone-900 to-stone-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juni Jabber</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gothic border pattern */
        .gothic-border {
            background-image: 
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='16' viewBox='0 0 60 16'%3E%3Cpath d='M30 0L15 8L0 16L30 8L60 16L45 8L30 0Z' fill='currentColor'/%3E%3C/svg%3E");
            background-repeat: repeat-x;
            opacity: 0.1;
        }
        
        .hover-amber:hover {
            border-color: rgb(245 158 11 / 0.5);
            background-color: rgb(245 158 11 / 0.1);
            transition: all 0.2s;
        }
    </style>
</head>
<body class="h-full text-stone-200">
    <nav class="border-b border-stone-700 bg-stone-900/50 backdrop-blur-sm">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="ml-2 text-2xl">Juni Jabber</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
        <?php if ($error): ?>
            <div class="rounded-md bg-red-500/10 border border-red-500 p-4 mb-6">
                <p class="text-red-500"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!isset($_GET['result'])): ?>
            <div class="relative p-8">
                <div class="gothic-border absolute inset-0 pointer-events-none"></div>
                <form method="POST" action="loading.php" class="max-w-4xl mx-auto">
                    <div class="text-center mb-12">
                        <h1 class="text-4xl mb-4">Begin Your Tale</h1>
                        <p class="text-stone-400">Share your story idea and let's create</p>
                    </div>

                    <div class="space-y-8">
                        <div>
                            <label class="block text-lg text-stone-400">Your Tale Begins With...</label>
                            <textarea 
                                name="idea" 
                                required
                                class="mt-2 block w-full rounded-lg bg-stone-800/50 border border-stone-700 text-stone-200 placeholder-stone-500 focus:border-amber-500 focus:ring-amber-500"
                                rows="4"
                                placeholder="Share the seed of your story..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-lg text-stone-400">Illustrated in the style of...</label>
                            <textarea 
                                name="style" 
                                required
                                class="mt-2 block w-full rounded-lg bg-stone-800/50 border border-stone-700 text-stone-200 placeholder-stone-500 focus:border-amber-500 focus:ring-amber-500"
                                rows="2"
                                placeholder="Describe the illustration style you desire..."
                            ></textarea>
                        </div>

                        <div class="mt-12 flex justify-center">
                            <button
                                type="submit"
                                name="generate"
                                value="create"
                                class="w-full sm:w-auto relative p-4 rounded-lg bg-stone-800/50 border border-stone-700 hover-amber"
                            >
                                <svg class="h-6 w-6 text-amber-500 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                <p class="text-center text-lg">Create</p>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        <?php elseif ($story): ?>
            <div class="relative p-8">
                <div class="gothic-border absolute inset-0 pointer-events-none"></div>
                <div class="space-y-8">
                    <div class="prose prose-invert max-w-none">
                        <h2 class="text-2xl text-amber-500 text-center"><?= htmlspecialchars($story['title']) ?></h2>
                        <?php foreach ($story['pages'] as $index => $page): ?>
                            <div class="mb-8">
                                <p class="text-stone-300 text-center"><?= nl2br(htmlspecialchars($page['text'])) ?></p>
                                <?php if (isset($story['illustrations'][$index])): ?>
                                    <div class="mt-4 flex justify-center">
                                        <img src="<?= htmlspecialchars($story['illustrations'][$index]) ?>" alt="Story illustration" class="rounded-lg max-w-full h-auto">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex justify-between">
                        <a 
                            href="index.php" 
                            class="px-4 py-2 text-stone-400 hover:text-stone-200"
                        >
                            Create Another Story
                        </a>
                        <a 
                            href="download.php" 
                            class="flex items-center px-6 py-2 bg-amber-500 text-stone-900 rounded-lg hover:bg-amber-400"
                        >
                            <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download PDF
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>