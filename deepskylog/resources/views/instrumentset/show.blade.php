<x-app-layout>
    <div class="prose">
        <h1>{{ $set->name }}</h1>
        <p>{!! nl2br(e($set->description)) !!}</p>
    </div>
</x-app-layout>
