# 05 - Redaktionsrolle einrichten

## Ziel
Eine ROLE_REDAKTION definieren und sicherstellen, dass Redakteure Artikel erstellen und bearbeiten können.

## Aufgabenbeschreibung
Erweitere das Rollen-System um eine Redaktionsrolle:
- Definiere eine `ROLE_REDAKTION` im System
- Richte die Zugriffskontrolle so ein, dass sowohl ROLE_ADMIN als auch ROLE_REDAKTION auf die Artikelverwaltung zugreifen können
- Passe die Rollen-Hierarchie an, falls sinnvoll

Überlege dir:
- Soll ROLE_ADMIN automatisch auch ROLE_REDAKTION-Rechte haben (Hierarchie)?
- Welche Unterschiede gibt es zwischen Admin und Redakteur?
- Sollten Redakteure auch auf die Nutzerverwaltung zugreifen können? (Nein!)
- Wie definierst du die Berechtigungen für Artikel-Routen?

## Technische Hinweise
- Erweitere die Rollen-Hierarchie in `config/packages/security.yaml`
- Für Artikel-Controller: `#[IsGranted('ROLE_REDAKTION')]`
- Wenn ROLE_ADMIN in der Hierarchie über ROLE_REDAKTION steht, haben Admins automatisch auch Redaktions-Rechte
- Dokumentation: [Symfony Security Authorization](https://symfony.com/doc/current/security.html#denying-access-roles-and-other-authorization)
- Dokumentation: [Role Hierarchy](https://symfony.com/doc/current/security.html#hierarchical-roles)

## Akzeptanzkriterien
- [ ] Eine `ROLE_REDAKTION` ist definiert
- [ ] Die Rollen-Hierarchie ist entsprechend konfiguriert
- [ ] Sowohl Admins als auch Redakteure können später auf Artikel-Routen zugreifen
- [ ] Die Nutzerverwaltung bleibt nur für Admins zugänglich
