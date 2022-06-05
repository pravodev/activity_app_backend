<?php

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\BaseRepositoryContract;

use DB;

class BaseRepositoryImplementation implements BaseRepositoryContract
{
    protected $builder; //this can be Eloquent\Builder object or Query\Builder object !!

    public function getModel()
    {
        return $this->builder;
    }

    public function setModel($builder)
    {
        $this->builder = $builder;
    }

    public function getAll()
    {
        return $this->builder->get();
    }

    public function allOrder($orderBy, $orderType)
    {

        return $this->builder->orderBy($orderBy, $orderType)->get();
    }

    public function find($id)
    {
        return $this->builder->find($id);
    }

    public function getOneWhere($column, $value, $with)
    {
        return $this->builder->with($with)->where($column, $value)->first();
    }

    public function getManyWhere($column, $value)
    {
        $tempStr = implode(',', $value);
        return $this->builder->whereIn($column, (array) $value)->orderByRaw(DB::raw("FIELD($column, $tempstr)"))->get();
    }

    public function countWhere($column, $value)
    {
        return $this->builder->where($column = '', $value)->count();
    }

    public function store(array $data)
    {
        $newData = $this->builder->create($data);
        return $newData->toArray();
    }

    public function update(array $data, $id)
    {
        $update = $this->builder->where('id', $id)->first();
        if($update) {
            $update->fill($data);
            $update->save();
            return $update;
        }
        else {
            //throw updateDataFailedException
        }
    }

    public function delete($id)
    {
        return $this->builder->find($id)->delete();
    }

    public function deleteWhere($column, $value)
    {
        return $this->builder->where($column, $value)->delete();
    }

    public function datatable()
    {
        return $this->builder->select();
    }

    public function datatableWith(array $with)
    {
        $this->builder = $this->builder->with($with);
        return $this;
    }

    public function orderBy($orderBy, $orderType) {

        $this->builder->orderBy($orderBy, $orderType);
        return $this;
    }

    public function get() {
        return $this->builder->get();
    }

    public function where($where) {
        $this->builder->where($where);
    }
}
