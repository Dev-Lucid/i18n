<?php
use Lucid\Component\I18n\I18n;

class ParseTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        # test for some en-us
        $i18n = new i18n();
        $i18n->addAvailableLanguage('en',['us', 'gb', 'au']);
        $i18n->addAvailableLanguage('de',['de']);
        $i18n->addAvailableLanguage('es',['es', 'mx']);

        $i18n->resetLanguage();
        $i18n->parseLanguageHeader(' da, en-gb;q=0.8, en;q=0.7');
        # echo($i18n->getMajorLanguage().'-'.$i18n->getMinorLanguage()."\n");
        $this->assertEquals('en', $i18n->getMajorLanguage());
        $this->assertEquals('gb', $i18n->getMinorLanguage());

        $i18n->resetLanguage();
        $i18n->parseLanguageHeader('es-mx,es,en');
        # echo($i18n->getMajorLanguage().'-'.$i18n->getMinorLanguage()."\n");
        $this->assertEquals('es', $i18n->getMajorLanguage());
        $this->assertEquals('mx', $i18n->getMinorLanguage());

        $i18n->resetLanguage();
        $i18n->parseLanguageHeader('zh, en-us; q=0.8, en; q=0.6');
        # echo($i18n->getMajorLanguage().'-'.$i18n->getMinorLanguage()."\n");
        $this->assertEquals('en', $i18n->getMajorLanguage());
        $this->assertEquals('us', $i18n->getMinorLanguage());

        #$this->assertEquals('gb', $i18n->getMinorLanguage());
    }
}