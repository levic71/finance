<?

// Role
define("_DEBUG_", 0);
define("_INFO_",  1);
define("_WARM_",  2);
define("_ERROR_", 3);
define("_FATAL_", 4);

// Theme
define("_THEME_CLASSIQUE_", 1);
define("_THEME_TWITTER_",   2);
define("_THEME_GREENY_",    3);
define("_THEME_MICROSOFT_", 4);
define("_THEME_BLUE_", 		5);
define("_THEME_AURORE_",    6);
define("_THEME_STADIUM_",   7);
define("_THEME_CURVE_",     8);
define("_THEME_GRUNGE_",    9);
define("_THEME_GLASS_",    10);
define("_THEME_CR7_",      11);
$libelle_theme = array(
	_THEME_CLASSIQUE_ => "Classique",
	_THEME_TWITTER_   => "Twitter",
	_THEME_GREENY_    => "Greeny",
	_THEME_MICROSOFT_ => "Microsoft",
	_THEME_BLUE_	  => "Blue",
	_THEME_AURORE_	  => "Aurore",
	_THEME_STADIUM_	  => "Stadium",
	_THEME_CURVE_	  => "Curve",
	_THEME_GRUNGE_	  => "Grunge",
	_THEME_GLASS_	  => "Glass",
	_THEME_CR7_  	  => "CR7"
);



// Medaille
define("_NO_MEDAILLE_",     0);
define("_GOLD_MEDAILLE_",   1);
define("_SILVER_MEDAILLE_", 2);
define("_BRONZE_MEDAILLE_", 3);
$libelle_medaille = array(
	_NO_MEDAILLE_     => "",
	_GOLD_MEDAILLE_   => "gold",
	_SILVER_MEDAILLE_ => "silver",
	_BRONZE_MEDAILLE_ => "bronze"
);


// Partenaires
$i = 1;
$partenaire[$i]['lat']     = "48.779963";
$partenaire[$i]['lng']     = "2.424288";
$partenaire[$i]['nom']     = "Foot 2x2 d'Alfortville";
$partenaire[$i]['ville']   = "Alfortville";
$partenaire[$i]['cp']      = "94140";
$partenaire[$i]['adresse'] = "ZAC du Val de Seine - Zone Techniparc";
$partenaire[$i]['tel']     = "01.48.93.03.19";
$partenaire[$i]['email']   = "jorkyball94@freesurf.fr";
$partenaire[$i]['web']     = "http://www.jorkyball94.com";
$partenaire[$i]['icon']    = "icon_ballon";
$partenaire[$i]['plan']    = "../images/partenaires/plan_j94.gif";
$partenaire[$i]['config']  = "../images/partenaires/pixelart_terrain_alfortvill.jpg";


$i++;
$partenaire[$i]['lat']     = "48.801546";
$partenaire[$i]['lng']     = "2.292833";
$partenaire[$i]['nom']     = "Foot 2x2 de Chatillon";
$partenaire[$i]['ville']   = "Chatillon";
$partenaire[$i]['cp']      = "92320";
$partenaire[$i]['adresse'] = "51, Bd de la Liberte / rue Louveau";
$partenaire[$i]['tel']     = "01.40.84.01.01";
$partenaire[$i]['email']   = "jorkyball92@freesurf.fr";
$partenaire[$i]['web']     = "http://www.jorkyball92.com";
$partenaire[$i]['icon']    = "icon_ballon";
$partenaire[$i]['plan']    = "../images/partenaires/plan_j92.jpg";
$partenaire[$i]['config']  = "../images/partenaires/pixelart_terrain_chatillon.jpg";

// Role
define("_ROLE_ANONYMOUS_", 0);
define("_ROLE_ADMIN_",     1);
define("_ROLE_DEPUTY_",    2);
$libelle_role = array(
	_ROLE_ANONYMOUS_ => "Anonyme",
	_ROLE_ADMIN_     => "Administrateur",
	_ROLE_DEPUTY_    => "Adjoint"
);

// Genre de championnat
define("_TS_JORKYBALL_",    1);
define("_TS_FUTSAL_",       2);
define("_TS_FOOTBALL_",     3);
define("_TS_BASKET_",       4);
define("_TS_HANDBALL_",     5);
define("_TS_VOLLEYBALL_",   6);
define("_TS_RUGBY_",        7);
define("_TS_BABYFOOT_",     8);
define("_TS_TENNIS_",       9);
define("_TS_PINGPONG_",     10);
define("_TS_PETANQUE_",     11);
define("_TS_PES_",          12);
define("_TS_AUTRE_",        0);
define("_TS_FOOTBALL_US_",  13);
define("_TS_BASEBALL_",     14);
define("_TS_BEACH_SOCCER_", 15);
define("_TS_BOXING_",       16);
define("_TS_CURLING_",      17);
define("_TS_SNOOKER_",      18);
$libelle_genre = array(
	_TS_AUTRE_ => "Autre",
	_TS_BABYFOOT_ => "Babyfoot",
	_TS_BASEBALL_ => "Baseball",
	_TS_BASKET_ => "Basket",
	_TS_BEACH_SOCCER_ => "Beach",
	_TS_BOXING_ => "Boxing",
	_TS_CURLING_ => "Curling",
	_TS_HANDBALL_ => "Handball",
	_TS_FOOTBALL_ => "Football",
	_TS_FOOTBALL_US_ => "Football US",
	_TS_JORKYBALL_ => "Foot 2x2",
	_TS_FUTSAL_ => "Futsal",
	_TS_PETANQUE_ => "P�tanque",
	_TS_PES_ => "PES",
	_TS_PINGPONG_ => "Ping pong",
	_TS_SNOOKER_ => "Snooker",
	_TS_TENNIS_ => "Tennis",
	_TS_RUGBY_ => "Rugby",
	_TS_VOLLEYBALL_ => "Volleyball"
);

