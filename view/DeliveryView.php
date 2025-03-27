<?php

    include_once dirname(__DIR__) . "/controller/ShejaController.php";
    // ShejaController::shelveNIO();

    include_once dirname(__DIR__) . "/model/Pair.php";
    include_once dirname(__DIR__) . "/model/Drop.php";
    include_once dirname(__DIR__) . "/model/Book.php";
    include_once dirname(__DIR__) . "/model/Found.php";
    include_once dirname(__DIR__) . "/model/BookShelf.php";

    include_once dirname(__DIR__) . "/controller/DeliveryController.php";
    DeliveryController::boltVolumes();
    DeliveryController::doSearch();
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    include_once dirname(__DIR__) . "/includes/common.head.php";
    ?>
    <title>Found</title>
</head>
<body>

<?php
    $crumbSB = ""; // StringBuilder
    $dropSB  = ""; // StringBuilder
?>

<div class="container">
<?php
    if ( empty(DeliveryController::$results) || empty(DeliveryController::$results->getPairs()) )
    {
       $msg = include dirname(__DIR__) . '/includes/on.error.page.php';
       echo str_replace (
            ['{{param.info}}',                                                    '{{param.error}}',  '{{param.proposal}}'],
            ["the <span class='text-danger'>search results</span> delivery page", "No entries found", "Start over from the main search page!"],
            $msg
        );
}
else
{
echo "<div class='p-3 mb-2 bg-success text-white'>" . count(DeliveryController::$results->getPairs()) . " entries found</div>" . PHP_EOL;
    echo "<div class='d-flex flex-wrap justify-content-center align-items-center my-5'>" . PHP_EOL;
    foreach (DeliveryController::$ofInterest->getBooks() as $b)
    {
        if( $b->getId() === DeliveryController::$book->getId())
        {
            echo "<a href='#' class='btn btn-primary ofInterest' ";
        }
        else
        {
            echo "<a href='#' class='btn ofInterest' ";
        }
		echo "data-toggle='tooltip' data-placement='top' ";
		echo "title='" . str_replace( array("'", '"', "`") , "’", $b->getEditions()[0]) . "'>" . $b->getId() . "</a> ";
    }
    echo "</div>" . PHP_EOL;

    $crumbSB .= "[";
    $dropSB .= "[";

    foreach (DeliveryController::$results->getPairs() as $p)
    {

        $drop = $p->getKey()->getKey();
        $chapters = $drop->getChapters();

        $dropSB .= $drop->getId() . ",";

        echo "<div class='row'>" . PHP_EOL;
        echo "<div class='col'>" . PHP_EOL;

        echo "<small><a href='#' class='edition'>";
        echo $drop->getEdition()->getTitle();
        echo "</a></small>";

        echo "<small class='drop'>";
        $crumbSB .= '[';

        for ($i = 0; $i < count($chapters); $i++)
        {
            if ($i == 0) echo "<br />";

            if ($i == 0) $crumbSB .= $chapters[$i]->getKey();
            else
            {
                $crumbSB .= ",";
                $crumbSB .= $chapters[$i]->getKey();
            }
            echo "&gt;<a href='#' class='text-secondary px-1 crumb'>";
            echo $chapters[$i]->getValue() . "</a>";
        }

        $crumbSB .= "],";

        echo ":</small>" . PHP_EOL;
        echo "</div>" . PHP_EOL;

        $drop = $p->getValue()->getKey();
        $chapters = $drop->getChapters();

        $dropSB .= $drop->getId() . ",";

        echo "<div class='col'>" . PHP_EOL;

        echo "<small><a href='#' class='edition'>";
        echo $drop->getEdition()->getTitle();
        echo "</a></small>";

        echo "<small class='drop'>";
        for ($i = 0; $i < count($chapters); $i++)
        {
            if ($i == 0) echo "<br />";
            echo "&gt;<a href='#' class='text-secondary px-1 crumb'>";
            echo $chapters[$i]->getValue() . "</a>";
        }
        echo ":</small>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
        echo "</div>" . PHP_EOL;

        echo "<div class='row border-bottom align-items-center py-4'>" . PHP_EOL;
        echo "<div class='col'>" . $p->getKey()->getValue() . "</div>" . PHP_EOL;
        echo "<div class='col'>" . $p->getValue()->getValue() . "</div>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
    }
    if (substr($crumbSB, -1) === ',') $crumbSB[strlen($crumbSB) - 1] = ']';
    else $crumbSB .= ']';
    $crumbSB .= ';';

    if (substr($dropSB, -1) === ',') $dropSB[strlen($dropSB) - 1] = ']';
    else $dropSB .= ']';
    $dropSB .= (';');

    echo "<div class='d-flex flex-wrap justify-content-center align-items-center my-5'>" . PHP_EOL;
    foreach (DeliveryController::$ofInterest->getBooks() as $b)
    {
        if( $b->getId() === DeliveryController::$book->getId())
        {
            echo "<a href='#' class='btn btn-primary ofInterest' ";
        }
        else
        {
            echo "<a href='#' class='btn ofInterest' ";
        }
		echo "data-toggle='tooltip' data-placement='bottom' ";
		echo  "title='" . str_replace( array("'", '"', "`") , "’", $b->getEditions()[1]) . "'>" . $b->getId() . "</a> ";
    }
    echo "</div>" . PHP_EOL;

echo "<div class='p-3 mb-2 bg-success text-white'></div>" . PHP_EOL;
}
?>
</div> <!-- container -->

