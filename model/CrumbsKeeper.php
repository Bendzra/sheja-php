<?php
include_once dirname(__DIR__) . "/model/Pair.php";

class CrumbsKeeper
{
    private $edition; // : Edition
    private $crumbs;  // : List<Pair<Integer, String>>

    public function __construct()
    {
        $this->edition = null;
        $this->crumbs = array();
    }


    function getEditionId() // : String
    {
        return $this->edition->getId();
    }

    function getEdition() // : Edition
    {
        return $this->edition;
    }

    function setEdition($edition)
    {
        $this->edition = $edition;
    }

    function getCrumbs() // : List<Pair<Integer, String>>
    {
        return $this->crumbs;
    }

    function setCrumb($index, $pair)
    {
        $this->crumbs[$index] = $pair;
    }

    function unsetCrumb($index)
    {
        // unset($this->crumbs[$index]);
        // array_splice($this->crumbs, $index, 1);
        array_pop($this->crumbs);
    }


    function addAll($crumbs)
    {
        $this->crumbs = array_merge($this->crumbs, $crumbs);
    }

    function add($crumb)
    {
        $this->crumbs[] = $crumb;
    }
}

?>
