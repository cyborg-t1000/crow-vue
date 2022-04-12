<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderProvDetail;

class OrderController extends Controller
{
    // orders
    public function detail(Request $request)
    {
        $params = [];

        $params['order'] = OrderProvDetail::where('OrderId', '=', $request->order_id)->
            firstOrFail();

        // Обновление. Первичный ключ указан OrderId. Автоинкремент оключен. формат строковый
        //$params['order']->SalesOpsNotes = 'Test order for Gus Reese';// Test order for Gus Reese
        //$params['order']->save();

        $params['breadcrumbs'] = [['link' => "orders", 'name' => "Orders"], ['name' => $params['order']->
            OrderId], ];

        return view('/content/orders/order', $params);
    }

    public function orders(Request $request)
    {
        $params = [];

        $params['sort'] = $request->sort ? $request->sort : 'OrderId';

        $query = OrderProvDetail::select(['order_prov_details.OrderId',
            'order_prov_details.IpEngStatus', 'order_prov_details.StatusCode',
            'order_prov_details.OrderCreatedDt', 'orderscheduledates.CustCutOverDt',
            'orderipeng.IPENGNotes', ]);
        $query->distinct('order_prov_details.OrderId');
        $query->leftJoin('orderipeng', 'order_prov_details.OrderId', '=',
            'orderipeng.OrderId');
        $query->leftJoin('orderscheduledates', 'order_prov_details.OrderId', '=',
            'orderscheduledates.OrderId');

        $query->orderBy(preg_replace("#[^a-zA-Z_]#", '', $params['sort']), stripos($params['sort'],
            '-') === 0 ? 'desc' : 'asc');

        if (is_array($request->search)) {
            foreach ($request->search as $key => $value) {
                $value = trim($value);

                if (empty($value) && $value != '0') {
                    continue;
                }

                switch ($key) {
                    case 'OrderId':
                    case 'IpEngStatus':
                    case 'StatusCode':
                    case 'OrderCreatedDt':
                        $query->where('order_prov_details.' . $key, '=', urldecode($value));
                        break;
                    case 'CustCutOverDt':
                        $query->where('orderscheduledates.' . $key, '=', urldecode($value));
                        break;
                    case 'IPENGNotes':
                        $query->where('orderipeng.' . $key, '=', urldecode($value));
                        break;
                }
            }
        }

        $params['orders'] = $query->paginate(22);
        $params['orders_count'] = $query->count();

        $params['breadcrumbs'] = [['link' => "orders", 'name' => "Orders"], ['name' =>
            "Index"]];

        return view('/content/orders/orders', $params);
    }
}
