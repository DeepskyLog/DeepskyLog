<li>
    <form role="form" action="" method="get">
        <h4>
            <?php echo e(_i('Quick Search')); ?>

        </h4>
        <input type="hidden" name="indexAction" value="quickpick" />
        <input type="hidden" name="titleobjectaction" value="search" />
        <input type="hidden" name="source"      value="quickpick" />
        <input type="hidden" name="myLanguages" value="true" />
        <select class="form-control selection" id="quickpickobject">
            <optgroup label="<?php echo e(_i("Deepsky")); ?>">
                <option value="M 1">M 1</option>
                <option value="M 2">M 2</option>
                <option value="M 3">M 3</option>
                <option value="M 4">M 4</option>
                <option value="M 5">M 5</option>
                <option value="M 6">M 6</option>
            </optgroup>
            <optgroup label="<?php echo e(_i("Comets")); ?>">
                <option value="Halley">Halley</option>
            </optgroup>
            <optgroup label="<?php echo e(_i("Moon")); ?>">
                <option value="Copernicus">Copernicus</option>
            </optgroup>
            <optgroup label="<?php echo e(_i("Planets")); ?>">
                <option value="Mercury">Mercury</option>
                <option value="Venus">Venus</option>
                <option value="Mars">Mars</option>
                <option value="Jupiter">Jupiter</option>
                <option value="Saturn">Saturn</option>
                <option value="Uranus">Uranus</option>
                <option value="Neptune">Neptune</option>
            </optgroup>
        </select>

        <br /><br />
        <div class="form group">
            <input class="btn btn-outline-secondary btn-block btn-sm" type="submit" name="searchObjectQuickPickQuickPick" value=" <?php echo e(_i("Search Object")); ?>" />
        </div>
        <div class="form group">
            <input class="btn btn-outline-secondary btn-block btn-sm" type="submit" name="searchObservationsQuickPick" value=" <?php echo e(_i("Search Observations")); ?>" />
        </div>

        <div class="form group">
            <input class="btn btn-outline-secondary btn-block btn-sm" type="submit" name="newObservationQuickPick" value=" <?php echo e(_i("New Observation")); ?>" />
        </div>
    </form>
</li>
