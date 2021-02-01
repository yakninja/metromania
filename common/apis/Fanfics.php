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
use yii\helpers\Json;

class Fanfics extends Component
{
    const BASE_URL = 'https://fanfics.me';
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

    private function isLoggedIn(DomInterface $dom)
    {
        if (($nodes = $dom->find('a.light')) && count($nodes)) {
            foreach ($nodes as $node) {
                /** @var Dom\Node\HtmlNode $node */
                if (trim($node->innerText) == 'Выход' &&
                    substr($node->href, 0, 11) == '/autent.php') {
                    // already logged in
                    return true;
                }
            }
        }
        // not logged in
        return false;
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
            if ($this->isLoggedIn($dom)) {
                return true;
            }

            if ($dom->find('form[name="autent"]')[0]) {
                // logging in
                $this->client->post(
                    '/autent.php',
                    ['form_params' => ['name' => $username, 'pass' => $password]]
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
        $parts = parse_url($url);
        if (empty($parts['query'])) {
            throw new Exception('Invalid chapter URL');
        }
        parse_str($parts['query'], $params);
        if (empty($params['id']) || @$params['chapter'] === '') {
            throw new Exception('Fic/chapter id not found in url');
        }
        $fic_id = $params['id'];
        $chapter_id = $params['chapter'];

        try {
            $response = $this->client->get($url);
        } catch (GuzzleException $e) {
            throw new Exception('Could not get the destination url: ' . $e->getMessage());
        }
        $dom = new Dom;
        try {
            $dom->loadStr($response->getBody());
            /** @var Dom\Node\HtmlNode $node */
            $editLink = null;
            if ($nodes = $dom->find('a.small_link')) {
                foreach ($nodes as $node) {
                    if (trim($node->innerText) == 'Редактировать текст главы') {
                        $editLink = $node;
                        break;
                    }
                }
            }

            if (!$editLink) {
                throw new Exception('Can\'t find the edit link for the destination');
            }

            $response = $this->client->get($editLink->href);
            $dom->loadStr($response->getBody());

            if (!($nodes = $dom->find('#newChapter')) || !($form = $nodes[0])) {
                throw new Exception('Can\'t find the chapter edit form');
            }
            $action = "https://fanfics.me/section_fic_write_post.php?" .
                "action=edit_fic_chapter_edit_take&fic_id={$fic_id}";

            echo "$action\n";

            $formParams = [
                'partTitle' => '',
                'partAnnotation' => '',
                'chapter_fic_id' => $chapter_id,
                'chapterName' => $title,
                'chapterText' => $content,
                'changes_comment' => '',
                'draft' => 'on',
                'goto' => 'contents',
            ];
            var_dump($formParams);
            $response = $this->client
                ->post($action, [
                    'form_params' => $formParams,
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Referer' => $editLink->href,
                    ],
                ]);
            if ($response->getStatusCode() != 200) {
                throw new Exception('Could not export: invalid status code: ' . $response->getStatusCode());
            }
            try {
                $responseData = Json::decode($response->getBody());
            } catch (\Exception $e) {
                throw new Exception('Invalid result (could not parse)');
            }
            if (!$responseData || !@$responseData['q']) {
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
