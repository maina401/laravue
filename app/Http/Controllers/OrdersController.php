<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrdersController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
  /*  function __construct()
    {
        $this->middleware('permission:order-list|order-create|order-edit|order-delete', ['only' => ['index','show']]);
        $this->middleware('permission:order-create', ['only' => ['create','store']]);
        $this->middleware('permission:order-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:order-delete', ['only' => ['destroy']]);
    }*/

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {

        $validated =request()->validate([
            'title' => 'required',
            'date' => 'required',
            'time' => 'required',
            'description' => 'required',
            'pages' => '',
            'amount' => '',
            'instructions' => '',
            'attachment' => '',
            'category' => 'required',
            'level' => '',
            'user_id' => '',
        ]);
        //TODO:Calculate Prices Dynamically
        $validated['amount']=isset($validated["pages"])?$validated["pages"]*10:0;
        \Log::info($validated['amount']);
        $validated['user_id']=$request->user()->id;
        \Log::info($validated);

        Order::create($validated);


    }

    /**
     * Display the specified resource.
     *
     * @param Order $order
     * @return Application|Factory|View
     */
    public function show(Order $order)
    {
        return view('orders.show',compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Order $order
     * @return Application|Factory|View
     */
    public function edit(Order $order)
    {
        return view('orders.edit',compact('order'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Order $order
     * @return RedirectResponse
     */
    public function update(Request $request, Order $order)
    {
        request()->validate([
            'name' => 'required',
            'detail' => 'required',
        ]);

        $order->update($request->all());

        return redirect()->route('orders.index')
            ->with('success','Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Order $order
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success','Product deleted successfully');
    }

    /**
     * Handle Order Uploads and attachments
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attach(Request $request): JsonResponse
    {
        $request->validate([
            'file'=>'required|mimes:jpeg,csv,doc,docx,zip,png,jpg,pdf'

        ]);
        $unique_id=Order::latest()->first()->id.'_'.uniqid();

        $fileName = 'ATT_'.$unique_id.'_'.time() . '.' . $request->file->getClientOriginalExtension();
        $path= $request->file->move(public_path('files/orders/attach'), $fileName);
        return response()->json([ 'filename'=>$fileName,
            'path'=>addslashes($path)]);
    }


}
