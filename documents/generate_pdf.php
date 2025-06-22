<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $html = $_POST['html'];

  $options = new Options();
  $options->set('isRemoteEnabled', true);

  $dompdf = new Dompdf($options);
  $dompdf->loadHtml('<html><body>' . $html . '</body></html>');
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream('certificate.pdf', ['Attachment' => false]);
}
?>
