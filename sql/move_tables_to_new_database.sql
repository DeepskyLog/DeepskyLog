-- Moves the tables of this site that the new application has no equivalent
-- for from the old database into the new one, unchanged, as legacy_*. After
-- this the site does not use the old database at all.
--
-- RENAME TABLE across databases on the same server moves the files and copies
-- nothing, so it takes a fraction of a second (the star tables are 2.7 GB).
-- Run it once, as a user with ALTER and DROP on the old database and CREATE
-- and INSERT on the new one, with the database names of the server in place
-- of deepskylog (old) and deepskylogLaravel (new).
--
-- The new application's stars table has the same stars, but the queries of
-- this site's atlas (one magnitude in a large area) are many times slower on
-- it than on these per-magnitude tables.

RENAME TABLE
    deepskylog.stars8 TO deepskylogLaravel.legacy_stars8,
    deepskylog.stars9 TO deepskylogLaravel.legacy_stars9,
    deepskylog.stars10 TO deepskylogLaravel.legacy_stars10,
    deepskylog.stars11 TO deepskylogLaravel.legacy_stars11,
    deepskylog.stars12 TO deepskylogLaravel.legacy_stars12,
    deepskylog.stars13 TO deepskylogLaravel.legacy_stars13,
    deepskylog.stars140 TO deepskylogLaravel.legacy_stars140,
    deepskylog.stars145 TO deepskylogLaravel.legacy_stars145,
    deepskylog.stars1500 TO deepskylogLaravel.legacy_stars1500,
    deepskylog.stars1525 TO deepskylogLaravel.legacy_stars1525,
    deepskylog.stars1550 TO deepskylogLaravel.legacy_stars1550,
    deepskylog.stars1575 TO deepskylogLaravel.legacy_stars1575,
    deepskylog.stars1600 TO deepskylogLaravel.legacy_stars1600,
    deepskylog.stars1625 TO deepskylogLaravel.legacy_stars1625,
    deepskylog.stars1650 TO deepskylogLaravel.legacy_stars1650,
    deepskylog.stars1675 TO deepskylogLaravel.legacy_stars1675,
    deepskylog.stars1700 TO deepskylogLaravel.legacy_stars1700,
    deepskylog.stars1725 TO deepskylogLaravel.legacy_stars1725,
    deepskylog.stars1750 TO deepskylogLaravel.legacy_stars1750,
    deepskylog.stars1775 TO deepskylogLaravel.legacy_stars1775,
    deepskylog.stars18 TO deepskylogLaravel.legacy_stars18,
    deepskylog.formlayouts TO deepskylogLaravel.legacy_formlayouts,
    deepskylog.observerqueries TO deepskylogLaravel.legacy_observerqueries,
    deepskylog.ICQ_METHOD TO deepskylogLaravel.legacy_icq_method,
    deepskylog.ICQ_REFERENCE_KEY TO deepskylogLaravel.legacy_icq_reference_key;
