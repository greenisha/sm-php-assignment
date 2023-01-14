<?php

declare(strict_types = 1);

namespace Tests\unit;

use DateTime;
use PHPUnit\Framework\TestCase;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Calculator\AveragePostsPerUserPerMonth;
use Statistics\Dto\ParamsTo;
use Statistics\Dto\StatisticsTo;

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

/**
 * Class AveragePostsPerUserPerMonthTest
 *
 * @package Tests\unit
 */
class AveragePostsPerUserPerMonthTest extends TestCase
{
    /**
     * Check if works
     */
    public function testDefault(): void
    {
        $data = $this->prepareData('/srv/sm_assignment/tests/data/social-posts-response.json', new DateTime('2018-01-01'), new DateTime('2022-01-01'));
        $value = $data->getChildren()[0];
        assertEquals(1,$value->getValue());
    }
    /**
     * Check if dates in params is used to filter data
     */
    public function testWrongDates(): void
    {
        $data = $this->prepareData('/srv/sm_assignment/tests/data/social-posts-response.json', new DateTime('2022-01-01'), new DateTime('2022-01-01'));
        assertEmpty($data->getChildren());
    }
    /**
     * Check same month in different years
     */
    public function testDifferentYears():void
    {
        $data = $this->prepareData('/srv/sm_assignment/tests/data/social-posts-response-different-years.json', new DateTime('2018-01-01'), new DateTime('2022-01-01'));
        assertEquals(1, $data->getChildren()[0]->getValue());
        assertEquals(2, $data->getChildren()[1]->getValue());               
    }
    /**
     * Check precision and rounding
     */
    public function testPrecision(): void
    {
        $data = $this->prepareData('/srv/sm_assignment/tests/data/social-posts-response-precision.json', new DateTime('2018-01-01'), new DateTime('2022-01-01'));
        assertEquals(1.33, $data->getChildren()[0]->getValue());
    }
    /**
     * Empty data- empty statistics
     */
    public function testNoData(): void
    {
        $data = $this->prepareData('/srv/sm_assignment/tests/data/social-posts-response-empty.json', new DateTime('2018-01-01'), new DateTime('2022-01-01'));
        assertEmpty($data->getChildren());
    }
    /**
     * If some posts don't have user id - they won't count
     */
    public function testNoUserId(): void
    {
        $data = $this->prepareData('/srv/sm_assignment/tests/data/social-posts-response-no-user-id.json', new DateTime('2018-01-01'), new DateTime('2022-01-01'));
        assertEquals(1, $data->getChildren()[0]->getValue());
       
    }
    /**
     * Gets data from file and processes
     * 
     * @param string    $path Path to input file with test data
     * @param DateTime  $from Start date in params 
     * @param DateTime  $to End date in params
     * @return StatisticsTo 
     */
    public function prepareData(string $path, DateTime $from, DateTime $to):StatisticsTo
    {
        $replyRaw = json_decode(file_get_contents($path), true);
        $hydrator = new FictionalPostHydrator();
        $testClass = new AveragePostsPerUserPerMonth();
        $paramsTo = new ParamsTo();
        $paramsTo->setStartDate($from);
        $paramsTo->setEndDate($to);
        $paramsTo->setStatName('test');
        $testClass->setParameters($paramsTo);
        foreach ($replyRaw['data']['posts'] as $value) {
            $testClass->accumulateData($hydrator->hydrate($value));
        }
        return $testClass->calculate();    
    }
}
