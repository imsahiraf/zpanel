<?php

declare(strict_types=1);

namespace ZPanel;

/** White label: the owner's brand (name, colour, logo link), overridden by a reseller's own for their clients. */
final class Brand
{
    /**
     * Panel themes (owner 2026-11-15: at least four to choose from; colours and shading changeable). key => label, sidebar, sidebar 2,
     * sidebar text, sidebar muted text, page background, corners (px). The colour (brand) and these can be changed on top.
     */
    public const THEMES = [
        'midnight' => ['Midnight (dark sidebar)', '#0f1530', '#1a1f45', '#c9cfe6', '#7f88ad', '#f3f5fb', 14],
        'daylight' => ['Daylight (light sidebar)', '#ffffff', '#eef1f8', '#1e293b', '#64748b', '#f7f8fc', 14],
        'ocean' => ['Ocean (deep teal)', '#083344', '#0e4a5c', '#cdeef5', '#7fb3c2', '#f2f8fa', 12],
        'forest' => ['Forest (green)', '#0f2a1d', '#17402b', '#cfe9d9', '#86ad95', '#f4f8f5', 12],
        'sunset' => ['Sunset (plum and orange)', '#3b0d2e', '#5a1546', '#f6d7e8', '#c28ab0', '#fbf6f4', 18],
        'graphite' => ['Graphite (grey, square corners)', '#1f2937', '#374151', '#e5e7eb', '#9ca3af', '#f3f4f6', 4],
    ];

    /** The fields of a look (theme and shading); a user may set these for himself. */
    public const LOOK = ['theme', 'color', 'side', 'bg', 'radius'];

    public static function default(): array
    {
        return ['name' => 'ZPanel', 'color' => '#2563eb', 'logo' => null, 'support' => null];
    }

    public static function owner(): array
    {
        return (json_decode((string) Db::setting('brand', '{}'), true) ?: []) + self::default();
    }

    /** The brand a user sees: their reseller's (or their own, for a reseller), else the owner's. */
    public static function for(array $user): array
    {
        $own = json_decode((string) $user['brand'], true) ?: [];
        $parent = [];
        if (!empty($user['parent_id'])) {
            $p = Auth::user((int) $user['parent_id']);
            $parent = $p ? (json_decode((string) $p['brand'], true) ?: []) : [];
        }
        // the user's own look (My account > Look) over their reseller's brand over the owner's
        $look = json_decode((string) Db::setting('look:' . (int) ($user['id'] ?? 0), '{}'), true) ?: [];

        // shading (sidebar, page, corners) belongs to the theme it was set with: below a layer that picks a theme it is not used
        $merged = [];
        $themeSet = false;
        foreach ([$look, $own, $parent, self::owner()] as $layer) {
            foreach ($layer as $k => $v) {
                if (in_array($k, ['side', 'bg', 'radius'], true) && $themeSet) {
                    continue;
                }
                $merged[$k] ??= $v;
            }
            $themeSet = $themeSet || isset($layer['theme']);
        }

        return self::withTheme($merged);
    }

    /** The theme's sidebar / page / corner values, with the colours set on top. */
    public static function withTheme(array $b): array
    {
        $t = self::THEMES[$b['theme'] ?? 'midnight'] ?? self::THEMES['midnight'];
        $b['theme'] = isset(self::THEMES[$b['theme'] ?? '']) ? $b['theme'] : 'midnight';
        $b['side'] ??= $t[1];
        $b['side2'] = $t[2];
        $b['side_text'] = $t[3];
        $b['side_muted'] = $t[4];
        $b['bg'] ??= $t[5];
        $b['radius'] ??= $t[6];

        return $b;
    }

    /** Only the look fields, cleaned (a user's personal look). */
    public static function cleanLook(array $in): array
    {
        return array_intersect_key(self::clean($in), array_flip(self::LOOK));
    }

    public static function clean(array $in): array
    {
        $out = [];
        if (isset($in['name']) && trim((string) $in['name']) !== '') {
            $out['name'] = mb_substr(trim((string) $in['name']), 0, 40);
        }
        if (isset($in['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', (string) $in['color'])) {
            $out['color'] = $in['color'];
        }
        if (!empty($in['logo']) && preg_match('#^https://[^\s"\'<>]+$#', (string) $in['logo'])) {
            $out['logo'] = (string) $in['logo'];
        }
        if (!empty($in['support']) && filter_var($in['support'], FILTER_VALIDATE_EMAIL)) {
            $out['support'] = (string) $in['support'];
        }
        if (isset($in['theme']) && isset(self::THEMES[(string) $in['theme']])) {
            $out['theme'] = (string) $in['theme'];
        }
        foreach (['side', 'bg'] as $k) {
            if (isset($in[$k]) && preg_match('/^#[0-9a-fA-F]{6}$/', (string) $in[$k])) {
                $out[$k] = (string) $in[$k];
            }
        }
        if (isset($in['radius']) && is_numeric($in['radius'])) {
            $out['radius'] = max(0, min(24, (int) $in['radius']));
        }

        return $out;
    }
}
