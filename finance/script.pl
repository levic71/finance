#!/usr/bin/perl

use DBI;
$myConnection = DBI->connect("DBI:mysql:jorkersfinance:jorkersfinance.mysql.db", "jorkersfinance", "Rnvubwi2021");

$query = $myConnection->prepare("SELECT * FROM stocks");
$res = $query->execute();

foreach my $row (@$res) {
   print join(',', @$row), "\n";
}


#    cnx = mysql.connector.connect(user='jorkersfinance', password='Rnvubwi2021', host='jorkersfinance.mysql.db', database='jorkersfinance')
