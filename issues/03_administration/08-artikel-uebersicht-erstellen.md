# 08 - Artikel-Übersicht erstellen

## Ziel
Eine Übersichtsseite für alle Artikel erstellen, die von Admins und Redakteuren verwaltet werden können.

## Aufgabenbeschreibung
Erstelle eine Übersichtsseite für Artikel:
- Zeige alle Artikel in einer Tabelle an (Titel, Autor, Erstellungsdatum, Status)
- Zeige den Veröffentlichungsstatus visuell an (published: true/false)
- Biete Links zum Erstellen, Bearbeiten und Anzeigen von Artikeln
- Optional: Implementiere Filterung nach Status oder Autor

Überlege dir:
- Welche Informationen sind in der Übersicht wichtig?
- Wie sollten veröffentlichte vs. unveröffentlichte Artikel gekennzeichnet werden?
- Sollte es Sortierung geben (neueste zuerst)?
- Wie gehst du mit vielen Artikeln um (Paginierung)?
- Sollten Redakteure nur ihre eigenen Artikel sehen oder alle?

## Akzeptanzkriterien
- [ ] Admins und Redakteure können die Artikel-Übersicht sehen
- [ ] Die Übersicht zeigt Titel, Autor, Datum und Veröffentlichungsstatus
- [ ] Der Status ist visuell erkennbar
- [ ] Links zu "Artikel erstellen", "Bearbeiten" und "Anzeigen" sind vorhanden
- [ ] Die Seite ist nur für berechtigte Nutzer zugänglich

## Zusatzaufgaben (Optional)
- Implementiere Paginierung
- Füge Filtermöglichkeiten hinzu (Status, Autor, ArticleType)
- Sortierung nach Datum oder Titel
- Zeige Statistiken an (Anzahl veröffentlichte Artikel, Entwürfe)
- Redakteure sehen nur ihre eigenen Artikel, Admins sehen alle

## Hinweise zum Testen
- **Integrationstests**: Erstelle WebTestCase-Tests, die prüfen:
  - Admin kann Artikel-Übersicht aufrufen (HTTP 200)
  - Redakteur kann Artikel-Übersicht aufrufen (HTTP 200)
  - Andere Rollen erhalten HTTP 403
  - Response enthält Artikel-Informationen im HTML
  - Links zu "Neuer Artikel" sind vorhanden
  - Tabellenstruktur ist korrekt vorhanden
