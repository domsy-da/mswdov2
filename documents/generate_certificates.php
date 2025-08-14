<?php
header('Content-Type: application/json');

// Ensure required fields
if (!isset($_POST['beneficiary_id']) || !isset($_POST['request_purpose'])) {
    echo json_encode(['success' => false, 'message' => 'Missing beneficiary_id or request_purpose.']);
    exit;
}

$beneficiary_id = $_POST['beneficiary_id'];
$request_purpose = $_POST['request_purpose'];

// Map request_purpose to template prefix
$prefix = '';
switch ($request_purpose) {
    case 'med_exp': $prefix = 'medical_'; break;
    case 'burial': $prefix = 'burial_'; break;
    case 'educational': $prefix = 'educational_'; break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid request purpose.']);
        exit;
}

// List of template files to process
$files = [
    "{$prefix}certificate_of_eligibility.php",
    "{$prefix}certificate.php",
    "{$prefix}social_case_study.php"
];

// Prepare directories
$templateDir = "templates";
$genSavedDir = "gen_saved";
$savedDir = "saved"; // Add this line
$beneficiaryDir = "$genSavedDir/$beneficiary_id";
if (!is_dir($genSavedDir)) mkdir($genSavedDir, 0777, true);
if (!is_dir($beneficiaryDir)) mkdir($beneficiaryDir, 0777, true);

// Format request amount as decimal
$request_amount = isset($_POST['request_amount']) && $_POST['request_amount'] !== ''
    ? number_format((float)$_POST['request_amount'], 2, '.', '')
    : '';

// Gather all placeholders and values from form
$placeholders = [
    '[Client Name]'           => $_POST['client_name'] ?? '',
    '[Client Address]'        => $_POST['complete_address'] ?? '',
    '[Relationship]'     => (function() {
        $relation = $_POST['relation'] ?? '';
        $gender = strtolower($_POST['patient_gender'] ?? '');
        if (!$relation) return '';
        // Map relation to pronoun
        $female_relations = ['mother', 'sister', 'wife', 'daughter', 'herself'];
        $male_relations = ['father', 'brother', 'husband', 'son', 'himself'];
        if (in_array(strtolower($relation), $female_relations)) {
            return 'her ' . $relation;
        } elseif (in_array(strtolower($relation), $male_relations)) {
            return 'his ' . $relation;
        } elseif ($gender === 'female') {
            return 'her ' . $relation;
        } elseif ($gender === 'male') {
            return 'his ' . $relation;
        } else {
            return 'their ' . $relation;
        }
    })(),
    '[Prepared By]'           => $_POST['prep_by'] ?? '',
    '[Civil Status]'          => $_POST['civil_status'] ?? '',
    '[Request Date]'          => $_POST['request_date'] ?? '',
    '[Patient Name]'          => $_POST['patient_name'] ?? '',
    '[Client Age]'            => $_POST['client_age'] ?? '',
    '[Patient Age]'           => $_POST['patient_age'] ?? '',
    '[Client Civil Status]'   => $_POST['civil_status'] ?? '',
    '[Patient Civil Status]'  => $_POST['patient_civil_status'] ?? '',
    '[Client Birthday]'       => $_POST['birthday'] ?? '',
    '[Patient Birthday]'      => $_POST['patient_birthday'] ?? '',
    '[Client Birthplace]'     => $_POST['birthplace'] ?? '',
    '[Patient Birthplace]'    => $_POST['patient_birthplace'] ?? '',
    '[Client Education]'      => $_POST['educational'] ?? '',
    '[Patient Education]'     => $_POST['patient_education'] ?? '',
    '[Client Occupation]'     => $_POST['occupation'] ?? '',
    '[Patient Occupation]'    => $_POST['patient_occupation'] ?? '',
    '[Client Religion]'       => $_POST['religion'] ?? '',
    '[Patient Religion]'      => $_POST['patient_religion'] ?? '',
    '[Client Complete Address]'   => $_POST['complete_address'] ?? '',
    '[Patient Complete Address]'  => $_POST['patient_complete_address'] ?? '',
    '[Prepared Position]'     => $_POST['pos_prep'] ?? '',
    '[Noted By]'              => $_POST['not_by'] ?? '',
    '[Noted Position]'        => $_POST['pos_not'] ?? '',
    '[Amount]'                => $request_amount,
    '[Diagnosis]'             => $_POST['request_diagnosis'] ?? '',
    '[req_spe_type]'          => $_POST['req_spe_type'] ?? '',
];

