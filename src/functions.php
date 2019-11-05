<?php

use Gettext\TranslatorFunctions as Translator;

/**
 * Returns the translation of a string.
 */
function __(string $original, ...$args): string
{
    $text = Translator::getTranslator()->gettext($original);
    return Translator::getFormatter()->format($text, $args);
}

/**
 * Noop, marks the string for translation but returns it unchanged.
 */
function noop__(string $original, ...$args): string
{
    $text = Translator::getTranslator()->noop($original);
    return Translator::getFormatter()->format($text, $args);
}

/**
 * Returns the singular/plural translation of a string.
 */
function n__(string $original, string $plural, int $value, ...$args): string
{
    $text = Translator::getTranslator()->ngettext($original, $plural, $value);
    return Translator::getFormatter()->format($text, $args);
}

/**
 * Returns the translation of a string in a specific context.
 */
function p__(string $context, string $original, ...$args): string
{
    $text = Translator::getTranslator()->pgettext($context, $original);
    return Translator::getFormatter()->format($text, $args);
}

/**
 * Returns the translation of a string in a specific domain.
 */
function d__(string $domain, string $original, ...$args): string
{
    $text = Translator::getTranslator()->dgettext($domain, $original);
    return Translator::getFormatter()->format($text, $args);
}

/**
 * Returns the translation of a string in a specific domain and context.
 */
function dp__(string $domain, string $context, string $original, ...$args): string
{
    $text = Translator::getTranslator()->dpgettext($domain, $context, $original);
    return Translator::getFormatter()->format($text, $args);
}

/**
 * Returns the singular/plural translation of a string in a specific domain.
 */
function dn__(string $domain, string $original, string $plural, int $value, ...$args): string
{
    $text = Translator::getTranslator()->dngettext($domain, $original, $plural, $value);
    return Translator::getFormatter()->format($text, $args);
}

/**
 * Returns the singular/plural translation of a string in a specific context.
 */
function np__(string $context, string $original, string $plural, int $value, ...$args): string
{
    $text = Translator::getTranslator()->npgettext($context, $original, $plural, $value);
    return Translator::getFormatter()->format($text, $args);
}

/**
 * Returns the singular/plural translation of a string in a specific domain and context.
 */
function dnp__(string $domain, string $context, string $original, string $plural, int $value, ...$args): string
{
    $text = Translator::getTranslator()->dnpgettext($domain, $context, $original, $plural, $value);
    return Translator::getFormatter()->format($text, $args);
}
