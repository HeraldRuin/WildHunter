<?php


namespace Modules\Order\Admin;


use Illuminate\Http\Request;
use Modules\AdminController;
use Modules\Order\Models\Order;
use Modules\Order\Resources\Admin\OrderResource;
use Modules\Order\Rules\ValidOrderItems;

class OrderController extends AdminController
{
    protected $orderClass;

    public function __construct(Order $order)
    {
        $this->setActiveMenu('order');
        $this->orderClass = $order;
    }

    public function index(Request $request)
    {
        $this->checkPermission('order_view');
        $query = $this->orderClass->query();
        $query->where('status', '!=', 'draft');
        if (!empty($request->input('s'))) {
            if (is_numeric($request->input('s'))) {
                $query->Where('id', '=', $request->input('s'));
            } else {
                $query->where(function ($query) use ($request) {
                    $query->where('first_name', 'like', '%' . $request->input('s') . '%')
                        ->orWhere('last_name', 'like', '%' . $request->input('s') . '%')
                        ->orWhere('email', 'like', '%' . $request->input('s') . '%')
                        ->orWhere('phone', 'like', '%' . $request->input('s') . '%');
                });
            }
        }

        $data = [
            'rows' => $query->orderBy('order_date', 'desc')->paginate(20),
            'page_title' => __("Manage Orders"),
            'statues' => $this->orderClass->statues()
        ];
        return view('Order::admin.order.index', $data);
    }

    public function create(Request $request)
    {
        $this->checkPermission('order_create');
        $data = [
            'order' => new Order(),
            'page_title' => __("Create Order"),
            'statues' => $this->orderClass->statues()
        ];
        return view('Order::admin.order.detail', $data);
    }

    public function edit(Request $request, Order $order)
    {
        $this->checkPermission('order_view');
        $data = [
            'order' => $order,
            'page_title' => __("Edit Order"),
            'statues' => $this->orderClass->statues()
        ];
        return view('Order::admin.order.detail', $data);
    }

    public function bulkEdit(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');
        if (empty($ids) or !is_array($ids)) {
            return redirect()->back()->with('error', __('No items selected!'));
        }
        if (empty($action)) {
            return redirect()->back()->with('error', __('Please select an action!'));
        }

        $allStatus = array_keys($this->orderClass->statues());

        switch ($action) {
            case "delete":
                foreach ($ids as $id) {
                    $query = $this->orderClass->where("id", $id);
                    if (!auth()->user()->hasPermission('order_manage_others')) {
                        $query->where("create_user", Auth::id());
                        $this->checkPermission('order_delete');
                    }
                    $query->delete();
                }
                return redirect()->back()->with('success', __('Deleted success!'));


            default:
                if (in_array($action, $allStatus)) {
                    foreach ($ids as $id) {
                        $order = $this->orderClass->find($id);
                        if($order){
                            $order->updateStatus($action);
                        }
                    }
                    return redirect()->back()->with('success', __('Updated status success!'));
                }
                return redirect()->back()->with('error', __('Invalid action!'));
            break;
        }
    }

    public function store(Request $request, Order $order = null)
    {

        if (!$this->hasPermission('order_manage_others')) {
            return $this->sendError(__("You are not allowed to do that"));
        }
        $rules = [
            'status' => 'required',
        ];
        if (!empty($order) and $order->isEditable()) {
            $rules = array_merge($rules, [
                'items.*.product_id' => 'required',
                'items.*.qty' => 'required|integer|gte:1',
                'items' => ['required', new ValidOrderItems()]
            ]);
        }
        $request->validate($rules);

        if (!$order) {
            $order = new $this->orderClass();
        }

        $data = [
            'customer_id' => $request->input('customer_id'),
            //            'status'=>$request->input('status'),
            'order_date' => $request->input('order_date'),
            'shipping_amount' => $request->input('shipping_amount'),
        ];
        if (!$order->isEditable()) {
            unset($data['shipping_amount']);
        }

        $order->fillByAttr(array_keys($data), $data);
        $order->updateStatus($request->input('status'));
        $order->save();

        $metas = [
            'billing' => $request->input('billing'),
            'shipping' => $request->input('shipping'),
            'shipping_method' => $request->input('shipping_method'),
        ];
        if (!$order->isEditable()) {
            unset($data['shipping_method']);
        }
        foreach ($metas as $k => $meta) {
            $order->addMeta($k, $meta);
        }

        if ($order->isEditable() and !empty($request->input('items'))) {
            $order->saveItems($request->input('items'));
            $order->saveTax($request->input('tax_lists'));
        }

        return $this->sendSuccess(
            [
                'data' => new OrderResource($order),
                'url' => route("order.admin.edit", ['order' => $order->id])
            ],
            __("Order saved")
        );
    }
}
