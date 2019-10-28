<?php

use Gettext\GlobalTranslator as Translator;

/**
 * Returns the translation of a string.
 */
function __(string $original, ...$args): string
{
    $text = Translator::get()->gettext($original);

    return Translator::format($text, $args);
}

/**
 * Noop, marks the string for translation but returns it unchanged.
 */
function noop__(string $original): string
{
    return $original;
}

/**
 * Returns the singular/plural translation of a string.
 */
function n__(string $original, string $plural, int $value, ...$args)
{
    $text = Translator::get()->ngettext($original, $plural, $value);

    return Translator::format($text, $args);
}

/**
 * Returns the translation of a string in a specific context.
 */
function p__(string $context, string $original, ...$args): string
{
    $text = Translator::get()->pgettext($context, $original);

    return Translator::format($text, $args);
}

/**
 * Returns the translation of a string in a specific domain.
 */
function d__(string $domain, string $original, ...$args): string
{
    $text = Translator::get()->dgettext($domain, $original);

    return Translator::format($text, $args);
}

/**
 * Returns the translation of a string in a specific domain and context.
 */
function dp__(string $domain, string $context, string $original, ...$args): string
{
    $text = Translator::get()->dpgettext($domain, $context, $original);

    return Translator::format($text, $args);
}

/**
 * Returns the singular/plural translation of a string in a specific domain.
 */
function dn__(string $domain, string $original, string $plural, int $value, ...$args): string
{
    $text = Translator::get()->dngettext($domain, $original, $plural, $value);

    return Translator::format($text, $args);
}

/**
 * Returns the singular/plural translation of a string in a specific context.
 */
function np__(string $context, string $original, string $plural, int $value, ...$args): string
{
    $text = Translator::get()->npgettext($context, $original, $plural, $value);

    return Translator::format($text, $args);
}

/**
 * Returns the singular/plural translation of a string in a specific domain and context.
 */
function dnp__(string $domain, string $context, string $original, string $plural, int $value, ...$args): string
{
    $text = Translator::get()->dnpgettext($domain, $context, $original, $plural, $value);

    return Translator::format($text, $args);
}
