<?php

namespace Gettext\Tests;

use Gettext\Translation;
use Gettext\Translations;
use Gettext\Translator;
use Gettext\TranslatorFunctions;
use Gettext\Generator\ArrayGenerator;
use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testPluralFunction()
    {
        $translations = Translations::create()->add(
            Translation::create(null, 'One comment', '%s comments')
                ->translate('Un commentaire')
                ->translatePlural('%s commentaires')
        );

        $t = Translator::createFromTranslations($translations);
        TranslatorFunctions::register($t);

        $this->assertEquals('%s commentaires', $t->ngettext('One comment', '%s comments', 3));
        $this->assertEquals('beaucoup de commentaires', n__('One comment', '%s comments', 3, 'beaucoup de'));
        $this->assertEquals('0 commentaires', n__('One comment', '%s comments', 3, 0));
        $this->assertEquals(' commentaires', n__('One comment', '%s comments', 3, null));
        $this->assertEquals('beaucoup de commentaires', n__('One comment', '%s comments', 3, ['%s' => 'beaucoup de']));
    }

    public function testContextFunction()
    {
        $translations = Translations::create()
            ->add(Translation::create('daytime', 'Hello %s')->translate('Bonjour %s'))
            ->add(Translation::create('nightime', 'Hello %s')->translate('Bonsoir %s'));
        
        $t = Translator::createFromTranslations($translations);
        TranslatorFunctions::register($t);

        $this->assertEquals('Bonjour %s', p__('daytime','Hello %s'));
        $this->assertEquals('Bonjour John', p__('daytime','Hello %s', 'John'));
        $this->assertEquals('Bonjour 0', p__('daytime','Hello %s', 0));
        $this->assertEquals('Bonjour ', p__('daytime','Hello %s', null));
        $this->assertEquals('Bonjour John', p__('daytime','Hello %s',['%s' => 'John']));
        $this->assertEquals('Bonsoir John', p__('nightime','Hello %s', 'John'));
        $this->assertEquals('Bonsoir John', p__('nightime','Hello %s',['%s' => 'John']));
    }

    public function testDomainFunction()
    {
        $translations = Translations::create('messages')
            ->add(Translation::create(null, 'Hello %s')->translate('Bonjour %s'));

        $t = Translator::createFromTranslations($translations);
        TranslatorFunctions::register($t);

        $this->assertEquals('Bonjour %s', d__('messages','Hello %s'));
        $this->assertEquals('Bonjour John', d__('messages','Hello %s','John'));
        $this->assertEquals('Bonjour 0', d__('messages','Hello %s',0));
        $this->assertEquals('Bonjour ', d__('messages','Hello %s',null));
        $this->assertEquals('Bonjour John', d__('messages','Hello %s',['%s' => 'John']));
        $this->assertEquals('Hello %s', d__('errors','Hello %s'));
        $this->assertEquals('Hello John', d__('errors','Hello %s',['%s' => 'John']));
    }

    public function testDomainPluralFunction()
    {
        $translations = Translations::create('messages')->add(
            Translation::create(null, 'One comment', '%s comments')
                ->translate('Un commentaire')
                ->translatePlural('%s commentaires')
        );

        $t = Translator::createFromTranslations($translations);
        TranslatorFunctions::register($t);

        $this->assertEquals('%s commentaires', dn__('messages', 'One comment', '%s comments', 3));
        $this->assertEquals('beaucoup de commentaires', dn__('messages', 'One comment', '%s comments', 3, 'beaucoup de'));
        $this->assertEquals('0 commentaires', dn__('messages', 'One comment', '%s comments', 3, 0));
        $this->assertEquals(' commentaires', dn__('messages', 'One comment', '%s comments', 3, null));
        $this->assertEquals('beaucoup de commentaires', dn__('messages', 'One comment', '%s comments', 3, ['%s' => 'beaucoup de']));
        $this->assertEquals('One comment', dn__('messages-2', 'One comment', '%s comments', 1, 1));
        $this->assertEquals('3 comments', dn__('messages-2', 'One comment', '%s comments', 3, 3));
    }

    public function testDomainAndContextFunction()
    {
        $translations = Translations::create('messages')
            ->add(Translation::create('daytime', 'Hello %s')->translate('Bonjour %s'))
            ->add(Translation::create('nightime', 'Hello %s')->translate('Bonsoir %s'));
        
        $t = Translator::createFromTranslations($translations);
        TranslatorFunctions::register($t);

        $this->assertEquals('Bonjour %s', dp__('messages','daytime','Hello %s'));
        $this->assertEquals('Bonjour John', dp__('messages','daytime','Hello %s', 'John'));
        $this->assertEquals('Bonjour 0', dp__('messages','daytime','Hello %s', 0));
        $this->assertEquals('Bonjour ', dp__('messages','daytime','Hello %s', null));
        $this->assertEquals('Bonjour John', dp__('messages','daytime','Hello %s',['%s' => 'John']));
        $this->assertEquals('Bonsoir John', dp__('messages','nightime','Hello %s', 'John'));
        $this->assertEquals('Bonsoir John', dp__('messages','nightime','Hello %s',['%s' => 'John']));
        $this->assertEquals('Hello John', dp__('errors','daytime','Hello %s',['%s' => 'John']));
    }

    public function testPluralAndContextFunction()
    {
        $translations = Translations::create()->add(
            Translation::create('comment', 'One comment', '%s comments')
                ->translate('Un commentaire')
                ->translatePlural('%s commentaires')
        );

        $t = Translator::createFromTranslations($translations);
        TranslatorFunctions::register($t);

        $this->assertEquals('%s commentaires', np__('comment', 'One comment', '%s comments', 3));
        $this->assertEquals('0 commentaires', np__('comment', 'One comment', '%s comments', 3, 0));
        $this->assertEquals(' commentaires', np__('comment', 'One comment', '%s comments', 3, null));
        $this->assertEquals('beaucoup de commentaires', np__('comment', 'One comment', '%s comments', 3, 'beaucoup de'));
        $this->assertEquals('beaucoup de commentaires', np__('comment', 'One comment', '%s comments', 3, ['%s' => 'beaucoup de']));
        $this->assertEquals('3 comments', np__('', 'One comment', '%s comments', 3, ['%s' => 3]));
    }
    public function testPluralAndContextAndDomainFunction()
    {
        $translations = Translations::create('messages')->add(
            Translation::create('comment', 'One comment', '%s comments')
                ->translate('Un commentaire')
                ->translatePlural('%s commentaires')
        );
        
        $t = Translator::createFromTranslations($translations);
        TranslatorFunctions::register($t);

        $this->assertEquals('%s commentaires', dnp__('messages', 'comment', 'One comment', '%s comments', 3));
        $this->assertEquals('0 commentaires', dnp__('messages', 'comment', 'One comment', '%s comments', 3, 0));
        $this->assertEquals(' commentaires', dnp__('messages', 'comment', 'One comment', '%s comments', 3, null));
        $this->assertEquals('beaucoup de commentaires', dnp__('messages', 'comment', 'One comment', '%s comments', 3, 'beaucoup de'));
        $this->assertEquals('beaucoup de commentaires', dnp__('messages', 'comment', 'One comment', '%s comments', 3, ['%s' => 'beaucoup de']));
        $this->assertEquals('beaucoup de comments', dnp__('errors', 'comment', 'One comment', '%s comments', 3, ['%s' => 'beaucoup de']));
        $this->assertEquals('beaucoup de comments', dnp__('messages', '', 'One comment', '%s comments', 3, ['%s' => 'beaucoup de']));
    }
    public function testNonLoadedTranslations()
    {
        $t = new Translator();
        $this->assertEquals('hello', $t->gettext('hello'));
        $this->assertEquals('worlds', $t->ngettext('world', 'worlds', 0));
        $this->assertEquals('world', $t->ngettext('world', 'worlds', 1));
        $this->assertEquals('worlds', $t->ngettext('world', 'worlds', 2));
    }
}
