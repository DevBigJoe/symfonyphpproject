# 02 - Nutzerübersicht für Administratoren erstellen

## Ziel
Eine Übersichtsseite erstellen, auf der Administratoren alle registrierten Nutzer sehen können.

## Aufgabenbeschreibung
Erstelle eine Admin-Seite mit einer Übersicht aller registrierten Nutzer:
- Zeige relevante Nutzerinformationen in einer Tabelle an (E-Mail, Rollen, Registrierungsdatum, Verifizierungsstatus)
- Biete Links zu Detailseiten der Nutzer
- Optional: Implementiere Sortierung und Filterung

Überlege dir:
- Welche Informationen sind für Administratoren in der Übersicht wichtig?
- Wie gehst du mit einer großen Anzahl von Nutzern um (Paginierung)?
- Sollte es eine Suchfunktion geben?
- Wie kannst du zwischen verifizierten und nicht-verifizierten Nutzern unterscheiden?

## Technische Hinweise
- Erstelle einen Controller `src/Controller/Admin/UserManagementController.php`
- Verwende das UserRepository, um alle Nutzer abzurufen
- Schütze die Route mit `#[IsGranted('ROLE_ADMIN')]`
- Für Paginierung kann das KnpPaginatorBundle hilfreich sein
- Dokumentation: [Doctrine Repositories](https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository)
- Dokumentation: [Twig Templates](https://symfony.com/doc/current/templates.html)

## Akzeptanzkriterien
- [ ] Administratoren können alle registrierten Nutzer sehen
- [ ] Die Übersicht zeigt E-Mail, Rollen, Registrierungsdatum und Verifizierungsstatus
- [ ] Die Seite ist nur für Administratoren zugänglich
- [ ] Die Darstellung ist übersichtlich und benutzerfreundlich
- [ ] Links zu Detailseiten sind vorhanden

## Zusatzaufgaben (Optional)
- Implementiere Paginierung für große Nutzerlisten
- Füge eine Suchfunktion hinzu
- Ermögliche Sortierung nach verschiedenen Kriterien
- Zeige zusätzliche Statistiken an (Anzahl Nutzer gesamt, davon verifiziert, etc.)

## Hinweise zum Testen
- **Integrationstests**: Erstelle WebTestCase-Tests, die prüfen:
  - Admin kann die Nutzerübersicht aufrufen (HTTP 200)
  - Nicht-Admin erhält HTTP 403
  - Die Response enthält die erwarteten Nutzerinformationen (E-Mail, Rollen)
  - Tabellenstruktur ist korrekt im HTML vorhanden
