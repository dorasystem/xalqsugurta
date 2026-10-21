<?php

namespace App\Services\Site;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Turns an old xalqsugurta.uz page into a title + plain HTML body for an InfoPage.
 * Keeps text, headings, lists, tables and links; drops images, icons, forms and the site's classes.
 */
final class OldSitePage
{
    public const BASE_URL = 'https://xalqsugurta.uz';

    /** Blocks that hold the page text, tried in order (the "about" page has its own layout) */
    private const CONTENT = ['template-content', 'info__content'];

    private const TITLE = ['banner-light__title', 'banner__title'];

    private const KEEP = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td'];

    private const SKIP = ['script', 'style', 'svg', 'img', 'picture', 'video', 'iframe', 'form', 'input', 'button', 'select', 'textarea', 'nav', 'aside', 'noscript'];

    private const BLOCKS = ['div', 'section', 'article', 'header', 'footer', 'main'];

    /** @var list<string> absolute URLs of the documents the body links to */
    public array $files = [];

    public function __construct(public readonly ?string $title, public readonly ?string $body)
    {
    }

    public static function parse(string $html): self
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $title = null;
        foreach (self::TITLE as $class) {
            $node = $xpath->query('//h1[' . self::hasClass($class) . ']')->item(0);
            if ($node && trim($node->textContent) !== '') {
                $title = self::squash($node->textContent);
                break;
            }
        }

        $content = null;
        foreach (self::CONTENT as $class) {
            $content = $xpath->query('//*[' . self::hasClass($class) . ']')->item(0);
            if ($content) {
                break;
            }
        }

        $page = new self($title, null);
        if (!$content) {
            return $page;
        }

        $body = trim(preg_replace("/\n{3,}/", "\n\n", $page->children($content)));
        $result = new self($title, trim(strip_tags($body)) === '' ? null : $body);
        $result->files = array_values(array_unique($page->files));

        return $result;
    }

    // ─── Conversion ───────────────────────────────────────────────────────────

    private function children(DOMNode $node): string
    {
        $html = '';
        $items = [];   // <li> met outside a list: gathered into one <ul>

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'li' && !in_array($node->nodeName, ['ul', 'ol'], true)) {
                $items[] = $this->node($child);
                continue;
            }
            if ($items && ($child instanceof DOMElement || trim($child->textContent) !== '')) {
                $html .= "<ul>\n" . implode("\n", $items) . "\n</ul>\n";
                $items = [];
            }
            $html .= $this->node($child);
        }

        return $items ? $html . "<ul>\n" . implode("\n", $items) . "\n</ul>\n" : $html;
    }

    private function node(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return e(preg_replace('/\s+/u', ' ', $node->textContent), false);
        }
        if (!$node instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, self::SKIP, true)) {
            return '';
        }

        $class = ' ' . $node->getAttribute('class') . ' ';

        // Card titles / year labels become headings
        if (preg_match('/__(title|year)\s/', $class) && !in_array($tag, ['h2', 'h3', 'h4'], true)) {
            $text = self::squash($node->textContent);

            return $text === '' ? '' : "<h3>" . e($text) . "</h3>\n";
        }

        $inner = $this->children($node);

        if ($tag === 'a') {
            $href = $this->href($node->getAttribute('href'));
            $text = trim($inner);

            return $href === null || $text === '' ? $text : '<a href="' . e($href) . '">' . $text . '</a> ';
        }

        if ($tag === 'h1' || $tag === 'h5' || $tag === 'h6') {
            $tag = 'h3';
        }

        if (in_array($tag, self::KEEP, true)) {
            if ($tag === 'br') {
                return "<br>\n";
            }
            if (trim(strip_tags($inner)) === '' && !in_array($tag, ['td', 'th'], true)) {
                return '';
            }

            return '<' . $tag . '>' . trim($inner) . '</' . $tag . ">\n";
        }

        // A block with only inline content is a paragraph; one holding blocks is just unwrapped
        if (in_array($tag, self::BLOCKS, true)) {
            $text = trim($inner);
            if ($text === '' || trim(strip_tags($text)) === '') {
                return '';
            }

            return preg_match('/<(p|ul|ol|h[2-4]|table|blockquote)\b/', $text) ? $text . "\n" : "<p>" . $text . "</p>\n";
        }

        return $inner;   // span, font, …
    }

    /** Absolute URL for a link, or null when it goes nowhere */
    private function href(string $href): ?string
    {
        $href = trim($href);
        if ($href === '' || $href === '#' || str_starts_with($href, 'javascript:')) {
            return null;
        }
        if (preg_match('/^(mailto|tel):\s*/i', $href)) {
            return preg_replace('/^(mailto|tel):\s*/i', '$1:', $href);
        }
        if (str_starts_with($href, '//')) {
            $href = 'https:' . $href;
        } elseif (str_starts_with($href, '/')) {
            $href = self::BASE_URL . $href;
        } elseif (!preg_match('#^https?://#i', $href)) {
            $href = self::BASE_URL . '/' . $href;
        }

        if (str_contains($href, '/uploads/')) {
            $this->files[] = $href;
        }

        return $href;
    }

    private static function hasClass(string $class): string
    {
        return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
    }

    private static function squash(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
