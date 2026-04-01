<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Partnership;

/**
 * Simple i18n helper.
 * Usage:  Lang::init('en');  then  Lang::t('hero_title')
 */
final class Lang
{
    private static string $lang = 'ru';
    private static array $messages = [];
    private static array $fallback = [];

    public static function init(?string $lang = null): void
    {
        if ($lang !== null && in_array($lang, ['ru', 'en'], true)) {
            self::$lang = $lang;
        }

        $file = __DIR__ . '/../../config/i18n/' . self::$lang . '.php';
        self::$messages = is_file($file) ? (array) require $file : [];

        if (self::$lang !== 'ru') {
            $fallbackFile = __DIR__ . '/../../config/i18n/ru.php';
            self::$fallback = is_file($fallbackFile) ? (array) require $fallbackFile : [];
        } else {
            self::$fallback = [];
        }
    }

    public static function get(): string
    {
        return self::$lang;
    }

    public static function t(string $key, string $default = ''): string
    {
        return self::$messages[$key] ?? self::$fallback[$key] ?? ($default !== '' ? $default : $key);
    }

    /**
     * Return the localised value for a DB field.
     * E.g.  Lang::field($card, 'org_name')  →  returns org_name_en if lang=en and it's not empty, else org_name.
     */
    public static function field(array $row, string $field): string
    {
        if (self::$lang === 'en') {
            return (string) ($row[$field . '_en'] ?? '');
        }

        return (string) ($row[$field] ?? '');
    }

    /**
     * JSON-массив из поля или из поля *_en для текущего языка (например subtasks / goals).
     *
     * @return list<mixed>
     */
    public static function jsonField(array $row, string $field): array
    {
        if (self::$lang === 'en') {
            return Partnership::decodeJson(isset($row[$field . '_en']) ? (string) $row[$field . '_en'] : null);
        }

        return Partnership::decodeJson(isset($row[$field]) ? (string) $row[$field] : null);
    }
}
