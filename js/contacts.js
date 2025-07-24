// Kontakte JavaScript Funktionalität

// Globale Variablen
let selectedContacts = new Set();
let currentContactId = null;

// Event Listeners beim Laden der Seite
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    updateDownloadBar();
});

// Such-Funktionalität
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        filterContacts(searchTerm);
    });
}

function filterContacts(searchTerm) {
    const contactItems = document.querySelectorAll('.contact-item');
    let visibleCount = 0;
    
    contactItems.forEach(item => {
        const name = item.dataset.name || '';
        const position = item.dataset.position || '';
        const company = item.dataset.company || '';
        
        const isVisible = name.includes(searchTerm) || 
                         position.includes(searchTerm) || 
                         company.includes(searchTerm);
        
        if (isVisible) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Zeige "Keine Ergebnisse" wenn nichts gefunden wurde
    const noResults = document.getElementById('noResults');
    if (visibleCount === 0 && searchTerm.length > 0) {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
    }
}

// Auswahl-Funktionen
function selectAll() {
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    checkboxes.forEach(checkbox => {
        const contactItem = checkbox.closest('.contact-item');
        if (contactItem.style.display !== 'none') {
            checkbox.checked = true;
            selectedContacts.add(checkbox.value);
        }
    });
    updateDownloadBar();
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectedContacts.clear();
    updateDownloadBar();
}

function updateDownloadBar() {
    const checkboxes = document.querySelectorAll('.contact-checkbox:checked');
    selectedContacts.clear();
    
    checkboxes.forEach(checkbox => {
        selectedContacts.add(checkbox.value);
    });
    
    const downloadBar = document.getElementById('downloadBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedContacts.size > 0) {
        downloadBar.style.display = 'block';
        selectedCount.textContent = `${selectedContacts.size} Kontakt${selectedContacts.size !== 1 ? 'e' : ''} ausgewählt`;
    } else {
        downloadBar.style.display = 'none';
    }
}

// Kontakt Details anzeigen
function showContactDetails(contactId) {
    currentContactId = contactId;
    
    fetch(`api/get_contact.php?id=${contactId}`)
        .then(response => response.json())
        .then(contact => {
            if (contact.error) {
                alert('Fehler beim Laden der Kontaktdaten: ' + contact.error);
                return;
            }
            
            displayContactInModal(contact);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Fehler beim Laden der Kontaktdaten');
        });
}

function displayContactInModal(contact) {
    const modalBody = document.getElementById('contactModalBody');
    
    const avatar = contact.vorname.charAt(0).toUpperCase() + contact.nachname.charAt(0).toUpperCase();
    
    // Responsive Avatar-Größe basierend auf Bildschirmgröße
    const isMobile = window.innerWidth < 768;
    const avatarSize = isMobile ? '80px' : '100px';
    const fontSize = isMobile ? '1.5rem' : '2rem';
    
    // Avatar HTML - mit oder ohne Bild
    let avatarHtml;
    if (contact.foto_url) {
        // Prüfen ob URL absolut ist oder relativer Pfad
        let imageUrl = contact.foto_url;
        if (!contact.foto_url.startsWith('http://') && !contact.foto_url.startsWith('https://') && !contact.foto_url.startsWith('data:')) {
            // Relativer Pfad - als relativer Pfad verwenden
            imageUrl = contact.foto_url;
        }
        
        avatarHtml = `<div class="contact-avatar mx-auto mb-3" style="width: ${avatarSize}; height: ${avatarSize}; font-size: ${fontSize}; background-image: url('${imageUrl}'); background-size: cover; background-position: center; border-radius: 50%; flex-shrink: 0;"></div>`;
    } else {
        avatarHtml = `<div class="contact-avatar mx-auto mb-3" style="width: ${avatarSize}; height: ${avatarSize}; font-size: ${fontSize}; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">${avatar}</div>`;
    }
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                ${avatarHtml}
                <h4>${contact.vorname} ${contact.nachname}</h4>
                ${contact.position ? `<p class="text-muted">${contact.position}</p>` : ''}
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Kontaktinformationen</h6>
                        
                        ${contact.telefon ? `
                            <div class="mb-3">
                                <label class="form-label small text-muted">TELEFON</label>
                                <div>
                                    <i class="bi bi-telephone me-2"></i>
                                    <a href="tel:${contact.telefon}" class="text-decoration-none">${contact.telefon}</a>
                                </div>
                            </div>
                        ` : ''}
                        
                        ${contact.email ? `
                            <div class="mb-3">
                                <label class="form-label small text-muted">E-MAIL</label>
                                <div>
                                    <i class="bi bi-envelope me-2"></i>
                                    <a href="mailto:${contact.email}" class="text-decoration-none">${contact.email}</a>
                                </div>
                            </div>
                        ` : ''}
                        
                        ${contact.website ? `
                            <div class="mb-3">
                                <label class="form-label small text-muted">WEBSITE</label>
                                <div>
                                    <i class="bi bi-globe me-2"></i>
                                    <a href="${contact.website}" target="_blank" class="text-decoration-none">${contact.website}</a>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Unternehmen & Adresse</h6>
                        
                        ${contact.firma ? `
                            <div class="mb-3">
                                <label class="form-label small text-muted">FIRMA</label>
                                <div>
                                    <i class="bi bi-building me-2"></i>
                                    ${contact.firma}
                                </div>
                            </div>
                        ` : ''}
                        
                        ${contact.adresse || contact.plz || contact.ort ? `
                            <div class="mb-3">
                                <label class="form-label small text-muted">ADRESSE</label>
                                <div>
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <div>
                                        ${contact.adresse || ''}<br>
                                        ${contact.plz || ''} ${contact.ort || ''}<br>
                                        ${contact.land || ''}
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                        
                        ${contact.notizen ? `
                            <div class="mb-3">
                                <label class="form-label small text-muted">NOTIZEN</label>
                                <div class="text-muted small">
                                    ${contact.notizen}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Modal anzeigen
    const modal = new bootstrap.Modal(document.getElementById('contactModal'));
    modal.show();
    
    // Download-Button Event Listener
    const downloadBtn = document.getElementById('downloadModalContact');
    downloadBtn.onclick = () => downloadContact(contact.id);
}

// Download-Funktionen
function downloadContact(contactId) {
    window.location.href = `api/download_vcard.php?id=${contactId}`;
}

function downloadSelected() {
    if (selectedContacts.size === 0) {
        alert('Bitte wählen Sie mindestens einen Kontakt aus.');
        return;
    }
    
    const contactIds = Array.from(selectedContacts).join(',');
    window.location.href = `api/download_multiple_vcards.php?ids=${contactIds}`;
}

// Hilfsfunktionen
function showSuccessMessage(message) {
    // Einfache Success-Nachricht (kann später durch Toast ersetzt werden)
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Automatisch nach 3 Sekunden entfernen
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

function showErrorMessage(message) {
    // Einfache Error-Nachricht (kann später durch Toast ersetzt werden)
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Automatisch nach 5 Sekunden entfernen
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