$icon_genre = array(
	_TS_AUTRE_ => "all_sports_icon.png",
	_TS_BABYFOOT_ => "icon_foosball.png",
	_TS_BASEBALL_ => "icon_baseball.png",
	_TS_BASKET_ => "icon_basketball.png",
	_TS_BEACH_SOCCER_ => "icon_beach_soccer.png",
	_TS_BOXING_ => "icon_boxing.png",
	_TS_CURLING_ => "icon_curling.png",
	_TS_HANDBALL_ => "icon_handball.png",
	_TS_FOOTBALL_ => "icon_soccer.png",
	_TS_FOOTBALL_US_ => "icon_american_football.png",
	_TS_JORKYBALL_ => "icon_jorky.png",
	_TS_FUTSAL_ => "icon_jorky.png",
	_TS_PETANQUE_ => "icon_petanque.png",
	_TS_PES_ => "icon_pes.png",
	_TS_PINGPONG_ => "icon_table_tennis.png",
	_TS_SNOOKER_ => "icon_snooker.png",
	_TS_TENNIS_ => "icon_tennis.png",
	_TS_RUGBY_ => "icon_rugby.png",
	_TS_VOLLEYBALL_ => "icon_volleyball.png"
);

// Genre de championnat
define("_TRACK_ACCES_HOME_", 0);
define("_TRACK_ADMIN_",      1);
define("_TRACK_PARTENAIRE_", 2);
define("_TRACK_EXPORT_",     3);
define("_TRACK_SONDAGE_",    4);
define("_TRACK_ACTUALITE_",  5);
define("_TRACK_AFFICHE_",    6);
define("_TRACK_PDF_",    7);
$libelle_track = array(_TRACK_PDF_ => "PDF", _TRACK_AFFICHE_ => "Affiche", _TRACK_SONDAGE_ => "Sondage", _TRACK_ACTUALITE_ => "Actualit�s", _TRACK_ACCES_HOME_ => "Acc�s home", _TRACK_ADMIN_ => "Admin", _TRACK_PARTENAIRE_ => "Partenaire", _TRACK_EXPORT_ => "Export");

// Phase finale pour un tournoi
define("_PHASE_PLAYOFF_",		2048);
define("_PHASE_CONSOLANTE1_",	1024);
define("_PHASE_CONSOLANTE2_",	512);
define("_PHASE_FINALE_32_",		32);
define("_PHASE_FINALE_16_",		16);
define("_PHASE_FINALE_8_",		8);
define("_PHASE_FINALE_4_",		4);
define("_PHASE_FINALE_2_",		2);
define("_PHASE_FINALE_",		1);
$libelle_phase_finale = array(_PHASE_PLAYOFF_ => "Phase finale", _PHASE_CONSOLANTE1_ => "Matchs de classement/Barrage", _PHASE_CONSOLANTE2_ => "Consolante", _PHASE_FINALE_32_ => "32 i�me", _PHASE_FINALE_16_ => "16 i�me", _PHASE_FINALE_8_ => "8 i�me", _PHASE_FINALE_4_ => "Quart", _PHASE_FINALE_2_ => "Demi", _PHASE_FINALE_ => "Finale");

// Etat d'un joueur
define("_ETAT_JOUEUR_ACTIF_",		0);
define("_ETAT_JOUEUR_BLESSE_",		1);
define("_ETAT_JOUEUR_VACANCES_",	2);
$libelle_etat_joueur = array(_ETAT_JOUEUR_ACTIF_ => "Actif", _ETAT_JOUEUR_BLESSE_ => "Bless�", _ETAT_JOUEUR_VACANCES_ => "En vacances");

// Type Championnat
define ("_TYPE_LIBRE_",             0);
define ("_TYPE_CHAMPIONNAT_",       1);
define ("_TYPE_TOURNOI_",           2);
$libelle_type = array(_TYPE_LIBRE_ => "Championnat Libre", _TYPE_CHAMPIONNAT_ => "Championnat", _TYPE_TOURNOI_ => "Tournoi");
$icon_type = array(_TYPE_LIBRE_ => "icon_libre.gif", _TYPE_CHAMPIONNAT_ => "icon_champ.gif", _TYPE_TOURNOI_ => "icon_tournoi.gif");

// Mode de visualisation des journ�es
define ("_VISU_JOURNEE_CALENDRIER_",    0);
define ("_VISU_JOURNEE_LISTE_",       	1);
$libelle_visu_journee = array(_VISU_JOURNEE_CALENDRIER_ => "Calendrier", _VISU_JOURNEE_LISTE_ => "Liste");

