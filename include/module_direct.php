<? $liste = JKCache::getCache("../cache/access_home.txt", 900, "_FLUX_ACCESS_"); ?>


<div id="direct" class="home_center_div">
	<div class="corps">

		<div class="accesbox">
			<label class="zone2" for="ref_champ1" id="labelTournoi">Tournoi:</label>
<?
			echo "<select class=\"mysel\" id=\"ref_champ1\" name=\"ref_champ1\" tabindex=\"5\" onchange=\"javascript:launch_access(this.value);\">";
			echo "<option value=\"0\"> &nbsp;</option>";
			foreach($liste as $c)
				if ($c['type'] == 2)
					echo "<option value=\"".$c['id']."\" ".(isset($mon_id_championnat) && $mon_id_championnat == $c['id'] ? "selected=\"selected\"" : "")."> ".htmlspecialchars($c['nom'])."</option>";
			echo "</select>";
?>
			<button class="ok" onclick="javascript:return launch_access(document.forms[0].ref_champ1.value);"><img src="../images/templates/defaut/bt_ok.gif" alt="" /></button>
			<button class="demo2" onclick="javascript:launch_access(86);"> Démo </button>
		</div>

		<div class="accesbox">
			<label class="zone2" for="ref_champ2" id="labelChampionnat">Championnat:</label>
<?
			echo "<select class=\"mysel\" id=\"ref_champ2\" name=\"ref_champ2\" tabindex=\"6\" onchange=\"javascript:launch_access(this.value);\">";
			echo "<option value=\"0\"> &nbsp;</option>";
			foreach($liste as $c)
				if ($c['type'] == 1)
					echo "<option value=\"".$c['id']."\" ".(isset($mon_id_championnat) && $mon_id_championnat == $c['id'] ? "selected=\"selected\"" : "")."> ".htmlspecialchars($c['nom'])."</option>";
			echo "</select>";
?>
			<button class="ok" onclick="javascript:return launch_access(document.forms[0].ref_champ2.value);"><img src="../images/templates/defaut/bt_ok.gif" alt="" /></button>
			<button class="demo2" onclick="javascript:launch_access(85);"> Démo </button>
		</div>

		<div class="accesbox">
			<label class="zone2" for="ref_champ3" id="labelLibre">Libre:</label>
<?
			echo "<select class=\"mysel\" id=\"ref_champ3\" name=\"ref_champ3\" tabindex=\"7\" onchange=\"javascript:launch_access(this.value);\">";
			echo "<option value=\"0\"> &nbsp; </option>";
			foreach($liste as $c)
				if ($c['type'] == 0)
					echo "<option value=\"".$c['id']."\" ".(isset($mon_id_championnat) && $mon_id_championnat == $c['id'] ? "selected=\"selected\"" : "")."> ".htmlspecialchars($c['nom'])."</option>";
			echo "</select>";
?>
			<button class="ok" onclick="javascript:return launch_access(document.forms[0].ref_champ3.value);"><img src="../images/templates/defaut/bt_ok.gif" alt="" /></button>
			<button class="demo2" onclick="javascript:launch_access(84);"> Démo </button>
		</div>


		<div class="accesbox" style="margin-top: 5px; padding-top: 10px;">
			<label class="zone2" for="search_champ" id="labelRecherche">Recherche : </label>
			<input type="text" id="search_champ" name="search_champ" accesskey="4" tabindex="8" size="30" />
		</div>

		<div class="accesbox">
			<label class="zone2" for="search_type" id="labelType">Type sport : </label>
			<select class="mysel" id="search_type" name="search_type" tabindex="9" >
				<option value="0"> Tous </option>
				<option value="1"> <?= $libelle_genre[_TS_JORKYBALL_] ?> </option>
				<option value="2"> <?= $libelle_genre[_TS_FUTSAL_]    ?> </option>
				<option value="3"> <?= $libelle_genre[_TS_FOOTBALL_]  ?> </option>
			</select>
			<button class="ok" onclick="javascript:document.forms[0].action='../www/championnat_recherche.php';document.forms[0].submit();"><img src="../images/templates/defaut/bt_ok.gif" alt="" /></button>
		</div>

	</div>
</div>
