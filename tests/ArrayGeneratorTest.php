<?php

namespace Gettext\Tests;

use Gettext\Generator\ArrayGenerator;
use Gettext\Translation;
use Gettext\Translations;
use PHPUnit\Framework\TestCase;

class ArrayGeneratorTest extends TestCase
{
    public function testArrayGenerator()
    {
        $translations = Translations::create('testingdomain');
        $translations->setLanguage('ru');

        $translation = Translation::create(null, 'Ensure this value has at least %(limit_value)d character (it has %sd).');
        $translations->add($translation);

        $translation = Translation::create(null, '%ss must be unique for %ss %ss.');
        $translation->translate('%ss mora da bude jedinstven za %ss %ss.');
        $translations->add($translation);

        $translation = Translation::create('other-context', '日本人は日本で話される言語です！');
        $translation->translate('singular');
        $translation->translatePlural('plural1', 'plural2', 'plural3');
        $translations->add($translation);

        $array = (new ArrayGenerator())->generateArray($translations);

        $expected = [
            'domain' => 'testingdomain',
            'plural-forms' => 'nplurals=3; plural=(n % 10 == 1 && n % 100 != 11) ? 0 : ((n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 12 || n % 100 > 14)) ? 1 : 2);',
            'messages' => [
                '' => [
                    '%ss must be unique for %ss %ss.' => '%ss mora da bude jedinstven za %ss %ss.',
                ],
                'other-context' => [
                    '日本人は日本で話される言語です！' => ['singular', 'plural1', 'plural2'],
                ],
            ],
        ];

        $this->assertSame($expected, $array);

        $this->checkFormatting($expected, $translations);
    }

    public function testArrayGeneratorWithEmptyTranslations()
    {
        $translations = Translations::create('testingdomain');
        $translations->setLanguage('en');

        $translation = Translation::create(null, 'Empty translation included');
        $translation->translate('');
        $translations->add($translation);

        $translation = Translation::create(null, 'Inexistent translation included');
        $translations->add($translation);

        $array = (new ArrayGenerator(['includeEmpty' => true]))->generateArray($translations);

        $expected = [
            'domain' => 'testingdomain',
            'plural-forms' => 'nplurals=2; plural=n != 1;',
            'messages' => [
                '' => [
                    'Empty translation included' => '',
                    'Inexistent translation included' => null,
                ],
            ],
        ];

        $this->assertSame($expected, $array);

        $this->checkFormatting($expected, $translations, ['includeEmpty' => true]);
    }

    private function checkFormatting(array $expected, Translations $translations, array $otherOptions = [])
    {
        foreach ([
            [],
            ['strictTypes' => true],
            ['pretty' => true],
            ['strictTypes' => true, 'pretty' => true],
        ] as $options) {
            $phpCode = (new ArrayGenerator($options + $otherOptions))->generateString($translations);
            if (empty($options['strictTypes'])) {
                $this->assertStringNotContainsString('declare(strict_types=1);', $phpCode);
            } else {
                $this->assertStringContainsString('declare(strict_types=1);', $phpCode);
            }
            if (empty($options['pretty'])) {
                $this->assertStringEndsWith(');', $phpCode);
                $prefix = '<?php ';
            } else {
                $this->assertStringEndsWith("];\n", $phpCode);
                $prefix = "<?php\n";
            }
            $this->assertStringStartsWith($prefix, $phpCode);
            $array = eval(substr($phpCode, strlen($prefix)));
            $this->assertIsArray($array);
            $this->assertSame($expected, $array);
        }
    }
}
