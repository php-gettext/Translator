<?php

namespace Gettext\Tests;

use Gettext\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function pluralFormsProvider(): array
    {
        return [
            // Nested ternary in the "true" branch
            ['nplurals=3; plural=n != 1 ? (n == 2 ? 1 : 2) : 0;', [1 => 0, 2 => 1, 3 => 2, 0 => 2]],
            // Nested ternary in the "true" branch, without parentheses
            ['nplurals=3; plural=n != 1 ? n == 2 ? 1 : 2 : 0;', [1 => 0, 2 => 1, 3 => 2, 0 => 2]],
            // Nested ternaries in both branches
            ['nplurals=4; plural=n > 2 ? (n > 10 ? 3 : 2) : (n == 1 ? 0 : 1);', [0 => 1, 1 => 0, 2 => 1, 3 => 2, 11 => 3]],
            // Chained ternaries in the "false" branch (the usual form)
            ['nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);', [1 => 0, 2 => 1, 5 => 2, 11 => 2, 21 => 0, 22 => 1, 25 => 2, 112 => 2]],
            ['nplurals=3; plural=(n % 10 == 1 && n % 100 != 11) ? 0 : ((n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 12 || n % 100 > 14)) ? 1 : 2);', [1 => 0, 3 => 1, 12 => 2, 24 => 1, 111 => 2]],
            ['nplurals=2; plural=n != 1;', [0 => 1, 1 => 0, 2 => 1]],
        ];
    }

    /**
     * @dataProvider pluralFormsProvider
     */
    public function testPluralForms(string $pluralForms, array $expected)
    {
        $translator = new Translator();
        $translator->addTranslations([
            'domain' => 'messages',
            'plural-forms' => $pluralForms,
            'messages' => [
                '' => [
                    'apple' => ['form 0', 'form 1', 'form 2', 'form 3'],
                ],
            ],
        ]);

        foreach ($expected as $n => $index) {
            $this->assertSame("form $index", $translator->ngettext('apple', 'apples', $n), "n = $n");
        }
    }
}
