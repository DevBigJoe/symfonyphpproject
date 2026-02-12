# Template Dokumentation

## Übersicht

### Templates
- **`demo/base.html.twig`** - Haupt-Template mit Navigation und Footer
- **`demo/_navigation.html.twig`** - Sticky Header mit Mobile Menu
- **`demo/_footer.html.twig`** - Mehrspaltige Footer-Komponente
- **`demo/demo.html.twig`** - Beispiel-Seite mit allen Layout-Varianten (Route: `/demo`)

### 2. Assets
- **`assets/app.js`** - Alpine.js Initialisierung
- **`assets/styles/app.css`** - Tailwind Konfiguration mit Custom Theme

## Custom Farben

### Primary (Indigo/Blue)
```css
bg-primary-50 bis bg-primary-950
text-primary-50 bis text-primary-950
```

### Accent (Amber/Orange)
```css
bg-accent-50 bis bg-accent-950
text-accent-50 bis text-accent-950
```

## Container-Klassen

### Vordefinierte Container
```twig
{# Schmaler Container für Text/Blog (max-w-3xl) #}
<div class="container-narrow">...</div>

{# Breiter Container für normale Inhalte (max-w-7xl) #}
<div class="container-wide">...</div>

{# Section Spacing #}
<section class="section-spacing">...</section>
```

### Layout-Varianten

#### Einspaltig (Text/Blog)
```twig
<div class="container-narrow">
    <div class="prose prose-lg prose-slate max-w-none">
        <!-- Content -->
    </div>
</div>
```

#### Zweispaltig (Features)
```twig
<div class="container-wide">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Items -->
    </div>
</div>
```

#### Dreispaltig (Cards/Services)
```twig
<div class="container-wide">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Cards -->
    </div>
</div>
```

#### Full-Width (Hero, Footer)
```twig
<section class="w-full">
    <div class="container-wide section-spacing">
        <!-- Content -->
    </div>
</section>
```

## Alpine.js Komponenten

### Mobile Menu
Bereits in `_navigation.html.twig` implementiert mit:
- Slide-in Animation von rechts
- Backdrop blur
- Close on click outside
- Hamburger Button

### Dropdown Menu
```twig
<div x-data="{ open: false }">
    <button @click="open = !open">Menu</button>
    <div x-show="open" x-transition>
        <!-- Dropdown Items -->
    </div>
</div>
```

### Modal

Das Modal verwendet `Alpine.store()` für globalen State. Der Store ist bereits in `assets/app.js` konfiguriert.

**Button zum Öffnen:**
```twig
<button x-data @click="$store.modal.show()">Modal öffnen</button>
```

**Modal-Component:**
```twig
<div x-data @keydown.escape.window="$store.modal.hide()">
    <!-- Backdrop -->
    <div
        x-show="$store.modal.open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        @click="$store.modal.hide()"
        class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50"
        style="display: none;"
    ></div>

    <!-- Modal Panel -->
    <div
        x-show="$store.modal.open"
        x-transition
        @click.away="$store.modal.hide()"
        class="fixed inset-0 flex items-center justify-center p-4 z-50 pointer-events-none"
        style="display: none;"
    >
        <div class="bg-white rounded-2xl p-8 pointer-events-auto">
            <button @click="$store.modal.hide()">Schließen</button>
            <!-- Modal Content -->
        </div>
    </div>
</div>
```

**Store-Methoden:**
- `$store.modal.show()` - Modal öffnen
- `$store.modal.hide()` - Modal schließen
- `$store.modal.toggle()` - Modal umschalten

### Accordion
```twig
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>
        <!-- Accordion Content -->
    </div>
</div>
```

## Eigene Seiten erstellen

### Neues Template erstellen:
```twig
{% extends 'base.html.twig' %}

{% block title %}Ihr Seitentitel{% endblock %}
{% block description %}Ihre Meta-Beschreibung{% endblock %}

{% block body %}
    <section class="section-spacing">
        <div class="container-wide">
            <!-- Ihr Content -->
        </div>
    </section>
{% endblock %}
```

### Navigation anpassen
1. `demo/_navigation.html.twig` in `templates` kopieren
2. `templates/_navigation.html.twig` bearbeiten:
    - Logo/Brand-Bereich
    - Menü-Punkte
    - CTA-Button

### Footer anpassen
1. `demo/_footer.html.twig` in `templates` kopieren
2. `templates/_footer.html.twig` bearbeiten:
   - Spalten-Inhalte
   - Social-Media-Links
   - Copyright-Text

## Spacing-System

### Section-Spacing
- Mobile: `py-12`
- Tablet: `py-16`
- Desktop: `py-24`

### Container-Padding
- Mobile: `px-4`
- Tablet: `px-6`
- Desktop: `px-8`

### Element-Gaps
- Standard: `gap-6`
- Tablet: `gap-8`
- Desktop: `gap-12`

### Farben ändern
Bearbeiten Sie `assets/styles/app.css` im `@theme` Block:
```css
@theme {
    --color-primary-500: #ihre-farbe;
    --color-accent-500: #ihre-farbe;
}
```
