# 01 - Startseite mit Navigation und Footer erstellen

## Ziel
Eine ansprechende Startseite (Homepage) für die Webanwendung erstellen, die eine professionelle Navigation und einen informativen Footer enthält.

## Aufgabenbeschreibung
Erstelle eine Startseite mit folgenden Mindestanforderungen:
- Responsive Navigation mit Logo/Branding
- Hero-Section mit aussagekräftigem Titel und Call-to-Action
- Mindestens 2-3 Content-Sektionen zur Präsentation von Features/Services
- Footer mit Links, Kontaktinformationen und Social Media Icons
- Die Seite soll auf allen Geräten (Desktop, Tablet, Mobile) gut aussehen

Als Vorlage kann die Demo-Seite (`demo/base.html.twig`, `demo/layout.html.twig`, `demo/_navigation.html.twig`, `demo/_footer.html.twig`) verwendet werden. Du kannst aber auch eine eigene Version mit eigenem Design erstellen.

Überlege dir:
- Welche Informationen sollen auf der Startseite prominent präsentiert werden?
- Wie soll die Navigation strukturiert sein? Welche Menüpunkte sind wichtig?
- Welche Call-to-Actions möchtest du einbauen?
- Wie kannst du die Seite visuell ansprechend gestalten?
- Welche Informationen gehören in den Footer?

## Technische Hinweise
- Erstelle einen Controller für die Startseite mit der Route `/`
- Verwende Twig Templates zur Strukturierung der Seite
- Die Navigation und der Footer sollten als separate Partials erstellt werden (`_navigation.html.twig`, `_footer.html.twig`)
- Nutze Tailwind CSS für das Styling (v4 ist bereits eingerichtet)
- Alpine.js steht für interaktive Elemente zur Verfügung (z.B. Mobile Menu Toggle)
- Die Container-Klassen sind bereits definiert: `container-narrow`, `container-wide`, `section-spacing`
- Dokumentation: [Twig Templates](https://symfony.com/doc/current/templates.html)
- Dokumentation: [Tailwind CSS](https://tailwindcss.com/docs)
- Dokumentation: [Alpine.js](https://alpinejs.dev/)

## Akzeptanzkriterien
- [ ] Die Startseite ist über die Haupt-Route (`/`) erreichbar
- [ ] Eine responsive Navigation ist implementiert und funktioniert auf Desktop und Mobile
- [ ] Die Seite enthält mindestens eine Hero-Section und 2-3 Content-Sektionen
- [ ] Ein Footer mit sinnvollen Links und Informationen ist vorhanden
- [ ] Die Seite ist responsive und funktioniert auf verschiedenen Bildschirmgrößen
- [ ] Die Navigation und der Footer sind als wiederverwendbare Partials angelegt
- [ ] Das Design ist konsistent und professionell

## Zusatzaufgaben (Optional)
- Füge Animationen mit Alpine.js hinzu (z.B. Scroll-Effekte)
- Implementiere ein Dropdown-Menü in der Navigation
- Erstelle eine Breadcrumb-Navigation
- Füge einen "Zurück nach oben"-Button hinzu
- Integriere ein Newsletter-Anmeldeformular im Footer
