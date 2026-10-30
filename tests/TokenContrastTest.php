<?php

declare(strict_types=1);

namespace Phlix\ObsidianFlame\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Phlix\ObsidianFlame\ObsidianFlamePlugin;

/**
 * WCAG contrast laws computed from the SHIPPED token values — no literals are
 * duplicated here, so changing a token in the payload immediately re-tests the
 * promise the README palette table makes about it.
 *
 * Laws pinned (theme-obsidian-flame__webux M1/M2):
 *  1. --accent-ring composited over EVERY painted ground (--bg, --surface,
 *     --surface-2, --surface-3) clears the WCAG 1.4.11 non-text minimum 3:1.
 *     The shipped 0.45 alpha sat at 2.19:1 on --bg — a focus indicator the
 *     review could not perceive; the cure lifted it to 0.65.
 *  2. --text-subtle clears the WCAG 1.4.3 body-text AA minimum 4.5:1 on every
 *     ground (the old #6d6460 was 3.49:1 on --bg and 2.72:1 on --surface-3;
 *     the cure #978d86 never drops below 4.84).
 *  3. The text ramp is STRICTLY ORDERED on every ground:
 *     --text > --text-muted > --text-subtle > --text-faint in prominence,
 *     i.e. contrast descends monotonically along the list. An equal or
 *     inverted step means two levels are visually the same ink — dead weight
 *     in the ramp (and a failed claim of four distinct levels).
 *  4. --text-faint clears the AA body-text minimum 4.5:1 on the grounds the ui
 *     actually paints placeholder ink onto: --bg, --surface, and the auth
 *     input ground (--surface composited over --bg at AuthField's 0.8 alpha).
 *     Placeholder text is not a disabled control, so it is AA-bound; the old
 *     #3d3735 sat at 1.72:1 on --bg (majors review obs #3) and the cure
 *     #857b76 never drops below 4.65 on the three grounds. The topmost
 *     elevations carry no faint text in the shipped flows and keep only the
 *     ramp-order law — the same three-ground scoping jade's reviewed 0e3b3dc
 *     and mystic's W-min136 faint cures shipped [R-THEME-OBSIDIAN-FLAME-01].
 */
#[CoversClass(ObsidianFlamePlugin::class)]
final class TokenContrastTest extends TestCase
{
    /** WCAG 1.4.11 non-text contrast minimum. */
    private const NON_TEXT_MIN = 3.0;

    /** WCAG 1.4.3 body-text AA contrast minimum. */
    private const BODY_TEXT_MIN = 4.5;

    /** @var list<string> every ground a token can be painted onto */
    private const GROUNDS = ['--bg', '--surface', '--surface-2', '--surface-3'];

    /**
     * Alpha the SPA paints `--surface` at for an auth input field.
     *
     * The shared ui's AuthField gives its input a translucent card ground
     * (src/components/auth/AuthField.vue: `.authfield__input { background:
     * color-mix(in srgb, var(--surface) 80%, transparent); }`), so the field's
     * real ground is `--surface` composited onto `--bg` at this alpha. The
     * value is family-canonical — jade and mystic pin the same constant.
     */
    private const INPUT_SURFACE_ALPHA = 0.8;