// Type lieu de pratique
define ("_LIEU_VILLE_",      0);
define ("_LIEU_PAYS_",       1);
define ("_LIEU_CONTINENT_",  2);
$libelle_typelieu = array(_LIEU_VILLE_ => "Ville", _LIEU_PAYS_ => "Pays", _LIEU_CONTINENT_ => "Continent");

// Liste des continents
define("_CONTINENT_ALL_",       0);
define("_CONTINENT_AFRIQUE_",   1);
define("_CONTINENT_AMERIQUES_", 2);
define("_CONTINENT_ASIE_",      3);
define("_CONTINENT_EUROPE_",    4);
define("_CONTINENT_OCEANIE_",   5);
$libelle_continent = array(_CONTINENT_ALL_ => "International", _CONTINENT_AFRIQUE_ => "Afrique", _CONTINENT_AMERIQUES_ => "Am�riques", _CONTINENT_ASIE_ => "Asie", _CONTINENT_EUROPE_ => "Europe", _CONTINENT_OCEANIE_ => "Oc�anie");

// Format d'affichage des pages
define ("_XDISPLAY_ALL_",			0);
define ("_XDISPLAY_FREE_",			1);
define ("_XDISPLAY_CHAMPIONNAT_",	2);
define ("_XDISPLAY_TOURNOI_",		3);

// $smileys

$smileys[] = "icon1.gif";
$smileys[] = "icon10.gif";
$smileys[] = "icon11.gif";
$smileys[] = "icon12.gif";
$smileys[] = "icon13.gif";
$smileys[] = "icon14.gif";
$smileys[] = "icon2.gif";
$smileys[] = "icon3.gif";
$smileys[] = "icon4.gif";
$smileys[] = "icon5.gif";
$smileys[] = "icon6.gif";
$smileys[] = "icon7.gif";
$smileys[] = "icon8.gif";
$smileys[] = "icon9.gif";
$smileys[] = "biggrin.gif";
$smileys[] = "blink.gif";
$smileys[] = "dry.gif";
$smileys[] = "ohmy.gif";
$smileys[] = "ph34r.gif";
$smileys[] = "sleep.gif";
$smileys[] = "rolleyes.gif";
$smileys[] = "sad.gif";
$smileys[] = "happy.gif";
$smileys[] = "huh.gif";
$smileys[] = "nav.gif";
$smileys[] = "smile.gif";
$smileys[] = "tongue.gif";
$smileys[] = "unsure.gif";
$smileys[] = "wacko.gif";
$smileys[] = "wink.gif";
$smileys[] = "wub.gif";
$smileys[] = "mad.gif";
$smileys[] = "mellow.gif";
$smileys[] = "215.GIF";
$smileys[] = "3d_045.gif";
$smileys[] = "argh.jpg";
$smileys[] = "Arrgh.jpg";
$smileys[] = "bizzz.jpg";
$smileys[] = "CA23KLIR.png";
$smileys[] = "CA2L69M3.png";
$smileys[] = "CA4BFXZE.png";
$smileys[] = "CA4CHOBM.png";
$smileys[] = "CA6B0T6V.png";
$smileys[] = "CA8FG9CF.png";
$smileys[] = "CAUJIBA3.png";
$smileys[] = "chuuuuut.JPG";
$smileys[] = "dont_tell_smile.gif";
$smileys[] = "Euh_ORIGINAL.jpg";
$smileys[] = "fauxcul.jpg";
$smileys[] = "fingerscrossed.gif";
$smileys[] = "Flirt.jpg";
$smileys[] = "HangLose.jpg";
$smileys[] = "noel.gif";
$smileys[] = "Ok.jpg";
$smileys[] = "Pinky_Piggy.jpg";
$smileys[] = "Rambo.jpg";
$smileys[] = "sbAmoureux7.gif";
$smileys[] = "sbEtonne7.gif";
$smileys[] = "Schnauze!.gif";
$smileys[] = "scream.jpg";
$smileys[] = "shhh.jpg";
$smileys[] = "smiley_270.gif";
$smileys[] = "smiley_274.gif";
$smileys[] = "Tire_la_langue.jpg";
$smileys[] = "yeah!.jpg";
$smileys[] = "trophe1.gif";
$smileys[] = "trophe2.gif";

