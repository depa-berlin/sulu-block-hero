# Code-Review: depa/sulu-block-hero

Review vom 2026-07-02 (drei parallele Review-Agents: PHP/Symfony, Sulu-XML/Twig, Paket-Integration/Doku).
Abarbeiten von oben nach unten — Kritisch und Major vor einem Release, Minor nach Kapazität.

**TL;DR:** Package strukturell sauber (Namespaces, Autoloading, XML-Schema, Fragmente),
aber `block--hero-promo-image` ist ein unfertiger Copy-Paste-Fork des Call2Action-Blocks
und aktuell nicht auslieferungsfähig (Rendering-Crash, fehlt in `_slots.yaml`).

---

## Kritisch

- [ ] **1. Rendering-Crash im Promo-Block: `image_mobile` wird nie gesetzt**
  `Resources/views/includes/blocks/block--hero-promo-image.html.twig:23` greift auf
  `image_mobile.thumbnails['768x450']` zu, aber es fehlt das
  `{% set image_mobile = sulu_resolve_media(content.image_mobile, app.request.locale) %}`
  (vgl. `block--hero-call2action.html.twig:3`).
  *Folge:* Mit `strict_variables` (Sulu-Default in dev/preview) Exception beim Rendern;
  ohne strict leeres `<img src="">`.

- [ ] **2. Desktop-Bild im Mobile-Retina-`srcset` (hero-content)**
  `Resources/views/includes/blocks/block--hero-content.html.twig:25` nutzt
  `image.thumbnails['767x-x2']` statt `image_mobile.thumbnails['767x-x2']`.
  *Folge:* Retina-Mobilgeräte bekommen bei `enable_x2` das Desktop-Motiv; ist nur das
  Mobile-Bild gepflegt (beide Properties optional), ist `image` null → Exception.

## Major

- [ ] **3. `block--hero-promo-image` fehlt in `_slots.yaml`**
  `Resources/config/blocks/_slots.yaml` listet unter `section` nur c2a/content/image,
  unter `container` nur c2a. Der Promo-Block ist nach `sulu:blocks:generate-slots`
  nirgends auswählbar — toter Code. In die gewünschten Slots aufnehmen.

- [ ] **4. Promo-Template ignoriert `element_headline`**
  `block--hero-promo-image.html.twig:27` rendert hartkodiert `<h1>`, obwohl die XML
  (`block--hero-promo-image.xml`) h1/h2/h3/p anbietet. Redakteurswahl wirkungslos;
  Gefahr mehrerer `<h1>` pro Seite (SEO/A11y). Dynamisches Element rendern
  (Vorbild: hero-image/hero-content).

- [ ] **5. `config_image.xml`-Fragment in Promo + Call2Action wirkungslos**
  Beide XMLs inkludieren das Fragment (`enable_x2`, `loading`, `fetchpriority_high`,
  `is_decorative`), aber die Templates lesen keines dieser Felder —
  `loading="eager"` und `fetchpriority="high"` sind hartkodiert
  (`block--hero-promo-image.html.twig`, `block--hero-call2action.html.twig`).
  Entweder im Template auswerten (Vorbild: hero-image/hero-content) oder
  Fragment aus den XMLs entfernen. Insbesondere `is_decorative` (A11y) muss wirken.

- [ ] **6. Falsche CSS-Klasse im Promo-Block**
  `block--hero-promo-image.html.twig:3` rendert `block--hero-call2action` als
  Wrapper-Klasse → korrekt: `block--hero-promo-image`.

- [ ] **7. `Bundle::getPath()` zeigt auf `src/` statt Paket-Root**
  `src/SuluBlockHeroBundle.php`: Die Bundle-Klasse liegt in `src/`, `Resources/` im
  Paket-Root. Konventionsbasierte Auflösung (Twig-Namespace `@SuluBlockHero`,
  pfadbasierte Discovery) läuft ins Leere. Fix:
  `public function getPath(): string { return \dirname(__DIR__); }`

- [ ] **8. `prepend()` ist ungetestet**
  `tests/Unit/DependencyInjection/SuluBlockHeroExtensionTest.php` testet nur `load()`.
  Prepend registriert aber Block-XMLs und Twig-Pfade — die eigentliche
  Integrationsfläche. Test ergänzen: twig-Extension am ContainerBuilder registrieren,
  `prepend()` aufrufen, `$container->getExtensionConfig('twig')` auf erwartete Pfade
  prüfen.

- [ ] **9. LICENSE-Datei fehlt**
  `README.md` verlinkt `LICENSE` ("See [LICENSE](LICENSE) for details."), die Datei
  existiert nicht. Lizenztext ergänzen (proprietär → umso wichtiger).

- [ ] **10. Installationsanleitung unvollständig (repositories)**
  `composer.json`-`repositories`-Einträge wirken nur im Root-Projekt. README muss
  dokumentieren, dass die konsumierende App eigene VCS-Einträge für
  `depa/sulu-block-hero`, `depa/sulu-block-helper` und `depa/sulu-block-content`
  braucht — sonst schlägt `composer require depa/sulu-block-hero` fehl.

## Minor

- [ ] **11. `@dev`-Constraints pinnen**
  `composer.json`: `depa/sulu-block-helper` und `depa/sulu-block-content` stehen auf
  `@dev` (= `*@dev`) — jeder Upstream-Breaking-Change wird ungefiltert gezogen.
  Besser `dev-main` oder Versions-Alias.

