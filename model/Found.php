<?php

include_once dirname(__DIR__) . "/model/Pair.php";

class Found
{
    // одна пара = (key, value), где:
    // key -> drop,
    // value -> найденный результат = обрезанная строка
    //
    // pair = match = (пара1, пара2) = Pair<Pair, Pair>

    private $pairs; // : List<Pair<Pair<Drop, String>, Pair<Drop, String>>>

    public function __construct()
    {
        $this->pairs = array();
    }


    function getPairs() // : List<Pair<Pair<Drop, String>, Pair<Drop, String>>>
    {
        return $this->pairs;
    }

    function setMatches($pairs)
    {
        $this->pairs = $pairs;
    }

    function addPairs($pairs)
    {
        $this->pairs = array_merge($this->pairs, $pairs);
    }

    function addPair($pair)
    {
        $this->pairs[] = $pair;
    }
}


?>
