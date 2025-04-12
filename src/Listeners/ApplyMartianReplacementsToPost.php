<?php

namespace YourVendor\MartianReplacer\Listener;

use Flarum\Post\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr; // Helper for array operations

class ApplyMartianReplacementsToPost
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(Saving $event)
    {
        $post = $event->post;
        // 只处理普通评论帖子，或根据需要调整
        // if ($post->exists && $post->type === 'comment') { // 或者只处理新帖子 if (!$post->exists)
        if ($post->content) { // 确保有内容
            $rulesSetting = $this->settings->get('your-namespace.martian-replacement-rules', '');
            $rules = $this->parseRules($rulesSetting);

            if (!empty($rules)) {
                $post->content = $this->applyReplacements($post->content, $rules);
                // 注意：这里直接修改了 $post->content。 Flarum 会自动保存这个修改。
            }
        }
        // }
    }

    /**
     * 解析设置字符串为规则数组
     *
     * @param string $settingString 规则字符串 (例如 JSON 或 "keyword:chars\nkeyword2:morechars")
     * @return array ['keyword' => 'chars', ...]
     */
    protected function parseRules(string $settingString): array
    {
        $rules = [];
        // 方案 A: JSON 格式
        $decoded = json_decode($settingString, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
             // 可以添加验证确保结构正确
             foreach ($decoded as $keyword => $chars) {
                 if (is_string($keyword) && !empty($keyword) && is_string($chars) && !empty($chars)) {
                     // 将关键词统一转为小写，方便后续不区分大小写匹配
                     $rules[mb_strtolower(trim($keyword), 'UTF-8')] = trim($chars);
                 }
             }

        }
        // 方案 B: keyword:chars 格式 (如果选择此方案，注释掉上面的 JSON 解析)
        /*
        $lines = explode("\n", trim($settingString));
        foreach ($lines as $line) {
            $parts = explode(':', trim($line), 2);
            if (count($parts) === 2) {
                $keyword = trim($parts[0]);
                $chars = trim($parts[1]);
                if (!empty($keyword) && !empty($chars)) {
                    // 统一转小写
                    $rules[mb_strtolower($keyword, 'UTF-8')] = $chars;
                }
            }
        }
        */
        return $rules;
    }

    /**
     * 应用替换规则到文本
     *
     * @param string $text 输入文本
     * @param array $rules ['keyword' => 'chars', ...] (keyword 必须是小写)
     * @return string 替换后的文本
     */
    protected function applyReplacements(string $text, array $rules): string
    {
        if (empty($rules)) {
            return $text;
        }

        // 提取所有关键词 (小写)
        $keywords = array_keys($rules);

        // 对关键词进行正则转义，防止特殊字符干扰
        $escapedKeywords = array_map(function ($kw) {
            return preg_quote($kw, '/');
        }, $keywords);

        // 构建正则表达式，匹配任何一个关键词
        // \b 是单词边界，防止替换单词内部的子串 (例如 不会把 pineapple 中的 apple 替换掉)
        // 如果不需要单词边界（例如中文环境），可以去掉 \b
        // 'u' 支持 UTF-8, 'i' 不区分大小写
        $pattern = '/\b(' . implode('|', $escapedKeywords) . ')\b/ui';

        // 使用 preg_replace_callback 进行替换
        $newText = preg_replace_callback($pattern, function ($matches) use ($rules) {
            // $matches[0] 是匹配到的完整单词 (保留原始大小写)
            // $matches[1] 是括号内匹配到的部分 (我们正则里就是整个词)
            $matchedWordOriginalCase = $matches[0];
            $matchedWordLower = mb_strtolower($matchedWordOriginalCase, 'UTF-8'); // 转小写用于查找规则

            if (isset($rules[$matchedWordLower])) {
                $charsToUse = $rules[$matchedWordLower];
                if (mb_strlen($charsToUse, 'UTF-8') > 0) {
                    // 将火星文字符串拆分为字符数组 (支持 UTF-8)
                    $charArray = mb_str_split($charsToUse, 1, 'UTF-8');
                    // 随机选择一个字符的索引
                    $randomIndex = array_rand($charArray);
                    // 返回随机选中的字符
                    return $charArray[$randomIndex];
                }
            }

            // 如果没有找到规则或火星文为空，返回原词 (或其他默认值如 '?')
            return $matchedWordOriginalCase;

        }, $text);

        // 检查 preg_replace_callback 是否出错
        if ($newText === null) {
             // Log error or return original text
             // error_log('preg_replace_callback failed for Martian Replacer');
             return $text;
        }

        return $newText;
    }
}
