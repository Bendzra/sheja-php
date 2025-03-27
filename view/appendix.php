<?php

$appendix = $_GET['a'] ;


if($appendix === "glossary")
{
  include_once dirname(__DIR__) . "/includes/sheja-appendix.combined.glossary.htm";
}
else if ($appendix === "names")
{
  include_once dirname(__DIR__) . "/includes/sheja-appendix.combined.names.htm";
}

?>
