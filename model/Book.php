<?php

include_once dirname(__DIR__) . "/parser/EditionsParserHandler.php";

class Book
{
    private $id;
    private $xmlPath;
    private $ul;       // название "папки" (категория книги)
    private $checked;
    private $editions;

    public function __construct()
    {
        $this->id = 0;
        $this->xmlPath = null;
        $this->ul      = "";
        $this->checked = false;
        $this->editions = array();
    }

    function getXmlPath()
    {
        return $this->xmlPath;
    }

    function setXmlPath($xmlPath)
    {
        $this->xmlPath = $xmlPath;
    }

    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function getEditions()
    {
        return $this->editions;
    }

    function addEdition($edition)
    {
        return $this->editions[] = $edition;
    }

    // по абсолютному пути парсит xml и запоминает все editions
    function parseEditionsXML()
    {
        $handler = new EditionsParserHandler();
        $parser = $handler->newSAXParser();

        $handler->parse($this->xmlPath);

        $this->editions = $handler->editions;
        $this->ul = $handler->ul;
        $this->checked = $handler->checked;

        $handler->freeSaxParser();
    }

    function getUL()
    {
        return $this->ul;
    }

    function setUL($ul)
    {
        $this->ul = $ul;
    }

    function isChecked()
    {
        return $this->checked;
    }

    function setChecked($checked)
    {
        $this->checked = $checked;
    }
}


?>
