
<!DOCTYPE html>
<html lang="en">
    <head>

        <style>
            /*------------- Content Boxes———————————————————— */
            .content-box-green,
            .content-box-gray {
                margin: 2%;
                overflow: hidden;
                padding: 1%;
            }

            .content-box-gray {
                background: linear-gradient( to bottom, #F5F5F5, #20ADAE);
                border: 1px solid #CCCCCC;
                border-radius: 5px;
            }

            .content-box-green {
                background-color: #FFF;
                border: 1px solid #20ADAE;
                border-radius: 5px;
                margin-left: 4%;
                margin-right: 4%;
            }
            .head{
                margin-left: 4%;
                margin-right: 4%;
            }

            /*----------- Color PLAC --------------*/
            .colorPLAC{
                color: #20ADAE;
            }

            .button{

                border-radius: 15px;
                color: #FFF;
                padding: 10px;

            }

            .button-success {
                background-color: #20ADAE;

            }


            .img{
                background-image: url("http://dev.placapp.com/images/image_email_pets_opt.png");
                background-repeat: no-repeat;
                background-position: center;
                padding-top: 10%;
                padding-bottom: 10%;
                width: 100%;
                height: 100%;
                background-size: 48%;

            }

            .content-red-social{
                text-align: center;
                color: #FFF;
            }

            a:link
            {
                text-decoration:none;
            }

            .link-social{
                margin-left: 1%;
            }

            .title {
                color: #000;
                margin-top:10px;
            }

            .top-btn-success{
                margin-top: 5%;
            }

        </style>

    </head>
    <body>

        <span colspan="2" style="border-bottom:1px solid #acacab" class="m_2101836623877976401gmail_msg">&nbsp;</span>

        <div class="content-box-gray">

            <div class="head">
                <img style=" width: 40px;height:40px" src="http://dev.placapp.com/images/logoplacgreen_welcome.png" alt=""/>
             
                <h3 class="title">{{$user}} </h3>
              
            </div>

            <div class="content-box-green">

                <table cellspacing="0" width="100%">

                    <thead>
                        <tr>
                            <th colspan="2" class="colorPLAC">
                                <h3> Publicación reportada</h3>
                            </th>
                        </tr>
                    </thead>

                    <tbody  style="margin-left:10px;margin-right:10px;font-size:13px;color:#717171">

                         <tr>
                            <td  style="text-align: justify;font-size:13px;color:#717171" align="center">
                                {{$msg}}
                            </td>
                        </tr>
                        
                         <tr>
                            <td  style="margin-top:10px;font-weight:bold;color:#717171;font-size:14px;"align="center">
                                {{$complaintName}}
                            </td>
                        </tr>
                    </tbody>
                </table>

                

                

                <div style="margin-top: 70px;"class="img">
                    <h1 class="colorPLAC" style="opacity: 0.5; margin-top: 8%; text-align: center"></h1>
                </div>
                
                <div style="margin-left:10px;margin-top:20px;font-size:10px">
                    PLAC revisará la publicación y te informará en caso de tomar una acción en contra.
                </div>

            </div>

            <div class="content-red-social">
                <label style="margin-left:10px;margin-bottom: 50px;" >Sigue creciendo con la comunidad de PLAC.</label>
                

                <a href="https://www.facebook.com/placoficial" target="_bank" class="link-social">
                    <img src="http://res.cloudinary.com/plac/image/upload/v1491500653/icons/facebook-logo.png" alt=""/>
                </a>

                <a href="https://twitter.com/placapp" target="_bank" class="link-social">
                    <img src="http://res.cloudinary.com/plac/image/upload/v1491500653/icons/twitter-logo.png" alt=""/>
                </a>

                <a href="https://www.instagram.com/placoficial" target="_bank" class="link-social">
                    <img src="http://res.cloudinary.com/plac/image/upload/v1491500653/icons/instagram-logo.png" alt=""/>
                </a>

            </div>

        </div>

    </body>
</html>
