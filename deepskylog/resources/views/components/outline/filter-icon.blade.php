
<style>
  .filter-icon { color:#e6eaf2; }                 /* main stroke on dark */
  .filter-icon [data-glass] {
    fill: var(--filter-color, currentColor);
    fill-opacity: .14;                            /* subtle glass tint */
    stroke: none;
  }
  .filter-icon [data-bandpass] { stroke: var(--filter-accent, #8ab4f8); }
  /* Presets (tweak to taste) */
  .filter--oiii  { --filter-color:#27d1a5; --filter-accent:#27d1a5; } /* teal/green */
  .filter--hbeta { --filter-color:#3aa0ff; --filter-accent:#3aa0ff; } /* blue/cyan   */
  .filter--uhc   { --filter-color:#62d3ff; --filter-accent:#62d3ff; } /* cool cyan   */
</style>

<!-- Example usage: O-III -->
<svg {{ $attributes->merge(['class' => 'filter-icon filter--oiii']) }} xmlns="http://www.w3.org/2000/svg"
     width="24" height="24" viewBox="0 0 24 24" role="img" aria-label="O-III filter"
     fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <g vector-effect="non-scaling-stroke">
    <ellipse cx="12" cy="7" rx="7" ry="3"/>
    <path d="M5 7 V17 M19 7 V17"/>
    <ellipse cx="12" cy="17" rx="7" ry="3"/>
    <ellipse cx="12" cy="7" rx="5" ry="2" data-glass/>
    <path d="M7.2 8.1 C9.3 9.1 14.7 9.1 16.8 8.1"/>
    <path d="M4.8 10 H5.5 M4.8 12 H5.5 M4.8 14 H5.5"/>
    <path d="M18.5 10 H19.2 M18.5 12 H19.2 M18.5 14 H19.2"/>
    <!-- O-III: two lines -->
    <g data-bandpass>
      <path d="M10.5 6 L10.5 8"/>
      <path d="M13.5 6 L13.5 8"/>
    </g>
  </g>
</svg>
