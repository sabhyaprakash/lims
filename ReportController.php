<?php


namespace App\Http\Controllers\Web\Admin\Logistics;

use App\Model\Logistics\Item;
use App\Model\Logistics\ItemCategory;
use App\Model\Logistics\Stock;
use App\Model\Logistics\StockBatch;
use App\Model\Logistics\SentTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Lab;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       
    }
    public function listofitems(Request $request )
    {   
        //dd($request->all());
		$Param1=this_lab()->id;
		if(!empty(\request('sent_to')))
		{
			$Param2='S';
			$Param3=\request('sent_to');
		}else{
			$Param2='A';
			//$Param3=999;
			$Param3=7;
			
		}	
        $item_list = DB::select('call lab_list_items(?,?,?)',array($Param1,$Param2,$Param3));
        //echo "<pre>"; print_r($item_list); die;
		$collection = new Collection($item_list);
		
		//search here
		if(!empty(\request('item_type_code')))
		{	
		$item_lists = $collection->where('Type',\request('item_type_code') );
		}		
		if(!empty(\request('code')))
		{	
		$item_lists = $collection->where('Code',\request('code') );
		}
		/*if(!empty(\request('description')))
		{	
		$item_lists = $collection->where('Item_Description',\request('description') );
		}*/
		if(!empty(\request('description')))
		{
			$value=\request('description');
			$item_lists = $collection->reject(function($element) use ($value) {
				  return mb_strpos(strtolower($element->Item_Description),strtolower($value)) === false;
			});
		}
		if(!empty(\request('sent_to')))
		{
		  $sent_to_id=\request('sent_to');	
          $sent_to_names = SentTo::select('name')->where('id',$sent_to_id)->get();
          //echo "<pre>";	print_r($sent_to_names[0]->name); die;
          $sent_to_name = $sent_to_names[0]->name;		  
		  $item_lists = $collection->where('Suppled_by',$sent_to_name);
		}	
		if(empty(\request('item_type_code')) && empty(\request('code')) && empty(\request('description'))&&empty(\request('sent_to')))
		{
			$item_lists = $collection;
		}    
       	
		//$sent_tos = SentTo::all()->where('flag',0);
		$sent_tos = SentTo::all()->where('rpttag',0);
		//echo "<pre>"; print_r($item_lists); die;
		
        return view(('admin.logistics.report.listofitems'),compact('item_lists','sent_tos'));
    }
	
	public function receiptregister(Request $request )
    {   
        $date = Carbon::now();// will get you the current date, time 
		$todaydate=$date->format("Y-m-d");
		$lab_id=this_lab()->id;
		$from_date=!empty(\request('from_date'))?date_format(date_create(\request('from_date')),'Y-m-d'):$todaydate;
		$to_date=!empty(\request('to_date'))?date_format(date_create(\request('to_date')),'Y-m-d'):$todaydate;
		$itemRadio=!empty(\request('itemRadio'))?\request('itemRadio'):'A';
		$authorityRadio=!empty(\request('authorityRadio'))?\request('authorityRadio'):'A';
		$sent_to=\request('sent_to');
		if($itemRadio=='A')
		{
			$Param_itemcode='All';
		}else{
			$Param_itemcode=\request('itemcode');
		}

        if($authorityRadio=='A')
		{
			$sent_to_id=999;
		}else{
			$sent_to_id=$sent_to;
		}		
		
        $rcpt_reg_item = DB::select('call lab_receipt_register (?, ?, ?, ?, ?, ?, ?)',array($lab_id,$from_date,$to_date,$itemRadio,$Param_itemcode,$authorityRadio,$sent_to_id));
        //echo "<pre>"; print_r($rcpt_reg_item); die;
		
		//$sent_tos = SentTo::all()->where('flag',0);
		$sent_tos = SentTo::all()->where('rpttag',0);
        return view(('admin.logistics.report.receiptregister'),compact('rcpt_reg_item','sent_tos','from_date','to_date'));
    }
	public function issueregister(Request $request )
    {  
    	$date = Carbon::now();// will get you the current date, time 
		$todaydate=$date->format("Y-m-d");
		$lab_id=this_lab()->id;
		$from_date=!empty(\request('from_date'))?date_format(date_create(\request('from_date')),'Y-m-d'):$todaydate;
		$to_date=!empty(\request('to_date'))?date_format(date_create(\request('to_date')),'Y-m-d'):$todaydate;
		$itemRadio=!empty(\request('itemRadio'))?\request('itemRadio'):'A';
		if($itemRadio=='A')
		{
			$Param_itemcode='All';
		}else{
			$Param_itemcode=\request('itemcode');
		}	
		
        $rcpt_reg_item = DB::select('call lab_issue_register (?, ?, ?, ?, ?)',array($lab_id,$from_date,$to_date,$itemRadio,$Param_itemcode));
        //echo "<pre>"; print_r($rcpt_reg_item); die;
		
        return view(('admin.logistics.report.issueregister'),compact('rcpt_reg_item','from_date','to_date'));
    }
	public function rejectedRequisition(Request $request )
    {   
        $date = Carbon::now();// will get you the current date, time 
		$todaydate=$date->format("Y-m-d");
		$lab_id=this_lab()->id;
		$from_date=!empty(\request('from_date'))?date_format(date_create(\request('from_date')),'Y-m-d'):$todaydate;
		$to_date=!empty(\request('to_date'))?date_format(date_create(\request('to_date')),'Y-m-d'):$todaydate;
		
		
        $rejected_item = DB::select('call lab_requisition_rejected (?, ?, ?)',array($lab_id,$from_date,$to_date));
        //echo "<pre>"; print_r($rejected_item); die;
		
		
        return view(('admin.logistics.report.rejectedrequisition'),compact('rejected_item','from_date','to_date'));
    }
	public function shortReceivedRegister(Request $request )
    {   
        $date = Carbon::now();// will get you the current date, time 
		$todaydate=$date->format("Y-m-d");
		$lab_id=this_lab()->id;
		$from_date=!empty(\request('from_date'))?date_format(date_create(\request('from_date')),'Y-m-d'):$todaydate;
		$to_date=!empty(\request('to_date'))?date_format(date_create(\request('to_date')),'Y-m-d'):$todaydate;
		$itemRadio=!empty(\request('itemRadio'))?\request('itemRadio'):'A';
		$authorityRadio=!empty(\request('authorityRadio'))?\request('authorityRadio'):'A';
		$sent_to=\request('sent_to');
		if($itemRadio=='A')
		{
			$Param_itemcode='All';
		}else{
			$Param_itemcode=\request('itemcode');
		}

        if($authorityRadio=='A')
		{
			$sent_to_id=999;
		}else{
			$sent_to_id=$sent_to;
		}		
		
        $shrt_rcvd_reg_item = DB::select('call lab_reject_register (?, ?, ?, ?, ?, ?, ?)',array($lab_id,$from_date,$to_date,$itemRadio,$Param_itemcode,$authorityRadio,$sent_to_id));
        //echo "<pre>"; print_r($shrt_rcvd_reg_item); die;
		
		//$sent_tos = SentTo::all()->where('flag',0);
		$sent_tos = SentTo::all()->where('shortrcvtag',0);
        return view(('admin.logistics.report.shortreceivedregister'),compact('shrt_rcvd_reg_item','sent_tos','from_date','to_date'));
    }
	public function shortReceivedItemDetails( Request $request ){
        //echo "<pre>"; print_r($request->all()); die;
        $this->validate($request, [
            'fromdate' => 'required',
			'todate' => 'required',
        ]);
		$fromdate=\request('fromdate');
		$todate=\request('todate');
	    $item_data = DB::select("SELECT DISTINCT A.item_code AS item_code,B.description AS item_description
				FROM 	t_itemrcpt_dtl A, t_itemrcpt_hdr C, m_item B
				WHERE 	B.code = A.item_code
				AND  	A.receipt_id = C.id
				AND     A.lab_id = C.lab_id
				AND 	C.lab_id = ".this_lab()->id);
	
        //echo "<pre>"; print_r($item_data); die;
        return $item_data;
		exit;

    }
	public function itemDetails( Request $request ){
	  //echo "<pre>"; print_r($request->all()); die;
	  $this->validate($request, [
            'fromdate' => 'required',
			'todate' => 'required',
        ]);
		$fromdate=\request('fromdate');
		$todate=\request('todate');
	    $item_data = DB::select("SELECT DISTINCT A.item_code AS item_code,B.description AS item_description
	    FROM 	t_itemrcpt_dtl A, t_itemrcpt_hdr C, m_item B 
WHERE 	C.lab_id = ".this_lab()->id." and B.code = A.item_code
GROUP BY item_code");
	
//	  echo "<pre>"; print_r($item_data); die;
	  //dd($item_data);
        return $item_data;
		exit;

    }
	public function issueItemDetails( Request $request ){
        //echo "<pre>"; print_r($request->all()); die;
        $this->validate($request, [
            'fromdate' => 'required',
			'todate' => 'required',
        ]);
		$fromdate=\request('fromdate');
		$todate=\request('todate');
	    $item_data = DB::select("SELECT DISTINCT A.item_code AS item_code,B.description AS item_description

		FROM 	t_stock_issue_item_dtl A, t_stock_issue_hdr C, m_item B
		WHERE 	B.code = A.item_code
		AND  	A.issue_no = C.issue_no
		AND 	C.issue_date BETWEEN '".$fromdate."' AND '".$todate."'
		AND 	C.lab_id = ".this_lab()->id."
		UNION
		SELECT DISTINCT A.item_code AS item_code,B.description AS item_description
		FROM 	t_stock_transfer_item_dtl A, t_stock_transfer_hdr C, m_item B
		WHERE 	B.code = A.item_code
		AND  	A.stocktransfer_no = C.stocktransfer_no
		AND 	C.stocktransfer_date BETWEEN '".$fromdate."' AND '".$todate."'
		AND 	A.from_lab_id =".this_lab()->id);
	
        //echo "<pre>"; print_r($item_data); die;
        return $item_data;
		exit;

    }
	public function stocksheet(Request $request )
    {  
        //dd($request->all());	
        $lab_id=this_lab()->id;
		$year=!empty(\request('year'))?\request('year'):0;
		//$year=\request('year');
		$period_type_id=!empty(\request('period_type_id'))?\request('period_type_id'):1;
		$rpt_period_tbl_id=\request('period');
		$period_val=0;
		$start_mm=0;
		$end_mm=0;
		if(!empty($rpt_period_tbl_id))
		{	
			$period = DB::select("SELECT * FROM rpt_period WHERE id=".$rpt_period_tbl_id);
			//echo "<pre>"; print_r($period); die;	
			$period_val=$period[0]->period;
			$start_mm=$period[0]->start_mm;
			$end_mm=$period[0]->end_mm;
		}
		//echo $lab_id.'--'.$year.'----'.$period_type_id.'--'.$period_val.'---'.$start_mm.'--'.$end_mm; die();
        $lab_stock_sheet_item = DB::select('call lab_stock_sheet (?, ?, ?, ?, ?,?)',array($lab_id,$year,$period_type_id,$period_val,$start_mm,$end_mm));
        //echo "<pre>"; print_r($lab_stock_sheet_item); die;
		
		
		$dropdown_year = DB::select("SELECT t_year FROM rpt_year ORDER BY t_year");
		//echo "<pre>"; print_r($dropdown_year); die;
		$type_of_period = DB::select("SELECT id, period_type FROM rpt_period_type ORDER BY id");
		//echo "<pre>"; print_r($type_of_period); die;
		
		$period = DB::select("SELECT id, period FROM rpt_period");
		//echo "<pre>"; print_r($period); die;
		
		$disp_type_of_period = DB::select("SELECT id, period_type FROM rpt_period_type  where id=".$period_type_id);
		//echo "<pre>"; print_r($disp_type_of_period); die;
		
        return view(('admin.logistics.report.stocksheet'),compact('dropdown_year','type_of_period','period','lab_stock_sheet_item','period_val','disp_type_of_period'));
    }
	public function getPeriod($period_id ){    
		$period = DB::select("SELECT id, period FROM rpt_period WHERE period_id=".$period_id);
		//echo "<pre>"; print_r($period); die;
        return $period;
		exit;

    }

}