# 02 - Customized Fehlerseiten erstellen

## Ziel
Professionelle, ansprechende Fehlerseiten für verschiedene HTTP-Fehler (404, 403, 500, etc.) erstellen, die zum Design der Anwendung passen.

## Aufgabenbeschreibung
Erstelle benutzerdefinierte Fehlerseiten mit folgenden Mindestanforderungen:
- Custom 404 Seite (Seite nicht gefunden)
- Custom 403 Seite (Zugriff verweigert)
- Generische Fehlerseite für alle anderen Fehler
- Die Fehlerseiten sollen das Layout der Hauptseite verwenden (Navigation + Footer)
- Hilfreiche Informationen und Links zur Navigation bereitstellen

Überlege dir:
- Wie kannst du den Nutzer bei einem Fehler bestmöglich unterstützen?
- Welche Informationen sind auf einer Fehlerseite hilfreich?
- Wie kannst du Humor einsetzen, ohne unprofessionell zu wirken?
- Sollten alle Fehlerseiten das gleiche Design haben oder unterschiedlich sein?
- Welche Links können dem Nutzer helfen, wieder auf den richtigen Weg zu kommen?

## Technische Hinweise
- Erstelle Twig-Templates im Verzeichnis `templates/bundles/TwigBundle/Exception/`
- Verfügbare Template-Namen:
  - `error404.html.twig` - für 404 Fehler
  - `error403.html.twig` - für 403 Fehler
  - `error.html.twig` - Fallback für alle anderen Fehler
- Die Templates erhalten folgende Variablen:
  - `status_code` - der HTTP-Statuscode
  - `status_text` - die Statusmeldung
  - `exception.message` - die Exception-Nachricht (nicht in Produktion anzeigen!)
- Verwende `{% extends 'base.html.twig' %}` um das Hauptlayout zu nutzen
- Zum Testen während der Entwicklung: `http://localhost/_error/404`
- Dokumentation: [Symfony Error Pages](https://symfony.com/doc/current/controller/error_pages.html)

**Wichtig:** Die Exception-Nachricht sollte nur im Dev-Modus angezeigt werden, nicht in Produktion (aus Sicherheitsgründen).

## Akzeptanzkriterien
- [ ] Eine custom 404 Fehlerseite existiert und wird angezeigt
- [ ] Eine custom 403 Fehlerseite existiert und wird angezeigt
- [ ] Eine generische Fehlerseite für andere HTTP-Fehler existiert
- [ ] Die Fehlerseiten verwenden das Hauptlayout (Navigation + Footer)
- [ ] Die Seiten enthalten hilfreiche Links (z.B. zur Startseite, Kontakt)
- [ ] Das Design ist konsistent mit dem Rest der Anwendung
- [ ] Die Fehlerseiten wurden während der Entwicklung getestet (via `/_error/404`)

## Zusatzaufgaben (Optional)
- Erstelle spezifische Seiten für weitere Fehler (500, 503)
- Füge eine Suchfunktion auf der 404-Seite hinzu
- Implementiere ein lustiges Easter Egg oder Animation
- Erstelle statische HTML-Versionen der Fehlerseiten: `php bin/console error:dump`
- Logge 404-Fehler, um defekte Links zu identifizieren
- Erstelle einen Custom Error Controller für erweiterte Logik