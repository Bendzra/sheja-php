<?php

include_once dirname(__DIR__) . "/model/Pair.php";
include_once dirname(__DIR__) . "/model/Drop.php";
include_once dirname(__DIR__) . "/model/Found.php";


class Fuse
{
    private $id;        // : int
    private $drops;     // excerpts : List<Drop>
    private $matches;   // : Found

    private const MAX_CHARS = 0x7F;

    public function __construct()
    {
        $this->drops = array();
        $this->matches = new Found();
    }

    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function getDrops()
    {
        return $this->drops;
    }

    function addDrop($drop)
    {
        return $this->drops[] = $drop;
    }

    function entwine()
    {
        for ($i = 0; $i < count($this->drops); $i++)
        {
            $d1 = $this->drops[$i]; // : Drop
            for ($j = 0; $j < count($d1->getStarts()); $j++)
            {
                $start1 = $d1->getStarts()[$j];
                $end1   = $d1->getEnds()[$j];
                $s1 = $this->doleOut($d1, $start1, $end1, self::MAX_CHARS, true); // : String
                for ($k = 0; $k < count( $this->drops ); $k++)
                {
                    if ($k === $i) continue;
                    $d2 = $this->drops[$k]; // : Drop
                    $f = strlen($d2->getText()) / strlen($d1->getText());
                    $start2 = intval($f * $start1);
                    $end2   = intval($f * $end1);
                    $span   = intval($f * self::MAX_CHARS);
                    $s2 = $this->doleOut($d2, $start2, $end2, max($span, self::MAX_CHARS), false);

                    $this->matches->addPair(new Pair( new Pair($d1, $s1), new Pair($d2, $s2)));
                }
            }
        }
    }

    function leftBranch($drop, $start, $span)
    {
        $left = max(0, $start - $span);
        while ($left > 0) {
            $ch = $drop->getText()[$left];
            if ($ch === " " || $ch === "\n" || $ch === "\r" || $ch === "\t")
            {
                $left++;
                break;
            }
            $left--;
        }
        return $left;
    }

    function rightBranch($drop, $end, $span)
    {
        $right = min( $end + $span, strlen( $drop->getText() ) );
        while ( $right < strlen( $drop->getText() ) ) {
            $ch = $drop->getText()[$right];
            if ($ch === " " || $ch === "\n" || $ch === "\r" || $ch === "\t")
            {
//                $right--;
                break;
            }
            $right++;
        }
        return $right;
    }

    function doleOut($drop, $start, $end, $span, $select) // : String
    {
        $left       = $this->leftBranch($drop, $start, $span);
        $leftLeft   = $this->leftBranch($drop, $left, 2 * $span);
        $right      = $this->rightBranch($drop, $end, $span);
        $rightRight = $this->rightBranch($drop, $right, 2 * $span);

        $overLeft = "<div class='middle'>";
        if ($left > 0) {
            $overLeft = "<div class='collapse hidden_'>"
                    . substr($drop->getText(), $leftLeft, $left - $leftLeft)
                    . "</div><div class='middle'><a class='btn btn-sm toggleLeft'><i class='fa fa-chevron-up'></i></a> ";
        }

        $overRight = "</div>";
        if ( $right < strlen($drop->getText()) ) {
            $overRight = " <a class='btn btn-sm toggleRight'><i class='fa fa-chevron-down'></i></a></div><div class='collapse _hidden'>"
                    . substr($drop->getText(), $right, $rightRight - $right) . "</div>";
        }

        $middle = ($select) ? substr($drop->getText(), $left, $start - $left)
                . "<b class='text-info'>" . substr($drop->getText(), $start, $end - $start) . "</b>"
                . substr($drop->getText(), $end, $right - $end) : substr($drop->getText(), $left, $right - $left);

        return $overLeft . $middle . $overRight;
    }

    function getMatches()
    {
        return $this->matches;
    }
}

?>
