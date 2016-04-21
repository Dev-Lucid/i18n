<?php
namespace Lucid\Component\I18n;

interface I18nInterface
{
    public function addAvailableLanguage(string $code, array $variants=[]);
    public function getAvailableLanguages();
    public function getMajorLanguage();
    public function getMinorLanguage();
    public function setLanguage(string $majorLanguage, string $minorLanguage);
    public function addPhrases(array $contents);
    public function translate(string $phrase, $parameters);
    public function __call(string $phrase, array $parameters);
    public function loadDictionaries(string $path);
    public function parseLanguageHeader(string $header);
    public function getPhrases(bool $asString);
}