// Gender-based pronouns for [He/She] and [His/Her]
$gender = strtolower($_POST['patient_gender'] ?? $_POST['client_gender'] ?? '');
if ($gender === 'male') {
    $placeholders['[He/She]'] = 'He';
    $placeholders['[His/Her]'] = 'his';
} elseif ($gender === 'female') {
    $placeholders['[He/She]'] = 'She';
    $placeholders['[His/Her]'] = 'her';
} else {
    $placeholders['[He/She]'] = '';
    $placeholders['[His/Her]'] = '';
}

// --- Fetch relatives for the beneficiary ---
include 'config/db.php'; // Make sure this is a valid PDO connection as $pdo

$relativesRows = '';
try {
    $stmt = $pdo->prepare("SELECT * FROM relatives WHERE beneficiary_id = ? ORDER BY id ASC");
    $stmt->execute([$beneficiary_id]);
    $relatives = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($relatives as $rel) {
        $relativesRows .= '<tr>
            <td style="border: 1px solid black; text-align: center;">' . htmlspecialchars($rel['name']) . '</td>
            <td style="border: 1px solid black; text-align: center;">' . htmlspecialchars($rel['age']) . '</td>
            <td style="border: 1px solid black; text-align: center;">' . htmlspecialchars($rel['civil_status']) . '</td>
            <td style="border: 1px solid black; text-align: center;">' . htmlspecialchars($rel['relationship']) . '</td>
            <td style="border: 1px solid black; text-align: center;">' . htmlspecialchars($rel['educational_attainment']) . '</td>
            <td style="border: 1px solid black; text-align: center;">' . htmlspecialchars($rel['occupation']) . '</td>
        </tr>';
    }
} catch (Exception $e) {
    $relativesRows = '<tr><td colspan="6" style="text-align:center;">Unable to load family data</td></tr>';
}

// Process each template
foreach ($files as $file) {
    // Determine the base filename (without extension)
    $baseName = pathinfo($file, PATHINFO_FILENAME);

    // Check for a saved template with .html extension
    $savedTemplatePath = "$savedDir/$baseName.html";
    $templatePath = "$templateDir/$file";

    // Use saved template if it exists, otherwise use default template
    if (file_exists($savedTemplatePath)) {
        $html = file_get_contents($savedTemplatePath);
    } elseif (file_exists($templatePath)) {
        $html = file_get_contents($templatePath);
    } else {
        echo json_encode(['success' => false, 'message' => "Missing template: $baseName"]);
        exit;
    }

    // --- Replace the relatives table placeholder ---
    $html = preg_replace(
        '/<tr>\s*<td[^>]*>\[Rel Name #1\]<\/td>.*?<\/tr>/s',
        $relativesRows ?: '<tr><td colspan="6" style="text-align:center;">No family data</td></tr>',
        $html
    );

    // --- Replace all other placeholders ---
    $html = str_replace(array_keys($placeholders), array_values($placeholders), $html);

    // Save the filled version
    if (file_put_contents("$beneficiaryDir/$file", $html) === false) {
        echo json_encode(['success' => false, 'message' => "Failed to write file: $beneficiaryDir/$file"]);
        exit;
    }
}

echo json_encode(['success' => true, 'req_pur' => $_POST['request_purpose'], 'message' => 'Certificates generated successfully!', 'beneficiary_id' => $beneficiary_id]);