<?php
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$url = substr($request_uri[0], 1); // Usuń początkowy '/'

switch ($url) {
    case 'index':
        header("location: main-page.php");
        break;
    case 'main':
        header("location: main-page.php");
        break;
    default:
        header("location: main-page.php");
        break;
}
