# 05 - Login-Logik implementieren

## Ziel
Die Backend-Logik für die Benutzeranmeldung implementieren, sodass registrierte Benutzer sich einloggen können.

## Aufgabenbeschreibung
Konfiguriere und implementiere die Authentifizierung:
- Überprüfung der eingegebenen E-Mail und des Passworts
- Vergleich des Passworts mit dem gespeicherten Hash
- Erstellung einer Benutzersession bei erfolgreicher Anmeldung
- Weiterleitung nach erfolgreichem Login
- Fehlerbehandlung bei falschen Anmeldedaten

Überlege dir:
- Wie werden Anmeldedaten sicher überprüft?
- Wo soll der Benutzer nach erfolgreichem Login landen?
- Wie gibst du Feedback bei fehlgeschlagenen Login-Versuchen?
- Sollten Login-Versuche limitiert werden?

## Technische Hinweise
- Symfony Security Component übernimmt die Authentifizierung
- **Passwort-Vergleich:** Nutze `FormLogin` bzw. `FormLoginAuthenticator` - dieser prüft automatisch Passwort-Hashes mit `UserPasswordHasherInterface`
- **WICHTIG:** Verwende KEINE eigenen Vergleichsfunktionen wie `password_verify()` oder direkten String-Vergleich
- Konfiguration erfolgt in `config/packages/security.yaml` mit `form_login` Authenticator
- Die User Entity muss `UserInterface` und `PasswordAuthenticatedUserInterface` implementieren
- Sessions werden automatisch von Symfony verwaltet
- Überprüfe bei Login, ob die E-Mail-Adresse verifiziert ist (z.B. mit einem Custom Authenticator oder Voter)
- Dokumentation: [Symfony Security](https://symfony.com/doc/current/security.html)
- Dokumentation: [Form Login](https://symfony.com/doc/current/security.html#form-login)
- Dokumentation: [Custom Authenticator](https://symfony.com/doc/current/security/custom_authenticator.html)

## Akzeptanzkriterien
- [ ] Registrierte Benutzer mit verifizierter E-Mail können sich erfolgreich einloggen
- [ ] Benutzer ohne verifizierte E-Mail werden abgelehnt mit entsprechender Meldung
- [ ] Passwörter werden automatisch über FormLoginAuthenticator sicher verglichen
- [ ] Der Benutzer wird nach Login zur gewünschten Seite weitergeleitet
- [ ] Fehlermeldungen werden bei falschen Anmeldedaten angezeigt
- [ ] Die Benutzersession bleibt über mehrere Seiten bestehen

## Zusatzaufgaben (Optional)
- Implementiere "Remember Me" Funktionalität
- Begrenze Login-Versuche (Rate Limiting)
- Logge fehlgeschlagene Login-Versuche für Sicherheit