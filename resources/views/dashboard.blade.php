@extends('layouts/dashboardlayout')

@section('content-section')
@can('local user')
    <style>
    div#social-links ul li{
        display: inline-block;
    }
    div#social-links ul li a{
        padding: 10px;
        font-size: 30px !important;
    }
    </style>
    {!! $shareComponent !!}

<h6 style="cursor:pointer;" data-code="{{Auth::user()->referral_code}}" class="copy"><span class="fa fa-copy mr-1"></span>Copy Referral Link</h6>
<h2 class="mb-4" style="float: left;">Dashboard</h2>
<h2 class="mb-4" style="float: right;">{{ $networkCount*10 }} Points</h2>
    
<table class="table">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Verified</th>
        </tr>
    </thead>
    <tbody>
        @if (count($networkData) > 0){
            @php
                $x = 1;
            @endphp
            @foreach ($networkData as $network )
              <tr>
                <td>{{$x++}}</td>
                <td>{{$network->user->name}}</td>
                <td>{{$network->user->email}}</td>
                <td>
                    @if ($network->user->is_verified == 0)
                        <b style="color: red;">Un verified</b>
                    @else
                        <b style="color: green;">Verified</b>
                    @endif
                </td>
              </tr>
            @endforeach
        }@else{
            <th colspan="4">No Referrals</th>
        }
            
        @endif

       </table>
@endcan


        <table class="table">
            
        @can('super admin')
        <h1>This is a super admin dashboard</h1>
        <h3 class="m-5 text-center">This is detailed list of the user in your Company</h3>
        
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Email</th>
                <th>Referral Code</th>
                <th>Password</th>
                <th>verified</th>
            </tr>
        </thead>
        @foreach (App\Models\User::all() as $user )
        <tbody>
                    <td>{{$user->id}}</td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td>
                        @if (empty($user->referral_code))
                            {{"Null"}}
                        @else
                        {{$user->referral_code}}
                        @endif
                    <td>{{Str::limit($user->password,20)}}</td>
                    <td>
                        @if (empty($user->is_verified))
                        <p class="fw-5 text-danger">Unverified</p>
                        @else
                        <p class="fw-5 text-primary">Verified</p>

                        @endif
                    </td>
                   
                  </tr>
             </tbody>
            @endforeach
       
            
      
        @endcan
    </tbody>
</table>

    <script>
        $(document).ready(function(){
            $('.copy').click(function(){
                $(this).parent().prepend('<span class="copied_text">Copied</span>');
            
                var code = $(this).attr('data-code');
                var url = "{{ URL::to('/') }}/referral-register?ref="+code;

                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(url).select();
                document.execCommand("copy");
                $temp.remove();

                setTimeout(() => {
                    $('.copied_text').remove();
                },2000);
            
            });
        });
    </script>
@endsection