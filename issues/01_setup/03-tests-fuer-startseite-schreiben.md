# 03 - Tests für die Startseite schreiben

## Ziel
Automatisierte Tests für die Startseite schreiben, um sicherzustellen, dass alle wichtigen Elemente vorhanden sind und die Seite korrekt funktioniert.

## Aufgabenbeschreibung
Erstelle PHPUnit-Tests für die Startseite mit folgenden Mindestanforderungen:
- Test, ob die Startseite unter der Route `/` erreichbar ist
- Test, ob die Navigation vorhanden ist
- Test, ob der Footer vorhanden ist
- Test, ob die wichtigsten Content-Sektionen vorhanden sind

Überlege dir:
- Welche Elemente sind kritisch für die Startseite und müssen getestet werden?
- Wie kannst du testen, ob die Seite responsive ist?
- Welche HTTP-Statuscodes sind für erfolgreiche Requests zu erwarten?
- Solltest du auch testen, ob bestimmte Texte oder Headlines vorhanden sind?
- Wie kannst du sicherstellen, dass die Navigation und der Footer als separate Partials eingebunden sind?

## Technische Hinweise
- Verwende `WebTestCase` als Basis für Controller-Tests
- Erstelle die Test-Klasse in `tests/Controller/DefaultControllerTest.php` (oder `HomeControllerTest.php`)
- Wichtige Assertions:
  - `self::assertResponseIsSuccessful()` - prüft auf HTTP 200
  - `self::assertSelectorExists('selector')` - prüft, ob ein HTML-Element existiert
  - `self::assertSelectorTextContains('selector', 'text')` - prüft auf bestimmten Text
  - `self::assertSelectorCount(1, 'nav')` - prüft, ob ein Element genau einmal vorkommt
- Der Test-Client simuliert Browser-Requests: `$client->request('GET', '/')`
- Tests sollten unabhängig voneinander laufen können
- Dokumentation: [Testing Symfony Applications](https://symfony.com/doc/current/testing.html)
- Dokumentation: [WebTestCase Reference](https://symfony.com/doc/current/testing.html#functional-tests)
- Beispiel: Siehe `tests/Controller/ArticleControllerTest.php`

**Tipp:** Nutze aussagekräftige Testnamen wie `testHomePageIsAccessible()` oder `testNavigationIsPresent()`.

## Akzeptanzkriterien
- [ ] Test für die Erreichbarkeit der Startseite (HTTP 200) ist vorhanden
- [ ] Test für das Vorhandensein der Navigation ist vorhanden
- [ ] Test für das Vorhandensein des Footers ist vorhanden
- [ ] Test für die wichtigsten Content-Sektionen (z.B. Hero-Section) ist vorhanden
- [ ] Mindestens ein Test für Responsive-Design (z.B. Viewport Meta-Tag) ist vorhanden
- [ ] Alle Tests laufen erfolgreich durch
- [ ] Die Tests sind sinnvoll benannt und dokumentieren ihr Testziel

## Tests ausführen
```bash
# Alle Tests ausführen
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
vendor/bin/phpunit

# Nur die Controller-Tests ausführen
vendor/bin/phpunit tests/Controller

# Nur einen spezifischen Test ausführen
vendor/bin/phpunit tests/Controller/DefaultControllerTest.php
```

Oder mit Docker:
```bash
make test
```

## Zusatzaufgaben (Optional)
- Teste, ob Links in der Navigation funktionieren und zu den richtigen Seiten führen
- Teste, ob bestimmte CSS-Klassen vorhanden sind (z.B. Container-Klassen)
- Schreibe einen Test, der prüft, ob Artikel auf der Startseite angezeigt werden
- Teste das Mobile-Menü (falls vorhanden)
- Schreibe einen Test für die Performance (z.B. Ladezeit unter X Sekunden)