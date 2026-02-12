# 10 - Artikelverwaltung in Admin-Navigation integrieren

## Ziel
Die Navigation um einen Menüpunkt für die Artikelverwaltung erweitern.

## Aufgabenbeschreibung
Erweitere die Admin-Navigation um Artikelverwaltung:
- Füge einen "Artikel" oder "Artikelverwaltung" Menüpunkt hinzu
- Der Menüpunkt soll für ROLE_ADMIN und ROLE_REDAKTION sichtbar sein
- Verlinke zur Artikel-Übersicht (`/admin/articles`)

Überlege dir:
- Wo sollte der Artikel-Menüpunkt platziert werden?
- Sollte es separate Menüpunkte für Übersicht und "Neuer Artikel" geben?
- Sollte die Anzahl der unveröffentlichten Entwürfe als Badge angezeigt werden?

## Technische Hinweise
- Erweitere das Template `templates/_navigation.html.twig`
- Verwende `is_granted('ROLE_REDAKTION')` zur Berechtigungsprüfung
- Da ROLE_ADMIN in der Hierarchie höher steht, reicht die Prüfung auf ROLE_REDAKTION
- Dokumentation: [Twig Security](https://symfony.com/doc/current/security.html#checking-permissions-in-twig)

## Akzeptanzkriterien
- [ ] Ein Artikel-Menüpunkt ist in der Navigation vorhanden
- [ ] Der Menüpunkt ist für Admins und Redakteure sichtbar
- [ ] Der Link führt zur Artikel-Übersicht
- [ ] Andere Rollen sehen den Menüpunkt nicht
- [ ] Die Navigation ist konsistent mit dem bestehenden Design

## Hinweise zum Testen
- **Integrationstests**: Erstelle WebTestCase-Tests, die prüfen:
  - Admin sieht Artikel-Menüpunkt im HTML (DOM-Abfrage mit CSS-Selector)
  - Redakteur sieht Artikel-Menüpunkt im HTML
  - Andere Rollen sehen den Menüpunkt nicht
  - Link führt zur korrekten Route (`/admin/articles`)
- Teste mit verschiedenen Nutzer-Rollen mittels `loginUser()`