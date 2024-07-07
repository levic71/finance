#!/usr/bin/perl

use strict;
use warnings;
use DBI;

#my $dbh = DBI->connect("DBI:mysql:jorkersfinance:jorkersfinance.mysql.db",'jorkersfinance','Rnvubwi2021');
my $dbh = DBI->connect("DBI:mysql:finance2:localhost",'root','root');

die "failed to connect to MySQL database:DBI->errstr()" unless($dbh);

# prepare SQL statement
my $sth = $dbh->prepare("SELECT symbol FROM stocks")
                   or die "prepare statement failed: $dbh->errstr()";

$sth->execute() or die "execution failed: $dbh->errstr()";

my($symbol);

# loop through each row of the result set, and print it
while(($symbol) = $sth->fetchrow()){
   print("$symbol\n");
}

$sth->finish();
$dbh->disconnect();


#use DBI;
#$myConnection = DBI->connect("DBI:mysql:jorkersfinance:jorkersfinance.mysql.db", "jorkersfinance", "Rnvubwi2021");

#if(!$myConnection){
# die "failed to connect to MySQL database DBI->errstr()";
#}else{
# print("Connected to MySQL server successfully.\n");
#}

#$query = $myConnection->prepare("SELECT * FROM stocks");
#$res = $query->execute();

#print "hello \n";


#foreach my $row (@$res) {
#   print join(',', @$row), "\n";
#   print "toto\n";
#}