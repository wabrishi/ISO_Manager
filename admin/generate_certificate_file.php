<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../models/Certificate.php';

// Composer autoload
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

if(!isset($_GET['id'])) {
    die("Certificate ID required.");
}

$database = new Database();
$db = $database->getConnection();
$cert = new Certificate($db);
$cert->id = $_GET['id'];

if(!$cert->readOne()) {
    die("Certificate not found.");
}

// Generate QR Code
$verify_url = "http://" . $_SERVER['HTTP_HOST'] . str_replace("admin/generate_certificate_file.php", "", $_SERVER['PHP_SELF']) . "verify.php?cert=" . $cert->certificate_number;
$qr_options = new QROptions([
    'version'    => 5,
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'   => QRCode::ECC_L,
]);
$qrcode = new QRCode($qr_options);
$qr_image = $qrcode->render($verify_url);
$qr_filename = $cert->certificate_number . ".png";
$qr_filepath = "../uploads/qrcodes/" . $qr_filename;

// Save QR Code to file from base64
$qr_data = explode(',', $qr_image)[1];
file_put_contents($qr_filepath, base64_decode($qr_data));

// Update DB with QR Path
$cert->qr_code = $qr_filename;

// Generate PDF
// Prepare template background
$template_path = realpath(__DIR__ . '/../assets/images/new_template.png');
$template_data = base64_encode(file_get_contents($template_path));
$template_src = 'data:image/png;base64,' . $template_data;

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 0px; size: A4 portrait; }
        body { margin: 0; padding: 0; font-family: "Helvetica", "Arial", sans-serif; width: 100%; height: 100%; }

        .certificate-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("' . $template_src . '");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }

        .content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .cert-number { position: absolute; top: 160px; left: 550px; font-size: 16px; font-weight: bold; }
        .company-name { position: absolute; top: 310px; left: 0; width: 100%; text-align: center; font-size: 34px; font-weight: bold; color: #1a365d; }
        .company-address { position: absolute; top: 370px; left: 0; width: 100%; text-align: center; font-size: 14px; }
        .certification-text { position: absolute; top: 430px; left: 0; width: 100%; text-align: center; font-size: 18px; font-style: italic; }
        .standard { position: absolute; top: 480px; left: 0; width: 100%; text-align: center; font-size: 26px; font-weight: bold; color: #b7791f; }
        .scope { position: absolute; top: 530px; left: 100px; width: 590px; text-align: center; font-size: 14px; line-height: 1.5; }

        .issue-date { position: absolute; top: 780px; left: 120px; font-size: 14px; font-weight: bold; }
        .expiry-date { position: absolute; top: 810px; left: 120px; font-size: 14px; font-weight: bold; }

        .qr-code { position: absolute; top: 760px; left: 600px; width: 100px; height: 100px; }
        .type-logo { position: absolute; top: 760px; left: 350px; width: 100px; height: 100px; }

        .type-logo-text { padding: 10px; border: 2px solid #1a5276; border-radius: 50%; color: #1a5276; font-weight: bold; font-size: 20px; width: 80px; height: 80px; line-height: 80px; text-align: center; }
    </style>
</head>
<body>
    <div class="certificate-container"></div>
    <div class="content">
        <div class="cert-number">No: ' . htmlspecialchars($cert->certificate_number) . '</div>

        <div class="company-name">' . htmlspecialchars($cert->company_name) . '</div>

        <div class="company-address">' . nl2br(htmlspecialchars($cert->address)) . '</div>

        <div class="certification-text">has been assessed and found to conform to the requirements of</div>

        <div class="standard">' . htmlspecialchars(explode("—", $cert->iso_standard)[0]) . '</div>

        <div class="scope">
            <strong>For the following scope:</strong><br>
            ' . nl2br(htmlspecialchars($cert->scope)) . '
        </div>

        <div class="issue-date">Issue Date: ' . date("d F Y", strtotime($cert->issue_date)) . '</div>
        <div class="expiry-date">Valid Until: ' . date("d F Y", strtotime($cert->expiry_date)) . '</div>

        <img src="' . $qr_image . '" class="qr-code">

        <div class="type-logo">
            <div class="type-logo-text">
                ' . htmlspecialchars($cert->main_type) . '
            </div>
        </div>
    </div>
</body>
</html>
';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdf_filename = $cert->certificate_number . ".pdf";
$pdf_filepath = "../uploads/certificates/" . $pdf_filename;
file_put_contents($pdf_filepath, $dompdf->output());

// Update DB with PDF Path
$cert->pdf_file = $pdf_filename;

// Execute direct update for pdf and qr paths
$query = "UPDATE certificates SET qr_code = :qr, pdf_file = :pdf WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":qr", $cert->qr_code);
$stmt->bindParam(":pdf", $cert->pdf_file);
$stmt->bindParam(":id", $cert->id);
$stmt->execute();

$_SESSION['message'] = "Certificate PDF and QR Code generated successfully.";
header("Location: certificates.php");
exit();
?>
