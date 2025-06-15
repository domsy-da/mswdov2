<?php
header('Content-Type: application/json');

$folderPath = '../scanned_id/'; // Change this to your folder path

function checkFiles($folderPath) {
    if (!is_dir($folderPath)) {
        return ["error" => "The folder does not exist!"];
    }

    // Allowed image extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $files = array_diff(scandir($folderPath), array('.', '..'));

    $imageFiles = [];

    // Filter out only image files
    foreach ($files as $file) {
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array(strtolower($fileExtension), $allowedExtensions)) {
            $imageFiles[] = $file;
        }
    }

    if (count($imageFiles) > 0) {
        $firstFile = $imageFiles[0]; // Get the first image file
        return [
            "hasFile" => true,
            "fileName" => $firstFile,
            "filePath" => $folderPath . $firstFile
        ];
    } else {
        return ["hasFile" => false];
    }
}

// Call the function and return the response as JSON
echo json_encode(checkFiles($folderPath));
?>
