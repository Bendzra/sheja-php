<?php

include_once dirname(__DIR__) . "/model/Pair.php";
include_once dirname(__DIR__) . "/model/SearchOptions.php";


class Drop
{
    private $id;
    private $fuseId;
    private $edition;
    private $text;
    private $starts;    // начала вхождения searchSting в text
    private $ends;      // концы вхождения searchSting в text
    private $chapters;  // List<Pair<Integer, String>>


    public function __construct()
    {
        $this->id = 0;
        $this->fuseId = 0;
        $this->edition = null;
        $this->text = "";
        $this->starts = array();
        $this->ends = array();
        $this->chapters = array();
    }


    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function getEditionId() // : String
    {
        return $this->edition->getId();
    }

    function getEdition()
    {
        return $this->edition;
    }

    function setEdition($edition)
    {
        $this->edition = $edition;
    }

    function getText()
    {
        return $this->text;
    }

    function setText($text)
    {
        $this->text = $text;
    }

    function appendText($text)
    {
        $this->text .= $text;
    }

    function getStarts() // : List<Integer>
    {
        return $this->starts;
    }

    function setStarts($starts)
    {
        $this->starts = $starts;
    }

    function getEnds() // : List<Integer>
    {
        return $this->ends;
    }

    function setEnds($ends)
    {
        $this->ends = $ends;
    }

    function getFuseId()
    {
        return $this->fuseId;
    }

    function setFuseId($fuseId)
    {
        $this->fuseId = $fuseId;
    }

    function getChapters() // List<Pair<Integer, String>>
    {
        return $this->chapters;
    }

    function addChapters($chapters)
    {
        $this->chapters = array_merge($this->chapters, $chapters);
    }


    function findIndexes()
    {
        $matches = null;

		$n = preg_match_all(SearchOptions::$pattern, $this->text, $matches, PREG_OFFSET_CAPTURE);
        for ($i = 0; $n && $i < count($matches[0]); $i++)
        {
            $this->starts[] = $matches[0][$i][1];
            $this->ends[]   = $matches[0][$i][1] + strlen($matches[0][$i][0]);
        }


		// if($n) 
		// {
			// echo "<pre>";
			// var_dump(SearchOptions::$pattern, $this->text, $n, $matches, $this->starts);
			// echo "</pre>";
		// }

        return $this->starts;
    }

}


?>
