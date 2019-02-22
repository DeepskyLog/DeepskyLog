<nav class="navbar navbar-expand-lg navbar-light bg-light border-top border-bottom">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader2" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarHeader2">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <span class="navbar-text">
                    <select class="form-control" id="activateLocation" onchange="location=this.options[this.selectedIndex].value;">
                        <optgroup label="Belgium">
                            <option value="/location/activate/Roosbeek">Roosbeek</option>
                            <option value="/location/activate/Bambrugge">Bambrugge</option>
                        </optgroup>
                        <optgroup label="France">
                            <option value="/location/activate/Le Castellard-Mélan">Le Castellard-Mélan</option>
                        </optgroup>
                    </select>
                </span>
            </li>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <li class="nav-item">
                <span class="navbar-text">
                    <select class="form-control" id="activateInstrument" onchange="instrument=this.options[this.selectedIndex].value;">
                        <optgroup label="Newtonian">
                            <option value="/instrument/activate/Obsession 18\'\'">Obsession 18''</option>
                            <option value="/instrument/activate/Zelfbouw 35cm">Zelfbouw 35cm</option>
                        </optgroup>
                        <optgroup label="Finderscopes">
                            <option value="/instrument/activate/9x50 zoeker">9x50 zoeker</option>
                        </optgroup>
                    </select>
                </span>
            </li>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <li class="nav-item">
                <span class="navbar-text">
                    {{ _i('List') }}
                </span>&nbsp;&nbsp;&nbsp;&nbsp;

                <select class="form-control" id="activateList" onchange="list=this.options[this.selectedIndex].value;">
                    <option value="/observingList/activate/none">No list</option>
                    <optgroup label="Personal observing lists">
                        <option selected value="/observingList/activate/list test">list test</option>
                        <option value="/observingList/activate/list orion">list orion</option>
                    </optgroup>
                    <optgroup label="Public observing lists">
                        <option value="/observingList/activate/list pegasus">list pegasus</option>
                    </optgroup>
                </select>
                <span class="navbar-text">
                    - <a href="/observingList/manage">{{ _i('Manage list') }}</a>
                </span>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <span class="navbar-text">
                {{ _i('Date') }}
            </span>
            &nbsp;&nbsp;
            <script>
                $( function() {
                    $( "#datepicker" ).datepicker({
                        showOtherMonths: true,
                        selectOtherMonths: true,
                        showButtonPanel: true,
                        dateFormat: "dd/mm/yy",
			            defaultDate: -7
                    });
                } );
            </script>

            <input class="form-control" type="text" value="" id="datepicker" size="10" >
            
        </ul>
    </div>
</nav>