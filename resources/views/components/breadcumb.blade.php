@if (isset($bcms))
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            @foreach ($bcms ?? [] as $item)
                <li class="breadcrumb-item {{ $item->active ? '' : 'active' }}">
                    @if ($item->active)
                        <a href="{{ $item->url }}">{{ $item->name }}</a>
                    @else
                        {{ $item->name }}
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

@endif
