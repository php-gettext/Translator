<?php
declare(strict_types = 1);

namespace Gettext;

use RuntimeException;

abstract class TranslatorFunctions
{
    private static $translator;
    private static $formatter;

    public static function register(TranslatorInterface $translator, FormatterInterface $formatter = null): ?TranslatorInterface
    {
        $previous = [self::$translator, self::$formatter];

        self::$translator = $translator;
        self::$formatter = $formatter ?: new Formatter();

        include_once __DIR__.'/functions.php';

        return $previous;
    }

    public static function getTranslator(): TranslatorInterface
    {
        return self::$translator;
    }

    public static function getFormatter(): FormatterInterface
    {
        return self::$formatter;
    }
}
