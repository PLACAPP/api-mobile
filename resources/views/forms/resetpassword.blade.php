<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PLAC - Resetear contrasena</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>

    <script src="http://api.placapp.com/assets/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" type="text/css" href="http://api.placapp.com/assets/dist/sweetalert.css">
    <link rel="stylesheet" href="http://api.placapp.com/assets/css/form/style.css">
    <link rel="stylesheet" href="http://api.placapp.com/assets/css/form/normalize.css">


</head>


<body>


<div class="container">
    <div class="row header">
        <h1>Cambiar contraseña</h1>

    </div>
    <div class="row body">
        {!! Form::open(array('url' => 'placuser/changepasswordreset', 'method' => 'post')) !!}

        {!! Form::hidden('plac_user_email', $email_user) !!}
        {!! Form::hidden('token', $token) !!}
        <ul>

            <li>
                <label for="first_name">Contraseña nueva<span class="req">*</span></label>
                <input type="password" name="pup"/>
            </li>

            <li>
                <label for="email">Confirmar contraseña nueva<span class="req">*</span></label>
                <input type="password" name="purp"/>
            </li>


            <li>
                <input class="btn btn-submit" type="submit" value="Enviar"/>

            </li>

        </ul>

        {!! Form::close() !!}
    </div>
</div>

<script>

    $(document).ready(function () {
        @if($password=="nomatch")
        swal("Error", "Las contraseña no coinciden", "error")
        @endif
    });

</script>
</body>


</html>
