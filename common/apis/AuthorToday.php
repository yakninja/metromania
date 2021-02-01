<?php

namespace common\apis;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPHtmlParser\Contracts\DomInterface;
use PHPHtmlParser\Dom;
use Yii;
use yii\base\Component;
use yii\base\Exception;

class AuthorToday extends Component
{
    const BASE_URL = 'https://author.today';

    public $cookieFile;

    /** @var Client */
    private $client;

    private $clientOptions;

    public function init()
    {
        parent::init();
        if (!$this->cookieFile) {
            $this->cookieFile = Yii::getAlias('@common/runtime/cookies.json');
        }
        $jar = new FileCookieJar($this->cookieFile);
        $this->clientOptions = [
            'headers' => [
                'authority' => 'author.today',
                'pragma' => 'no-cache',
                'cache-control' => 'no-cache',
                'sec-ch-ua' => '"Chromium";v="88", "Google Chrome";v="88", ";Not A Brand";v="99"',
                'sec-ch-ua-mobile' => '?0',
                'upgrade-insecure-requests' => '1',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_1_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36',
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site' => 'none',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-user' => '?1',
                'sec-fetch-dest' => 'document',
                'accept-language' => 'en-US,en;q=0.9,ru;q=0.8,bg;q=0.7,ru-RU;q=0.6,uk;q=0.5',
            ],
            'decode_content' => 'gzip',
        ];
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'cookies' => $jar,
            'request.options' => $this->clientOptions,
        ]);
    }

    private function isLoggedIn(DomInterface $dom)
    {
        return ($nodes = $dom->find('i.icon-exit')) && count($nodes);
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
            $options = $this->clientOptions;
            $options['debug'] = true;
            $response = $this->client->get('/', $options);
        } catch (GuzzleException $e) {
            throw new Exception('Could not log in: ' . $e->getMessage());
        }
        echo "got front page\n";

        // Now we can see either if we are logged in or a login form here
        $dom = new Dom;
        try {
            $dom->loadStr($response->getBody());
            if ($this->isLoggedIn($dom)) {
                return true;
            }

            if (($form = $dom->find('form[action="/account/login"]')[0])) {
                /** @var Dom\Node\HtmlNode $form */
                if (!($input = $form->find('input[name="__RequestVerificationToken"]')[0])) {
                    throw new Exception('Could not find login verification token');
                }
                // logging in
                $this->client->post(
                    '/account/login',
                    [
                        'form_params' => [
                            '__RequestVerificationToken' => $input->value,
                            'Login' => $username,
                            'Password' => $password,
                            'RememberMe' => 'true',
                        ],
                    ],
                );
                $response = $this->client->get('/');
                $dom->loadStr($response->getBody());
                if (!$this->isLoggedIn($dom)) {
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
        return true;
    }
}
