<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class RichText
{
    /**
     * Render stored rich-text content as an HtmlString for Blade output.
     *
     * Content produced by the WYSIWYG editor (Sun Editor) is HTML and is
     * returned as-is. Legacy plain-text content is escaped and wrapped
     * with line breaks so existing notes/descriptions keep their look.
     */
    public static function render(?string $content): HtmlString
    {
        $content = trim((string) $content);

        if ($content === '') {
            return new HtmlString('');
        }

        if (preg_match('#<(p|div|br|ul|ol|li|h[1-6]|strong|em|u|a|code|pre|blockquote|span)\b#i', $content)) {
            return new HtmlString($content);
        }

        return new HtmlString(nl2br(e($content)));
    }
}
