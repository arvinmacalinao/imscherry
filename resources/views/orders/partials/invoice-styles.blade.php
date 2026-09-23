<style>
    @page {
        margin: 0;
        size: {{ config('invoice.paper_width_mm') }}mm {{ config('invoice.paper_height_mm') }}mm;
    }

    body {
        margin: 0;
        font-family: "DejaVu Sans Mono", monospace;
        font-size: 10pt;
        font-weight: bold;
        color: #000;
    }

    /* one pre-printed form; 1mm shorter than the paper so dompdf never adds a blank page */
    .invoice-page {
        position: relative;
        width: {{ config('invoice.paper_width_mm') }}mm;
        height: {{ config('invoice.paper_height_mm') - 1 }}mm;
        overflow: hidden;
    }
    .page-break { page-break-after: always; }

    .field {
        position: absolute;
        white-space: nowrap;
        line-height: 1.2;
    }
    .small { font-size: 9pt; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .address {
        white-space: normal;
        font-size: 8pt;
        line-height: 1.1;
        height: 7mm;            /* max 2 lines, never runs into the table header */
        overflow: hidden;
    }

    .items {
        width: 189mm;
        border-collapse: collapse;
        white-space: normal;
    }
    .items td { padding: 0 0 1mm 0; vertical-align: top; }
    .items .col-qty   { width: 14mm;  text-align: center; }
    .items .col-desc  { width: 96.2mm; padding-left: 16.8mm; }   /* 113mm incl. padding */
    .items .col-price { width: 27mm;  text-align: right; }
    .items .col-total { width: 35mm;  text-align: right; }
</style>
