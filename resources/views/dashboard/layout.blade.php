<!DOCTYPE html>
<html>
    <head>
        <title>Adding Rooms</title>
        <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico" />
        <link rel="stylesheet" href="assest/Dashboard/style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>
        <script src="{{ URL::asset('assets/js/attendanceFront.js') }}"></script>
    </body>
</html>
