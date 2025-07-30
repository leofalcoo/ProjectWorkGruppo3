/**
 * Gestione Login/Registrazione con AJAX
 * Tenuta Manarese
 */

document.addEventListener('DOMContentLoaded', function() {
    // Gestione form di login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }
    
    // Gestione form di registrazione
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
    
    // Gestione form password dimenticata
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', handleForgotPasswordSubmit);
    }
    
    // Controlla se ci sono messaggi URL da mostrare
    checkUrlMessages();
});

/**
 * Gestisce l'invio del form di login
 */
function handleLoginSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('login-message');
    
    // Mostra loading
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Accesso in corso...';
    submitBtn.disabled = true;
    
    fetch('process_login.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(messageDiv, data.message, 'success');
            // Reindirizza dopo 1 secondo
            setTimeout(() => {
                window.location.href = data.redirect || 'index.php';
            }, 1000);
        } else {
            showMessage(messageDiv, data.message, 'danger');
            // Ripristina il pulsante
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showMessage(messageDiv, 'Errore di connessione. Riprova più tardi.', 'danger');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Gestisce l'invio del form di registrazione
 */
function handleRegisterSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('register-message');
    
    // Controlla se le password corrispondono
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('register-confirm-password').value;
    
    if (password !== confirmPassword) {
        showMessage(messageDiv, 'Le password non corrispondono', 'danger');
        return;
    }
    
    // Mostra loading
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Registrazione in corso...';
    submitBtn.disabled = true;
    
    fetch('process_register.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(messageDiv, data.message, 'success');
            // Reset del form dopo successo
            form.reset();
            // Passa al tab login dopo 2 secondi
            setTimeout(() => {
                document.getElementById('login-tab').click();
                showMessage(document.getElementById('login-message'), 'Registrazione completata! Ora puoi effettuare il login.', 'success');
            }, 2000);
        } else {
            showMessage(messageDiv, data.message, 'danger');
        }
        
        // Ripristina il pulsante
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    })
    .catch(error => {
        console.error('Errore:', error);
        showMessage(messageDiv, 'Errore di connessione. Riprova più tardi.', 'danger');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Gestisce l'invio del form password dimenticata
 */
function handleForgotPasswordSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Mostra loading
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Invio in corso...';
    submitBtn.disabled = true;
    
    fetch('forgot_password.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Chiudi il modal e mostra messaggio di successo
            const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
            modal.hide();
            
            showMessage(document.getElementById('login-message'), data.message, 'success');
            form.reset();
        } else {
            // Mostra errore nel modal
            const modalBody = form.parentNode;
            const existingAlert = modalBody.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = data.message;
            modalBody.insertBefore(alertDiv, form);
        }
        
        // Ripristina il pulsante
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    })
    .catch(error => {
        console.error('Errore:', error);
        const modalBody = form.parentNode;
        const existingAlert = modalBody.querySelector('.alert');
        if (existingAlert) existingAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Errore di connessione. Riprova più tardi.';
        modalBody.insertBefore(alertDiv, form);
        
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Mostra un messaggio di successo o errore
 */
function showMessage(container, message, type) {
    container.className = `alert alert-${type}`;
    container.textContent = message;
    container.classList.remove('d-none');
    
    // Scroll al messaggio
    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Nascondi automaticamente dopo 5 secondi se è un messaggio di successo
    if (type === 'success') {
        setTimeout(() => {
            container.classList.add('d-none');
        }, 5000);
    }
}

/**
 * Controlla i parametri URL per messaggi da mostrare
 */
function checkUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('registered') === 'true') {
        showMessage(document.getElementById('login-message'), 'Registrazione completata! Controlla la tua email per attivare l\'account.', 'success');
    }
    
    if (urlParams.get('login') === 'success') {
        showMessage(document.getElementById('login-message'), 'Login effettuato con successo!', 'success');
    }
    
    if (urlParams.get('logout') === 'true') {
        showMessage(document.getElementById('login-message'), 'Logout effettuato con successo.', 'success');
    }
    
    const error = urlParams.get('error');
    if (error) {
        showMessage(document.getElementById('login-message'), decodeURIComponent(error), 'danger');
    }
    
    const registerError = urlParams.get('register_error');
    if (registerError) {
        // Attiva il tab registrazione e mostra l'errore
        document.getElementById('register-tab').click();
        showMessage(document.getElementById('register-message'), decodeURIComponent(registerError), 'danger');
    }
}
