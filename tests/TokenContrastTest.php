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
 *     The shipped 0.45 alpha sat at 2.20:1 on --bg — a focus indicator the
 *     review could not perceive; the cure lifted it to 0.65.
 *  2. --text-subtle clears the WCAG 1.4.3 body-text AA minimum 4.5:1 on every
 *     ground (the old #6d6460 was 3.48:1 on --bg and 2.72:1 on --surface-3;
 *     the cure #978d86 never drops below 4.84).
 *  3. The text ramp is STRICTLY ORDERED on every ground:
 *     --text > --text-muted > --text-subtle > --text-faint in prominence,
 *     i.e. contrast descends monotonically along the list. An equal or
 *     inverted step means two levels are visually the same ink — dead weight
 *     in the ramp (and a failed claim of four distinct levels).
 *  4. --text-faint clears the AA body-text minimum 4.5:1 on every ground the
 *     ui actually paints faint ink onto. Faint ink is AA-bound — placeholder
 *     and hint text is not a disabled control. The live consumers are the
 *     CommandPalette search ::placeholder (a transparent input on the palette
 *     panel, --surface-2), the SyncPlayModal input ::placeholder (--surface
 *     composited at 0.8 over the shared Modal panel, itself --surface-2), and
 *     the LoginForm/SignupForm divider + hint ink on the auth page (--bg) and
 *     auth card (--surface). AuthField is NOT a consumer: its ::placeholder
 *     binds --text-subtle, so the former AuthField citation was stale. The
 *     --surface-2 overlay panels are the binding ground: the old #857b76 sat
 *     at 4.28:1 there (measured red) and the cure #8b817c clears 4.65, while
 *     staying under --text-subtle (5.44 on --surface-2) so the ramp keeps
 *     four distinct levels. --surface-3 carries no faint text in the shipped
 *     flows and keeps only the ramp-order law (law 3) [R-THEME-OBSIDIAN-FLAME-01].
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
     * Alpha the SPA paints `--surface` at for a modal text input.
     *
     * The shared ui's SyncPlayModal gives its input a translucent ground
     * (src/components/syncplay/SyncPlayModal.vue: `.syncplay-modal__input {
     * background: color-mix(in srgb, var(--surface) 80%, transparent); }`)
     * inside the shared Modal panel (src/components/ui/Modal.vue: `background:
     * var(--surface-2)`), so the field's real ground is `--surface`
     * composited onto `--surface-2` at this alpha. The value is
     * family-canonical — jade and mystic pin the same constant.
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
     * The modal input ground as a solid triplet: `--surface` composited over
     * `--surface-2` at INPUT_SURFACE_ALPHA, the way browsers paint
     * SyncPlayModal's translucent input on the Modal panel.
     *
     * @param array<string, string> $tokens
     * @return array{0: float, 1: float, 2: float}
     */
    private function modalInputGround(array $tokens): array
    {
        $surface = $this->rgbOf($tokens, '--surface');
        $translucentSurface = [$surface[0], $surface[1], $surface[2], self::INPUT_SURFACE_ALPHA];

        return $this->composite($translucentSurface, $this->rgbOf($tokens, '--surface-2'));
    }

    /**
     * Assert --text-faint clears the AA body-text floor on each named ground.
     *
     * @param array<string, array{0: float, 1: float, 2: float}> $grounds
     */
    private function assertFaintClearsAaOn(array $grounds): void
    {
        $faint = $this->rgbOf($this->tokens(), '--text-faint');

        foreach ($grounds as $name => $ground) {
            $ratio = $this->contrast($faint, $ground);
            $this->assertGreaterThanOrEqual(
                self::BODY_TEXT_MIN,
                $ratio,
                sprintf('--text-faint on %s is %.2f:1 — below the 4.5:1 AA faint-ink floor', $name, $ratio)
            );
        }
    }

    #[Test]
    public function testTextFaintMeetsAaOnTheAuthPageAndCardGrounds(): void
    {
        $tokens = $this->tokens();

        // LoginForm/SignupForm paint their OAuth-divider and hint ink in
        // --text-faint on the auth page (--bg) and the auth card (--surface).
        $this->assertFaintClearsAaOn([
            '--bg' => $this->rgbOf($tokens, '--bg'),
            '--surface' => $this->rgbOf($tokens, '--surface'),
        ]);
    }

    #[Test]
    public function testTextFaintMeetsAaAsPlaceholderInkOnTheSurface2OverlayPanels(): void
    {
        $tokens = $this->tokens();

        // CommandPalette's search input is transparent on its --surface-2
        // panel, and SyncPlayModal's input is --surface at 0.8 over the Modal
        // panel's --surface-2; both bind --text-faint as ::placeholder ink.
        // The old #857b76 measured 4.28:1 on --surface-2 — this pin was red.
        $this->assertFaintClearsAaOn([
            '--surface-2' => $this->rgbOf($tokens, '--surface-2'),
            'modal input composite' => $this->modalInputGround($tokens),
        ]);
    }
}
