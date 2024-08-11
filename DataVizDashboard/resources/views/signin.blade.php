<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title> 
    <link rel="stylesheet" href="{{ asset('css/sign.css') }}">
</head>
<body>
  <div class="wrapper">
    <h2>Login</h2>
    <br>
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger">
        {{ session('error') }}
      </div>
    @endif
    <form method="POST" action="login">
        @csrf
      <div class="input-box">
        <input type="text" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
      </div>
      <div class="input-box">
        <input type="password" name="password" placeholder="Enter password" required>
      </div>
      <div class="input-box button">
        <input type="submit" value="Login">
      </div>
      <div class="text">
        <h3>Don't have an account? <a href="signup">Signup now</a></h3>
      </div>
    </form>
  </div>
</body>
</html>
