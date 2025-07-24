// Profile URL Helper
function goToProfile(vorname, nachname) {
    const cleanUrl = generateCleanUrl(vorname, nachname);
    const fallbackUrl = 'profile.php?name=' + encodeURIComponent(cleanUrl);
    
    // Versuche die saubere URL, falls sie nicht funktioniert, verwende Fallback
    const testUrl = cleanUrl;
    
    // Teste, ob saubere URLs funktionieren
    fetch(testUrl, {method: 'HEAD'})
        .then(response => {
            if (response.ok) {
                window.location.href = testUrl;
            } else {
                window.location.href = fallbackUrl;
            }
        })
        .catch(() => {
            // Falls fetch fehlschlägt, verwende Fallback
            window.location.href = fallbackUrl;
        });
}

function generateCleanUrl(vorname, nachname) {
    // Kombiniere Vor- und Nachname
    let combined = vorname.trim() + '-' + nachname.trim();
    
    // Entferne Leerzeichen und ersetze sie durch Bindestriche
    combined = combined.replace(/\s+/g, '-');
    
    // Entferne Sonderzeichen (behalte nur Buchstaben, Zahlen und Bindestriche)
    combined = combined.replace(/[^A-Za-zÀ-ÿ0-9\-]/g, '');
    
    // Mehrfache Bindestriche durch einzelne ersetzen
    combined = combined.replace(/-+/g, '-');
    
    // Bindestriche am Anfang und Ende entfernen
    combined = combined.replace(/^-+|-+$/g, '');
    
    return combined.toLowerCase();
}
