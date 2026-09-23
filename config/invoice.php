<?php

/*
|--------------------------------------------------------------------------
| Invoice printing (pre-printed continuous form, Epson LX-310)
|--------------------------------------------------------------------------
|
| paper_*  : size of ONE form without the tractor-hole strips:
|            8.5in x 11in = Letter = 215.9mm x 279.4mm.
|            Must match the paper size selected in the Epson driver, and the PDF
|            must be printed at "Actual size" / 100% (never "Fit to page" /
|            "Fit to printable area" - that shrinks everything to ~85%).
|
| offset_* : moves EVERY field at once, in mm. Use this after a test print:
|            text too far left  -> increase offset_x
|            text too high      -> increase offset_y
|
*/

return [
    'paper_width_mm'  => 215.9,
    'paper_height_mm' => 279.4,

    'offset_x_mm' => 0,
    'offset_y_mm' => 3.4,   // 13px; tuned on the LX-310 with Letter paper size (2026-09-23)
];
