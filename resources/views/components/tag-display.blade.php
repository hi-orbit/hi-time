@php
    $tags = isset($tag) ? collect([$tag]) : ($tags ?? collect());
    $sizeClass = match($size ?? 'sm') {
        'xs' => 'px-1.5 py-0.5 text-xs',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        default => 'px-2 py-0.5 text-xs'
    };
@endphp

@if($tags && $tags->count() > 0)
    <div class="flex flex-wrap gap-1 {{ $class ?? '' }}">
        @foreach($tags as $tagItem)
            <span class="inline-flex items-center {{ $sizeClass }} rounded-full font-medium text-white"
                  style="background-color: {{ $tagItem->color }}">
                {{ $tagItem->name }}
            </span>
        @endforeach
    </div>
@endif
