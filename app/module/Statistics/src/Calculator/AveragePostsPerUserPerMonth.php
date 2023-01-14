<?php

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

/**
 * Class AveragePostsPerUserPerMonth
 *
 * @package Statistics\Calculator
 */
class AveragePostsPerUserPerMonth extends AbstractCalculator
{

    protected const UNITS = 'posts';

    protected const PRECISION = 2;
    
    /**
     * @var array
     */
    private $totals = [];

    /**
     * @var array
     */
    private $users = [];

    /**
     * @param SocialPostTo $postTo
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getDate()->format('\M\o\n\t\h m, Y');
        if($postTo->getAuthorId() === null){
            return;
        }
        if (!isset($this->users[$key][$postTo->getAuthorId()])){
            $this->users[$key][$postTo->getAuthorId()]=true;
        }
        $this->totals[$key]=($this->totals[$key] ?? 0)+1;
    }

    /**
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();
        foreach ($this->totals as $splitPeriod => $total) {
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($splitPeriod)
                ->setValue(round($total/count($this->users[$splitPeriod]),self::PRECISION))
                ->setUnits(self::UNITS);
            $stats->addChild($child);
        }

        return $stats;
    }
}
