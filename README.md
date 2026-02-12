# Symfony Setup

## Dokumentation Shortcuts

### Frontend
- [AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html)
- [StimulusBundle](https://symfony.com/bundles/StimulusBundle/current/index.html)
- [Tailwind](https://symfony.com/bundles/TailwindBundle/current/index.html)
- [Alpine.js](https://alpinejs.dev/)

### Backend
- [Routing](https://symfony.com/doc/current/routing.html)
- [Databases / Doctrine](https://symfony.com/doc/current/doctrine.html)
- [Logging](https://symfony.com/doc/current/logging.html)
- [Security](https://symfony.com/doc/current/security.html)
- [Symfony Mailer](https://symfony.com/doc/current/mailer.html)

## Installation / Erste Schritte

Nach dem ersten Checkout des Projekts:

```bash
# Umgebungsvariablen kopieren
cp .env.dev.local.dist .env.dev.local

# Docker Container starten
make build

# Composer Dependencies installieren
make composer-install

# Datenbank aufsetzen (erstellen, Migrationen ausführen, Fixtures laden)
make db-setup

# Start symfony messenger
make up

# Compile Tainwind CSS
make tailwind
```

Das Projekt ist nun unter `http://localhost:8090` erreichbar.

## Wichtige Symfony Befehle

### Console Commands
```bash
# Liste aller verfügbaren Befehle
bin/console list

# Cache leeren
bin/console cache:clear

# Routes anzeigen
bin/console debug:router

# Services anzeigen
bin/console debug:container
```

### AssetMapper & Frontend
```bash
# Assets kompilieren
bin/console asset-map:compile

# Tailwind CSS kompilieren
bin/console tailwind:build

# Tailwind CSS watch mode
bin/console tailwind:build --watch
```

## Doctrine

### Wichtige Konzepte
- **Entity**: PHP-Klasse, die eine Datenbanktabelle repräsentiert (mit Attributen)
- **Repository**: Klasse für Datenbankabfragen
- **EntityManager**: Zentrale Schnittstelle für Persistierung und Abfragen
- **Migrations**: Versionierte Datenbankschema-Änderungen
- **Fixtures**: Test-/Demo-Daten für die Entwicklung

### Workflow
1. Entity erstellen/ändern (`src/Entity/`)
2. Migration generieren (`doctrine:migrations:diff`)
3. Migration prüfen und ausführen (`doctrine:migrations:migrate`)
4. Optional: Fixtures laden (`doctrine:fixtures:load`)

### Kommandos
```bash
# Datenbank erstellen
bin/console doctrine:database:create

# Datenbank löschen
bin/console doctrine:database:drop --force

# Migration generieren (aus Entity-Änderungen)
bin/console doctrine:migrations:diff

# Migrationen ausführen
bin/console doctrine:migrations:migrate

# Migration Status
bin/console doctrine:migrations:status

# Letzte Migration rückgängig machen
bin/console doctrine:migrations:migrate prev

# Fixtures laden
bin/console doctrine:fixtures:load

# Entity validieren
bin/console doctrine:schema:validate

# Doctrine Cache leeren
bin/console doctrine:cache:clear-metadata
```

### Makefile Shortcuts
```bash
# Datenbank neu aufsetzen (drop, create, migrate, fixtures)
make db-setup
```

## Symfony Mailer
```bash
# E-Mail Test senden (im Docker Container)
bin/console mailer:test your-email@example.com
```

**Konfiguration:**
Der Symfony Mailer ist bereits installiert und mit **Maildev** konfiguriert.

**Maildev Features:**
- Alle E-Mails werden lokal abgefangen (kein echter Versand)
- Web-Interface zur Ansicht aller gesendeten E-Mails
- Ideal für Entwicklung und Testing

**Wichtige Schritte für E-Mail-Versand:**
1. Die MAILER_DSN ist bereits in `.env` konfiguriert (Maildev)
2. Erstelle E-Mail-Templates in `templates/emails/`
3. Nutze den `MailerInterface` Service in deinen Controllern/Services
4. Dokumentation: [Sending Emails](https://symfony.com/doc/current/mailer.html)
5. Für HTML-E-Mails: [Creating & Rendering Messages](https://symfony.com/doc/current/mailer.html#html-content)

# Docker Setup
## Overview
This Docker setup provides a complete development environment for PHP applications with the following components:

**Services:**
- **PHP 8.4 with Apache** - Web server running on `http://localhost:8090`
- **PostgreSQL 16** - Database server

**Key Features:**
- Xdebug enabled for debugging
- Composer pre-installed for dependency management
- Apache with mod_rewrite enabled
- Document root mapped to `./public` directory
- Database credentials: app/!ChangeMe!, database name: web_app
