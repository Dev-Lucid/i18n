<?php
namespace Lucid\Component\I18n;

class I18n implements I18nInterface
{
    protected $phrases = [];
    protected $availableLanguages = [];
    protected $majorLanguage = null;
    protected $minorLanguage = null;

    # this function exists only for testing purposes
    public function resetLanguage()
    {
        $this->majorLanguage = null;
        $this->minorLanguage = null;
    }

    public function addAvailableLanguage(string $code, array $variants=[])
    {
        $this->availableLanguages[$code] = $variants;
        return $this;
    }

    public function getAvailableLanguages() : array
    {
        return $this->availableLanguages;
    }

    public function getMajorLanguage()
    {
        return $this->majorLanguage;
    }

    public function getMinorLanguage()
    {
        return $this->minorLanguage;
    }

    public function setLanguage(string $majorLanguage, string $minorLanguage=null)
    {
        if (array_key_exists($majorLanguage, $this->availableLanguages)) {
            $this->majorLanguage = $majorLanguage;
            if (array_key_exists($minorLanguage, $this->availableLanguages[$majorLanguage])) {
                $this->minorLanguage = $minorLanguage;
            }
        }
        return $this;
    }

    public function addPhrases(array $newPhrases)
    {
        foreach ($newPhrases as $key=>$value) {
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

    public function __call(string $phrase, array $parameters)
    {
        return $this->translate($phrase, ...$parameters);
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

    public function getPhrases(bool $asString = true)
    {
        if ($asString === true) {
            return print_r($this->phrases, true);
        }
        return $this->phrases;
    }

    public function parseLanguageHeader(string $userLang)
    {
        # da, en-gb;q=0.8, en;q=0.7
        # es-mx,es,en
        # zh, en-us; q=0.8, en; q=0.6
        $langs = explode(',', $userLang);
        $parsedLanguages = [];
        foreach ($langs as $lang) {
            $lang = trim($lang);
            $lang = explode(';', $lang);
            $parts = explode('-', $lang[0]);
            $major = $parts[0];
            $minor = $parts[1] ?? null;

            # only bother looking if we don't have a major language at all,
            # or if this entry contains a variant of the same language as the current major language
            if (is_null($this->majorLanguage) || (is_null($this->minorLanguage) && $this->majorLanguage == $major))  {

                # if we haven't found a major language yet and this major language is available, use it.
                if (is_null($this->majorLanguage) === true && isset($this->availableLanguages[$major]) === true) {
                    $this->majorLanguage = $major;
                    $this->minorLanguage = $minor;
                }

                if (is_null($this->minorLanguage) === true && in_array($minor, $this->availableLanguages[$major] ?? []) === true) {
                    $this->minorLanguage = $minor;
                }
            }
            /*
            if (isset($lang[1]) === true) {
                $lang[1] = explode('=', $lang[1]);
                $quality = $lang[1][1];
            } else {
                $quality = 1;
            }
            */

            #echo('$major   == '.$major."\n");
            #echo('$minor   == '.$minor."\n");
            # echo('$quality == '.$quality."\n");
        }
        return $this;
        /* Keeping this code around for a it, but the replacement code above seems better
        #print_r($parsedLanguages);
        #foreach($parsedLanguages as $language

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
        */
    }
}
