// cookie_manager.js - Sistema cookie GDPR minimal

class CookieManager {
    constructor() {
        this.cookieName = 'cookie_consent';
        this.init();
    }
    
    init() {
        if (!this.hasConsent()) {
            this.showBanner();
        }
    }
    
    hasConsent() {
        return this.getCookie(this.cookieName) !== null;
    }
    
    showBanner() {
        if (document.getElementById('cookie-banner')) return;
        
        const banner = document.createElement('div');
        banner.id = 'cookie-banner';
        banner.className = 'cookie-banner';
        
        banner.innerHTML = `
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <p class="mb-0">
                            <i class="fas fa-cookie-bite me-2"></i>
                            Utilizziamo cookie per migliorare la tua esperienza. 
                            <a href="privacy_policy.php" target="_blank">Leggi l'informativa</a>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-success btn-sm me-2" onclick="cookieManager.acceptAll()">
                            Accetta
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="cookieManager.acceptNecessary()">
                            Solo necessari
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(banner);
        setTimeout(() => banner.classList.add('show'), 100);
    }
    
    acceptAll() {
        this.setCookie(this.cookieName, 'accepted', 365);
        this.hideBanner();
    }
    
    acceptNecessary() {
        this.setCookie(this.cookieName, 'necessary', 365);
        this.hideBanner();
    }
    
    hideBanner() {
        const banner = document.getElementById('cookie-banner');
        if (banner) {
            banner.classList.remove('show');
            setTimeout(() => banner.remove(), 300);
        }
    }
    
    showPreferences() {
        this.hideBanner();
        alert('Per modificare le preferenze cookie, vai su Privacy Policy nel footer.');
    }
    
    // Metodi di utilità per i cookie
    setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    }
    
    getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
}

// Inizializza quando il DOM è pronto
let cookieManager;
document.addEventListener('DOMContentLoaded', function() {
    cookieManager = new CookieManager();
});
