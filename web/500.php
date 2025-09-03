<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="w-32 h-32 bg-gradient-to-r from-red-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-8">
            <i class="fas fa-exclamation-triangle text-white text-5xl"></i>
        </div>
        
        <h1 class="text-6xl font-bold text-gray-900 mb-4">500</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Server Error</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Something went wrong on our end. Our team has been notified and is working to fix the issue. 
            Please try again in a few moments.
        </p>
        
        <div class="space-x-4">
            <a href="index.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors duration-200">
                <i class="fas fa-home mr-2"></i>Go Home
            </a>
            <button onclick="location.reload()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-redo mr-2"></i>Try Again
            </button>
        </div>
        
        <div class="mt-12 text-sm text-gray-500">
            <p>If the problem persists, please contact support.</p>
        </div>
    </div>
</body>
</html>
