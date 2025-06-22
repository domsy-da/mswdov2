<!-- Header Section -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
  <!-- Left Logo -->
  <img src="<?php include '../img/bagongpilipinas.php'; ?>" alt="Left Logo" style="width: 80px; height: auto;">

  <!-- Center Text -->
  <div style="text-align: center; flex-grow: 1;">
    <p style="margin: 0; font-weight: bold; font-size: 14px;">Republic of the Philippines</p>
    <p style="margin: 0; font-weight: bold; font-size: 14px;">Province of Oriental Mindoro</p>
    <p style="margin: 0; font-weight: bold; font-size: 14px;">Municipality of Gloria</p>
  </div>

  <!-- Right Logo -->
  <img src="<?php include '../img/mswdo.php'; ?>" alt="Right Logo" style="width: 80px; height: auto;">
</div>

<!-- Divider Lines -->
<hr style="border: 1px solid black; margin: 2px 0;">
<hr style="border: 1px solid black; margin: 0 0 20px 0;">

<!-- Title -->
<h2 style="text-align: center; margin-bottom: 30px;">
  <?php
    $title = 'C E R T I F I C A T E';
    foreach (str_split($title) as $char) {
      if ($char === ' ') {
        echo '&nbsp;';
      } else {
        echo '<span style="text-decoration: underline;">' . htmlspecialchars($char) . '</span>';
      }
    }
  ?>
</h2>

<!-- Body Content -->
<p style="text-align: justify; text-indent: 40px; line-height: 1.6;">
  This is to certify that <strong>Kompa Tua</strong> <strong>Single</strong> of legal age/s resident/s of <strong>Dupong, Kawit, Magdalena, Laguna</strong> is financially incapable to support <strong>herself</strong> medical expenses.
</p>

<p style="text-align: justify; text-indent: 40px; line-height: 1.6;">
  This further certifies that their income is below family threshold. The family doesn't have any real property for taxation purposes according to the Assessor and Treasury Office of this Municipality.
</p>

<p style="text-align: justify; text-indent: 40px; line-height: 1.6;">
  Issued this <strong>2025-06-19</strong> for the purpose of seeking financial assistance.
</p>

<!-- Signature Block -->
<div style="text-align: right; margin-top: 80px;">
  <p style="margin: 0;">Signed by:</p>
  <p style="margin: 0; font-weight: bold;">Doms Agoncillo</p>
  <p style="margin: 0;">Municipal Social Welfare & Dev’t Officer</p>
</div>
