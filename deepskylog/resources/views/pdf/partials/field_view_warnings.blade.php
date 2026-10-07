@if ($result['warnings'])
    <div class="warnings">
        @foreach ($result['warnings'] as $warning)
            <div>{{ $warning }}</div>
        @endforeach
    </div>
@endif