// Buddys1
$buddys1[] = "42414.gif";
$buddys1[] = "42415.gif";
$buddys1[] = "42496.gif";
$buddys1[] = "42511.gif";
$buddys1[] = "42631.gif";
$buddys1[] = "42650.gif";
$buddys1[] = "42672.gif";
$buddys1[] = "42795.gif";
$buddys1[] = "42796.gif";
$buddys1[] = "42970.gif";
$buddys1[] = "42997.gif";
$buddys1[] = "43012.gif";
$buddys1[] = "43016.gif";
$buddys1[] = "43083.gif";
$buddys1[] = "43095.gif";
$buddys1[] = "43107.gif";
$buddys1[] = "43138.gif";
$buddys1[] = "43139.gif";
$buddys1[] = "43140.gif";
$buddys1[] = "43149.gif";
$buddys1[] = "43178.gif";
$buddys1[] = "43182.gif";
$buddys1[] = "43201.gif";
$buddys1[] = "43229.gif";
$buddys1[] = "43478.gif";
$buddys1[] = "43505.gif";
$buddys1[] = "43511.gif";
$buddys1[] = "43545.gif";
$buddys1[] = "43546.gif";
$buddys1[] = "43734.gif";
$buddys1[] = "43794.gif";
$buddys1[] = "43813.gif";
$buddys1[] = "43858.gif";
$buddys1[] = "43880.gif";
$buddys1[] = "45131.gif";
$buddys1[] = "45132.gif";
$buddys1[] = "45136.gif";
$buddys1[] = "45141.gif";
$buddys1[] = "45142.gif";
$buddys1[] = "45143.gif";
$buddys1[] = "45144.gif";
$buddys1[] = "45145.gif";
$buddys1[] = "45146.gif";
$buddys1[] = "45147.gif";
$buddys1[] = "45148.gif";
$buddys1[] = "45149.gif";
$buddys1[] = "45152.gif";
$buddys1[] = "45153.gif";
$buddys1[] = "45155.gif";
$buddys1[] = "45160.gif";
$buddys1[] = "45163.gif";
$buddys1[] = "45165.gif";
$buddys1[] = "45166.gif";
$buddys1[] = "45168.gif";
$buddys1[] = "45182.gif";
$buddys1[] = "45184.gif";
$buddys1[] = "45185.gif";
$buddys1[] = "45187.gif";
$buddys1[] = "45188.gif";
$buddys1[] = "45190.gif";
$buddys1[] = "45191.gif";
$buddys1[] = "45192.gif";
$buddys1[] = "45194.gif";
$buddys1[] = "45196.gif";
$buddys1[] = "45198.gif";
$buddys1[] = "45231.gif";
$buddys1[] = "45252.gif";
$buddys1[] = "45266.gif";
$buddys1[] = "45267.gif";
$buddys1[] = "45296.gif";
$buddys1[] = "45297.gif";
$buddys1[] = "45316.gif";
$buddys1[] = "45318.gif";
$buddys1[] = "45628.gif";
$buddys1[] = "45675.gif";
$buddys1[] = "45691.gif";
$buddys1[] = "45725.gif";
$buddys1[] = "46014.gif";
$buddys1[] = "46031.gif";
$buddys1[] = "46032.gif";
$buddys1[] = "46144.gif";
$buddys1[] = "46197.gif";
$buddys1[] = "46258.gif";
$buddys1[] = "46324.gif";
$buddys1[] = "46336.gif";
$buddys1[] = "46337.gif";
$buddys1[] = "46651.gif";
$buddys1[] = "47164.gif";
$buddys1[] = "47206.gif";
$buddys1[] = "47507.gif";
$buddys1[] = "47602.gif";
$buddys1[] = "47626.gif";
$buddys1[] = "47850.gif";
$buddys1[] = "47851.gif";
$buddys1[] = "47857.gif";
$buddys1[] = "47998.gif";
$buddys1[] = "47999.gif";
$buddys1[] = "48000.gif";
$buddys1[] = "49982.gif";
$buddys1[] = "50130.gif";
$buddys1[] = "50725.gif";
$buddys1[] = "50734.gif";
$buddys1[] = "52856.gif";
$buddys1[] = "53538.gif";
$buddys1[] = "55703.gif";
$buddys1[] = "58141.gif";
$buddys1[] = "58171.gif";
$buddys1[] = "58184.gif";
$buddys1[] = "58190.gif";
$buddys1[] = "arson.gif";
$buddys1[] = "artbell.gif";
$buddys1[] = "buddykart.gif";
$buddys1[] = "com-link.gif";
$buddys1[] = "darthvader.gif";
$buddys1[] = "fastbuddy.gif";
$buddys1[] = "gotmilk.gif";
$buddys1[] = "perfect.gif";
$buddys1[] = "slipnslide.gif";

