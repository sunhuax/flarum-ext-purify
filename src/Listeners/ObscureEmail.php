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

class ObscureEmail
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Saving::class, [$this, 'obscureEmail']);
    }

    public function obscureEmail(Saving $event)
    {
        $post = $event->post;
        $content = $post->content;
        $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';

        if (!$this->settings->get('justoverclock-purify.AlsoEmail')) {
            return;
        }

        $emailsShouldBeObscured = $this->settings->get('justoverclock-purify.AlsoEmail');

        if (!$emailsShouldBeObscured) {
            return;
        }

        if (is_array($content) && isset($content['raw'])) {
            $content['raw'] = $this->replaceEmails($content['raw'], $pattern);
        } else {
            $content = $this->replaceEmails($content, $pattern);
        }

        $post->content = $content;
    }

    private function replaceEmails(string $content, string $pattern): string
    {
        preg_match_all($pattern, $content, $matches);

        foreach ($matches[0] as $match) {
            $content = str_replace($match, str_repeat('*', strlen($match)), $content);
        }

        return $content;
    }
}
