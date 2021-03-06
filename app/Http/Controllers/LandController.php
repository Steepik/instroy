<?php

namespace App\Http\Controllers;

use App\Category;
use App\Land;
use App\LandRegion;
use App\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Meta;

class LandController extends Controller
{
    public function __construct()
    {
        Meta::set('title', 'Список всех участков - Недвижимость в Белгороде');
        Meta::set('description', '"ООО Иистрой" - Недвижимость в Белгороде. Земля под строительство ИЖС, усадьбы, торговых объектов, кооммерческого назначения');
    }

    public function index($id)
    {
        $land = Land::findOrFail($id);

        $similarLands = Land::getSimilarLandFromCategory(3);

        #Local news
        $localNews = News::getRecentNews(1, 3, $land->locality_id);

        Meta::set('title', $land->name . ' - Недвижимость в Белгороде');

        return view('lands.single', compact('land', 'similarLands', 'localNews'));
    }

    public function show($slug, Request $request)
    {
        $lands = Land::getLandsListBySlug($slug);
        $catName = Land::getCategoryNameBySlug($slug);
        $catId = Land::getCatIdBySlug($slug);

        #For Filters
        $regions = LandRegion::orderBy('name', 'ASC')->get();

        $priceRange = DB::table('lands')
            ->select(DB::raw('MIN(price) as min, MAX(price) as max'))
            ->where('category_id', $catId)
            ->first();

        $similarLands = Land::getSimilarLandFromCategory(3);

        return view('lands.list-category', compact('lands', 'priceRange', 'similarLands', 'catName', 'regions', 'request', 'catId'));
    }

    public function filteredLands(Request $request)
    {
        $where = [];

        $region = (!is_null($request->region)) ? (int)$request->region : '' ;
        $locality = (!is_null($request->locality)) ? (int)$request->locality : '' ;
        $type = (!is_null($request->type)) ? (int)$request->type : '' ;
        $query = (!is_null($request->q)) ? $request->q : '' ;
        $landCategory = (!is_null($request->land_category)) ? $request->land_category : '';
        $area = (!is_null($request->area)) ? $request->area : '' ;
        $assignment = (!is_null($request->assignment)) ? $request->assignment : '' ;
        $catId = (!is_null($request->category)) ? $request->category : '' ;

        if (!empty($catId))
            $where['category_id'] = $catId;

        if (!empty($region))
            $where['region_id'] = $region;

        if (!empty($locality))
            $where['locality_id'] = $locality;

        if (!empty($type))
            $where['type_id'] = $type;

        if (!empty($landCategory))
            $where['land_category_id'] = $landCategory;

        if (!empty($area))
            $where['area'] = $area;

        if (!empty($assignment))
            $where['assignment_id'] = $assignment;

        //Do query
        $lands = Land::where($where);

        if(!empty($query))
            $lands->where('name', 'like', '%'.$query.'%');

        if ($request->has('price_sort')) {
            $lands->orderBy('price', $request->price_sort);
            $sortBy = $request->price_sort;
        }

        if ($request->has('price_from') && $request->has('price_to'))
            $lands->whereBetween('price', [$request->price_from, $request->price_to]);

        $lands = $lands->orderBy('created_at', 'desc')->paginate(10);

        //get min and max price
        $priceRange = DB::table('lands')
            ->select(DB::raw('MIN(price) as min, MAX(price) as max'))
            ->first();

        #For Filters
        $regions = LandRegion::orderBy('name', 'ASC')->get();

        $similarLands = Land::getSimilarLandFromCategory(3);

        return view('lands.list', compact('lands', 'priceRange', 'similarLands', 'sortBy', 'regions', 'request', 'catId'));
    }
}
