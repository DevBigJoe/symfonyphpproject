# 06 - Artikel-Routes in Admin-Bereich verschieben

## Ziel
Die bestehenden Artikel-Routen vom öffentlichen Bereich (`/blog`) in den Admin-Bereich (`/admin/articles`) verschieben und mit Berechtigungen schützen.

## Aufgabenbeschreibung
Verschiebe die Artikel-Verwaltung in den Admin-Bereich:
- Ändere die Route `/blog/new` zu `/admin/articles/new`
- Schütze die Route mit `#[IsGranted('ROLE_REDAKTION')]`
- Passe die Template-Pfade an
- Die öffentliche Anzeige-Route `/blog/{slug}` kann bleiben

Überlege dir:
- Sollte `/blog/new` komplett entfernt oder nur verschoben werden?
- Wohin soll nach dem Erstellen weitergeleitet werden? (Artikel-Übersicht?)
- Wie strukturierst du Controller und Templates im Admin-Bereich?

## Akzeptanzkriterien
- [ ] Route `/admin/articles/new` ist verfügbar
- [ ] Route ist nur für ROLE_REDAKTION und ROLE_ADMIN zugänglich
- [ ] Formular funktioniert und speichert Artikel

## Hinweise zum Testen
- **Integrationstests**: Erstelle WebTestCase-Tests, die prüfen:
  - Admin kann `/admin/articles/new` aufrufen (HTTP 200)
  - Redakteur kann `/admin/articles/new` aufrufen (HTTP 200)
  - Andere Rollen erhalten HTTP 403
  - Formular-Submission funktioniert und erstellt Artikel in DB
