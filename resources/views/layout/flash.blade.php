@if(flash()->message)
    <div class="container">
        <br />
        <div class="{{ flash()->class }}">
            {{ flash()->message }}
        </div>
    </div>
@endif
