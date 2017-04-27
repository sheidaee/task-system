<?php
if ( !isset($_SESSION) ) {
    session_start();
    if (!isset($_SESSION['initiated']))
    {
        $_SESSION['initiated'] = true;
        session_regenerate_id(true);
    }
}
/* ---------- Site Settings ---------- */
define('_DB_HOST', 'localhost');
define('_DB_USER', 'root');
define('_DB_PASS', '');
define('_DB_NAME', 'streamline_studios_task_system');
define('_SITE_URL', 'http://localhost/task');
define('_SITE_NAME', 'Task System');
define('_IMG_URL', _SITE_URL . '/theme/images/');
define('_TEM_HEADER_PATH', 'theme/header.php');
define('_TEM_SIDEBAR_PATH', 'theme/sidebar.php');
define('_TEM_FOOTER_PATH', 'theme/footer.php');

global $database;
$database = new Database();
