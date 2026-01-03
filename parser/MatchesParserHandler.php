<?php

include_once dirname(__DIR__) . "/model/Pair.php";
include_once dirname(__DIR__) . "/model/CrumbsKeeper.php";
include_once dirname(__DIR__) . "/model/Fuse.php";
include_once dirname(__DIR__) . "/model/Found.php";
include_once dirname(__DIR__) . "/parser/DefaultHandler.php";


class MatchesParserHandler extends DefaultHandler
{
    private $editions;    // : List<Edition>
    private $spreadsheet; // Текущие списки crumbs для всех всех editions: List<CrumbsKeeper>
    private $stepsAway;   // как далеко отошли по крошкам (= stack.size) : int
    private $branchID;    // <b>...</b> : int
    private $fuseID;      // <f>...</f> : int
    private $dropID;      // <d>...</d>
    private $currentTag;  // : String
    private $drop;        // : Drop
    private $dropFlag;    // : boolean
    private $fuse;        // : Fuse
    private $found;       // : boolean
    private $results;     // : Found

    private const MAX_PAIRS = 402;


    public function __construct()
    {
        parent::__construct();
        $this->editions    = array();
        $this->spreadsheet = array();
        $this->stepsAway   = 0;
        $this->branchID    = 0;
        $this->fuseID      = 0;
        $this->dropID      = 0;
        $this->currentTag  = null;
        $this->drop        = null;
        $this->dropFlag    = false;
        $this->fuse        = null;
        $this->found       = false;
        $this->results     = new Found();
    }


    function startElement($sax, $tag, $attr)
    {
        if($this->stop) return;

        $this->currentTag = $tag;
        if ($tag === $this->editionTag)
        {
            $edition = EditionsParserHandler::newEdition($attr);
            $this->editions[] = $edition;
            $editionCrumbs = new CrumbsKeeper();
            $editionCrumbs->setEdition($edition);
            $this->spreadsheet[] = $editionCrumbs;
        }
        else if ($tag === $this->branchTag)
        {
            $this->branchID++;
            $this->stepsAway++;
            foreach ($this->spreadsheet as &$crumbsKeeper)
            {
                $crumbsKeeper->add(new Pair(-1, "_placeholder_"));
            }
        }
        else if ($tag === $this->fuseTag)
        {
            $this->fuse = new Fuse();
            $this->fuse->setId(++$this->fuseID);
            $this->found = false;
        }
        else if ($tag === $this->dropTag)
        {
            $this->drop = new Drop();
            $this->dropFlag = true;
            $this->drop->setId(++$this->dropID);
            $this->drop->setFuseId($this->fuseID);

            // добавляем edition
            if ( !empty($attr["e"]) )
            {
                foreach ($this->editions as $edition)
                {
                    if ($edition->getId() === $attr["e"])
                    {
                        $this->drop->setEdition($edition);
                        break;
                    }
                }
            }

            // проверяем на crumb и добавляем в текущий список крошек, если есть
            if ( isset($attr["crumb"]) )
            {
                foreach ($this->spreadsheet as &$crumbsKeeper)
                {
                    if ($crumbsKeeper->getEditionId() === $this->drop->getEditionId())
                    {
                        $crumbsKeeper->setCrumb($this->stepsAway - 1, new Pair($this->branchID, $attr["crumb"]));
                        break;
                    }
                }
            }

            foreach ($this->spreadsheet as &$crumbsKeeper)
            {
                if ($crumbsKeeper->getEditionId() === $this->drop->getEditionId())
                {
                    $this->drop->addChapters($crumbsKeeper->getCrumbs());
                    break;
                }
            }

            $this->fuse->addDrop($this->drop);
        }
    }

    function characters($sax, $data)
    {
        if($this->stop) return;

        if ( $this->dropFlag  )
        {
            $this->drop->appendText( $data );
        }
    }

    function endElement($sax, $tag)
    {
        if($this->stop) return;

        if ($tag === $this->dropTag)
        {
            $integers = $this->drop->findIndexes();
            $t = count($integers);

            if ($t > 0)
            {
                $this->found = true;
            }

            $this->dropFlag = false;
        }
        else if ($tag === $this->fuseTag)
        {
            if ($this->found)
            {
                $this->fuse->entwine();
                $this->results->addPairs($this->fuse->getMatches()->getPairs());

                if ( count( $this->results->getPairs() ) >= self::MAX_PAIRS - 1)
                {
                    $this->stop = true;
                    $pair1 = $this->results->getPairs()[count( $this->results->getPairs() ) - 1]->getKey();
                    $pair2 = $this->results->getPairs()[count( $this->results->getPairs() ) - 1]->getValue();
                    $this->results->addPair(
                            new Pair(
                                    new Pair($pair1->getKey(), "<div class='p-3 mb-2 bg-info text-white'>limit exceeded</div>"),
                                    new Pair($pair2->getKey(), "<div class='p-3 mb-2 bg-info text-white'>the job to remove all restrictions is to be done although</div>")
                            )
                    );
                }
            }
        }
        else if ($tag === $this->branchTag)
        {
            $this->stepsAway--;
            foreach($this->spreadsheet as &$crumbsKeeper)
            {
                $crumbsKeeper->unsetCrumb($this->stepsAway);
            }
        }

        $this->currentTag = null;
    }

    function getResults()
    {
        return $this->results;
    }

}

?>
