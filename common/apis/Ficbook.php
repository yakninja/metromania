<?php

namespace common\apis;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPHtmlParser\Dom;
use Yii;
use yii\base\Component;
use yii\base\Exception;

class Ficbook extends Component
{
    const BASE_URL = 'https://ficbook.net';

    public $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_1_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36';
    public $cookieFile;

    /** @var Client */
    private $client;

    public function init()
    {
        parent::init();
        if (!$this->cookieFile) {
            $this->cookieFile = Yii::getAlias('@common/runtime/cookies.json');
        }
        $jar = new FileCookieJar($this->cookieFile);
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'cookies' => $jar,
            'request.options' => [
                'headers' => ['User-agent' => $this->userAgent],
            ],
        ]);
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @throws Exception if not logged in for any reason
     */
    public function login(string $username, string $password): bool
    {
        try {
            $response = $this->client->get('/');
        } catch (GuzzleException $e) {
            throw new Exception('Could not log in: ' . $e->getMessage());
        }

        // Now we can see either if we are logged in or a login form here
        $dom = new Dom;
        try {
            $dom->loadStr($response->getBody());
            if ($dom->find('a[href=/logout]')[0]) {
                // already logged in
                return true;
            }
            if ($dom->find('#mainLoginForm')[0]) {
                // logging in
                $this->client->post(
                    '/login_check',
                    ['form_params' => ['login' => $username, 'password' => $password, 'remember' => 'on']]
                );
                $response = $this->client->get('/');
                $dom->loadStr($response->getBody());
                if (!$dom->find('a[href=/logout]')[0]) {
                    // failed
                    throw new Exception('Login failed');
                }
                // success
            } else {
                throw new Exception('No login form found on page');
            }
        } catch (\Exception $e) {
            throw new Exception('Could not log in: invalid home page response: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            throw new Exception('Could not log in: ' . $e->getMessage());
        }
        return true;
    }
}
