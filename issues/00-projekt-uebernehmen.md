# 00 - Projekt als Vorlage übernehmen

## Ziel
Das Projekt-Template in ein eigenes Gruppen-Repository übernehmen und lokal zum Laufen bringen.

## Anleitung

### 1. Repository erstellen (ein Gruppenmitglied)
Ein Gruppenmitglied erstellt das Repository auf dem RWTH GitLab:
- Gehe zu https://git-ce.rwth-aachen.de und erstelle ein neues, leeres Repository
- Wähle **kein** README, keine .gitignore und keine Lizenz (kommt alles aus dem Template)
- Füge alle Gruppenmitglieder als "Maintainer" oder "Developer" zum Projekt hinzu
- Notiere dir die Repository-URL (z.B. `git@git-ce.rwth-aachen.de:gruppe-x/mein-projekt.git`)

### 2. Template-Code herunterladen (ein Gruppenmitglied)
```bash
# Template-Repository klonen
git clone https://git-ce.rwth-aachen.de/ws2526-php/symfony-template.git mein-projekt
cd mein-projekt

# Bestehenden Git-Verlauf entfernen
rm -rf .git

# Neues Git-Repository initialisieren
git init

# Remote zu eurem neuen Repository hinzufügen
git remote add origin <eure-repository-url>

# Initialen Commit erstellen
git add .
git commit -m "Initial commit"

# Zu GitLab pushen
git branch -M main
git push -u origin main
```

### 3. Repository klonen (alle Gruppenmitglieder)
Alle anderen Gruppenmitglieder klonen das Repository:
```bash
git clone <eure-repository-url>
cd mein-projekt
```