<?php
include_once dirname(__DIR__) . "/includes/common.shoes.php";
?>

<script>
    $(document).ready(function () {

		$('[data-toggle="tooltip"]').tooltip();
		
        $('.toggleRight').on('click', function () {
            var row = $(this).closest('div.row');
            row.find(".collapse._hidden").collapse('toggle');
            var rights = row.find(".toggleRight");
            rights.each(function () {
                $(this).children('.fa').toggleClass("fa-chevron-down fa-chevron-up");
            });
        });

        $('.toggleLeft').on('click', function () {
            var row = $(this).closest('div.row');
            row.find(".collapse.hidden_").collapse('toggle');
            var lefts = row.find(".toggleLeft");
            lefts.each(function () {
                $(this).children('.fa').toggleClass("fa-chevron-down fa-chevron-up");
            });
        });

        var crumb_id = -1;
        var drop_id = -1;

        function identify($crumb) {
            <?php
                echo "var crumbs = " . $crumbSB . PHP_EOL;
                echo "var drops  = " . $dropSB . PHP_EOL;
            ?>
            var step_away = $crumb.index() - 1;
            var drop_index = $('.drop').index($crumb.closest('.drop'));
            drop_id = drops[drop_index];

            var drops_per_row = drops.length / crumbs.length | 0;
            var fuse_index = drop_index / drops_per_row | 0;
            crumb_id = crumbs[fuse_index][step_away];
        }

        $('.edition').on('click', function() {
            var $drop = $(this).closest('small').next();
            var $crumb = $drop.children().last();

            identify($crumb);
            $(this).attr("href",
                "/sheja?book_id=" + <?php echo DeliveryController::$book->getId() ?>
                + "&crumb_id=" + crumb_id
                + "&drop_id=" + drop_id
                + "&p=outline");
            $(this).attr("target", "_new");
        });

        $('.crumb').on('click', function() {
            identify($(this));
            $(this).attr("href",
                "/sheja?book_id=" + <?php echo DeliveryController::$book->getId() ?>
                + "&crumb_id=" + crumb_id
                + "&drop_id=" + drop_id
                + "&p=show");
            $(this).attr("target", "_new");
        });

        $('.ofInterest').on('click', function() {
            <?php
                echo "var qs = \"" . $_SERVER['QUERY_STRING'] ."\"" . PHP_EOL;
            ?>
            qs = qs.replace(/&?v=\d+\b/g, "");

            var s = "";
            $(this).parent().children().each(function( i ) {
                if (i !== 0) s += "&";
                s += "v=" + $( this ).text();
            });
            if (qs.length > 0 && qs.charAt(0) !== '&') s += '&';
            qs = s + qs;

            var re = /\bcb=\d+\b/;
            s = "cb=" + $(this).text();
            if(re.test(qs)) {
                qs = qs.replace(re, s);
            } else {
                qs += "&" + s;
            }
            $(this).attr("href", "/sheja?" + qs );
            $(this).attr("target", "_self");
        });

    });
</script>

</body>
</html>
