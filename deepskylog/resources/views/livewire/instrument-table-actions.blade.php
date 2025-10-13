{{-- Fallback actions view for InstrumentTable to avoid ViewExceptions.
     Delegates to the existing actions.instrument view if present. --}}

@if (view()->exists('actions.instrument'))
    @include('actions.instrument', get_defined_vars())
@else
    {{-- Minimal fallback markup if actions.instrument is missing --}}
    <div class="pg-actions-fallback">
        <button class="btn btn-sm">Action</button>
    </div>
@endif
