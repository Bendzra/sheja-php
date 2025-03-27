<?php

class SearchOptions
{
    static $reqBookIds   = null;
    static $searchString = null;
    static $fMatchCase   = false;
    static $fMatchWord   = false;
    static $fWylie       = false;
    static $fRegEx       = false;
    static $curBookId    = 0;
    static $pattern      = null;

    static function init()
    {
    	self::$reqBookIds   = null;
    	self::$searchString = null;
    	self::$fMatchCase   = false;
    	self::$fMatchWord   = false;
    	self::$fWylie       = false;
    	self::$fRegEx       = false;
    	self::$curBookId    = 0;
        self::$pattern      = null;
    }


    // подготавливаем RegEx
    static function apply()
    {
        $searchString = self::$searchString;
        $modifier = "mu";

        if (!self::$fMatchCase) $modifier .= "i";

        if (!self::$fRegEx) {
            // spec chars: \.[]{}()<>*+-=!?^$|
            $rx = array(
                    "\\", ".", "[", "]", "{", "}", "(", ")", "<", ">",
                    "*", "+", "-", "=", "!", "?", "^", "$", "|"
            );
            $rt = array(
                    "\\\\", "\.", "\[", "\]", "\{", "\}", "\(", "\)", "\<", "\>",
                    "\*", "\+", "\-", "\=", "\!", "\?", "\^", "\$", "\|"
            );

            $searchString = str_replace($rx, $rt, $searchString);
        }

        if (self::$fMatchWord)
        {
            if (self::$fWylie)
            {
                $searchString = "(?<=^|[\\s\")(_/!*\\[\\]{},:@#])" . $searchString . "(?=[\\s\")(_/!*\\[\\]{},:@#]|$)";

            }
            else
            {
                $searchString = "\\b" . $searchString . "\\b";
            }
        }
        self::$pattern = "/" . $searchString. "/" . $modifier;

    }

}

?>