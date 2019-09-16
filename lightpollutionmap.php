<?php
        print file_get_contents(
            "https://www.lightpollutionmap.info/QueryRaster/" .
             "?ql=wa_2015&qt=point&qd=" . $_GET['longitude']
             . "," . $_GET['latitude'] . "&key=6hDh3zLAIhFXdpaX"
        );

?>