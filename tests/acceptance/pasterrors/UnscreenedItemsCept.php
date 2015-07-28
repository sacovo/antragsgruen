<?php

/** @var \Codeception\Scenario $scenario */
use app\models\settings\Consultation;

$I = new AcceptanceTester($scenario);
$I->populateDBData1();


$I->wantTo('create a unscreened motion');

$I->gotoConsultationHome(true, 'bdk', 'bdk');
$I->loginAsStdAdmin();

$I->gotoStdAdminPage(true, 'bdk', 'bdk');



$I->click('#sidebar .createMotion');

$title = 'Nicht freigeschalteter Testantrag';

$I->fillField(['name' => 'sections[20]'], $title);
$I->executeJS('CKEDITOR.instances.sections_21_wysiwyg.setData("<p><strong>Test</strong></p>");');
$I->executeJS('CKEDITOR.instances.sections_22_wysiwyg.setData("<p><strong>Test 2</strong></p>");');
$I->selectOption('#personTypeOrga', \app\models\db\ISupporter::PERSON_ORGANIZATION);
$I->fillField(['name' => 'Initiator[name]'], 'Mein Name');
$I->fillField(['name' => 'Initiator[contactEmail]'], 'test@example.org');
$I->fillField('#resolutionDate', '01.01.2000');
$I->submitForm('#motionEditForm', [], 'save');
$I->submitForm('#motionConfirmForm', [], 'confirm');
$I->gotoConsultationHome(true, 'bdk', 'bdk');


$layoutTypes = Consultation::getStartLayouts();
foreach ($layoutTypes as $typeId => $typeName) {
    $I->wantTo('switch to: ' . $typeName);

    $page = $I->gotoStdAdminPage(true, 'bdk', 'bdk');
    $I->see($title);
    $page->gotoConsultation();
    $I->selectOption('#startLayoutType', $typeName);
    $I->submitForm('#consultationSettingsForm', [], 'save');

    $I->gotoConsultationHome(true, 'bdk', 'bdk');
    $I->dontSee($title);
}

// @TODO As an agenda item