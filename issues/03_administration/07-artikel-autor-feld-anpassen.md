# 07 - Autor in Artikel anpassen

## Ziel
Das Autor-Feld von einem String zu einer Relation zur User-Entity ändern, sodass der angemeldete Nutzer automatisch als Autor gesetzt wird.

## Aufgabenbeschreibung
Passe die Article-Entity an:
- Ändere das `author`-Feld von `string` zu einer Relation zur User-Entity
- Setze den Autor automatisch beim Erstellen auf den aktuell angemeldeten Nutzer
- Passe die Getter-Methode entsprechend an

Überlege dir:
- Welche Relation (n-n, 1-n, ...) zwischen User und Article besteht?
- Wie gehst du mit bestehenden Artikeln um, die einen String als Author haben?
- Sollte das Author-Feld nullable sein?
- Wird der Autor beim Bearbeiten änderbar sein?
- Wie zeigst du den Autor-Namen in Templates an?

## Technische Hinweise
- Passe Getter/Setter an
- Erstelle Migration: `php bin/console doctrine:migrations:diff`
- Im Controller beim Erstellen: `$article->setAuthor($this->getUser());`
- Dokumentation: [Doctrine Associations](https://symfony.com/doc/current/doctrine/associations.html)

## Akzeptanzkriterien
- [ ] Author-Feld ist eine Relation zur User-Entity
- [ ] Migration wurde erstellt und ausgeführt
- [ ] Beim Erstellen wird der angemeldete Nutzer als Autor gesetzt
- [ ] Templates zeigen den Autor-Namen korrekt an

## Zusatzaufgaben (Optional)
- Zeige in der Nutzer-Detailseite alle Artikel des Nutzers an

## Hinweise zum Testen
- **Integrationstests**: Teste, dass beim Erstellen eines Artikels:
  - Der aktuell angemeldete Nutzer als Autor gesetzt wird
  - Die Relation in der Datenbank korrekt gespeichert wird
  - Der Autor korrekt abgerufen werden kann
