# Example files for the observation import

Example CSV and OAL 2.1 files for `/observation/import`, one for each case the
importers handle. `tests/Feature/ObservationImportFixturesTest.php` imports
every file here and checks the outcome below; it fails when a file is added
without an entry in its table.

## Before testing by hand

The CSV importer only uses equipment and locations that are already in the
account, by name (case-insensitive). Add these first:

| Kind       | Name                 | Details                                           |
|------------|----------------------|---------------------------------------------------|
| Location   | `Example Site`       | latitude 50.88, longitude 4.70 (Leuven)           |
| Instrument | `Example Dobson 250` | 250 mm, focal length 1200 mm                      |
| Instrument | `Naked eye`          | type Naked Eye                                    |
| Eyepiece   | `Example 10mm`       | 10 mm, 82°                                        |
| Filter     | `Example UHC`        |                                                   |
| Lens       | `Example Barlow 2x`  | factor 2                                          |

The OAL importer finds these by name too. It creates what is missing: the OAL
files contain the same site, scope, eyepiece, lens and filter, so without them
the OAL import creates them (and with them, `valid-observations.xml` still
creates `Example Remote Site` and `Example Refractor 102`).

A target of an OAL file that is not in the database is only imported when the
file defines it as a deep-sky object: an `xsi:type` of `oal:deepSkyAS`, `CG`,
`DN`, `DS`, `GC`, `GN`, `GX`, `MS`, `OC`, `PN` or `QS`, and a `<position>`. It
is then added to the objects table (data source "OAL import by <username>").
Delete `Example Nebula 123` after testing by hand; it is added to the real
database.

The horizon checks need the coordinates of Example Site and of the objects
M 31, M 13, M 42, M 45, M 81 and NGC 104, all of which are in the DeepskyLog
database. The solar-system files use 12P/Pons-Brooks (with its orbital
elements), the Moon, Copernicus, Tycho and Io; their times are chosen so that
the comet, the Moon and Jupiter are well above (or below) the horizon of
Example Site.

The all-types files also use the Sun, Jupiter and the asteroid Vesta. The
development database has no asteroids (the `asteroids` table is filled by an
import from the MPC), so by hand the Vesta observation is skipped as not found
until Vesta is in that table; the automated test adds it.

A name is looked up among the deep-sky objects first, then the Sun and planets,
lunar features, the moons (the Moon and the moons of the planets), asteroids
and comets. `Moon` is stored as the Moon of the moons table, from a CSV file and
from an OAL file alike.

Importing a file again updates the observations it imported before instead of
adding them a second time: an observation of the same object at the same UT
moment (or on the same date, both without a time) with the same instrument.
An observation with another instrument is another observation; one without an
instrument (from the old site) is updated and gets the instrument of the file.
Delete the observations to start over.

## CSV files (`csv/`)

Columns (no header line, `;` between them): object; observer; date
(DD-MM-YYYY or YYYY-MM-DD); UT time (HH:MM or HH:MM:SS); location; instrument;
eyepiece; filter; lens; seeing (1–5); limiting magnitude; SQM; visibility
(1–7); language; description.

