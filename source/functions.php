<?php
/*
 * Load service home page
 * @ServiceName string
 */
function stlLoadServiceHomePage($serviceName) {
    stlLoadServiceScript('tasks');
    include 'services/' . $serviceName . '/index.php';
}

/*
 * Load service sidebar
 * @ServiceName string
 */
function stlLoadServiceSidebar($serviceName) {
    include 'services/' . $serviceName . '/routine.php';
    include 'services/' . $serviceName . '/sidebar.php';
}

/*
 * Load service insert page
 * @ServiceName string
 */
function stlLoadServiceInsertPage($serviceName) {
    stlLoadServiceScript('tasks');
    include 'services/' . $serviceName . '/insert.php';
}

/*
 * Load service edit page
 * @ServiceName string
 */
function stlLoadServiceEditPage($serviceName) {
    stlLoadServiceScript('tasks');
    include 'services/' . $serviceName . '/edit.php';
}


/*
 * Load service script
 * @ServiceName string
 */
function stlLoadServiceScript($serviceName) {
    d('<script src="services/' . $serviceName . '/'. $serviceName .'.js"></script>');
}


/*
 * Echo something
 * @var
 */
function d($var) {
    echo $var;
}

/*
 * Set global variable
 * @varName
 * @varValue
 */
function stlGlobalSetVar($varName, $varValue) {
    $GLOBALS[$varName] = $varValue;
}

/*
 * Get global variable
 * @varName
 */
function stlGlobal($varName) {
    return $GLOBALS[$varName];
}

/*
 * Set page tile
 */
function stlSetPageTitle($pageTitle) {
    stlGlobalSetVar('stlPageTitle', $pageTitle);
}

/*
 * Get page tile
 */
function stlGetPageTitle() {
    if (isset($GLOBALS['stlPageTitle']))
    {
        return stlGlobal('stlPageTitle');
    } else
    {
        stlGlobalSetVar('stlPageTitle', _SITE_NAME);

        return _SITE_NAME;
    }
}

/*
 * Dump variable
 * @var
 * @die boolean
 */
function stlDump($var, $die = true) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    if($die) {
        die();
    }
}

/*
 * Get service image url
 * @service name string
 * @image name string
 */
function stlThemeImage($serviceName, $imageName) {
    return _SITE_URL .'/upload/content/'. $serviceName . '/' . $imageName;
}

/*
 * Get variable from $_GET
 */
function stlParam($var) {
    return isset($_GET[$var]) ? $_GET[$var] : false;
}

/*
 * Get variable from $_POST
 */
function stlRecord($var) {
    return isset($_POST[$var]) ? $_POST[$var] : false;
}

if(stlParam('function_call')) {
    if( stlParam('function_call') != 'tskHasChildren')
        return false;
    call_user_func(stlParam('function_call'));
}