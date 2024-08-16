<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApiQueryFilter {

    /**
     * Model to be used
     * 
     */
    private $model = '';

    /**
     * Request from api
     */
    private $request;

    public function __construct($model, Request $request)
    {
        $this->model = $model;
        
        $this->request = $request;
    }

    /**
     * Search query filter
     * 
     * @param array $searchkeys
     * @return ;
     */
    public function search(array $searchkeys =  []) 
    {   
        $model = $this->model;
        $request = $this->request;

        if(!$request->searchkey) return $model;

        $qu = $model->when($request->searchkey, function($q) use ($request, $searchkeys) {
            foreach($searchkeys as $i=>$key) {

                $key2 = explode("|", $key);

                if(count($key2)) {
                    foreach($key2 as $k=>$key2_2) {
                        $q->orWhere($key2_2, 'like', '%' . $request->searchkey . '%');
                    }
                } else {
                    $q->orWhere($key, 'like', '%' . $request->searchkey . '%');
                }
            }
            
            return $q;
            
        });

        return $qu;
    }

    public function sortlist() 
    {
        $model = $this->model;
        $request = $this->request;

        $model->when($request->sortkey && $request->sortby, function($q) use ($request) {
            $key2 = explode("|", $request->by);

            if(count($key2)) {
                foreach($key2 as $k=>$key2_2) {
                    return $q->orderBy($key2_2, $request->sortkey);
                }
            } else {
                return $q->orderBy($request->by, $request->sortkey);
            }
        });


        return $model;
    }

    public function searchAndSort(array $searchkeys =  [])
    {
        $model = $this->model;
        $request = $this->request;

        $qu = $model->where(function($searchQuery) use ($searchkeys, $request) {
            return $searchQuery->when($request->searchkey, function($q) use ($request, $searchkeys) {
                foreach($searchkeys as $i=>$key) {
    
                    $key2 = explode("|", $key);
    
                    if(count($key2) > 1) {
                        foreach($key2 as $k=>$key2_2) {
                            ($i == 0) 
                            ? $q->where($key2_2, $request->searchkey)
                            : $q->orWhere($key2_2, 'like', '%' . $request->searchkey . '%');
                        }
                    } else {
                        
                        $relationSearch = explode('.', $key);
    
                        if(count($relationSearch) > 1) {
                            ($i == 0) 
                            ? $q->whereHas($relationSearch[0], function(Builder $relation) use ($i, $request, $relationSearch) {
                                $relation->where($relationSearch[1], $request->searchkey);
                                $relation->orWhere($relationSearch[1], 'like', '%' . $request->searchkey . '%');
                            })
                            : $q->orWhereHas($relationSearch[0], function(Builder $relation) use ($i, $request, $relationSearch) {
                                $relation->where($relationSearch[1], $request->searchkey);
                                $relation->orWhere($relationSearch[1], 'like', '%' . $request->searchkey . '%');
                            });
                                
                        } else {
                            ($i == 0) 
                            ? $q->where($key, $request->searchkey)
                            : $q->orWhere($key, 'like', '%' . $request->searchkey . '%');
                        }
                    }
                    
                }
                
                return $q;
                
            });
        });

        $qu->when($request->sortkey && $request->sortby, function($q) use ($request) {

            $qu2 = $q;

            $key2 = explode("|", $request->sortby);

            if(count($key2) > 1) {
                foreach($key2 as $k=>$key2_2) {
                    $qu2->orderBy($key2_2, $request->sortkey);
                }
            } else {

                $relationSort = explode('.', $request->sortby);

                if(count($relationSort) > 1) {
                    $qu2 = $q->with([$relationSort[0] => function(Builder $query) use ($relationSort, $request) {
                        $query->orderBy($relationSort[1], $request->sortkey);
                    }]);
                } else {
                    $qu2 = $q->orderBy($request->sortby, $request->sortkey);
                }
                
            }

            return $qu2;
        });

        return $qu;
    }
}