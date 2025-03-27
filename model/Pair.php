<?php

class Pair
{
    // по-простому, без setter'ов и getter'ов

	private $key;
	private $value;

    public function __construct($key=null, $value=null)
    {
        $this->key = $key;
        $this->value = $value;
    }

    function getKey()
    {
        return $this->key;
    }

    function getValue()
    {
        return $this->value;
    }
}

?>