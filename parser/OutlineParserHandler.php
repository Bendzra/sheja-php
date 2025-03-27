<?php

include_once dirname(__DIR__) . "/model/Book.php";
include_once dirname(__DIR__) . "/model/Drop.php";
include_once dirname(__DIR__) . "/parser/DefaultHandler.php";

class OutlineParserHandler extends DefaultHandler
{
    private $book;              // : Book
    private $drop_id;           // : int порядковый номер (1.....)
    private $branch_id;         // : int порядковый номер (1.....)
    private $edition_index;     // : int
    private $drop_count   = 0;  // : int
    private $branch_count = 0;  // : int
    private $currentTag = null; // : String
    private $drop       = null; // : Drop
    private $dropFlag  = false; // : boolean
    private $crambFlag = false; // : boolean
    private $drops;             // : List<Drop>

    public function __construct($book, $drop_id, $branch_id)
    {
        parent::__construct();

        $this->book = $book;
        $this->drop_id = $drop_id;
        $this->branch_id = $branch_id;
        $this->edition_index = ($drop_id - 1) % count($book->getEditions());
        $this->drop_count   = 0;
        $this->branch_count = 0;
        $this->currentTag = null;
        $this->drop       = null;
        $this->dropFlag  = false;
        $this->crambFlag = false;
        $this->drops   = array();
    }

    function getDrops()
    {
        return $this->drops;
    }

    function startElement($sax, $tag, $attr)
    {
        if($this->stop) return;

        $this->currentTag = $tag;
        if ($tag === "d")
        {
            $this->drop_count++;

            if ( !empty($attr["e"]) && intval($attr["e"]) === ($this->edition_index + 1) )
            {
                $this->dropFlag = true;
            }

            if ( $this->dropFlag && isset($attr["crumb"]) )
            {
                $this->crambFlag = true;
            }

            if ($this->dropFlag && $this->crambFlag)
            {
                $this->branch_count++;
                $this->drop = new Drop();
                $this->drop->setId($this->drop_count);
                if($this->branch_count === $this->branch_id)
                {
                    $this->drop->appendText("<ul>" . PHP_EOL . "<li><a class='text-danger font-weight-bold vewable' href='#'>");
                }
                else
                {
                    $this->drop->appendText("<ul>" . PHP_EOL . "<li><a href='#'>");
                }
            }
        }
    }

    function endElement($sax, $tag)
    {
        if($this->stop) return;

        if ($tag === "d")
        {
            if ($this->dropFlag && $this->crambFlag)
            {
                $this->drop->appendText("</a></li>");
                $this->drops[] = $this->drop;
            }
            $this->dropFlag = false;
            $this->crambFlag = false;
        }
        else if ($tag === "b")
        {
            $this->drops[count($this->drops) - 1]->appendText(PHP_EOL . "</ul>");
        }
    }

    function characters($sax, $data)
    {
        if($this->stop) return;

        if (!is_null($this->currentTag) && $this->currentTag === "d" && $this->dropFlag && $this->crambFlag)
        {
            $this->drop->appendText($data);
        }
    }
}

?>
