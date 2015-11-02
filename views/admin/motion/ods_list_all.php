<?php

use \app\components\opendocument\Spreadsheet;
use app\components\StringSplitter;
use app\components\UrlHelper;
use app\models\db\Amendment;
use app\models\db\ConsultationAgendaItem;
use app\models\db\Motion;

/**
 * @var $this yii\web\View
 * @var array $items
 */

/** @var \app\controllers\Base $controller */
const agendaColor = '#fff2cc';
const motionColor = '#38761d';
$controller   = $this->context;
$consultation = $controller->consultation;

$DEBUG = false;

/** @var \app\models\settings\AntragsgruenApp $params */
$params = \yii::$app->params;

$tmpZipFile   = $params->tmpDir . uniqid('zip-');
$templateFile = \yii::$app->basePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'OpenOffice-Template.ods';
copy($templateFile, $tmpZipFile);

$zip = new ZipArchive();
if ($zip->open($tmpZipFile) !== true) {
    die("cannot open <$tmpZipFile>\n");
}

$content = $zip->getFromName('content.xml');
$doc     = new Spreadsheet($content);

$currCol = 0;

$COL_PREFIX    = $currCol++;
$COL_TITLE     = $currCol++;
$COL_INITIATOR = $currCol++;
$COL_EMAIL     = $currCol++;
$COL_PHONE     = $currCol++;
$COL_LINK      = $currCol++;

$colLimit = $currCol;

$doc->setCell(0, $COL_PREFIX, Spreadsheet::TYPE_HTML, 'Kürzel');
$doc->setCell(0, $COL_TITLE, Spreadsheet::TYPE_HTML, 'Titel');
$doc->setCell(0, $COL_INITIATOR, Spreadsheet::TYPE_HTML, 'Antragsteller*in');
$doc->setCell(0, $COL_EMAIL, Spreadsheet::TYPE_HTML, 'Email-Adresse');
$doc->setCell(0, $COL_PHONE, Spreadsheet::TYPE_HTML, 'Telefonnummer');
$doc->setCell(0, $COL_LINK, Spreadsheet::TYPE_HTML, 'Antrag');

$doc->setColumnWidth($COL_PREFIX, 2);
$doc->setColumnWidth($COL_TITLE, 6);
$doc->setColumnWidth($COL_INITIATOR, 6);
$doc->setColumnWidth($COL_EMAIL, 4);
$doc->setColumnWidth($COL_PHONE, 4);

$row = 1;

$fill = function ($cellAttributes, $textAttributes) use ($doc, &$row, $colLimit) {
    for ($col = 0; $col < $colLimit; $col++) {
        $doc->setCellStyle($row, $col, $cellAttributes, $textAttributes);
    }
};

foreach ($items as $item) {
    if ($item instanceof ConsultationAgendaItem) {
        $doc->setCell($row, $COL_PREFIX, Spreadsheet::TYPE_HTML, $item->code);
        $fill (['fo:background-color' => agendaColor], []);
    } else {
        if ($item instanceof Motion || $item instanceof Amendment) {
            $title  = $item->title;
            $prefix = $item->titlePrefix;

            if ($item instanceof Motion) {
                $subject = str_replace('%MOTION%', $prefix, \Yii::t('export', 'mail_motion_x'));
                $body    = $prefix . ' ("' . $title . '")';
            } else {
                $subject = $title;
                $title   = mb_substr($title, mb_strlen($prefix) + 2);
                $body    = \Yii::t('export', 'mail_amendment_x_to_y');
                $body    = str_replace(['%AMENDMENT%', '%MOTION%'], [$prefix, $item->motion->title], $body);
            }
            $initiator = $item->getInitiators() [0];
            $email     = $initiator->contactEmail;
            $phone     = $initiator->contactPhone;
            $name      = $initiator->getNameWithOrga();
            $firstName = StringSplitter::first([' '], mb_substr($name, 0, 4) == 'Dr. ' ? mb_substr($name, 4) : $name);
            if ($item instanceof Motion) {
                $doc->setCell($row, $COL_TITLE, Spreadsheet::TYPE_HTML, $item->title);
                $fill ([], ['fo:color' => motionColor]);
            }
            $doc->setCell($row, $COL_PREFIX, Spreadsheet::TYPE_HTML, $prefix);
            $doc->setCell($row, $COL_INITIATOR, Spreadsheet::TYPE_HTML, $name);
            $mailbody = str_replace(['%MOTION%', '%NAME%'], [$body, $firstName], \Yii::t('export', 'mail_body'));
            $href     = 'mailto:' . $email . '?subject=' . $prefix . ': ' . $title . '&body=' . rawurlencode($mailbody);
            $doc->setCell($row, $COL_EMAIL, Spreadsheet::TYPE_LINK, ['href' => $href, 'text' => $email]);
            if ($phone) {
                $phoneLink = 'tel:' . StringSplitter::first(['//', ','], $phone);
                $doc->setCell($row, $COL_PHONE, Spreadsheet::TYPE_LINK, ['href' => $phoneLink, 'text' => $phone]);
            }
            $viewUrl    = $item instanceof Motion ? UrlHelper::createMotionUrl($item) : UrlHelper::createAmendmentUrl($item);
            $linkParams = ['href' => UrlHelper::absolutizeLink($viewUrl), 'text' => \Yii::t('export', 'motion')];
            $doc->setCell($row, $COL_LINK, Spreadsheet::TYPE_LINK, $linkParams);
        } else { // null
            $doc->setCell($row, $COL_PREFIX, Spreadsheet::TYPE_HTML, \Yii::t('export', 'misc'));
            $fill (['fo:background-color' => agendaColor], []);
        }
    }
    $row++;
}

$content = $doc->create();

if ($DEBUG) {
    $doc->debugOutput();
}

$zip->deleteName('content.xml');
$zip->addFromString('content.xml', $content);
$zip->close();

readfile($tmpZipFile);
unlink($tmpZipFile);
