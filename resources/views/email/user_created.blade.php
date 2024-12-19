<!DOCTYPE html>
<html>
<head>
    <title>Welcome to ZedTicket</title>
</head>
<body>
    <h1>Hello, {{ $user->fname }} {{ $user->lname }}!</h1>
    <p>Your account has been successfully created.</p>
    <p>Email: {{ $user->email }}</p>
    <p>Password: {{ $plainPassword }}</p>
    <p>Thank you for joining us! http://zedticket.co.zm:8080/</p>
</body>
</html>