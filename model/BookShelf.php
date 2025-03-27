<?php

class BookShelf
{
    private $books;
    // public $default_checked_ids;

    public function __construct()
    {
        // $this->default_checked_ids = array();
        $this->books = array();
    }


    function setBooks($books)
    {
        $this->books = $books;
    }

    function getBooks()
    {
        return $this->books;
    }

    function addBook($book)
    {
        return $this->books[] = $book;
    }
}

?>