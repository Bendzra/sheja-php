<?php
function getFormattedDate()
{
    return date("d.m.Y H:i:s");
}

$s = '<div class="p-3 mb-2"></div>'. PHP_EOL;
$s .= '<h1>Welcome to</h1>'. PHP_EOL;
$s .= '<p>{{param.info}}</p>'. PHP_EOL;
$s .= '<div class="p-3 mb-2 bg-info text-white">{{param.error}}</div>'. PHP_EOL;
$s .= '<p><i>{{param.proposal}}</i></p>'. PHP_EOL;
$s .= '<p><i>Today is ' . getFormattedDate() . '</i></p>'. PHP_EOL;
$s .= '<div class="p-3 mt-5"></div>'. PHP_EOL;

return $s;
?>
