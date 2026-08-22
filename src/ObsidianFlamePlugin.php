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
 * the base's status colours and semantic tokens.
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
                    '--accent-ring' => 'rgba(255, 107, 53, 0.45)',
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
                    '--text-subtle' => '#6d6460',
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

                    // Legacy `--color-*` aliases.
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
