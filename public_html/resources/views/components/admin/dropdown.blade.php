@if (count(array_filter($menu, fn($m) => $m['permit'] ?? false)) > 0)
    <div class="dropdown">
        <a href="#" class="h2 btn-action text-decoration-none {{ $buttonClass ?? '' }}" data-bs-toggle="dropdown"
            aria-expanded="false" onclick="event.stopPropagation();">
            <i class="ti ti-dots-vertical text-white"></i>
        </a>
        @isset($menu)
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" onclick="event.stopPropagation();">
                @foreach ($menu as $item)
                    @if ($item['permit'] ?? false)
                        <a href="{{ $item['url'] ?? 'javascript:;' }}" class="dropdown-item {{ $item['class'] ?? '' }}"
                            @isset($item['onclick']) onclick="{{ $item['onclick'] }}" @endisset>
                            @isset($item['icon'])
                                <i class="{{ $item['icon'] }} me-1"></i>
                            @endisset
                            {{ $item['title'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        @endisset
    </div>
@endif