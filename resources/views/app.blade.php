<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Customer Base Analyst</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
<div x-data="app()" class="max-w-2xl mx-auto px-6 py-16">

    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight">Store Customer Base Analyst</h1>
        <p class="mt-2 text-gray-500">Upload a CSV file of store locations to begin analysis.</p>
    </div>

    <div x-show="!importing && !analysing && !done">
        <label
            class="group flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-gray-300 bg-white px-6 py-12 text-center transition hover:border-indigo-400 hover:bg-indigo-50/50 cursor-pointer"
            :class="{ 'border-indigo-400 bg-indigo-50/50': dragging }"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="dragging = false; upload({ target: { files: $event.dataTransfer.files, value: '' } })"
        >
            <svg class="size-10 text-gray-400 transition group-hover:text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
            </svg>
            <div>
                <span class="font-medium text-indigo-600">Choose a file</span>
                <span class="text-gray-500"> or drag and drop</span>
            </div>
            <span class="text-xs text-gray-400">CSV up to 10 MB</span>
            <input type="file" accept=".csv" class="sr-only" @change="upload($event)">
        </label>
    </div>

    <div x-show="importing" x-cloak class="rounded-xl border border-gray-200 bg-white p-8">
        <div class="flex items-center gap-4">
            <svg class="size-6 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <div>
                <p class="font-semibold">Importing stores&hellip;</p>
                <p class="text-sm text-gray-500">This may take a moment depending on the file size.</p>
            </div>
        </div>
        <div x-show="progress > 0" class="mt-6">
            <div class="flex items-center justify-between text-sm mb-1.5">
                <span class="text-gray-600">Progress</span>
                <span class="font-medium" x-text="progress + '%'"></span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-indigo-600 transition-all duration-300" :style="'width:' + progress + '%'"></div>
            </div>
        </div>
    </div>

    <div x-show="analysing" x-cloak class="rounded-xl border border-gray-200 bg-white p-8">
        <div class="flex items-center gap-4">
            <svg class="size-6 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <div>
                <p class="font-semibold">Running analysis&hellip;</p>
                <p class="text-sm text-gray-500">Generating insights from your store data.</p>
            </div>
        </div>
    </div>

    <div x-show="done" x-cloak class="rounded-xl border border-gray-200 bg-white p-8 prose prose-indigo max-w-none" x-html="analysisHtml"></div>

</div>

<script>
    function app() {
        return {
            importing: false,
            analysing: false,
            done: false,
            progress: 0,
            analysisHtml: '',
            dragging: false,

            async upload(e) {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('file', file);
                e.target.value = '';
                this.importing = true;

                try {
                    await fetch('/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                } finally {
                    this.importing = false;
                }
            },
        }
    }
</script>
</body>
</html>
