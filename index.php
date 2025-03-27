<?php
session_start();

$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

$page = !empty($_GET['p']) ? $_GET['p'] : 'sheja';
$page = ucfirst(strtolower($page)) . 'View.php';

$dir = __DIR__ . '/view/';
if (!file_exists($dir . $page))
{
    $page = 'ShejaView.php';
}

if($page === "ShejaView.php" || $page === "DeliveryView.php" || $page === "ShowView.php" || $page === "OutlineView.php")
{
    include __DIR__ . "/.stats/Stats.php";
    include __DIR__ . "/controller/ShejaController.php";
    ShejaController::shelveNIO();
}

include ($dir . $page);



?>