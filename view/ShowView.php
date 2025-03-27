<?php
include_once dirname(__DIR__) . "/model/Drop.php";
include_once dirname(__DIR__) . "/model/Drops.php";
include_once dirname(__DIR__) . "/controller/ShowController.php";

ShowController::doGet();

?>
<!DOCTYPE html>
<html>
<head>
<?php
include_once dirname(__DIR__) . "/includes/common.head.php";
?>
    <title>Show</title>
</head>
<body>
<div class="p-3 mb-2 bg-info"></div>
<div class="container">
<?php
    if ( empty(ShowController::$branch) || empty(ShowController::$branch->drops) )
    {
       $msg = include dirname(__DIR__) . '/includes/on.error.page.php';
       echo str_replace (
            ['{{param.info}}',                                                  '{{param.error}}',                                 '{{param.proposal}}'],
            ["the book <span class='text-danger'>section</span> delivery page", "Something went wrong! Session probably expired.", "Start over from the main search page!"],
            $msg
        );
    }
    else
    {
        foreach (ShowController::$branch->drops as $d)
        {
            echo $d->getText() . PHP_EOL;
        }
    }
?>
</div>
<div class="p-3 mt-5 bg-info"></div>

<?php
include_once dirname(__DIR__) . "/includes/common.shoes.php";
?>

</body>
</html>
