<?php

namespace App\Http\Controllers\Lead;

use Carbon\Carbon;
use App\Models\City;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use App\Models\Media;
use App\Models\State;
use App\Models\Country;
use App\Models\Industry;
use App\Models\Language;
use App\Models\Proposal;
use App\Models\Reminder;
use App\Models\FormField;
use App\Models\Leads\Lead;
use App\Models\ActivityLog;
use App\Imports\LeadsImport;
use Illuminate\Http\Request;
use App\Models\WebToLeadForm;
use App\Models\Address\Address;
use App\Models\SocialMediaField;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Contact\ContactTitle;
use Illuminate\Support\Facades\Auth;
use App\Models\Leads\Acvitity;
use SheetDB\SheetDB;

class LeadController extends Controller
{

    /**
     *  GET - /lead/import
     *  
     *  @return blade file
     */
    public function import()
    {
        $languages = Language::all();
        $industries = Industry::all();
        $route_active = 'manage_lead';
        return view('crm.lead.leads_import', compact(['route_active', 'languages', 'industries']));
    }
    
    public function googlesheet(){
         $route_active = 'Facebook Lead';
            $sheetdb = new SheetDB('mqp0v0qom09h6');
         $leads = $sheetdb->get();
    
         
         
         
         
        foreach ($leads as $sku) {
            
            $check=Lead::where('ad_id',$sku->id)->first();
            if(!empty($check)){
         
      
            }else{
                     $insertlead= new Lead();
                $insertlead->full_name=$sku->full_name ?? '';
      $insertlead->ad_name=$sku->ad_name ?? '';
      $insertlead->form_name=$sku->form_name ?? '';
      $insertlead->campaign_name=$sku->campaign_name ?? '';
      $insertlead->purpose=$sku->solar_panel_purpose ?? '';
      $insertlead->ad_id=$sku->id ?? '';
      $insertlead->phone=$sku->work_phone_number ?? '';
      $insertlead->lead_status='pending' ?? '';
    
                
            }
              $insertlead->save();
}

         
         $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
         $lead_owners = User::where(['role_id'=>'3'])->get();
              
         return view('crm.lead.sheet', 
         compact(['route_active', 'leads', 'won_leads','pending_leads','dead_leads','poor_leads','lead_owners','total_staff_leads']));

    }
    
       

    /**
     *  POST - /lead/import
     *  
     *  @param - lead fields
     *  
     *  @return - bulk import leads
     */
    public function importStore(Request $request){
        $file = $request->file('file')->store('import');
        $import = new LeadsImport;
        $import->import($file);
        if(count($import->errors()) == 0){
            $notification = array(
                'message' => $import->getRowCount().' leads imported successfully!',
                'alert-type' => 'success'
            );
            return back()->with($notification);
        }else{
            return back()->withErrors($import->errors());
        }
    }


    public function countStaffLeads(){
        // Count Total Leads, if current user is not admin
        $user = Auth::user();
        $user_id = $user->id;
        if($user->role->name != 'admin'){
            $query = Lead::query($user_id);
            if ($user->role->name != 'admin') {
                $query = $query->where(['owner_id'=>$user_id]);
            }
            $total_staff_leads = $query->count();
        }else{
            $total_staff_leads = null;
        }
        return $total_staff_leads;
    }

    public function count_pending_leads(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','pending');
        $query = $query->where(['is_dead'=>'no','is_poor_fit'=>'no']);
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $pending_leads = $query->orderBy('id','desc')->count();            
        return $pending_leads;
    }
    
    public function com_lead(){
           $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Won Lead');
        $query = $query->where(['is_dead'=>'no','is_poor_fit'=>'no']);
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $pending_leads = $query->orderBy('id','desc')->count();            
        return $pending_leads;
        
    }

  public function count_booking_leads(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Oder Booking');
        $query = $query->where(['is_dead'=>'no','is_poor_fit'=>'no']);
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $pending_leads = $query->orderBy('id','desc')->count();            
        return $pending_leads;
    }
    public function count_dead_leads(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Dead Lead');
       
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $dead_leads = $query->orderBy('id','desc')->count();
        return $dead_leads;
    }
    
       public function lost_dead_leads(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Lost Lead');
       
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $dead_leads = $query->orderBy('id','desc')->count();
        return $dead_leads;
    }
     public function follow_dead_leads(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Follow');
       
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $dead_leads = $query->orderBy('id','desc')->count();
        return $dead_leads;
    }


    public function count_poorfit_leads(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Hot lead');

        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $poor_leads = $query->count();
        return $poor_leads;
    }
    
     public function count_won_lead(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Won Lead');

        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $poor_leads = $query->count();
        return $poor_leads;
    }
    
      public function count_cold_lead(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Cold Lead');

        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $poor_leads = $query->count();
        return $poor_leads;
    }

  public function count_deadlead(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Dead Lead');

        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $poor_leads = $query->count();
        return $poor_leads;
    }
    
     public function count_lostlead(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Lost Lead');

        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $poor_leads = $query->count();
        return $poor_leads;
    }
    