// Buddys2
$buddys2[]="IKON01c216b594a2ceab28d8d1447c350e8da8872dc382.gif";
$buddys2[]="IKON01f649ba3dee74d8a233774b586b51ce9441da527d.gif";
$buddys2[]="IKON02cae84d9f68b0a6fba1948e5465823a3496662c6e.gif";
$buddys2[]="IKON034bdc8040a62cc43a53efcc18ddd8dacdf2acf10d.gif";
$buddys2[]="IKON043267e1190f8c3ccecc7753b9ac3245a446504a8d.gif";
$buddys2[]="IKON044dac6e555c2e971527a706172d5a1cfcba241d7b.gif";
$buddys2[]="IKON07a622b9f9e096a7d7b60a03e9184cb6832faff4a7.gif";
$buddys2[]="IKON090fc5a090ceba42a9b149eda627dacb77071b63ad.gif";
$buddys2[]="IKON09ade8878e82af4b21046ffedd8324e1f1c2f49d88.gif";
$buddys2[]="IKON0a16c3c4d768a4be45ded0f2ec9d1918039bcbeca6.gif";
$buddys2[]="IKON0b2b4eef84ec1f67221ec4716dc31cb8450fe0ba33.gif";
$buddys2[]="IKON0b63e7b531621b2b503aa1a2dc257cf51e22542fea.gif";
$buddys2[]="IKON0c042599f5f40f3d2eee955a885e127b48057f2e76.gif";
$buddys2[]="IKON0ec435b12b9b8c60ff6e1cf374c1514fbebfd08dc1.gif";
$buddys2[]="IKON0ed9afa4b0211924c9b9b60fce0ac180f323f0dd0a.gif";
$buddys2[]="IKON102af9734b6f062e8f6e998eaae4c11b2ae1534819.gif";
$buddys2[]="IKON115494a7071d6787de758957c0602886e9064b0aaf.gif";
$buddys2[]="IKON1201a871a054f74d07b23123cb2cbf5a08e47f3b65.gif";
$buddys2[]="IKON147f824fe4bedd5b3804426b1a417346b5b2755596.gif";
$buddys2[]="IKON15f691b9b09d3be6b9bb3d3d30989cfbbb4156b600.gif";
$buddys2[]="IKON164d0623e21539649af36a61e40a7af4632b1e3e8c.gif";
$buddys2[]="IKON1742d5debf3f68185c473164c6e8ad5bad1bbc7ef5.gif";
$buddys2[]="IKON18501b8eb2dd2d5270e61b79d30238aa9cd6a34b07.gif";
$buddys2[]="IKON1d603abd78c3720c11c85812bcaf0bc681896927d4.gif";
$buddys2[]="IKON21b3e77de9433e58984761c2a3e3d7192c53c10298.gif";
$buddys2[]="IKON238f6816c22ca82c7b33f69c8886ddb51ea557f533.gif";
$buddys2[]="IKON2420dec4c433c4e4bdc0ab941f76d7434cf3c43eba.gif";
$buddys2[]="IKON256da04d3671ef7b71b6d4bc9cdc92ae18b231c8aa.gif";
$buddys2[]="IKON27099f8b2ded41bce116520c41230bc785af7ecf7e.gif";
$buddys2[]="IKON27ea2e233c1d27db63db99783ef9d0e41e06e288d4.gif";
$buddys2[]="IKON2a99294e1f22a5d4cb4f67d8a8a1dbb82b87b8a626.gif";
$buddys2[]="IKON2cc0a7d47aec93d660b839d12f357aa82dbbfcec58.gif";
$buddys2[]="IKON2cc6157c6710521388768cbbe5fb4b2dca6e12f240.gif";
$buddys2[]="IKON2d01208fb2addd272638ae6b5990002a404b96eaf6.gif";
$buddys2[]="IKON2d0500734168c08468f568dc2ed9745ce6d37b3acf.gif";
$buddys2[]="IKON2dd84ddccf2be3981c19fb3d45b710d9c2bcf23c2b.gif";
$buddys2[]="IKON30073d99a2a959a14a27a4b50ee91d94e87d6c2b6e.gif";
$buddys2[]="IKON301d1e5ee2089d7efa72e154a57c1073b07e7c97bf.gif";
$buddys2[]="IKON3475163a4dd670562d32c40bf1be41eb9f9ea94dd2.gif";
$buddys2[]="IKON3a13edf8e037ad665661cab875b9b5395b353f1ef1.gif";
$buddys2[]="IKON3a46900faa2488bdeb42f87f53429785349a4ba2ac.gif";
$buddys2[]="IKON3e0952141db9df0a6cfe9826e2ad52c76000086657.gif";
$buddys2[]="IKON3e5dc164fe6abc4f91eb4c9f8d20f1cc710505f5d8.gif";
$buddys2[]="IKON3e96856a3915d9fb8271ecd23cb5d4cb0a0ce03688.gif";
$buddys2[]="IKON3f93ec940a7614d4c1a0c0bd0c78accb2446e8d776.gif";
$buddys2[]="IKON3fffe5d8987be2bdcde2498a4098cef372d8a3f85e.gif";
$buddys2[]="IKON41185eeddf4b8db03fa9ff4d41d67a81da774a0399.gif";
$buddys2[]="IKON43444bf1317ea8d56fb68bc7d9f750bed9aeabb654.gif";
$buddys2[]="IKON4447405a62b0d2669e4b73dd0b2ab8227092b09332.gif";
$buddys2[]="IKON45f9bb7bc8035336a6c7d6bd3fceabda334fd1e51d.gif";
$buddys2[]="IKON4a870239cc8ec8bf66f615b73d588ab2f2830c96cc.gif";
$buddys2[]="IKON4ab5ba760f14f45c16aae902bf907c980ed6d4938f.gif";
$buddys2[]="IKON4bebe1f9919a34bfa82c22162d1f0076aac14d768e.gif";
$buddys2[]="IKON4e00399569fe6d3ebb1e72b8ef6c39c90291ba1f81.gif";
$buddys2[]="IKON4e35dade176851afe6348258374776255993912386.gif";
$buddys2[]="IKON4ea679983989f51d5c243d66c78cb8e796170ff620.gif";
$buddys2[]="IKON4f07350448099faf2e0bb70fdbc022bc356d33e9d3.gif";
$buddys2[]="IKON4f8d0c36c5f7131e1876b2afc9cbfa4520d44037f5.gif";
$buddys2[]="IKON4f9b578103d330a21892037370dc8b57fc9020898f.gif";
$buddys2[]="IKON5071314dacd5613d0c43208489aa4dfbc11895097c.gif";
$buddys2[]="IKON50a409474f55bef783c4b9ececa8c5434b68ec44ac.gif";
$buddys2[]="IKON510ecae742ea641960fd170cc1c84977c0123f8cbd.gif";
$buddys2[]="IKON515908d82498bb4b23cc42f8655f9ebad66ae5b631.gif";
$buddys2[]="IKON53847ff420b92bb7d2e59b9db453bd3321144646c7.gif";
$buddys2[]="IKON53c4455591c05e8b92d252556599d98cdd4806ca0a.gif";
$buddys2[]="IKON54270210506ddc633df80c63d6df5cac4afe32d1ae.gif";
$buddys2[]="IKON577fb39db8440d392237d3aebe7227ba02850c9a1d.gif";
$buddys2[]="IKON57c603ab32644ab4014939156e954216290fdd30c5.gif";
$buddys2[]="IKON57f13c1d2a2ce3ddc7548954e54b4dc60b60efbf22.gif";
$buddys2[]="IKON58293fd120605213f39853edbc81390fb9606e0e46.gif";
$buddys2[]="IKON5a0f8270f4ed45d5cd9ca320b23c9a460043170af3.gif";
$buddys2[]="IKON5b17a1c3bc1a8935bdc0e29c049f1401223f5f77f9.gif";
$buddys2[]="IKON5d1e1b0fdc10bea8c0a696f201a4e250bb1ea79f6d.gif";
$buddys2[]="IKON5d3c498ba2a21e160f1274bf09dee4f47ef77e38a4.gif";
$buddys2[]="IKON5f29272e9f21a16d4abf429aa7c432797be4ad1329.gif";
$buddys2[]="IKON616cb878dcac524299956b52c2b26c4373efc87c16.gif";
$buddys2[]="IKON61cb5932ca9bb1e9b9df8177c24c701a731addbd71.gif";
$buddys2[]="IKON6236821d74a83dee2573a2db23ca7ca92d56d91768.gif";
$buddys2[]="IKON62e3c3db3984d94fda9a4e1847fec16136404298ca.gif";
$buddys2[]="IKON666f2f7168fa80edf589d1a404ea03289590e4df4e.gif";
$buddys2[]="IKON667e89263f1440800bbc27ade62b1758001274e84a.gif";
$buddys2[]="IKON66d9fdc212176b30e5cb72f80382d3c5c58397bbb3.gif";
$buddys2[]="IKON6a61da4cda69feae8f47d925301cd743d750a7efc3.gif";
$buddys2[]="IKON6c7d44ce812869fbfd4935902979e586a73fb5fa05.gif";
$buddys2[]="IKON6e4085cb4f32398b2bba30b7a9f15d7d52e3fe7531.gif";
$buddys2[]="IKON7029b959b9b7e241516104159447e6d14f8b837c41.gif";
$buddys2[]="IKON7081db29b78fe43d1d5344c98f93c4492bc0f46002.gif";
$buddys2[]="IKON729ee62a232b2de25cd50df1095262da3e2c7e0d7c.gif";
$buddys2[]="IKON73434c74502f6443e0667c6100673dfd63cb7865dd.gif";
$buddys2[]="IKON73b10697e8e8e12f25b72c870f63b78213810ac0fc.gif";
$buddys2[]="IKON73ff53183a203cd17f34aa5be3b2bd0486a31edb4f.gif";
$buddys2[]="IKON751804c70ee699181027304615ce137736faa5bc07.gif";
$buddys2[]="IKON76456ea10478043468a1ca08e0246e59720678650d.gif";
$buddys2[]="IKON79d11bc88930af3f03d57fdd7e30e700fa42835446.gif";
$buddys2[]="IKON7bb5ace906df1ac31381b17bd0f1dcdcbcea19bfdb.gif";
$buddys2[]="IKON7bfafbeede3ecf8e5237de53157412d89718137225.gif";
$buddys2[]="IKON7dc2d2d1b5c31e9eeb44529bb37108b19087b98e66.gif";
$buddys2[]="IKON7e8d00f72cdab76465c80458c4eab354b88612afb7.gif";
$buddys2[]="IKON7f098e33043afc15b1f4ffc0c1bc1e190fcc59d0cc.gif";
$buddys2[]="IKON8260f56a7836345b49b7953a53c2934a02139813cd.gif";
$buddys2[]="IKON8267b1a07564dc17447b924249ee9d6c39b0c24d0a.gif";
$buddys2[]="IKON826ca14d13f65520520bd337e135a79cb8c031fdf1.gif";

