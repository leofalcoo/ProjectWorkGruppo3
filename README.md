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

## 🔐 Sicurezza

### File Sensibili (GIT IGNORED)
- `config.php` - Credenziali database
- `config_email.php` - Configurazione email
- `*.env` - Variabili ambiente
- `*.log` - File di log
- `*.sql` - Dump database

### Best Practices Implementate
- Prepared statements per query database
- Validazione e sanitizzazione input
- Controllo accessi basato su ruoli
- Crittografia password
- Protezione CSRF
- Headers di sicurezza

## 📊 Database Schema

Il database include le seguenti tabelle principali:
- `utenti` - Dati utenti e autenticazione
- `prodotti` - Catalogo prodotti
- `carrello` - Carrello shopping
- `ordini` - Gestione ordini
- `ordini_dettagli` - Dettagli prodotti ordinati
- `wishlist` - Lista desideri utenti

## 🚀 Deploy

### Hosting Consigliato
- Altervista (configurazione inclusa)
- Hosting con supporto PHP 7.4+/8.x e MySQL

### Checklist Pre-Deploy
- [ ] Configurare `config.php` con credenziali produzione
- [ ] Importare database su server produzione
- [ ] Verificare permessi file
- [ ] Configurare SSL/HTTPS
- [ ] Testare funzionalità principali
- [ ] Configurare backup automatici

## 🔧 Configurazione Avanzata

### Variabili Ambiente
Crea un file `.env` per configurazioni sensitive:
```env
DB_HOST=localhost
DB_NAME=tenuta_manarese
DB_USER=username
DB_PASS=password
ADMIN_EMAIL=admin@tenutamanarese.it
```

### Personalizzazione
- Modifica `css/style.css` per personalizzare il design
- Aggiorna `immagini/` con le tue immagini
- Configura `js/script.js` per comportamenti custom

## 📞 Supporto

Per problemi tecnici o domande:
- Verifica la documentazione in `docs/`
- Controlla i log di errore
- Verifica la configurazione database

## 📄 Licenza

Progetto sviluppato per Tenuta Manarese. Tutti i diritti riservati.

## 🏆 Credits

Sviluppato con ❤️ utilizzando:
- PHP & MySQL
- Bootstrap 5
- Font Awesome
- jQuery
- Chart.js (per statistiche admin)

---

⚠️ **IMPORTANTE**: Ricordati di configurare sempre `config.php` prima dell'uso e di non committare mai file con credenziali sensibili!
