/**
 * Tenuta Manarese - main.js
 * Script principale per funzionalità interattive del sito
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inizializza tutte le funzionalità
    initFormValidation();
    initBackToTop();
    initProdottiFilter();
    // Aggiorna il contatore carrello all'avvio
    updateCartCounter();
});

/**
 * Funzione globale per aggiornare il contatore del carrello
 */
function updateCartCounter() {
    fetch('carrello.php?action=get_count')
        .then(response => response.json())
        .then(data => {
            // Trova tutti i badge del carrello nella pagina
            const cartBadges = document.querySelectorAll('.cart-count, .badge.bg-danger');
            const cartLinks = document.querySelectorAll('.cart-link, [href*="carrello"]');
            
            cartBadges.forEach(badge => {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            });
            
            // Se non ci sono badge esistenti, prova a crearli
            if (cartBadges.length === 0 && cartLinks.length > 0) {
                cartLinks.forEach(link => {
                    if (!link.querySelector('.badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-danger cart-count';
                        badge.style.marginLeft = '5px';
                        badge.textContent = data.count;
                        badge.style.display = data.count > 0 ? 'inline' : 'none';
                        link.appendChild(badge);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Errore nell\'aggiornamento del contatore carrello:', error);
        });
}
/**
 * Validazione avanzata del form di contatto
 */
function initFormValidation() {
    const form = document.querySelector('form');
    if (!form) return;
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        const nome = document.getElementById('nome');
        const email = document.getElementById('email');
        const telefono = document.getElementById('telefono');
        const messaggio = document.getElementById('messaggio');
        const privacy = document.getElementById('privacy');
        
        // Rimuovi messaggi di errore precedenti
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Valida nome (almeno 2 caratteri)
        if (!nome.value || nome.value.length < 2) {
            showError(nome, 'Inserisci un nome valido (almeno 2 caratteri)');
            isValid = false;
        }
        
        // Valida email (formato corretto)
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value || !emailRegex.test(email.value)) {
            showError(email, 'Inserisci un indirizzo email valido');
            isValid = false;
        }
        
        // Valida telefono (opzionale ma deve essere valido se inserito)
        if (telefono.value && !/^[0-9+\s()-]{6,20}$/.test(telefono.value)) {
            showError(telefono, 'Inserisci un numero di telefono valido');
            isValid = false;
        }
        
        // Valida messaggio (almeno 10 caratteri)
        if (!messaggio.value || messaggio.value.length < 10) {
            showError(messaggio, 'Il messaggio deve contenere almeno 10 caratteri');
            isValid = false;
        }
        
        // Valida privacy
        if (!privacy.checked) {
            const privacyParent = privacy.parentElement;
            privacyParent.classList.add('text-danger');
            showError(privacy, 'Deve accettare la privacy policy');
            isValid = false;
        }
        
        if (!isValid) {
            event.preventDefault();
        } else {
            // Effetto di caricamento durante l'invio
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Invio in corso...';
            submitBtn.disabled = true;
        }
        
        function showError(element, message) {
            element.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message invalid-feedback';
            errorDiv.textContent = message;
            element.parentNode.appendChild(errorDiv);
        }
    });
}

/**
 * Pulsante "Torna su" che appare durante lo scroll
 */
function initBackToTop() {
    // Crea il bottone
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.className = 'back-to-top';
    backToTopBtn.setAttribute('aria-label', 'Torna in cima');
    
    // Stile CSS inline
    backToTopBtn.style.position = 'fixed';
    backToTopBtn.style.bottom = '20px';
    backToTopBtn.style.right = '20px';
    backToTopBtn.style.width = '50px';
    backToTopBtn.style.height = '50px';
    backToTopBtn.style.borderRadius = '50%';
    backToTopBtn.style.backgroundColor = 'var(--color-secondary)';
    backToTopBtn.style.color = 'white';
    backToTopBtn.style.border = 'none';
    backToTopBtn.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    backToTopBtn.style.cursor = 'pointer';
    backToTopBtn.style.display = 'none';
    backToTopBtn.style.opacity = '0';
    backToTopBtn.style.transition = 'opacity 0.3s, transform 0.3s';
    backToTopBtn.style.zIndex = '99';
    
    document.body.appendChild(backToTopBtn);
    
    // Mostra/nascondi il bottone durante lo scroll
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'block';
            setTimeout(() => {
                backToTopBtn.style.opacity = '1';
            }, 50);
        } else {
            backToTopBtn.style.opacity = '0';
            setTimeout(() => {
                backToTopBtn.style.display = 'none';
            }, 300);
        }
    });
    
    // Funzionalità di scroll verso l'alto
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Filtro prodotti nella pagina prodotti
 */
function initProdottiFilter() {
    const productSection = document.querySelector('.container .row.g-4');
    if (!productSection || !document.querySelector('.product-card')) return;
    
    // Crea il filtro
    const filterContainer = document.createElement('div');
    filterContainer.className = 'product-filter text-center mb-5';
    filterContainer.innerHTML = `
        <div class="btn-group" role="group" aria-label="Filtro prodotti">
            <button type="button" class="btn btn-outline-filtro active" data-filter="all">Tutti</button>
            <button type="button" class="btn btn-outline-filtro" data-filter="bianco">Vini Bianchi</button>
            <button type="button" class="btn btn-outline-filtro" data-filter="rosso">Vini Rossi</button>
            <button type="button" class="btn btn-outline-filtro" data-filter="frizzante">Frizzanti</button>
        </div>
    `;
    
    // Inserisci il filtro prima della sezione prodotti
    const sectionTitle = document.querySelector('.section-title.text-center');
    if (sectionTitle) {
        sectionTitle.parentNode.insertBefore(filterContainer, sectionTitle.nextSibling);
    }
    
    // Aggiungi categorie ai prodotti in base al testo
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        const info = card.querySelector('.product-info').textContent.toLowerCase();
        if (info.includes('bianco')) card.dataset.category = 'bianco';
        else if (info.includes('rosso')) card.dataset.category = 'rosso';
        
        if (info.includes('frizzante') || info.includes('spumante')) {
            if (card.dataset.category) {
                card.dataset.category += ' frizzante';
            } else {
                card.dataset.category = 'frizzante';
            }
        }
        
        // Imposta "all" come categoria predefinita
        if (!card.dataset.category) {
            card.dataset.category = 'all';
        }
    });
    
    // Aggiungi funzionalità di filtro
    const filterButtons = document.querySelectorAll('.product-filter button');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Rimuovi classe active da tutti i pulsanti
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Aggiungi classe active al pulsante cliccato
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            productCards.forEach(card => {
                if (filter === 'all' || card.dataset.category.includes(filter)) {
                    card.closest('.col-md-6').style.display = 'block';
                    // Animazione fade-in
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transition = 'opacity 0.4s ease-in-out';
                    }, 50);
                } else {
                    card.closest('.col-md-6').style.display = 'none';
                }
            });
        });
    });
}
