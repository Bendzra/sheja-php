<?php
include_once dirname(__DIR__) . "/model/Drop.php";
include_once dirname(__DIR__) . "/model/Drops.php";
include_once dirname(__DIR__) . "/model/Book.php";
include_once dirname(__DIR__) . "/controller/OutlineController.php";

OutlineController::doGet();
?>
<!DOCTYPE html>
<html>
<head>
<?php
include_once dirname(__DIR__) . "/includes/common.head.php";
?>
    <title>Outline</title>
</head>
<body>

<div class="p-3 mb-2 bg-info"></div>
<div class="container">
<?php

    $dropSB = "";

    if ( empty(OutlineController::$branch) || empty(OutlineController::$branch->drops) || empty(OutlineController::$book)  || empty(OutlineController::$book->getXmlPath()) )
    {
       $msg = include dirname(__DIR__) . '/includes/on.error.page.php';
       echo str_replace (
            ['{{param.info}}',                                                  '{{param.error}}',                                 '{{param.proposal}}'],
            ["the book <span class='text-danger'>outline</span> delivery page", "Something went wrong! Session probably expired.", "Start over from the main search page!"],
            $msg
        );
    }
    else
    {
        $dropSB .= "[";

        foreach (OutlineController::$branch->drops as $d)
        {
            echo $d->getText() . PHP_EOL;
            $dropSB .= $d->getId() . ",";
        }
        if (substr($dropSB, -1) === ',') $dropSB[strlen($dropSB) - 1] = ']';
        else $dropSB .= ']';
        $dropSB .= ";";
    }
?>
</div>
<div class="p-3 mt-5 bg-info"></div>

<?php
include_once dirname(__DIR__) . "/includes/common.shoes.php";
?>
<script>
    $(document).ready(function ()
    {
        var crumb_id = -1;
        var drop_id = -1;

        function identify($crumb)
        {
            <?php
                echo "var drops = " . $dropSB;
            ?>
            crumb_id = $('li').index($crumb.closest('li')) + 1;
            drop_id = drops[crumb_id - 1];
        }

        $('a').on('click', function()
        {
            identify($(this));
            $(this).attr("href",
                "/sheja?book_id=" + <?php echo OutlineController::$book->getId() ?>
                + "&crumb_id=" + crumb_id
                + "&drop_id=" + drop_id
                + "&p=show");
            $(this).attr("target", "_new");
        });
		
    });
</script>
</body>
</html>
