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
$template_path = realpath(__DIR__ . '/../assets/images/certificate_template.jpg');
$template_data = base64_encode(file_get_contents($template_path));
$template_src = 'data:image/jpeg;base64,' . $template_data;

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 0px; }
        body { font-family: "Helvetica", sans-serif; text-align: center; color: #333; margin: 0; padding: 0; }
        .cert-container {
            position: relative;
            width: 100%;
            height: 100%;
            background-image: url("' . $template_src . '");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .content-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding-top: 150px;
            box-sizing: border-box;
        }

        .cert-number { font-size: 12px; position: absolute; top: 660px; left: 240px; }
        .issue-date { font-size: 12px; position: absolute; top: 700px; left: 240px; }
        .expiry-date { font-size: 12px; position: absolute; top: 720px; left: 240px; }

        .company-name { font-size: 24px; font-weight: bold; margin-top: 50px; }
        .address { font-size: 14px; margin-top: 10px; padding: 0 100px; line-height: 1.5; }

        .standard-section { margin-top: 100px; }
        .standard { font-size: 36px; font-weight: bold; color: #1a5276; margin: 5px 0; }
        .main-type { font-size: 16px; font-weight: bold; color: #333; margin-top: 5px; }

        .scope { font-size: 14px; margin-top: 30px; padding: 0 150px; font-weight: bold; }

        .qr-code { position: absolute; top: 50px; left: 50px; width: 80px; height: 80px; }
        .status-url { font-size: 10px; position: absolute; top: 750px; left: 80px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="cert-container">
        <div class="content-overlay">
            <img src="' . $qr_image . '" class="qr-code">

            <div class="company-name">' . htmlspecialchars($cert->company_name) . '</div>
            <div class="address">' . nl2br(htmlspecialchars($cert->address)) . '</div>

            <div class="standard-section">
                <div class="standard">' . htmlspecialchars(explode("—", $cert->iso_standard)[0]) . '</div>
                <div class="main-type">Type: ' . htmlspecialchars($cert->main_type) . '</div>
            </div>

            <div class="scope">
                ( ' . nl2br(htmlspecialchars($cert->scope)) . ' )
            </div>

            <div class="cert-number">: ' . htmlspecialchars($cert->certificate_number) . '</div>
            <div class="issue-date">: ' . date("d - m - Y", strtotime($cert->issue_date)) . '</div>
            <div class="expiry-date">: ' . date("d - m - Y", strtotime($cert->expiry_date)) . '</div>

            <div class="status-url">"http://' . $_SERVER['HTTP_HOST'] . '/verify.php"</div>
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
