Parallel URL crawling library.
###Installation with Composer
```json
{
    "require": {
        "stil/curl-robot": "*"
    }
}
```
###Example of use
In this example we will download StackOverflow questions sequentially starting from question with index 100000.
```php
<?php
require __DIR__.'/vendor/autoload.php';

use cURL\Robot;
use cURL\Request;
use cURL\Event;
use cURL\RequestProviderInterface;

/**
 * Class providing requests to crawler
 */
class Crawler implements RequestProviderInterface
{
    /**
     * Starting question index
     */
    protected $question = 100000;

    /**
     * Prefix URL
     */
    protected $url = 'http://stackoverflow.com/questions/';

    /**
     * Method returning next request to execute
     */
    public function nextRequest()
    {
        return new Request($this->url.($this->question++));
    }
}

$robot = new Robot();
$robot->setRequestProvider(new Crawler());
// maximum amount of concurrent requests
$robot->setQueueSize(5);
// maximum amount of requests per minute (use it to prevent server overloading)
$robot->setMaximumRPM(60);

$queue = $robot->getQueue();
$queue->getDefaultOptions()
    ->set(CURLOPT_ENCODING, '') // gzip encoding
    ->set(CURLOPT_FOLLOWLOCATION, true) // follow redirects
    ->set(CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    ])
    ->set(CURLOPT_RETURNTRANSFER, true);

$queue->addListener('complete', function (Event $event) use ($robot, &$count) {
    $response = $event->response;
    $info = $response->getInfo();

    echo date('[Y-m-d H:i:s]').'[HTTP '.$info['http_code'].'] ';
    if ($info['http_code'] == 200) {
        $html = $response->getContent();

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $title = $dom->getElementsByTagName('title');
        if ($title->item(0)) {
            // print <title> tag contents
            echo $title->item(0)->textContent;
        }
    }

    echo "\n";
});

$robot->run();
```

Sample result:
```
[2014-08-25 13:56:30][HTTP 200] file format - General Binary Data Viewer for Windows Vista - Stack Overflow
[2014-08-25 13:56:30][HTTP 200] MVC or event-driven component-oriented web frameworks? - Stack Overflow
[2014-08-25 13:56:31][HTTP 404]
[2014-08-25 13:56:32][HTTP 200] osx - Eclipse text comparison order - Stack Overflow
[2014-08-25 13:56:33][HTTP 200] oop - What is a metaclass in Python? - Stack Overflow
[2014-08-25 13:56:34][HTTP 200] What's the best API you've ever used? - Stack Overflow
[2014-08-25 13:56:35][HTTP 200] c# - Logging Application Block - Logging the caller - Stack Overflow
[2014-08-25 13:56:36][HTTP 200] ide - How many of you prefer full screen? - Stack Overflow
[2014-08-25 13:56:37][HTTP 200] svn - What Are Some Decent ISPs That Host Subversion - Stack Overflow
[2014-08-25 13:56:38][HTTP 404]
[2014-08-25 13:56:39][HTTP 200] What's the best API you've ever used? - Stack Overflow
[2014-08-25 13:56:40][HTTP 200] svn - How can I create a directory listing of a subversion repository - Stack Overflow
[2014-08-25 13:56:41][HTTP 200] management - Perks for new programmers - Programmers Stack Exchange
[2014-08-25 13:56:42][HTTP 200] MVC or event-driven component-oriented web frameworks? - Stack Overflow
...
```
