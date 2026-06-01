# HP ZBook Studio 16 G11 Red Filter Bezel

This folder contains a parametric OpenSCAD design for a removable red-filter bezel that fits over the display lid of an HP ZBook Studio 16 G11.

The design is intentionally split into printable parts:

- `left_frame_half`
- `right_frame_half`
- `splice_bar` (print 2)
- `top_clip` (print 2)
- `bottom_clip` (print 2)
- `filter_template` (2D outline for the red sheet)

## What This Design Assumes

- 16 inch, 16:10 panel
- lid width close to 355.8 mm
- lid height close to 229.6 mm
- lid thickness around 4.8 mm
- filter material around 1.0 to 1.5 mm thick

Those defaults are close enough to start, but you should measure your own machine before exporting the final STLs. The important values are at the top of `zbook_red_filter.scad`.

## Measurements To Verify

Use calipers if possible.

1. `lid_width`: front width of the display lid, measured across the bezel.
2. `lid_height`: front height of the display lid, measured from top edge to bottom edge.
3. `lid_thickness`: thickness of the display lid near the top edge.
4. `face_clearance`: how much air gap you want between the printed frame and the front aluminum around the panel. `0.6` to `1.0` mm is a sensible range.

If the top and bottom edge thickness differ, set the model for the thicker edge first and sand or pad the looser clips.

## Recommended Material

- PETG for easy printing and a little flex in the clips
- ASA if you want better heat resistance in a car or observatory
- black filament to minimize reflections

Avoid PLA for the clips unless you know the laptop will stay cool.

## Slicer And Print Notes

- Layer height: 0.20 mm
- Walls: 4
- Top/bottom layers: 5
- Infill: 20% gyroid or cubic
- Frame halves: print front face down (large flat side on the bed), no supports needed
- Clips: print on their side only if your slicer generates stronger layer orientation for the hook; otherwise print flat and use PETG

For Bambu Studio, use these support settings for the frame halves:

- Enable support: Off
- If support must be enabled globally for another object: Build plate only
- Brim: 3 to 5 mm if you see corner lift

## Export Workflow

Open `zbook_red_filter.scad` in OpenSCAD and set:

```scad
part = "left_frame_half";
```

Export these parts one at a time:

1. `left_frame_half`
2. `right_frame_half`
3. `splice_bar`
4. `top_clip`
5. `bottom_clip`
6. `filter_template`

Suggested quantities:

- 1 x `left_frame_half`
- 1 x `right_frame_half`
- 2 x `splice_bar`
- 2 x `top_clip`
- 2 x `bottom_clip`

## Assembly

1. Print one `top_clip` first and test the fit on the lid.
2. Adjust `lid_thickness` or `clip_clearance` if needed.
3. Print the rest of the parts.
4. Dry-fit the frame halves on a flat table.
5. Glue the `splice_bar` parts into the recessed pockets on the back of the frame with epoxy or medium CA.
6. Cut the red filter using `filter_template`.
7. Bond the filter sheet into the back rebate using thin black VHB tape or a narrow bead of flexible adhesive.
8. Tape the frame onto the laptop temporarily.
9. Position the clips on the back so they hold the lid without forcing it.
10. Mark the clip positions and glue them to the back of the frame.

## Filter Material Suggestions

- red acrylic sheet, 1.0 mm or 1.5 mm
- red polycarbonate sheet
- theater lighting gel if you want the lightest option

If you use a thin gel instead of rigid sheet, back it with a second clear sheet or add narrow tape strips around the rebate so the gel stays flat.

## Notes

- This is a removable observing accessory, not a permanent laptop modification.
- The defaults aim to hide the bright bezel while keeping the visible screen area unobstructed.
- The clips are intentionally separate so you can place them around microphones, webcam areas, or any geometry HP changed between revisions.