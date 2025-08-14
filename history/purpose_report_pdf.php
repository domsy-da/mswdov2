<?php
require_once '../documents/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['html'])) {
    $html = $_POST['html'];

    // Minimalist black and white style
    $style = '
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            color: #111;
            background: #fff;
            font-size: 13px;
        }
        h2, h3 {
            color: #111;
            border-bottom: 1.5px solid #222;
            padding-bottom: 4px;
            margin-bottom: 16px;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        th, td {
            border: 1px solid #222;
            padding: 7px 10px;
            text-align: left;
        }
        th {
            background: #222;
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) td {
            background: #f5f5f5;
        }
        tr:nth-child(odd) td {
            background: #fff;
        }
        .total {
            text-align: right;
            font-weight: bold;
            font-size: 1.08em;
            margin-top: 10px;
            color: #111;
        }
    </style>
    ';

    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml('<html><head>' . $style . '</head><body>' . $html . '</body></html>');
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('purpose_report_section.pdf', ['Attachment' => false]);
    exit;
}
?>