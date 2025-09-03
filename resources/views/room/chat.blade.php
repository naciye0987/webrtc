<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Sohbet - Oda {{ $roomId }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="mb-4">
                <h1 class="text-2xl font-bold">Oda: {{ $roomId }}</h1>
                <p class="text-gray-600">Kullanıcı: {{ $userName }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="relative">
                    <h2 class="text-lg font-semibold mb-2">Sizin Görüntünüz</h2>
                    <video id="localVideo" autoplay muted playsinline class="w-full bg-black rounded-lg"></video>
                </div>
                <div class="relative">
                    <h2 class="text-lg font-semibold mb-2">Karşı Taraf</h2>
                    <video id="remoteVideo" autoplay playsinline class="w-full bg-black rounded-lg"></video>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ mix('js/app.js') }}"></script>
    <script>
        const userName = @json($userName);
        const roomId = @json($roomId);
    </script>
    <script src="{{ asset('js/chat.js') }}"></script>
</body>
</html> 