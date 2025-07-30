-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Lug 30, 2025 alle 23:52
-- Versione del server: 8.0.36
-- Versione PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_projectworkgruppo3`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `carrello`
--

CREATE TABLE `carrello` (
  `id` int NOT NULL,
  `utente_id` int DEFAULT NULL,
  `sessione_id` varchar(255) DEFAULT NULL,
  `prodotto_id` int NOT NULL,
  `quantita` int NOT NULL DEFAULT '1',
  `data_aggiunta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_modifica` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `categorie`
--

CREATE TABLE `categorie` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descrizione` text,
  `immagine` varchar(255) DEFAULT NULL,
  `attiva` tinyint(1) DEFAULT '1',
  `data_creazione` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `codici_sconto`
--

CREATE TABLE `codici_sconto` (
  `id` int NOT NULL,
  `codice` varchar(50) NOT NULL,
  `tipo` enum('percentuale','fisso') NOT NULL,
  `valore` decimal(10,2) NOT NULL,
  `minimo_ordine` decimal(10,2) DEFAULT '0.00',
  `utilizzi_massimi` int DEFAULT NULL,
  `utilizzi_correnti` int DEFAULT '0',
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `attivo` tinyint(1) DEFAULT '1',
  `data_creazione` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `gdpr_log`
--

CREATE TABLE `gdpr_log` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `attempt_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `was_successful` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `attiva` tinyint(1) DEFAULT '1',
  `data_iscrizione` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_disiscrizione` timestamp NULL DEFAULT NULL,
  `token_conferma` varchar(64) DEFAULT NULL,
  `confermata` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ordini`
--

CREATE TABLE `ordini` (
  `id` int NOT NULL,
  `numero_ordine` varchar(20) NOT NULL,
  `utente_id` int NOT NULL,
  `stato` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `subtotale` decimal(10,2) NOT NULL,
  `spese_spedizione` decimal(10,2) DEFAULT '0.00',
  `tasse` decimal(10,2) DEFAULT '0.00',
  `sconto` decimal(10,2) DEFAULT '0.00',
  `totale` decimal(10,2) NOT NULL,
  `codice_sconto` varchar(50) DEFAULT NULL,
  `metodo_pagamento` varchar(50) DEFAULT NULL,
  `stato_pagamento` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `indirizzo_fatturazione` json DEFAULT NULL,
  `indirizzo_spedizione` json DEFAULT NULL,
  `note` text,
  `tracking_code` varchar(100) DEFAULT NULL,
  `data_ordine` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_spedizione` timestamp NULL DEFAULT NULL,
  `data_consegna` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ordini_dettagli`
--

CREATE TABLE `ordini_dettagli` (
  `id` int NOT NULL,
  `ordine_id` int NOT NULL,
  `prodotto_id` int NOT NULL,
  `nome_prodotto` varchar(200) NOT NULL,
  `prezzo_unitario` decimal(10,2) NOT NULL,
  `quantita` int NOT NULL,
  `subtotale` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `prodotti`
--

CREATE TABLE `prodotti` (
  `id` int NOT NULL,
  `nome` varchar(200) NOT NULL,
  `descrizione` text,
  `descrizione_breve` varchar(500) DEFAULT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `prezzo_scontato` decimal(10,2) DEFAULT NULL,
  `categoria_id` int DEFAULT NULL,
  `immagine_principale` varchar(255) DEFAULT NULL,
  `galleria_immagini` json DEFAULT NULL,
  `caratteristiche` json DEFAULT NULL,
  `giacenza` int DEFAULT '0',
  `disponibile` tinyint(1) DEFAULT '1',
  `peso` decimal(8,2) DEFAULT '0.75',
  `data_creazione` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_modifica` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `indirizzo` text,
  `cap` int DEFAULT NULL,
  `citta` varchar(100) DEFAULT NULL,
  `provincia` varchar(2) DEFAULT NULL,
  `data_nascita` date DEFAULT NULL,
  `newsletter` tinyint(1) DEFAULT '0',
  `notifiche_ordini` tinyint(1) DEFAULT '1',
  `notifiche_offerte` tinyint(1) DEFAULT '0',
  `attivo` tinyint(1) DEFAULT '0',
  `activation_token` varchar(64) DEFAULT NULL,
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL,
  `remember_expires` datetime DEFAULT NULL,
  `creato_il` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `aggiornato_il` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_registrazione` date DEFAULT NULL,
  `data_attivazione` datetime DEFAULT NULL,
  `ultimo_accesso` datetime DEFAULT NULL,
  `ruolo` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `failed_login_attempts` int DEFAULT '0',
  `account_locked_until` datetime DEFAULT NULL,
  `last_failed_login` datetime DEFAULT NULL,
  `consenso_marketing` tinyint(1) DEFAULT '1',
  `processing_restricted` tinyint(1) DEFAULT '0',
  `privacy_version` varchar(10) DEFAULT '1.0',
  `consent_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_modifica` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `carrello`
--
ALTER TABLE `carrello`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utente` (`utente_id`),
  ADD KEY `idx_prodotto` (`prodotto_id`),
  ADD KEY `idx_sessione` (`sessione_id`);

--
-- Indici per le tabelle `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indici per le tabelle `codici_sconto`
--
ALTER TABLE `codici_sconto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codice` (`codice`);

--
-- Indici per le tabelle `gdpr_log`
--
ALTER TABLE `gdpr_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `ordini`
--
ALTER TABLE `ordini`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_ordine` (`numero_ordine`),
  ADD KEY `idx_utente` (`utente_id`),
  ADD KEY `idx_stato` (`stato`),
  ADD KEY `idx_data_ordine` (`data_ordine`);

--
-- Indici per le tabelle `ordini_dettagli`
--
ALTER TABLE `ordini_dettagli`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ordine` (`ordine_id`),
  ADD KEY `idx_prodotto` (`prodotto_id`);

--
-- Indici per le tabelle `prodotti`
--
ALTER TABLE `prodotti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria` (`categoria_id`),
  ADD KEY `idx_prezzo` (`prezzo`),
  ADD KEY `idx_disponibile` (`disponibile`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_activation_token` (`activation_token`),
  ADD KEY `idx_password_reset_token` (`password_reset_token`),
  ADD KEY `idx_remember_token` (`remember_token`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `carrello`
--
ALTER TABLE `carrello`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `codici_sconto`
--
ALTER TABLE `codici_sconto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `gdpr_log`
--
ALTER TABLE `gdpr_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `ordini`
--
ALTER TABLE `ordini`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `ordini_dettagli`
--
ALTER TABLE `ordini_dettagli`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `prodotti`
--
ALTER TABLE `prodotti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `carrello`
--
ALTER TABLE `carrello`
  ADD CONSTRAINT `fk_carrello_prodotto` FOREIGN KEY (`prodotto_id`) REFERENCES `prodotti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_carrello_utente` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `ordini`
--
ALTER TABLE `ordini`
  ADD CONSTRAINT `fk_ordini_utente` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `ordini_dettagli`
--
ALTER TABLE `ordini_dettagli`
  ADD CONSTRAINT `fk_ordini_dettagli_ordine` FOREIGN KEY (`ordine_id`) REFERENCES `ordini` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ordini_dettagli_prodotto` FOREIGN KEY (`prodotto_id`) REFERENCES `prodotti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `prodotti`
--
ALTER TABLE `prodotti`
  ADD CONSTRAINT `fk_prodotti_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorie` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
