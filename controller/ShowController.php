<?php

include_once dirname(__DIR__) . "/model/Drops.php";
include_once dirname(__DIR__) . "/controller/ShejaController.php";
include_once dirname(__DIR__) . "/parser/BranchesParserHandler.php";

class ShowController
{
    static $branch = null;

    static function doGet()
    {
        // по полученным book_id и crumb_id (=branch_id) парсим xml и получаем всю ветку
        // выделяем drop_id

        if( is_null(ShejaController::$bookShelf) )
        {
            self::$branch = null;
            return;
        }

        $book_id  = empty($_GET["book_id"])  ? 1 : intval($_GET["book_id"]);
        $crumb_id = empty($_GET["crumb_id"]) ? 1 : intval($_GET["crumb_id"]);
        $drop_id  = empty($_GET["drop_id"])  ? 1 : intval($_GET["drop_id"]);

        if ($book_id  === 0) $book_id  = 1;
        if ($crumb_id === 0) $crumb_id = 1;
        if ($drop_id  === 0) $drop_id  = 1;

        $handler = new BranchesParserHandler(ShejaController::$bookShelf->getBooks()[$book_id - 1], $drop_id, $crumb_id);
        $parser = $handler->newSAXParser();
        $path = ShejaController::$bookShelf->getBooks()[$book_id - 1]->getXmlPath();
        $handler->parse($path);
        self::$branch = new Drops();
        self::$branch->drops = $handler->getDrops();
        $handler->freeSaxParser();
    }
}

?>
