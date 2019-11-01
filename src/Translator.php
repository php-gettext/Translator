<?php
declare(strict_types = 1);

namespace Gettext;

use Gettext\Generator\ArrayGenerator;
use InvalidArgumentException;

class Translator implements TranslatorInterface
{
    protected $domain;
    protected $dictionary = [];
    protected $plurals = [];

    public static function createFromTranslations(Translations ...$allTranslations): Translator
    {
        $translator = new static();
        $arrayGenerator = new ArrayGenerator();

        foreach ($allTranslations as $translations) {
            $translator->addTranslations(
                $arrayGenerator->generateArray($translations)
            );
        }

        return $translator;
    }

    /**
     * Load new translations from php files
     */
    public function loadTranslations(string ...$files): self
    {
        foreach ($files as $file) {
            $translations = include $file;

            if (!is_array($translations)) {
                throw new InvalidArgumentException('Invalid translations file: it must return an array');
            }

            $this->addTranslations($translations);
        }

        return $this;
    }

    /**
     * Add new translations to the dictionary.
     */
    public function addTranslations(array $translations): self
    {
        $domain = $translations['domain'] ?? '';

        //Set the first domain loaded as default domain
        if ($this->domain === null) {
            $this->domain = $domain;
        }

        if (isset($this->dictionary[$domain])) {
            $this->dictionary[$domain] = array_replace_recursive($this->dictionary[$domain], $translations['messages']);

            return $this;
        }

        if (!empty($translations['plural-forms'])) {
            list($count, $code) = array_map('trim', explode(';', $translations['plural-forms'], 2));

            // extract just the expression turn 'n' into a php variable '$n'.
            // Slap on a return keyword and semicolon at the end.
            $this->plurals[$domain] = [
                'count' => (int) str_replace('nplurals=', '', $count),
                'code' => str_replace('plural=', 'return ', str_replace('n', '$n', $code)).';',
            ];
        }

        $this->dictionary[$domain] = $translations['messages'];

        return $this;
    }

    /**
     * Set the default domain.
     */
    public function defaultDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function noop(string $original): string
    {
        return $original;
    }

    public function gettext(string $original): string
    {
        return $this->translate(null, null, $original);
    }

    public function ngettext(string $original, string $plural, int $value): string
    {
        return $this->translatePlural(null, null, $original, $plural, $value);
    }

    public function dngettext(string $domain, string $original, string $plural, int $value): string
    {
        return $this->translatePlural($domain, null, $original, $plural, $value);
    }

    public function npgettext(string $context, string $original, string $plural, int $value): string
    {
        return $this->translatePlural(null, $context, $original, $plural, $value);
    }

    public function pgettext(string $context, string $original): string
    {
        return $this->translate(null, $context, $original);
    }

    public function dgettext(string $domain, string $original): string
    {
        return $this->translate($domain, null, $original);
    }

    public function dpgettext(string $domain, string $context, string $original): string
    {
        return $this->translate($domain, $context, $original);
    }

    public function dnpgettext(string $domain, string $context, string $original, string $plural, int $value): string
    {
        return $this->translatePlural($domain, $context, $original, $plural, $value);
    }

    protected function translate(?string $domain, ?string $context, string $original): string
    {
        $translation = $this->getTranslation($domain, $context, $original);

        if (isset($translation[0]) && $translation[0] !== '') {
            return $translation[0];
        }

        return $original;
    }

    protected function translatePlural(
        ?string $domain,
        ?string $context,
        string $original,
        string $plural,
        int $value
    ): string {
        $translation = $this->getTranslation($domain, $context, $original);
        $key = $this->getPluralIndex($domain, $value, $translation === null);

        if (isset($translation[$key]) && $translation[$key] !== '') {
            return $translation[$key];
        }

        return ($key === 0) ? $original : $plural;
    }

    /**
     * Search and returns a translation.
     */
    protected function getTranslation(?string $domain, ?string $context, string $original): ?array
    {
        if ($domain === null) {
            $domain = $this->domain;
        }

        if ($context === null) {
            $context = '';
        }

        $translation = $this->dictionary[$domain][$context][$original] ?? null;

        return $translation === null ? $translation : (array) $translation;
    }

    /**
     * Executes the plural decision code given the number to decide which
     * plural version to take.
     */
    protected function getPluralIndex(?string $domain, int $n, bool $fallback): int
    {
        if ($domain === null) {
            $domain = $this->domain;
        }

        //Not loaded domain or translation, use a fallback
        if (!isset($this->plurals[$domain]) || $fallback === true) {
            return $n == 1 ? 0 : 1;
        }

        if (!isset($this->plurals[$domain]['function'])) {
            $code = self::fixTerseIfs($this->plurals[$domain]['code']);
            $this->plurals[$domain]['function'] = eval("return function (\$n) { $code };");
        }

        if ($this->plurals[$domain]['count'] <= 2) {
            return call_user_func($this->plurals[$domain]['function'], $n) ? 1 : 0;
        }

        return call_user_func($this->plurals[$domain]['function'], $n);
    }

    /**
     * This function will recursively wrap failure states in brackets if they contain a nested terse if.
     *
     * This because PHP can not handle nested terse if's unless they are wrapped in brackets.
     *
     * This code probably only works for the gettext plural decision codes.
     *
     * return ($n==1 ? 0 : $n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ? 1 : 2);
     * becomes
     * return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ? 1 : 2));
     */
    private static function fixTerseIfs(string $code, bool $inner = false): string
    {
        /*
         * (?P<expression>[^?]+)   Capture everything up to ? as 'expression'
         * \?                      ?
         * (?P<success>[^:]+)      Capture everything up to : as 'success'
         * :                       :
         * (?P<failure>[^;]+)      Capture everything up to ; as 'failure'
         */
        preg_match('/(?P<expression>[^?]+)\?(?P<success>[^:]+):(?P<failure>[^;]+)/', $code, $matches);

        // If no match was found then no terse if was present
        if (!isset($matches[0])) {
            return $code;
        }

        $expression = $matches['expression'];
        $success = $matches['success'];
        $failure = $matches['failure'];

        // Go look for another terse if in the failure state.
        $failure = static::fixTerseIfs($failure, true);
        $code = $expression.' ? '.$success.' : '.$failure;

        if ($inner) {
            return "($code)";
        }

        // note the semicolon. We need that for executing the code.
        return "$code;";
    }
}
