<?php
if (isset($_POST['html']) && isset($_POST['filename'])) {
  $html = $_POST['html'];
  $filename = basename($_POST['filename']);
  $saveDir = "saved";

  // ✅ Ensure the "saved" folder exists
  if (!is_dir($saveDir)) {
    mkdir($saveDir, 0777, true);
  }

  $savePath = "$saveDir/$filename";
  file_put_contents($savePath, $html);
  echo "Certificate saved to $savePath!";
} else {
  echo "Missing data.";
}
