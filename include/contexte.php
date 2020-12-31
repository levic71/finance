<?

define("_ECRAN_ACCUEIL_",	0);


class contexte
{
	

var $ecran		= _ECRAN_ACCUEIL_;
var $eventinfo		= true;
var $libelleCalendar	= true;


function getEcran()
{
	return $this->ecran;
}

function setEcran($ecran)
{
	$this->ecran = $ecran;
}

function setEcranAccueil()
{
	$this->setEcran(_ECRAN_ACCUEIL_);
}

}


$contexte = new contexte();


?>