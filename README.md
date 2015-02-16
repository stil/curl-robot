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
$robot->setQueueSize(3);
// maximum amount of requests per minute (use it to prevent server overloading)
$robot->setMaximumRPM(60);

$queue = $robot->getQueue();
$queue->getDefaultOptions()->set([
    CURLOPT_TIMEOUT        => 5,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING       => '',
    CURLOPT_HTTPHEADER     => [
        'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    ]
]);

$count = 0;
$queue->addListener('complete', function (Event $event) use ($robot, &$count) {
    $response = $event->response;
    $httpCode = $response->getInfo(CURLINFO_HTTP_CODE);

    $question = '';
    if ($httpCode == 200) {
        $count++;
        $html = $response->getContent();
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $title = $dom->getElementsByTagName('title');
        if ($title->item(0)) {
            // print <title> tag contents
            $question = $title->item(0)->textContent;
        }
    }

    printf(
        "[OK %d][%s][HTTP %s] %s\n",
        $count,
        date('Y-m-d H:i:s'),
        $httpCode,
        $question
    );

    if (!$robot->isPauseRequested() && $count > 0 && $count % 5 == 0) {
        echo "Pausing crawler... \n";
        $robot->pause();
    }

    if ($robot->hasPaused()) {
        echo "Processing completed requests (adding to database etc.)...\n";
        $robot->resume();
    }
});

$robot->run();
```

Sample result:
```
[OK 0][2015-02-16 11:22:42][HTTP 404]
[OK 1][2015-02-16 11:22:42][HTTP 200] file format - General Binary Data Viewer for Windows Vista - Stack Overflow
[OK 2][2015-02-16 11:22:43][HTTP 200] MVC or event-driven component-oriented web frameworks? - Stack Overflow
[OK 3][2015-02-16 11:22:43][HTTP 200] osx - Eclipse text comparison order - Stack Overflow
[OK 4][2015-02-16 11:22:45][HTTP 200] oop - What is a metaclass in Python? - Stack Overflow
[OK 5][2015-02-16 11:22:46][HTTP 200] What's the best API you've ever used? - Stack Overflow
Pausing crawler...
[OK 6][2015-02-16 11:22:46][HTTP 200] ide - How many of you prefer full screen? - Stack Overflow
[OK 7][2015-02-16 11:22:48][HTTP 200] c# - Logging Application Block - Logging the caller - Stack Overflow
Processing completed requests (adding to database etc.)...
[OK 7][2015-02-16 11:22:49][HTTP 404]
[OK 8][2015-02-16 11:22:50][HTTP 200] svn - What Are Some Decent ISPs That Host Subversion - Stack Overflow
[OK 9][2015-02-16 11:22:51][HTTP 200] What's the best API you've ever used? - Stack Overflow
[OK 10][2015-02-16 11:22:52][HTTP 200] svn - How can I create a directory listing of a subversion repository - Stack Overflow
Pausing crawler...
[OK 11][2015-02-16 11:22:53][HTTP 200] management - Perks for new programmers - Programmers Stack Exchange
[OK 12][2015-02-16 11:22:54][HTTP 200] Should I remove tags which don't seem appropriate? - Meta Stack Exchange
Processing completed requests (adding to database etc.)...
[OK 12][2015-02-16 11:22:55][HTTP 404]
[OK 13][2015-02-16 11:22:56][HTTP 200] MVC or event-driven component-oriented web frameworks? - Stack Overflow
...
```
