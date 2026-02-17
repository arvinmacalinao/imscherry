// $(function () {
//     "use strict";

//     /**
//      * Generating PDF from HTML using jQuery
//      */
//     $(document).on("click", "#invoice_download_btn", function () {
//         var contentWidth = $("#invoice_wrapper").width();
//         var contentHeight = $("#invoice_wrapper").height();
//         var topLeftMargin = 20;
//         var pdfWidth = contentWidth + topLeftMargin * 2;
//         var pdfHeight = pdfWidth * 1.5 + topLeftMargin * 2;
//         var canvasImageWidth = contentWidth;
//         var canvasImageHeight = contentHeight;
//         var totalPDFPages = Math.ceil(contentHeight / pdfHeight) - 1;
//         const dateNow = new Date().toLocaleString().split(",")[0];

//         html2canvas($("#invoice_wrapper")[0], { allowTaint: true }).then(
//             function (canvas) {
//                 canvas.getContext("2d");
//                 var imgData = canvas.toDataURL("image/jpeg", 1.0);
//                 var pdf = new jsPDF("p", "pt", [pdfWidth, pdfHeight]);
//                 pdf.addImage(
//                     imgData,
//                     "JPG",
//                     topLeftMargin,
//                     topLeftMargin,
//                     canvasImageWidth,
//                     canvasImageHeight
//                 );
//                 for (var i = 1; i <= totalPDFPages; i++) {
//                     pdf.addPage(pdfWidth, pdfHeight);
//                     pdf.addImage(
//                         imgData,
//                         "JPG",
//                         topLeftMargin,
//                         -(pdfHeight * i) + topLeftMargin * 4,
//                         canvasImageWidth,
//                         canvasImageHeight
//                     );
//                 }
//                 pdf.save(`invoice-${dateNow}.pdf`);
//             }
//         );
//     });
// });
$(function () {
    "use strict";

    $(document).on("click", "#invoice_download_btn", function () {
        const dateNow = new Date().toLocaleString().split(",")[0];

        // US Letter size in points (1 in = 72pt)
        var pdfWidth = 612;   // 8.5 in * 72
        var pdfHeight = 792;  // 11 in * 72
        var margin = 20;

        html2canvas($("#invoice_wrapper")[0], {
            allowTaint: true,
            scale: 2, // improve quality
            useCORS: true
        }).then(function (canvas) {
            var imgData = canvas.toDataURL("image/jpeg", 1.0);

            var pdf = new jsPDF("p", "pt", [pdfWidth, pdfHeight]);

            // scale image to fit width
            var imgWidth = pdfWidth - margin * 2;
            var pageHeight = pdfHeight - margin * 2;
            var imgHeight = (canvas.height * imgWidth) / canvas.width;

            var heightLeft = imgHeight;
            var position = margin;

            // first page
            pdf.addImage(imgData, "JPG", margin, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            // add more pages if needed
            while (heightLeft > 0) {
                position = heightLeft - imgHeight + margin;
                pdf.addPage([pdfWidth, pdfHeight]);
                pdf.addImage(imgData, "JPG", margin, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            pdf.save(`invoice-${dateNow}.pdf`);
        });
    });
});