### 4. Projekt lokal aufsetzen (alle Gruppenmitglieder)
Folge den Installationsanweisungen in der [README.md](../README.md#installation--erste-schritte).

### 5. Projekt testen
- Öffne `http://localhost:8090` im Browser
- Die Demo-Seite sollte angezeigt werden

## Workflow für Issues in der Gruppe

Für jedes Issue sollte mindestens ein Merge Request erstellt werden. Ihr könnt die Issues unter euch aufteilen.

**Wichtig:** Ein Merge Request sollte möglichst nur aus einem Commit bestehen, um eine saubere Git-Historie zu gewährleisten.

### 1. Feature Branch erstellen
```bash
# Stelle sicher, dass dein main Branch aktuell ist
git checkout main
git pull

# Erstelle einen neuen Branch für dein Issue
git checkout -b feature/01-homepage
```

### 2. An deinem Feature arbeiten
```bash
# Arbeite an deinem Issue und committe regelmäßig während der Entwicklung
git add .
git commit -m "Add navigation component"

git add .
git commit -m "Add hero section"

git add .
git commit -m "Add content sections"

git add .
git commit -m "Add footer component"
```

### 3. Commits zusammenfassen (Squash)

Bevor du deinen Merge Request erstellst, fasse alle Commits zu einem zusammen:

```bash
# Finde heraus, wie viele Commits du gemacht hast
git log --oneline

# Squash die letzten N Commits (z.B. 4 Commits)
git rebase -i HEAD~4
```

Im Editor, der sich öffnet:
- Lass das erste `pick` stehen
- Ändere alle anderen `pick` zu `squash` (oder `s`)
- Speichere und schließe den Editor
- Im nächsten Editor verfasse eine finale Commit Message nach den Regeln

Beispiel:
```
pick abc1234 Add navigation component
squash def5678 Add hero section
squash ghi9012 Add content sections
squash jkl3456 Add footer component
```

Finale Commit Message:
```
Implement homepage with navigation and footer

Add responsive navigation with mobile menu toggle.
Include hero section with call-to-action button.
Add three content sections showcasing main features.
Implement footer with links and social media icons.
```

### 4. Branch mit main synchronisieren (Rebase)

Wenn in der Zwischenzeit andere Änderungen in `main` gemerged wurden:

```bash
# Hole die neuesten Änderungen
git checkout main
git pull

# Kehre zu deinem Feature Branch zurück
git checkout feature/01-homepage

# Rebase deinen Branch auf main
git rebase main
```

Falls Konflikte auftreten:
```bash
# Löse die Konflikte in den betroffenen Dateien
# Dann:
git add .
git rebase --continue
```

### 5. Branch pushen und Merge Request erstellen

```bash
# Branch zu GitLab pushen (force, da wir rebase/squash gemacht haben)
git push -f origin feature/01-homepage
```

Erstelle dann auf GitLab einen Merge Request:
- Gehe zu eurem Repository auf GitLab
- Klicke auf "Merge Requests" → "New Merge Request"
- Wähle deinen Feature Branch als Source und `main` als Target
- Gib dem MR einen aussagekräftigen Titel (z.B. "Implement homepage with navigation and footer")
- Beschreibe im MR, was du implementiert hast
- Weise den MR optional einem anderen Gruppenmitglied zur Review zu
- Erstelle den Merge Request

### 6. Code Review
Ein anderes Gruppenmitglied sollte den Code reviewen:
- Änderungen im GitLab MR durchsehen
- Kommentare hinterlassen, wenn etwas unklar ist
- Den MR approven, wenn alles gut aussieht

### 7. Merge Request mergen
Wenn die Arbeit abgeschlossen ist:
- Überprüfe die Changes im GitLab MR
- Merge den Request in den `main` Branch (nutze "Rebase" wenn möglich)
- Lösche den Feature Branch nach dem Merge

### 8. Nächstes Issue
```bash
# Zurück zum main Branch
git checkout main

# Aktualisiere deinen lokalen main Branch
git pull

# Lösche den alten Feature Branch lokal
git branch -d feature/01-homepage

# Erstelle einen neuen Branch für das nächste Issue
git checkout -b feature/02-error-pages
```

## Commit Message Regeln

**Alle Commit Messages müssen auf Englisch verfasst werden** und sollten folgende Regeln befolgen:

1. Betreffzeile und Body durch Leerzeile trennen
2. Betreffzeile auf 50 Zeichen begrenzen
3. Betreffzeile mit Großbuchstaben beginnen
4. Keinen Punkt am Ende der Betreffzeile
5. Imperativ verwenden ("Add feature" nicht "Added feature")
6. Body auf 72 Zeichen umbrechen
7. Body erklärt was und warum, nicht wie

### Beispiele für gute Commit Messages:

```bash
# Einfacher Commit (nur Betreffzeile)
git commit -m "Add navigation component"

# Commit mit Body (für komplexere Änderungen)
git commit -m "Add user registration form

The form includes email and password fields with validation.
Password confirmation is required to prevent typos.
CSRF protection is enabled by default through Symfony Forms."
```

### Beispiele für schlechte Commit Messages:
```bash
# ❌ Zu lang, kein Imperativ, Punkt am Ende
git commit -m "Added a new navigation component that displays the main menu."

# ❌ Zu vage
git commit -m "Fix bug"

# ❌ Nicht auf Englisch
git commit -m "Navigation hinzugefügt"
```

## Wichtige Git-Konzepte

### Rebase vs. Merge
- **Rebase**: Setzt deine Änderungen auf die neueste Version von `main` → saubere, lineare Historie
- **Merge**: Erstellt einen Merge-Commit → verzweigte Historie

**Nutzt Rebase, wann immer möglich!**

### Squash
- Fasst mehrere Commits zu einem zusammen
- Sorgt für eine übersichtliche Git-Historie
- Ein Merge Request = Ein aussagekräftiger Commit

## Wichtige Hinweise
- Committe während der Entwicklung regelmäßig (später werden sie zusammengefasst)
- Vor dem Merge Request: Commits squashen zu einem einzigen Commit
- Verwende Rebase statt Merge, um deinen Branch zu aktualisieren
- Ein Branch und Merge Request pro Issue (mindestens)
- Kommuniziert in der Gruppe, wer an welchem Issue arbeitet
- Die Demo-Dateien unter `demo/` können als Referenz dienen oder gelöscht werden
- Bei Problemen: Überprüfe die Docker-Logs mit `make logs`

## Tipps für die Gruppenarbeit
- Verteilt die Issues gleichmäßig in der Gruppe
- Sprecht euch ab, bevor ihr am gleichen Feature arbeitet
- Macht regelmäßig `git pull` auf dem main Branch
- Nutzt Merge Requests für Code Reviews untereinander
- Bei Merge-Konflikten: Sprecht euch ab und löst sie gemeinsam
- Squasht eure Commits, bevor ihr einen MR erstellt

## Hilfreiche Git-Befehle
```bash
# Status anzeigen
git status

# Aktuellen Branch anzeigen
git branch

# Zu anderem Branch wechseln
git checkout <branch-name>

# Änderungen committen
git add .
git commit -m "Your commit message in English"

# Branch pushen
git push

# Branch force-pushen (nach rebase/squash)
git push -f

# Aktuellen Stand vom Remote holen
git pull

# Rebase auf main
git rebase main

# Interaktives Rebase (zum Squashen)
git rebase -i HEAD~N  # N = Anzahl der Commits

# Letzten Commit bearbeiten
git commit --amend

# Log anzeigen
git log --oneline
```

## Eigene Entwicklung starten
Ihr könnt nun mit der ersten Aufgabe [01 - Startseite erstellen](01_setup/01-startseite-erstellen.md) beginnen!