// Buddys3
$buddys3[]="IKON8274c3f1a1ec1c6e9ca03a62ca36556cef58cccff0.gif";
$buddys3[]="IKON84fa2def6b4cbce5b1494943e100a2e99d1d805c1a.gif";
$buddys3[]="IKON8552f761fe50734b804f06bd930f2ff1439e2c5a3f.gif";
$buddys3[]="IKON86416584c3feff5c98d5a6e23f90b8423eb8e69082.gif";
$buddys3[]="IKON86e7c1e8c83087d221defeaf4c2bde68a9465791b3.gif";
$buddys3[]="IKON89964c9907fd7ac193c9573ced4ef5ba1bab99cef2.gif";
$buddys3[]="IKON8cb075f87c17137ec8b5b7e123e5b1bf983699a281.gif";
$buddys3[]="IKON8e2be6d13d797bd40ecb4a5da89e32dcf434a1ab15.gif";
$buddys3[]="IKON8e7b06fa974db9fb3ed6676bd877893477e9ca0e15.gif";
$buddys3[]="IKON8f3f7f7abb55516e00599ca13dc9a524c078806f82.gif";
$buddys3[]="IKON906c434b1c4b71d9da562bd6b8693b36aa34f02085.gif";
$buddys3[]="IKON945a3c8337afdeae968cd484b16c2a793fd183de52.gif";
$buddys3[]="IKON972e4c600708de91445bf74368c6fceae41065285b.gif";
$buddys3[]="IKON9749a0409aca7c2fedf365c919570299174569a3e3.gif";
$buddys3[]="IKON9790f5e6bdc15d5512785445222b77eea33450f824.gif";
$buddys3[]="IKON99262a44bc95e2b99ac691963fa9d8338c58e678c0.gif";
$buddys3[]="IKON9974d3f2f4cea87b47ce5510c2e685d3d5730e13db.gif";
$buddys3[]="IKON998cc832aa628fa29c459adc4d36ee79387288f224.gif";
$buddys3[]="IKON9b41fadc86087a68b4ca657e64b77ce4aa3f28af04.gif";
$buddys3[]="IKON9bab790fd854e172a55272b33c1ac2d7324514341b.gif";
$buddys3[]="IKON9d9a3dc14566ad74b876664710aa15b42b4ea8efbf.gif";
$buddys3[]="IKON9fad0b76c81d5f7b8bf9264d2863c6f059fd3ae4aa.gif";
$buddys3[]="IKONa0d6780e74ad6cfacbd2599b559e3c4a61ba20ef75.gif";
$buddys3[]="IKONa136e9856dd4cf0aecbdf59da6b1c9420d6c535469.gif";
$buddys3[]="IKONa240e0e7b414937e0813e33128188f2b1c1fe96167.gif";
$buddys3[]="IKONa2c35e67730c2485e36653414048e64afe6e68e23f.gif";
$buddys3[]="IKONa2df453298245d5ac74c092f4a9465b35ebdd106bc.gif";
$buddys3[]="IKONa3455edd24e59457c158748a9f3205e058a0a5c865.gif";
$buddys3[]="IKONa76d932c9a7a97593fce05dea9e6c5cc7afccc91cf.gif";
$buddys3[]="IKONa88fcebced743eec8866f76225d725743058b6056d.gif";
$buddys3[]="IKONa9bf15492c249b47ad866498f7061e747277322854.gif";
$buddys3[]="IKONaab378956188ac1758892e4b896e1b68bc56c33a91.gif";
$buddys3[]="IKONabb4ac098163e2a954785542bcef4f82c56a1f1b79.gif";
$buddys3[]="IKONacc8fbac6990859e96b6ad078facb9152a76bfa45b.gif";
$buddys3[]="IKONae379b56d64f86a1e691d0522a8b6d3da4c940e97f.gif";
$buddys3[]="IKONb06b99485afea51b21568844b52c09eb1d162d8e95.gif";
$buddys3[]="IKONb16821ee171f5b5edb4666cf6ec7e13159c4d95afb.gif";
$buddys3[]="IKONb303e68c641490986ce51c9c1c0b586659ae6af586.gif";
$buddys3[]="IKONb3cffe956efd8ed5ce953ac3e916b8cf83aa1b5cc6.gif";
$buddys3[]="IKONb4bfbaa05c7e9488f7915eb682f200099b0e068b01.gif";
$buddys3[]="IKONb76ded9c887999662ccbcc5b3af56b42d69572f4cc.gif";
$buddys3[]="IKONb8d2629d8f6af69f2034338dfe2487ce454b535cae.gif";
$buddys3[]="IKONbca1cea55fa0ed8a090a7c1955c8a614f586b6456f.gif";
$buddys3[]="IKONbca98dcb08a6dde3aa64c762aa216ec44015d37594.gif";
$buddys3[]="IKONbd88dec694dbd85792bcb4631f92c845941c330595.gif";
$buddys3[]="IKONbe417f5f0179f0abc3f9866497bcaa817c32f7dccd.gif";
$buddys3[]="IKONc047ed73f054c49a9bf7a7d8519abca314c0f37d95.gif";
$buddys3[]="IKONc23e5ff958de14f53e34e4b4aa2f4f2faf240fc537.gif";
$buddys3[]="IKONc284f05978d5df2df1e415b1d64ced48c2fbc509ec.gif";
$buddys3[]="IKONc33d08838f085a7f66157eb322f7057132e0c2d369.gif";
$buddys3[]="IKONc46f6d0d2b1c01dc15848c1b08f58793e4b655bf94.gif";
$buddys3[]="IKONc59409ee05d5c17f8317309d89135872a086f6e80b.gif";
$buddys3[]="IKONc61796c03bbe797c4ee06213b8f9e72d4244c055e6.gif";
$buddys3[]="IKONc62ce608ecdb573af824a8c784347d852dd96bf7a3.gif";
$buddys3[]="IKONc69d765b09369c5cdc0367c11300fc81b3aec5de37.gif";
$buddys3[]="IKONc6c0d104a175a970edc915f951d4d67af3857e48e9.gif";
$buddys3[]="IKONca04fcafe742e365479a3f286e9a582dddff873618.gif";
$buddys3[]="IKONcad991b019229932ff514dbe8698bc74ad2f6109ac.gif";
$buddys3[]="IKONcbbe6a4ce64834634b89e47536f8aa0db4de151db8.gif";
$buddys3[]="IKONcd8859b6892be49691310007bd67d823d2ed61355e.gif";
$buddys3[]="IKONcdc83857223e0328704dda2c47b472959af647e101.gif";
$buddys3[]="IKONcdd1394c66e5115cd2b32a744c927b5ed3f4900ac5.gif";
$buddys3[]="IKONce6275eddadb3b803a91e28b758af61cc41b0356e9.gif";
$buddys3[]="IKONce74812d2b0f03795f56bc12c8962a01e14cd375d2.gif";
$buddys3[]="IKONcfde0012f18398ac5e56baaea1e786803bff3c837b.gif";
$buddys3[]="IKONd1a0cf61389586ba606c3f1443471acc8ed5027bbc.gif";
$buddys3[]="IKONd2bde30df53e588bccb2c6db62580c684c89c7238d.gif";
$buddys3[]="IKONd4d2a1400648a6c97e57abacabafe8d27cf77d87fd.gif";
$buddys3[]="IKONd61bb743d0d913b9fa7dc2972d29be0503309b2326.gif";
$buddys3[]="IKONd6953aba369ecfaf0fc0a9b8d04a9f8b5f9dd2d9e6.gif";
$buddys3[]="IKONd6d52f08d19fc1f25388c10d3e3cb55f9e30a7e59a.gif";
$buddys3[]="IKONd8f7d17ae789503d7f44ad616596bed009be0888db.gif";
$buddys3[]="IKONda9bb73550b1ca424ded8e738db099e237eba5ca1b.gif";
$buddys3[]="IKONdd970f7d5a5e38eab5aa3ae5ab3a760fc8d28d4709.gif";
$buddys3[]="IKONdf0a566833e83dfe939e23e92539b54b6befbaac74.gif";
$buddys3[]="IKONe09f78d6327010ea96ceaca6f9ce2c10e8b635e66f.gif";
$buddys3[]="IKONe0ba1d79d812a629199f6a3e76b1702fd2ba6fc14b.gif";
$buddys3[]="IKONe3bdc2e92ca6ba4a2d50c4b01d0e70f0edba572b45.gif";
$buddys3[]="IKONe4b25d124e48ee0f4fbd46bf9f26376f9568690c97.gif";
$buddys3[]="IKONe69d8c11c4a0ab2745a3c5aa108f3180b8b29e9119.gif";
$buddys3[]="IKONe948a1a0d9151f69d003c04add61b79ca5778f6935.gif";
$buddys3[]="IKONe96ae86fdabdf778b23f2500828f8bf9a6b15ac817.gif";
$buddys3[]="IKONea23014b0d602ab62e2ee607160b87939f83e2f90b.gif";
$buddys3[]="IKONeb484bd75d993eaea4127eb2bcaaeebf0062d3ac87.gif";
$buddys3[]="IKONec6ef4120d50397332be39cb7c53ac309dca070431.gif";
$buddys3[]="IKONedbcf2caa604757c53b5e7dab23a7c847ca6b75a30.gif";
$buddys3[]="IKONf1c791f9400d837ad8b4f1468db3c49f234b8d6af0.gif";
$buddys3[]="IKONf29ee3327cde78611640e3fa47f1927ced2d0efbb5.gif";
$buddys3[]="IKONf2ded58d331e4dbccebb0f3c1edecaeaa3f2eca720.gif";
$buddys3[]="IKONf3d02a83b9bb3e52039a7e7225397abb3e4e70e83a.gif";
$buddys3[]="IKONf639edf97445f5f23502953dd32429bf40718d631a.gif";
$buddys3[]="IKONf77cbedce3cb92b50f3e524dc4ec6645ac95131ac6.gif";
$buddys3[]="IKONf86f84c5fc02449a483f3c69062542247c95c4c1b2.gif";
$buddys3[]="IKONfab060bb16010f285d957cb678d8f438f3e8d7fd25.gif";
$buddys3[]="IKONfc94fb89945217474cd64b67939f2799c0bd74eb1c.gif";
$buddys3[]="IKONfd23dd6d17ba28fa56c4dd3f6e5aec1c8f0b68732d.gif";
$buddys3[]="IKONfd5c8c3762a783f70f691130316ae68f38fbc38b5e.gif";
$buddys3[]="IKONfd5f07c0a9ca9da16296fad98e36c7ddf9a6d117a7.gif";

define ("_JOUEUR_REGULIER_",	1);
define ("_JOUEUR_INVITE_",		0);

$libelle_presence = array(_JOUEUR_REGULIER_ => "R�gulier", _JOUEUR_INVITE_ => "Invit�");

?>
