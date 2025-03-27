<?php

class Edition
{
    // Названия полей в моделе (классе) должны совпадать с названиями атрибутов в теге (xml:edition)

    private $id         = "";
    private $lang       = "";
    private $title      = "";
    private $author     = "";
    private $translator = "";

    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function getLang()
    {
        return $this->lang;
    }

    function setLang($lang)
    {
        $this->lang = $lang;
    }

    function getTitle()
    {
        return $this->title;
    }

    function setTitle($title)
    {
        $this->title = $title;
    }

    function getAuthor()
    {
        return $this->author;
    }

    function setAuthor($author)
    {
        $this->author = $author;
    }

    function getTranslator()
    {
        return $this->translator;
    }

    function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getFields()
    {
        return get_object_vars($this);
    }
	
	public function __toString()
    {
        return $this->title . ". " . $this->author . ". " . $this->translator;
    }
	
}

?>
