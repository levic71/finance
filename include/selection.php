<?

class ouinon
{
	var $ouinon = array (	1 => "Non",
							2 => "Oui");

	function displayCombo($defaut = 1)
	{
		echo "<select name=\"ouinon\">";

		while(list($cle, $valeur) = each($this->ouinon))
			echo "<option value=\"$cle\" ".(($cle == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";

		echo "</select>";
	}

	function getOuiNon($valeur)
	{
		return $this->ouinon[$valeur];
	}
}

class pays
{
	var $pays = array(	"af" => "Afghanistan",
						"cf" => "Afrique du central",
						"za" => "Afrique du Sud",
						"al" => "Albanie",
						"dz" => "Alg�rie",
						"de" => "Allemagne",
						"ad" => "Andorre",
						"uk" => "Angleterre",
						"ao" => "Angola",
						"ag" => "Antigua-et-Barbuda",
						"sa" => "Arabie Saoudite",
						"ar" => "Argentine",
						"am" => "Armenie",
						"au" => "Australie",
						"at" => "Autriche",
						"az" => "Azerba�djan",
						"bs" => "Bahamas",
						"bh" => "Bahre�n",
						"bd" => "Bangladesh",
						"bb" => "Barbades",
						"be" => "Belgique",
						"bz" => "Belize",
						"bj" => "B�nin",
						"bt" => "Bhoutan",
						"by" => "Bi�lorussie",
						"bo" => "Bolivie",
						"ba" => "Bosnie-Herz�govine",
						"bw" => "Botswana",
						"br" => "Br�sil",
						"bn" => "Brun�i Darussalam",
						"bg" => "Bulgarie",
						"bf" => "Burkina-Faso",
						"bi" => "Burundi",
						"kh" => "Cambodge",
						"cm" => "Cameroun",
						"ca" => "Canada",
						"cv" => "Cap-Vert",
						"cl" => "Chili",
						"cn" => "Chine",
						"cy" => "Chypre",
						"co" => "Colombie",
						"km" => "Comores",
						"cg" => "Congo",
						"kr" => "Cor�e du Sud",
						"cr" => "Costa Rica",
						"ci" => "C�te d'Ivoire",
						"hr" => "Croatie",
						"cu" => "Cuba",
						"dk" => "Danemark",
						"dj" => "Djibouti",
						"eg" => "Egypte",
						"ae" => "Emirats arabes unis",
						"ec" => "Equateur",
						"er" => "Erythr�e",
						"es" => "Espagne",
						"ee" => "Estonie",
						"et" => "Ethiopie",
						"fj" => "Fidji",
						"fi" => "Finlande",
						"fr" => "France",
						"ga" => "Gabon",
						"gm" => "Gambie",
						"gh" => "Ghana",
						"gr" => "Gr�ce",
						"gd" => "Grenade",
						"gp" => "Guadeloupe",
						"gt" => "Guatemala",
						"gw" => "Guin�e-Bissau",
						"gq" => "Guin�e �quatoriale",
						"gy" => "Guyane",
						"ht" => "Ha�ti",
						"nl" => "Hollande",
						"hn" => "Honduras",
						"hu" => "Hongrie",
						"mu" => "Ile Maurice",
						"in" => "Inde",
						"id" => "Indon�sie",
						"iq" => "Irak",
						"ir" => "Iran",
						"ie" => "Irlande",
						"is" => "Islande",
						"il" => "Isra�l",
						"it" => "Italie",
						"jm" => "Jamaique",
						"jp" => "Japon",
						"jo" => "Jordanie",
						"kz" => "Kazakhstan",
						"ke" => "Kenya",
						"kw" => "Kowe�t",
						"la" => "Laos",
						"ls" => "Lesotho",
						"lb" => "Liban",
						"lr" => "Lib�ria",
						"ly" => "Libye",
						"lt" => "Lituanie",
						"lu" => "Luxembourg",
						"mg" => "Madagascar",
						"my" => "Malaisie",
						"mw" => "Malawi",
						"ml" => "Mali",
						"mt" => "Malte",
						"ma" => "Maroc",
						"mq" => "Martinique",
						"mr" => "Mauritanie",
						"mx" => "Mexique",
						"md" => "Moldavie",
						"mc" => "Monaco",
						"mn" => "Mongolie",
						"mz" => "Mozambique",
						"mm" => "Myanmar",
						"na" => "Namibie",
						"ni" => "Nicaragua",
						"ne" => "Niger",
						"nx" => "Northern Ireland",
						"no" => "Norv�ge",
						"nc" => "Nouvelle Cal�donie",
						"nz" => "Nouvelle Z�land",
						"pk" => "Pakistan",
						"pa" => "Panama",
						"pg" => "Papouasie - Nouvelle Guin�e",
						"py" => "Paraguay",
						"pe" => "P�rou",
						"ph" => "Philippines",
						"pl" => "Pologne",
						"pr" => "Porto Rico",
						"pt" => "Portugal",
						"qa" => "Qatar",
						"do" => "R�publique Dominicaine",
						"ro" => "Roumanie",
						"ru" => "Russie",
						"rw" => "Rwanda",
						"sv" => "Salvador",
						"st" => "Sao Tom�-et-Principe",
						"sn" => "S�n�gal",
						"sx" => "Serbie",
						"sl" => "Sierra Leone",
						"sg" => "Singapoure",
						"si" => "Slov�nie",
						"sd" => "Soudan",
						"lk" => "Sri Lanka",
						"se" => "Su�de",
						"ch" => "Suisse",
						"sr" => "Surinam",
						"sz" => "Swaziland",
						"sy" => "Syrie",
						"tj" => "Tadjikistan",
						"tw" => "Taiwan",
						"tz" => "Tanzanie",
						"td" => "Tchad",
						"cz" => "Tch�quie",
						"th" => "Tha�lande",
						"tx" => "Tibet",
						"tg" => "Togo",
						"tt" => "Trinit� et Tobago",
						"tn" => "Tunisie",
						"tm" => "Turkm�nistan",
						"tr" => "Turquie",
						"ua" => "Ukraine",
						"uy" => "Uruguay",
						"us" => "USA",
						"uz" => "Uzbekistan",
						"ve" => "V�n�zuela",
						"vn" => "Vietnam",
						"wx" => "Wales",
						"ye" => "Y�men",
						"yu" => "Yougoslavie",
						"zm" => "Zambie",
						"zw" => "Zimbabwe");
	
	function getCombo2($defaut = "France")
	{
		if ($defaut == "") $defaut = "France";
		$select = "<select name=\"mon_pays\"> ";
		while(list($cle, $valeur) = each($this->pays))
			$select .= "<option value=\"$valeur\" ".(($valeur == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";
		$select .= "</select>";
		
		return $select;
	}

	function getCombo($defaut = "fr")
	{
		$select = "<select name=\"mon_pays\"> ";
		while(list($cle, $valeur) = each($this->pays))
			$select .= "<option value=\"$cle\" ".(($cle == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";
		$select .= "</select>";
		
		return $select;
	}
	
	function displayCombo($defaut = "fr")
	{
		echo $this->getCombo($defaut);
	}

	function getPays($valeur)
	{
		return $this->pays[$valeur];
	}
}

class nationalite
{
	var $nationalite = array( "af" => "Afghan",
				"al" => "Albanaise",
				"dz" => "Alg�rienne",
				"de" => "Allemande",
				"us" => "Am�ricaine",
				"ad" => "Andorran",
				"uk" => "Anglaise",
				"ao" => "Angolaise",
				"ag" => "Antiguais et Barbudien",
				"ar" => "Argentine",
				"am" => "Armenienne",
				"au" => "Australienne",
				"at" => "Autrichienne",
				"az" => "Azerba�djanaise",
				"bs" => "Bahamien",
				"bh" => "Bahre�nien",
				"bd" => "Bangladaise",
				"bb" => "Barbadienne",
				"be" => "Belge",
				"bz" => "B�lizienne",
				"bj" => "B�ninoise",
				"bt" => "Bhotia",
				"by" => "Bi�lorusse",
				"mm" => "Birmane",
				"gw" => "Bissau-Guin�enne",
				"bo" => "Bolivienne",
				"ba" => "Bosniaque",
				"bw" => "Botswanaise",
				"br" => "Br�silienne",
				"bn" => "Brun�ien",
				"bg" => "Bulgare",
				"bf" => "Burkinab�",
				"bi" => "Burundaise",
				"kh" => "Cambodgienne",
				"cm" => "Camerounaise",
				"ca" => "Canadienne",
				"cv" => "Cap-Verdienne",
				"cf" => "Central-Africain",
				"cl" => "Chilienne",
				"cn" => "Chinoise",
				"cy" => "Chypriote",
				"co" => "Colombienne",
				"km" => "Comorienne",
				"cg" => "Congolaise",
				"kr" => "Cor�enne",
				"cr" => "Costaricaine",
				"hr" => "Croate",
				"cu" => "Cubaine",
				"dk" => "Danoise",
				"dj" => "Djiboutien",
				"do" => "Dominicaine",
				"eg" => "Egyptienne",
				"ae" => "�mirien",
				"gq" => "Equato-Guin�en",
				"ec" => "Equatorien",
				"er" => "Erythr�enne",
				"es" => "Espagnole",
				"ee" => "Estonienne",
				"et" => "Ethiopienne",
				"fj" => "Fidjienne",
				"fi" => "Finlandaise",
				"fr" => "Fran�aise",
				"ga" => "Gabonaise",
				"gm" => "Gambienne",
				"gh" => "Ghan�enne",
				"gr" => "Grecque",
				"gd" => "Grenadin",
				"gt" => "Guatemalt�que",
				"gy" => "Guyanaise",
				"ht" => "Ha�tienne",
				"nl" => "Hollandaise",
				"hn" => "Hondurienne",
				"hu" => "Hongroise",
				"in" => "Indienne",
				"id" => "Indon�sienne",
				"iq" => "Irakienne",
				"ir" => "Iranienne",
				"ix" => "Irlandaise",
				"ie" => "Irlandaise",
				"is" => "Islandaise",
				"il" => "Isra�lienne",
				"it" => "Italienne",
				"ci" => "Ivoirienne",
				"jm" => "Jamaicaine",
				"jp" => "Japonnaise",
				"jo" => "Jordanienne",
				"kz" => "Kazakh",
				"ke" => "K�nyane",
				"kw" => "Koweitienne",
				"la" => "Laotienne",
				"lb" => "Libanaise",
				"lr" => "Lib�rien",
				"ly" => "Libyenne",
				"lt" => "Lituanienne",
				"lu" => "Luxembourgeoise",
				"my" => "Malais",
				"mw" => "Malawien",
				"mg" => "Malgache",
				"ml" => "Malienne",
				"mt" => "Maltaise",
				"ma" => "Marocaine",
				"mu" => "Mauricienne",
				"mr" => "Mauritanienne",
				"mx" => "Mexicaine",
				"md" => "Moldave",
				"mc" => "Mon�gasque",
				"mn" => "Mongole",
				"mz" => "Mozambicaine",
				"na" => "Namibienne",
				"nz" => "Neo-Z�landaise",
				"ni" => "Nicaraguayenne",
				"ne" => "Nig�rienne",
				"no" => "Norv�gienne",
				"pk" => "Pakistanaise",
				"pa" => "Panam�enne",
				"pg" => "Papouan-N�o-Guin�en",
				"py" => "Paraguayenne",
				"pe" => "P�ruvienne",
				"ph" => "Philippine",
				"pl" => "Polonaise",
				"pr" => "Portoricaine",
				"pt" => "Portugaise",
				"qa" => "Qatarienne",
				"ro" => "Roumaine",
				"rw" => "Ruandaise",
				"ru" => "Russe",
				"sv" => "Salvadorienne",
				"st" => "Santom�enne",
				"sa" => "Saoudienne",
				"sn" => "S�n�galaise",
				"sx" => "Serbe",
				"sl" => "Sierra-L�onaise",
				"sg" => "Singapourien",
				"si" => "Slov�ne",
				"ls" => "Sotho",
				"sd" => "Soudanaise",
				"lk" => "Sri-lankaise",
				"za" => "Sud Africaine",
				"se" => "Su�doise",
				"ch" => "Suisse",
				"sr" => "Surinamaise",
				"sz" => "Swazi",
				"sy" => "Syrienne",
				"tj" => "Tadjik",
				"tw" => "Taiwanese",
				"tz" => "Tanzanian",
				"td" => "Tchadienne",
				"cz" => "Tch�que",
				"th" => "Tha�landaise",
				"tx" => "Tib�taine",
				"tg" => "Togolaise",
				"tt" => "Trinidadien",
				"tn" => "Tunisienne",
				"tm" => "Turkm�ne",
				"tr" => "Turque",
				"ua" => "Ukrainienne",
				"uy" => "Uruguayenne",
				"uz" => "Uzbek",
				"ve" => "V�n�zuelienne",
				"vn" => "Vietnamienne",
				"wx" => "Welsh",
				"ye" => "Y�m�nite",
				"yu" => "Yougoslave",
				"zm" => "Zambien",
				"zw" => "Zimbabw�enne");

	function displayCombo($defaut = "fr")
	{
		echo "<select name=\"ma_nationalite\">";
		
		while(list($cle, $valeur) = each($this->nationalite))
			echo "<option value=\"$cle\" ".(($cle == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";

		echo "</select>";
	}
	
	function getNationalite($valeur)
	{
		return $this->nationalite[$valeur];
	}
}

class titre
{
	var $titre = array(	"1" => "Monsieur",
						"2" => "Madame",
						"3" => "Mademoiselle",
						"4" => "Soci�t�");
	
	function displayCombo($defaut = "1")
	{
		echo "<select name=\"mon_titre\">";

		while(list($cle, $valeur) = each($this->titre))
			echo "<option value=\"$cle\" ".(($cle == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";

		echo "</select>";
	}

	function getTitre($valeur)
	{
		return $this->titre[$valeur];
	}
}

class fonction
{
	var $fonction = array(	"0" => "Salari�",
							"1" => "Int�rimaire",
							"2" => "Consultant");
	
	function displayCombo($defaut = "1")
	{
		echo "<select name=\"ma_fonction\">";

		while(list($cle, $valeur) = each($this->fonction))
			echo "<option value=\"$cle\" ".(($cle == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";

		echo "</select>";
	}

	function getFonction($valeur)
	{
		return $this->fonction[$valeur];
	}
}

class disponibilite
{
	var $disponibilite = array(	"1" => "Disponible",
								"2" => "Non disponible",
								"3" => "En mission");
	
	function displayCombo($defaut = "1")
	{
		echo "<select name=\"ma_disponibilite\">";

		while(list($cle, $valeur) = each($this->disponibilite))
			echo "<option value=\"$cle\" ".(($cle == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";

		echo "</select>";
	}

	function getDisponibilite($valeur)
	{
		return $this->disponibilite[$valeur];
	}
}

class status
{
	var $status = array("1" => "Ind�pendant",
						"2" => "Salari�",
						"3" => "Chercheur d'emploi");
	
	function displayCombo($defaut = "1")
	{
		echo "<select name=\"mon_status\">";
		
		while(list($cle, $valeur) = each($this->status))
			echo "<option value=\"$cle\" ".(($cle == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";

		echo "</select>";
	}

	function getStatus($valeur)
	{
		return $this->status[$valeur];
	}
}

class situation
{
	var $situation = array(	"1" => "C�libataire",
						"2" => "Mari�(e)");

	function displayCombo($defaut = "1")
	{
		echo "<select name=\"ma_situation\">";

		while(list($cle, $valeur) = each($this->situation))
			echo "<option value=\"$cle\" ".(($cle == $defaut) ? "selected=\"selected\"" : "")."> $valeur </option>";

		echo "</select>";
	}

	function getSituation($valeur)
	{
		return $this->situation[$valeur];
	}
}

class nb_enfant
{
	function displayCombo($defaut = 0)
	{
		echo "<select name=\"mes_bebes\">";
		
		for ($i = 0; $i < 11; $i++)
			echo "<option value=\"$i\" ".(($i == $defaut) ? "selected=\"selected\"" : "")."> $i </option>";

		echo "</select>";
	}
}

class anciennete
{
	function displayCombo($defaut = 0)
	{
		echo "<select name=\"mon_anciennete\">";

		for ($i = 0; $i < 50; $i++)
			echo "<option value=\"$i\" ".(($i == $defaut) ? "selected=\"selected\"" : "")."> $i </option>";

		echo "</select>";
	}
}

class date_generique
{
	var $mois = array(	"01" => "Janvier",
					"02" => "F�vrier",
					"03" => "Mars",
					"04" => "Avril",
					"05" => "Mai",
					"06" => "Juin",
					"07" => "Juillet",
					"08" => "Ao�t",
					"09" => "Septembre",
					"10" => "Octobre",
					"11" => "Novembre",
					"12" => "D�cembre");

	function displayCombo($def_j = "1", $def_m = "01", $def_a = "1970", $name = "ma_date")
	{
			if ($def_j == "") $def_j = "1";
			if ($def_m == "") $def_m = "01";
			if ($def_a == "" || $def_a == "0000") $def_a = "1970";

			echo "<table border=\"0\">";

			echo "<tr><td><select name=\"".$name."_jour\">";
			for ($i = 1; $i < 32; $i++)
					echo "<option value=\"$i\" ".(($i == $def_j) ? "selected=\"selected\"" : "")."> $i </option>";
			echo "</select></td></tr>";

			echo "<td><select name=\"".$name."_mois\">";
			while(list($cle, $valeur) = each($this->mois))
				echo "<option value=\"$cle\" ".(($cle == $def_m) ? "selected=\"selected\"" : "")."> $valeur </option>";
			echo "</select></td></tr>";

			echo "<td><input type=\"text\" maxlength=\"4\" size=\"4\" value=\"".$def_a."\" name=\"".$name."_annee\" /></td></tr>";

			echo "</table>";
	}
	
	function getMois($valeur)
	{
		return $this->mois[$valeur];
	}
	
	function date_de_naissance($def_j = "1", $def_m = "01", $def_a = "1970")
	{
		$this->displayCombo($def_j, $def_m, $def_a, "ma_naissance");
	}

}

function echo_date_select($def_j = "1", $def_m = "01", $def_a = "1970", $name = "ma_date")
{
	echo_date_generique_select($def_j, $def_m, $def_a, $name);
}

?>
