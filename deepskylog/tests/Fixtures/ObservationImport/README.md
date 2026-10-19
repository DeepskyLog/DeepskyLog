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
adding them a second time. Delete the observations to start over.

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
| `valid-solar-system.xml`               | 5        | 0       | No errors. A `CometTargetType`; the Moon as a `MoonTargetType` with the naked eye; Copernicus as DeepskyLog exports a lunar feature (no type, the table and key in `<notes>`); Tycho as a `MoonTargetType`, as other programs may write a lunar feature; Io. |
| `invalid-solar-system.xml`             | 0        | 3       | An unknown lunar feature (a `MoonTargetType` is never added as an object); the Moon below the horizon; 12P/Pons-Brooks below the horizon. |
| `valid-latin1.xml`                     | 1        | 0       | No errors. Encoded in ISO-8859-1, with accents in the French description.               |
| `valid-new-targets.xml`                | 3        | 0       | No errors. `Example Nebula 123` is not in the database but the file defines it (deep-sky type and position), so it is added as an object (type BRTNB, diameters, magnitude, surface brightness, position angle) and both observations of it point to it; `Andromeda Galaxy` is found through its `<alias>M31</alias>`. |
| `invalid-unknown-targets.xml`          | 0        | 4       | One error per observation of a target that is not in the database: no position; type `deepSkyNA`, which has no DeepskyLog type; a comet; a defined galaxy that is below the horizon (no object is added for it either). |
| `warning-no-observations.xml`          | 0        | 0       | A warning: no `<observation>` elements.                                                |
| `invalid-not-well-formed.xml`          | 0        | 0       | Two schema validation errors: a closing tag is missing. Nothing is imported.            |
| `invalid-schema-missing-target.xml`    | 0        | 0       | A schema validation error: the observation has no `<target>`.                          |
| `invalid-schema-date-only.xml`         | 0        | 0       | A schema validation error: `<begin>2026-09-12</begin>` has no time.                    |
| `invalid-schema-unknown-reference.xml` | 0        | 0       | A schema validation error: `<scope>` refers to an id that is not in the file.          |
| `invalid-observations.xml`             | 0        | 4       | Schema-valid, but each observation is skipped with an error: a description of only spaces; M 42 below the horizon; NGC 104 never rises (a begin at midnight UT counts as no time); a scope without a model or vendor. |
| `below-horizon.xml`                    | 0        | 2       | As `below-horizon.csv`, including the checkbox.                                        |
