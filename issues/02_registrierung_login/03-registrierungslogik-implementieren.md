# 03 - Registrierungslogik implementieren

## Ziel
Die Backend-Logik für die Benutzerregistrierung implementieren, sodass neue Benutzer gespeichert werden können.

## Aufgabenbeschreibung
Implementiere einen Controller, der das Registrierungsformular verarbeitet:
- Validierung der Eingaben (E-Mail-Format, Passwort-Übereinstimmung)
- Prüfung auf bereits existierende E-Mail-Adressen
- Sicheres Hashen des Passworts
- Generierung eines eindeutigen Verifizierungs-Tokens
- Speichern des neuen Benutzers in der Datenbank (mit isVerified = false)
- Versenden einer Bestätigungs-E-Mail mit Verifizierungs-Link
- Implementierung einer Route zur E-Mail-Bestätigung (Token-Validierung)
- Weiterleitung nach erfolgreicher Registrierung

Überlege dir:
- Welche Validierungen sind serverseitig notwendig?
- Was passiert, wenn die E-Mail bereits existiert?
- Wie generierst du einen sicheren Verifizierungs-Token?
- Wie lange soll der Verifizierungs-Link gültig sein?
- Wie gibst du Feedback an den Benutzer (Erfolg/Fehler)?
- Wohin sollte der Benutzer nach erfolgreicher Registrierung weitergeleitet werden?
- Was passiert, wenn ein Benutzer versucht sich einzuloggen, ohne die E-Mail verifiziert zu haben?

## Technische Hinweise
- Verwende einen Symfony Controller für die Logik
- Symfony Forms bieten automatische Validierung
- **Passwort-Hashing:** Nutze ausschließlich `UserPasswordHasherInterface` - siehe [Password Hashing Dokumentation](https://symfony.com/doc/current/security/passwords.html)
  ```php
  $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
  ```
- **WICHTIG:** Verwende KEINE eigenen Hashing-Funktionen wie `password_hash()`, `md5()`, `sha1()` etc.
- Doctrine EntityManager speichert Entities in der Datenbank: `$entityManager->persist()` und `$entityManager->flush()`
- **Token-Generierung:** Nutze Symfony's `Uuid` (aus `symfony/uid`) oder `UriSafeTokenGenerator` zur Generierung sicherer Verifizierungs-Tokens
- Nutze den Symfony Mailer Service zum Versenden von E-Mails
- Flash Messages eignen sich für Erfolgsmeldungen
- Dokumentation: [Symfony Controllers](https://symfony.com/doc/current/controller.html)
- Dokumentation: [Form Validation](https://symfony.com/doc/current/validation.html)
- Dokumentation: [Symfony Mailer](https://symfony.com/doc/current/mailer.html)

## Akzeptanzkriterien
- [ ] Neue Benutzer können sich erfolgreich registrieren
- [ ] Passwörter werden sicher gehasht gespeichert
- [ ] Doppelte E-Mail-Adressen werden verhindert
- [ ] Nach der Registrierung wird eine Bestätigungs-E-Mail mit Verifizierungs-Link versendet
- [ ] Benutzer können ihre E-Mail-Adresse durch Klick auf den Link bestätigen
- [ ] Nur Benutzer mit verifizierter E-Mail-Adresse können sich einloggen
- [ ] Verifizierungs-Token haben ein Ablaufdatum
- [ ] Der Benutzer erhält Feedback bei erfolgreicher Registrierung und E-Mail-Verifizierung
- [ ] Fehlermeldungen werden bei ungültigen Eingaben oder abgelaufenen Tokens angezeigt

## Zusatzaufgaben (Optional)
- Implementiere eine Funktion zum erneuten Versenden der Bestätigungs-E-Mail
- Logge Registrierungsversuche für Sicherheitszwecke
- Erstelle eine Email-Vorlage mit ansprechendem Design