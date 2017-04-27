<?php
require_once 'routine.php';
stlGlobalSetVar('mainMenuItem', 2);
include _TEM_HEADER_PATH;
?>
    <div class="container">
        <div class="row">
            <?php include _TEM_SIDEBAR_PATH;?>
            <div class="col-md-9">
                <?php stlLoadServiceEditPage('tasks'); ?>
            </div>
        </div>
    </div>
<?php include _TEM_FOOTER_PATH; ?>