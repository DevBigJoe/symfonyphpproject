# 04 - Login-Formular erstellen

## Ziel
Ein HTML-Formular für die Benutzeranmeldung erstellen, das E-Mail und Passwort entgegennimmt.

## Aufgabenbeschreibung
Erstelle ein Login-Formular mit folgenden Mindestanforderungen:
- Eingabefeld für E-Mail-Adresse
- Eingabefeld für Passwort
- Submit-Button zum Absenden
- Link zur Registrierung (für neue Benutzer)

Überlege dir:
- Wie unterscheidet sich das Login- vom Registrierungsformular?
- Welche zusätzlichen Funktionen könnten sinnvoll sein (z.B. "Angemeldet bleiben")?
- Wie sollte das Formular gestaltet sein?
- Wo sollte ein Link zum Passwort-Zurücksetzen platziert werden?

## Technische Hinweise
- Symfony Security Component bietet spezielle Login-Form-Unterstützung
- Erstelle manuell:
  1. Ein Login-Template (z.B. `templates/security/login.html.twig`)
  2. Einen Security Controller (z.B. `src/Controller/SecurityController.php`)
  3. Konfiguriere die Security-Einstellungen in `config/packages/security.yaml`
- Twig-Templates für die Darstellung verwenden
- CSRF-Protection ist auch hier wichtig
- Verwende `{{ error }}` im Template um Login-Fehler anzuzeigen
- Verwende `{{ last_username }}` um die zuletzt eingegebene E-Mail beizubehalten
- Dokumentation: [Symfony Security Authentication](https://symfony.com/doc/current/security.html#form-login)

## Akzeptanzkriterien
- [ ] Das Formular kann E-Mail und Passwort entgegennehmen
- [ ] Das Formular ist benutzerfreundlich gestaltet
- [ ] Ein Link zur Registrierung ist vorhanden
- [ ] Das Formular ist über eine Route erreichbar
- [ ] Fehlermeldungen können angezeigt werden

## Zusatzaufgaben (Optional)
- Füge eine "Angemeldet bleiben"-Checkbox hinzu
- Implementiere einen "Passwort vergessen"-Link
- Zeige die Anzahl fehlgeschlagener Login-Versuche an