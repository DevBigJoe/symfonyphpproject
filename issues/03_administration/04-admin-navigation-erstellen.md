# 07 - Admin-Navigation erstellen

## Ziel
Eine Navigation für den Admin-Bereich erstellen, die nur für Administratoren sichtbar ist.

## Aufgabenbeschreibung
Erweitere die bestehende Navigation um Admin-Funktionen:
- Füge einen "Administration" oder "Verwaltung" Menüpunkt hinzu
- Der Menüpunkt soll nur für Nutzer mit ROLE_ADMIN sichtbar sein
- Verlinke zur Nutzerübersicht
- Optional: Erstelle ein Dropdown-Menü für mehrere Admin-Funktionen

Überlege dir:
- Wo sollte der Admin-Bereich in der Navigation platziert werden?
- Sollte es ein separates Admin-Menü geben oder Integration in die Hauptnavigation?
- Wie kennzeichnest du visuell, dass dies ein Admin-Bereich ist?
- Welche weiteren Admin-Funktionen könnten zukünftig hinzukommen?

## Technische Hinweise
- Verwende Twig's `is_granted()` Funktion zur Berechtigungsprüfung
- Syntax: `{% if is_granted('ROLE_ADMIN') %}`
- Optional: Verwende Icon-Fonts oder Emojis zur Kennzeichnung
- Dokumentation: [Twig Security](https://symfony.com/doc/current/security.html#checking-permissions-in-twig)
- Dokumentation: [Twig Templates](https://symfony.com/doc/current/templates.html)

## Akzeptanzkriterien
- [ ] Ein Admin-Menüpunkt ist in der Navigation vorhanden
- [ ] Der Menüpunkt ist nur für Administratoren sichtbar
- [ ] Ein Link führt zur Nutzerübersicht
- [ ] Die Navigation ist konsistent mit dem bestehenden Design
- [ ] Nicht-Admins sehen den Menüpunkt nicht

## Zusatzaufgaben (Optional)
- Erstelle ein Dropdown-Menü mit mehreren Admin-Funktionen
- Füge eine visuelle Kennzeichnung für den Admin-Bereich hinzu
- Implementiere ein separates Admin-Layout mit eigener Navigation
- Zeige die Anzahl gesperrter Nutzer im Menü an (Badge)
- Erweitere die DataFixtures um einen Admin-Nutzer zur Demonstration

## Hinweise zum Testen
- **Integrationstests**: Erstelle WebTestCase-Tests, die prüfen:
  - Admin-Nutzer sieht den Admin-Menüpunkt im HTML (DOM-Abfrage mit CSS-Selector)
  - Nicht-Admin-Nutzer sieht den Admin-Menüpunkt nicht
  - Nicht authentifizierte Nutzer sehen den Admin-Menüpunkt nicht
  - Link im Menü führt zur korrekten Route (`/admin/users`)
- Teste mit verschiedenen Nutzer-Rollen mittels `loginUser()`
