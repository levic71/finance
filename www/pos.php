<? //error_reporting(0);

$q = $_GET['q'] ? stripslashes( $_GET['q'] ) : 'jorkyball';
$u = $_GET['u'] ? stripslashes( $_GET['u'] ) : 'www.jorkers.com';

$q2 = htmlspecialchars( $q );
$u2 = htmlspecialchars( $u );

$link_url = $u;

if ( !preg_match("!^http://!",$u) )      $u = "http://$u";
if ( preg_match("!^http://[^/]+$!",$u) )   $u .= '/';
$u = str_replace( '.', '\.', $u );
$u = str_replace( '*', '.*?', $u );

$qe = urlencode( $q );

echo <<<EOF
   <HTML><BODY>\n
   <TITLE>POSITIONS</TITLE>\n
   <FORM ACTION=$_SERVER[PHP_SELF] METHOD=Get>
    <A HREF=./ STYLE=font-weight:bold;>Home</A><BR>
    <B>Requête Google</B> <INPUT TYPE=Text NAME=q VALUE="$q2" SIZE=50><BR>
    <B>URL à trouver</B> <INPUT TYPE=Text NAME=u VALUE="$u2" SIZE=50><BR>
    <INPUT TYPE=Submit>

EOF;

if ( !$_GET['q'] || !$_GET['u'] )
   exit;

echo "<FONT SIZE=2 COLOR=Red><B>---</B> : Signifie que le site n'a pas été trouvé parmi les 100 premiers résultats Google.</FONT><P>\n";
echo "<FONT SIZE=2 COLOR=Red>Recherche en cours, veuillez patienter ...</FONT><P>\n";flush();


$serveurs = array
(
   '72.14.203.104',
   '66.249.93.104',
   '64.233.179.104',
   '216.239.37.104',
   '216.239.39.104',
   '216.239.53.104',
   '216.239.57.104',
   '216.239.59.104',
   '216.239.63.104',
   '64.233.161.104',
   '64.233.167.104',
   '64.233.171.104',
   '64.233.183.104',
   '64.233.185.104',
   '64.233.187.104',
   '66.102.7.104',
   '64.233.189.104',
   '66.102.9.104',
   '66.102.11.104'
);

echo"
<table border='0' cellpadding='2'>
  <tr>
    <th scope='col'>Rank</th>
    <th scope='col'>Total</th>
    <th scope='col'>BL</th>
    <th scope='col'>Serveur</th>
    <th scope='col'>Description</th>
  </tr>
";

for ( $i=0; $i<=count($serveurs)-1; $i++ )
{
   $f = 0;
   $found = false;
   $serveur = $serveurs["$i"];
   
   for ( $s=0; $s<=0; $s++ )
   {
      $ss = $s * 10;
//query
$g = "http://$serveur/search?as_q=$qe&num=100&hl=fr&btnG=Recherche+Google&as_epq=&as_oq=&as_eq=&lr=&as_ft=i&as_filetype=&as_qdr=all&as_occt=any&as_dt=i&as_sitesearch=&as_rights=&safe=images";
$html = file_get_contents( $g );
$html = preg_replace ("'<blockquote[^>]*?>.*?</blockquote>'si", "", "$html");

//total result
$pos = strpos($html, "sur un total d'environ");
$rest = substr("$html", $pos+22, 35);
$total_number = eregi_replace("[^0-9.-]", "", $rest);
$total_number = number_format($total_number, 0, ',', ' ');

//back link
$link = "http://$serveur/search?q=link%3A$link_url&sourceid=mozilla-search&start=0&start=0&ie=utf-8&oe=utf-8";
$link = file_get_contents( $link );

$pos = strpos($link, "of about");
$rest = substr("$link", $pos+5, 35);

$link = eregi_replace("[^0-9.-]", "", $rest);
$link = number_format($link, 0, ',', ' ');

      foreach ( split('<br>',$html) as $serps )
      {
         if ( preg_match("!<a class=l href=\"http://!",$serps) )
         {
            $f++;
           
            if ( preg_match("!<a class=l href=\"$u\"!",$serps) )
            {
               $found = true;
               break 2;
            };
         };
      };
   };
   if (!$found) $f='--';

echo"<tr><td>n°<FONT COLOR=Red><B>$f</B></FONT></td>
<td>$total_number</td>
<td>$link</td>
<td><A HREF=$g TARGET=_blank STYLE=text-decoration:none;color=dimgray;font-weight:bold>$serveur</A> <I>(".($i+1)."/".count($serveurs).")</I></td>
";
 //  echo "n°<FONT COLOR=Red><B>$f</B></FONT> sur <A HREF=$g TARGET=_blank STYLE=text-decoration:none;color=dimgray;font-weight:bold>$serveur</A> <I>(".($i+1)."/".count($serveurs).")</I>";
echo"<td>";
if ($serveur=='72.14.203.104') echo " &nbsp; &nbsp;<FONT COLOR=green>- www IRL Pour la majorité des requetes</FONT>";
if ($serveur=='66.249.93.104') echo " &nbsp; &nbsp;<FONT COLOR=red>- BigDaddy1 IRL</FONT>";
if ($serveur=='64.233.179.104') echo " &nbsp; &nbsp;<FONT COLOR=red>- BigDaddy2 US</FONT>";
if ($serveur=='216.239.37.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- www-va - US</FONT>";
if ($serveur=='216.239.39.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- www-dc - US</FONT>";
if ($serveur=='216.239.53.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- www-in - US</FONT>";
if ($serveur=='216.239.57.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- www-cw - US</FONT>";
if ($serveur=='66.102.7.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- www-mc - US</FONT>";
if ($serveur=='216.239.63.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- US -eu-customers</FONT>";
if ($serveur=='64.233.161.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- US -us-peers</FONT>";
if ($serveur=='64.233.167.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- US -us-peers-</FONT>";
if ($serveur=='64.233.171.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- US -us-customers</FONT>";
if ($serveur=='64.233.183.104') echo " &nbsp; &nbsp;<FONT COLOR=green>- IRL</FONT>";
if ($serveur=='64.233.185.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- US -asia-customers</FONT>";
if ($serveur=='64.233.187.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- US -asia-customers</FONT>";
if ($serveur=='64.233.189.104') echo " &nbsp; &nbsp;<FONT COLOR=Blue>- ASIA -asia-customers</FONT>";
if ($serveur=='216.239.59.104') echo " &nbsp; &nbsp;<FONT COLOR=green>- www-gv - IRL</FONT>";
if ($serveur=='66.102.9.104') echo " &nbsp; &nbsp;<FONT COLOR=green>- www-lm - IRL</FONT>";
if ($serveur=='66.102.11.104') echo " &nbsp; &nbsp;<FONT COLOR=green>- www-kr - IRL</FONT>";

   echo "</td></tr>";
   flush();
};
?>
</table>
   </FORM>
   </BODY></HTML> 
