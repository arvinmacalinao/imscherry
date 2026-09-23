<?php

/*
|--------------------------------------------------------------------------
| Invoice printing (pre-printed continuous form, Epson LX-310)
|--------------------------------------------------------------------------
|
| paper_*  : physical size of ONE form (perforation to perforation).
|            9.5in x 11in = 241.3mm x 279.4mm (standard continuous form).
|            Must match the paper size selected in the Epson driver, and the PDF
|            must be printed at "Actual size" / 100% (never "Fit to page").
|
| offset_* : moves EVERY field at once, in mm. Use this after a test print:
|            text too far left  -> increase offset_x
|            text too high      -> increase offset_y
|
*/

return [
    'paper_width_mm'  => 241.3,
    'paper_height_mm' => 279.4,

    'offset_x_mm' => 0,
    'offset_y_mm' => 0,
];
