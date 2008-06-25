#!/usr/bin/perl

use strict;
use Getopt::Std;

my $line;

if( ! @ARGV )
{
  die "Usage: adaptLocationDatabase.pl filename\n";
}

# Change the inputfile (COUNTRY.TXT) to the outputfile (COUNTRY.AST)
my $locationFile = $ARGV[0];

my $outputFile = "us.ast";
my $state = ucfirst(lc(substr($locationFile, 0, -4)));

# Read the locations file and write a new one
print "Converting $locationFile to $outputFile\n";

open(fileIN, "$locationFile") or die("Can't open $locationFile for reading: $!");
open(fileOUT, ">>$outputFile") or die("Can't open $outputFile for writing: $!");

while($line = <fileIN>)
{
 if ($line =~ /.{13}(.{101})(ppl).{23}(.{7}).(.{8})/)
 {
  my $longname = $1;
  my $lat = $3;
  my $lon = $4;
  my $name;
  my $longitude;
  my $latitude;

  $longname =~ s/( +)$//;

  if ($lon =~ /(.{3})(.{2})(.{2})(.)/)
  {
   my $sign = 1;

   if ($4 eq "W")
   { 
    $sign = -1;    
   }
   $longitude = $sign * ($1 + ($2 + $3 / 60.0) / 60.0);
  }

  if ($lat =~ /(.{2})(.{2})(.{2})(.)/)
  {
   my $sign = -1;

   if ($4 eq "N")
   {
    $sign = 1;
   }
   $latitude = $sign * ($1 + ($2 + $3 / 60.0) / 60.0);
  }

  printf fileOUT "$longname\t%.3f\t%.3f\tUnited States\t$state\n", $longitude, $latitude;
 }
}

close(fileOUT);
close(fileIN);

print "Conversion succesfully done!\n"; 
