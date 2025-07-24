<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

class BusinessCardPDF {
    private $contact;
    private $qrCodeUrl;
    
    public function __construct($contact) {
        $this->contact = $contact;
        $this->qrCodeUrl = $this->generateQRCodeUrl();
    }
    
    private function generateQRCodeUrl() {
        // vCard-Daten für QR-Code
        $vcard = "BEGIN:VCARD\nVERSION:3.0\n";
        $vcard .= "FN:" . $this->contact['vorname'] . " " . $this->contact['nachname'] . "\n";
        
        if (!empty($this->contact['telefon'])) {
            $vcard .= "TEL:" . $this->contact['telefon'] . "\n";
        }
        if (!empty($this->contact['email'])) {
            $vcard .= "EMAIL:" . $this->contact['email'] . "\n";
        }
        if (!empty($this->contact['firma'])) {
            $vcard .= "ORG:" . $this->contact['firma'] . "\n";
        }
        if (!empty($this->contact['position'])) {
            $vcard .= "TITLE:" . $this->contact['position'] . "\n";
        }
        
        $vcard .= "END:VCARD";
        
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&format=png&data=' . urlencode($vcard);
    }
    
    public function generateHTML() {
        $name = htmlspecialchars($this->contact['vorname'] . ' ' . $this->contact['nachname']);
        $position = htmlspecialchars($this->contact['position'] ?? '');
        $firma = htmlspecialchars($this->contact['firma'] ?? '');
        $telefon = htmlspecialchars($this->contact['telefon'] ?? '');
        $email = htmlspecialchars($this->contact['email'] ?? '');
        $website = htmlspecialchars($this->contact['website'] ?? '');
        
        // Foto-URL verarbeiten
        $fotoHtml = '';
        if (!empty($this->contact['foto_url'])) {
            $fotoUrl = $this->contact['foto_url'];
            if (!filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
                $fotoUrl = '../' . ltrim($fotoUrl, '/');
            }
            $fotoHtml = '<img src="' . $fotoUrl . '" class="contact-photo" alt="Foto">';
        }
        
        return '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitenkarte - ' . $name . '</title>
    <style>
        @page { size: 85mm 54mm; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8px; color: #333; }
        
        .business-cards {
            display: flex;
            flex-direction: column;
            gap: 5mm;
            padding: 10mm;
        }
        
        .business-card {
            width: 85mm;
            height: 54mm;
            border: 1px solid #ddd;
            border-radius: 3mm;
            overflow: hidden;
            page-break-inside: avoid;
            position: relative;
        }
        
        /* Sicherheitszone für Druckränder */
        .safe-zone {
            padding: 2mm;
            height: calc(100% - 4mm);
        }
        
        .front-side {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 6mm;
            position: relative;
            height: 100%;
        }
        
        .contact-photo {
            width: 14mm;
            height: 14mm;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            float: left;
            margin-right: 4mm;
            margin-bottom: 2mm;
        }
        
        .name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 1mm;
            clear: both;
        }
        
        .position {
            font-size: 9px;
            margin-bottom: 1mm;
            opacity: 0.9;
        }
        
        .company {
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 3mm;
            color: #fbbf24;
        }
        
        .contact-details {
            font-size: 7px;
            line-height: 1.4;
        }
        
        .contact-details div {
            margin-bottom: 0.8mm;
        }
        
        .logo-front {
            position: absolute;
            top: 3mm;
            right: 3mm;
            font-size: 8px;
            text-align: right;
            background: rgba(255,255,255,0.1);
            padding: 2mm;
            border-radius: 2mm;
        }
        
        .back-side {
            background: white;
            padding: 6mm 6mm 10mm 6mm;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .main-logo {
            text-align: center;
            color: #1e3a8a;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .qr-section {
            text-align: center;
            margin: 1mm 0;
        }
        
        .qr-code {
            width: 16mm;
            height: 16mm;
            border: 1px solid #e5e7eb;
        }
        
        .company-info {
            text-align: center;
            color: #1e3a8a;
            font-size: 6px;
            line-height: 1.2;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .company-info div {
            margin-bottom: 0.5mm;
        }
        
        .services {
            font-size: 5px;
            color: #666;
            text-align: center;
            margin-top: 1mm;
            margin-bottom: 4mm;
            line-height: 1.1;
            padding-bottom: 2mm;
        }
        
        @media print {
            body { -webkit-print-color-adjust: exact !important; }
            .business-card { page-break-after: always; }
            .business-card:last-child { page-break-after: auto; }
        }
        
        /* Zusätzliche Regeln für bessere Textumbrüche */
        .company-info {
            hyphens: auto;
            -webkit-hyphens: auto;
            -ms-hyphens: auto;
        }
        
        .company-info div {
            max-width: 100%;
            overflow-wrap: anywhere;
        }
    </style>
</head>
<body>
    <div class="business-cards">
        <!-- VORDERSEITE -->
        <div class="business-card">
            <div class="front-side">
                <div class="logo-front">
                    <div style="font-weight: bold; color: white;">SCHÜTZ</div>
                    <div style="font-size: 4px; color: #fbbf24;">FÜR IHRE SICHERHEIT</div>
                </div>
                
                ' . $fotoHtml . '
                
                <div class="name">' . $name . '</div>
                ' . (!empty($position) ? '<div class="position">' . $position . '</div>' : '') . '
                ' . (!empty($firma) ? '<div class="company">' . $firma . '</div>' : '') . '
                
                <div class="contact-details">
                    ' . (!empty($telefon) ? '<div>📞 ' . $telefon . '</div>' : '') . '
                    ' . (!empty($email) ? '<div>✉️ ' . $email . '</div>' : '') . '
                    ' . (!empty($website) ? '<div>🌐 ' . $website . '</div>' : '') . '
                </div>
            </div>
        </div>
        
        <!-- RÜCKSEITE -->
        <div class="business-card">
            <div class="back-side">
                <div class="main-logo">
                    <div style="color: #1e3a8a; font-size: 18px;">SCHÜTZ</div>
                    <div style="color: #dc2626; font-size: 8px;">SCHLÜSSEL- & SCHREINERSERVICE</div>
                </div>
                
                <div class="qr-section">
                    <img src="' . $this->qrCodeUrl . '" class="qr-code" alt="QR Code">
                    <div style="font-size: 5px; color: #666; margin-top: 0.5mm;">Kontakt scannen</div>
                </div>
                
                <div class="company-info">
                    <div style="font-weight: bold; color: #dc2626; margin-bottom: 0.5mm; font-size: 6px;">24/7 NOTFALLDIENST</div>
                    <div style="margin-bottom: 0.3mm;">Winterthur & Bülach</div>
                    <div style="margin-bottom: 0.3mm;">+41 52 560 14 40</div>
                    <div style="margin-bottom: 0.3mm; word-break: break-all;">info@schuetz-schluesselservice.ch</div>
                    <div style="margin-bottom: 0.5mm;">www.schuetz-schluesselservice.ch</div>
                    
                    <div class="services">
                        <div style="margin-bottom: 0.5mm;">• Schlüsseldienst • Schreinerarbeiten •</div>
                        <div style="margin-bottom: 2mm;">• Sicherheitstechnik • Notöffnungen •</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    }
}

// Hauptlogik
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ungültige Kontakt-ID']);
    exit;
}

try {
    $contactManager = new ContactManager();
    $contact = $contactManager->getContactById($_GET['id']);

    if (!$contact) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Kontakt nicht gefunden']);
        exit;
    }

    $businessCard = new BusinessCardPDF($contact);
    
    // Vorschau-Modus
    if (isset($_GET['preview'])) {
        header('Content-Type: text/html; charset=utf-8');
        echo $businessCard->generateHTML();
        exit;
    }
    
    // HTML-Download für PDF-Konvertierung
    $html = $businessCard->generateHTML();
    $filename = 'visitenkarte_' . preg_replace('/[^a-zA-Z0-9]/', '_', $contact['vorname'] . '_' . $contact['nachname']) . '.html';
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo $html;

} catch (Exception $e) {
    error_log('Business Card Generation Error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Fehler beim Generieren der Visitenkarte: ' . $e->getMessage()]);
}
?>
