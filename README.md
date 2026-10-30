# Obsidian Flame Theme

A dark UI theme plugin for Phlix with ember orange accents against deep obsidian backgrounds.

## Overview

Obsidian Flame extends the built-in `midnight` theme, layering a rich obsidian palette over the base's status colors and semantic tokens. The theme features a deep obsidian background with warm ember orange accents that create a distinctive, atmospheric visual experience.

## Color Palette

### Accent Colors (Ember Orange)
- **Primary Accent**: `#ff6b35` — ember orange
- **Accent Hover**: `#ff8c5a`
- **Accent Active**: `#e05520`
- **Accent Soft**: `rgba(255, 107, 53, 0.12)`
- **Accent Ring**: `rgba(255, 107, 53, 0.65)` — focus ring ≥3:1 on every ground
- **Accent Text**: `#ffe4d6`

### Background & Surface
- **Background**: `#08070a` — deep obsidian black
- **Surface**: `#110e12`
- **Surface 2**: `#1c171d`
- **Surface 3**: `#282127`
- **Surface Glass**: `rgba(17, 14, 18, 0.65)`
- **Surface Glass Strong**: `rgba(8, 7, 10, 0.85)`

### Text
- **Text Primary**: `#f0ebe4` — warm off-white
- **Text Muted**: `#a69e96`
- **Text Subtle**: `#978d86` — ≥4.5:1 AA on every ground
- **Text Faint**: `#3d3735`
- **Text On Accent**: `#08070a`

### Borders
- **Border**: `#2a2328`
- **Border Subtle**: `#1a161a`
- **Border Strong**: `#3d353b`

### Atmosphere
- **Grain Opacity**: `0.035`
- **Vignette**: `rgba(0, 0, 0, 0.7)`
- **Ambient Glow**: `rgba(255, 107, 53, 0.18)`

## Installation

Do **not** install this with `composer require` into the Phlix server. The
server's plugin loader scans `var/plugins/<plugin-name>/` — it never looks in
its own `vendor/`, so a composer-require'd copy sits on disk invisible to the
theme picker. (`composer require` is only meaningful for a library that embeds
this package as its own dependency, which the server deliberately is not.)

### The supported route: the plugin catalog

The Phlix server installs plugins from a **catalog** (`plugins.json`). A catalog
entry pins an exact commit (`ref`) and the sha256 of that commit's tarball
(`artifactSha256`), and the server verifies those bytes before it extracts
anything. Without a pin, `PluginLoader::install()` refuses outright — that is a
deliberate default-deny, not a bug to work around.

**Admin UI** — *Admin → Plugins* (`/app/admin/plugins`), find **Obsidian Flame
Theme**, press **Install**, then flip its switch to enable it. (The page posts
the catalog entry's `repo` URL to `POST /api/v1/admin/plugins/install`; the
server matches that URL against the catalog and supplies the pin itself.)

**CLI** — the same thing, from the server root:

```bash
php bin/phlix plugin:install https://github.com/detain/phlix-plugin-obsidian-flame-theme
php bin/phlix plugin:enable  phlix-plugin-obsidian-flame-theme
php bin/phlix plugin:list
```

Two details that are easy to get wrong:

* **Install is not enable.** A fresh install lands *disabled* — enabling is
  what registers the theme source, so until you flip the switch (or run
  `plugin:enable`) the theme picker will not show **Obsidian Flame**. That is
  by design, not a failed install.
* **Pass the repository URL, not the plugin name.** `pinFor()` will match a
  bare `phlix-plugin-obsidian-flame-theme` against the catalog and find the
  pin, but the source string itself is then handed to the installer unchanged,
  and `phlix-plugin-obsidian-flame-theme` is not something it can download. The
  **`repo` URL** is rewritten to `…/archive/<pinned-ref>.tar.gz`; the name is
  not.

### A local build, for developing your own theme

While you are iterating on a fork there is no catalog entry yet. Build a
tarball and install it over `file://`, which is exempt from the pin requirement
because the bytes are already yours:

```bash
tar --exclude=vendor --exclude=.git -czf /tmp/obsidian-flame.tar.gz phlix-plugin-obsidian-flame-theme/
php bin/phlix plugin:install file:///tmp/obsidian-flame.tar.gz
php bin/phlix plugin:enable  phlix-plugin-obsidian-flame-theme
```

A `.zip` works identically, and the archive may have a single top-level
directory (the shape `git archive` and GitHub's codeload produce) — the
installer lifts its contents up automatically.

## Requirements

- PHP 8.3+ (CI runs `8.3` and `8.4`)
- Phlix 0.44.0+

## Theme Tokens

The theme provides CSS custom properties (tokens) that can be referenced in your components:

```css
/* Usage example */
.my-component {
  background-color: var(--surface);
  color: var(--text);
  border-color: var(--border);
}

.my-button {
  background-color: var(--accent);
  color: var(--text-on-accent);
}
```

The payload also carries the live half of the legacy `--color-*` alias family
(`--color-bg`, `--color-surface`, `--color-text`, `--color-text-muted`,
`--color-border`), each set explicitly to the same value as the modern token it
mirrors: theme payloads are flattened by the host, which loses CSS's `var()`
following, so a theme that overrides one half of a pair must set both. The 13
zero-consumer aliases (`--color-primary{,-hover,-active}`,
`--color-surface-{hover,elevated,active}`, `--color-text-secondary`,
`--color-text-subtle`, `--color-border-subtle`, `--color-{error,success,
warning,info}-bg`) are omitted on purpose — nothing in the shipped SPA reads
them. If you fork this theme, keep the habit: only add an alias you can point
at a real `var()` consumer. (Status tokens are inherited whole from
`midnight`, pairs included, so their four live aliases need no copy here.)

## Development

```bash
composer install
```

The same three checks `.github/workflows/test.yml` runs:

```bash
vendor/bin/phpunit
vendor/bin/phpstan analyse -c phpstan.neon
vendor/bin/phpcs --standard=phpcs.xml
```

Pass the config files rather than bare paths: `phpstan.neon` scans `dev-stubs/` for the host-only `Phlix\Theming\ThemeSourceInterface`, and `phpcs.xml` covers `src`, `tests`, and `dev-stubs`. `composer phpstan` and `composer phpcs` are shortcuts for the last two.

## License

MIT License - see [LICENSE](LICENSE) for details.
