<?php

abstract class DefaultHandler
{
    protected $sax = null;
    protected $stop = false;

    function __construct()
    {
        $this->sax = null;
        $this->stop = false;
    }

    abstract function startElement($sax, $tag, $attr);
    abstract function endElement($sax, $tag);
    abstract function characters($sax, $data);

    function newSAXParser()
    {
        $this->sax = xml_parser_create();
        xml_set_object($this->sax, $this);
        xml_parser_set_option($this->sax, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->sax, XML_OPTION_SKIP_WHITE, 1);
        xml_set_element_handler($this->sax, "startElement", "endElement");
        xml_set_character_data_handler($this->sax, "characters");

        return $this->sax;
    }

    function freeSaxParser()
    {
        xml_parser_free($this->sax);
    }

    function parse($xmlPath)
    {
        $fp = fopen($xmlPath, "r");
        $data = '';
        while ( ($data = fread($fp, 4096)) )
        {
            $is_final = $this->stop || feof($fp);
            if( 0 === xml_parse($this->sax, $data, $is_final) )
            {
                if(!$is_final)
                {
                    echo (sprintf("XML Error: %s at line %d", xml_error_string(xml_get_error_code($this->sax)), xml_get_current_line_number($this->sax))) . "<br>";
                }
                break;
            }
        }

        fclose($fp);
    }

}

?>
