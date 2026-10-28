<div class="mt-4">
    <h3 class="text-sm text-gray-300 mb-2">{{ __('Sky quality') }}</h3>
    <div class="mt-2">
        @livewire('observation-conditions', ['initialSqm' => old('sqm', ''), 'initialNelm' => old('limiting_magnitude', ''), 'initialBortle' => old('bortle', '')])
    </div>

    <h3 class="text-sm text-gray-300 mb-2 mt-4">{{ __('Comet details') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1">{{ __('Magnitude method key') }} <a class="text-xs text-blue-300" href="#">{{ __('More info about the codes') }}</a></label>
            <select name="comet_method" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                <option value="">{{ __('Select method') }}</option>
                <option value="a" @if(old('comet_method') === 'a') selected @endif>a - Orange filter used on SOHO spacecraft with C2 and C3 coronagraphs</option>
                <option value="B" @if(old('comet_method') === 'B') selected @endif>B - VBM (Van Biesbroeck-Bobrovnikoff-Meisel) or simple Out-Out method</option>
                <option value="b" @if(old('comet_method') === 'b') selected @endif>b - VBM method using RCA #4549 image intensifier</option>
                <option value="C" @if(old('comet_method') === 'C') selected @endif>C - Unfiltered total CCD magnitude</option>
                <option value="c" @if(old('comet_method') === 'c') selected @endif>c - Unfiltered nuclear CCD magnitude</option>
                <option value="D" @if(old('comet_method') === 'D') selected @endif>D - Cousins B filter</option>
                <option value="G" @if(old('comet_method') === 'G') selected @endif>G - CCD magnitude with a Corion NR-400 minus-infrared filter</option>
                <option value="g" @if(old('comet_method') === 'g') selected @endif>g - CCD magnitude with Gunn g filter</option>
                <option value="H" @if(old('comet_method') === 'H') selected @endif>H - Cousins I filter with CCD</option>
                <option value="I" @if(old('comet_method') === 'I') selected @endif>I - In-focus</option>
                <option value="i" @if(old('comet_method') === 'i') selected @endif>i - Visual observation using an image intensifier</option>
                <option value="J" @if(old('comet_method') === 'J') selected @endif>J - Kron-Cousins V filter employed</option>
                <option value="j" @if(old('comet_method') === 'j') selected @endif>j - Kron-Cousins V filter employed (m2 estimate)</option>
                <option value="K" @if(old('comet_method') === 'K') selected @endif>K - Clear filter used on SOHO spacecraft with C3 coronagraph</option>
                <option value="k" @if(old('comet_method') === 'k') selected @endif>k - CCD magnitude with Cousins R filter</option>
                <option value="L" @if(old('comet_method') === 'L') selected @endif>L - Photoelectric B</option>
                <option value="l" @if(old('comet_method') === 'l') selected @endif>l - CCD magnitude with a Wratten 25 (red) filter</option>
                <option value="M" @if(old('comet_method') === 'M') selected @endif>M - Modified-Out method</option>
                <option value="N" @if(old('comet_method') === 'N') selected @endif>N - Magnitude of nucleus or condensation</option>
                <option value="O" @if(old('comet_method') === 'O') selected @endif>O - Out-of-focus method</option>
                <option value="o" @if(old('comet_method') === 'o') selected @endif>o - Visual extrafocal comparison using RCA #4549 image intensifier</option>
                <option value="P" @if(old('comet_method') === 'P') selected @endif>P - Photographic</option>
                <option value="p" @if(old('comet_method') === 'p') selected @endif>p - Photographic with Kodak 2415 film</option>
                <option value="Q" @if(old('comet_method') === 'Q') selected @endif>Q - Out-out</option>
                <option value="q" @if(old('comet_method') === 'q') selected @endif>q - R-band magnitude for nuclear condensation</option>
                <option value="R" @if(old('comet_method') === 'R') selected @endif>R - Photoelectric R</option>
                <option value="r" @if(old('comet_method') === 'r') selected @endif>r - CCD magnitude with Gunn r filter</option>
                <option value="S" @if(old('comet_method') === 'S') selected @endif>S - VSS (Vsekhsvyatskii-Steavenson-Sidgwick) or In-Out method</option>
                <option value="s" @if(old('comet_method') === 's') selected @endif>s - VSS method using image intensifier</option>
                <option value="T" @if(old('comet_method') === 'T') selected @endif>T - Magnitude estimated from a TV monitor</option>
                <option value="t" @if(old('comet_method') === 't') selected @endif>t - Total visual magnitude (historical obs. only)</option>
                <option value="U" @if(old('comet_method') === 'U') selected @endif>U - Photoelectric U</option>
                <option value="u" @if(old('comet_method') === 'u') selected @endif>u - Photoelectric U</option>
                <option value="V" @if(old('comet_method') === 'V') selected @endif>V - CCD-derived V nuclear magnitudes</option>
                <option value="v" @if(old('comet_method') === 'v') selected @endif>v - Photoelectric V</option>
                <option value="W" @if(old('comet_method') === 'W') selected @endif>W - Photoelectric with filters to match visual</option>
                <option value="w" @if(old('comet_method') === 'w') selected @endif>w - Photoelectric</option>
                <option value="X" @if(old('comet_method') === 'X') selected @endif>X - Ortochromatic film + yellow filter</option>
                <option value="Y" @if(old('comet_method') === 'Y') selected @endif>Y - CCD magnitude with Wratten No. 15 (yellow) filter</option>
            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-400 mb-1">{{ __('Magnitude reference key') }} <a class="text-xs text-blue-300" href="#">{{ __('More info about the codes') }}</a></label>
            <select name="comet_chart" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                <option value="">{{ __('Select reference') }}</option>
                <option value="AE" @if(old('comet_chart') === 'AE') selected @endif>AE - Planetary magnitudes from American Ephemeris and Nautical Almanac</option>
                <option value="AT" @if(old('comet_chart') === 'AT') selected @endif>AT - Arizona-Tonantzintla Catalog</option>
                <option value="AU" @if(old('comet_chart') === 'AU') selected @endif>AU - ASAS-3 V magnitudes</option>
                <option value="BR" @if(old('comet_chart') === 'BR') selected @endif>BR - V magnitude sequence for stars in the Coma cluster of galaxies</option>
                <option value="BS" @if(old('comet_chart') === 'BS') selected @endif>BS - Johnson V photometry by Brian Skiff</option>
                <option value="C" @if(old('comet_chart') === 'C') selected @endif>C - Photovisual magnitudes from Cape Photographic Catalogue for 1950.0</option>
                <option value="CA" @if(old('comet_chart') === 'CA') selected @endif>CA - M44 standard sequence</option>
                <option value="CD" @if(old('comet_chart') === 'CD') selected @endif>CD - Open star cluster NGC 225 photometry</option>
                <option value="CE" @if(old('comet_chart') === 'CE') selected @endif>CE - Open star cluster NGC 1647 photometry</option>
                <option value="CF" @if(old('comet_chart') === 'CF') selected @endif>CF - Open star cluster NGC 2129 photometry</option>
                <option value="CG" @if(old('comet_chart') === 'CG') selected @endif>CG - Open star cluster NGC 2422 (M47) photometry</option>
                <option value="CH" @if(old('comet_chart') === 'CH') selected @endif>CH - Open star cluster NGC 6494 (M23) photometry</option>
                <option value="CI" @if(old('comet_chart') === 'CI') selected @endif>CI - Open star cluster NGC 6823 photometry</option>
                <option value="CJ" @if(old('comet_chart') === 'CJ') selected @endif>CJ - Open star cluster NGC 6910 photometry</option>
                <option value="CK" @if(old('comet_chart') === 'CK') selected @endif>CK - Open star cluster NGC 7031 photometry</option>
                <option value="CL" @if(old('comet_chart') === 'CL') selected @endif>CL - Photometry by Hoag et al. (1961)</option>
                <option value="CM" @if(old('comet_chart') === 'CM') selected @endif>CM - Photovisual and photoelectric-V magnitudes from Cape Mimeograms</option>
                <option value="CN" @if(old('comet_chart') === 'CN') selected @endif>CN - Open star cluster NGC 7235 photometry</option>
                <option value="CO" @if(old('comet_chart') === 'CO') selected @endif>CO - UBV photometry for 39 stars in the range 11.7 &lt; V &lt; 18.7</option>
                <option value="CR" @if(old('comet_chart') === 'CR') selected @endif>CR - V magnitudes of 13 stars surrounding NGC 3627 (M66)</option>
                <option value="CS" @if(old('comet_chart') === 'CS') selected @endif>CS - Catalogue of Stellar Identifications</option>
                <option value="D" @if(old('comet_chart') === 'D') selected @endif>D - Dutch Comet Halley Handbook</option>
                <option value="E" @if(old('comet_chart') === 'E') selected @endif>E - One of Everhart's 3 Selected Area charts</option>
                <option value="EA" @if(old('comet_chart') === 'EA') selected @endif>EA - Selected Area 51:  From Everhart</option>
                <option value="EB" @if(old('comet_chart') === 'EB') selected @endif>EB - Selected Area 57:  From Everhart</option>
                <option value="EC" @if(old('comet_chart') === 'EC') selected @endif>EC - Selected Area 68:  From Everhart</option>
                <option value="FA" @if(old('comet_chart') === 'FA') selected @endif>FA - V photometry by Harold Ables, U.S. Naval Observatory</option>
                <option value="GA" @if(old('comet_chart') === 'GA') selected @endif>GA - Guide Star Photometric Catalog - I</option>
                <option value="GP" @if(old('comet_chart') === 'GP') selected @endif>GP - Harvard E Regions</option>
                <option value="HD" @if(old('comet_chart') === 'HD') selected @endif>HD - Henry Draper Catalog</option>
                <option value="HE" @if(old('comet_chart') === 'HE') selected @endif>HE - Harvard E Regions</option>
                <option value="HI" @if(old('comet_chart') === 'HI') selected @endif>HI - Hipparcos Input Catalogue</option>
                <option value="HJ" @if(old('comet_chart') === 'HJ') selected @endif>HJ - Magnitudes in the Hipparcos photometric system, Hp</option>
                <option value="HK" @if(old('comet_chart') === 'HK') selected @endif>HK - H_p magnitudes from the Hipparcos Catalogue</option>
                <option value="HN" @if(old('comet_chart') === 'HN') selected @endif>HN - Arne Henden's photometric sequences</option>
                <option value="HP" @if(old('comet_chart') === 'HP') selected @endif>HP - Harvard Photometry</option>
                <option value="HR" @if(old('comet_chart') === 'HR') selected @endif>HR - Harvard Revised Photometry</option>
                <option value="HV" @if(old('comet_chart') === 'HV') selected @endif>HV - Johnson V magnitudes from Hipparcos Catalogue</option>
                <option value="JT" @if(old('comet_chart') === 'JT') selected @endif>JT - Cousins VRI magnitudes of stars in M67</option>
                <option value="L" @if(old('comet_chart') === 'L') selected @endif>L - Landolt V Photoelectric Sequences</option>
                <option value="LA" @if(old('comet_chart') === 'LA') selected @endif>LA - Landolt photoelectric sequences</option>
                <option value="LB" @if(old('comet_chart') === 'LB') selected @endif>LB - Landolt sequences as published by Christian Buil</option>
                <option value="LC" @if(old('comet_chart') === 'LC') selected @endif>LC - Landolt magnitude sequence for 33 stars near V1057 Cyg</option>
                <option value="MC" @if(old('comet_chart') === 'MC') selected @endif>MC - Carlsberg Meridian Catalogue</option>
                <option value="ME" @if(old('comet_chart') === 'ME') selected @endif>ME - V photometry by Tedesco, Tholen, and Zellner</option>
                <option value="MK" @if(old('comet_chart') === 'MK') selected @endif>MK - V magnitudes for M67 in LE GUIDE PRATIQUE DE L'ASTRONOMIE CCD</option>
                <option value="MP" @if(old('comet_chart') === 'MP') selected @endif>MP - McCormick Photovisual Sequence</option>
                <option value="MS" @if(old('comet_chart') === 'MS') selected @endif>MS - McCormick Photovisual Sequences</option>
                <option value="MT" @if(old('comet_chart') === 'MT') selected @endif>MT - Visual magnitudes of stars in M67</option>
                <option value="MV" @if(old('comet_chart') === 'MV') selected @endif>MV - From Publ. Leander McCormick Obs.</option>
                <option value="NH" @if(old('comet_chart') === 'NH') selected @endif>NH - North Polar Sequence as published by Henden and Kaitchuck</option>
                <option value="NN" @if(old('comet_chart') === 'NN') selected @endif>NN - NGC 2129/6531/1342 cluster photometry</option>
                <option value="NO" @if(old('comet_chart') === 'NO') selected @endif>NO - U.S.N.O. Photoelectric Photometry Catalogue</option>
                <option value="NP" @if(old('comet_chart') === 'NP') selected @endif>NP - North Polar Sequence</option>
                <option value="NS" @if(old('comet_chart') === 'NS') selected @endif>NS - Magnitudes and Colors of Stars North of +80</option>
                <option value="OB" @if(old('comet_chart') === 'OB') selected @endif>OB - Magnitudes for faint cluster stars</option>
                <option value="PA" @if(old('comet_chart') === 'PA') selected @endif>PA - M45 sequence</option>
                <option value="PB" @if(old('comet_chart') === 'PB') selected @endif>PB - Pleiades chart in Sky and Telescope 70</option>
                <option value="PC" @if(old('comet_chart') === 'PC') selected @endif>PC - Pleiades sequence, Henden and Kaitchuck</option>
                <option value="PI" @if(old('comet_chart') === 'PI') selected @endif>PI - IC 4665 sequence as found in Henden and Kaitchuck</option>
                <option value="RB" @if(old('comet_chart') === 'RB') selected @endif>RB - Photoelectric Magnitudes and Colours of Southern Stars</option>
                <option value="RC" @if(old('comet_chart') === 'RC') selected @endif>RC - Standard Magnitudes in the E Regions</option>
                <option value="SD" @if(old('comet_chart') === 'SD') selected @endif>SD - V magnitudes of members of the globular cluster M15</option>
                <option value="SE" @if(old('comet_chart') === 'SE') selected @endif>SE - V magnitudes of 134 stars of the II Persei Association</option>
                <option value="SK" @if(old('comet_chart') === 'SK') selected @endif>SK - Brian Skiff's compilation of magnitudes as part of the LONEOS project</option>
                <option value="SM" @if(old('comet_chart') === 'SM') selected @endif>SM - V magnitudes from A Visual Atlas of the Small Magellanic Cloud</option>
                <option value="SP" @if(old('comet_chart') === 'SP') selected @endif>SP - Skalnate-Pleso Atlas Catalog</option>
                <option value="SS" @if(old('comet_chart') === 'SS') selected @endif>SS - Various regions covering declination -60 deg to +10 deg</option>
                <option value="SW" @if(old('comet_chart') === 'SW') selected @endif>SW - Four half-degree fields with finder charts and UBV photometry</option>
                <option value="TA" @if(old('comet_chart') === 'TA') selected @endif>TA - Comparison-star magnitudes from The Amateur Sky Survey</option>
                <option value="TG" @if(old('comet_chart') === 'TG') selected @endif>TG - CCD magnitudes on the Thuan-Gunn system</option>
                <option value="TI" @if(old('comet_chart') === 'TI') selected @endif>TI - Tycho Input Catalogue</option>
                <option value="TJ" @if(old('comet_chart') === 'TJ') selected @endif>TJ - Tycho Catalogue Johnson V magnitudes</option>
                <option value="TK" @if(old('comet_chart') === 'TK') selected @endif>TK - Tycho-2 Catalogue</option>
                <option value="TS" @if(old('comet_chart') === 'TS') selected @endif>TS - Field of 13 stars</option>
                <option value="TT" @if(old('comet_chart') === 'TT') selected @endif>TT - Tycho/Hipparcos Catalogue V_T magnitudes</option>
                <option value="VG" @if(old('comet_chart') === 'VG') selected @endif>VG - Japanese variable-star charts edited by K. Gomi</option>
                <option value="Y" @if(old('comet_chart') === 'Y') selected @endif>Y - Yale Bright Star Catalogue</option>
                <option value="YF" @if(old('comet_chart') === 'YF') selected @endif>YF - Yale Bright Star Catalogue, fourth edition</option>
                <option value="YG" @if(old('comet_chart') === 'YG') selected @endif>YG - Yale Bright Star Catalogue, fifth edition</option>
                <option value="AA" @if(old('comet_chart') === 'AA') selected @endif>AA - A.A.V.S.O. Variable Star Atlas</option>
                <option value="AC" @if(old('comet_chart') === 'AC') selected @endif>AC - Charts of the Amer. Assn. of Var. Star Observers</option>
                <option value="AP" @if(old('comet_chart') === 'AP') selected @endif>AP - Atlas Photometrique des Constellations</option>
                <option value="AS" @if(old('comet_chart') === 'AS') selected @endif>AS - AAVSO chart for M81 (NGC 3031) in Ursa Major</option>
                <option value="FD" @if(old('comet_chart') === 'FD') selected @endif>FD - A photoelectric BVRI sequence in the field of NGC 6205 (M13)</option>
                <option value="FG" @if(old('comet_chart') === 'FG') selected @endif>FG - A Field Guide to the Stars and Planets</option>
                <option value="LM" @if(old('comet_chart') === 'LM') selected @endif>LM - A Visual Atlas of the Large Magellanic Cloud</option>
                <option value="ML" @if(old('comet_chart') === 'ML') selected @endif>ML - V magnitudes on chart of Large Magellanic Cloud by Mati Morel</option>
                <option value="MM" @if(old('comet_chart') === 'MM') selected @endif>MM - V magnitudes on chart of Small Magellanic Cloud by Mati Morel</option>
                <option value="OH" @if(old('comet_chart') === 'OH') selected @endif>OH - From listing of bright stars in Observers' Handbook, R.A.S.C.</option>
                <option value="PK" @if(old('comet_chart') === 'PK') selected @endif>PK - From the Soviet Program for Comet Halley</option>
                <option value="S" @if(old('comet_chart') === 'S') selected @endif>S - Smithsonian Astrophysical Obs. Star Catalog</option>
                <option value="SA" @if(old('comet_chart') === 'SA') selected @endif>SA - M67 sequence by R. E. Schild</option>
                <option value="SC" @if(old('comet_chart') === 'SC') selected @endif>SC - Sky Catalogue 2000.0</option>
                <option value="VB" @if(old('comet_chart') === 'VB') selected @endif>VB - Variable star charts of the British Astr. Assn.</option>
                <option value="VF" @if(old('comet_chart') === 'VF') selected @endif>VF - Variable star charts of the A.F.O.E.V. (France)</option>
                <option value="VN" @if(old('comet_chart') === 'VN') selected @endif>VN - Variable star charts of the R.A.S. of New Zealand</option>
                <option value="W" @if(old('comet_chart') === 'W') selected @endif>W - International Halley Watch (IHW) version of an unspecified AAVSO chart</option>
                <option value="WA" @if(old('comet_chart') === 'WA') selected @endif>WA - Special IHW version of AAVSO chart for SU Tauri</option>
                <option value="WB" @if(old('comet_chart') === 'WB') selected @endif>WB - Special IHW version of AAVSO chart for CZ Orionis</option>
                <option value="WC" @if(old('comet_chart') === 'WC') selected @endif>WC - Special IHW version of AAVSO chart for Y Tauri</option>
                <option value="WD" @if(old('comet_chart') === 'WD') selected @endif>WD - Special IHW version of AAVSO chart for V Tauri</option>
                <option value="WE" @if(old('comet_chart') === 'WE') selected @endif>WE - IHW version of AAVSO chart for X Sextantis</option>
                <option value="WF" @if(old('comet_chart') === 'WF') selected @endif>WF - IHW version of AAVSO chart for S Sextantis</option>
                <option value="WG" @if(old('comet_chart') === 'WG') selected @endif>WG - IHW version of AAVSO chart for SX Leonis</option>
                <option value="WH" @if(old('comet_chart') === 'WH') selected @endif>WH - Unspecified IHW charts</option>
                <option value="WW" @if(old('comet_chart') === 'WW') selected @endif>WW - B.A.A. Charts as published in the IHW Observers' Manual</option>
            </select>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1">{{ __('Magnitude') }}</label>
            <div class="flex gap-2 items-center">
                <label class="text-xs text-gray-300"><input type="checkbox" name="comet_less_magnitude" value="1" @if(old('comet_less_magnitude')) checked @endif /> {{ __('Weaker than') }}</label>
                <input name="comet_magnitude_estimate" type="number" step="0.1" class="flex-1 rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" value="{{ old('comet_magnitude_estimate') }}" />
                <label class="ml-2 text-xs text-gray-300"><input type="checkbox" name="comet_magnitude_uncertain" value="1" @if(old('comet_magnitude_uncertain')) checked @endif /> {{ __('Uncertain') }}</label>
            </div>
        </div>

        <div>
            <label class="block text-xs text-gray-400 mb-1">{{ __('Degree of condensation') }}</label>
            <select name="comet_dc" class="w-24 rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                <option value="">-</option>
                @for ($i = 0; $i <= 9; $i++)
                    <option value="{{ $i }}" @if(old('comet_dc') !== null && (string) old('comet_dc') === (string) $i) selected @endif>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1">{{ __('Coma') }}</label>
            <div class="flex items-center gap-2">
                <input name="comet_coma" type="number" step="1" min="0" class="w-32 rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" value="{{ old('comet_coma') }}" />
                <span class="text-xs text-gray-400">arcminutes</span>
            </div>
        </div>

        <div>
            <label class="block text-xs text-gray-400 mb-1">{{ __('Tail length') }}</label>
            <div class="flex items-center gap-2">
                <input name="comet_tail_length" type="number" step="1" min="0" class="w-32 rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" value="{{ old('comet_tail_length') }}" />
                <span class="text-xs text-gray-400">arcminutes</span>
            </div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1">{{ __('Position angle of tail') }}</label>
            <div class="flex items-center gap-2">
                <input name="comet_position_angle" type="number" step="1" min="0" max="360" class="w-32 rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" value="{{ old('comet_position_angle') }}" />
                <span class="text-xs text-gray-400">degrees</span>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-xs text-gray-400 mb-1">{{ __('Drawing') }}</label>
        <div>
            <input type="file" name="sketch" accept="image/*" class="text-xs text-gray-300" />
        </div>
    </div>
</div>
