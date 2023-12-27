<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\PrintNode\src\Models\PrintJob;
use App\Modules\PrintNode\src\PrintNode;
use App\Modules\PrintNode\src\Resources\PrintJobResource;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;

/**
 * Class PrintOrderController.
 */
class PrintOrderController extends Controller
{
    /**
     * @throws Exception
     */
    public function update(Request $request, string $order_number, string $template): PrintJobResource
    {
        $pdfString = OrderService::getOrderPdf($order_number, $template);

        $printJob = new PrintJob();
        $printJob->printer_id = $request->user()->printer_id;
        $printJob->title = $template.'_'.$order_number.'_by_'.$request->user()->id;
        $printJob->pdf = base64_encode($pdfString);

        PrintNode::print($printJob);

        return PrintJobResource::make($printJob);
    }
}