| File                         | Imported | Skipped | Expected                                                                                     |
|------------------------------|---------:|--------:|----------------------------------------------------------------------------------------------|
| `valid-observations.csv`     | 6        | 0       | No errors or warnings. Every column used; a DD-MM-YYYY and a YYYY-MM-DD date; a time with seconds; an SQM with a decimal comma; HTML in the description; a naked-eye observation; objects written without a space and with a leading zero (`M81`, `M042`, stored as `M 81` and `M 42`); a row without a time or location in lower case (`m 31`, `example dobson 250`). |
| `valid-export-format.csv`    | 2        | 0       | No errors. Written as DeepskyLog exports it: byte order mark, CRLF, quoted fields, a `;` and `""` inside a quoted description, `0` for a limiting magnitude or SQM that was not measured. |
| `invalid-unknown-object.csv` | 0        | 1       | An error: `Example Unknown Nebula 123` is not in the database, so the row is skipped. |
| `invalid-rows.csv`           | 0        | 12      | One error per row, in this order: no object; no instrument; no description; a description of only `<p>&nbsp;</p>`; date `2026/09/12`; unknown location; unknown instrument; unknown eyepiece; unknown filter; unknown lens; M 42 44.4° below the horizon; NGC 104 never rises. |
| `mixed.csv`                  | 2        | 2       | Rows 1 and 3 imported; row 2 an unknown instrument, row 4 the date `31-13-2026`.              |
| `valid-all-types.csv`        | 8        | 0       | One observation of each kind of target: a deep-sky object (M 31), a comet (12P/Pons-Brooks), the Sun, a planet (Jupiter), the Moon (naked eye), a lunar feature (Copernicus), a moon of a planet (Io) and an asteroid (Vesta). |
| `valid-same-moment.csv`      | 4        | 0       | M 31 at the same moment through the Dobson and with the naked eye, and the same without a time the next night: four observations, also after importing the file again. |
| `valid-solar-system.csv`     | 6        | 0       | No errors. A comet (12P/Pons-Brooks, also in lower case without a time), the Moon with the naked eye, the lunar features Copernicus and Tycho, and Io (a moon of Jupiter). |
| `invalid-solar-system.csv`   | 0        | 4       | An unknown comet; an unknown lunar feature; the Moon 19.2° below the horizon; 12P/Pons-Brooks 7.6° below the horizon. |
| `below-horizon.csv`          | 0        | 2       | M 42 below the horizon at that moment; NGC 104 never rising on that date. With *Also import observations of objects below the horizon* ticked: both imported, each with a warning. |

## OAL files (`oal/`)

All files except the `invalid-schema-*` and `invalid-not-well-formed` ones
validate against the OAL 2.1 schema (`resources/schemas/oal21/oal21.xsd`).
They have the `.xml` extension because the upload field only offers `.csv`,
`.txt` and `.xml`.

