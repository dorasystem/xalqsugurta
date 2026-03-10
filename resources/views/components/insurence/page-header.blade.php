@props(['icon' => 'bi-shield-check', 'title', 'subtitle' => ''])

<div class="flex items-center gap-3 mb-5">
    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
        <i class="bi {{ $icon }} text-white"></i>
    </div>
    <div>
        <h1 class="text-xl font-bold text-gray-900 mb-0">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-gray-500 mb-0">{{ $subtitle }}</p>
        @endif
    </div>
</div>
