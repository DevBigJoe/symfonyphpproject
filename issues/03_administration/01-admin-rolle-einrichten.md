# 02 - Admin-Rolle und Berechtigungsprüfung einrichten

## Ziel
Eine Administrator-Rolle einrichten und sicherstellen, dass nur Administratoren auf die Nutzerverwaltung zugreifen können.

## Aufgabenbeschreibung
Erweitere das bestehende Rollen-System um eine Admin-Rolle:
- Definiere eine `ROLE_ADMIN` im System
- Richte die Zugriffskontrolle für Admin-Bereiche ein
- Stelle sicher, dass nur Nutzer mit Admin-Rechten auf die Verwaltungsfunktionen zugreifen können

Überlege dir:
- Soll es eine Rollen-Hierarchie geben?
- Wie kannst du sicherstellen, dass bestimmte Routen nur für Admins zugänglich sind?
- Sollten Admins auch die Rechte anderer Rollen haben?

## Technische Hinweise
- Konfiguriere die Rollen-Hierarchie in `config/packages/security.yaml`
- Symfony bietet verschiedene Möglichkeiten für Access Control:
  - `access_control` in `security.yaml` für URL-basierte Zugriffskontrollen
  - `#[IsGranted()]` Attribute in Controllern
  - `denyAccessUnlessGranted()` in Controller-Actions
- Verwende sinnvolle Pfade für Admin-Bereiche (z.B. `/admin/*`)
- Dokumentation: [Symfony Security Authorization](https://symfony.com/doc/current/security.html#denying-access-roles-and-other-authorization)
- Dokumentation: [Role Hierarchy](https://symfony.com/doc/current/security.html#hierarchical-roles)

## Akzeptanzkriterien
- [ ] Eine `ROLE_ADMIN` ist definiert
- [ ] Die Rollen-Hierarchie ist sinnvoll konfiguriert
- [ ] Admin-Routen sind nur für Administratoren zugänglich
- [ ] Nicht-Admins erhalten eine 403-Fehlermeldung beim Zugriff auf Admin-Bereiche
- [ ] Die Konfiguration ist in `security.yaml` dokumentiert

## Hinweise zum Testen
- **Integrationstests**: Erstelle WebTestCase-Tests, die prüfen:
  - Admin-Nutzer (mit ROLE_ADMIN) erhalten Zugriff auf `/admin/*` Routen (HTTP 200)
  - Nicht-Admin-Nutzer erhalten HTTP 403 bei Zugriff auf `/admin/*` Routen
  - Nicht authentifizierte Nutzer werden zur Login-Seite weitergeleitet
  - Falls Rollen-Hierarchie konfiguriert: Admin hat auch Rechte niedrigerer Rollen
- Verwende `loginUser()` in Tests, um Nutzer mit verschiedenen Rollen zu simulieren
