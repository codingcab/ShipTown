<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\SampleReport;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SampleReportController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $report = new SampleReport();

        return $report->response($request);
    }
}
