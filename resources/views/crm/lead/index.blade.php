@extends('crm.layouts.app')

@section('styles')
  <link rel="stylesheet" href="{{asset('theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('theme/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endsection

<style>
    .btn-outline--success {
    color: #28c76f;
    border-color: #28c76f;
}
</style>

@section('content')
  <div class="content-wrapper">
    @include('crm.layouts.breadcrumb')

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-12">
                      
                    
                    
                    <!--</a> -->
                    
                     
                    <!--  <a class="btn btn-default" href="{{url('/lead/googlelead')}}">{{__('Fb Lead')}}-->
                    <!--  <span class="badge badge-light bg-info ml-1"> </span>-->
                    <!--</a> -->
                 
                   
                    <a class="btn btn-default" href="{{url('/lead/list')}}">{{__('Pending Leads')}}
                      <span class="badge badge-light bg-info ml-1"> {{@$pending_leads ?? '0'}}</span>
                    </a>  
                    <a class="btn btn-default" href="{{url('lead/super')}}">{{__('Super Hot Lead')}} 
                        <span class="badge badge-light bg-success ml-1"> {{@$won_leads ?? '0'}}</span>
                      </a>
                      <a class="btn btn-default" href="{{url('lead/hot')}}">{{__('Hot lead')}} 
                        <span class="badge badge-light bg-primary ml-1"> {{@$poor_leads ?? '0'}}</span>
                      </a>
                      <a class="btn btn-default" href="{{url('lead/won_lead')}}">{{__('Won Lead')}} 
                        <span class="badge badge-light bg-danger ml-1"> {{@$com_lead ?? '0'}}</span>
                      </a>
                      
                       <a class="btn btn-default" href="{{url('lead/cold_lead')}}">{{__('Cold Lead')}} 
                        <span class="badge badge-light bg-danger ml-1"> {{@$cold_leads ?? '0'}}</span>
                      </a>
                      
                        <a class="btn btn-default" href="{{url('lead/dead')}}">{{__('Dead Lead')}} 
                        <span class="badge badge-light bg-primary ml-1"> {{@$dead_leads ?? '0'}}</span>
                      </a>
                      
                       <a class="btn btn-default" href="{{url('/lead/lost')}}">{{__('Lost Lead')}} 
                        <span class="badge badge-light bg-primary ml-1"> {{@$lost_lead ?? '0'}}</span>
                      </a>
                       <br><br>
                       <a class="btn btn-default" href="{{url('/lead/booking')}}">{{__('Oder Booking')}} 
                        <span class="badge badge-light bg-primary ml-1"> {{@$booking_lead ?? '0'}}</span>
                      </a>
                     
                       <a class="btn btn-default" href="{{url('lead/leadfollow')}}" >{{__('Follow Lead')}} 
                        <!--<span class="badge badge-light bg-primary ml-1"> {{@$poor_leads ?? '0'}}</span>-->
                      </a>
                      <a>
                           <a class="btn btn-default" href="{{url('/lead/totallead')}}">{{__('Total Leads')}}
                    <span class="badge badge-light bg-info ml-1">
                        @if (Auth::user()->role->name == 'admin')
                          {{session('total_leads')}}
                        @else 
                          {{@$total_staff_leads ?? '0'}}
                        @endif
                      </span>
                      </a>
                      
                      
                       
                  </div>
                  @if (Auth::user()->role->name == 'admin')
                  
                  @endif
                </div>
                

              </div>
            </div>
          </div>
          @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
          <div class="col-md-12">
            <div class="card card-secondary">
                <div class="card-header">
                  <h3 class="card-title">{{$route_active ?? ''}}</h3>
                  <a type="button" class="btn btn-sm btn-primary float-right" href="{{url('lead/create')}}">{{__('New Lead')}}</a>
                <a type="button" class="btn btn-sm btn-info float-right mr-2" href="{{url('lead/import')}}">
                    <i class="fas fa-cloud-upload-alt mr-1"></i>
                    {{__('Import Bulk Leads ')}}
                  </a> 
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                  <div class="table-responsive">
              <table id="leadsTable" class="table table-bordered table-striped display">
    <thead>
        <tr>
            <!--<th>{{__('S.No')}}</th>-->
            <th style="width: 22%">{{__('Lead')}}</th>
            <th style="width: 22%">{{__('Activity')}}</th>
            <!--<th style="width: 15%">{{__('Category')}}</th>-->
            <th style="width: 15%">{{__('KW Plant')}}</th>
            <!--<th style="width: 10%">Source</th>-->
            <!--<th style="width: 10%">{{__('History')}}</th>-->
            <!--<th style="width: 10%">{{__('Detail')}}</th>-->
            <th style="width: 20%">{{__('Lead Assign')}}</th>
            <th>{{__('Actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $leadCount = 0;
        @endphp

        @foreach ($leads as $lead)
            @php
                $leadCount++;
            @endphp
            <tr>
                <td style="font-size: 13px;">
                    @if(!empty($lead->first_name))
                        <b>Name-</b> {{$lead->first_name }} {{$lead->last_name}}<br>
                    @else
                        <b>Name-</b> <b>{{$lead->full_name }} </b><br>
                    @endif
                    <b>Purpose-</b> {{$lead->purpose ?? '' }}<br>
                    <b>City-</b> {{$lead->form_name ?? '' }}<br>
                    <b>Electricity-</b> {{$lead->campaign_name ?? '' }}<br>
                </td>
              <td style="padding-top: 10px;">
    <div style="display: flex; align-items: center;">
        <a href="tel:{{$lead->phone}}" style="margin-right: 10px;">
            <i class="fas fa-phone fa-lg"></i>
        </a>
        <a href="https://wa.me/{{$lead->phone}}" target="_blank" rel="noopener noreferrer" style="margin-right: 10px;">
            <i class="fab fa-whatsapp fa-lg" style="color: green;"></i>
        </a>
        <button onclick="showActionsModal({{$lead->id}})" class="btn btn-info">
            <i class="fas fa-ellipsis-h"></i>
        </button>
    </div>
</td>


                <td>
                    {{$lead->plant ?? ''}}
                </td>
                <td style="color:green">
                    @php
                        $user = DB::table('users')->where('id', $lead->owner_id)->orderBy('id', 'desc')->first();
                    @endphp
                    {{$user->name ?? ''}} <br>
                    <a onclick="assign({{$lead->id}})" style="color:#fff" data-toggle="modal" data-target="#exampleModal3" class="btn btn-info">Assign</a>
                </td>
                <td>
                    @can('update-lead', User::class)
                        <a class="mr-2 text-primary" href="{{url('/lead/show', $lead->id)}}">
                            <i class="fas fa-edit"></i>
                        </a>
                    @endcan
                    @can('delete-lead', User::class)
                        <span id="delbtn{{@$lead->id}}"></span>
                        <form id="delete-lead-{{$lead->id}}" action="{{ url('lead/destroy', $lead->id) }}" method="POST">
                            @method('DELETE')
                            @csrf
                        </form>
                    @endcan
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

                </div>
                </div>
              </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  {{-- model --}}
<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document" id="popup2">
  
  </div>
</div>


<!---->

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" id="popup1">
   
</div>

</div>

<!---->

<!---->

<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" id="popup4">
   
  </div>
</div>

<!---->

<!---->

<!---->
<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" id="popup3">
   
</div>
</div>


<div class="modal fade" id="exampleModal3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" id="popup5">
   
  </div>
</div>

<div class="modal fade" id="exampleModal4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" id="popup6">
   
  </div>
</div>
<div class="modal fade" id="exampleModal5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" id="popup7">
   
  </div>
</div>
<!-- Modal for additional actions -->
<div class="modal fade" id="actionsModal" tabindex="-1" role="dialog" aria-labelledby="actionsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionsModalLabel">Additional Actions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="actionsModalBody">
                <!-- Action buttons will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




  {{-- end --}}
@endsection

@section('scripts')

<script>

function not($id){
var id=$id;
$.ajax({
    url:'{{url("/lead/interest")}}',
    type:'get',
    data:{id:id},
    success:function(data){
     
        $('#popup1').html(data);
        
    }
})

    
}
</script>



<script>

function update($id){
var id=$id;
$.ajax({
    url:'{{url("/lead/update")}}',
    type:'get',
    data:{id:id},
    success:function(data){
     
        $('#popup6').html(data);
        
    }
})

    
}
</script>

<script>

function history($id){
var id=$id;
$.ajax({
    url:'{{url("/lead/tracking")}}',
    type:'get',
    data:{id:id},
    success:function(data){
     $('#popup2').html(data);
        
    }
})

    
}
</script>



<script>

function remarkupdate($id){
   
var id=$id;
$.ajax({
    url:'{{url("/lead/remarks")}}',
    type:'get',
    data:{id:id},
    success:function(data){
     $('#popup7').html(data);
        
    }
})

    
}
</script>


<script>

function pickup($id){
var id=$id;
$.ajax({
    url:'{{url("/lead/pickup")}}',
    type:'get',
    data:{id:id},
    success:function(data){
     $('#popup3').html(data);
        
    }
})

    
}
</script>

<script>

function follow($id){
var id=$id;
$.ajax({
    url:'{{url("/lead/follow")}}',
    type:'get',
    data:{id:id},
    success:function(data){
     $('#popup4').html(data);
        
    }
})

    
}
</script>

<script>

function assign($id){
var id=$id;
$.ajax({
    url:'{{url("/lead/assgin_lead")}}',
    type:'get',
    data:{id:id},
    success:function(data){
       
     $('#popup5').html(data);
        
    }
})

    
}

function toggleActions(leadId) {
    var actionsDiv = document.getElementById('actions-' + leadId);
    if (actionsDiv.style.display === 'none') {
        actionsDiv.style.display = 'block';
    } else {
        actionsDiv.style.display = 'none';
    }
}

function showActionsModal(leadId) {
    var modalBody = document.getElementById('actionsModalBody');
    modalBody.innerHTML = `
        <a data-toggle="modal" data-target="#exampleModalLong" onclick="history(${leadId}); closeAndOpenModal('#actionsModal', '#exampleModalLong');" data-toggle="tooltip" title="History" class="historyBtn btn btn-warning">
            <img src="{{asset('image/history.png')}}" alt="History" width="18" height="18">
        </a>
        <a data-toggle="modal" data-target="#exampleModal" lead_id="36375" incid="20579" id="ni" data-toggle="tooltip" onclick="not(${leadId}); closeAndOpenModal('#actionsModal', '#exampleModal');" title="Not Interested" class="openBtn btn btn-danger">
            <img src="{{asset('image/block-user.png')}}" alt="Not Interested" width="18" height="18">
        </a>
        <a href="#" data-toggle="modal" data-target="#exampleModalCenter" mobile="" lead_id="36375" incid="20579" onclick="follow(${leadId}); closeAndOpenModal('#actionsModal', '#exampleModalCenter');" data-toggle="tooltip" title="Follow UP" class="openBtn btn btn-warning">
            <img src="{{asset('image/writing.png')}}" alt="Follow UP" width="18" height="18">
        </a>
        <a href="#" data-toggle="modal" onclick="remarkupdate(${leadId}); closeAndOpenModal('#actionsModal', '#exampleModal5');" data-target="#exampleModal5" data-toggle="tooltip" incid="20579" id="np" data-toggle="tooltip" title="Remarks" class="openBtn btn btn-danger">
            <img src="{{asset('image/copy-writing.png')}}" alt="Remarks" width="18" height="18">
        </a>
        <a href="#" data-toggle="modal" onclick="pickup(${leadId}); closeAndOpenModal('#actionsModal', '#exampleModal2');" data-target="#exampleModal2" data-toggle="tooltip" incid="20579" id="np" data-toggle="tooltip" title="Lost Lead" class="openBtn btn btn-danger">
            <img src="{{asset('image/collaboration.png')}}" alt="Lost Lead" width="18" height="18">
        </a>
        <a>{{ ucfirst(@$lead->lead_status ?? '') }}</a>
        <a onclick="update({{$lead->id}})" style="color:#fff;margin-top:8px";  closeAndOpenModal('#actionsModal', '#exampleModal4');" data-toggle="modal" data-target="#exampleModal4" class="btn btn-danger">Change</a>
    `;
    $('#actionsModal').modal('show');
}

function closeAndOpenModal(currentModal, nextModal) {
    $(currentModal).modal('hide');
    $(currentModal).on('hidden.bs.modal', function (e) {
        $(nextModal).modal('show');
        $(currentModal).off('hidden.bs.modal');
    });
}

</script>






  @include('crm.lead.index_js')
@endsection


