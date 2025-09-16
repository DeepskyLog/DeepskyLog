<style>
  .barlow-icon { color:#e6eaf2; }                 /* outline color on dark */
  .barlow-icon [data-socket] {
    fill: currentColor; fill-opacity:.10; stroke:none; /* subtle cavity tint */
  }
  .barlow-icon [data-group]  { stroke: #8ab4f8; }      /* lens-group accent */
  .barlow-icon [data-screw]  { opacity:.7; }           /* deemphasize or hide */
  /* For 2" look, scale width or increase barrel rx via a variant class */
  .barlow--twoinch { /* example: use a wider SVG or scale container */ }
</style>
<!-- Astronomical Lens (Barlow / Telecentric) Icon -->
<svg
  xmlns="http://www.w3.org/2000/svg"
  width="24" height="24" viewBox="0 0 24 24"
  role="img" aria-labelledby="title desc"
  fill="none" stroke="currentColor" stroke-width="1.5"
  stroke-linecap="round" stroke-linejoin="round"
  style="display:inline-block;vertical-align:middle"
>
  <title id="title">Astronomical amplifying lens (Barlow / telecentric)</title>
  <desc id="desc">
    Stylized Barlow/telecentric lens with eyepiece socket, knurl ring, long barrel, and internal lens group.
  </desc>

  <g vector-effect="non-scaling-stroke">
    <!-- Eyepiece socket (top) -->
    <ellipse cx="12" cy="5.5" rx="4.8" ry="2.1"/>
    <ellipse cx="12" cy="5.5" rx="3.6" ry="1.5" data-socket/>

    <!-- Side walls of socket -->
    <path d="M7.2 5.5 V9.8"/>
    <path d="M16.8 5.5 V9.8"/>

    <!-- Knurled ring (grip) -->
    <path d="M8.6 9.4 H15.4"/>
    <path d="M8.4 10.2 H15.6"/>

    <!-- Main body taper into barrel -->
    <path d="M8.6 10 L9.9 14.2"/>
    <path d="M15.4 10 L14.1 14.2"/>
    <path d="M9.9 14.2 Q12 14.9 14.1 14.2"/>

    <!-- Barrel (1.25") -->
    <path d="M10.2 14.2 V20"/>
    <path d="M13.8 14.2 V20"/>
    <ellipse cx="12" cy="20" rx="2.0" ry="1.0"/>
    <!-- Safety undercut -->
    <path d="M10.6 17.2 H13.4"/>

    <!-- Internal lens group (diverging/telecentric hint) -->
    <!-- Parentheses-like pair inside the barrel -->
    <path d="M11.05 16.1 C11.55 16.6 11.55 18.4 11.05 18.9" data-group/>
    <path d="M12.95 16.1 C12.45 16.6 12.45 18.4 12.95 18.9" data-group/>

    <!-- (Optional) tiny thumbscrew hint on the socket (toggle via CSS as needed) -->
    <path d="M17.2 8.7 H18.6" data-screw/>
    <circle cx="18.9" cy="8.7" r="0.45" data-screw/>
  </g>
</svg>
