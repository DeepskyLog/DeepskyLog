#!/usr/bin/perl

use strict;
use Getopt::Std;

my $line;
my @countriesData;
my @provincesData;
my %countryList;
my %provincesList;

if( ! @ARGV )
{
  die "Usage: adaptLocationDatabase.pl filename\n";
}

# Change the inputfile (COUNTRY.TXT) to the outputfile (COUNTRY.AST)
my $locationFile = $ARGV[0];

my $outputFile = substr($locationFile, 0, - 3)."ast";

# Reading in the file with all countries.
print "Reading the countries.txt file\n";

open(countriesFile, "countries.txt") or die("Can't open countries.txt: $!");

@countriesData = <countriesFile>;
close(countriesFile);

my $Country;
my $Code;

foreach $line (@countriesData)
{
 ($Country, $Code) = split(/ - \(/, $line);
 $Code = substr($Code, 0, 2);
 
 if ($Code ne "")
 {
  $countryList{$Code} = ucfirst(lc($Country));
 }
}

# Load the regions table. In the hash table, the country and the number are
# concatenated as key. This means : BE01 -> Antwerpen
open(provincesFile, "provinces.txt") or die("Can't open provinces.txt: $!");

@provincesData = <provincesFile>;
close(provincesFile);

my $Province;
my $code;
my $cnt;
my %list;

foreach $line (@provincesData)
{
 ($cnt, $code, $Province) = split(/\t/, $line);

 $code = $cnt.$code; 
 $provincesList{$code} = $Province;
}


# Read the locations file and write a new one
print "Converting $locationFile to $outputFile\n";

open(fileIN, "$locationFile") or die("Can't open $locationFile for reading: $!");
open(fileOUT, ">$outputFile") or die("Can't open $outputFile for writing: $!");

my $r0;
my $r1;
my $r2;
my $r3;
my $r4;
my $r5;
my $r6;
my $r7;
my $r8;
my $r9;
my $r10;
my $r11;
my $r12;
my $r13;
my $r14;
my $r15;
my $r16;
my $r17;
my $r18;
my $r19;
my $r20;
my $r21;
my $r22;
my $r23;

while($line = <fileIN>)
{
 ($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $r8, $r9, $r10, $r11, $r12, $r13, $r14, $r15, $r16, $r17, $r18, $r19, $r20, $r21, $r22, $r23) = split(/\t/, $line);

 if ($r9 eq "P")
 {
  print fileOUT $r23."\t".$r4."\t".$r3."\t".$countryList{$r12}."\t".$provincesList{$r12.$r13}."\n";
 }
}

close(fileOUT);
close(fileIN);

print "Conversion succesfully done!\n"; 
