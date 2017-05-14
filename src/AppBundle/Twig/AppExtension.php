<?php

namespace AppBundle\Twig;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\MongoDB\Connection;
use Doctrine\Bundle\MongoDBBundle\DataCollector\PrettyDataCollector;

class AppExtension extends \Twig_Extension
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var Connection
     */
    protected $mongoDBconnection;

    /**
     * @var PrettyDataCollector
     */
    protected $prettyDataCollector;

    protected $databaseQueries;

    /**
     * AppExtension constructor.
     * @param DocumentManager $documentManager
     * @param Connection $connection
     * @param PrettyDataCollector $prettyDataCollector
     */
    public function __construct(DocumentManager $documentManager,
                         Connection $connection,
                         PrettyDataCollector $prettyDataCollector)
    {
        $this->documentManager = $documentManager;
        $this->mongoDBconnection = $connection;
        $this->prettyDataCollector = $prettyDataCollector;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('request_time', [$this, 'requestTime'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('query_time', [$this, 'queryTime'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('query_count', [$this, 'queryCount'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('user_count', [$this, 'userCount'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('task_count', [$this, 'taskCount'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Returns application execution time
     *
     * @param int $decimals
     * @return string
     */
    public function requestTime($decimals = 0)
    {
        return number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])*1000, $decimals);
    }

    /**
     * Returns doctrine query execution time
     *
     * @param int $decimals
     * @return string
     */
    public function queryTime($decimals = 0)
    {
        $profile = $this->mongoDBconnection->selectCollection("todolist", "system.profile");
        $executionTimes = $profile->find(["millis" => ['$gte' => 0 ]])->limit(50)->sort(['ts' => -1])->toArray();
        $request_time_micro = (double) $_SERVER['REQUEST_TIME_FLOAT']*pow(10, 6);

        foreach ($executionTimes as $key => $executionTime) {
            if (doubleval($executionTime['ts']->sec.$executionTime['ts']->usec) <= doubleval($request_time_micro)) {
                unset($executionTimes[$key]);
            }
          //Set this to avoid counting councurent requests
            if (doubleval($executionTime['ts']->sec.$executionTime['ts']->usec) > doubleval($request_time_micro - microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])*1000) {
                unset($executionTimes[$key]);
            }
        }
        reset($executionTimes);

        $tmp = (array) $this->prettyDataCollector;
        $tmp = array_values($tmp);
        $databaseQueries = $tmp[0];

        if (count($executionTimes) < $databaseQueries) {
            return 0;
        } else {
            $executionTimeNs =
                (current($executionTimes)['ts']->sec.current($executionTimes)['ts']->usec)
                - (end($executionTimes)['ts']->sec.end($executionTimes)['ts']->usec);
        }
        $executionTimeMs = $executionTimeNs / pow(10, 3);
        $this->databaseQueries = count($executionTimes);
        return number_format($executionTimeMs, $decimals);
    }

    /**
     * Returns doctrine query count
     *
     * @return string
     */
    public function queryCount()
    {
        return $this->databaseQueries;
    }

    public function userCount()
    {
        $em = $this->documentManager;
        $user_count = $em->getRepository('AppBundle:User')->count();
        return $user_count;
    }

    public function taskCount()
    {
        $dm = $this->documentManager;
        $task_count = $dm->getRepository('AppBundle:Task')->count();
        return $task_count;
    }

    public function getName()
    {
        return 'app_extension';
    }
}
