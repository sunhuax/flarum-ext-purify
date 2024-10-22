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

class ObscureBadWords
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;

    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Saving::class, [$this, 'filterPostContent']);
    }

    public function filterPostContent(Saving $event)
    {
        $post = $event->post;
        $content = $post->content;

        if (is_array($content) && isset($content['raw'])) {
            $content['raw'] = $this->filterOutBadWords($content['raw']);
        } else {
            $content = $this->filterOutBadWords($content);
        }

        $post->content = $content;
    }

    private function filterOutBadWords(string $content)
    {

        if (!$this->settings->get('justoverclock-purify.badWordsList')) {
            return;
        }

        $badWordsSetting = $this->settings->get('justoverclock-purify.badWordsList');

        if (empty($badWordsSetting)) {
            return $content;
        }

        $badWords = explode(',', $badWordsSetting);

        foreach ($badWords as $badWord) {
            $badWord = trim($badWord);
            if (!empty($badWord)) {
                $content = str_replace($badWord, str_repeat('*', strlen($badWord)), $content);
            }
        }

        return $content;

    }
}
