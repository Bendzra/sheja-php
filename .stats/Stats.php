<?php

class Stats
{
	private $remote_ip;
	private $spoofed_ip;
	private $visits;
	private $recent;
	private $client;
	private $volumes;

	private const MAX_RECENT = 10;
	private const STATS_FILE = __DIR__ . "/.stats";

	// <clients>
	// 	<remote ip="remote1">
	// 		<spoofed ip="spoofed1" visits="1" volumes="5,6,8">
	// 			<recent time="time1">recent1</recent>
	// 			<recent time="time2">recent2</recent>
	// 		</spoofed>
	// 		<spoofed ip="spoofed2" visits="2" volumes="1,2,4,21">
	// 			<recent time="time3">recent1</recent>
	// 			<recent time="time4">recent2</recent>
	// 		</spoofed>
	// 	</remote>
	// 	<remote ip="remote2">
	// 		<spoofed ip="spoofed1" visits="1" volumes="1,2,4">
	// 			<recent>recent1</recent>
	// 			<recent>recent2</recent>
	// 		</spoofed>
	// 		<spoofed ip="spoofed2" visits="2" volumes="1,2,4,9">
	// 			<recent time="time5">recent1</recent>
	// 			<recent time="time6">recent2</recent>
	// 		</spoofed>
	// 	</remote>
	// </clients>

    public function __construct($remote_ip="no", $spoofed_ip="no", $visits=0, $recent=array(), $volumes="")
    {
        $this->remote_ip  = $remote_ip;
        $this->spoofed_ip = $spoofed_ip;
        $this->visits  = $visits ;
		$this->recent  = $recent ;
		$this->volumes = $volumes;
    }

    function readClients()
    {
    	$r = false;
		$xmlDoc = new DOMDocument();
		// $xmlDoc->preserveWhiteSpace = false;
		// $xmlDoc->formatOutput = true;

		if( file_exists(self::STATS_FILE) ) $r = $xmlDoc->load(self::STATS_FILE);
		if($r)
		{
			$root = $xmlDoc->documentElement;
			$r = ("clients" === $root->tagName);
		}
		if(!$r) $r = $xmlDoc->loadXML('<clients></clients>');
		if(!$r) return;

		$xpath = new DOMXpath($xmlDoc);

		$remotes = $xpath->query("/clients/remote[@ip='" . $this->remote_ip . "']");

		if (is_null($remotes) || count($remotes) === 0 || !$remotes)
		{
			$remote = $this->addNewRemote($xmlDoc);
  			$spoofed = $this->addNewSpoofed($xmlDoc, $remote);
  			$this->addNewRecent($xmlDoc, $spoofed);

			$xmlDoc->save(self::STATS_FILE);
			return;
		}

		$spoofeds = $xpath->query("/clients/remote[@ip='" . $this->remote_ip . "']/spoofed[@ip='" . $this->spoofed_ip . "']");

		if (is_null($spoofeds) || count($spoofeds) === 0 || !$spoofeds)
		{
			$remote = $remotes[0];
  			$spoofed = $this->addNewSpoofed($xmlDoc, $remote);
  			$this->addNewRecent($xmlDoc, $spoofed);

			$xmlDoc->save(self::STATS_FILE);
			return;
		}


		$spoofed = $spoofeds[0];
		$spoofed->setAttribute("visits", (string) (intval($spoofed->getAttribute('visits')) + 1) );
		$spoofed->setAttribute("volumes", $this->volumes);
		$recents = $spoofed->childNodes;
		$d = -1;
		$i = 0;
		for(; $i < count($recents); $i++)
		{
			if( $d === -1 && trim($recents[$i]->nodeValue) === trim(SearchOptions::$searchString) )
			{
				$d = $i;
			}
		}

		if($d > -1) {
			$i--;
			$spoofed->removeChild($recents->item($d));
		} else if ($i >= self::MAX_RECENT) {
			$i--;
			$spoofed->removeChild($recents->item(0));
		}

		if ($i < self::MAX_RECENT)
		{
  			$this->addNewRecent($xmlDoc, $spoofed);
		}

		$xmlDoc->save(self::STATS_FILE);

	}

	function setVolumes($volumes)
	{
		$this->volumes = $volumes;
	}

	function getVolumes()
	{
		return $this->volumes;
	}

	static function getLastVolumes($remote_ip, $spoofed_ip)
	{
    	$r = false;
		$xmlDoc = new DOMDocument();

		if( file_exists(self::STATS_FILE) ) $r = $xmlDoc->load(self::STATS_FILE);
		if($r)
		{
			$root = $xmlDoc->documentElement;
			$r = ("clients" === $root->tagName);
		}
		if(!$r) return;

		$xpath = new DOMXpath($xmlDoc);
		$r = $xpath->query( "/clients/remote[@ip='" . $remote_ip . "']/spoofed[@ip='" . $spoofed_ip . "']/@volumes" );
		if( count($r) === 0 ) return;

		return explode(",", $r[0]->value);

	}

	private function addNewRemote($xmlDoc)
	{
		$remote = $xmlDoc->createElement("remote");
		$remote->setAttribute("ip", (string) $this->remote_ip);
		$xmlDoc->documentElement->appendChild($remote);
		return $remote;
	}

	private function addNewSpoofed($xmlDoc, $remote)
	{
		$spoofed = $xmlDoc->createElement("spoofed");
		$spoofed->setAttribute("ip", (string) $this->spoofed_ip);
		$spoofed->setAttribute("visits", (string) 1);
		$spoofed->setAttribute("volumes", $this->volumes);
		$remote->appendChild($spoofed);
		return $spoofed;
	}

	private function addNewRecent($xmlDoc, $spoofed)
	{
		$recent = $xmlDoc->createElement("recent");
		$recent->setAttribute("time", (string) date("d.m.Y H:i:s"));
		$spoofed->appendChild($recent);
		$text = $xmlDoc->createTextNode(htmlspecialchars(SearchOptions::$searchString, ENT_NOQUOTES));
		$recent->appendChild( $text );
		return $recent;
	}

}

?>