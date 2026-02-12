# 09 - Funktion zum Bearbeiten von Artikeln implementieren

## Ziel
Admins und Redakteuren ermöglichen, bestehende Artikel zu bearbeiten.

## Aufgabenbeschreibung
Implementiere die Funktionalität zum Bearbeiten von Artikeln:
- Erstelle eine Edit-Route `/admin/articles/{id}/edit`
- Verwende den bereits existierenden ArticleFormType
- Lade den bestehenden Artikel anhand der ID
- Das `updatedAt`-Feld wird durch `#[ORM\PreUpdate]` automatisch aktualisiert
- Optional: Prüfe, ob ein Redakteur nur seine eigenen Artikel bearbeiten darf

Überlege dir:
- Sollte der Autor nachträglich änderbar sein?
- Dürfen Redakteure nur ihre eigenen Artikel bearbeiten?
- Sollten Admins alle Artikel bearbeiten dürfen?
- Wie gehst du mit nicht-existierenden Artikel-IDs um?
- Sollte es eine "Veröffentlichen/Zurückziehen" Funktion geben?

## Technische Hinweise
- Verwende den existierenden `ArticleFormType`

## Akzeptanzkriterien
- [ ] Admins und Redakteure können Artikel bearbeiten
- [ ] Das Formular wird mit bestehenden Daten vorausgefüllt
- [ ] Nicht existierende Artikel führen zu HTTP 404
- [ ] Berechtigungen werden korrekt geprüft
- [ ] Nach dem Speichern erfolgt Weiterleitung

## Zusatzaufgaben (Optional)
- Implementiere einen Security Voter, der prüft, ob ein Nutzer einen Artikel bearbeiten darf
- Redakteure können nur ihre eigenen Artikel bearbeiten, Admins alle
- Füge "Veröffentlichen" und "Zurückziehen" Buttons hinzu
- Zeige Änderungshistorie an

## Hinweise zum Testen
- **Integrationstests**: Erstelle WebTestCase-Tests, die prüfen:
  - Admin kann eigene und fremde Artikel bearbeiten (HTTP 200)
  - Redakteur kann Artikel bearbeiten (HTTP 200)
  - Optional: Redakteur kann nur eigene Artikel bearbeiten
  - Nicht-existierende Artikel-ID führt zu HTTP 404
  - Änderungen werden in der Datenbank gespeichert
  - `updatedAt` wird aktualisiert
  - Formular ist mit bestehenden Daten vorausgefüllt
