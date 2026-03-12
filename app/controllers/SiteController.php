<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ShortLinkForm;
use app\models\ShortLinks;
use app\models\ShortLinkVisits;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Label\Label;



class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('generatinga_short_link', ['model'=> new ShortLinkForm()]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Creates a new short link via AJAX.
     * @return Response
     */
    public function actionCreateShortLink()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new ShortLinkForm();

        if (!$model->load(Yii::$app->request->post()) || !$model->validate()) {
            return [
                'success' => false,
                'errors' => $model->getErrors(),
            ];
        }

        // Ищем существующую ссылку или создаем новую
        $shortLink = ShortLinks::findOne(['original_url' => $model->original_url]);

        if ($shortLink === null) {
            $shortLink = new ShortLinks();
            $shortLink->original_url = $model->original_url;

            // Генерируем уникальный короткий код
            do {
                $shortCode = Yii::$app->security->generateRandomString(8);
            } while (ShortLinks::find()->where(['short_code' => $shortCode])->exists());

            $shortLink->short_code = $shortCode;

            if (!$shortLink->save()) {
                return [
                    'success' => false,
                    'errors' => $shortLink->getErrors(),
                ];
            }
        }

        $shortUrl = Url::to(['site/redirect', 'code' => $shortLink->short_code], true);

        $writer = new PngWriter();
        $qrCode = new QrCode(
            data: $shortUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        // Create generic logo
        $path = \Yii::getAlias('@webroot/img/bender.png');
        if (!file_exists($path)) {
            \yii\helpers\FileHelper::createDirectory(dirname($path));
            $img = imagecreatetruecolor(50, 50);
            // Делаем фон прозрачным
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefill($img, 0, 0, $transparent);
            
            imagepng($img, $path);
        } 

        $logo = new Logo(
            path: $path,
            resizeToWidth: 50,
            punchoutBackground: true
        );

        // Create generic label
        $label = new Label(
            text: 'Label',
            textColor: new Color(255, 0, 0)
        );

        $result = $writer->write($qrCode, $logo, $label);

        return [
            'success' => true,
            'shortUrl' => $shortUrl,
            'qrCodeDataUri' => $result->getDataUri(),
        ];
    }

    /**
     * Redirects a short code to its original URL.
     * @param string $code the short code
     * @return Response
     * @throws NotFoundHttpException if the short code does not exist.
     */
    public function actionRedirect($code)
    {
        $shortLink = ShortLinks::findOne(['short_code' => $code]);

        if ($shortLink === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        // Записываем информацию о переходе
        $ip = Yii::$app->request->userIP;
        $visit = ShortLinkVisits::findOne(['short_link_id' => $shortLink->id, 'ip_address' => $ip]);

        if ($visit === null) {
            $visit = new ShortLinkVisits([
                'short_link_id' => $shortLink->id,
                'ip_address' => $ip,
            ]);
            $visit->save();
        } else {
            $visit->updateCounters(['visits' => 1]);
        }
        
        return $this->redirect($shortLink->original_url);
    }
}
