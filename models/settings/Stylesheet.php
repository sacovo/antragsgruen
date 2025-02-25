<?php

namespace app\models\settings;

class Stylesheet implements \JsonSerializable
{
    use JsonConfigTrait;

    const TYPE_COLOR = 'color';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_PIXEL = 'pixel';
    const TYPE_NUMBER = 'number';
    const TYPE_FONT = 'font';
    const TYPE_IMAGE = 'image';

    const DEFAULTS_LAYOUT_CLASSIC = 'layout-classic';
    const DEFAULTS_LAYOUT_DBJR = 'layout-dbjr';

    public $bodyFont;
    public $containerSize;
    public $colorLinks;
    public $colorLinksLight;
    public $colorDelLink;
    public $brandPrimary;
    public $buttonFont;
    public $buttonSuccessBackground;
    public $textColor;
    public $sidebarWidth;
    public $sidebarBackground;
    public $sidebarActionFont;
    public $createMotionBtnColor;
    public $bookmarkAmendmentBackground;
    public $bookmarkCommentColor;
    public $headingFont;
    public $headingPrimaryText;
    public $headingPrimaryBackground;
    public $headingPrimarySize;
    public $headingSecondaryText;
    public $headingSecondaryBackground;
    public $headingSecondarySize;
    public $headingTertiaryText;
    public $headingTertiaryBackground;
    public $headingTertiarySize;
    public $linkTextDecoration;
    public $useBoxShadow;
    public $contentBorderRadius;
    public $backgroundImage;
    public $menuActive;
    public $menuLink;
    public $menuFont;
    public $motionFixedFontColor;
    public $motionFixedFont;
    public $motionStdFontSize;

    public static $DEFAULTS_CLASSIC = [
        'useBoxShadow'                => true,
        'containerSize'               => 1024,
        'contentBorderRadius'         => 10,
        'sidebarWidth'                => 256,
        'sidebarBackground'           => '#e2007a',
        'sidebarActionFont'           => '"Open Sans", sans-serif',
        'createMotionBtnColor'        => '#e2007a',
        'bookmarkAmendmentBackground' => '#afcb08',
        'bookmarkCommentColor'        => '#e2007a',
        'headingFont'                 => '"Open Sans", sans-serif',
        'headingPrimaryText'          => '#ffffff',
        'headingPrimaryBackground'    => '#285f19',
        'headingPrimarySize'          => 15,
        'headingSecondaryText'        => '#ffffff',
        'headingSecondaryBackground'  => '#afcb08',
        'headingSecondarySize'        => 15,
        'headingTertiaryText'         => '#000000',
        'headingTertiaryBackground'   => '#1b4afb',
        'headingTertiarySize'         => 15,
        'menuFont'                    => '"Open Sans", sans-serif',
        'menuLink'                    => '#6d7e00',
        'menuActive'                  => '#739b9b',
        'backgroundImage'             => '',
        'bodyFont'                    => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu sans", "Helvetica Neue", Helvetica, Arial, sans-serif',
        'textColor'                   => '#484649',
        'colorLinks'                  => '#6d7e00',
        'colorLinksLight'             => '#6d7e00',
        'colorDelLink'                => '#FF7777',
        'linkTextDecoration'          => false,
        'motionFixedFont'             => '"VeraMono", Consolas, Courier, sans-serif',
        'motionFixedFontColor'        => '#222',
        'motionStdFontSize'           => 14,
        'brandPrimary'                => '#e2007a',
        'buttonSuccessBackground'     => '#2c882c',
        'buttonFont'                  => '"Open Sans", sans-serif',
    ];

