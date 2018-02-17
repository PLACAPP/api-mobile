<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PLAC </title>
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
        <h1   >     {!! $header !!}</h1>

        <div style="float: right;width:20%">

            <img width="80" height="80" src="http://www.placapp.com/image/logo_plac.png"/>

        </div>



    </div>
    <div class="row body">


        <form>
            <ul>

                <li>
                    <label for="message">

                        {!! $message !!}
                        <span class="req"></span></label>

                </li>


            </ul>
        </form>

    </div>
</div>


</body>


</html>
