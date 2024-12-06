<?php
// loading.php
session_start();

// Store the form data in session
$_SESSION['pending_story'] = [
    'idea' => $_POST['idea'],
    'style' => $_POST['style']
];
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gradient-to-b from-stone-900 to-stone-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weaving Your Tale - Tale Weaver</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes fade {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 1; }
        }
        .fade-pulse {
            animation: fade 2s ease-in-out infinite;
        }
    </style>
    <meta http-equiv="refresh" content="0;url=generate.php">
</head>
<body class="h-full text-stone-200">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="text-center space-y-8">
            <svg class="spinner h-16 w-16 text-amber-500 mx-auto" viewBox="0 0 24 24" fill="none">
                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                      d="M12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12"/>
            </svg>
            <div>
                <h2 class="text-2xl font-medieval mb-4">Weaving Your Tale</h2>
                <div class="space-y-2 text-stone-400">
                    <p class="fade-pulse" id="status1">Crafting your narrative...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(element, delay) {
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.animation = 'fade 2s ease-in-out infinite';
            }, delay);
        }

        updateStatus(document.getElementById('status1'), 0);
    </script>
</body>
</html>