    public static $DEFAULTS_DBJR = [
        'useBoxShadow'                => true,
        'containerSize'               => 1024,
        'contentBorderRadius'         => 10,
        'sidebarWidth'                => 256,
        'sidebarBackground'           => '#dd0b18',
        'sidebarActionFont'           => '"FiraSans", sans-serif',
        'createMotionBtnColor'        => '#dd0b18',
        'bookmarkAmendmentBackground' => '#dd0b18',
        'bookmarkCommentColor'        => '#008000',
        'headingFont'                 => '"FiraSans", sans-serif',
        'headingPrimaryText'          => '#ffffff',
        'headingPrimaryBackground'    => '#404040',
        'headingPrimarySize'          => 15,
        'headingSecondaryText'        => '#ffffff',
        'headingSecondaryBackground'  => '#a6a6a6',
        'headingSecondarySize'        => 15,
        'headingTertiaryText'         => '#000000',
        'headingTertiaryBackground'   => '#c8c8c8',
        'headingTertiarySize'         => 15,
        'menuFont'                    => '"FiraSans", sans-serif',
        'menuLink'                    => '#8d8d8d',
        'menuActive'                  => '#333333',
        'backgroundImage'             => '',
        'bodyFont'                    => '"FiraSans", "Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu sans", "Helvetica Neue", Helvetica, Arial, sans-serif',
        'textColor'                   => '#484649',
        'colorLinks'                  => '#dd0b18',
        'colorLinksLight'             => '#dd0b18',
        'colorDelLink'                => '#dd0b18',
        'linkTextDecoration'          => false,
        'motionFixedFont'             => '"VeraMono", Consolas, Courier, sans-serif',
        'motionFixedFontColor'        => '#222',
        'motionStdFontSize'           => 14,
        'brandPrimary'                => '#dd0b18',
        'buttonSuccessBackground'     => '#dd0b18',
        'buttonFont'                  => '"FiraSans", sans-serif',
    ];

