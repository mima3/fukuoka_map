<?php
namespace MyLib;

/**
 * @class TwitterCtrl
 * @brief tmhOAuthを用いてツイッターのコントロールを行なう
 */
class TwitterCtrl
{
    const STATUS_NONE    = 0;
    const STATUS_REQUEST_TEMPORARY_TOKEN = 1;
    const STATUS_REQUEST_ACCESS_TOKEN = 2;
    const STATUS_AUTHORIZED = 3;
    const STATUS_ERROR = -1;

    private $status = self::STATUS_NONE;
    private $tmhOAuth;
    private $callbackurl;

    private $consumerKey;
    private $consumerSecret;
    private $accessToken;
    private $accessSecret;
    public function getConsumerKey()
    {
         return $this->consumerKey;
    }
    public function getConsumerSecret()
    {
         return $this->consumerSecret;
    }
    public function getAccessToken()
    {
         return $this->accessToken;
    }
    public function getAccessSecret()
    {
         return $this->accessSecret;
    }

    /**
     * コンストラクタ
     * @param[in] $consumerKey        コンシューマー・キー
     * @param[in] $consumerSecret コンシューマー・シークレット
     */
    public function __construct($consumerKey, $consumerSecret, $callbackurl)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;

        $this->callbackurl = $callbackurl;
        try {
            if ( isset($_SESSION['access_token'] ) ) {
                $this->accessToken = $_SESSION['access_token']['oauth_token'];
                $this->accessSecret = $_SESSION['access_token']['oauth_token_secret'];
                $this->tmhOAuth = new \tmhOAuth(
                    array(
                        'consumer_key' => $this->consumerKey,
                        'consumer_secret' => $this->consumerSecret,
                        'user_token'    => $this->accessToken,
                        'user_secret' => $this->accessSecret
                    )
                );
                $this->status = self::STATUS_AUTHORIZED;
            } else {
                $this->tmhOAuth = new \tmhOAuth(
                    array(
                        'consumer_key' => $this->consumerKey,
                        'consumer_secret' => $this->consumerSecret,
                    )
                );
                if ( isset($_SESSION['oauth']) and isset( $_REQUEST['oauth_verifier'] ) ) {
                     // テンポラリートークンは取得済み
                     $this->status = self::STATUS_REQUEST_ACCESS_TOKEN;
                } else {
                     $this->status = self::STATUS_REQUEST_TEMPORARY_TOKEN;
                }
            }
        } catch (Exception $e) {
            $this->status = self::STATUS_ERROR;
        }
    }

    /**
     * 現在のステータス状態を取得
     * @retval<TwitterCtrl::STATUS_NONE> エラーの状態
     * @retval<TwitterCtrl::STATUS_REQUEST_TEMPORARY_TOKEN> 
     *   テンポラリー・トークンを取得する必要がある状態
     * @retval<TwitterCtrl::STATUS_REQUEST_ACCESS_TOKEN>
     *  アクセストークンを取得する必要がある状態
     * @retval<TwitterCtrl::STATUS_AUTHORIZED> 認証済み
     */
    public function getStatus()
    {
        return $this->status;
    }
    public function isAuthorized()
    {
        return ($this->status == self::STATUS_AUTHORIZED);
    }

    /**
     * 未認証状態に戻す
     */
    public function reset()
    {
             /*
                $this->tmhOAuth = new tmhOAuth(array(
                    'consumer_key' => $consumerKey,
                    'consumer_secret' => $consumerSecret,
                ));
                */
                $this->status = self::STATUS_NONE;
                unset($_SESSION['access_token']);
                unset($_SESSION['oauth']);
    }

    /**
     * 認証を行なうURLを取得する<br>
     * この関数に成功した場合、 $_SESSION['oauth'] にテンポラリートークンが設定される
     * @return 失敗時は"" 成功時は認証を行なうページのURLを返す
     */
    public function getAuthorizeUrl()
    {
        $code = $this->tmhOAuth->request(
            'POST',
            $this->tmhOAuth->url('oauth/request_token', ''),
            array(
                'oauth_callback' => $this->callbackurl
            )
        );

        if ($code == 200) {

            $_SESSION['oauth'] = $this->tmhOAuth->extract_params($this->tmhOAuth->response['response']);
            $authurl = $this->tmhOAuth->url("oauth/authorize", '') . "?oauth_token={$_SESSION['oauth']['oauth_token']}";

            return $authurl;
        } else {
            return "";
        }
    }

    /**
     * アクセストークンを取得する。
     * この関数に成功した場合、アクセストークンは$_SESSION['access_token'] に格納される
     * この関数は下記のセッションが情報が存在することが前提となる
     *    ・$_REQUEST['oauth_verifier']
     *    ・$_SESSION['oauth']
     * @return true の場合は正常に取得できている。
     */
    public function requesAccessToken()
    {
        if ( !isset( $_REQUEST['oauth_verifier'] ) ) {
            $this->reset();

            return false;
        }
        if ( !isset( $_SESSION['oauth'] ) ) {
            $this->reset();

            return false;
        }
        $this->tmhOAuth->config['user_token'] = $_SESSION['oauth']['oauth_token'];
        $this->tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

        $code = $this->tmhOAuth->request(
            'POST',
            $this->tmhOAuth->url('oauth/access_token', ''),
            array(
                'oauth_verifier' => $_REQUEST['oauth_verifier']
            )
        );

        if ($code == 200) {
            $_SESSION['access_token'] = $this->tmhOAuth->extract_params($this->tmhOAuth->response['response']);
            unset($_SESSION['oauth']);
            $this->tmhOAuth->config['user_token'] = $_SESSION['access_token']['oauth_token'];
            $this->tmhOAuth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];
            $this->status = self::STATUS_AUTHORIZED;

            return true;
        } else {
            echo $code;
            $this->reset();

            return false;
        }
    }
    public function requestVerify()
    {
        if ( !$this->isAuthorized() ) {
            return null;
        }
        $code = $this->tmhOAuth->request(
            'GET',
            $this->tmhOAuth->url('1.1/account/verify_credentials')
        );

        if ($code == 200) {
            $resp = json_decode($this->tmhOAuth->response['response']);

            return $resp;
        } else {
            if ($code == 401) {
                // Invalid or expired token.
                $this->reset();
            }

            return null;
        }
    }
    public function requestUpdateImage($msg, $imagePath)
    {
        if (!$this->isAuthorized()) {
            return false;
        }
        // use auth: true multipart:true
        $code = $this->tmhOAuth->request(
            'POST',
            $this->tmhOAuth->url("1.1/statuses/update_with_media"),
            array(
                'status' => $msg,
                'media[]' => $imagePath,
            ),
            true,
            true
        );
        if ($code == 200) {
            //tmhUtilities::pr(json_decode($this->tmhOAuth->response['response']));
            return true;
        } else {
            //tmhUtilities::pr($this->tmhOAuth->response['response']);
            if ($code == 401) {
                // Invalid or expired token.
                $this->reset();
            }

            return false;
        }
    }
    public function requestGeoSearch($lat, $long, $accuracy)
    {
        if ( !$this->isAuthorized() ) {
            return false;
        }
        $geo = $lat . ',' . $long . ',' . $accuracy;
        // use auth: true multipart:false
        $code = $this->tmhOAuth->request(
            'GET',
            $this->tmhOAuth->url("1.1/search/tweets.json"),
            array(
                'geocode' => $geo,
                'result_type' => 'recent',
                'count' => 100
            ),
            true,
            false
        );
        if ($code == 200) {
            //tmhUtilities::pr(json_decode($this->tmhOAuth->response['response']));
            return json_decode($this->tmhOAuth->response['response']);
        } else {
            //tmhUtilities::pr($this->tmhOAuth->response['response']);
            if ($code == 401) {
                // Invalid or expired token.
                $this->reset();
            }

            return null;
        }
    }
}
