<?php

include_once dirname(__DIR__) . "/model/Book.php";
include_once dirname(__DIR__) . "/model/Drop.php";
include_once dirname(__DIR__) . "/parser/DefaultHandler.php";

class BranchesParserHandler extends DefaultHandler
{
    private $book;              // : Book
    private $drop_id;           // : int порядковый номер (1.....)
    private $branch_id;         // : int порядковый номер (1.....)
    private $edition_index;     // : int
    private $drop_count   = 0;  // : int
    private $branch_count = 0;  // : int
    private $branch_stack_size = -1; // : int
    private $currentTag = null; // : String
    private $drop       = null; // : Drop
    private $dropFlag  = false; // : boolean
    private $crambFlag = false; // : boolean
	private $rootFlag  = false; // : boolean
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
        $this->branch_stack_size = -1;
        $this->currentTag   = null;
        $this->drop         = null;
        $this->dropFlag     = false;
        $this->crambFlag    = false;
        $this->rootFlag     = false;
        $this->drops        = array();
    }

    function getDrops()
    {
        return $this->drops;
    }

    function startElement($sax, $tag, $attr)
    {
        if($this->stop) return;

        $this->currentTag = $tag;
        if ($tag === "b")
        {
            $this->branch_count++;
            if ($this->branch_count === $this->branch_id)
            {
                $this->branch_stack_size = 0;
            }

            if ($this->branch_stack_size >= 0) // put
            {
                $this->branch_stack_size++;
            }
        }
        else if ($tag === "d")
        {
            $this->drop_count++;
            if ($this->branch_stack_size > 0)
            {
                if (!empty($attr["e"]))
                {
                    if (intval($attr["e"]) === $this->edition_index + 1)
                    {
                        $this->dropFlag = true;
                        $this->drop = new Drop();
                    }
                }

                if ($this->dropFlag)
                {
                    if($this->drop_count === $this->drop_id)
                    {
                        $this->drop->appendText("<div class='text-primary vewable'>");
                    }


                    if (isset($attr["crumb"]))
                    {
						$hn = min($this->branch_stack_size, 6);
                        $this->drop->appendText("<h" . $hn . ">");
                        $this->crambFlag = true;
                    }
                    else if (isset($attr["root"]))
                    {
                        $this->drop->appendText("<strong>");
                        $this->rootFlag = true;
                    }

                }
            }
        }
    }

    function endElement($sax, $tag)
    {
        if($this->stop) return;

        if ($tag === "b")
        {
            if ($this->branch_stack_size > 0) // pop
            {
                $this->branch_stack_size--;
            }

            if ($this->branch_stack_size === 0)
            {
                $this->stop = true;
            }
        }
        else if ($tag === "d")
        {
            if ($this->crambFlag)
            {
				$hn = min($this->branch_stack_size, 6);
                $this->drop->appendText("</h" . $hn . ">");
            }
			if ($this->rootFlag)
            {
                $this->drop->appendText("</strong>");
            }
            if ($this->dropFlag && $this->drop_count === $this->drop_id)
            {
                $this->drop->appendText("</div>");
            }
            if ($this->branch_stack_size > 0 && $this->dropFlag)
            {
                $this->drops[] = $this->drop;
            }
            $this->crambFlag = false;
			$this->rootFlag = false;
            $this->dropFlag = false;
        }
    }

    function characters($sax, $data)
    {
        if($this->stop) return;

        if (!is_null($this->currentTag) && $this->currentTag === "d" && $this->branch_stack_size > 0 && $this->dropFlag)
        {
            for ($i = 0; $i < strlen($data); $i++)
            {
				if($data[$i] === "\r")
                {
					continue;
				}
                else if ($data[$i] === "\n")
                {
                    $this->drop->appendText("<br />" . PHP_EOL);
                }
                else
                {
                    $this->drop->appendText( $data[$i] );
                }
            }
        }
    }

}

?>
