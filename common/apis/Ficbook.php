<?php

namespace common\apis;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPHtmlParser\Dom;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;

class Ficbook extends Component
{
    const BASE_URL = 'https://ficbook.net';
    const STATUS_IN_PROGRESS = '1';
    const STATUS_FINISHED = '2';
    const STATUS_FROZEN = '3';

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
                echo "logged in!\n";
            } else {
                throw new Exception('No login form found on page');
            }
        } catch (\Exception $e) {
            throw new Exception('Could not log in: invalid home page content: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            throw new Exception('Could not log in: ' . $e->getMessage());
        }
        return true;
    }

    /**
     * @param string $url
     * @param string $title
     * @param string $content
     * @return bool
     * @throws Exception
     */
    public function publish(string $url, string $title, string $content)
    {
        try {
            $response = $this->client->get($url);
        } catch (GuzzleException $e) {
            throw new Exception('Could not get the destination url: ' . $e->getMessage());
        }
        $dom = new Dom;
        try {
            $dom->loadStr($response->getBody());
            #main > div:nth-child(1) > section > div > article > div:nth-child(5) > a
            /** @var Dom\Node\HtmlNode */
            if (!($editIcon = $dom->find('article svg.ic_edit')[0])) {
                throw new Exception('Can\'t find the edit link for the destination');
            }
            $link = $editIcon->parent->href;
            if (!preg_match('`/home/myfics/([0-9]+)/parts/([0-9]+)`', $link, $r)) {
                throw new Exception('Can\'t parse the edit link for fanfic id/part id');
            }
            $fanficId = $r[1];
            $partId = $r[2];
            $response = $this->client->get($link);
            $dom->loadStr($response->getBody());
            $formParams = [
                'part_id' => $partId,
                'fanfic_id' => $fanficId,
                'title' => $title,
                'content' => $content,
                'comment_direction' => '0',
                'comment' => '',
                'change_description' => '',
                'status' => self::STATUS_IN_PROGRESS,
                'not_published' => '1',
                'auto_pub' => '0',
            ];
            $response = $this->client->post('/home/fanfics/partbetaedit_save',
                ['form_params' => $formParams]);
            if ($response->getStatusCode() != 200) {
                throw new Exception('Could not export: invalid status code: ' . $response->getStatusCode());
            }
            $responseData = Json::decode($response->getBody());
            if (!$responseData || !@$responseData['result']) {
                throw new Exception('Invalid result');
            }
        } catch (\Exception $e) {
            throw new Exception('Could not export: invalid page content: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            throw new Exception('Could not get the edit url: ' . $e->getMessage());
        }
        return true;
    }
}
