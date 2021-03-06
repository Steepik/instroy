<?php

namespace App\Http\Controllers;

use App\Assignment;
use App\Land;
use Illuminate\Http\Request;

class AjaxController extends Controller
{

    /**
     * Get assignment by land category id
     *
     * @param $landCategoryId
     * @param Request $request
     */
    public function getAssignment(Request $request)
    {
        if ($request->ajax())
            return Assignment::where('land_category_id', (int)$request->landCategoryId)
                ->orderBy('id', 'ASC')
                ->get()
                ->toJson();

        return abort(404);
    }

    public function getJsonDataMap(Request $request)
    {
        if ($request->ajax()) {
            return Land::getLandsForMap($request->region_id);
        } else {
            return abort(404);
        }
    }
}
