<?php

namespace App\Repositories\Contracts;

interface ActivityRepositoryContract
{
    public function search($fields);

    public function getUsingMonthYear($month, $year, $showOnlyActiveStatus = true);

    public function getDailyUsingMonthYear($date, $showOnlyActiveStatus = true);

    public function changePosition($new_position);

    public function getFocusReport($month, $year);
}
