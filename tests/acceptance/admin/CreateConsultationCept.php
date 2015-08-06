<?php

/** @var \Codeception\Scenario $scenario */
use app\tests\_pages\ConsultationHomePage;

$I = new AcceptanceTester($scenario);
$I->populateDBData1();

$I->loginAndGotoStdAdminPage()->gotoConsultationCreatePage();

$I->see('Standard-Veranstaltung', '.consultation1');

$I->wantTo('create a new consultation');
$I->fillField('#newTitle', 'Neue Veranstaltung 1');
$I->fillField('#newShort', 'NeuKurz');
$I->fillField('#newPath', 'neukurz');
$I->uncheckOption('#newSetStandard');
$I->submitForm('.consultationCreateForm', [], 'createConsultation');

$I->see('Die neue Veranstaltung wurde angelegt.');
$I->see('Neue Veranstaltung 1', '.consultation7');
$I->see('Standard-Veranstaltung', '.consultation1');

ConsultationHomePage::openBy($I, [
    'subdomain' => 'stdparteitag'
]);
$I->see('Test2', 'h1');


$I->wantTo('create the same again, should not work');

$I->gotoStdAdminPage()->gotoConsultationCreatePage();

$I->fillField('#newTitle', 'Neue Veranstaltung 2');
$I->fillField('#newShort', 'NeuKurz 2');
$I->fillField('#newPath', 'neukurz');
$I->uncheckOption('#newSetStandard');
$I->submitForm('.consultationCreateForm', [], 'createConsultation');

$I->see('Diese Adresse ist leider schon von einer anderen Veranstaltung auf dieser Seite vergeben.');


$I->wantTo('create a new standard consultation');

$I->fillField('#newTitle', 'Noch eine neue Veranstaltung');
$I->fillField('#newShort', 'NeuKurz2');
$I->fillField('#newPath', 'neukurz2');
$I->checkOption('#newSetStandard');
$I->submitForm('.consultationCreateForm', [], 'createConsultation');

$I->see('Die neue Veranstaltung wurde angelegt.');
$I->see('Noch eine neue Veranstaltung', '.consultation8');
$I->see('Standard-Veranstaltung', '.consultation8');

ConsultationHomePage::openBy($I, [
    'subdomain' => 'stdparteitag'
]);
$I->see('Noch eine neue Veranstaltung', 'h1');



$I->wantTo('set another consultation as standard');

$I->gotoStdAdminPage()->gotoConsultationCreatePage();

$I->click('.consultation7 .stdbox button');
$I->see('Die Veranstaltung wurde als Standard-Veranstaltung festgelegt.');

ConsultationHomePage::openBy($I, [
    'subdomain' => 'stdparteitag'
]);
$I->see('Neue Veranstaltung 1', 'h1');