<?php
function getFormattedDate()
{
    return date("d.m.Y H:i:s");
}

$s = '<div class="p-3 mb-2"></div>';
$s .= '<h1>Welcome to</h1>';
$s .= '<p>{{param.info}}</p>';
$s .= '<div class="p-3 mb-2 bg-info text-white">{{param.error}}</div>';
$s .= '<p><i>{{param.proposal}}</i></p>';
$s .= '<i>Today is ' . getFormattedDate() . '</i>';
$s .= '<div class="p-3 mt-5"></div>';

return $s;
?>
