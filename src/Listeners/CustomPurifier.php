<?php

/*
 * This file is part of purify extension by Marco Colia.
 *
 * Copyright (c) Marco Colia.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Justoverclock\Purify\Listeners;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Post\Event\Saving;

class CustomPurifier
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Saving::class, [$this, 'customPurifier']);
    }

    public function isRegexValid($regex)
    {
        return @preg_match($regex, '') !== false;
    }

    public function customPurifier(Saving $event)
    {
        $post = $event->post;
        $content = $post->content;

        if (!$this->settings->get('justoverclock-purify.regexcustom')) {
            return;
        }

        $customPurifierPattern = $this->settings->get('justoverclock-purify.regexcustom');

        if (!$this->isRegexValid($customPurifierPattern)) {
            return;
        }

        if (is_array($content) && isset($content['raw'])) {
            $content['raw'] = $this->purify($content['raw'], $customPurifierPattern);
        } else {
            $content = $this->purify($content, $customPurifierPattern);
        }

        $post->content = $content;
    }

    private function purify(string $content, string $pattern): string
    {
        preg_match_all($pattern, $content, $matches);

        foreach ($matches[0] as $match) {
            $content = str_replace($match, str_repeat('*', strlen($match)), $content);
        }

        return $content;
    }
}
