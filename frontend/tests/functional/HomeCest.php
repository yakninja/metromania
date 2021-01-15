<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class HomeCest
{
    public function checkOpen(FunctionalTester $I)
    {
        $I->amOnPage(\Yii::$app->homeUrl);
        $I->see('Metromania');
        $I->seeLink('Signup');
        $I->click('Signup');
        $I->see('Signup');
    }
}
