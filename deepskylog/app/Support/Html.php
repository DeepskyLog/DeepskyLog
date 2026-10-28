<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitizes the user-written rich text (descriptions, profile text, session
 * notes, messages) that is printed with {!! !!}.
 *
 * The allowlist keeps what the TinyMCE editors produce (bold, italic,
 * alignment, indent, lists) and the markup of the old DeepskyLog, and drops
 * scripts, event handlers and every other tag or attribute.
 */
class Html
{
    private static ?HTMLPurifier $purifier = null;

    /**
     * Returns the purified HTML. With $decodeEntities the text is first
     * entity-decoded: the old DeepskyLog stored descriptions with their HTML
     * as entities (&lt;br /&gt;), which were printed as markup.
     */
    public static function purify(?string $html, bool $decodeEntities = false): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        if ($decodeEntities) {
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return self::purifier()->purify($html);
    }

    /**
     * Whether the text shows nothing: empty, or only markup, entities and
     * (non-breaking) spaces, such as the <p>&nbsp;</p> of an empty editor.
     */
    public static function isBlank(?string $html): bool
    {
        $text = html_entity_decode(strip_tags(html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(str_replace("\u{00A0}", ' ', $text)) === '';
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed',
                'a[href|title|target],b,strong,i,em,u,s,strike,sub,sup,br,hr,'.
                'p[style|align|class],div[style|align|class],span[style|class],'.
                'h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],'.
                'ul[style|class],ol[style|class],li[style|class],blockquote,code,pre,'.
                'img[src|alt|title|width|height]'
            );
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('CSS.AllowedProperties', [
                'text-align', 'margin', 'margin-left', 'padding-left', 'text-indent',
                'list-style-type', 'font-weight', 'font-style', 'text-decoration',
            ]);
            $config->set('Cache.DefinitionImpl', null);

            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier;
    }
}
