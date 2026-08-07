# Obsidian Flame Theme

A dark UI theme plugin for Phlix with ember orange accents against deep obsidian backgrounds.

## Overview

Obsidian Flame extends the built-in `midnight` theme, layering a rich obsidian palette over the base's status colours and semantic tokens. The theme features a deep obsidian background with warm ember orange accents that create a distinctive, atmospheric visual experience.

## Color Palette

### Accent Colors (Ember Orange)
- **Primary Accent**: `#ff6b35` — ember orange
- **Accent Hover**: `#ff8c5a`
- **Accent Active**: `#e05520`
- **Accent Soft**: `rgba(255, 107, 53, 0.12)`
- **Accent Ring**: `rgba(255, 107, 53, 0.45)`
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
- **Text Subtle**: `#6d6460`
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

```bash
composer require detain/phlix-plugin-obsidian-flame-theme
```

## Requirements

- PHP 8.3+
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

## License

MIT License - see [LICENSE](LICENSE) for details.
