<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('excerpt', [$this, 'getExcerpt']),
        ];
    }

    public function getExcerpt(string $content, int $length = 150): string
    {
        // Remove the first <h3> line
        $content = preg_replace('/<h3 class="wp-block-heading">.*?<\/h3>/', '', $content, 1);

        // Remove HTML tags for the excerpt
        $content = strip_tags($content);

        // Extract the first $length characters without cutting words
        if (strlen($content) > $length) {
            $content = substr($content, 0, strpos(wordwrap($content, $length), "\n")) . '...';
        }

        return $content;
    }
}