     public function count_bookinglead(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Lost Lead');

        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $poor_leads = $query->count();
        return $poor_leads;
    }
    public function count_won_leads(){
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query =  $query->where('lead_status','Super Hot Lead');
       
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $won_leads = $query->count();
        return $won_leads;
    }

    public function wonLeads()
    {
        $route_active = 'Super Hot Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Super Hot Lead');
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->get();
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active', 'leads', 'won_leads','pending_leads','dead_leads','poor_leads','lead_owners','total_staff_leads','lead_ids']));
    }
    
    public function follow_lead()
    {
        $route_active = 'Super Hot Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('follow',1);
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->orderBy('id','desc')->get();
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active', 'leads', 'won_leads','pending_leads','dead_leads','poor_leads','lead_owners','total_staff_leads','lead_ids']));
    }
    
    public function followlead(){
        $route_active = 'Super Hot Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('follow',1);
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->get();
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active', 'leads', 'won_leads','pending_leads','dead_leads','poor_leads','lead_owners','total_staff_leads','lead_ids']));
        
    }
    
      public function won_Leads()
    {
        $route_active = 'Won Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Won Lead');
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->get();
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_lead();
         $cold_leads=$this->count_cold_lead();
        $won=$this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active', 'leads', 'won_leads','pending_leads','dead_leads','poor_leads','lead_owners','total_staff_leads','lead_ids']));
    }
    
    
    
     public function cold_Leads()
    {
        $route_active = 'Cold Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Cold Lead');
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->get();
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_lead();
         $cold_leads=$this->count_cold_lead();
        $won=$this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active', 'cold_leads','leads', 'won_leads','pending_leads','dead_leads','poor_leads','lead_owners','total_staff_leads','lead_ids']));
    }


  public function lost_Leads()
    {
        $route_active = 'Lost Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Lost Lead');
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->get();
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_lead();
         $cold_leads=$this->count_cold_lead();
        $won=$this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
          $lost_lead = $this->lost_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active', 'cold_leads','leads', 'won_leads','pending_leads','dead_leads','poor_leads','lead_owners','total_staff_leads','lead_ids','lost_lead']));
    }
    
    
      public function book_Leads()
    {
        $route_active = 'Lost Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Oder Booking');
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->get();
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_lead();
         $cold_leads=$this->count_cold_lead();
         $com_lead=$this->com_lead();
         $booking_lead=$this->count_booking_leads();
        $won=$this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active', 'cold_leads','leads','com_lead','booking_lead','won_leads','pending_leads','dead_leads','poor_leads','lead_owners','total_staff_leads','lead_ids']));
    }
    public function poorfitLeads()
    {
        $route_active = 'Hot Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
     
        $query =  $query->where('lead_status','Hot lead');
        
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->orderBy('id','desc')->get();
         
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $com_lead=$this->com_lead();
         $booking_lead=$this->count_booking_leads();
         $cold_leads=$this->count_cold_lead();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active','com_lead','leads','booking_lead','cold_leads','won_leads','dead_leads','poor_leads','pending_leads','lead_owners','total_staff_leads','lead_ids']));
    }
    
    
     public function totallead()
    {
        $route_active = 'Total Lead';
        $user = Auth::user();
        $user_id = $user->id;
        $query = Lead::query($user_id);
     
        $query =  $query;
        
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->orderBy('id','desc')->get();
         
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $com_lead=$this->com_lead();
        $lost_lead = $this->lost_dead_leads();
        $booking_lead=$this->count_booking_leads();
         $cold_leads=$this->count_cold_lead();
        $poor_leads = $this->count_poorfit_leads();
        $lead_owners = User::where(['role_id'=>'3'])->get();

        return view('crm.lead.index', compact(['route_active','booking_lead','com_lead','cold_leads','lost_lead', 'leads','won_leads','dead_leads','poor_leads','pending_leads','lead_owners','total_staff_leads','lead_ids']));
    }

    public function deadLeads()
    {
        $route_active = 'manage_lead';
        $user = Auth::user();
        $user_id = $user->id;

        $query = Lead::query($user_id);
        $query = $query->where('lead_status','Dead Lead');
        
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->orderBy('id','desc')->get();
        $lead_ids = response()->json($leads->modelKeys());
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
         $com_lead=$this->com_lead();
         $booking_lead=$this->count_booking_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
         $cold_leads=$this->count_cold_lead();
        $poor_leads = $this->count_poorfit_leads();

        $lead_owners = User::where(['role_id'=>'3'])->get();
        return view('crm.lead.index', compact(['route_active','com_lead','booking_lead','leads','cold_leads','won_leads','dead_leads','poor_leads','pending_leads','lead_owners','total_staff_leads','lead_ids']));
    }


    public function sort(Request $request)
    {
        $route_active = 'manage_lead';
        $user = Auth::user();
        $user_id = $user->id;
        $now = Carbon::now();

        $yesterday = Carbon::yesterday();
        $query = Lead::query($user_id);
        if ($request->sort_status_wise != null) {
            if($request->sort_status_wise == 'pending_leads'){
                $query = $query->where('lead_status_id','!=','1');
                $query = $query->where(['is_dead'=>'no','is_poor_fit'=>'no']);
            }elseif($request->sort_status_wise == 'won_leads'){
                $query = $query->where('lead_status_id','=', 1);
            }elseif($request->sort_status_wise == 'poorfit_leads'){
                $query = $query->where('lead_status_id','!=','1');
                $query = $query->where(['is_poor_fit'=>'yes']);
            }elseif($request->sort_status_wise == 'dead_leads'){
                $query = $query->where('lead_status_id','!=','1');
                $query = $query->where(['is_dead'=>'yes']);
            }
        }

        if ($request->date_sort != null) {
            if($request->date_sort == 'today')
            {
                $today = Carbon::today()->toDateString();
                $query = $query->whereDate('created_at','=',$today);
            }
            elseif($request->date_sort == 'yesterday'){
                $yesterday = Carbon::yesterday()->toDateString();
                $query = $query->whereDate('created_at','=',$yesterday);                
            }
            elseif($request->date_sort == 'this_week'){
                $weekStartDate = $now->startOfWeek()->format('Y-m-d H:i');
                $weekEndDate = $now->endOfWeek()->format('Y-m-d H:i');
                $query = $query->whereDate('created_at','>=',$weekStartDate);     
                $query = $query->whereDate('created_at','<=',$weekEndDate);     
            }  
            elseif($request->date_sort == 'last_week'){
                $subWeek = $now->subWeek();
                $lastWeekStartDate = $subWeek->startOfWeek()->format('Y-m-d H:i');
                $lastWeekEndDate = $subWeek->endOfWeek()->format('Y-m-d H:i');
                $query = $query->whereDate('created_at','>=',$lastWeekStartDate);     
                $query = $query->whereDate('created_at','<=',$lastWeekEndDate); 
            } 
            elseif($request->date_sort == 'this_month'){
                $startOfMonth = $now->startOfMonth()->toDateString();
                $endOfMonth = $now->endOfMonth()->toDateString();
                $query = $query->whereDate('created_at','>=',$startOfMonth);     
                $query = $query->whereDate('created_at','<=',$endOfMonth); 
            } 
            elseif($request->date_sort == 'last_month'){
                $subMonth = $now->subMonth();
                $startOfMonth = $subMonth->startOfMonth()->toDateString();
                $endOfMonth = $subMonth->endOfMonth()->toDateString();
                $query = $query->whereDate('created_at','>=',$startOfMonth);     
                $query = $query->whereDate('created_at','<=',$endOfMonth); 
            } 
            elseif($request->date_sort == 'last_3_months'){
                // For Last 3 months
                $lastOneMonth = $now->subMonth(1);
                $lastOneMonthEnd = $lastOneMonth->endOfMonth()->toDateString();
                $thirdLastMonth =  $now->subMonth(2);
                $thirdLastMonthStart = $thirdLastMonth->startOfMonth()->toDateString();

                $query = $query->whereDate('created_at','>=',$thirdLastMonthStart); 
                $query = $query->whereDate('created_at','<=',$lastOneMonthEnd); 
            } 
            elseif($request->date_sort == 'last_6_months'){
                // For Last 6(3+3) months
                $lastOneMonth = $now->subMonth(1);
                $lastOneMonthEnd = $lastOneMonth->endOfMonth()->toDateString();

                $Last6thMonth =  $now->subMonth(6);
                $Last6thMonthStart = $Last6thMonth->startOfMonth()->toDateString();

                $query = $query->whereDate('created_at','>=',$Last6thMonthStart);     
                $query = $query->whereDate('created_at','<=',$lastOneMonthEnd); 
            } 
            elseif($request->date_sort == 'this_year'){
                // This Year 1 Jan to 31 Dec
                $thisYearStart = $now->startOfYear(1)->toDateString();
                $thisYearEnd = $now->endOfYear(1)->toDateString();
                $query = $query->whereDate('created_at','>=',$thisYearStart);     
                $query = $query->whereDate('created_at','<=',$thisYearEnd); 
            } 
            elseif($request->date_sort == 'last_year'){
                // This Year 1 Jan to 31 Dec
                $lastYear = $now->subYear(1);
                $lastYearStart = $lastYear->startOfYear(1)->toDateString();
                $lastYearEnd = $lastYear->endOfYear(1)->toDateString();
                $query = $query->whereDate('created_at','>=',$lastYearStart);     
                $query = $query->whereDate('created_at','<=',$lastYearEnd); 
            } 
            elseif($request->date_sort == 'custom'){
                $dates = explode(' - ', $request->custom_range);
                $query = $query->whereDate('created_at','>=',$dates[0]);     
                $query = $query->whereDate('created_at','<=',$dates[1]); 
            } 
        }

        if($request->owner_id != null){
            $query = $query->where('owner_id','=',$request->owner_id);
        }

        $leads = $query->orderBy('id','desc')->get();
        $lead_ids = response()->json($leads->modelKeys());
        $lead_owners = User::where(['role_id'=>'3'])->get();
        
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();

        return view('crm.lead.index', compact(['route_active', 'leads', 'pending_leads','won_leads','dead_leads','poor_leads','lead_owners','request','lead_ids']));
    }
 

  public function index()
    {

 $sheetdb = new SheetDB('9rfny9u2rjxtm');
         $leads = $sheetdb->get();
 foreach ($leads as $sku) {
            
            $check=Lead::where('ad_id',$sku->id)->first();
            if(!empty($check)){
      }else{
        $insertlead= new Lead();
        $insertlead->full_name=$sku->full_name ?? '';
$insertlead->ad_name=$sku->ad_name ?? '';
 $insertlead->form_name=$sku->you_city ?? '';
$insertlead->campaign_name=$sku->your_monthly_electricity_bill ?? '';
$insertlead->purpose=$sku->solar_panel_purpose ?? '';
$insertlead->ad_id=$sku->id ?? '';
$insertlead->phone=$sku->work_phone_number ?? '';
$insertlead->lead_status='pending' ?? '';
$insertlead->save();
 }
              
}
        $route_active = 'manage_lead';
        $user = Auth::user();
        $user_id = $user->id;
        $lead_owners = User::where(['role_id'=>'3'])->get();
        $query = Lead::query($user_id);
        $query = $query->where('lead_status','pending');
        $query = $query->where(['is_dead'=>'no','is_poor_fit'=>'no']);
        if ($user->role->name != 'admin') {
            $query = $query->where(['owner_id'=>$user_id]);
        }
        $leads = $query->orderBy('id','desc')->get();

        // Lead IDs for delete lead button used in JS
        $lead_ids = response()->json($leads->modelKeys());
        
        $total_staff_leads = $this->countStaffLeads();
        $pending_leads = $this->count_pending_leads();
        $won_leads = $this->count_won_leads();
        $dead_leads = $this->count_dead_leads();
        $poor_leads = $this->count_poorfit_leads();
        return view('crm.lead.index', compact(['route_active', 'leads', 'pending_leads','won_leads','dead_leads','poor_leads','lead_owners','total_staff_leads','lead_ids']));
    }
    public function create()
    {
        $route_active = 'add_lead';
        $lead_titles = ContactTitle::get();
        $countries = DB::table('countries')->get();
        $languages = DB::table('languages')->get();
        $industries = DB::table('industries')->get();
        $users = User::whereIn('role_id',['2','3'])->where('status','active')->get();
        return view('crm.lead.create', compact(['route_active', 'lead_titles', 'countries','languages','industries','users']));
    }

    // API ROUTE
    public function getTitles(){
        $contactTitle = ContactTitle::select(['id','name'])->orderby('id','desc')->get();
        return response()->json(['contactTitle'=>$contactTitle]);
    }

    public function check_email_availability(Request $request){
        $check_email = Lead::where(['email'=>$request->email])->first();
        if ($check_email) {
            return response()->json([
                'status'=>'success',
                'message'=>'email_found'
            ]);
        }else{
            return response()->json([
                'status'=>'success',
                'message'=>'email_not_found'
            ]);
        }
    }

    public function getProposals(Lead $lead){
        $proposals = Proposal::where(['relation'=>'Lead','lead_id'=>$lead->id])->get();
        $proposal_ids = response()->json($proposals->modelKeys());
        $route_active = 'lead_proposal';
        return view('crm.lead.proposal', compact(['route_active', 'lead' ,'proposals','proposal_ids']));
    }

    public function getTasks(Lead $lead){
        $tasks = Task::where(['lead_id'=>$lead->id])->get();
        $route_active = 'lead_task';
        $salesperson = User::where('role_id','!=','1')->where('role_id','3')->where('status','active')->get();
        return view('crm.lead.task', compact(['route_active', 'lead' ,'tasks','salesperson']));
    }

    public function getMedia(Lead $lead){
        $media = Media::where(['lead_id'=>$lead->id])->get();
        // For buttons in js
        $media_names = [];
        foreach ($media as $item) {
            array_push($media_names, $item->file_path);
        }
        $media_names = json_encode($media_names);
        $media_ids = response()->json($media->modelKeys());
        $route_active = 'lead_media';
        return view('crm.lead.media', compact(['route_active', 'lead' ,'media','media_ids','media_names']));
    }

    public function getReminders(Lead $lead){
        $reminders = Reminder::where(['lead_id'=>$lead->id])->get();
        $reminder_ids = response()->json($reminders->modelKeys());
        $route_active = 'lead_reminder';
        $users = User::where('role_id','!=','1')->where('status','active')->get(); // all users except admin
        return view('crm.lead.reminder', compact(['route_active', 'lead','users','reminders','reminder_ids']));
    }

    public function webToLeadForm(Request $request, $token){
        $form = WebToLeadForm::where('token', $token)->first();
        if($form == null){
            return abort(403, 'Token corrupted, Unauthorized action.');
        }
        $validator = $request->validate([
            'last_name'=>'required',
        ]);
        if(!$validator){
            return back()->withErrors($validator)->withInput();
        }

        $lead = Lead::create([
            'owner_id'=> '1',
            'first_name'=> $request->first_name,
            'last_name'=> $request->last_name,
            'company_name'=> $request->company_name,
            'email'=> $request->email,
            'phone'=> $request->phone,
            'whatsapp'=> $request->whatsapp,
            'website'=> $request->website,
            'lead_status_id'=>'2'
        ]);

        $note = Note::create([
            "lead_id"=>$lead->id,
            'note'=> $request->note
        ]);        

        if($request->country != null){
            $country = Country::
            where('name', 'like', '%'.$request->country.'%')
            ->first();
        }else{
            $country = null;
        }
        if($request->state != null){
            $state = State::
            where('name', 'like', '%'.$request->state.'%')
            ->first();
        }else{
            $state = null;
        }
        if($request->city != null){
            $city = City::
            where('name', 'like', '%'.$request->city.'%')
            ->first();
        }else{
            $city = null;
        }

        $address = Address::create([
            "lead_id"=>$lead->id,
            'country_id'=>($country != null) ? $country->id : null,
            'state_id'=>($state != null) ? $state->id : null,
            'city_id'=>($city != null) ? $city->id : null,
            'zip'=>$request->zip,
        ]);

        $socialMediaField = SocialMediaField::create([
            "lead_id"=>$lead->id,
            'facebook'=>$request->facebook,
            'twitter'=>$request->twitter,
            'linkedin'=>$request->linkedin,
            'youtube'=>$request->youtube,
            'skype'=>$request->skype,
            'instagram'=>$request->instagram,
        ]);

        $url = $form->returnurl;
        return view('layouts.webform_success', compact('url'));   
    }

    public function store(Request $request)
    {
        
       $today= Carbon::now();
	       date_default_timezone_set('Asia/Kolkata');
$currentTime = date( 'h:i A', time () );
        
        
        $validator = $request->validate([
          
            'last_name'=>'required',
            
           
        ]);
        if(!$validator){
            return back()->withErrors($validator)->withInput();
        }
        $user = Auth::user();

        $lead = Lead::create([
            'lead_source_id'=> $request->lead_source_id,
            'lead_status_id'=>'',
            'user_id'=>$user->id ?? '',
            'time'=>$currentTime,
            'owner_id'=> $request->owner_id,
            'lead_temprature'=> $request->lead_temprature,
            'score'=> $request->score,
            'prospect_status'=> '',
            "title_id"=> 'Mr',
            'first_name'=> $request->first_name,
            'last_name'=> $request->last_name,
            'company_name'=> '',
            'email'=> $request->email,
            'email_opt_out'=> ($request->email_opt_out == 'yes')?'yes': 'no',
            'fax'=> '',
            'fax_opt_out'=> ($request->fax_opt_out == 'yes')?'yes': 'no',
            'phone'=> $request->phone,
            'phone_opt_out'=> ($request->phone_opt_out == 'yes')?'yes': 'no',
            'whatsapp'=> $request->whatsapp,
            'website'=> '',
            'note' =>$request->note ?? '',
            'plant' =>$request->browser ?? '',
  'lead_status' =>$request->lead_status_id ?? '',
            'language_id'=> '',
            'industry_id'=> '',
        ]);

        $note = Note::create([
            "lead_id"=>$lead->id,
            'note'=> $request->note
        ]);  
        
         $act= new Acvitity();
        
        $act->acvitiy= 'Lead Note';
       
       $act->remarks=  $request->note ?? '';
        $act->date=   '';
       $act->time=  '';
       $act->current_time=$currentTime ?? '';
        $act->lead_id= $lead->id ?? '';
        $act->user_id=$user->id ??  '';
       $act->save();
        
        
        $user=User::where('id',$request->owner_id)->first();
     $userid = Auth::user();
       $act= new Acvitity();
        $username="Assigned to" .''.$user->name;
        $act->acvitiy=$username ?? '';
       
       $act->remarks= $request->remark ?? '';
        $act->date=   '';
       $act->time=  '';
       $act->current_time=$currentTime ?? '';
        $act->lead_id= $lead->id ?? '';
        $act->user_id=$userid->id ??  '';
       $act->save();

        $address = Address::create([
            "lead_id"=>$lead->id,
            "address_line_1" => $request->address_line_1,
            "address_line_2" => $request->address_line_2,
            "country_id" => $request->country_id,
            "state_id" => $request->state_id,
            "city_id" => $request->city_id,
            "zip" => $request->zip,
            "phone_1" => $request->phone_1,
            "phone_2" => $request->phone_2,
            "email_1" => $request->email_1,
            "email_2" => $request->email_2,
            "is_billing_address" => ($request->is_billing_address == 'yes')?'yes': 'no',
            "is_shipping_address" => ($request->is_shipping_address == 'yes')?'yes': 'no',
        ]);

        $socialMediaField = SocialMediaField::create([
            "lead_id"=>$lead->id,
            "linkedin" => $request->linkedin,
            "facebook" => $request->facebook,
            "twitter" => $request->twitter,
            "skype" => $request->skype,
            "instagram" => $request->instagram,
            "youtube" => $request->youtube,
            "tumblr" => $request->tumblr,
            "snapchat" => $request->snapchat,
            "reddit" => $request->reddit,
            "pinterest" => $request->pinterest,
            "telegram" => $request->telegram,
            "vimeo" => $request->vimeo,
            "patreon" => $request->patreon,
            "flickr" => $request->flickr,
            "discord" => $request->discord,
            "tiktok" => $request->tiktok,
            "vine" => $request->vine
        ]);

        ActivityLog::create([
           'lead_id'=>$lead->id,
           'user_id'=>$user->id, 
           'action'=>'create',
           'model'=>'Lead',
           'model_id'=>$lead->id,
           'field'=>'all',
           'message'=>'New Lead Created'
        ]);    

        ActivityLog::create([
            'lead_id'=>$lead->id,
            'user_id'=>$user->id, 
            'action'=>'create',
            'model'=>'Address',
            'model_id'=>$address->id,
            'field'=>'all',
            'message'=>'New Address Created and attached to the lead'
         ]); 
         
         ActivityLog::create([
            'lead_id'=>$lead->id,
            'user_id'=>$user->id, 
            'action'=>'create',
            'model'=>'SocialMediaField',
            'model_id'=>$socialMediaField->id,
            'field'=>'all',
            'message'=>'New socialMediaField Created and attached to the lead'
         ]);  

        if($lead){
            $notification = array(
                'message' => 'lead added successfully!',
                'alert-type' => 'success'
            );
            return back()->with($notification);
        }else{
            $notification = array(
                'message' => 'Please try again!',
                'alert-type' => 'error'
            );
            return back()->with($notification)->withInput();
        }
    }


   public function show(Lead $lead)
{
    $route_active = 'show_lead';
    $lead_titles = ContactTitle::get();
    $countries = DB::table('countries')->get();
    $address = Address::where(['lead_id' => $lead->id])->first();
    $SocialMediaField = SocialMediaField::where(['lead_id' => $lead->id])->first();
    $states = [];
    $cities = [];
    
    // Check if $lead->address exists and is not null
    if ($lead->address) {
        // If $lead->address exists
        // Fetch states based on the country_id from $lead->address
        $states = DB::table('states')->where('country_id', $lead->address->country_id)->get();
        
        // Check if $lead->address->state_id is not null
        if ($lead->address->state_id) {
            // If $lead->address->state_id is not null
            // Fetch cities based on the state_id from $lead->address
            $cities = DB::table('cities')->where('state_id', $lead->address->state_id)->get();
        }
    }
    
    $languages = DB::table('languages')->get();
    $industries = DB::table('industries')->get();
    $users = User::where('role_id', '3')->where('status', 'active')->get();

    return view('crm.lead.show', compact('route_active', 'lead', 'lead_titles', 'countries', 'states', 'cities', 'languages', 'industries', 'address', 'SocialMediaField', 'users'));
}



    public function edit(Lead $lead)
    {
    }

    public function update(Request $request, Lead $lead)
    {
        // if($lead->leadStatus->name == 'Won'){
        //     $notification = array(
        //         'message' => "You can't update this lead! This is already a customer.",
        //         'alert-type' => 'error'
        //     );
        //     return back()->with($notification)->withInput();
        // }
        $validator = $request->validate([
            
            'last_name'=>'required',
            'lead_source_id'=>'required',
            'lead_status_id'=>'required',
        ]);
        if(!$validator){
            return back()->withErrors($validator)->withInput();
        }
        $address = Address::where(['lead_id'=>$lead->id])->first();
        $SocialMediaField = SocialMediaField::where(['lead_id'=>$lead->id])->first();
        
    

        $lead->lead_source_id = $request->lead_source_id;
        $lead->lead_status_id = $request->lead_status_id;
        $lead->first_name = $request->first_name;
        $lead->last_name = $request->last_name;
        $lead->plant=$request->browser ?? '';
        $lead->industry_id = 0;
        $lead->language_id ='';
        $lead->website = '';
        $lead->whatsapp = $request->whatsapp;
        $lead->phone_opt_out = ($request->phone_opt_out == 'yes')?'yes':'no';
        $lead->phone = $request->phone;
        $lead->fax_opt_out = ($request->fax_opt_out == 'yes')?'yes':'no';
        $lead->fax = '';
        $lead->email_opt_out = ($request->email_opt_out == 'yes')?'yes':'no';
        $lead->email = $request->email;
        $lead->company_name = '';
        
        $lead->title_id = $request->title_id;
        $lead->prospect_status ='';
        $lead->score = '';
        $lead->lead_temprature = '';
        
        

        $lead->save();
        $user = Auth::user();
        ActivityLog::create([
            'lead_id'=>$lead->id,
            'user_id'=>$user->id, 
            'action'=>'update',
            'model'=>'Lead',
            'model_id'=>$lead->id,
            'field'=>'all',
            'message'=>'Lead Updated'
         ]);  

        if ($address) {
    // If $address exists, assign properties to it
    $address->address_line_1 = $request->address_line_1;
    // Assign other properties similarly
} else {
    // If $address is null, you might want to handle this situation
    // For example, you could create a new Address object and assign properties to it
    $address = new Address();
    $address->address_line_1 = $request->address_line_1;
    // Assign other properties similarly
}
        $address->address_line_2 = $request->address_line_2;
        $address->country_id = $request->country_id;
        $address->state_id = $request->state_id;
        $address->city_id = $request->city_id;
        $address->zip = $request->zip;
        $address->phone_1 = $request->phone_1;
        $address->phone_2 = $request->phone_2;
        $address->email_1 = $request->email_1;
        $address->email_2 = $request->email_2;
        $address->is_billing_address = ($request->is_billing_address == 'yes')?'yes': 'no';
        $address->is_shipping_address = ($request->is_shipping_address == 'yes')?'yes': 'no';
        $address->save();


        if ($SocialMediaField) {
    // If $SocialMediaField exists, assign properties to it
    $SocialMediaField->linkedin = $request->linkedin;
    // Assign other properties similarly
} else {
    // If $SocialMediaField is null, you might want to handle this situation
    // For example, you could create a new SocialMediaField object and assign properties to it
    $SocialMediaField = new SocialMediaField();
    $SocialMediaField->linkedin = $request->linkedin;
    // Assign other properties similarly
}
        $SocialMediaField->facebook = $request->facebook;
        $SocialMediaField->twitter = $request->twitter;
        $SocialMediaField->skype = $request->skype;
        $SocialMediaField->instagram = $request->instagram;
        $SocialMediaField->youtube = $request->youtube;
        $SocialMediaField->tumblr = $request->tumblr;
        $SocialMediaField->snapchat = $request->snapchat;
        $SocialMediaField->reddit = $request->reddit;
        $SocialMediaField->pinterest = $request->pinterest;
        $SocialMediaField->telegram = $request->telegram;
        $SocialMediaField->vimeo = $request->vimeo;
        $SocialMediaField->patreon = $request->patreon;
        $SocialMediaField->flickr = $request->flickr;
        $SocialMediaField->discord = $request->discord;
        $SocialMediaField->tiktok = $request->tiktok;
        $SocialMediaField->vine = $request->vine;
        $SocialMediaField->save();

        if($lead->save()){
            $notification = array(
                'message' => 'Lead updated successfully!',
                'alert-type' => 'success'
            );
            return back()->with($notification)->withInput();
        }else{
            $notification = array(
                'message' => 'Please try again!',
                'alert-type' => 'error'
            );
            return back()->with($notification)->withInput();
        }

    }

    public function markAsDead(Request $request, Lead $lead){
        if ($lead->lead_status_id == '1') {
            $notification = array(
                'message' => 'This lead is already a customer!',
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }
        $lead->is_dead = 'yes';
        $lead->is_poor_fit = 'no';
        $lead->save();

        $user = Auth::user();
        ActivityLog::create([
            'lead_id'=>$lead->id,
            'user_id'=>$user->id, 
            'action'=>'update',
            'model'=>'Lead',
            'model_id'=>$lead->id,
            'field'=>'markAsDead',
            'message'=>'Lead Updated'
         ]);  

        if($lead->save()){
            $notification = array(
                'message' => 'Lead updated successfully!',
                'alert-type' => 'success'
            );
            return redirect(url('lead'))->with($notification)->withInput();
        }else{
            $notification = array(
                'message' => 'Please try again!',
                'alert-type' => 'error'
            );
            return back()->with($notification)->withInput();
        }
    }

    public function normalizeLead(Request $request, Lead $lead){
        if ($lead->lead_status_id == '1') {
            $notification = array(
                'message' => 'This lead is already a customer!',
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }
        $lead->is_dead = 'no';
        $lead->is_poor_fit = 'no';
        $lead->save();

        if($lead->save()){
            $notification = array(
                'message' => 'Lead updated successfully!',
                'alert-type' => 'success'
            );
            return redirect(url('lead'))->with($notification)->withInput();
        }else{
            $notification = array(
                'message' => 'Please try again!',
                'alert-type' => 'error'
            );
            return back()->with($notification)->withInput();
        }
    }

    public function markAsPoorfit(Request $request, Lead $lead){
        if ($lead->lead_status_id == '1') {
            $notification = array(
                'message' => 'This lead is already a customer!',
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }
        $lead->is_dead = 'no';
        $lead->is_poor_fit = 'yes';
        $lead->save();

        $user = Auth::user();
        ActivityLog::create([
            'lead_id'=>$lead->id,
            'user_id'=>$user->id, 
            'action'=>'update',
            'model'=>'Lead',
            'model_id'=>$lead->id,
            'field'=>'markAsDead',
            'message'=>'Lead Updated'
         ]);  

        if($lead->save()){
            $notification = array(
                'message' => 'Lead updated successfully!',
                'alert-type' => 'success'
            );
            return redirect(url('lead'))->with($notification)->withInput();
        }else{
            $notification = array(
                'message' => 'Please try again!',
                'alert-type' => 'error'
            );
            return back()->with($notification)->withInput();
        }
    }

    public function destroy(Lead $lead)
    {
        Address::where(['lead_id'=>$lead->id])->delete();
        SocialMediaField::where(['lead_id'=>$lead->id])->delete();
        if($lead->delete()){
            $notification = [
                'alert-type'=>'success',
                'message'=>'Lead, address and social media fields deleted successfully!'
            ];
            return back()->with($notification);
        }
    }
    
    
    public function interest(Request $request){
        $id=$request->id;
            
        return view('crm.lead.popup.interest',compact('id'));
        
    }
      public function follow(Request $request){
        $id=$request->id;
            
        return view('crm.lead.popup.follow',compact('id'));
        
    }
    
     public function pickupstatus(Request $request){
        $id=$request->id;
            
        return view('crm.lead.popup.pickupstatus',compact('id'));
        
    }
    public function leadstatus2(Request $request){
          $today= Carbon::now();
	       date_default_timezone_set('Asia/Kolkata');
$currentTime = date( 'h:i A', time () );
        $user = Auth::user();
       $act= new Acvitity();
       $act->acvitiy= $request->update ?? '';
        $act->remarks= $request->remark ?? '';
    
        $act->lead_id= $request->id ?? '';
        $act->user_id= $user->id ?? '';
            $act->current_time=$currentTime ?? '';
        
      $act->save();
       return back();
    }
    
    
    
    public function leadtracking(Request $request){
        $id=$request->id;
        $tracking=Acvitity::where('lead_id',$id)->orderBy('id','desc')->get();
    
        return view('crm.lead.popup.tracking',compact('tracking'));
    }
    
    public function leadstatus1(Request $request){
          $today= Carbon::now();
	       date_default_timezone_set('Asia/Kolkata');
$currentTime = date( 'h:i A', time () );
        $user = Auth::user();
       $act= new Acvitity();
       $act->acvitiy= $request->vehicle3 ?? '';
       $act->remarks= $request->remark ?? '';
        $act->lead_id= $request->id ?? '';
        $act->user_id= $user->id ?? '';
         $act->current_time=$currentTime ?? '';
       $act->save();
       return back();
    }
    
    public function leadstatus4(Request $request){
          $today= Carbon::now();
	       date_default_timezone_set('Asia/Kolkata');
$currentTime = date( 'h:i A', time () );

$update=Lead::find($request->id);
$update->follow=1;
$update->save();
        $user = Auth::user();
       $act= new Acvitity();
       $act->acvitiy= 'Follow Up' ?? '';
       $act->remarks= $request->remark ?? '';
        $act->date= $request->date ?? '';
       $act->time= $request->time ?? '';
        $act->lead_id= $request->id ?? '';
        $act->user_id= $user->id ?? '';
         $act->current_time=$currentTime ?? '';
       $act->save();
       return back();
    }
    
    public function statusupdate(Request $request){
         $today= Carbon::now();
	       date_default_timezone_set('Asia/Kolkata');
$currentTime = date( 'h:i A', time () );
        
        $update=Lead::find($request->id);
        
     
        $update->lead_status=$request->update ?? '';
        $update->save();
       $user = Auth::user();
       $act= new Acvitity();
       $act->acvitiy= 'Status Update' ?? '';
       $act->remarks= $request->update ?? '';
        $act->lead_id= $request->id ?? '';
        $act->user_id= $user->id ?? '';
          $act->current_time=$currentTime ?? '';
       $act->save();  
       return back()->with('success','Status Update Successfully');
        
        
    }
    
    
    public function lead_assign(Request $request){
        
        $id=$request->id;
        
          
             $tracking = User::whereIn('role_id',['2','3'])->where('status','active')->get();
    
        return view('crm.lead.popup.assign',compact('tracking','id'));
        
        
    }
    
    
     
    public function remarkupdate(Request $request){
        
        $id=$request->id;
        
     
    
        return view('crm.lead.popup.remarks',compact('id'));
        
        
    }
    
    public function updatestatus(Request $request){
        $id=$request->id;
      return view('crm.lead.popup.leadupdate',compact('id'));
        
    }
    
    public function statusassign(Request $request){
       
         $today= Carbon::now();
	       date_default_timezone_set('Asia/Kolkata');
            $currentTime = date( 'h:i A', time () );
        
              $update=Lead::find($request->id);
        
        $update->owner_id=$request->ownerid ?? '';
        $update->save();
         $user=User::where('id',$request->ownerid)->first();
         
         
     
         $act= new Acvitity();
        $username="Assigned to"  .$user->name;
        $act->acvitiy=$username ?? '';
        $act->remarks=  '';
        $act->date=   '';
        $act->time=  '';
        $act->current_time=$currentTime ?? '';
        $act->lead_id= $request->id ?? '';
        $act->user_id= Auth::user()->id ?? '';
       $act->save(); 
      return back()->with('success','Lead Assign  Successfully');
        }
        
        
        public function remarks_update(Request $request){
             $user = Auth::user();
              $today= Carbon::now();
	       date_default_timezone_set('Asia/Kolkata');
            $currentTime = date( 'h:i A', time () );
              $act= new Acvitity();
        $username="Remark";
        $act->acvitiy=$username ?? '';
        $act->remarks=$request->remark ?? '';
        $act->date=   '';
        $act->time=  '';
        $act->current_time=$currentTime ?? '';
        $act->lead_id= $request->id ?? '';
        $act->user_id= $user->id ?? '';
       $act->save(); 
      return back()->with('success','Lead Assign  Successfully');
            
            
        }
}
