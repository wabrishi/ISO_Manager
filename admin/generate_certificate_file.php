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
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "Helvetica", sans-serif; text-align: center; color: #333; margin: 0; padding: 20px; }
        .cert-container { border: 10px solid #1a365d; padding: 40px; position: relative; height: 900px; }
        .title { font-size: 48px; font-weight: bold; color: #1a365d; margin-bottom: 10px; text-transform: uppercase; }
        .subtitle { font-size: 24px; color: #666; margin-bottom: 40px; }
        .presented-to { font-size: 20px; margin-bottom: 10px; }
        .company-name { font-size: 36px; font-weight: bold; color: #2c5282; margin-bottom: 20px; border-bottom: 2px solid #ccc; display: inline-block; padding-bottom: 5px; }
        .details { font-size: 18px; margin-bottom: 30px; line-height: 1.6; }
        .standard { font-size: 28px; font-weight: bold; color: #2b6cb0; margin: 20px 0; }
        .footer { position: absolute; bottom: 50px; width: 100%; left: 0; }
        .footer-table { width: 100%; padding: 0 50px; }
        .footer-td { width: 33%; text-align: center; vertical-align: bottom; }
        .signature-line { border-top: 1px solid #333; margin-top: 10px; padding-top: 5px; font-weight: bold; }
        .qr-code { width: 120px; height: 120px; }
        .cert-number { font-size: 14px; color: #888; position: absolute; top: 20px; right: 30px; }
    </style>
</head>
<body>
    <div class="cert-container">
        <div class="cert-number">Certificate No: ' . htmlspecialchars($cert->certificate_number) . '</div>

        <div class="title">Certificate of Registration</div>
        <div class="subtitle">This is to certify that the Management System of</div>

        <div class="company-name">' . htmlspecialchars($cert->company_name) . '</div>
        <div class="details">
            ' . nl2br(htmlspecialchars($cert->address)) . '
        </div>

        <div class="presented-to">has been assessed and found to conform to the requirements of</div>

        <div class="standard">' . htmlspecialchars($cert->iso_standard) . '</div>

        <div class="details">
            <strong>For the following scope:</strong><br>
            ' . nl2br(htmlspecialchars($cert->scope)) . '
        </div>

        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td class="footer-td">
                        <div style="margin-bottom:10px;">
                            <strong>Issue Date:</strong> ' . date("F d, Y", strtotime($cert->issue_date)) . '<br>
                            <strong>Expiry Date:</strong> ' . date("F d, Y", strtotime($cert->expiry_date)) . '
                        </div>
                    </td>
                    <td class="footer-td">
                        <img src="' . $qr_image . '" class="qr-code">
                        <div style="font-size: 12px; margin-top: 5px;">Scan to Verify</div>
                    </td>
                    <td class="footer-td">
                        <div style="height: 60px;"></div>
                        <div class="signature-line">Authorized Signature</div>
                    </td>
                </tr>
            </table>
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
