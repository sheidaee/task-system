<?php
require 'routine.php';
stlGlobalSetVar('mainMenuItem', 1);
stlGlobalSetVar('records_per_page', 20);

include _TEM_HEADER_PATH;
?>
    <div class="container">
        <div class="row">
            <?php include _TEM_SIDEBAR_PATH;?>
            <div class="col-md-9">
                <?php stlLoadServiceHomePage('tasks'); ?>
            </div>
        </div>
    </div>
<?php include _TEM_FOOTER_PATH; ?>