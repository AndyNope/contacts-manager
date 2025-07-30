<?php
session_start();

class Database {
    private $host = 'localhost';
    private $user = 'kontaktverwaltung';
    private $pass = 'Kontakt&Verwaltung';
    private $dbname = 'kontaktverwaltung';
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

class ContactManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAllContacts() {
        $stmt = $this->db->prepare("SELECT * FROM kontakte WHERE is_active = TRUE ORDER BY nachname, vorname");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getContactById($id) {
        $stmt = $this->db->prepare("SELECT * FROM kontakte WHERE id = ? AND is_active = TRUE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function addContact($data) {
        $stmt = $this->db->prepare("INSERT INTO kontakte (vorname, nachname, telefon, email, position, firma, adresse, plz, ort, land, website, notizen, foto_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss", 
            $data['vorname'], $data['nachname'], $data['telefon'], $data['email'],
            $data['position'], $data['firma'], $data['adresse'], $data['plz'],
            $data['ort'], $data['land'], $data['website'], $data['notizen'], $data['foto_url']
        );
        return $stmt->execute();
    }
    
    public function updateContact($id, $data) {
        $stmt = $this->db->prepare("UPDATE kontakte SET vorname=?, nachname=?, telefon=?, email=?, position=?, firma=?, adresse=?, plz=?, ort=?, land=?, website=?, notizen=?, foto_url=? WHERE id=?");
        $stmt->bind_param("sssssssssssssi", 
            $data['vorname'], $data['nachname'], $data['telefon'], $data['email'],
            $data['position'], $data['firma'], $data['adresse'], $data['plz'],
            $data['ort'], $data['land'], $data['website'], $data['notizen'], $data['foto_url'], $id
        );
        return $stmt->execute();
    }
    
    public function deleteContact($id) {
        $stmt = $this->db->prepare("UPDATE kontakte SET is_active = FALSE WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function generateVCard($contact) {
        $vcard = "BEGIN:VCARD\r\n";
        $vcard .= "VERSION:3.0\r\n";
        $vcard .= "FN:" . $contact['vorname'] . " " . $contact['nachname'] . "\r\n";
        $vcard .= "N:" . $contact['nachname'] . ";" . $contact['vorname'] . ";;;\r\n";
        
        if (!empty($contact['telefon'])) {
            $vcard .= "TEL;TYPE=WORK,VOICE:" . $contact['telefon'] . "\r\n";
        }
        
        if (!empty($contact['email'])) {
            $vcard .= "EMAIL;TYPE=WORK:" . $contact['email'] . "\r\n";
        }
        
        if (!empty($contact['position'])) {
            $vcard .= "TITLE:" . $contact['position'] . "\r\n";
        }
        
        if (!empty($contact['firma'])) {
            $vcard .= "ORG:" . $contact['firma'] . "\r\n";
        }
        
        if (!empty($contact['adresse']) && !empty($contact['plz']) && !empty($contact['ort'])) {
            $vcard .= "ADR;TYPE=WORK:;;" . $contact['adresse'] . ";" . $contact['ort'] . ";;" . $contact['plz'] . ";" . $contact['land'] . "\r\n";
        }
        
        if (!empty($contact['website'])) {
            $vcard .= "URL:" . $contact['website'] . "\r\n";
        }
        
        if (!empty($contact['notizen'])) {
            $vcard .= "NOTE:" . $contact['notizen'] . "\r\n";
        }
        
        $vcard .= "END:VCARD\r\n";
        
        return $vcard;
    }
}

// Admin-Authentifizierung
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

// Funktion um saubere URL zu generieren (nur definieren falls noch nicht vorhanden)
if (!function_exists('generateProfileUrl')) {
    function generateProfileUrl($vorname, $nachname) {
        // Leerzeichen entfernen und durch Bindestriche ersetzen
        $vorname = trim($vorname);
        $nachname = trim($nachname);
        
        // Deutsche Umlaute in ASCII-Äquivalente umwandeln
        $umlaut_map = array(
            'ä' => 'ae',
            'Ä' => 'ae',
            'ö' => 'oe', 
            'Ö' => 'oe',
            'ü' => 'ue',
            'Ü' => 'ue',
            'ß' => 'ss'
        );
        
        $vorname = strtr($vorname, $umlaut_map);
        $nachname = strtr($nachname, $umlaut_map);
        
        // Kombiniere Vor- und Nachname mit Bindestrich
        $combined = $vorname . '-' . $nachname;
        
        // Entferne Leerzeichen und ersetze sie durch Bindestriche
        $combined = preg_replace('/\s+/', '-', $combined);
        
        // Entferne alle Zeichen außer Buchstaben, Zahlen und Bindestriche (nur ASCII)
        $clean = preg_replace('/[^A-Za-z0-9\-]/', '', $combined);
        
        // Mehrfache Bindestriche durch einzelne ersetzen
        $clean = preg_replace('/-+/', '-', $clean);
        
        // Bindestriche am Anfang und Ende entfernen
        $clean = trim($clean, '-');
    
        return strtolower($clean);
    }
}

// Funktion um schöne Profile-URL zu generieren (für Links und QR-Codes)
if (!function_exists('generateCleanProfileUrl')) {
    function generateCleanProfileUrl($vorname, $nachname, $baseUrl = '') {
        if (empty($baseUrl)) {
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        }
        
        $profileName = generateProfileUrl($vorname, $nachname);
        return $baseUrl . '/profile/' . $profileName;
    }
}

?>