- [ ] **12. Sulu-Abhängigkeit nicht per Composer abgesichert**
  README verlangt "Sulu CMS 3.0+", `composer.json` hat kein `require`/`conflict` auf
  `sulu/sulu`. Explizit ergänzen. Nebenbefund: XML-Kommentare verweisen auf
  Sulu-2.x-Doku (`block--hero-content.xml`, docs.sulu.io/en/2.2) — prüfen, ob 2.x
  oder 3.x gemeint ist.

- [ ] **13. Property-Typ `config_line` verifizieren**
  `Resources/config/_fragments/attr_class.xml:6` und `attr_id.xml:6` nutzen
  `config_line` — kein Sulu-Standardtyp (Standard: `text_line`). Funktioniert nur,
  wenn `depa/sulu-block-helper` den Typ registriert; sonst bricht das Admin-Formular.

- [ ] **14. Null-/Empty-Guards im Promo-Template**
  `block--hero-promo-image.html.twig:11–28`: kein `{% if image %}`-Guard (Preview
  ohne Bild → Exception/leere src), `headline`/`text` ohne Empty-Guard (leere
  `<h1>`/`<p>`), `text` ohne `nl2br` (Zeilenumbrüche gehen verloren; vgl. c2a).

- [ ] **15. `sulu_block_preview` fehlt in hero-image**
  `block--hero-image.html.twig` rendert kein `sulu_block_preview(content)` (alle
  anderen Templates tun es) → Preview-Highlighting im Admin funktioniert nicht.

- [ ] **16. Externer Platzhalter `placehold.co` in hero-image**
  `block--hero-image.html.twig:33`: externe Abhängigkeit im Admin-Preview
  (CSP/Datenschutz/Offline). Lokales Asset oder Inline-SVG verwenden.

- [ ] **17. Link-Target/`rel`-Handling**
  `block--hero-call2action.html.twig:8`, `block--hero-promo-image.html.twig:6`,
  `block--hero-content.html.twig:16`: `target="{{ view.link.target }}"` ohne
  `|default('_self')`, kein `rel="noopener"` bei `_blank`; c2a/promo rendern zudem
  `<a>` ohne `href`, wenn kein Link gepflegt ist (Vorbild: `<a>`/`<div>`-Fallback in
  hero-content).

- [ ] **18. Tests schärfen**
  `SuluBlockHeroExtensionTest.php`: exakte Blockliste via `assertSame` statt
  `assertContains` (Z. 75–84); `children`-Test ist vakuum-grün bei leerem Array
  (Z. 86–98) — konkrete Kind-Referenzen (`block--content-*`) prüfen.

- [ ] **19. `phpstan-symfony` einbinden oder entfernen**
  Steht in `composer.json` (require-dev), wird aber in `phpstan.neon` nicht
  aktiviert (kein `includes:`, kein extension-installer) — tote Abhängigkeit.

- [ ] **20. PHPUnit-Härtung**
  `phpunit.xml.dist`: `cacheDirectory=".phpunit.cache"` (von .gitignore bereits
  erwartet), `failOnWarning="true"`, `failOnRisky="true"`,
  `displayDetailsOnTestsThatTriggerDeprecations="true"` ergänzen.

- [ ] **21. GitHub-URLs `depa-berlin` verifizieren**
  `composer.json:20,24`: letzte `depa-berlin`-Reste in den VCS-URLs. Korrekt, falls
  die GitHub-Org weiterhin so heißt — sonst tote URLs. Einmalig prüfen.

## Nitpicks

- [ ] **22.** README: Slot-Mechanik (`_slots.yaml`) und die acht eingebetteten
  `block--content-*`-Child-Typen aus `depa/sulu-block-content` dokumentieren;
  ebenso die Registrierung der Block-XMLs als Sulu-Template-Pfad.
- [ ] **23.** `alt` und `title` mit identischem Wert (c2a:18, promo:17,24) — redundant
  für Screenreader; besser `description` als `title` (wie hero-image/hero-content).
- [ ] **24.** `element_headline`-Options-Titel nur `lang="en"` (c2a/promo-XML), übrige
  Metas sind zweisprachig — vereinheitlichen.
- [ ] **25.** Übersetzung „auf dem letzten Drücker" für `lazy`
  (`config_image.xml:37`) → „verzögert (lazy)".
- [ ] **26.** Einrückung in `block--hero-call2action.html.twig:44–48` und
  `block--hero-content.html.twig:69–75` spiegelt die Tag-Struktur nicht wider.
- [ ] **27.** CI (`.github/workflows/ci.yml`): `composer install` braucht Zugriff auf
  die privaten VCS-Repos — Token konfigurieren, sonst schlägt der Workflow fehl.

---

## Positiv verifiziert (kein Handlungsbedarf)

- Umbenennung `depa-berlin/` → `depa/` vollständig (Reste nur in GitHub-URLs, s. Punkt 21);
  keine Reste von `BlockMetadataLoaderTrait` oder alten Namespaces.
- PSR-4-Autoload, Extension-Naming und README-Snippets (`config/bundles.php`, Blocknamen)
  stimmen exakt mit dem Code überein.
- XInclude-Fragmente, XML-Namespaces/Schema und Typenwahl sind Sulu-konform.
- Kein `|raw` im gesamten Bundle, Autoescaping greift überall — XSS-seitig sauber.
- `block--hero-image` und `block--hero-content` werten `loading`/`fetchpriority`/
  `is_decorative`/`enable_x2` vorbildlich aus (Qualitätsmaßstab für die anderen Templates).
- `composer validate` läuft durch (nur `@dev`-Warnungen, s. Punkt 11).
