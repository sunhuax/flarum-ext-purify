<?php

namespace YourVendor\MartianReplacer;

use Flarum\Extend;
use Flarum\Post\Event\Saving as PostSaving;
use Flarum\Discussion\Event\Saving as DiscussionSaving; // If replacing in titles too

return [
    // (可能需要) 将设置序列化到 Forum 对象，以便在监听器中访问
     (new Extend\Settings)
         ->serializeToForum('martianReplacementRules', 'your-namespace.martian-replacement-rules'), // Optional: if needed elsewhere

    // 监听帖子保存事件
    (new Extend\Event)
        ->listen(PostSaving::class, Listener\ApplyMartianReplacementsToPost::class),

    // (可选) 监听讨论保存事件，如果你也想替换标题中的词
    // (new Extend\Event)
    //    ->listen(DiscussionSaving::class, Listener\ApplyMartianReplacementsToDiscussion::class),

    // 添加后台管理路由和组件
    (new Extend\Routes('admin'))
        ->get('/martian-replacer', 'martian-replacer.admin.get', Controller\SettingsController::class), // Example route

    // 添加后台设置页面JS
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

     // 添加语言文件
    new Extend\Locales(__DIR__ . '/resources/locale'),
];
