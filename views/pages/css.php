<?php

/**
 * @var \app\models\settings\Stylesheet $stylesheetSettings
 */

$css = '
@charset "UTF-8";

@import "variables";

$OpenSansPath: "./fonts/OpenSans/fonts";
@import "./fonts/OpenSans/open-sans.scss";
$veraSansPath: "./fonts/BitstreamVeraSansMono";
@import "./fonts/BitstreamVeraSansMono/verasans";
$firaSansFontPath: "./fonts/firasans/";
@import "./fonts/firasans/firasans";
$veraSansPath: "./fonts/BitstreamVeraSansMono";
@import "./fonts/BitstreamVeraSansMono/verasans";

' . $stylesheetSettings->toScssVariables(\app\models\settings\Stylesheet::DEFAULTS_LAYOUT_CLASSIC) . '

$screen-md-min: $container-md !default;
$mainContentWidth: $container-md - $sidebarWidth !default;
$content-max-width: $mainContentWidth - 2px !default;
$grid-float-breakpoint: $container-md - 32px !default; // $screen-md-min;

$table-border-color: $colorGreenLight;

$sidebarActionFont: "Open Sans", sans-serif;
$deadlineCircleFont: "Open Sans", sans-serif;
$motionFixedWidth: 740px;
$inlineAmendmentPreambleHeight: 30px;
$inlineAmendmentPreambleColor: rgb(226, 0, 122);
$icon-font-path: "./fonts/";

@import "bootstrap";
@import "fontello";
@import "wizard";
@import "helpers";
@import "elements";
@import "bootstrap_fuelux_overwrites";
@import "base_layout";
@import "contentpage";
@import "consultation_motion_list";
@import "motions";
@import "proposed_procedure";
@import "styles";
@import "sidebar";
@import "user_pages";

';

if ($stylesheetSettings->backgroundImage) {
    $css .= '
body {
    background: url("' . $stylesheetSettings->backgroundImage . '") no-repeat scroll center center transparent;
    background-size: cover;
    background-attachment: fixed;
}
';
} else {
    $css .= '
html {
  background: url("./img/wallpaper.jpg") repeat scroll 0 0 transparent;
}

body {
  background: url("./img/backgroundGradient.png") repeat-x scroll 0 0 transparent;
}
    ';
}

$css .= '.logoImg {
  display: block;
  width: 377px;
  height: 55px;
  background-image: url("./img/logo.png");
  @media screen and (max-width: 479px) {
    width: 300px;
    height: 44px;
    background-size: 300px 44px;
  }
}
';


$scss = new \Leafo\ScssPhp\Compiler();
$scss->addImportPath(Yii::$app->basePath . '/web/css/');
$scss->setFormatter(\Leafo\ScssPhp\Formatter\Compressed::class);
echo $scss->compile($css);
