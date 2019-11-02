<?php
declare(strict_types = 1);

namespace Gettext\Generator;

use Gettext\Headers;
use Gettext\Translation;
use Gettext\Translations;

final class ArrayGenerator extends Generator
{
    public function generateString(Translations $translations): string
    {
        $array = $this->generateArray($translations);

        return sprintf('<?php return %s;', var_export($array, true));
    }

    public function generateArray(Translations $translations): array
    {
        $pluralForm = $translations->getHeaders()->getPluralForm();
        $pluralSize = is_array($pluralForm) ? ($pluralForm[0] - 1) : null;
        $messages = [];

        foreach ($translations as $translation) {
            if (!$translation->getTranslation() || $translation->isDisabled()) {
                continue;
            }

            $context = $translation->getContext() ?: '';
            $original = $translation->getOriginal();

            if (!isset($messages[$context])) {
                $messages[$context] = [];
            }

            if (self::hasPluralTranslations($translation)) {
                $messages[$context][$original] = $translation->getPluralTranslations($pluralSize);
                array_unshift($messages[$context][$original], $translation->getTranslation());
            } else {
                $messages[$context][$original] = $translation->getTranslation();
            }
        }

        return [
            'domain' => $translations->getDomain(),
            'plural-forms' => $translations->getHeaders()->get(Headers::HEADER_PLURAL),
            'messages' => $messages,
        ];
    }

    private static function hasPluralTranslations(Translation $translation): bool
    {
        return implode('', $translation->getPluralTranslations()) !== '';
    }
}
