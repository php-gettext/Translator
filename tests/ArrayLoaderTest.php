<?php

namespace Gettext\Tests;

use Gettext\Loader\ArrayLoader;
use PHPUnit\Framework\TestCase;

class ArrayLoaderTest extends TestCase
{
    public function testArrayLoader()
    {
        $loader = new ArrayLoader();

        $translations = $loader->loadFile(__DIR__.'/assets/translations.php');

        $this->assertCount(13, $translations->getHeaders());
        $this->assertSame('1.0', $translations->getHeaders()->get('MIME-Version'));
        $this->assertCount(13, $translations);

        $translation = $translations->find(null, 'Integer');

        $this->assertNotNull($translation);
        $this->assertSame('Cijeo broj', $translation->getTranslation());
        $this->assertCount(0, $translation->getPluralTranslations());
    }
}
