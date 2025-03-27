<?php

include_once dirname(__DIR__) . "/model/Pair.php";
include_once dirname(__DIR__) . "/model/Drop.php";
include_once dirname(__DIR__) . "/parser/DefaultHandler.php";

// находит в xml первый match или не находит

class TestParserHandler extends DefaultHandler
{
    private $currentTag;
    private $drop;
    public $found;

    public function __construct()
    {
        parent::__construct();
        $this->currentTag = null;
        $this->drop = new Drop();
        $this->found = false;
    }

    function startElement($sax, $tag, $attr)
    {
        if($this->stop) return;

        $this->currentTag = $tag;
        if ($tag === "d")
        {
            $this->drop->setText("");
        }
    }

    function endElement($sax, $tag)
    {
        if($this->stop) return;

        if ($tag === "d")
        {
            if ( count($this->drop->findIndexes()) > 0 )
            {
                $this->found = true;
                $this->stop = true;
            }
        }
    }

    function characters($sax, $data)
    {
        if($this->stop) return;

        if (!is_null($this->currentTag) && $this->currentTag === "d")
        {
            $this->drop->appendText( $data );
        }
    }

}

?>
