# 02 - Registrierungsformular erstellen

## Ziel
Ein HTML-Formular für die Benutzerregistrierung erstellen, das E-Mail und Passwort entgegennimmt.

## Aufgabenbeschreibung
Erstelle ein Registrierungsformular mit folgenden Mindestanforderungen:
- Eingabefeld für E-Mail-Adresse
- Eingabefeld für Passwort
- Eingabefeld für Passwort-Wiederholung
- Submit-Button zum Absenden

Überlege dir:
- Welche HTML-Input-Typen sind für die jeweiligen Felder geeignet?
- Wie kannst du die Benutzerfreundlichkeit verbessern (Labels, Platzhalter, etc.)?
- Welche clientseitigen Validierungen sind sinnvoll?
- Wie sollte das Formular gestaltet sein (CSS)?

## Technische Hinweise
- Erstelle eine Form-Klasse manuell in `src/Form/RegistrationFormType.php`
- Verwende Symfony Forms (extend `AbstractType`)
- Symfony Forms bieten automatische CSRF-Protection
- Nutze Twig-Templates für die Darstellung
- HTML5 bietet native Validierung für E-Mail-Felder
- Wichtige Form Types: `EmailType`, `PasswordType`, `RepeatedType`, `SubmitType`
- Dokumentation: [How to Build Forms](https://symfony.com/doc/current/forms.html#building-forms)
- Dokumentation: [Form Types Reference](https://symfony.com/doc/current/reference/forms/types.html)

## Akzeptanzkriterien
- [ ] Das Formular kann E-Mail und Passwort entgegennehmen
- [ ] Passwort-Wiederholung ist vorhanden
- [ ] Das Formular ist benutzerfreundlich gestaltet
- [ ] Fehlermeldungen werden angezeigt, wenn Eingaben ungültig sind
- [ ] Das Formular ist über eine Route erreichbar

## Zusatzaufgaben (Optional)
- Füge weitere Felder hinzu (z.B. Vorname, Nachname)
- Implementiere eine Passwort-Stärke-Anzeige
- Gestalte das Formular responsive für mobile Geräte