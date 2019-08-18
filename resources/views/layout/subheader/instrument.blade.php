<li class="nav-item">
    <span class="navbar-text">
        <select class="form-control selection" id="activateInstrument" onchange="instrument=this.options[this.selectedIndex].value;">
            {!! App\Instrument::getInstrumentOptions() !!}
        </select>
    </span>
</li>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
