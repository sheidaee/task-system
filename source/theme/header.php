<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php d(stlGetPageTitle()); ?></title>
    <meta http-equiv="Expires" content="0" />
	<meta name="Revisit-After" content="1 days" />

    <!-- Bootstrap -->
    <link href="theme/css/bootstrap.min.css" rel="stylesheet">
    <link href="theme/css/style.css" rel="stylesheet">
    <link href="theme/css/task.css" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="theme/javascript/html5shiv.min.js"></script>
    <script src="theme/javascript/respond.min.js"></script>
    <![endif]-->
    <script>
        var _SITE_URL = '<?php d(_SITE_URL); ?>';
    </script>
</head>
<body>
<div class="bodyContainer">
    <header class="header">
        <div class="container">
            <div class="logoWarp">
                <div class="logo">
                    <a href="<?php d(_SITE_URL) ?>">
                        <img src="theme/images/logo.png" alt="Streamline Studios">
                    </a>
                </div>
            </div>
        </div>
    </header>
    <nav class="navbar navbar-default mainMenu">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-nav"
                        aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="bs-nav">
                <ul class="nav navbar-nav">
                    <li <?php stlGlobal('mainMenuItem') == 1 ? d('class="active"') : '' ?>><a href="index.php">Task system</a></li>
                    <li <?php stlGlobal('mainMenuItem') == 2 ? d('class="active"') : '' ?>><a href="insert.php">Insert task</a></li>
                </ul>
            </div>
        </div>
    </nav>