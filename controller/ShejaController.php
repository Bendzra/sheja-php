<?php

include_once dirname(__DIR__) . "/model/Book.php";
include_once dirname(__DIR__) . "/model/BookShelf.php";


class ShejaController
{
    static $bookShelf = null;

    static function shelveNIO()
    {
        if ( !is_null(self::$bookShelf ) ) return;

        $dir = dirname(__DIR__) . "/resources";
        $xmls = self::collectXmlFiles($dir);
        sort($xmls);

        self::$bookShelf = new BookShelf();
        for ($i = 0; $i < count($xmls); $i++)
        {
            $book = new Book();
            $book->setId($i + 1);
            $book->setXmlPath($xmls[$i]);
            $book->parseEditionsXML();
            self::$bookShelf->addBook($book);
        }
    }

    static function collectXmlFiles($dir, &$xmls=array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value)
        {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if ( !is_dir($path) )
            {
                if ( "xml" === pathinfo($path, PATHINFO_EXTENSION) ) $xmls[] = $path;
            }
            else if ($value != "." && $value != "..")
            {
                self::collectXmlFiles($path, $xmls);
            }
        }
        return $xmls;
    }

    static function pickupSessionOptions()
    {
        $volumes = array();
        if( isset($_SESSION['VOLUMES']) ) $volumes = $_SESSION['VOLUMES'];
        if( empty($volumes) ) $volumes = Stats::getLastVolumes($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR']);
        if( empty($volumes) ) return;
        if( empty(self::$bookShelf->getBooks()) ) return;
        foreach(self::$bookShelf->getBooks() as &$b)
        {
            $b->setChecked(in_array($b->getId(), $volumes));
        }
    }
}

?>
