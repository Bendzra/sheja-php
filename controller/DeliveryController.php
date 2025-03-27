<?php

include_once dirname(__DIR__) . "/model/Book.php";
include_once dirname(__DIR__) . "/model/BookShelf.php";
include_once dirname(__DIR__) . "/model/SearchOptions.php";
include_once dirname(__DIR__) . "/parser/TestParserHandler.php";
include_once dirname(__DIR__) . "/parser/MatchesParserHandler.php";


class DeliveryController
{

    static $ofInterest     = null; // bookShelf
    static $results        = null; // results found
    static $book           = null; // book explored

    // парсим GET запрс
    static function boltVolumes()
    {
        SearchOptions::init();

        $isValid = (!empty($_GET['q'])) && (strlen($_GET['q']) > 0) ;
        if(!$isValid) return null;

        // фильтруем книги в запросе:

        $query  = explode('&', $_SERVER['QUERY_STRING']);
        $volumes = array();
        $shelved_count = count(ShejaController::$bookShelf->getBooks());

        foreach($query as $param)
        {
            if (strpos($param, '=') === false) continue;
            list($name, $value) = explode('=', $param, 2);

            if ($name !== 'v' || !is_numeric($value) ) continue;
            $value = intval($value);

            if ($value > $shelved_count || $value < 1) continue;
            if (in_array($value, $volumes)) continue;
            $volumes[] = $value;
        }

        if ( empty($volumes) ) return null;

        SearchOptions::$searchString = $_GET['q'];
        SearchOptions::$reqBookIds = $volumes;

        SearchOptions::$fMatchCase = !empty($_GET[ "case"]);
        SearchOptions::$fMatchWord = !empty($_GET[ "word"]);
        SearchOptions::$fWylie     = !empty($_GET["wylie"]);
        SearchOptions::$fRegEx     = !empty($_GET["regex"]);

        $cb = empty($_GET["cb"]) ? 0 : $_GET["cb"];
        $cb = !is_numeric($cb)  ? 0 : intval($cb);
        SearchOptions::$curBookId = in_array($cb, $volumes) ? $cb : 0;

        SearchOptions::apply();

        return $volumes;
    }

    // сохраняем в сессии $reqBookIds, etc.
    static function storeSessionOptions()
    {
        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
        $_SESSION['REMOTE_IP'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['SPOOFED_IP'] = $_SERVER['HTTP_X_FORWARDED_FOR'];

        if ( SearchOptions::$curBookId !== 0 ) return;
        if ( empty(SearchOptions::$reqBookIds) ) return;

        $_SESSION['VOLUMES'] = SearchOptions::$reqBookIds;
        $_SESSION['SEARCH'] = SearchOptions::$searchString;

        $client = new Stats($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR']);
        $client->setVolumes( implode(",", $_SESSION['VOLUMES']) );
        $client->readClients();

    }

    // пробегаем поиском по всем файлам из запрошенного списка,
    // по абсолютному пути парсим xml и
    // составляем новый список из тех, где что-то нашли.
    static function doTest()
    {
        self::storeSessionOptions();

        $interestingIDs = array();
        foreach (SearchOptions::$reqBookIds as $id)
        {
            $handler = new TestParserHandler();
            $parser = $handler->newSAXParser();

            $book_index = $id - 1;
            $path = ShejaController::$bookShelf->getBooks()[$book_index]->getXmlPath();
            $handler->parse($path);
            if ($handler->found)
            {
                $interestingIDs[] = $id;
            }
            $handler->freeSaxParser();
        }
        return $interestingIDs;
    }


    static function doSearch()
    {
		$interestingIDs = null;
		$ofInterest = new BookShelf();

		// if there is no current book in the query, filter the list of all books
		if (SearchOptions::$curBookId === 0)
        {
			$interestingIDs = self::doTest();
		}
        else
        {
			$interestingIDs = SearchOptions::$reqBookIds;
		}

        if( empty($interestingIDs) ) return ;

        foreach ($interestingIDs as $id)
        {
            $ofInterest->addBook(ShejaController::$bookShelf->getBooks()[$id - 1]);
        }

        $book_index = (SearchOptions::$curBookId === 0) ? $interestingIDs[0] - 1 : SearchOptions::$curBookId - 1;

        $path = ShejaController::$bookShelf->getBooks()[$book_index]->getXmlPath();

        $handler = new MatchesParserHandler();
        $parser = $handler->newSAXParser();
        $handler->parse($path);

        self::$results = $handler->getResults();
        self::$book = ShejaController::$bookShelf->getBooks()[$book_index];
        self::$ofInterest = $ofInterest;

        $handler->freeSaxParser();
    }

}

?>
