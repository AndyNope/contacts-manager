// Admin JavaScript Funktionalität - Commercial Version

// Profilbild hochladen - Placeholder für neue API
function uploadProfileImage(input) {
    const file = input.files[0];
    if (!file) return;
    
    console.log('Image upload will be implemented with new multi-tenant backend');
    
    // TODO: Implement new upload API for multi-tenant system
    // This will need to handle company-specific uploads
    alert('Image upload functionality will be available in the final commercial version');
}

// Profilbild entfernen
function removeProfileImage() {
    document.getElementById('foto_url').value = '';
    document.getElementById('foto_file').value = '';
    document.getElementById('uploadMessage').innerHTML = '';
    updateImagePreview();
}

// Profilbild-Vorschau aktualisieren
function updateImagePreview() {
    const fotoUrl = document.getElementById('foto_url').value;
    const avatarPreview = document.getElementById('avatarPreview');
    const removeBtn = document.getElementById('removeImageBtn');
    const vorname = document.getElementById('vorname').value;
    const nachname = document.getElementById('nachname').value;
    
    if (fotoUrl && fotoUrl.trim() !== '') {
        // Prüfen ob URL absolut ist oder relativer Pfad
        let imageUrl = fotoUrl;
        if (!fotoUrl.startsWith('http://') && !fotoUrl.startsWith('https://') && !fotoUrl.startsWith('data:')) {
            // Relativer Pfad - als relativer Pfad verwenden
            imageUrl = fotoUrl;
        }
        
        // URL-Bild anzeigen
        avatarPreview.style.backgroundImage = `url(${imageUrl})`;
        avatarPreview.style.backgroundSize = 'cover';
        avatarPreview.style.backgroundPosition = 'center';
        avatarPreview.innerHTML = '';
        removeBtn.style.display = 'inline-block';
    } else {
        // Initialen anzeigen
        avatarPreview.style.backgroundImage = 'none';
        const initials = (vorname.charAt(0) + nachname.charAt(0)).toUpperCase();
        avatarPreview.innerHTML = initials || '<i class="bi bi-person"></i>';
        removeBtn.style.display = 'none';
    }
}

// Event Listener für Eingabefelder
document.addEventListener('DOMContentLoaded', function() {
    // Profilbild-Vorschau bei Änderungen aktualisieren
    document.getElementById('foto_url').addEventListener('input', updateImagePreview);
    document.getElementById('vorname').addEventListener('input', updateImagePreview);
    document.getElementById('nachname').addEventListener('input', updateImagePreview);
    
    // Drag & Drop für Bild-Upload
    const fileInput = document.getElementById('foto_file');
    const imagePreview = document.getElementById('imagePreview');
    
    // Drag Over
    imagePreview.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.backgroundColor = 'rgba(30, 58, 138, 0.1)';
        this.style.border = '2px dashed var(--primary-color)';
    });
    
    // Drag Leave
    imagePreview.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.backgroundColor = '';
        this.style.border = '';
    });
    
    // Drop
    imagePreview.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.backgroundColor = '';
        this.style.border = '';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                fileInput.files = files;
                uploadProfileImage(fileInput);
            }
        }
    });
    
    // Initiale Vorschau setzen
    updateImagePreview();
});

// Kontakt bearbeiten
function editContact(contact) {
    // Form-Titel und Aktion ändern
    document.getElementById('formTitle').textContent = 'Kontakt bearbeiten';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('contactId').value = contact.id;
    document.getElementById('submitText').textContent = 'Kontakt aktualisieren';
    
    // Formular mit Kontaktdaten füllen
    document.getElementById('vorname').value = contact.vorname || '';
    document.getElementById('nachname').value = contact.nachname || '';
    document.getElementById('telefon').value = contact.telefon || '';
    document.getElementById('email').value = contact.email || '';
    document.getElementById('position').value = contact.position || '';
    document.getElementById('firma').value = contact.firma || '';
    document.getElementById('adresse').value = contact.adresse || '';
    document.getElementById('plz').value = contact.plz || '';
    document.getElementById('ort').value = contact.ort || '';
    document.getElementById('land').value = contact.land || 'Schweiz';
    document.getElementById('website').value = contact.website || '';
    document.getElementById('notizen').value = contact.notizen || '';
    document.getElementById('foto_url').value = contact.foto_url || '';
    
    // Upload-Nachrichten zurücksetzen
    document.getElementById('uploadMessage').innerHTML = '';
    
    // Profilbild-Vorschau aktualisieren
    updateImagePreview();
    
    // Zum Formular scrollen
    document.querySelector('.contact-form').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
    
    // Fokus auf Vorname-Feld setzen
    setTimeout(() => {
        document.getElementById('vorname').focus();
    }, 500);
}

