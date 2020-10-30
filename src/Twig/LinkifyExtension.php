<?php

namespace App\Twig;

use Misd\Linkify\Linkify;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LinkifyExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('linkify', [$this, 'linkify'], array('pre_escape' => 'html','is_safe' => array('html'))),
        ];
    }

    public function linkify($in)
    {
        $linkify = new Linkify();
        return $linkify->process($in);
    }
}
