<?php
declare(strict_types = 1);

namespace Gettext;

use RuntimeException;

abstract class GlobalTranslator
{
    private static $translator;

    public static function register(TranslatorInterface $translator): ?TranslatorInterface
    {
        $previous = self::$translator;
        self::$translator = $translator;
        include_once __DIR__.'/functions.php';

        return $previous;
    }

    public static function get(): TranslatorInterface
    {
        $translator = self::$translator;

        if (!$translator) {
            throw new RuntimeException('No translator registered');
        }

        return $translator;
    }

    public static function format(string $text, array $args): string
    {
        if (empty($args)) {
            return $text;
        }
    
        return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
    }
}
