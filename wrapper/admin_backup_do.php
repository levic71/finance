<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$valid = Wrapper::getRequest('valid', 0);

function compress($srcFileName, $dstFileName)
{
   // getting file content
   $fp = fopen( $srcFileName, "r" );
   $data = fread ( $fp, filesize( $srcFileName ) );
   fclose( $fp );

   // writing compressed file
   $zp = gzopen( $dstFileName, "w9" );
   gzwrite( $zp, $data );
   gzclose( $zp );
}

function backup_table($table, $req, $fichier)
{
    $fields  = mysqli_query("SELECT * FROM ".$table." LIMIT 1,1");
    $columns = mysqli_num_fields($fields);

    $cols_type = array();
    for ($i = 0; $i < $columns; $i++)
    {
    	$name = mysqli_field_name($fields, $i);
    	$type = mysqli_field_type($fields, $i);
       	$cols_type[$name] = $type;
    }

	$i = 0;
	$j = 0;
    $res = dbc::execSQL($req);
    if (mysqli_num_rows($res) > 0)
    {
        fputs($fichier, "INSERT INTO `".$table."` ");
        while($row = mysqli_fetch_assoc($res))
        {
            if ($i == 0)
            {
                fputs($fichier, "(");
                $k = 0;
                while(list($cle, $val) = each($row))
              	{
                    fputs($fichier, ($k == 0 ? "" : ",")."`".$cle."`");
                    $k++;
                }
                reset($row);
                $i++;
                fputs($fichier, ") VALUES ");
            }

            fputs($fichier, ($j > 0 ? ",\n" : "\n")."(");
       	    $k = 0;
            while(list($cle, $val) = each($row))
          	{
                fputs($fichier, ($k == 0 ? "" : ","));
                if ($cols_type[$cle] == "int")
                    fputs($fichier, $val);
                else if ($cols_type[$cle] == "string" || $cols_type[$cle] == "blob")
                    fputs($fichier, "'".preg_replace("/(\r\n|\n|\r)/", "\\r\\n", str_replace("'", "\'", $val))."'");
                else
                    fputs($fichier, "'".$val."'");
                $k++;
            }
            fputs($fichier, ")");

            $j++;
        }
        fputs($fichier, ";\n\n");
    }

    return $j;
}

?>

<h2 class="grid dashboard">Backup/Restore</h2>

<?

if ($valid == 1)
{
    $sqlfilename = "../backup/bck_".$sess_context->getRealChampionnatId().".sql";
    $fichier = fopen($sqlfilename, "w");
    if (flock($fichier, LOCK_EX))
    {
        $table = "jb_championnat";
        $req = "SELECT * FROM ".$table." WHERE id=".$sess_context->getRealChampionnatId();
        fputs($fichier, "DELETE FROM ".$table." WHERE id=".$sess_context->getRealChampionnatId().";\n");
        $nb_championnats = backup_table($table, $req, $fichier);

        $table = "jb_saisons";
        $req = "SELECT * FROM ".$table." WHERE id_champ=".$sess_context->getRealChampionnatId();
        fputs($fichier, "DELETE FROM ".$table." WHERE id_champ=".$sess_context->getRealChampionnatId().";\n");
        $nb_saisons = backup_table($table, $req, $fichier);

        $table = "jb_joueurs";
        $req = "SELECT * FROM ".$table." WHERE id_champ=".$sess_context->getRealChampionnatId();
        fputs($fichier, "DELETE FROM ".$table." WHERE id_champ=".$sess_context->getRealChampionnatId().";\n");
        $nb_joueurs = backup_table($table, $req, $fichier);

        $table = "jb_equipes";
        $req = "SELECT * FROM ".$table." WHERE id_champ=".$sess_context->getRealChampionnatId();
        fputs($fichier, "DELETE FROM ".$table." WHERE id_champ=".$sess_context->getRealChampionnatId().";\n");
        $nb_equipes = backup_table($table, $req, $fichier);

        $nb_journees = 0;
        $nb_matchs   = 0;

        $req_saison = "SELECT * FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId();
        $res = dbc::execSQL($req_saison);
        while($row = mysqli_fetch_assoc($res))
        {
            $table = "jb_journees";
            $req = "SELECT * FROM ".$table." WHERE id_champ=".$row['id'];
            fputs($fichier, "DELETE FROM ".$table." WHERE id_champ=".$row['id'].";\n");
            $nb_journees += backup_table($table, $req, $fichier);

            $table = "jb_classement_poules";
            $req = "SELECT * FROM ".$table." WHERE id_champ=".$row['id'];
            fputs($fichier, "DELETE FROM ".$table." WHERE id_champ=".$row['id'].";\n");
            backup_table($table, $req, $fichier);

            $table = "jb_matchs";
            $req = "SELECT * FROM ".$table." WHERE id_champ=".$row['id'];
            fputs($fichier, "DELETE FROM ".$table." WHERE id_champ=".$row['id'].";\n");
            $nb_matchs += backup_table($table, $req, $fichier);
        }

        flock($fichier, LOCK_UN);
    }
    fclose($fichier);

    $zipfilename = "../backup/bck_".$sess_context->getRealChampionnatId();
    if (file_exists($zipfilename."_3.gz")) unlink($zipfilename."_3.gz");
    if (file_exists($zipfilename."_2.gz")) rename($zipfilename."_2.gz", $zipfilename."_3.gz");
    if (file_exists($zipfilename."_1.gz")) rename($zipfilename."_1.gz", $zipfilename."_2.gz");

    compress($sqlfilename, $zipfilename."_1.gz");
    unlink($sqlfilename);
?>

<div>
<b>Résultat BACKUP : OK</b>
<ul>
    <li> Nombre de championnats: <?= $nb_championnats ?>
    <li> Nombre de saisons: <?= $nb_saisons ?>
    <li> Nombre de joueurs: <?= $nb_joueurs ?>
    <li> Nombre d'equipes: <?= $nb_equipes ?>
    <li> Nombre de journées: <?= $nb_journees ?>
    <li> Nombre de matchs: <?= $nb_matchs ?>
</ul>
<b>ATTENTION: Les messages du forum ne sont pas sauvegardés.</b>
</div>

<? } ?>


<div style="margin: 30px 0px 30px 0px;">
<b>Liste des backups disponiques :</b>
<ul>

<?
$dir = "../backup/";
if (is_dir($dir))
{
   if ($dh = opendir($dir))
   {
       while (($file = readdir($dh)) !== false)
       {
           if (is_file($dir.$file) && strstr($file, "bck_".$sess_context->getRealChampionnatId()."_"))
           {
				$fp = fopen($dir.$file, "r");
				$fstat = fstat($fp);
				fclose($fp);
				echo "<li>".date("Y-m-d H:i:s", $fstat['mtime'])." ".$file;
			}
       }
       closedir($dh);
   }
}

?>

</ul>
<b>ATTENTION: On ne conserve que les 3 derniers backups.</b>
</div>

<div class="actions grouped_inv">
<button onclick="go({action: 'seasons', id:'main', url:'admin_backup_do.php?valid=1'});" class="button green">Lancer un backup</button>
<button onclick="mm({action: 'dashboard'});" class="button gray">Annuler</button>
</div>

<br />

<div style="margin: 30px 0px 30px 0px;">
<b>
Pour restaurer un backup, contacter le webmaster.
La restauration d'un backup écrase les modifications rélalisées entre la date du backup et la date de restauration.
</b>
</div>

