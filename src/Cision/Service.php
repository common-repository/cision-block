<?php

namespace CisionBlock\Cision;

use CisionBlock\Frontend\Frontend;
use CisionBlock\GuzzleHttp\ClientInterface;
use CisionBlock\Psr\Container\ContainerInterface;
use CisionBlock\Settings\Settings;

class Service
{
    const FEED_DETAIL_LEVEL = 'detail';
    const FEED_FORMAT = 'json';
    const FEED_RELEASE_URL = 'http://publish.ne.cision.com/papi/Release/';
    const FEED_URL = 'https://publish.ne.cision.com/papi/NewsFeed/';
    const USER_AGENT = 'cision-block/' . Frontend::VERSION;

    /** @var ContainerInterface */
    protected ContainerInterface $container;

    /** @var Settings */
    protected Settings $settings;

    /** @var ClientInterface */
    protected ClientInterface $client;

    /**
     * @param ContainerInterface $container
     * @param Settings $settings
     * @param ClientInterface $client
     */
    public function __construct(ContainerInterface $container, Settings $settings, ClientInterface $client)
    {
        $this->container = $container;
        $this->settings = $settings;
        $this->client = $client;
    }

    /**
     * Returns an array of all different
     * feed types.
     *
     * @return array
     */
    public function getFeedTypes(): array
    {
        return [
            'KMK' => __('Annual Financial statement', 'cision-block'),
            'RDV' => __('Annual Report', 'cision-block'),
            'PRM' => __('Company Announcement', 'cision-block'),
            'RPT' => __('Interim Report', 'cision-block'),
            'INB' => __('Invitation', 'cision-block'),
            'NBR' => __('Newsletter', 'cision-block'),
        ];
    }

    /**
     * Return a list of languages.
     *
     * @return string[]
     */
    public function getLanguages(): array
    {
        $languages = [
            'ar' => __('Arabic', 'cision-block'),
            'bs' => __('Bosnian', 'cision-block'),
            'bg' => __('Bulgarian', 'cision-block'),
            'cs' => __('Czech', 'cision-block'),
            'zh' => __('Chinese', 'cision-block'),
            'hr' => __('Croatian', 'cision-block'),
            'da' => __('Danish', 'cision-block'),
            'de' => __('German', 'cision-block'),
            'nl' => __('Dutch', 'cision-block'),
            'en' => __('English', 'cision-block'),
            'es' => __('Spanish', 'cision-block'),
            'et' => __('Estonian', 'cision-block'),
            'fr' => __('French', 'cision-block'),
            'el' => __('Greek', 'cision-block'),
            'hu' => __('Hungarian', 'cision-block'),
            'is' => __('Icelandic', 'cision-block'),
            'it' => __('Italian', 'cision-block'),
            'ja' => __('Japanese', 'cision-block'),
            'ko' => __('Korean', 'cision-block'),
            'lv' => __('Latvian', 'cision-block'),
            'lt' => __('Lithuanian', 'cision-block'),
            'no' => __('Norwegian', 'cision-block'),
            'pl' => __('Polish', 'cision-block'),
            'pt' => __('Portuguese', 'cision-block'),
            'ro' => __('Romanian', 'cision-block'),
            'ru' => __('Russian', 'cision-block'),
            'sr' => __('Serbian', 'cision-block'),
            'sk' => __('Slovakian', 'cision-block'),
            'sl' => __('Slovenian', 'cision-block'),
            'fi' => __('Finnish', 'cision-block'),
            'sv' => __('Swedish', 'cision-block'),
            'tr' => __('Turkish', 'cision-block'),
        ];
        asort($languages);
        return $languages;
    }

    /**
     * Returns an array of available image styles.
     *
     * @return array
     */
    public function getImageStyles(): array
    {
        return [
            'DownloadUrl' => [
                'label' => __('Original Image', 'cision-block'),
                'class' => 'image-original',
            ],
            'UrlTo100x100ArResized' => [
                'label' => __('100x100 Resized', 'cision-block'),
                'class' => 'image-100x100-resized',
            ],
            'UrlTo200x200ArResized' => [
                'label' => __('200x200 Resized', 'cision-block'),
                'class' => 'image-200x200-resized',
            ],
            'UrlTo400x400ArResized' => [
                'label' => __('400x400 Resized', 'cision-block'),
                'class' => 'image-400x400-resized',
            ],
            'UrlTo800x800ArResized' => [
                'label' => __('800x800 Resized', 'cision-block'),
                'class' => 'image-800x800-resized',
            ],
            'UrlTo100x100Thumbnail' => [
                'label' => __('100x100 Thumbnail', 'cision-block'),
                'class' => 'image-100x100-thumbnail',
            ],
            'UrlTo200x200Thumbnail' => [
                'label' => __('200x200 Thumbnail', 'cision-block'),
                'class' => 'image-200x200-thumbnail',
            ],
        ];
    }

    /**
     * @param array $params
     * @return \stdClass|null
     * @throws \CisionBlock\GuzzleHttp\Exception\GuzzleException
     */
    public function fetchFeed(array $params = []): ?\stdClass
    {
        try {
            $response = \json_decode($this->client->get(self::FEED_URL . $this->settings->get('source_uid'), [
                'query' => $params,
            ])
                ->getBody()
                ->getContents());
            $response = apply_filters('cision-block/filter/feed/response', $response, $params, $this->settings);
            do_action('cision-block/action/feed/response', $response);
        } catch (\Exception $e) {
            error_log('Failed to fetch feed :: ' . $e->getMessage());
            $response = null;
        }
        return $response;
    }

    /**
     * @param string $id
     * @return \stdClass|null
     * @throws \CisionBlock\GuzzleHttp\Exception\GuzzleException
     */
    public function fetchReleaseById(string $id): ?\stdClass
    {
        try {
            $response = \json_decode($this->client->get(self::FEED_RELEASE_URL . $id)
                ->getBody()
                ->getContents());
        } catch (\Exception $e) {
            error_log(sprintf("Failed to fetch release: %s", $id) . ' ' . $e->getMessage());
            $response = null;
        }
        return $response;
    }
}
