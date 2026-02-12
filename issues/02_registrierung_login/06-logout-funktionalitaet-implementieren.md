# 06 - Logout-Funktionalität implementieren

## Ziel
Eine Logout-Funktion implementieren, die es angemeldeten Benutzern ermöglicht, sich abzumelden.

## Aufgabenbeschreibung
Implementiere die Abmeldefunktion mit folgenden Anforderungen:
- Erstellung einer Logout-Route
- Beendigung der Benutzersession
- Weiterleitung nach erfolgreichem Logout
- Platzierung eines Logout-Links in der Navigation

Überlege dir:
- Wo sollte der Logout-Link platziert werden?
- Wohin soll der Benutzer nach dem Logout weitergeleitet werden?
- Sollte vor dem Logout eine Bestätigung erfolgen?
- Wie stellst du sicher, dass nur angemeldete Benutzer den Logout-Link sehen?

## Technische Hinweise
- Symfony Security Component bietet integrierte Logout-Funktionalität
- Konfiguration erfolgt in `config/packages/security.yaml`
- Die Logout-Route muss definiert, aber nicht implementiert werden (Symfony übernimmt die Logik)
- Twig bietet `is_granted('IS_AUTHENTICATED')` zur Prüfung des Login-Status
- Dokumentation: [Symfony Logout](https://symfony.com/doc/current/security.html#logging-out)
- Dokumentation: [Security in Templates](https://symfony.com/doc/current/security.html#checking-to-see-if-a-user-is-logged-in-is-authenticated-fully)

## Akzeptanzkriterien
- [ ] Angemeldete Benutzer können sich erfolgreich abmelden
- [ ] Die Session wird vollständig beendet
- [ ] Der Benutzer wird nach Logout zur Login-Seite weitergeleitet
- [ ] Der Logout-Link ist nur für angemeldete Benutzer sichtbar
- [ ] Nach Logout ist kein Zugriff mehr auf geschützte Bereiche möglich

## Zusatzaufgaben (Optional)
- Zeige eine Erfolgsmeldung nach dem Logout
- Implementiere automatisches Logout nach Inaktivität
- Lösche "Remember Me" Cookies beim Logout