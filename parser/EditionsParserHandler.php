<?php

include_once dirname(__DIR__) . "/model/Edition.php";
include_once dirname(__DIR__) . "/parser/DefaultHandler.php";

class EditionsParserHandler extends DefaultHandler
{
    // по-простому, без setter'ов и getter'ов

    public $editions;
    public $ul;
    public $checked;

    public function __construct()
    {
        parent::__construct();
        $this->editions = array();
        $this->ul = "book";
        $this->checked = false;
    }

    function startElement($sax, $tag, $attr)
    {
        if($this->stop) return;

        if ($tag === "editions")
        {
            $this->checked = ("true" === strtolower($attr["checked"]));
            $this->ul = $attr["ul"];
        }
        else if ($tag === "edition")
        {
            $edition = self::newEdition($attr);
            $this->editions[] = $edition;
        }
    }

    function endElement($sax, $tag)
    {
        if($this->stop) return;

        if ($tag === "editions")
        {
            $this->stop = true;
        }
    }

    function characters($sax, $data)
    {
        if($this->stop) return;
    }

    static function newEdition($attr)
    {
        $edition = new Edition();
        // считаем, что названия полей в моделе (классе)
        // совпадают с названиями атрибутов в теге (xml:edition)
        $fields = $edition->getFields();
        foreach ($fields as $k => $v)
        {
            $funcname = "set" . ucfirst($k);
            $edition->$funcname( $attr[$k] );
        }
        return $edition;
    }
}

?>
