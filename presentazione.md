Presentazione: https://docs.google.com/presentation/d/1v7aW6f75cQbkj9Sf2FifS2X-dUpeWAo3/edit?usp=sharing&ouid=107269115441606231055&rtpof=true&sd=true

# 🚀 Tenuta Manarese E-commerce

Un e-commerce completo per l'azienda vinicola Tenuta Manarese, realizzato con PHP, MySQL e Bootstrap 5.

## 📋 Caratteristiche Principali

### ✅ **Frontend**
- Design responsive con Bootstrap 5
- Interfaccia utente moderna e intuitiva
- Catalogo prodotti dinamico
- Carrello shopping con AJAX
- Sistema di checkout completo

### ✅ **Backend** 
- Architettura PHP modulare
- Database MySQL ottimizzato
- Sistema di autenticazione sicuro
- Admin panel completo
- API RESTful

### ✅ **Sicurezza**
- Password hashing con bcrypt
- Protezione CSRF
- SQL injection prevention
- Sessioni sicure
- Conformità GDPR

### ✅ **Funzionalità E-commerce**
- Gestione prodotti e inventario
- Sistema ordini completo
- Profili utente personalizzabili
- Lista desideri (wishlist)
- Storico acquisti
- Funzione "riordina"

## 🛠 Installazione

### Requisiti
- PHP 7.4+ o 8.x
- MySQL 5.7+
- Apache/Nginx
- Estensioni PHP: PDO, mysqli, mbstring, openssl

### Setup Database
1. Importa il file `database_schema.sql` nel tuo database MySQL
2. Crea un utente database con i privilegi necessari

### Configurazione
1. Copia `config.template.php` in `config.php`
2. Modifica `config.php` con le tue credenziali database:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

3. Configura le impostazioni email se necessario
4. Imposta i permessi corretti sui file (644 per i file, 755 per le directory)

### File di Configurazione
- `config.php` - Configurazione principale (NON committare)
- `config.template.php` - Template per la configurazione
- `.env.example` - Esempio variabili ambiente

## 📁 Struttura Progetto

```
├── api/                    # API endpoints
│   ├── cart.php           # Gestione carrello
│   ├── reorder.php        # Riordinare prodotti
│   └── wishlist.php       # Lista desideri
├── css/                   # Fogli di stile
├── js/                    # JavaScript
├── immagini/              # Immagini del sito
├── sql/                   # File database
├── includes/              # File PHP includibili
└── pages/                 # Pagine principali
```
