###Installation
You can install this package with Composer. Run following command:
```bash
composer require stil/curl-robot:dev-master
```
###Example 1
```php
<?php
require __DIR__.'/../vendor/autoload.php';

use cURL\Request;
use cURL\Robot\RobotSwarm;
use cURL\Robot\Robot;
use cURL\Robot\Event\RequestAttachingEvent;
use cURL\Robot\Event\RequestCompletedEvent;
use cURL\Robot\RequestProviderInterface;

class Crawler implements RequestProviderInterface
{
    protected $number = 0;

    /**
     * Method returning next request to execute
     */
    public function nextRequest()
    {
        return new Request("http://httpbin.org/delay/1?num=".($this->number++));
    }
}

$swarm = new RobotSwarm();
$swarm->setRequestProvider(new Crawler());
$swarm->getDefaultOptions()->set([
    CURLOPT_TIMEOUT        => 5,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    ]
]);

$robot1 = new Robot();
$robot1->setQueueSize(1);
$robot1->addRateLimit(new RateLimit(20, 60));
$robot1->addListener('request.attaching', function (RequestAttachingEvent $e) {
    echo "Attaching request from robot1\n";
});

$robot2 = new Robot();
$robot2->setQueueSize(3);
$robot1->addRateLimit(new RateLimit(120, 60));
$robot2->addListener('request.attaching', function (RequestAttachingEvent $e) {
    // Proxy requests
    $e->request->getOptions()->set(CURLOPT_PROXY, '10.0.0.1:8080');
    echo "Attaching request from robot2 (proxied)\n";
});

$swarm->addListener('request.completed', function (RequestCompletedEvent $e) {
    $httpCode = $e->response->getInfo(CURLINFO_HTTP_CODE);

    if ($httpCode == 200) {
        $json = $e->response->getContent();
        $data = json_decode($json, true);
        printf("Successful request #%d\n", $data['args']['num']);
    } else {
        printf("Wrong HTTP code %d\n", $httpCode);
        // Retry request until we exceeded allowed amount of attempts
        if ($e->handler->getAttempts() < 3) {
            printf("Retrying, attempt %d\n", $e->handler->getAttempts());
            $e->swarm->retry($e->handler);
        }
    }
});

$swarm->add($robot1);
$swarm->add($robot2);
$swarm->run();
```