| File                                   | Imported | Skipped | Expected                                                                               |
|----------------------------------------|---------:|--------:|----------------------------------------------------------------------------------------|
| `valid-observations.xml`               | 5        | 0       | No errors or warnings. A begin with an offset (`+02:00`, stored as 21:30 UT), with `Z` and without an offset; eyepiece, lens, filter; a deep-sky result with a rating; two results of which the first is empty (the Dutch one is used); naked eye (no scope); a new site and a new scope, which are created; target names without a space (`m13`, `M81`, stored as `M 13` and `M 81`). |
| `valid-all-types.xml`                  | 8        | 0       | As `valid-all-types.csv`, with the OAL target type of each kind: `deepSkyGX`, `CometTargetType`, `SunTargetType`, `PlanetTargetType`, `MoonTargetType` (Moon and Io), a lunar feature as DeepskyLog exports it, and `MinorPlanetTargetType`. |
| `valid-same-moment.xml`                | 4        | 0       | As `valid-same-moment.csv`: through the Dobson and with the naked eye (no scope), at 21:30 UT (once written in local time) and at midnight UT. |
| `valid-scopes.xml`                     | 11       | 0       | One observation through each kind of scope, which are created: binoculars (`fixedMagnificationOpticsType` with magnification, true field and vendor Celestron, a known make), fixed-magnification optics without a type (binoculars), the type letters N, R, C, S, M and K, an aperture of 102.6 mm (stored as 103), type A (the account's naked-eye instrument), `Example Dobson 250` with 300 mm, which is not the account's 250 mm one and is added as `Example Dobson 250 (300 mm)`, and vendor Meade with model `Meade LX200 10` (named `Meade LX200 10`, with make Meade). |
| `valid-solar-system.xml`               | 5        | 0       | No errors. A `CometTargetType`; the Moon as a `MoonTargetType` with the naked eye; Copernicus as DeepskyLog exports a lunar feature (no type, the table and key in `<notes>`); Tycho as a `MoonTargetType`, as other programs may write a lunar feature; Io. |
| `invalid-solar-system.xml`             | 0        | 3       | An unknown lunar feature (a `MoonTargetType` is never added as an object); the Moon below the horizon; 12P/Pons-Brooks below the horizon. |
| `valid-latin1.xml`                     | 1        | 0       | No errors. Encoded in ISO-8859-1, with accents in the French description.               |
| `valid-new-targets.xml`                | 3        | 0       | No errors. `Example Nebula 123` is not in the database but the file defines it (deep-sky type and position), so it is added as an object (type BRTNB, diameters, magnitude, surface brightness, position angle) and both observations of it point to it; `Andromeda Galaxy` is found through its `<alias>M31</alias>`. |
| `invalid-unknown-targets.xml`          | 0        | 5       | One error per observation of a target that is not in the database: no position; type `deepSkyNA`, which has no DeepskyLog type; a comet (only deep-sky objects are added from a file); a defined galaxy that is below the horizon (no object is added for it either); a position but no type. |
| `warning-no-observations.xml`          | 0        | 0       | A warning: no `<observation>` elements.                                                |
| `invalid-not-well-formed.xml`          | 0        | 0       | Two schema validation errors: a closing tag is missing. Nothing is imported.            |
| `invalid-schema-missing-target.xml`    | 0        | 0       | A schema validation error: the observation has no `<target>`.                          |
| `invalid-schema-date-only.xml`         | 0        | 0       | A schema validation error: `<begin>2026-09-12</begin>` has no time.                    |
| `invalid-schema-unknown-reference.xml` | 0        | 0       | A schema validation error: `<scope>` refers to an id that is not in the file.          |
| `invalid-observations.xml`             | 0        | 4       | Schema-valid, but each observation is skipped with an error: a description of only spaces; M 42 below the horizon; NGC 104 never rises (a begin at midnight UT counts as no time); a scope without a model or vendor. |
| `below-horizon.xml`                    | 0        | 2       | As `below-horizon.csv`, including the checkbox.                                        |
| `valid-sessions.xml`                   | 4        | 0       | No errors or warnings. Two sessions, both created: one at Example Site with a begin and end in local time (`+02:00`, stored as 21:00 to 02:30 the next day), a co-observer (not added; an info message gives the name), Dutch weather, equipment (two lines) and comments; one at `Example Remote Site` (created) in UT (stored as 20:00 to 00:00, the local time) without texts. The first holds M 31 and M 45 (after midnight local time), the second M 81; M 42 is in no session. |
| `valid-sessions-only.xml`              | 0        | 0       | A warning: no `<observation>` elements. The session is imported all the same.           |
| `invalid-sessions.xml`                 | 1        | 0       | Two errors, each session skipped: one ends before it begins; one refers to a site that is not in the file. The observation of the first is imported, in no session. |
| `invalid-schema-session-without-site.xml` | 0     | 0       | A schema validation error: the session has no `<site>`.                                 |
| `warning-unknown-session.xml`          | 1        | 0       | A warning: the observation refers to a session that is not in the file. The schema means to reject this, but its keyref for sessions selects the wrong elements. |

## Sessions

A `<session>` of an OAL file is imported as a session of the observer, named
"Session at <site> on <date>" (OAL gives a session no name; rename it on the
session page). Its begin and end are stored as the local time at its site, as
the session form stores them. A session of the observer with the same begin
and end date is updated instead, so importing `valid-sessions.xml` again
changes nothing. Co-observers are not added: that sends them an invitation and
a copy of the session, so the observer adds them on the session page.

As every session in DeepskyLog, an imported session holds the observations of
its observers between its begin and end date, not the observations the file
links to it: importing `valid-sessions-only.xml` into an account with
observations on 12 or 13 September 2026 links those. The `<session>` of an
observation is only checked against the sessions of the file.

Delete the sessions after testing by hand.

## Round trips

`tests/Feature/ObservationImportExportRoundTripTest.php` imports these files,
exports the observations again (the CSV export of the observation search, and
`/observations/search/export.oal.xml`) and compares them:

- file → database → file in the same format: the exported file holds the same
  observations as the original;
- database → file → database: the export imported into another account (CSV
  or OAL, and from one format into the other) gives the same observations, and
  imported into the same account it changes nothing.

The sessions of an OAL export (of a session, or of the observation search) are
compared in `tests/Feature/OalSessionImportExportTest.php`.

The files are compared by observation, not as text: the order, the quoting,
the ids, the observer and the target details an export adds differ. Two
differences are expected:

- an SQM the file leaves out is derived from the limiting magnitude when the
  observation is saved, so the export has one;
- OAL requires a time, so an observation without a time is exported at
  midnight UT; another account imports it as an observation at 00:00 UT (the
  same account matches it to the observation without a time).

To compare by hand: import a file into an empty account, export the
observations on the observation search page, and compare the observations (not
the text), keeping these differences in mind.
