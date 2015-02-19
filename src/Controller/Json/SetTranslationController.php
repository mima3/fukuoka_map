<?php
namespace Controller\Json;

/**
 * 翻訳用テキストを変更する
 */
class SetTranslationController extends \Controller\ControllerBase
{
    public function route()
    {
        $model = $this->models['MsTranslatorCacheModel'];
        $modelLog = $this->models['TranslationLogModel'];
        if (!$this->app->request->post('id')) {
            $this->app->halt(412, 'Precondition Failed');
            return;
        }
        if (!$this->app->request->post('result')) {
            $this->app->halt(412, 'Precondition Failed');
            return;
        }
        $twCtrl = $this->modules['TwitterCtrl'];
        $sts = $twCtrl->getStatus();
        if ($sts !== \MyLib\TwitterCtrl::STATUS_AUTHORIZED) {
            $this->app->halt(412, 'Precondition Failed');
            return;
        }
        $updated = time();
        $user = $_SESSION['twitter_user'];
        $id = htmlspecialchars($this->app->request->post('id'));
        $result = htmlspecialchars($this->app->request->post('result'));

        $ret = $model->search(0, 1, $id, null, null, null, null);
        if (!$ret) {
            $this->app->halt(412, 'not found (id)');
            return;
        }
        var_dump($ret->rows[0]);
        $previous = $ret->rows[0]['result'];
        if ($previous === $result) {
            $this->app->halt(412, 'no change.');
            return;
        }

        $model->update(
            $id,
            $result,
            $user,
            $updated
        );
        $modelLog->append(
            $user,
            $id,
            $previous,
            $result,
            $updated
        );
        return;
    }
}
