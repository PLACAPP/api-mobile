<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <style type="text/css">
            /* CLIENT-SPECIFIC STYLES */
            body, table, td, a {
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }

            /* Prevent WebKit and Windows mobile changing default text sizes */
            table, td {
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
            }

            /* Remove spacing between tables in Outlook 2007 and up */
            img {
                -ms-interpolation-mode: bicubic;
            }

            /* Allow smoother rendering of resized image in Internet Explorer */

            /* RESET STYLES */
            img {
                border: 0;
                height: auto;
                line-height: 100%;
                outline: none;
                text-decoration: none;
            }

            table {
                border-collapse: collapse !important;
            }

            body {
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            A:link {text-decoration:none;color:#20ADAE;} 
            A:visited {text-decoration:none;color:#E3E3E3;} 
            A:active {text-decoration:none;color:#E3E3E3;} 
            A:hover {text-decoration:underline;color:#59DCDC;} 

            /*----------------------------------------------------------------*/

            .textDetailSale{
                margin: 10px;
            }

            .imgCircule{
                /* cambia estos dos valores para definir el tamaño de tu círculo */
                height: 100px;
                width: 100px;
                /* los siguientes valores son independientes del tamaño del círculo */
                background-repeat: no-repeat;
                background-position: 50%;
                border-radius: 50%;
                background-size: 100% auto;
            }

            .textCenter{
                text-align: center;
            }
            .textLeft{
                text-align: left;
            }
            .textRight{
                text-align: right;
            }

            .row{
                margin: 15px;
            }

            .colorPlac{
                color: #20ADAE;
            }

            .table{
                border: 1px #9d9d9d solid;
                border-radius: 10px;
            }

            .headContainer{
                padding: 30px;
                border-bottom:  1px #9d9d9d solid;
                background-color: #E3E3E3;
            }
            .bodyContainer{
                padding: 30px;
            }

            .imgProduct{
                display: inline-block;
            }

            /*----------------------------------------------------------------*/


        </style>
    </head>
    <body style="margin: 0 !important; padding: 0 !important;">

        @if($stock == 0)


        <div class="row">
            <div class="textCenter">
                <h2 class="colorPlac">Producto con stock en cero</h2>
            </div>
        </div>

        <div class="row">
            <div class="textCenter">
                <p>
                    {{$messageViewEmail}}
                </p>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="table">
                <div class="headContainer">

                    <table cellspacing="0" width="100%">

                        <td class="textCenter">
                            <img src="http://dev.placapp.com/images/logoPlacHomepages.png" alt="" />
                        </td>
                        <td class="textCenter colorPlac">
                            <h3>Caracteristica del producto</h3>
                        </td>

                    </table>

                </div>
                <div class="bodyContainer">

                    <table cellspacing="0" width="100%">
                        <td class="textCenter">
                            <img src="{{$productImageMain}}" alt="" class="imgCircule"/>
                        </td>
                        <td class="textLeft">
                            <strong>Producto: </strong>
                            <br>
                            <strong>Stock: </strong>
                        </td>
                        <td class="textLeft">
                            <span>{{$productName}}</span>
                            <br>
                            <span>{{$stock}}</span>
                        </td>
                    </table>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="textCenter">
                <p>
                    Gracias por dejarnos ayudarte a crecer como empresa y que muchos usuarios mas te conozcan.
                </p>
            </div>
        </div>


        <div class="row">
            <div class="textCenter">
                <p>
                    Si te encuentras logueado en la pagina web de PLAC has click en el siguiente link <br>
                    para ir directamente a <a href="http://dev.placapp.com/productos/{{$product_id}}">actuaslizar producto</a>.
                </p>
            </div>
        </div>

        @else


        <div class="row">
            <div class="textCenter">
                <h2 class="colorPlac">Solicitud de compra</h2>
            </div>
        </div>

        <div class="row">
            <div class="textCenter">
                <p>
                    {{$messageViewEmail}}
                </p>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="table">
                <div class="headContainer">

                    <table cellspacing="0" width="100%">

                        <td class="textCenter">
                            <img src="http://www.placapp.com/images/logoPlacHomepages.png" alt="" />
                        </td>
                        <td class="textCenter colorPlac">
                            <h3>Detalle de venta</h3>
                        </td>

                    </table>

                </div>
                <div class="bodyContainer">

                    <table cellspacing="0" width="100%">
                        <td class="textCenter">
                            <img src="{{$productImageMain}}" alt="" class="imgCircule"/>
                        </td>
                        <td class="textLeft">
                            <strong>Producto: </strong>
                            <br>
                            <strong>Precio: </strong>
                            <br>
                            <strong>Cantidad: </strong>
                            <br>
                            <strong>Total: </strong>
                        </td>
                        <td class="textLeft">
                            <span>{{$productName}}</span>
                            <br>
                            <span>{{$salePrice}}</span>
                            <br>
                            <span>{{$saleQuantity}}</span>
                            <br>
                            <span>{{$salePriceEnd}}</span>
                            <br>
                            <strong>IVA incluido.</strong>
                        </td>
                    </table>

                </div>
            </div>
        </div>

        @endif

        <div class="row">
            <div class="textCenter">
                Enviado por:
                <a href="http://www.placapp.com">
                    http://www.placapp.com
                </a>
            </div>
        </div>

    </body>
</html>
