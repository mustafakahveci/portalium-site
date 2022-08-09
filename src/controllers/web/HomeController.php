<?php

namespace portalium\site\controllers\web;

use Yii;
use portalium\site\Module;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use portalium\site\models\Setting;

use portalium\site\models\ContactForm;
use portalium\web\Controller as WebController;

class HomeController extends WebController
{
    public function actionIndex()
    {
        $settings = ArrayHelper::map(Setting::find()->asArray()->all(), 'name', 'value');
        // has module content module
        $hasContentModule = Yii::$app->hasModule('content');
        $content = "";

        if ($hasContentModule) {
            $content = \portalium\content\models\Content::find()->where(['id_content' => $settings['page::home']])->one();
            if ($content) {
                $content = $content->body;
            } else {
                if (Yii::$app->user->isGuest) {
                    return $this->redirect('site/auth/login');
                } else {
                    $content = "<h1>" . Module::t('No content') . "</h1>";
                }
            }
        } else {
            $content = Module::t('<div class="site-index">
            <div class="jumbotron">
                <h1>' . Module::t('Portalium Home - Frontend') . '</h1>
            </div>
        </div>');
        }
        return $this->render(
            'index',
            [
                'content' => $content,
            ]
        );
    }

    public function actionAbout()
    {
        if (Setting::findOne(['name' => 'page::about'])->value)
            return $this->render('about');
        return $this->goHome();
    }

    public function actionContact()
    {
        if (Setting::findOne(['name' => 'form::contact'])->value) {
            $model = new ContactForm();
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($model->sendEmail(Setting::findOne(['name' => 'email::address'])->value)) {
                    Yii::$app->session->setFlash('success', Module::t('Thank you for contacting us. We will respond to you as soon as possible.'));
                } else {
                    Yii::$app->session->setFlash('error', Module::t('There was an error sending your message.'));
                }

                return $this->refresh();
            } else {
                return $this->render('contact', [
                    'model' => $model,
                ]);
            }
        }

        return $this->goHome();
    }

    public function actionLang($lang)
    {
        Yii::$app->session->set('lang', $lang);
        return $this->goBack(Yii::$app->request->referrer);
    }
}
