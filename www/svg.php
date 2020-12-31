<?php
  header("Content-type: image/svg+xml");
  echo '<?xml version="1.0" encoding="iso-8859-1"?>';
  $texte = "Vive la France!";
?>

<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20010904//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">

<svg xml:space="default" width="300" height="200">
  <rect style="fill:#0000ff" x="0" y="0" width="100" height="200"/>
  <rect style="fill:#ffffff" x="100" y="0" width="100" height="200"/>
  <rect style="fill:#ff0000" x="200" y="0" width="100" height="200"/>
  <text x="150" y="100" style="text-anchor:middle; fill:#000000; font-size:30; font-weight:15; font-family:Tahoma, Verdana; font-style:regular">
    <?=$texte;?>
  </text>
</svg>