    private ObsidianFlamePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = new ObsidianFlamePlugin();
    }

    /**
     * The shipped token map, narrowed at runtime (the shape claim IS the test;
     * it also keeps this file honest under the phpstan level-9 corpus).
     *
     * @return array<string, string>
     */
    private function tokens(): array
    {
        $themes = $this->plugin->providedThemes();
        $this->assertNotEmpty($themes, 'Plugin must provide at least one theme.');

        $tokens = $themes[0]['tokens'] ?? null;
        $this->assertIsArray($tokens, 'The payload must carry a tokens map.');

        $narrowed = [];
        foreach ($tokens as $key => $value) {
            $this->assertIsString($key, 'A token name must be a string.');
            $this->assertIsString($value, "Token {$key} must have a string value.");
            $narrowed[$key] = $value;
        }

        return $narrowed;
    }

    /**
     * @param array<string, string> $tokens
     * @return array{0: float, 1: float, 2: float, 3: float} r, g, b (0..1), alpha
     */
    private function rgbaOf(array $tokens, string $name): array
    {
        $this->assertArrayHasKey($name, $tokens, "Token {$name} must exist.");

        $matched = preg_match(
            '/^rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*([0-9.]+)\s*\)$/',
            $tokens[$name],
            $parts
        );
        $this->assertSame(1, $matched, "Token {$name} must be an rgba() literal.");

        return [
            (int) $parts[1] / 255,
            (int) $parts[2] / 255,
            (int) $parts[3] / 255,
            (float) $parts[4],
        ];
    }

    /**
     * @param array<string, string> $tokens
     * @return array{0: float, 1: float, 2: float} r, g, b (0..1)
     */
    private function rgbOf(array $tokens, string $name): array
    {
        $this->assertArrayHasKey($name, $tokens, "Token {$name} must exist.");

        $matched = preg_match('/^#([0-9a-f]{6})$/i', $tokens[$name], $parts);
        $this->assertSame(1, $matched, "Token {$name} must be a #rrggbb literal.");

        $hex = $parts[1];

        return [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];
    }

    /** WCAG relative luminance of a linear-ish sRGB triplet (components 0..1). */
    private function luminance(float $r, float $g, float $b): float
    {
        $channel = static function (float $v): float {
            return $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        };

        return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
    }

    /**
     * WCAG contrast ratio between two RGB triplets (components 0..1).
     *
     * @param array{0: float, 1: float, 2: float} $a
     * @param array{0: float, 1: float, 2: float} $b
     */
    private function contrast(array $a, array $b): float
    {
        $la = $this->luminance($a[0], $a[1], $a[2]);
        $lb = $this->luminance($b[0], $b[1], $b[2]);
        $lighter = max($la, $lb);
        $darker = min($la, $lb);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Composite a translucent token over an opaque ground (the way browsers
     * paint it), returning the resulting solid triplet.
     *
     * @param array{0: float, 1: float, 2: float, 3: float} $rgba
     * @param array{0: float, 1: float, 2: float} $ground
     * @return array{0: float, 1: float, 2: float}
     */
    private function composite(array $rgba, array $ground): array
    {
        $blend = static fn (int $i): float => round($rgba[$i] * $rgba[3] + $ground[$i] * (1 - $rgba[3]), 4);

        return [$blend(0), $blend(1), $blend(2)];
    }

    #[Test]
    public function testAccentRingClearsNonTextMinimumOnEveryGround(): void
    {
        $tokens = $this->tokens();
        $ring = $this->rgbaOf($tokens, '--accent-ring');

        foreach (self::GROUNDS as $groundName) {
            $ground = $this->rgbOf($tokens, $groundName);
            $ratio = $this->contrast($this->composite($ring, $ground), $ground);
            $this->assertGreaterThanOrEqual(
                self::NON_TEXT_MIN,
                $ratio,
                "--accent-ring over {$groundName} must clear the 3:1 non-text minimum"
            );
        }
    }

    #[Test]
    public function testTextSubtleClearsBodyTextAaMinimumOnEveryGround(): void
    {
        $tokens = $this->tokens();
        $subtle = $this->rgbOf($tokens, '--text-subtle');

        foreach (self::GROUNDS as $groundName) {
            $ratio = $this->contrast($subtle, $this->rgbOf($tokens, $groundName));
            $this->assertGreaterThanOrEqual(
                self::BODY_TEXT_MIN,
                $ratio,
                "--text-subtle over {$groundName} must clear the 4.5:1 AA body-text minimum"
            );
        }
    }

    #[Test]
    public function testTextRampIsStrictlyOrderedOnEveryGround(): void
    {
        $tokens = $this->tokens();
        $ramp = ['--text', '--text-muted', '--text-subtle', '--text-faint'];

        foreach (self::GROUNDS as $groundName) {
            $ground = $this->rgbOf($tokens, $groundName);
            $previous = null;

            foreach ($ramp as $level) {
                $levelContrast = $this->contrast($this->rgbOf($tokens, $level), $ground);
                if ($previous !== null) {
                    $this->assertLessThan(
                        $previous,
                        $levelContrast,
                        "{$level} must be strictly less prominent than the previous ramp level over {$groundName}"
                    );
                }

                $previous = $levelContrast;
            }
        }
    }

    /**
     * The auth input ground as a solid triplet: `--surface` composited over
     * `--bg` at INPUT_SURFACE_ALPHA, the way browsers paint AuthField's
     * translucent input.
     *
     * @param array<string, string> $tokens
     * @return array{0: float, 1: float, 2: float}
     */
    private function inputGround(array $tokens): array
    {
        $surface = $this->rgbOf($tokens, '--surface');
        $translucentSurface = [$surface[0], $surface[1], $surface[2], self::INPUT_SURFACE_ALPHA];

        return $this->composite($translucentSurface, $this->rgbOf($tokens, '--bg'));
    }

    #[Test]
    public function testTextFaintMeetsAaAsPlaceholderInkOnTheAuthGrounds(): void
    {
        $tokens = $this->tokens();
        $faint = $this->rgbOf($tokens, '--text-faint');

        $grounds = [
            // The grounds placeholder ink actually paints on — the auth page
            // bg, the card surface, and the translucent login input, which
            // held the old faint at 1.72/1.64/1.66. The ui binds --text-faint
            // as the AuthField ::placeholder color (plus the OAuth-divider
            // ink), and placeholder text is not a disabled control — it is
            // AA-bound.
            '--bg' => $this->rgbOf($tokens, '--bg'),
            '--surface' => $this->rgbOf($tokens, '--surface'),
            'input composite' => $this->inputGround($tokens),
        ];

        foreach ($grounds as $name => $ground) {
            $ratio = $this->contrast($faint, $ground);
            $this->assertGreaterThanOrEqual(
                self::BODY_TEXT_MIN,
                $ratio,
                sprintf('--text-faint on %s is %.2f:1 — below the 4.5:1 AA placeholder floor', $name, $ratio)
            );
        }
    }
}
