<h1>Register</h1>
<form  action="{{ route('registered') }}">

    @csrf

    <input type="text" name="name" placeholder="Enter Name">
    @error('name')
    <span style="color: red;">{{ $message }}</span>
    @enderror
    <br> <br>
    <input type="email" name="email" placeholder="Enter Email">
    @error('email')
    <span style="color: red;">{{ $message }}</span>
    @enderror
    <br> <br>
    <input type="text" name="referral_code" placeholder="Enter Referral Code(Optional)" value="{{$referral}}" style="pointer-events:none;background-color:lightgrey; ">
    <br><br>
    <input type="password" name="password" placeholder="Enter Password">
    @error('password')
    <span style="color: red;">{{ $message }}</span>
    @enderror
    <br> <br>
    <input type="password" name="password_confirmation" placeholder="Enter Confirm Password">
    <br> <br>
    <input type="submit" value="Register"> 
</form>

 @if (Session::has('success'))
    <p style="color: green;">{{ Session::get('success')}}</p>     
 @endif

 @if (Session::has('error'))
 <p style="color: red;">{{ Session::get('error')}}</p>     
@endif
