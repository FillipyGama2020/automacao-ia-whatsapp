@props(['name'])

@php
$paths = [
    'edit' => '<path d="M17 3a2.85 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" />',
    'check' => '<circle cx="12" cy="12" r="9" /><polyline points="8 12.5 10.5 15 16 9" />',
    'pause' => '<circle cx="12" cy="12" r="9" /><line x1="10" y1="9" x2="10" y2="15" /><line x1="14" y1="9" x2="14" y2="15" />',
    'archive' => '<rect x="3" y="4" width="18" height="4" rx="1" /><path d="M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" /><line x1="10" y1="12" x2="14" y2="12" />',
    'trash' => '<polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" />',
    'message' => '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />',
    'back' => '<line x1="19" y1="12" x2="5" y2="12" /><polyline points="12 19 5 12 12 5" />',
    'cpu' => '<rect x="4" y="4" width="16" height="16" rx="2" /><rect x="9" y="9" width="6" height="6" /><line x1="9" y1="1" x2="9" y2="4" /><line x1="15" y1="1" x2="15" y2="4" /><line x1="9" y1="20" x2="9" y2="23" /><line x1="15" y1="20" x2="15" y2="23" /><line x1="20" y1="9" x2="23" y2="9" /><line x1="20" y1="14" x2="23" y2="14" /><line x1="1" y1="9" x2="4" y2="9" /><line x1="1" y1="14" x2="4" y2="14" />',
    'chat' => '<path d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v3l-4-3H9a2 2 0 01-1.6-.8" /><path d="M15 12V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h1v3l4-3h3a2 2 0 002-2z" />',
    'eye' => '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" /><circle cx="12" cy="12" r="3" />',
    'target' => '<circle cx="12" cy="12" r="9" /><circle cx="12" cy="12" r="5" /><circle cx="12" cy="12" r="1" />',
    'cash' => '<rect x="2" y="6" width="20" height="12" rx="2" /><circle cx="12" cy="12" r="3" /><line x1="6" y1="12" x2="6" y2="12" /><line x1="18" y1="12" x2="18" y2="12" />',
    'report' => '<path d="M14 3v4a1 1 0 001 1h4" /><path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" /><line x1="9" y1="13" x2="15" y2="13" /><line x1="9" y1="17" x2="13" y2="17" />',
    'chevron-down' => '<polyline points="6 9 12 15 18 9" />',
    'headset' => '<path d="M3 18v-6a9 9 0 0 1 18 0v6" /><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />',
    'send' => '<line x1="22" y1="2" x2="11" y2="13" /><polygon points="22 2 15 22 11 13 2 9 22 2" />',
];
@endphp

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => 'w-4 h-4']) }}>
    {!! $paths[$name] ?? '' !!}
</svg>