// Formular zurücksetzen
function resetForm() {
    // Form-Titel und Aktion zurücksetzen
    document.getElementById('formTitle').textContent = 'Neuen Kontakt hinzufügen';
    document.getElementById('formAction').value = 'add';
    document.getElementById('contactId').value = '';
    document.getElementById('submitText').textContent = 'Kontakt hinzufügen';
    
    // Formular zurücksetzen
    document.getElementById('contactForm').reset();
    
    // Standard-Land setzen
    document.getElementById('land').value = 'Schweiz';
    
    // Upload-Nachrichten zurücksetzen
    document.getElementById('uploadMessage').innerHTML = '';
    
    // Profilbild-Vorschau zurücksetzen
    updateImagePreview();
}

// Kontakt löschen
function deleteContact(contactId, contactName) {
    document.getElementById('deleteContactId').value = contactId;
    document.getElementById('deleteContactName').textContent = contactName;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Event Listeners beim Laden der Seite
document.addEventListener('DOMContentLoaded', function() {
    // Automatisches Ausblenden von Alerts nach 5 Sekunden
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
    
    // Form-Validierung verbessern
    const form = document.getElementById('contactForm');
    form.addEventListener('submit', function(e) {
        const vorname = document.getElementById('vorname').value.trim();
        const nachname = document.getElementById('nachname').value.trim();
        
        if (!vorname || !nachname) {
            e.preventDefault();
            alert('Bitte füllen Sie mindestens Vor- und Nachname aus.');
            return false;
        }
        
        // E-Mail-Validierung
        const email = document.getElementById('email').value.trim();
        if (email && !isValidEmail(email)) {
            e.preventDefault();
            alert('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
            return false;
        }
        
        // Website-Validierung
        const website = document.getElementById('website').value.trim();
        if (website && !isValidUrl(website)) {
            e.preventDefault();
            alert('Bitte geben Sie eine gültige Website-URL ein (mit http:// oder https://).');
            return false;
        }
    });
});

// Hilfsfunktionen
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

// Telefonnummer formatieren für Schweiz
document.getElementById('telefon').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Nur Zahlen
    
    // Schweizer Telefonnummer-Formatierung
    if (value.startsWith('41')) {
        value = '+41 ' + value.substring(2);
    } else if (value.startsWith('0')) {
        value = '+41 ' + value.substring(1);
    } else if (!value.startsWith('+41') && value.length > 0) {
        value = '+41 ' + value;
    }
    
    // Formatierung für bessere Lesbarkeit (Schweizer Format)
    if (value.startsWith('+41 ')) {
        const rest = value.substring(4);
        if (rest.length > 2) {
            // Format: +41 XX XXX XX XX
            value = '+41 ' + rest.substring(0, 2) + ' ' + rest.substring(2);
        }
        if (rest.length > 5) {
            value = '+41 ' + rest.substring(0, 2) + ' ' + rest.substring(2, 5) + ' ' + rest.substring(5);
        }
        if (rest.length > 7) {
            value = '+41 ' + rest.substring(0, 2) + ' ' + rest.substring(2, 5) + ' ' + rest.substring(5, 7) + ' ' + rest.substring(7, 9);
        }
    }
    
    e.target.value = value.trim();
});

// Website-URL automatisch vervollständigen
document.getElementById('website').addEventListener('blur', function(e) {
    let url = e.target.value.trim();
    if (url && !url.startsWith('http://') && !url.startsWith('https://')) {
        e.target.value = 'https://' + url;
    }
});

// PDF-Visitenkarte herunterladen
function downloadBusinessCard(contactId) {
    if (!contactId) {
        alert('Ungültige Kontakt-ID');
        return;
    }
    
    // Loading-State anzeigen
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Generiere...';
    button.disabled = true;
    
    // PDF-Download starten
    const url = `api/generate_business_card.php?id=${contactId}`;
    
    // Neues Fenster öffnen für Download
    const downloadFrame = document.createElement('iframe');
    downloadFrame.style.display = 'none';
    downloadFrame.src = url;
    document.body.appendChild(downloadFrame);
    
    // Button nach kurzer Zeit zurücksetzen
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
        
        // Iframe nach Download entfernen
        setTimeout(() => {
            if (downloadFrame.parentNode) {
                downloadFrame.parentNode.removeChild(downloadFrame);
            }
        }, 5000);
    }, 2000);
}

// PDF-Visitenkarte Vorschau
function previewBusinessCard(contactId) {
    if (!contactId) {
        alert('Ungültige Kontakt-ID');
        return;
    }
    
    const url = `api/generate_business_card.php?id=${contactId}&preview=1`;
    window.open(url, '_blank');
}
