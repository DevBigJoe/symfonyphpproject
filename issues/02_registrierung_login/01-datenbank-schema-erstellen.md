# 01 - Datenbank-Schema für Benutzer erstellen

## Ziel
Eine Doctrine Entity für Benutzer erstellen, die alle notwendigen Informationen für Registrierung und Login speichern kann.

## Aufgabenbeschreibung
Entwerfe und erstelle eine Doctrine Entity `User`, die folgende Mindestanforderungen erfüllt:
- Speicherung von E-Mail-Adressen (dienen gleichzeitig als Username)
- Speicherung von Passwörtern (sicher gehasht)
- Felder für E-Mail-Verifizierung (Token, Bestätigungsstatus, Ablaufdatum)
- Weitere sinnvolle Felder nach eigenem Ermessen

Überlege dir:
- Welche zusätzlichen Informationen könnten für ein Benutzersystem relevant sein?
- Welche Datentypen sind für die jeweiligen Informationen geeignet?
- Wie stellst du sicher, dass keine Duplikate entstehen können?
- Wie kannst du ein späteres Rollen- und Rechtesystem vorbereiten?
- Welche Felder benötigst du für die E-Mail-Verifizierung (Token, isVerified, verificationTokenExpiresAt)?

Erstelle anschließend eine Migration, um die Datenbanktabelle anzulegen.

## Technische Hinweise
- Verwende Doctrine Entities zur Definition der Datenstruktur
- Symfony verwendet `UserPasswordHasherInterface` zum sicheren Hashen von Passwörtern (siehe [Password Hashing](https://symfony.com/doc/current/security/passwords.html))
- **WICHTIG:** Verwende KEINE eigenen Hashing-Funktionen (wie `password_hash()`, `md5()`, `sha1()` etc.) - nutze ausschließlich Symfony's `UserPasswordHasherInterface`
- Der Standard-Hash-Algorithmus (bcrypt/auto) erzeugt Strings mit mindestens 60 Zeichen
- Beachte, dass sich Hash-Längen in Zukunft ändern können (Puffer einplanen: mindestens 255 Zeichen für das Passwort-Feld)
- Datumswerte sollten als `DateTimeImmutable` gespeichert werden
- Erstelle die Entity-Klasse manuell in `src/Entity/User.php` mit Doctrine Annotations
- Die User Entity muss `UserInterface` und `PasswordAuthenticatedUserInterface` implementieren
- Verwende Doctrine Migrations zur Schema-Erstellung:
  1. `php bin/console doctrine:migrations:diff` - erstellt automatisch eine Migration basierend auf Entity-Änderungen
  2. `php bin/console doctrine:migrations:migrate` - führt die Migration aus
- Dokumentation: [Symfony Security - Password Hashing](https://symfony.com/doc/current/security/passwords.html)
- Dokumentation: [Doctrine Entities](https://symfony.com/doc/current/doctrine.html)

## Akzeptanzkriterien
- [ ] Die Entity kann E-Mail-Adressen und Passwörter speichern
- [ ] Stelle sicher, dass E-Mail-Adressen eindeutig sind
- [ ] Die Entity hat einen eindeutigen Identifikator für jeden Benutzer
- [ ] Das Schema ist für ein späteres Rollen- und Rechtesystem vorbereitet
- [ ] Felder für E-Mail-Verifizierung sind vorhanden (Token, isVerified, Ablaufdatum)
- [ ] Eine Migration wurde erstellt und kann ausgeführt werden