    public static function getAllSettings(string $defaults = 'layout-classic'): array
    {
        $settings = [
            'useBoxShadow'                => [
                'group'    => 'layout',
                'type'     => static::TYPE_CHECKBOX,
                'scssName' => 'use-box-shadow',
            ],
            'containerSize'               => [
                'group'    => 'layout',
                'type'     => static::TYPE_PIXEL,
                'scssName' => 'container-md',
            ],
            'contentBorderRadius'         => [
                'group'    => 'layout',
                'type'     => static::TYPE_PIXEL,
                'scssName' => 'contentBorderRadius',
            ],
            'sidebarBackground'           => [
                'group'    => 'layout',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'sidebarBackground',
            ],
            'sidebarWidth'                => [
                'group'    => 'layout',
                'type'     => static::TYPE_PIXEL,
                'scssName' => 'sidebarWidth',
            ],
            'sidebarActionFont'           => [
                'group'    => 'layout',
                'type'     => static::TYPE_FONT,
                'scssName' => 'sidebarActionFont',
            ],
            'createMotionBtnColor'        => [
                'group'    => 'layout',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'createMotionBtnColor',
            ],
            'bookmarkAmendmentBackground' => [
                'group'    => 'layout',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'bookmarkAmendmentBackground',
            ],
            'bookmarkCommentColor'        => [
                'group'    => 'layout',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'bookmarkCommentColor',
            ],
            'headingFont'                 => [
                'group'    => 'headings',
                'type'     => static::TYPE_FONT,
                'scssName' => 'headingFont',
            ],
            'headingPrimaryText'          => [
                'group'    => 'headings',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'headingPrimaryText',
            ],
            'headingPrimaryBackground'    => [
                'group'    => 'headings',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'headingPrimaryBackground',
            ],
            'headingPrimarySize'          => [
                'group'    => 'headings',
                'type'     => static::TYPE_PIXEL,
                'scssName' => 'headingPrimarySize',
            ],
            'headingSecondaryText'        => [
                'group'    => 'headings',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'headingSecondaryText',
            ],
            'headingSecondaryBackground'  => [
                'group'    => 'headings',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'headingSecondaryBackground',
            ],
            'headingSecondarySize'        => [
                'group'    => 'headings',
                'type'     => static::TYPE_PIXEL,
                'scssName' => 'headingSecondarySize',
            ],
            'headingTertiaryText'         => [
                'group'    => 'headings',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'headingTertiaryText',
            ],
            'headingTertiaryBackground'   => [
                'group'    => 'headings',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'headingTertiaryBackground',
            ],
            'headingTertiarySize'         => [
                'group'    => 'headings',
                'type'     => static::TYPE_PIXEL,
                'scssName' => 'headingTertiarySize',
            ],
            'menuFont'                    => [
                'group'    => 'layout',
                'type'     => static::TYPE_FONT,
                'scssName' => 'menuFont',
            ],
            'menuLink'                    => [
                'group'    => 'layout',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'menuLink',
            ],
            'menuActive'                  => [
                'group'    => 'layout',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'menuActive',
            ],
            'backgroundImage'             => [
                'group'    => 'layout',
                'type'     => static::TYPE_IMAGE,
                'scssName' => 'backgroundImage',
            ],
            'bodyFont'                    => [
                'group'    => 'text',
                'type'     => static::TYPE_FONT,
                'scssName' => 'bodyFont',
            ],
            'textColor'                   => [
                'group'    => 'text',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'textColor',
            ],
            'colorLinks'                  => [
                'group'    => 'text',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'colorLinks',
            ],
            'colorLinksLight'             => [
                'group'    => 'text',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'colorLinksLight',
            ],
            'colorDelLink'                => [
                'group'    => 'text',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'colorDelLink',
            ],
            'linkTextDecoration'          => [
                'group'    => 'text',
                'type'     => static::TYPE_CHECKBOX,
                'scssName' => 'linkTextDecoration',
            ],
            'motionFixedFont'             => [
                'group'    => 'text',
                'type'     => static::TYPE_FONT,
                'scssName' => 'motionFixedFont',
            ],
            'motionFixedFontColor'        => [
                'group'    => 'text',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'motionFixedFontColor',
            ],
            'motionStdFontSize'           => [
                'group'    => 'text',
                'type'     => static::TYPE_PIXEL,
                'scssName' => 'motionStdFontSize',
            ],
            'brandPrimary'                => [
                'group'    => 'buttons',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'brand-primary',
            ],
            'buttonSuccessBackground'     => [
                'group'    => 'buttons',
                'type'     => static::TYPE_COLOR,
                'scssName' => 'btn-success-bg',
            ],
            'buttonFont'                  => [
                'group'    => 'buttons',
                'type'     => static::TYPE_FONT,
                'scssName' => 'buttonFont',
            ],
        ];

        $defaultsArr = ($defaults === static::DEFAULTS_LAYOUT_DBJR ? static::$DEFAULTS_DBJR : static::$DEFAULTS_CLASSIC);
        foreach ($defaultsArr as $key => $value) {
            $settings[$key]['default'] = $value;
        }

        return $settings;
    }

    public function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    public function getValue(string $field, string $defaults): string
    {
        if ($this->$field !== null) {
            return (string)$this->$field;
        } else {
            return (string)static::getAllSettings($defaults)[$field]['default'];
        }
    }

    public function toScssVariables(string $defaults): string
    {
        $scss = '';
        foreach (static::getAllSettings() as $key => $data) {
            switch ($data['type']) {
                case static::TYPE_PIXEL:
                    $scss .= '$' . $data['scssName'] . ': ' . $this->getValue($key, $defaults) . "px;\n";
                    break;
                case static::TYPE_CHECKBOX:
                    if ($key === 'linkTextDecoration') {
                        $scss .= '$linkTextDecoration: ' . ($this->getValue($key, $defaults) ? 'underline' : 'none') . ";\n";
                    } else {
                        $scss .= '$' . $data['scssName'] . ': ' . ($this->getValue($key, $defaults) ? 'true' : 'false') . ";\n";
                    }
                    break;
                case static::TYPE_NUMBER:
                case static::TYPE_COLOR:
                case static::TYPE_FONT:
                    $scss .= '$' . $data['scssName'] . ': ' . $this->getValue($key, $defaults) . ";\n";
                    break;
            }
        }

        return $scss;
    }

    public function getSettingsHash(): string
    {
        return sha1(json_encode($this));
    }
}
