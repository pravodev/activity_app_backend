<?php

namespace App\Services\Contracts;

interface ActivityServiceContract {
    public function get();

    public function store($input);

    public function update($input, $id);

    public function delete($id);

    public function search($fields);

    public function getUsingMonthYear($month, $year, $showOnlyActiveStatus = true);

    public function getDailyUsingMonthYear($date, $showOnlyActiveStatus = true);

    public function changePosition($new_position);

    public function getFocusReport($month, $year);
}
