<?php

/**
 * Obsidian Flame — a dark ui-theme plugin for Phlix.
 *
 * @copyright 2026 Joe Huss <detain@interserver.net>
 * @license   MIT
 */

declare(strict_types=1);

namespace Phlix\ObsidianFlame;

use Phlix\Shared\Plugin\LifecycleInterface;
use Phlix\Theming\ThemeSourceInterface;
use Psr\Container\ContainerInterface;

/**
 * Obsidian Flame theme plugin.
 *
 * A dark theme with ember orange accents against deep obsidian backgrounds.
 * Extends the built-in `midnight` theme, layering the obsidian palette over
 * the base's status colors and semantic tokens.
 *
 * @package Phlix\ObsidianFlame
 * @since 1.0.0
 */
final class ObsidianFlamePlugin implements LifecycleInterface, ThemeSourceInterface
{
    /**
     * Canonical provenance key for this source.
     */
    public const SOURCE_NAME = 'obsidian-flame';

    /**
     * Nothing to do — the host registers the themes off the `instanceof`.
     *
     * @param ContainerInterface $container The host container (unused).
     */
    public function onEnable(ContainerInterface $container): void
    {
    }

    /**
     * Nothing to do — the host deregisters this source by name on disable.
     */
    public function onDisable(): void
    {
    }

    /**
     * A theme plugin subscribes to no events.
     *
     * @return array<class-string, string> Always empty.
     */
    public function subscribedEvents(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function themeSourceName(): string
    {
        return self::SOURCE_NAME;
    }

    /**
     * @inheritDoc
     *
     * @return list<array<array-key, mixed>>
     */
    public function providedThemes(): array
    {
        return [
            [
                'id' => 'obsidian-flame',
                'name' => 'Obsidian Flame',
                'dark' => true,
                'extends' => 'midnight',
                'tokens' => [
                    // Accent ramp — ember orange.
                    '--accent' => '#ff6b35',
                    '--accent-hover' => '#ff8c5a',
                    '--accent-active' => '#e05520',
                    '--accent-soft' => 'rgba(255, 107, 53, 0.12)',
                    // 0.65 clears WCAG 1.4.11's 3:1 non-text minimum against
                    // every painted ground (tightest: 3.11 on --surface-3);
                    // the old 0.45 sat at 2.19 — focus was barely perceivable.
                    '--accent-ring' => 'rgba(255, 107, 53, 0.65)',
                    '--accent-text' => '#ffe4d6',

                    // Background + elevation stack.
                    '--bg' => '#08070a',
                    '--surface' => '#110e12',
                    '--surface-2' => '#1c171d',
                    '--surface-3' => '#282127',
                    '--surface-glass' => 'rgba(17, 14, 18, 0.65)',
                    '--surface-glass-strong' => 'rgba(8, 7, 10, 0.85)',

                    // Text ramp.
                    '--text' => '#f0ebe4',
                    '--text-muted' => '#a69e96',
                    // #978d86 clears WCAG 1.4.3's 4.5:1 AA body-text minimum on
                    // every ground (tightest: 4.84 on --surface-3) while staying
                    // strictly below --text-muted on all grounds; the old
                    // #6d6460 sat at 3.49 on --bg and 2.72 on --surface-3.
                    '--text-subtle' => '#978d86',
                    '--text-faint' => '#3d3735',
                    '--text-on-accent' => '#08070a',

                    // Borders.
                    '--border' => '#2a2328',
                    '--border-subtle' => '#1a161a',
                    '--border-strong' => '#3d353b',

                    // Atmosphere.
                    '--grain-opacity' => '0.035',
                    '--vignette' => 'rgba(0, 0, 0, 0.7)',
                    '--ambient' => 'rgba(255, 107, 53, 0.18)',

                    // Legacy `--color-*` aliases — ONLY the ones the shipped
                    // SPA still reads, set wherever the corresponding modern
                    // token is overridden (flattening loses CSS's var()
                    // following, so an override moves both halves explicitly).
                    // The 13 zero-consumer aliases stay omitted on purpose —
                    // see the README's alias note.
                    '--color-bg' => '#08070a',
                    '--color-surface' => '#110e12',
                    '--color-text' => '#f0ebe4',
                    '--color-text-muted' => '#a69e96',
                    '--color-border' => '#2a2328',
                ],
            ],
        ];
    }
}
