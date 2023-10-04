<?php
namespace app\components\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash() or addFlash(). You can set message as following:
 *
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->addFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 */
class Alert extends \yii\base\Widget
{
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - key: the name of the session flash variable
     * - value: the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning'
    ];

    /**
     * @var boolean If close button to be showed.
     */
    public $showCloseButton = true;

    /**
     * Alert block options
     * @var array the options to be appended to alert div tag.
     */
    public $options = [];


    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();

        foreach ($flashes as $type => $flash) {
            if (!isset($this->alertTypes[$type])) {
                continue;
            }

            foreach ((array) $flash as $i => $message) {
                $options =  array_merge($this->options, [
                    'id' => $this->getId() . '-' . $type . '-' . $i,
                    'class' => 'alert ' . $this->alertTypes[$type]
                ]);
                if ($this->showCloseButton) {
                    $options['class'] .= ' alert-dismissible';
                }
                if (strpos($type, 'error')) {
                    $message = '<strong>Error!</strong> ' . $message;
                }
                $this->renderSingleAlert($message, $options);
            }

            $session->removeFlash($type);
        }
    }
    public static function shouldhideRegisterForm(){
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        return array_key_exists('success',$flashes) ? 555 : 000;
    }

    /**
     * Renders single alert widget
     * @param  string $message Alert message. It will NOT be HTML-encoded.If this is is coming from end users, you should consider encode it to prevent XSS attacks.
     * @param  array $options Array of attributes to be appended to alert div tag.
     * @return void
     */
    protected function renderSingleAlert($message, $options) {
        echo Html::beginTag('div', $options) . "\n";
        if ($this->showCloseButton) {
            echo Html::button('<span aria-hidden="true">×</span>', [
                'class' => 'close',
                'data-dismiss' => 'alert',
                'aria-label' => 'Close'
            ]);
        }
        echo $message . "\n";
        echo Html::endTag('div');
    }
}
