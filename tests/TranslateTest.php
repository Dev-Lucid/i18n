<?php
use Lucid\Component\i18n\I18n;

class TranslateTest extends \PHPUnit_Framework_TestCase
{
    public $i18n = null;
    public function setUp()
    {
        $this->i18n = new I18n();
        $this->i18n->addAvailableLanguage('en',['us']);
        $this->i18n->setLanguage('en','us');
        $this->i18n->addPhrases([
            'greeting1'=>'hello',
            'greeting2'=>'hello :name',
        ]);
    }

    public function testPhrase()
    {
        $this->assertEquals('hello', $this->i18n->translate('greeting1'));
        $this->assertEquals('hello John Doe', $this->i18n->translate('greeting2', ['name'=>'John Doe']));
        $this->assertEquals('unknownphrase', $this->i18n->translate('unknownphrase', ['name'=>'John Doe']));
    }

    public function testMagicMethod()
    {
        $this->assertEquals('hello', $this->i18n->greeting1());
        $this->assertEquals('hello John Doe', $this->i18n->greeting2(['name'=>'John Doe']));
    }
}