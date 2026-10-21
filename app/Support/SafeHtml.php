<?php

namespace App\Support;

/**
 * HTML from the admin panel (or imported from the old site) reduced to formatting tags:
 * no scripts, styles, event handlers or javascript: links. Only a safe href survives.
 */
final class SafeHtml
{
    private const TAGS = '<p><br><strong><b><em><i><u><s><ul><ol><li><h2><h3><h4><blockquote><a><table><thead><tbody><tr><th><td>';

    /** Clean HTML, or null when no text is left */
    public static function clean(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', $html);
        $html = strip_tags($html, self::TAGS);
        $html = preg_replace_callback('/<(\w+)\b[^>]*>/', function (array $m): string {
            $tag = strtolower($m[1]);
            if ($tag === 'a' && preg_match('/\bhref\s*=\s*["\']?(https?:\/\/[^"\'\s>]+|\/[^"\'\s>]*|mailto:[^"\'\s>]+|tel:[^"\'\s>]+)/i', $m[0], $href)) {
                return '<a href="' . e(html_entity_decode($href[1])) . '" rel="noopener" target="_blank">';
            }

            return '<' . $tag . '>';
        }, $html);

        return trim(strip_tags($html)) === '' ? null : trim($html);
    }
}
