<?php

namespace CisionBlock\Locale;

class Locale
{
    /** @var string[] */
    private static array $localeCode = [
        'ar' => 'ar',
        'bs_BA' => 'bs',
        'bg_BG' => 'bg',
        'cs_CZ' => 'cs',
        'zh' => 'zh',
        'zh_CN' => 'zh',
        'zh_HK' => 'zh',
        'zh_SG' => 'zh',
        'zh_TW' => 'zh',
        'hr' => 'hr',
        'da_DK' => 'da',
        'de' => 'de',
        'de_DE' => 'de',
        'de_AT' => 'de',
        'de_CH' => 'de',
        'nl' => 'nl',
        'nl_NL' => 'nl',
        'nl_BE' => 'nl',
        'en' => 'en',
        'en_US' => 'en',
        'en_AU' => 'en',
        'en_CA' => 'en',
        'en_NZ' => 'en',
        'es' => 'es',
        'es_AR' => 'es',
        'es_CL' => 'es',
        'es_CR' => 'es',
        'es_CO' => 'es',
        'es_DO' => 'es',
        'es_EC' => 'es',
        'es_GT' => 'es',
        'es_HN' => 'es',
        'es_MX' => 'es',
        'es_PE' => 'es',
        'es_PR' => 'es',
        'es_ES' => 'es',
        'es_UY' => 'es',
        'es_VE' => 'es',
        'et' => 'et',
        'fr' => 'fr',
        'fr_FR' => 'fr',
        'fr_BE' => 'fr',
        'fr_CA' => 'fr',
        'el' => 'el',
        'hu_HU' => 'hu',
        'is_IS' => 'is',
        'it_IT' => 'it',
        'ja' => 'ja',
        'ko_KR' => 'ko',
        'lv' => 'lv',
        'lt_LT' => 'lt',
        'no' => 'no',
        'nb_NO' => 'no',
        'nn_NO' => 'no',
        'pl_PL' => 'pl',
        'pt' => 'pt',
        'pt_PT' => 'pt',
        'pt_AO' => 'pt',
        'pt_BR' => 'pt',
        'ro_RO' => 'ro',
        'ru' => 'ru',
        'ru_RU' => 'ru',
        'ru_UA' => 'ru',
        'sr_RS' => 'sr',
        'sk_SK' => 'sk',
        'sl_SI' => 'sl',
        'fi' => 'fi',
        'sv_SE' => 'sv',
        'tr_TR' => 'tr',
    ];

    /** @var string[] */
    private static array $languageCode = [
        'ar' => 'ar',
        'bs' => 'bs_BA',
        'bg' => 'bg_BG',
        'cs' => 'cs_CZ',
        'zh' => 'zh_CN',
        'hr' => 'hr',
        'da' => 'da_DK',
        'de' => 'de_DE',
        'nl' => 'nl_NL',
        'en' => 'en_US',
        'es' => 'es_ES',
        'et' => 'et',
        'fr' => 'fr_FR',
        'el' => 'el',
        'hu' => 'hu_HU',
        'is' => 'is_IS',
        'it' => 'is_IT',
        'ja' => 'ja',
        'ko' => 'ko_KR',
        'lv' => 'lv',
        'lt' => 'lt_LT',
        'no' => 'nb_NO',
        'pl' => 'pl_PL',
        'pt' => 'pt_PT',
        'ro' => 'ro_RO',
        'ru' => 'ru_RU',
        'sr' => 'sr_RS',
        'sk' => 'sk_SK',
        'sl' => 'sl_SI',
        'fi' => 'fi',
        'sv' => 'sv_SE',
        'tr' => 'tr_TR',
    ];

    /**
     * @param string $localeCode
     * @return string
     */
    private static function parseLocaleCode(string $localeCode): string
    {
        $localeCode = explode('_', $localeCode);
        $localeCode[0] = strtolower($localeCode[0]);
        if (count($localeCode) > 1) {
            $localeCode[1] = strtoupper($localeCode[1]);
        }
        return implode('_', $localeCode);
    }


    /**
     * @param string $localeCode
     * @return string|null
     */
    public static function localeCodeToLanguageCode(string $localeCode): ?string
    {
        $localeCode = self::parseLocaleCode($localeCode);
        return self::$localeCode[$localeCode] ?? null;
    }

    /**
     * @param string $localeCode
     * @return bool
     */
    public static function isValidLocaleCode(string $localeCode): bool
    {
        $localeCode = self::parseLocaleCode($localeCode);
        return isset(self::$localeCode[$localeCode]);
    }

    /**
     * @param string $languageCode
     * @return string|null
     */
    public static function languageCodeToLocaleCode(string $languageCode): ?string
    {
        $languageCode = strtolower($languageCode);
        return self::$languageCode[$languageCode] ?? null;
    }

    /**
     * @param string $languageCode
     * @return bool
     */
    public static function isValidLanguageCode(string $languageCode): bool
    {
        $languageCode = strtolower($languageCode);
        return isset(self::$languageCode[$languageCode]);
    }
}
