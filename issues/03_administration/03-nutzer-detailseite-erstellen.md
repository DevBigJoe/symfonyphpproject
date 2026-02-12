# 03 - Nutzer-Detailseite für Administratoren erstellen

## Ziel
Eine Detailseite erstellen, auf der Administratoren alle Informationen zu einem einzelnen Nutzer sehen können.

## Aufgabenbeschreibung
Erstelle eine Detailansicht für einzelne Nutzer:
- Zeige alle relevanten Nutzerinformationen an
- Zeige die Nutzer-Historie (Registrierungsdatum, E-Mail-Verifizierung)
- Biete einen Button zum Zurückkehren zur Übersicht

Überlege dir:
- Welche Informationen sollten auf der Detailseite angezeigt werden?
- Wie gehst du mit sensiblen Informationen um?
- Sollten Admins hier auch Rollen ändern können?
- Wie gehst du mit nicht existierenden Nutzern um (404-Fehler)?

## Technische Hinweise
- Verwende Route-Parameter für die User-ID (z.B. `/admin/users/{id}`)
- Dokumentation: [Controller](https://symfony.com/doc/current/controller.html)
- Dokumentation: [Routing](https://symfony.com/doc/current/routing.html)

## Akzeptanzkriterien
- [ ] Administratoren können Details eines Nutzers einsehen
- [ ] Die Seite zeigt E-Mail, Rollen, Registrierungsdatum, Verifizierungsstatus
- [ ] Nicht existierende Nutzer führen zu einer 404-Seite
- [ ] Die Seite ist nur für Administratoren zugänglich
- [ ] Ein Link zurück zur Übersicht ist vorhanden

## Zusatzaufgaben (Optional)
- Zeige die letzte Login-Zeit an (erfordert Tracking in der User-Entity)
- Ermögliche das Bearbeiten von Nutzerinformationen (z.B. Rollen zuweisen)
- Zeige eine Timeline der Nutzer-Events
- Zeige zusätzliche Informationen wie das Verifizierungstoken-Ablaufdatum

## Hinweise zum Testen
- **Integrationstests**: Erstelle WebTestCase-Tests, die prüfen:
  - Admin kann Nutzer-Detailseite aufrufen (HTTP 200)
  - Nicht-Admin erhält HTTP 403
  - Nicht-existierende User-ID führt zu HTTP 404
  - Response enthält korrekte Nutzerinformationen (E-Mail, Rollen, Registrierungsdatum)
  - Verifizierungsstatus wird korrekt angezeigt
