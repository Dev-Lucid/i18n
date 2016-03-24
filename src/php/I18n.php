<?php
namespace Lucid\Component\I18n;
use Lucid\Lucid;

class I18n implements I18nInterface
{
    protected $phrases = [];
    protected $availableLanguages = [];
    protected $majorLanguage = null;
    protected $minorLanguage = null;

    public function addAvailableLanguage(string $code, array $variants=[])
    {
        $this->availableLanguages[$code] = $variants;
    }

    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }

    public function getMajorLanguage()
    {
        return $this->majorLanguage;
    }

    public function getMinorLanguage()
    {
        return $this->minorLang;
    }

    public function setLanguage(string $majorLanguage, string $minorLanguage=null)
    {
        if (array_key_exists($majorLanguage, $this->availableLanguages)) {
            $this->majorLanguage = $majorLanguage;
            if (array_key_exists($minorLanguage, $this->availableLanguages[$majorLanguage])) {
                $this->minorLanguage = $minorLanguage;
            }
        }
    }

    public function addPhrases(array $contents)
    {
        foreach ($contents as $key=>$value) {
            $this->phrases[$key] = $value;
        }
    }

    public function translate(string $phrase, $parameters=[]): string
    {
        if (isset($this->phrases[$phrase]) === false) {
            return $phrase;
        }
        $phrase = $this->phrases[$phrase];
        foreach ($parameters as $key=>$value) {
            $phrase = str_replace(':'.$key, $value, $phrase);
        }
        return $phrase;
    }

    public function loadDictionaries(string $path)
    {
        $this->phrases  = [];
        $langMajorFiles = [];
        $langMinorFiles = [];

        $langMajorFiles = glob($path.'/'.$this->majorLanguage.'[._]*json');
        $langMinorFiles = glob($path.'/'.$this->majorLanguage.$this->minorLanguage.'*json');

        foreach ($langMajorFiles as $file) {
            $contents = json_decode(file_get_contents($file), true);
            $this->addPhrases($contents);
        }

        foreach ($langMinorFiles as $file) {
            $contents = json_decode(file_get_contents($file), true);
            $this->addPhrases($contents);
        }
    }

    public function parseLanguageHeader(string $userLang)
    {
        # taken from http://stackoverflow.com/questions/6038236/using-the-php-http-accept-language-server-variable
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $userLang, $langParse);
        $languages = $langParse[1];
        $rank  = $langParse[4];
        $userLanguages = [];
        for ($i=0; $i<count($languages); $i++) {
            if (isset($rank[$i]) === true) {
                if (isset($rank[$i+1]) === false) {
                    $rank[$i+1] = null;
                }
                $userLanguages[strtolower($languages[$i])] = floatval( ($rank[$i] == NULL) ? $rank[$i+1] : $rank[$i] );
            }
        }

        # this should sort the user languages from worst to best.
        asort($userLanguages, SORT_NUMERIC);

        $bestMajor = null;
        $bestMinor = null;
        foreach ($userLanguages as $code=>$rank) {
            $code = explode('-',$code);
            $major = array_shift($code);
            $minor = (count($code) > 0)?array_shift($code):null;

            if (in_array($major, array_keys($this->availableLanguages)) === true || (isset($this->availableLanguages[$major]) && in_array($minor, array_keys($this->availableLanguages[$major]) === true))) {
                if ($major == $bestMajor and is_null($minor) === true) {
                    # do nothing! We don't want to overwrite an existing minor language setting if we've already got the
                    # right major language
                } else {
                    $bestMajor = $major;
                    $bestMinor = $minor;
                }
            }
        }

        if (is_null($bestMajor) === false) {
            $this->setLanguage($bestMajor, $bestMinor);
        }
    }
}
